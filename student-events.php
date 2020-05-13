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

mysql_select_db($database_wotg, $wotg);
$query_events = "SELECT * FROM events WHERE eventDate >= CURRENT_DATE() AND studioID=".$row_student['studioID'];
$events = mysql_query($query_events, $wotg) or die(mysql_error());
$row_events = mysql_fetch_assoc($events);
$totalRows_events = mysql_num_rows($events);

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
<title><?php echo $row_student['studioName']; ?> | Upcoming Events</title>
<style>
.sRow {
	margin: 0 0 60px 0;
	width: 100%;
	clear:both;
}
.sRow p {
	padding:0 10px 10px 10px;
	margin:0;
}
.sColumn1 {
	width: 200 px;
	margin-left: 20px;
	padding-bottom: 10px;
	float: left
}
.sColumn2 {
	margin-left: 240px;
	text-align: left;
}
@media screen and (max-width: 767px) {
.sColumn1 {
	width: 100%;
	text-align:center;
	clear:both;
}
.sColumn2 {
	width: 100%;
	clear:both;
	text-align: center;
	margin-left: 0;
}
}
.sRow form {
background-color: #fff;
padding: 0;
border-bottom: none;
}
.button {
	color: <?php echo $row_student['color'];?>!important;
	background-color:#fff;
	border: 1px solid <?php echo $row_student['color'];?>;
	border-radius:3px;
	padding:5px;
	text-transform:uppercase;
	font-weight:bold;
	display:inline-block;
	text-align:center;
	min-width:125px;
	cursor:pointer;
	transition: all 0.25s linear;
	-webkit-transition: all 0.25s linear;
	-moz-transition: all 0.25s linear;
	-o-transition: all 0.25s linear;
	-ms-transition: all 0.25s linear;
}
.button:hover {
	color: #fff!important;
	background-color:<?php echo $row_student['color'];?>;
}
</style>
</head>

<body>
<?php include("student-header.php"); ?>
<h1 class="studentH1 twd_centered"><?php echo $row_student['studioName']; ?> | Upcoming Events</h1>
<?php 
//get logo if it exists
if($row_student['logoURL']!=''){ 
?>
<div class="twd_centered twd_margin20" style="padding-top:20px; clear:both"><img src="uploads/<?php echo $row_student['logoURL']; ?>" /></div>
<?php 
}
?>
<div class="twd_container">
  <?php do { ?>
  <div class="sRow">
  <h2><?php echo $row_events['eventName']; ?></h2>
    <?php
	$newDate = date("m/d/Y", strtotime($row_events['eventDate']));
	$newStartTime = date('g:i A', strtotime($row_events['eventStartTime']));
	$newEndTime = date('g:i A', strtotime($row_events['eventEndTime']));
	if(!isset($row_events['eventEndTime'])) $newEndTime='';
	?>
    <div class="sColumn1">
      <?php if($row_events['thumbnail']!=''){ ?>
      <img height="200" width="200" src="uploads/<?php echo $row_events['thumbnail']; ?>" />
      <?php } else { ?>
      <img height="200" width="200" src="uploads/unavailable.gif" />
      <?php } ?>
    </div>
    <div class="sColumn2"> 
      <strong><?php echo date("l", $newDate)." ".$newDate.", ".$newStartTime." - ".$newEndTime; ?></strong>
      <br /><br />
      <?php 
	  
	  if($row_events['eventFee']!=''){ 
	    echo "Cost: $".number_format($row_events['eventFee'], 2)."<br><br>";
      }
	  echo $row_events['description']; ?>
      <?php 
	  if($row_events['requireRegistration']==1){ 
	  ?>
      <br /><br /><a class="button" href="student-register-event.php?eventID=<?php echo $row_events['eventID']; ?>">register now</a>
      <?php
      }
	  if($row_events['paymentCode']!=''){ 
	    echo "<br><br>".$row_events['paymentCode'];
      } 
	  ?>
    </div>
  </div>
  <?php } while ($row_events = mysql_fetch_assoc($events)); ?>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($student);

mysql_free_result($events);
?>
