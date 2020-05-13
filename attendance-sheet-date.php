<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
if (!isset($_SESSION)) {
  session_start();
}
?>
<?php require_once('Connections/wotg.php'); ?>
<?php
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

$MM_restrictGoTo = "login-failed.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
<?php
$today = $_GET['dateAdded'];
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

$colname_currentUser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_currentUser = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_currentUser = sprintf("SELECT * FROM instructors,studios WHERE instructors.studioID=studios.studioID AND instructors.username = %s", GetSQLValueString($colname_currentUser, "text"));
$currentUser = mysql_query($query_currentUser, $wotg) or die(mysql_error());
$row_currentUser = mysql_fetch_assoc($currentUser);
$totalRows_currentUser = mysql_num_rows($currentUser);

$_SESSION['studioID'] = $row_currentUser['studioID'];

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$colname_classes = "-1";
if (isset($_GET['classID'])) {
  $colname_classes = $_GET['classID'];
}
mysql_select_db($database_wotg, $wotg);
$query_classes = sprintf("SELECT * FROM classes INNER JOIN instructors ON classes.instructorID = instructors.instructorID WHERE classID = %s", GetSQLValueString($colname_classes, "int"));
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);

mysql_select_db($database_wotg, $wotg);
$query_currentMembers = "SELECT * FROM members,students WHERE members.studioID = ".$row_currentUser['studioID']." AND members.studentID = students.studentID AND '".$today."' between members.startDate AND members.endDate";
$currentMembers = mysql_query($query_currentMembers, $wotg) or die(mysql_error());
$row_currentMembers = mysql_fetch_assoc($currentMembers);
$totalRows_currentMembers = mysql_num_rows($currentMembers);

mysql_select_db($database_wotg, $wotg);
$query_instructors = "SELECT * FROM instructors WHERE studioID = ".$row_currentUser['studioID']." AND active = TRUE ORDER BY lastName ASC";
$instructors = mysql_query($query_instructors, $wotg) or die(mysql_error());
$row_instructors = mysql_fetch_assoc($instructors);
$totalRows_instructors = mysql_num_rows($instructors);

	  if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
	//validate form
	$valid = true;
	if (empty($_POST["studentID"])) {
      $err = "Error: student name is required<br/><br/>";
      $valid = false; //false
   }
   if (empty($_POST["attendanceType"])) {
      $err .= "Error: attendance type is required<br/><br/>";
      $valid = false;
   }
   if ($_POST["attendanceType"] == 'Drop In' && empty($_POST["paymentType"])) {
      $err .= "Error: payment type is required<br/><br/>";
      $valid = false;
   }
   
  //if valid then continue
  if($valid){
	  $validStudent = true;
	//handle prepaid pakages	  
    if ($_POST["attendanceType"] == 'Pre Paid') {
	  $studentrecord = "SELECT studentID,classesLeft FROM students WHERE studentID = ".$_POST['studentID'];
	  mysql_select_db($database_wotg, $wotg);
	  $studentResult = mysql_query($studentrecord, $wotg) or die(mysql_error());
	  $row_studentrecord = mysql_fetch_assoc($studentResult);
	  if (($row_studentrecord['classesLeft']==0)) {
		$validStudent = false;
		echo "<script>alert('Warning - the student you are trying to add does not have classes left on their account! Please add classes to their account in order to add them to this attendance sheet.');</script>";
	  	$classesLeft = 0;
	  } else {
		$classesLeft = $row_studentrecord['classesLeft']-1;
	  }
	  $updaterecord = "UPDATE students SET classesLeft=".$classesLeft." WHERE studentID = ".$_POST['studentID'];
	  mysql_select_db($database_wotg, $wotg);
	  mysql_query($updaterecord, $wotg) or die(mysql_error());	
	}
	  
	//check for 'no one' student
	$paymentType = $_POST['paymentType'];
	if($_POST['studentID']==3107){ $paymentType = 'NA'; }
	if($validStudent){
  $insertSQL = sprintf("INSERT INTO attendance (studentID, classID, instructorID, dateAdded, attendanceType, paymentType, studioID) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['studentID'], "int"),
                       GetSQLValueString($_POST['classID'], "int"),
                       GetSQLValueString($_POST['instructorID'], "int"),
                       GetSQLValueString($_POST['dateAdded'], "date"),
                       GetSQLValueString($_POST['attendanceType'], "text"),
                       GetSQLValueString($paymentType, "text"),
                       GetSQLValueString($row_currentUser['studioID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());

  $insertGoTo = "attendance-sheet-date.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
  }}
}
?>
<?php
$instructorID=$row_classes['instructorID'];
if ($_GET['instructorID'] != '') $instructorID=$_GET['instructorID'];

//add member to list
if ($_GET['action'] == 'addMember') { 
	$addrecords = "INSERT INTO attendance(studentID, classID, instructorID, dateAdded, attendanceType, studioID) VALUES (".$_GET['studentID'].",".$_GET['classID'].",".$instructorID.",'".$_GET['dateAdded']."','Member',".$row_currentUser['studioID'].")";
	mysql_select_db($database_wotg, $wotg);
	mysql_query($addrecords, $wotg) or die(mysql_error());	
    header("Location: ?classID=".$_GET['classID']."&dateAdded=".$_GET['dateAdded']);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
<link rel="icon" type="image/png" href="favicon-16x16.png" sizes="16x16" />
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/minified/jquery-ui.min.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="styles.css"/>
<title><?php echo $row_currentUser['studioName']; ?> | Attendance Sheet</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script> 
</head>

<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Online Attendance</h1>
<?php
if ($row_currentUser['securityLevel'] == 'administrator' OR $row_currentUser['securityLevel'] == 'super') {
?>
<?php include("navigation.php"); ?>
<?php } ?>
<div class="twd_container">

      <h3 class="twd_centered twd_margin30"><?php echo $row_classes['name']; ?> on <?php echo date( 'm/d/Y', strtotime($today)); ?></h3>
  <div class="twd_row">
    <div class="twd_column twd_two twd_margin20 twd_breakOnTablet">
      <div id="errorMessage" style="color:#F00"></div>
      <script>
	  //update error message
function updateErrorMessage(message){
  $("#errorMessage").html(message);
  }
</script>
<?php
if ($err != ''){
	echo '<script type="text/javascript">'
   , 'updateErrorMessage("'.$err.'");'
   , '</script>'
;
}
?>
      <h2 style="padding-bottom:0">Add a New Student</h2>
      <p><strong>Drop In Fee: $<?php echo $row_classes['classFee']; ?></strong></p>
      <form name="form1" action="<?php echo $editFormAction; ?>" method="POST">
        Instructor
        <select name="instructorID" id="instructorID">
          <?php
do {  
?>
          <option value="<?php echo $row_instructors['instructorID']?>"<?php if (!(strcmp($row_instructors['instructorID'], $instructorID))) {echo "selected=\"selected\"";} ?>><?php echo $row_instructors['lastName']?>, <?php echo $row_instructors['firstName']?></option>
          <?php
} while ($row_instructors = mysql_fetch_assoc($instructors));
  $rows = mysql_num_rows($instructors);
  if($rows > 0) {
      mysql_data_seek($instructors, 0);
	  $row_instructors = mysql_fetch_assoc($instructors);
  }
?>
        </select>
        Student Name <a href="javescript:void();" onclick="window.open('students-add.php','Add Student','height=500,width=300');return false;"><img src="images/addPerson.png" width="20" height="20" alt="add a new student" /></a>
        <input type="text" autocomplete="off" name="student" class="auto" id="student" />
        <input type="hidden" id="studentID" name="studentID" />
        Select One
        <select name="attendanceType" id="attendanceType">
          <option selected="selected"> </option>
          <option value="Drop In">Drop In</option>
          <option value="Pre Paid">Pre Paid</option>
        </select>
        Payment Type
        <select name="paymentType" id="paymentType">
          <option selected="selected"> </option>
          <option value="Cash">Cash</option>
          <option value="Check">Check</option>
          <option value="PayPal">PayPal</option>
          <option value="Credit Card">Credit Card</option>
        </select>
        <input name="submit" type="submit" value="Add Student" />
        <input name="action" type="hidden" value="add" />
        <input name="classID" type="hidden" value="<?php echo $_GET['classID']; ?>" />
        <input type="hidden" name="MM_insert" value="form" />
        <input type="hidden" name="dateAdded" value="<?php echo $today ?>" />
        <input type="hidden" name="MM_insert" value="form1" />
      </form>
    </div>
    <div class="twd_column twd_two twd_breakOnTablet">
      <h2 style="padding-bottom:0">Current Members <a href="javascript:void();" onclick="window.open('members-add.php','Add Member','height=500,width=350');return false;"><img src="images/addPerson.png" width="20" height="20" alt="add a new member" /></a></h2>
      <?php 
	  if($row_currentUser['enableMemberships']==0) { 
  echo '<p>Memberships are not enabled on your account. Please <a href="settings.php">update your studio settings</a> to enable memberships!</p>'; 
	  } else {
  if($totalRows_currentMembers==0) { 
  echo '<p>No members found.</p>'; 
  } 
  else { 
  echo '<p class="twd_hideOnDesktop"><a class="button" id="expandMembers">Expand member list</a></p>';
 ?>
      <div id="memberList" class="twd_showOnDesktop">
        <p>Memberships are not valid unless they are listed below. Click on any member name to add them to the attendance sheet!</p>
        <table width="100%" border="0" cellpadding="5" cellspacing="0">
          <thead>
            <tr>
              <th><strong>name</strong></th>
              <th><strong>end date</strong></th>
            </tr>
          </thead>
          <?php do { ?>
          <tr>
            <td><a href="?action=addMember&amp;instructorID=<?php echo $instructorID; ?>&amp;studentID=<?php echo $row_currentMembers['studentID']; ?>&amp;classID=<?php echo $_GET['classID']; ?>&amp;dateAdded=<?php echo $_GET['dateAdded']; ?>"><?php echo $row_currentMembers['lastName']; ?>, <?php echo $row_currentMembers['firstName']; ?></a></td>
            <td><?php echo date( 'm/d/Y', strtotime($row_currentMembers['endDate'])); ?></td>
          </tr>
          <?php } while ($row_currentMembers = mysql_fetch_assoc($currentMembers)); } } ?>
        </table>
      </div>
    </div>
  </div>
</div>
<hr style="margin:20px 0 20px 0" />
<div class="twd_container" style="padding-left:1%">
  <h2>Online Attendance Sheet</h2>
  <div id="attendanceSheetList"></div>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript" src="https://code.jquery.com/ui/1.10.1/jquery-ui.min.js"></script> 
<script type="text/javascript">
$(function() {	
	//autocomplete
	$(".auto").autocomplete({
		source: "search.php",
		minLength: 1, //search after 1 character
      select: function(event,ui){
		  $("#studentID").attr("value",ui.item.studentID);
      }
	});				
});
//members accordion for mobile & tablet
$("#expandMembers").click(function() {
	$("#memberList").slideDown('slow');
	$("#expandMembers").hide();
});
//on instructor drop down change, reload page/remember the instructor that was selected
$(function(){
  $("#instructorID").change(function(){
	var url = window.location.search;
	//url = url.replace("?", ''); // remove the ?
    window.location = url + '&instructorID='+ this.value;
  });
});
$(document).ready(function(){
	$("#attendanceSheetList").load('attendance-sheet-list.php?today=<?php echo $today?>&classID=<?php echo $_GET['classID']?>');
});
function deleteRecord(attendanceID,attendanceType,studentID){
	jQuery.ajax({
	 type: "POST",
	 url: "attendance-sheet-delete.php",
	 data: 'attendanceID='+attendanceID+'&attendanceType='+attendanceType+'&studentID='+studentID,
	 cache: false,
	 success: function(response)
	 {
	   $("#attendanceSheetList").load('attendance-sheet-list.php?today=<?php echo $today?>&classID=<?php echo $_GET['classID']?>');
	 }
   });
}
function checkin(attendanceID){
	jQuery.ajax({
	 type: "POST",
	 url: "attendance-sheet-checkin.php",
	 data: 'attendanceID='+attendanceID,
	 cache: false,
	 success: function(response)
	 {
	   $("#attendanceSheetList").load('attendance-sheet-list.php?today=<?php echo $today?>&classID=<?php echo $_GET['classID']?>');
	 }
   });
}
</script>
</body>
</html>
<?php
mysql_free_result($classes);

mysql_free_result($currentMembers);

mysql_free_result($instructors);
?>