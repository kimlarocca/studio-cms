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

date_default_timezone_set($row_student['studioTimezone']);
$today = $expiry_date = date("Y-m-d", strtotime("now"));
//get day of the week selected
$month = date('m', strtotime($_GET['datePicked']));
$day = date('d', strtotime($_GET['datePicked']));
$year = date('Y', strtotime($_GET['datePicked']));
$dateArray=getdate(mktime(0,0,0,$month,$day,$year));
$dayPicked = $dateArray[weekday];

mysql_select_db($database_wotg, $wotg);
$query_classes = "SELECT * FROM classes WHERE studioID = ".$row_student['studioID']." AND classDay = '".$dayPicked."' AND classActive = 1 ORDER BY startTime ASC";
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);

$colname_attendance = "-1";
if (isset($_GET['datePicked'])) {
  $colname_attendance = $_GET['datePicked'];
}
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
<title><?php echo $row_student['studioName']; ?> | My Account</title>
</head>

<body>
<?php include("student-header.php"); ?>
<h1 class="studentH1 twd_centered"><?php echo $row_student['studioName']; ?> | Register Online</h1>
<?php 
//get logo if it exists
if($row_student['logoURL']!=''){ 
?>
<div class="twd_centered twd_margin20" style="padding-top:20px; clear:both"><img src="uploads/<?php echo $row_student['logoURL']; ?>" /></div>
<?php 
}
?>
<div class="twd_container">
  <h2 class="twd_centered">Choose a Class</h2>
<?php
 $formattedDate = date("l, m/d/Y", strtotime($_GET['datePicked']));
if ($totalRows_classes==0){
	echo '<p class="red twd_centered">Sorry - we do not have any classes on the date you selected!<br><br>Date Picked: '.$formattedDate.'<br><br></p>';
} else {
if($today > $_GET['datePicked']){
	echo '<p class="red twd_centered">Sorry - you cannot choose a date in the past!<br><br>Date Picked: '.$formattedDate.'<br><br></p>';
}
else {
 ?>
 <h3 class="twd_centered twd_margin20">Date Selected: <?php echo $formattedDate; ?></h3>
 <p class="twd_centered twd_margin20">Click on any class to continue:</p>
  <table border="0" align="center" cellpadding="3" cellspacing="0" class="twd_margin40">
    <?php do { 
	$formattedTime = date("g:i a", strtotime($row_classes['startTime']));
	?>
      <tr>
        <td class="twd_centered"> <a href="student-register-date2.php?datePicked=<?php echo $_GET['datePicked']; ?>&classID=<?php echo $row_classes['classID']; ?>"><?php if($row_classes['thumbnail']!=''){ ?>
        <img height="200" width="200" src="uploads/<?php echo $row_classes['thumbnail']; ?>" />
        <?php } else { ?>
        <img height="200" width="200" src="uploads/unavailable.gif" />
        <?php } ?></a></td>
        <td>
        <p class="twd_centeredOnMobile"><?php echo $formattedTime; ?></p>
        <p class="twd_centeredOnMobile"><strong><a href="student-register-date2.php?datePicked=<?php echo $_GET['datePicked']; ?>&classID=<?php echo $row_classes['classID']; ?>"><?php echo $row_classes['name']; ?></a></strong></p>
        <p class="twd_centeredOnMobile"><?php 
		
		mysql_select_db($database_wotg, $wotg);
		$query_attendance = "SELECT * FROM attendance WHERE dateAdded = '".$_GET['datePicked']."' AND classID=".$row_classes['classID'];
		$attendance = mysql_query($query_attendance, $wotg) or die(mysql_error());
		$row_attendance = mysql_fetch_assoc($attendance);
		$totalRows_attendance = mysql_num_rows($attendance);
		
		if($row_classes['classCapacity']==$totalRows_attendance){
			echo '<span class="red">This class is full!</span>';
		} else {
		echo ($row_classes['classCapacity']-$totalRows_attendance).' spots left!'; 
		}
		?></p></td>
      </tr>
      <?php } while ($row_classes = mysql_fetch_assoc($classes)); ?>
  </table>
 <?php } } ?>
<p class="twd_centered"><a href="student-home.php">&lt;&lt; start over &amp; pick a new date</a></p>
 </div>
</body>
</html>
<?php
mysql_free_result($student);

mysql_free_result($classes);
?>