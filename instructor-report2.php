<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
?>
<?php require_once('Connections/wotg.php'); ?>
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

$MM_restrictGoTo = "index.php?action=failed";
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

mysql_select_db($database_wotg, $wotg);
$query_instructors = "SELECT * FROM instructors WHERE studioID = ".$row_currentUser['studioID']." AND active = 1 ORDER BY lastName ASC";
$instructors = mysql_query($query_instructors, $wotg) or die(mysql_error());
$row_instructors = mysql_fetch_assoc($instructors);
$totalRows_instructors = mysql_num_rows($instructors);

mysql_select_db($database_wotg, $wotg);
//$query_attendance = sprintf("SELECT attendance.attendanceID,attendance.studentID,attendance.classID,attendance.instructorID,attendance.dateAdded,attendance.attendanceType,attendance.addedBy,attendance.dropInName,attendance.dropInEmail,attendance.paymentType,students.studentID,classes.classID,classes.name,classes.startTime,classes.classFee,classes.prepaidFee,students.firstName,students.lastName FROM attendance,classes,students WHERE attendance.instructorID = ".$_GET['instructorID']." AND attendance.studentID=students.studentID AND attendance.classID = classes.classID AND DATE(attendance.dateAdded) BETWEEN '".$_GET['startDate']."' AND '".$_GET['endDate']."' ORDER BY attendance.dateAdded, attendance.classID");
$query_attendance = sprintf("SELECT attendance.attendanceID,attendance.studentID,attendance.classID,attendance.instructorID,attendance.dateAdded,attendance.attendanceType,attendance.addedBy,attendance.dropInName,attendance.dropInEmail,attendance.paymentType,students.studentID,classes.classID,classes.name,classes.startTime,classes.classFee,classes.prepaidFee,students.firstName,students.lastName, instructors.lastName AS iLastName, instructors.firstName AS iFirstName FROM attendance,classes,students, instructors WHERE instructors.instructorID=attendance.instructorID AND attendance.studentID=students.studentID AND attendance.classID = classes.classID AND DATE(attendance.dateAdded) BETWEEN '".$_GET['startDate']."' AND '".$_GET['endDate']."' ORDER BY attendance.instructorID, attendance.dateAdded, attendance.classID");
$attendance = mysql_query($query_attendance, $wotg) or die(mysql_error());
$row_attendance = mysql_fetch_assoc($attendance);
$totalRows_attendance = mysql_num_rows($attendance);
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
<title><?php echo $row_currentUser['studioName']; ?> | <?php echo $row_currentUser['firstName']; ?>'s Attendance Report</title>
</head>
<body>
<?php include("header.php"); ?>

<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | All Instructors Attendance Report</h1>

<?php include("navigation.php"); ?>
<div class="twd_container">

    <?php do { 
	//go to first attendance record
	mysql_data_seek($attendance, 0);
	?>
  <h2 style="padding:20px 0 20px 0" class="twd_centered"><?php echo $row_instructors['lastName']; ?>, <?php echo $row_instructors['firstName']; ?></h2>
      
  <table border="0" cellpadding="10" cellspacing="0" align="center" class="twd_margin20">
    <tr>
      <td>&nbsp;</td>
      <td><strong>Date</strong></td>
      <td><strong>Time</strong></td>
      <td><strong>Class</strong></td>
      <td><strong>Student</strong></td>
      <td><strong>Type</strong></td>
      <td><strong>Payment</strong></td>
      <td>&nbsp;</td>
    </tr>
    <?php 
	$totalStudents = 0;
	$totalRevenue = 0;
	$totalNoShows = 0;
	$currentClass = $row_attendance['classID'];
	$currentDate = $row_attendance['dateAdded'];
	$totalClasses = 0;
	if ($totalRows_attendance == 0) {
		$totalClasses = 0;
	} else {
	do { 
	  //check instructorID
	  if($row_attendance['instructorID']==$row_instructors['instructorID']){
		  $totalStudents = $totalStudents+1;
	  //check if class has changed
	  if ($currentDate == $row_attendance['dateAdded'] && $currentClass != $row_attendance['classID']){
		  $totalClasses = $totalClasses+1;
		  $currentClass = $row_attendance['classID'];
		  $currentDate = $row_attendance['dateAdded'];
	  } 
	  else if ($currentDate != $row_attendance['dateAdded']){
		  $totalClasses = $totalClasses+1;
		  $currentDate = $row_attendance['dateAdded'];
		  $currentClass = $row_attendance['classID'];
	  } 
	?>
      <tr>
        <td><?php echo $totalClasses; ?> / <?php echo $totalStudents; ?></td>
        <td><?php 
		$newDate = date("m/d/Y", strtotime($row_attendance['dateAdded']));
		$newTime = date('g:i', strtotime($row_attendance['startTime']));
		echo $newDate; 
		?></td>
        <td><?php echo $newTime; ?></td>
        <td><?php echo $row_attendance['name']; ?></td>
        
        <?php
		//count no shows
		if ($row_attendance['firstName'] == 'No' && $row_attendance['lastName'] == 'One') $totalNoShows = $totalNoShows+1;
		?>
        
        <td><?php echo $row_attendance['firstName']; ?> <?php echo $row_attendance['lastName']; ?></td>
        <td><?php echo $row_attendance['attendanceType']; ?></td>
        <td><?php echo $row_attendance['paymentType']; ?></td>
        <td>
		<?php 
		
		if ($row_attendance['attendanceType'] == 'Drop In') {
			echo '$'.$row_attendance['classFee']; 
			$totalRevenue = $totalRevenue+$row_attendance['classFee'];
		}
		if ($row_attendance['attendanceType'] == 'Pre Paid') {
			echo '$'.$row_attendance['prepaidFee']; 
			$totalRevenue = $totalRevenue+$row_attendance['prepaidFee'];
		}
		?>
        </td>
      </tr>
      <?php 
	  } }
	  while ($row_attendance = mysql_fetch_assoc($attendance)); 
	  }
	  //calculate totals
	  $averageStudents = 0;
	  if($totalClasses!=0) $averageStudents = $totalStudents/$totalClasses;
	  $hourlyFee = 35;
	  if ($averageStudents < 5) $hourlyFee = 25;
	  if ($averageStudents >= 8) $hourlyFee = 45;
	  $instructorFee = $hourlyFee*$totalClasses;
	  ?>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td><strong>Total:</strong></td>
        <td><strong>$<?php echo $totalRevenue ?></strong></td>
      </tr>
  </table>
  <?php if ($totalStudents != 0){ ?>
  <h3 style="padding:20px 0 20px 0" class="twd_centered">Summary for <?php echo $row_instructors['lastName']; ?>, <?php echo $row_instructors['firstName']; ?></h3>
  <table width="0" border="0" cellspacing="0" cellpadding="10" align="center" class="twd_margin20">
  <tr>
    <td>Number of Students Taught:</td>
    <td><?php echo $totalStudents-$totalNoShows ?></td>
  </tr>
  <tr>
    <td>Number of Classes Taught:</td>
    <td><?php echo $totalClasses ?></td>
  </tr>
  <tr>
    <td>Average Students per Class:</td>
    <td><?php echo $averageStudents ?></td>
  </tr>
  <!--
  <tr>
    <td>Amount Paid per Class:</td>
    <td>$<?php echo $hourlyFee ?> *</td>
  </tr>
  <tr>
    <td><strong>Instructor's Fee:</strong></td>
    <td><strong>$<?php echo $instructorFee ?></strong></td>
  </tr>
  -->
</table><hr class="hr2" />
<!--<p><em>* $25/class if average is less than 5, $35/class if average is 5 - 7, $45/class if average is 8 or more</em></p>-->
<?php 
} else echo '<p class="twd_centered twd_margin20">This instructor had no students during the selected time frame!</p><hr class="hr2" />'; 
}
while ($row_instructors = mysql_fetch_assoc($instructors)); ?>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($currentUser);
mysql_free_result($attendance);
mysql_free_result($instructors);
?>