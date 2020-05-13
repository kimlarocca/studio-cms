<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
if (!isset($_SESSION)) {
  session_start();
}
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
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

$maxRows_currentUser = 10;
$pageNum_currentUser = 0;
if (isset($_GET['pageNum_currentUser'])) {
  $pageNum_currentUser = $_GET['pageNum_currentUser'];
}
$startRow_currentUser = $pageNum_currentUser * $maxRows_currentUser;

$colname_currentUser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_currentUser = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_currentUser = sprintf("SELECT * FROM instructors,studios WHERE instructors.studioID=studios.studioID AND instructors.username = %s", GetSQLValueString($colname_currentUser, "text"));
$query_limit_currentUser = sprintf("%s LIMIT %d, %d", $query_currentUser, $startRow_currentUser, $maxRows_currentUser);
$currentUser = mysql_query($query_limit_currentUser, $wotg) or die(mysql_error());
$row_currentUser = mysql_fetch_assoc($currentUser);

if (isset($_GET['totalRows_currentUser'])) {
  $totalRows_currentUser = $_GET['totalRows_currentUser'];
} else {
  $all_currentUser = mysql_query($query_currentUser);
  $totalRows_currentUser = mysql_num_rows($all_currentUser);
}
$totalPages_currentUser = ceil($totalRows_currentUser/$maxRows_currentUser)-1;

$studioID = $row_currentUser['studioID'];

//update record

$updaterecord = "UPDATE studios SET timesheetPayPeriod='".$_GET['timesheetPayPeriod']."' AND timesheetStartDate='".$_GET['timesheetStartDate']."' WHERE studioID = '".$studioID."'";
mysql_select_db($database_wotg, $wotg);
mysql_query($updaterecord, $wotg) or die(mysql_error());
/*
header("Location: timesheets-admin.php?action=saved");
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="styles.css"/>
<title><?php echo $row_currentUser['studioName']; ?> | Timesheets</title>
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Timesheets</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
  <p class="twd_centered">saving changes...</p>
  <table border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td>timesheetPayPeriod</td>
      <td>timesheetStartDate</td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo $row_currentUser['timesheetPayPeriod']; ?></td>
        <td><?php echo $row_currentUser['timesheetStartDate']; ?></td>
      </tr>
      <?php } while ($row_currentUser = mysql_fetch_assoc($currentUser)); ?>
  </table>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($currentUser);
?>