<?php require_once('Connections/wotg.php'); ?>
<?php
$today = $expiry_date = date("Y-m-d", strtotime("now"));
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

$MM_restrictGoTo = "student-login.php?action=failed";
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

$colname_student = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_student = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_student = sprintf("SELECT * FROM students,studios WHERE emailAddress = %s AND students.studioID=studios.studioID", GetSQLValueString($colname_student, "text"));
$student = mysql_query($query_student, $wotg) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);

$colname_reservation = "-1";
if (isset($_GET['eventAttendanceID'])) {
  $colname_reservation = $_GET['eventAttendanceID'];
}
mysql_select_db($database_wotg, $wotg);
$query_reservation = sprintf("SELECT * FROM eventAttendance,events WHERE eventAttendance.eventAttendanceID = %s AND events.eventID=eventAttendance.eventID", GetSQLValueString($colname_reservation, "int"));
$reservation = mysql_query($query_reservation, $wotg) or die(mysql_error());
$row_reservation = mysql_fetch_assoc($reservation);
$totalRows_reservation = mysql_num_rows($reservation);

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
<title><?php echo $row_student['studioName']; ?> | Cancel Your Reservation</title>
</head>

<body>
<?php include("student-header.php"); ?>
<h1 class="twd_centered studentH1"><?php echo $row_student['studioName']; ?> | Cancel Your Reservation</h1>
<?php 
//get logo if it exists
if($row_student['logoURL']!=''){ 
?>
<div class="twd_centered twd_margin20" style="padding-top:20px; clear:both"><img src="uploads/<?php echo $row_student['logoURL']; ?>" /></div>
<?php 
}
?>
<div class="twd_container">

<?php
$date1 = new DateTime($today);
$date2 = new DateTime($row_reservation['dateAdded']);
$diff = $date2->diff($date1);
$hours = $diff->h;
$hours = $hours + ($diff->days*24);
if ($hours<48) {
	echo '<p class="twd_centered" style="color:red;">Sorry - you must cancel your reservation more than 1 day in advance. Please contact '.$row_student['studioName'].' for more information or if there are special circumstances. Thank you!</p>';
} else {
?>

  <p class="twd_centered twd_margin20"><strong>Are you sure you'd like to cancel this resevation?</strong></p>
  <p class="twd_centered twd_margin20"><em>Name: <?php echo $row_reservation['eventName']; ?></em></p>
  <p class="twd_centered twd_margin20"><em>Date: <?php echo $row_reservation['dateAdded']; ?></em></p>
  <p class="twd_centered">&nbsp;</p>
  <p class="twd_centered"><a href="student-event-cancel2.php?eventAttendanceID=<?php echo $row_reservation['eventAttendanceID']; ?>" class="button">cancel reservation</a></p>
  <?php } ?>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($student);

mysql_free_result($reservation);
?>
