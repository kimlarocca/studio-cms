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
<title><?php echo $row_currentUser['studioName']; ?> | Resources &amp; Widgets</title>
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Resources &amp; Widgets</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
  <h2 class="twd_centered">Social Media Marketing</h2>
  
    <p class="twd_centered twd_margin40"><a class="button" href="https://www.facebook.com/dialog/pagetab?app_id=1006785049344497&next=https://my.slateit.com/student-login-universal.php" target="_blank">Add A "Reserve Now" Tab To Your Facebook Page</a></p>
  <h2 class="twd_centered">Slate It Widgets</h2>
  
  <h3 class="twd_centered twd_margin20">Your Class Schedule (Monthly Calendar View)</h3>
  <p class="twd_centered twd_margin20">Copy and paste the following iframe code if you want to add your weekly class schedule to your website:</p>
  <p>
    <textarea class="twd_centered twd_margin20" style="width:75%" rows="5"><iframe src="https://my.slateit.com/iframe-calendar.php?studioID=<?php echo $row_currentUser['studioID']; ?>" frameborder="0" style="overflow: hidden; height: 100%; width: 100%; position: absolute;" height="100%" width="100%"></iframe>
    </textarea>
  </p>
  <p class="twd_centered twd_margin20">Preview this page: <a target="_blank" href="https://my.slateit.com/iframe-calendar.php?studioID=<?php echo $row_currentUser['studioID']; ?>">https://my.slateit.com/iframe-calendar.php?studioID=<?php echo $row_currentUser['studioID']; ?></a></p>
  
  <hr class="hr2 twd_margin20" />
  
  <h3 class="twd_centered twd_margin20">Your Class Schedule (Weekly View)</h3>
  <p class="twd_centered twd_margin20">Copy and paste the following iframe code if you want to add your weekly class schedule to your website:</p>
  <p>
    <textarea class="twd_centered twd_margin20" style="width:75%" rows="5"><iframe src="https://my.slateit.com/iframe-scheduleWeekly.php?studioID=<?php echo $row_currentUser['studioID']; ?>" frameborder="0" style="overflow: hidden; height: 100%; width: 100%; position: absolute;" height="100%" width="100%"></iframe>
    </textarea>
  </p>
  <p class="twd_centered twd_margin20">Preview this page: <a target="_blank" href="https://my.slateit.com/iframe-scheduleWeekly.php?studioID=<?php echo $row_currentUser['studioID']; ?>">https://my.slateit.com/iframe-scheduleWeekly.php?studioID=<?php echo $row_currentUser['studioID']; ?></a></p>
  
  <hr class="hr2 twd_margin20" />
  
  <h3 class="twd_centered twd_margin20">Today's Classes</h3>
  <p class="twd_centered twd_margin20">Copy and paste the following iframe code if you want to add "Today's Classes" to your website! This automatically updates each day.</p>
  <textarea class="twd_centered twd_margin20" style="width:75%" rows="5"><iframe src="https://my.slateit.com/iframe-todaysClasses.php?studioID=<?php echo $row_currentUser['studioID']; ?>" frameborder="0" style="overflow: hidden; height: 100%; width: 100%; position: absolute;" height="100%" width="100%"></iframe></textarea>
  <p class="twd_centered twd_margin20">Preview this page: <a target="_blank" href="https://my.slateit.com/iframe-todaysClasses.php?studioID=<?php echo $row_currentUser['studioID']; ?>">https://my.slateit.com/iframe-scheduleWeekly.php?studioID=<?php echo $row_currentUser['studioID']; ?></a></p>
  
  <hr class="hr2 twd_margin20" />
  
  <h3 class="twd_centered twd_margin20">Instructors</h3>
  <p class="twd_centered twd_margin20">Copy and paste the following iframe code if you want to add your instructors' photos &amp; bios to your website:</p>
  <p>
    <textarea class="twd_centered twd_margin20" style="width:75%" rows="5"><iframe src="https://my.slateit.com/iframe-instructors.php?studioID=<?php echo $row_currentUser['studioID']; ?>" frameborder="0" style="overflow: hidden; height: 100%; width: 100%; position: absolute;" height="100%" width="100%"></iframe>
    </textarea>
  </p>
  <p class="twd_centered twd_margin20">Preview this page: <a target="_blank" href="https://my.slateit.com/iframe-instructors.php?studioID=<?php echo $row_currentUser['studioID']; ?>">https://my.slateit.com/iframe-instructors.php?studioID=<?php echo $row_currentUser['studioID']; ?></a></p>
  <hr class="hr2 twd_margin20" />
  
  <h3 class="twd_centered twd_margin20">Upcoming Events</h3>
  <p class="twd_centered twd_margin20">Copy and paste the following iframe code if you want to add your weekly class schedule to your website:</p>
  <p>
    <textarea class="twd_centered twd_margin20" style="width:75%" rows="5"><iframe src="https://my.slateit.com/iframe-events.php?studioID=<?php echo $row_currentUser['studioID']; ?>" frameborder="0" style="overflow: hidden; height: 100%; width: 100%; position: absolute;" height="100%" width="100%"></iframe>
    </textarea>
  </p>
  <p class="twd_centered twd_margin20">Preview this page: <a target="_blank" href="https://my.slateit.com/iframe-events.php?studioID=<?php echo $row_currentUser['studioID']; ?>">https://my.slateit.com/iframe-events.php?studioID=<?php echo $row_currentUser['studioID']; ?></a></p>
  
  <hr class="hr2 twd_margin20" />
  
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($currentUser);
?>