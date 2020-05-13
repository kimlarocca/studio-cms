<?php require_once('Connections/wotg.php'); ?>
<?php
$today = date("Y-m-d", strtotime("now"));
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "student-login.php?action=failed&error=studenthome";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  //if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$colname_student = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_student = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_student = sprintf("SELECT students.reviewStars, students.studentID,students.emailAddress,students.classesLeft,students.firstName,students.studioID,studios.studioID,students.waiver,studios.studioID,studios.studioTimezone,studios.studioName,studios.logoURL,studios.messageStudents,studios.allowStudentSelfCheckin,studios.enableMemberships,studios.color,studios.colorFont FROM students,studios WHERE emailAddress = %s AND students.studioID=studios.studioID", GetSQLValueString($colname_student, "text"));
$student = mysql_query($query_student, $wotg) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);

if ($row_student['studentID']==NULL) header("Location: student-selectStudio.php");

mysql_select_db($database_wotg, $wotg);
$query_member = "SELECT studentID,endDate,startDate FROM members  WHERE studentID = ".$row_student['studentID']." ORDER BY endDate DESC";
$member = mysql_query($query_member, $wotg) or die(mysql_error());
$row_member = mysql_fetch_assoc($member);
$totalRows_member = mysql_num_rows($member);

mysql_select_db($database_wotg, $wotg);
$query_upcoming = "SELECT attendance.classID,attendance.studentID,attendance.dateAdded,attendance.attendanceID,attendance.attendanceType,classes.name,classes.startTime, classes.classID FROM attendance,classes WHERE attendance.classID=classes.classID AND attendance.studentID = ".$row_student['studentID']." AND attendance.dateAdded >= '".$today."' ORDER BY attendance.dateAdded, classes.startTime ASC";
$upcoming = mysql_query($query_upcoming, $wotg) or die(mysql_error());
$row_upcoming = mysql_fetch_assoc($upcoming);
$totalRows_upcoming = mysql_num_rows($upcoming);

mysql_select_db($database_wotg, $wotg);
$query_packages = "SELECT packageID, studioID, packageName, packageCost, numberOfSessions, daysValidFor FROM packages WHERE studioID = ".$row_student['studioID']." ORDER BY packageCost ASC";
$packages = mysql_query($query_packages, $wotg) or die(mysql_error());
$row_packages = mysql_fetch_assoc($packages);
$totalRows_packages = mysql_num_rows($packages);

mysql_select_db($database_wotg, $wotg);
$query_upcomingEvents = "SELECT * FROM eventAttendance,events WHERE events.eventDate >= CURRENT_DATE() AND eventAttendance.eventID=events.eventID AND eventAttendance.studentID = ".$row_student['studentID'];
$upcomingEvents = mysql_query($query_upcomingEvents, $wotg) or die(mysql_error());
$row_upcomingEvents = mysql_fetch_assoc($upcomingEvents);
$totalRows_upcomingEvents = mysql_num_rows($upcomingEvents);

//process dates
date_default_timezone_set($row_student['studioTimezone']);
$today = $expiry_date = date("Y-m-d", strtotime("now"));
$month = date('m', strtotime($today));
$day = date('d', strtotime($today));
$year = date('Y', strtotime($today));
$dateArray=getdate(mktime(0,0,0,$month,$day,$year));
$dayPicked = $dateArray[weekday];

mysql_select_db($database_wotg, $wotg);
$query_classes = "SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studio, classes.classCapacity, classes.studioID, classes.thumbnail, instructors.instructorID, instructors.firstName, instructors.studioID FROM classes,instructors WHERE classes.instructorID=instructors.instructorID AND classes.studioID = ".$row_student['studioID']." AND classes.classDay = '".$dayPicked."' AND classes.classActive = 1 ORDER BY classes.startTime ASC";
// old working query
//$query_classes = "SELECT * FROM classes,instructors WHERE classes.instructorID=instructors.instructorID AND classes.studioID = ".$row_student['studioID']." AND classes.classDay = '".$dayPicked."' AND classes.classActive = 1 ORDER BY classes.startTime ASC";
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);

mysql_select_db($database_wotg, $wotg);
$query_cancellations = "SELECT cancellationDate,studioID,classID FROM classCancellations WHERE cancellationDate = '".$today."' AND studioID=".$row_student['studioID'];
$cancellations = mysql_query($query_cancellations, $wotg) or die(mysql_error());
$row_cancellations = mysql_fetch_assoc($cancellations);
$totalRows_cancellations = mysql_num_rows($cancellations);

mysql_select_db($database_wotg, $wotg);
$query_cancellations2 = "SELECT cancelAllClasses,cancellationDate,studioID FROM classCancellations WHERE cancelAllClasses = 1 AND cancellationDate = '".$today."' AND studioID=".$row_student['studioID'];
$cancellations2 = mysql_query($query_cancellations2, $wotg) or die(mysql_error());
$row_cancellations2 = mysql_fetch_assoc($cancellations2);
$totalRows_cancellations2 = mysql_num_rows($cancellations2);

$cancellationsArray = array();
do { 
array_push($cancellationsArray, $row_cancellations['classID']);
} while ($row_cancellations = mysql_fetch_assoc($cancellations));

ini_set('session.save_path',getcwd(). '/../tmp/');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
<link rel="icon" type="image/png" href="favicon-16x16.png" sizes="16x16" />
<link rel="stylesheet" type="text/css" href="styles.css"/>
<title><?php echo $row_student['studioName']; ?>| My Account</title>
<script type="text/javascript">
function MM_validateForm() { //v4.0
  if (document.getElementById){
    var i,p,q,nm,test,num,min,max,errors='',args=MM_validateForm.arguments;
    for (i=0; i<(args.length-2); i+=3) { test=args[i+2]; val=document.getElementById(args[i]);
      if (val) { nm=val.name; if ((val=val.value)!="") {
        if (test.indexOf('isEmail')!=-1) { p=val.indexOf('@');
          if (p<1 || p==(val.length-1)) errors+='- '+nm+' must contain an e-mail address.\n';
        } else if (test!='R') { num = parseFloat(val);
          if (isNaN(val)) errors+='- '+nm+' must contain a number.\n';
          if (test.indexOf('inRange') != -1) { p=test.indexOf(':');
            min=test.substring(8,p); max=test.substring(p+1);
            if (num<min || max<num) errors+='- '+nm+' must contain a number between '+min+' and '+max+'.\n';
      } } } else if (test.charAt(0) == 'R') errors += '- '+nm+' is required.\n'; }
    } if (errors) alert('The following error(s) occurred:\n'+errors);
    document.MM_returnValue = (errors == '');
} }
</script>
</head>

<body>
<?php include("student-header.php"); ?>
<h1 class="twd_centered studentH1"><?php echo $row_student['studioName']; ?> | <?php echo $row_student['firstName']; ?>'s Account</h1>
<?php 
//get logo if it exists
if($row_student['logoURL']!=''){ 
?>
<div class="twd_centered twd_margin20" style="padding-top:20px; clear:both"><img src="uploads/<?php echo $row_student['logoURL']; ?>" /></div>
<?php 
}
?>
<?php 
//get logo if it exists
if($row_student['logoURL']!=''){ 
?>
<div class="twd_centered twd_margin20" style="padding-top:20px; clear:both"><img src="uploads/<?php echo $row_student['logoURL']; ?>" /></div>
<?php 
}
?>
<div class="twd_container">
  <div class="twd_row">
    <div class="twd_column twd_two twd_margin20 twd_breakOnTablet">
      <?php if ($row_student['waiver']==0){ ?>
      <h3 class="red">action required</h3>
      <p>Save time before class! Please review and sign our waiver with your touch screen device.</p>
      <a class="button twd_margin20" target="_blank" href="waiver.php?studentID=<?php echo $row_student['studentID']; ?>">sign now</a>&nbsp; <a class="button twd_margin20" href="mailto:<?php echo $row_student['emailAddress']; ?>?subject=please%20sign%20our%20waiver&body=Save time before class and sign our waiver now with your touch screen device: https://my.slateit.com/waiver.php?studentID=<?php echo $row_student['studentID']; ?>">email link</a>
      <?php } ?>
      <?php
//check url parameters
if ($_GET['action'] == 'password') print '<p style="color:red;">Your password has been updated!</p>';
?>
      <?php if ($row_student['messageStudents']!=''){ ?>
      <h2 class="subheader twd_centeredOnTablet">Message To Students</h2>
      <p class="twd_margin30 twd_centeredOnTablet"><?php echo $row_student['messageStudents']; ?></p>
      <?php } ?>
      <h2 class="subheader twd_centeredOnTablet">Today's Classes</h2>
      
      <?php 
	  if ($totalRows_cancellations2>0){
	echo '<p class="red twd_centeredOnTablet">Sorry - all classes have been cancelled today!</p>';
 } else {
	 if ($totalRows_classes==0){?>
      <p class="red twd_centeredOnTablet">Sorry - there are no classes today!</p>
      <?php } else {?>
      <p class="twd_centeredOnTablet">Click on any class to reserve your spot!</p>
      <table border="0" cellpadding="3" cellspacing="0" class="twd_margin40" id="todaysClasses">
    <?php do { 
	$formattedTime = date("g:i a", strtotime($row_classes['startTime']));  
	$formattedEndTime = date("g:i a", strtotime($row_classes['endTime'])); 

		//check availability and cancellations
		mysql_select_db($database_wotg, $wotg);
		$query_attendance = "SELECT * FROM attendance WHERE dateAdded = '".$today."' AND classID=".$row_classes['classID'];
		$attendance = mysql_query($query_attendance, $wotg) or die(mysql_error());
		$row_attendance = mysql_fetch_assoc($attendance);
		$totalRows_attendance = mysql_num_rows($attendance);
		$allowReservation = TRUE;
		
		if (in_array($row_classes['classID'], $cancellationsArray)) {
    $classMessage = '<span class="red">This class is has been cancelled!</span>';
		$allowReservation = FALSE;
} else {
		if($row_classes['classCapacity']==$totalRows_attendance){
			$classMessage = '<span class="red">This class is full!</span>';
		$allowReservation = FALSE;
		} else {
		$classMessage = '<span class="green">'.($row_classes['classCapacity']-$totalRows_attendance).' spots left!</span>'; 
		} }
		?>
      <tr>
        <td class="twd_centered"> <?php if ($allowReservation) { ?><a href="student-register-date2.php?datePicked=<?php echo $today; ?>&classID=<?php echo $row_classes['classID']; ?>"><?php } if($row_classes['thumbnail']!=''){ ?>
        <img height="200" width="200" src="uploads/<?php echo $row_classes['thumbnail']; ?>" />
        <?php } else { ?>
        <img height="200" width="200" src="uploads/unavailable.gif" />
        <?php } if ($allowReservation) { ?></a><?php } ?></td>
        <td>
        <p class="twd_centeredOnMobile"><?php echo $formattedTime.' - '.$formattedEndTime; ?></p>
        <p class="twd_centeredOnMobile"><strong><?php if ($allowReservation) { ?><a href="student-register-date2.php?datePicked=<?php echo $today; ?>&classID=<?php echo $row_classes['classID']; ?>"><?php } echo $row_classes['name']; if ($allowReservation) { ?></a><?php } ?></strong></p>
        <p class="twd_centeredOnMobile"><?php echo $classMessage; ?></p>
            <p class="twd_centeredOnTablet">Instructor: <a href="student-instructors.php"><?php echo $row_classes['firstName']; ?></a></p>
        <p class="twd_centeredOnTablet">Room: <?php echo $row_classes['studio']; ?></p></td>
      </tr>
      <?php } while ($row_classes = mysql_fetch_assoc($classes)); ?>
  </table>
      <?php } } ?>
    </div>
    <div class="twd_column twd_two twd_margin20 twd_breakOnTablet">
    <?php if($row_student['reviewStars']==''){ ?> 
      <div class="twd_margin20"><h3>PLEASE RATE US! <a href="student-rate.php?stars=1"><img class="star1" src="images/star.png" width="30" height="28" alt="1 star" /></a><a href="student-rate.php?stars=2"><img class="star2" src="images/star.png" width="30" height="28" alt="2 stars" /></a><a href="student-rate.php?stars=3"><img class="star3" src="images/star.png" width="30" height="28" alt="3 star2" /></a><a href="student-rate.php?stars=4"><img class="star4" src="images/star.png" width="30" height="28" alt="4 stars" /></a><a href="student-rate.php?stars=5"><img class="star5" src="images/star.png" width="30" height="28" alt="5 stars" /></a></h3></div>
      <?php } ?>
      <h2 class="subheader twd_centeredOnTablet">Reservations</h2>
      
      <div class="twd_margin20 twd_centeredOnTablet"><a class="button" href="student-events.php">view upcoming events &amp; sessions</a></div>
      <h3 class="twd_centeredOnTablet">Reserve a spot in a class</h3>
      <form action="student-register-date.php" method="get" class="twd_margin30">
        <p class="twd_centeredOnTablet">Please choose a date to make your reservation!</p>
        <input class="twd_centeredOnTablet" type="text" id="datePicked" name="datePicked">
        <input class="twd_centeredOnTablet" name="Continue" type="submit" value="Continue" />
      </form>
      <h3 class="twd_centeredOnTablet">Upcoming Reservations</h3>
      <?php
//check url parameters
if ($_GET['action'] == 'cancelled') print '<p style="color:red;" class="twd_centeredOnTablet">Your reservation has been cancelled!</p>';
if ($_GET['action'] == 'reserved') print '<p style="color:red;" class="twd_centeredOnTablet">Your reservation has been saved!</p>';
if ($_GET['action'] == 'eventreserved') print '<p style="color:red;" class="twd_centeredOnTablet">Your reservation has been saved!</p>';
if ($_GET['action'] == 'error') print '<p style="color:red;" class="twd_centeredOnTablet">Sorry! There was an error processing your payment. Your reservation was not saved. Please try again!</p>';
?>
      <?php
	  if($totalRows_upcoming==0&&$totalRows_upcomingEvents==0){ 
	 echo '<p class="twd_margin30 twd_centeredOnTablet">You have no upcoming reservations.</p>';
	  }
	  else {
 if($totalRows_upcoming>0){
	 ?>
      <table border="0" cellpadding="3" cellspacing="0" class="twd_margin20">
        <tr>
          <th><strong>date</strong></th>
          <th><strong>name</strong></th>
          <th>&nbsp;</th>
          <th><strong>type</strong></th>
          <?php if($row_student['allowStudentSelfCheckin'] == 1){?>
          <th>&nbsp;</th>
          <?php } ?>
          <th>&nbsp;</th>
        </tr>
        <?php do { 
		$newTime = date('g:i A', strtotime($row_upcoming['startTime']));
		?>
          <tr>
            <td class="twd_centeredOnTablet"><?php echo date("m/d/y", strtotime($row_upcoming['dateAdded'])); ?></td>
            <td class="twd_centeredOnTablet"><?php echo $row_upcoming['name']; ?></td>
            <td class="twd_centeredOnTablet"><?php echo $newTime; ?></td>
            <td class="twd_centeredOnTablet"><?php echo $row_upcoming['attendanceType']; ?></td>
            <?php if($row_student['allowStudentSelfCheckin'] == 1){?>
            <td align="center"><?php 
		if ($row_upcoming['checkedIn']==true) {
			?>
              <div style="text-align:center"><img src="images/checkmark.png" width="20" height="20" /></div>
              <?php } else {
		?>
              <button class="twd_centeredOnTablet" onclick="checkin(<?php echo $row_upcoming['attendanceID']; ?>);">check in</button>
              <?php } ?></td>
            <?php } ?>
            <td class="twd_centeredOnTablet"><a class="tooltip" title="cancel this reservation" href="student-cancel.php?attendanceID=<?php echo $row_upcoming['attendanceID']; ?>"><img src="images/delete.png" width="22" height="22" /></a></td>
          </tr>
          <?php } while ($row_upcoming = mysql_fetch_assoc($upcoming)); ?>
      </table>
      <?php } ?>
      <?php
 if($totalRows_upcomingEvents>0){
	 ?>
      <table border="0" cellpadding="3" cellspacing="0" class="twd_margin20">
        <tr>
          <th><strong>date</strong></th>
          <th><strong>name</strong></th>
          <th>&nbsp;</th>
          <!--<th>&nbsp;</th>-->
        </tr>
        <?php do { 
		$newTime = date('g:i A', strtotime($row_upcomingEvents['startTime']));
		?>
          <tr>
            <td class="twd_centeredOnTablet"><?php echo date("m/d/y", strtotime($row_upcomingEvents['eventDate'])); ?></td>
            <td class="twd_centeredOnTablet"><?php echo $row_upcomingEvents['eventName']; ?></td>
            <td class="twd_centeredOnTablet"><?php echo $newTime; ?></td>
            <!--<td class="twd_centeredOnTablet"><a class="tooltip" title="cancel this reservation" href="student-cancel-event.php?eventAttendanceID=<?php echo $row_upcomingEvents['eventAttendanceID']; ?>"><img src="images/delete.png" width="22" height="22" /></a></td>-->
          </tr>
          <?php } while ($row_upcomingEvents = mysql_fetch_assoc($upcomingEvents)); ?>
      </table>
      <?php } } ?>
      <div class="twd_margin30 twd_centeredOnTablet"><a class="button" href="student-reservations.php">reservation history</a></div>
      <h2  class="twd_centeredOnTablet subheader">Prepaid Packages</h2>
      <?php
//check url parameters
if ($_GET['action'] == 'prepaidPaid') print '<p style="color:red;" class="twd_centeredOnTablet">Your payment was successful and your account has been updated. Thank you!</p>';
?>
      <?php 
	  if($row_student['classesLeft']==0){
	  ?>
      <p class="twd_centeredOnTablet">You do not currently have any prepaid classes left on your account.</p>
      <?php
	  } else {
	  ?>
      <p class="twd_centeredOnTablet">You have<strong> <?php echo $row_student['classesLeft']; ?> </strong>prepaid classes left on your account.</p>
      <?php } 
	  if($totalRows_packages!=0){
	  ?>
      <h3 class="twd_centeredOnTablet">refill your account</h3>
      <table border="0" cellpadding="3" cellspacing="0" class="twd_margin30">
        <?php do { ?>
          <tr class="noStyle">
            <td class="noStyle twd_centeredOnTablet"><?php echo $row_packages['packageName']; ?>: </td>
            <td class="noStyle twd_centeredOnTablet">$<?php echo $row_packages['packageCost']; ?></td>
            <td class="noStyle twd_centeredOnTablet"><?php if ($row_packages['daysValidFor']!=""){ ?>
              <em>valid for <?php echo $row_packages['daysValidFor']; ?> days</em>
              <?php } ?></td>
            <td class="noStyle twd_centeredOnTablet"><a class="button" href="student-buy-prepaid.php?packageID=<?php echo $row_packages['packageID']; ?>">buy now</a></td>
          </tr>
          <?php } while ($row_packages = mysql_fetch_assoc($packages)); ?>
      </table>
      <?php } ?>
      <?php
	  if($row_student['enableMemberships']==1){
	  ?>
      <h2 class="subheader twd_centeredOnTablet">Memberships</h2>
      <?php
//check url parameters
if ($_GET['action'] == 'membershipPaid') print '<p style="color:red;" class="twd_centeredOnTablet">Your payment was successful and your membership is now active. You can now reserve spots in any class!</p>';
?>
      <?php if ($totalRows_member==0) { echo '<p class="twd_margin30 twd_centeredOnTablet">You do not have an active membership at this time!</p>'; } else { ?>
      <h3 class="twd_centeredOnTablet">active memberships:</h3>
      <?php 
	do { 
	//if ($row_member['startDate'] <= $today && $row_member['endDate'] >= $today) {;
	if ($row_member['endDate'] >= $today) {;
	?>
      <div style="border-top:#fff solid 1px; width:100%; padding-bottom:20px;">
        <p class="twd_centeredOnTablet"><strong>Start Date:</strong> <?php echo date("m/d/y", strtotime($row_member['startDate'])); ?></p>
        <p class="twd_centeredOnTablet"><strong>End Date:</strong> <?php echo date("m/d/y", strtotime($row_member['endDate'])); ?></p>
      </div>
      <?php } } while ($row_member = mysql_fetch_assoc($member)); ?>
      <?php } } ?>
      <div class="twd_margin30">
        <form action="student-buy-membership.php" method="get" name="form0" onsubmit="MM_validateForm('startDate','','R');return document.MM_returnValue">
          <input class="twd_centeredOnTablet" type="text" id="startDate" name="startDate" placeholder="Select a Start Date" required="required">
          <input class="twd_centeredOnTablet" name="submit2" type="submit" value="buy membership" />
        </form>
      </div>
    </div>
  </div>
</div>
<?php include("footer.php"); ?>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<script type="text/javascript" src="datePicker/picker.js"></script>
<script>
/**
 * pick a date
 */
$('#datePicked').pickadate({
  onOpen: function() {
    scrollIntoView( this.$node )
  },
  format: 'yyyy-mm-dd'
})
$('#startDate').pickadate({
  onOpen: function() {
    scrollIntoView( this.$node )
  },
  format: 'yyyy-mm-dd'
})
function scrollIntoView( $node ) {
  $('html,body').animate({
      scrollTop: ~~$node.offset().top - 60
  })
}
function checkin(attendanceID){
	jQuery.ajax({
	 type: "POST",
	 url: "attendance-sheet-checkin.php",
	 data: 'attendanceID='+attendanceID,
	 cache: false,
	 success: function(response)
	 {
	   location.reload();
	 }
   });
}
//rating stars
$(document).ready(function(){
	 $('.star2').hover(function(){
		  $('.star1, .star2').addClass('star-yellow');
	 },function(){
		  $('.star1, .star2').removeClass('star-yellow');
	 });
	 $('.star3').hover(function(){
		  $('.star1, .star2, .star3').addClass('star-yellow');
	 },function(){
		  $('.star1, .star2, .star3').removeClass('star-yellow');
	 });
	 $('.star4').hover(function(){
		  $('.star1, .star2, .star3, .star4').addClass('star-yellow');
	 },function(){
		  $('.star1, .star2, .star3, .star4').removeClass('star-yellow');
	 });
	 $('.star5').hover(function(){
		  $('.star1, .star2, .star3, .star4, .star5').addClass('star-yellow');
	 },function(){
		  $('.star1, .star2, .star3, .star4, .star5').removeClass('star-yellow');
	 });
});
</script>
</html>
<?php
mysql_free_result($student);
mysql_free_result($member);
mysql_free_result($upcoming);
mysql_free_result($packages);

mysql_free_result($upcomingEvents);
mysql_free_result($classes);
mysql_free_result($cancellations);
mysql_free_result($cancellations2);
?>