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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ($_POST["entryDate"]=="") {
$messageText = "Please select a date! ";	
}
if (!is_numeric($_POST['entryHours'])) {
$messageText = $messageText."Hours must be a valid number! ";	
	
}
if (is_numeric($_POST['entryHours']) && $_POST["entryDate"]!="" && (isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$messageText = "Your entry has been added.";
  $insertSQL = sprintf("INSERT INTO timeSheets (instructorID, entryDate, entryHours, lastModifiedBy) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['instructorID'], "int"),
                       GetSQLValueString($_POST['entryDate'], "date"),
                       GetSQLValueString($_POST['entryHours'], "double"),
                       GetSQLValueString($_POST['lastModifiedBy'], "text"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());
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
$query_timesheet = "SELECT * FROM timeSheets,instructors WHERE timeSheets.instructorID=instructors.instructorID ORDER BY timeSheets.entryDate DESC";
$timesheet = mysql_query($query_timesheet, $wotg) or die(mysql_error());
$row_timesheet = mysql_fetch_assoc($timesheet);
$totalRows_timesheet = mysql_num_rows($timesheet);

$studioID = $row_currentUser['studioID'];
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
<title><?php echo $row_currentUser['studioName']; ?> | Timesheets Admin</title>
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Timesheets Admin</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">

<h2 class="twd_centered" >Timesheet Entry History</h2>
<table border="0" align="center" cellpadding="3" cellspacing="0">
  <tr>
    <td><strong>Entry Date</strong></td>
    <td><strong>Name</strong></td>
    <td><strong>Hours</strong></td>
    <td colspan="2"><strong>Last Modified By</strong></td>
    <td>&nbsp;</td>
  </tr>
  <?php do { 
$phpdate = strtotime( $row_timesheet['dateAdded'] );
$mysqldate = date( 'm/d/Y H:ia', $phpdate );
  ?>
    <tr>
      <td><?php echo date('m/d/Y', strtotime($row_timesheet['entryDate'])); ?></td>
      <td><?php echo $row_timesheet['lastName']; ?>, <?php echo $row_timesheet['firstName']; ?></td>
      <td><?php echo $row_timesheet['entryHours']; ?></td>
      <td><?php echo $row_timesheet['lastModifiedBy']; ?></td>
      <td><em><?php echo $mysqldate; ?></em></td>
      <td><a class="tooltip" title="update this record" href="timesheets-update.php?sheetID=<?php echo $row_timesheets['sheetID']; ?>"><img src="images/edit.png" alt="" width="20" height="20"/></a> <a class="tooltip" title="delete this record" href="timesheets-delete.php?sheetID=<?php echo $row_timesheets['sheetID']; ?>"><img src="images/delete.png" width="20" height="20" /></a></td>
    </tr>
    <?php } while ($row_timesheet = mysql_fetch_assoc($timesheet)); ?>
</table>
</div>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript" src="datePicker/picker.js"></script> 
<script>
/**
 * pick a date
 */
$('#entryDate').pickadate({
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
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($timesheet);
?>