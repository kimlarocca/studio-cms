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

//delete record
if ($_GET['action'] == 'delete') {
    $deleterecords = "DELETE FROM eventAttendance WHERE eventAttendanceID = ".$_GET['eventAttendanceID'];
    mysql_select_db($database_wotg, $wotg);
    mysql_query($deleterecords, $wotg) or die(mysql_error());
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE events SET eventDate=%s, eventStartTime=%s, eventEndTime=%s, eventName=%s, `description`=%s, eventFee=%s, instructorID=%s, paymentCode=%s, eventCapacity=%s, guestInstructor=%s, requireRegistration=%s WHERE eventID=%s",
                       GetSQLValueString($_POST['eventDate'], "date"),
                       GetSQLValueString($_POST['eventStartTime'], "date"),
                       GetSQLValueString($_POST['eventEndTime'], "date"),
                       GetSQLValueString($_POST['eventName'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['eventFee'], "double"),
                       GetSQLValueString($_POST['instructorID'], "int"),
                       GetSQLValueString($_POST['paymentCode'], "text"),
                       GetSQLValueString($_POST['eventCapacity'], "int"),
                       GetSQLValueString($_POST['guestInstructor'], "text"),
                       GetSQLValueString($_POST['requireRegistration'], "int"),
                       GetSQLValueString($_POST['eventID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "events-update.php?action=saved";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
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

$colname_event = "-1";
if (isset($_GET['eventID'])) {
  $colname_event = $_GET['eventID'];
}
mysql_select_db($database_wotg, $wotg);
$query_event = sprintf("SELECT * FROM events WHERE eventID = %s", GetSQLValueString($colname_event, "int"));
$event = mysql_query($query_event, $wotg) or die(mysql_error());
$row_event = mysql_fetch_assoc($event);
$totalRows_event = mysql_num_rows($event);

mysql_select_db($database_wotg, $wotg);
$query_instructors = "SELECT instructorID,lastName,firstName,studioID,active,securityLevel FROM instructors WHERE securityLevel = 'Instructor' AND studioID = ".$row_currentUser['studioID']." AND active = 1 ORDER BY lastName";
$instructors = mysql_query($query_instructors, $wotg) or die(mysql_error());
$row_instructors = mysql_fetch_assoc($instructors);
$totalRows_instructors = mysql_num_rows($instructors);

$colname_eventAttendance = "-1";
if (isset($_GET['eventID'])) {
  $colname_eventAttendance = $_GET['eventID'];
}
mysql_select_db($database_wotg, $wotg);
$query_eventAttendance = sprintf("SELECT * FROM eventAttendance,events,students WHERE eventAttendance.eventID = %s AND eventAttendance.eventID=events.eventID AND eventAttendance.studentID=students.studentID ORDER BY students.lastName, students.firstName", GetSQLValueString($colname_eventAttendance, "int"));
$eventAttendance = mysql_query($query_eventAttendance, $wotg) or die(mysql_error());
$row_eventAttendance = mysql_fetch_assoc($eventAttendance);
$totalRows_eventAttendance = mysql_num_rows($eventAttendance);
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
<title><?php echo $row_currentUser['studioName']; ?> | Update Event</title>
<link rel="stylesheet" type="text/css" href="dropzone.css"/>
<script src="dropzone.js"></script>
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Events</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
<h2 class="twd_centered"> <?php echo $row_event['eventName']; ?>: Registrants</h2>
<p class="twd_centered twd_margin20"><a href="events-addRegistrant.php?eventID=<?php echo $_GET['eventID']; ?>" class="button">manually add a registrant</a></p>
<?php if ($totalRows_eventAttendance == 0) { echo '<p class="twd_centered twd_margin20">There are no registrations yet for this event/session!</p>'; } else { ?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="twd_margin20">
  <tr>
    <td><strong>Name</strong></td>
    <td>&nbsp;</td>
    </tr>
  <?php do { ?>
    <tr>
      <td><?php echo $row_eventAttendance['lastName']; ?>, <?php echo $row_eventAttendance['firstName']; ?></td>
      <td><a class="tooltip" title="delete this registration" href="events-update.php?action=delete&eventID=<?php echo $_GET['eventID']; ?>&eventAttendanceID=<?php echo $row_eventAttendance['eventAttendanceID']; ?>"><img src="images/delete.png" width="20" height="20" /></a></td>
      </tr>
    <?php } while ($row_eventAttendance = mysql_fetch_assoc($eventAttendance)); ?>
</table>
<?php } ?>
<h2 class="twd_centered">Update This Event</h2>
<?php
//check if changes were saved
if ($_GET['action'] == 'saved') print '<p class="twd_centered twd_margin20" style="color:red">Your changes have been saved!</p>'; 
?><h3 class="twd_centered twd_margin20">event photo</h3>
<p class="twd_centered twd_margin20">For best results, images should be a minimum of 200px wide by 200px tall and be in a square format. Max file upload size is 2MB.</p>
<div style="margin:auto; width:420px;">
<div class="image_upload_div" style="width:200px; height:200px; margin:auto; float:left">
    <form action="upload-event.php" class="dropzone" id="myAwesomeForm">
    	<input name="file_name" type="hidden" value="<?php echo $now; ?>" />
      <input name="eventID" type="hidden" value="<?php echo $row_event['eventID']; ?>" />
      <input name="studioID" type="hidden" value="<?php echo $row_event['studioID']; ?>" />
    </form></div>
<div style="width:200px; height:200px; margin:auto; padding:10px 0 0 20px; float:left">
 <?php if($row_event['thumbnail']!=''){ ?>
        <img height="200" width="200" src="uploads/<?php echo $row_event['thumbnail']; ?>" />
        <?php } else { ?>
        <img height="200" width="200" src="uploads/unavailable.gif" />
        <?php } ?>
    </div>
  </div>
  <div class="twd_clearfloat" style="padding-top:20px"></div> 
  <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
    <table align="center">
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Name *</td>
        <td><input type="text" name="eventName" required="required" value="<?php echo htmlentities($row_event['eventName'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Date *</td>
        <td><input type="text" name="eventDate" id="eventDate" placeholder="<?php echo htmlentities($row_event['eventDate'], ENT_COMPAT, 'UTF-8'); ?>" value="<?php echo htmlentities($row_event['eventDate'], ENT_COMPAT, 'UTF-8'); ?>" data-value="<?php echo htmlentities($row_event['eventDate'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Start Time *</td>
        <td><select name="eventStartTime">
          <option value="5:00" <?php if (!(strcmp("05:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:00 AM</option>
          <option value="5:05" <?php if (!(strcmp("05:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:05 AM</option>
          <option value="5:10" <?php if (!(strcmp("05:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:10 AM</option>
          <option value="5:15" <?php if (!(strcmp("05:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:15 AM</option>
          <option value="5:20" <?php if (!(strcmp("05:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:20 AM</option>
          <option value="5:25" <?php if (!(strcmp("05:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:25 AM</option>
          <option value="5:30" <?php if (!(strcmp("05:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:30 AM</option>
          <option value="5:35" <?php if (!(strcmp("05:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:35 AM</option>
          <option value="5:40" <?php if (!(strcmp("05:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:40 AM</option>
          <option value="5:45" <?php if (!(strcmp("05:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:45 AM</option>
          <option value="5:50" <?php if (!(strcmp("05:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:50 AM</option>
          <option value="5:55" <?php if (!(strcmp("05:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:55 AM</option>
          <option value="6:00" <?php if (!(strcmp("06:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:00 AM</option>
          <option value="6:05" <?php if (!(strcmp("06:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:05 AM</option>
          <option value="6:10" <?php if (!(strcmp("06:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:10 AM</option>
          <option value="6:15" <?php if (!(strcmp("06:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:15 AM</option>
          <option value="6:20" <?php if (!(strcmp("06:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:20 AM</option>
          <option value="6:25" <?php if (!(strcmp("06:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:25 AM</option>
          <option value="6:30" <?php if (!(strcmp("06:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:30 AM</option>
          <option value="6:35" <?php if (!(strcmp("06:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:35 AM</option>
          <option value="6:40" <?php if (!(strcmp("06:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:40 AM</option>
          <option value="6:45" <?php if (!(strcmp("06:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:45 AM</option>
          <option value="6:50" <?php if (!(strcmp("06:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:50 AM</option>
          <option value="6:55" <?php if (!(strcmp("06:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:55 AM</option>
          <option value="7:00" <?php if (!(strcmp("07:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:00 AM</option>
          <option value="7:05" <?php if (!(strcmp("07:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:05 AM</option>
          <option value="7:10" <?php if (!(strcmp("07:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:10 AM</option>
          <option value="7:15" <?php if (!(strcmp("07:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:15 AM</option>
          <option value="7:20" <?php if (!(strcmp("07:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:20 AM</option>
          <option value="7:25" <?php if (!(strcmp("07:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:25 AM</option>
          <option value="7:30" <?php if (!(strcmp("07:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:30 AM</option>
          <option value="7:35" <?php if (!(strcmp("07:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:35 AM</option>
          <option value="7:40" <?php if (!(strcmp("07:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:40 AM</option>
          <option value="7:45" <?php if (!(strcmp("07:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:45 AM</option>
          <option value="7:50" <?php if (!(strcmp("07:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:50 AM</option>
          <option value="7:55" <?php if (!(strcmp("07:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:55 AM</option>
          <option value="8:00" <?php if (!(strcmp("08:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:00 AM</option>
          <option value="8:05" <?php if (!(strcmp("08:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:05 AM</option>
          <option value="8:10" <?php if (!(strcmp("08:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:10 AM</option>
          <option value="8:15" <?php if (!(strcmp("08:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:15 AM</option>
          <option value="8:20" <?php if (!(strcmp("08:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:20 AM</option>
          <option value="8:25" <?php if (!(strcmp("08:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:25 AM</option>
          <option value="8:30" <?php if (!(strcmp("08:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:30 AM</option>
          <option value="8:35" <?php if (!(strcmp("08:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:35 AM</option>
          <option value="8:40" <?php if (!(strcmp("08:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:40 AM</option>
          <option value="8:45" <?php if (!(strcmp("08:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:45 AM</option>
          <option value="8:50" <?php if (!(strcmp("08:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:50 AM</option>
          <option value="8:55" <?php if (!(strcmp("08:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:55 AM</option>
          <option value="9:00" <?php if (!(strcmp("09:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:00 AM</option>
          <option value="9:05" <?php if (!(strcmp("09:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:05 AM</option>
          <option value="9:10" <?php if (!(strcmp("09:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:10 AM</option>
          <option value="9:15" <?php if (!(strcmp("09:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:15 AM</option>
          <option value="9:20" <?php if (!(strcmp("09:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:20 AM</option>
          <option value="9:25" <?php if (!(strcmp("09:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:25 AM</option>
          <option value="9:30" <?php if (!(strcmp("09:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:30 AM</option>
          <option value="9:35" <?php if (!(strcmp("09:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:35 AM</option>
          <option value="9:40" <?php if (!(strcmp("09:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:40 AM</option>
          <option value="9:45" <?php if (!(strcmp("09:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:45 AM</option>
          <option value="9:50" <?php if (!(strcmp("09:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:50 AM</option>
          <option value="9:55" <?php if (!(strcmp("09:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:55 AM</option>
          <option value="10:00" <?php if (!(strcmp("10:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:00 AM</option>
          <option value="10:05" <?php if (!(strcmp("10:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:05 AM</option>
          <option value="10:10" <?php if (!(strcmp("10:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:10 AM</option>
          <option value="10:15" <?php if (!(strcmp("10:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:15 AM</option>
          <option value="10:20" <?php if (!(strcmp("10:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:20 AM</option>
          <option value="10:25" <?php if (!(strcmp("10:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:25 AM</option>
          <option value="10:30" <?php if (!(strcmp("10:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:30 AM</option>
          <option value="10:35" <?php if (!(strcmp("10:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:35 AM</option>
          <option value="10:40" <?php if (!(strcmp("10:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:40 AM</option>
          <option value="10:45" <?php if (!(strcmp("10:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:45 AM</option>
          <option value="10:50" <?php if (!(strcmp("10:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:50 AM</option>
          <option value="10:55" <?php if (!(strcmp("10:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:55 AM</option>
          <option value="11:00" <?php if (!(strcmp("11:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:00 AM</option>
          <option value="11:05" <?php if (!(strcmp("11:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:05 AM</option>
          <option value="11:10" <?php if (!(strcmp("11:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:10 AM</option>
          <option value="11:15" <?php if (!(strcmp("11:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:15 AM</option>
          <option value="11:20" <?php if (!(strcmp("11:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:20 AM</option>
          <option value="11:25" <?php if (!(strcmp("11:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:25 AM</option>
          <option value="11:30" <?php if (!(strcmp("11:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:30 AM</option>
          <option value="11:35" <?php if (!(strcmp("11:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:35 AM</option>
          <option value="11:40" <?php if (!(strcmp("11:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:40 AM</option>
          <option value="11:45" <?php if (!(strcmp("11:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:45 AM</option>
          <option value="11:50" <?php if (!(strcmp("11:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:50 AM</option>
          <option value="11:55" <?php if (!(strcmp("11:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:55 AM</option>
          <option value="12:00" <?php if (!(strcmp("12:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>12:00 AM</option>
          <option value="12:05" <?php if (!(strcmp("12:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>12:05 AM</option>
          <option value="12:10" <?php if (!(strcmp("12:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>12:10 AM</option>
          <option value="12:15" <?php if (!(strcmp("12:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>12:15 AM</option>
          <option value="12:20" <?php if (!(strcmp("12:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>12:20 AM</option>
          <option value="12:25" <?php if (!(strcmp("12:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>12:25 AM</option>
          <option value="12:30" <?php if (!(strcmp("12:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>12:30 AM</option>
          <option value="12:35" <?php if (!(strcmp("12:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>12:35 AM</option>
          <option value="12:40" <?php if (!(strcmp("12:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>12:40 AM</option>
          <option value="12:45" <?php if (!(strcmp("12:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>12:45 AM</option>
          <option value="12:50" <?php if (!(strcmp("12:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>12:50 AM</option>
          <option value="12:55" <?php if (!(strcmp("12:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>12:55 AM</option>
          <option value="13:00" <?php if (!(strcmp("13:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>1:00 PM</option>
          <option value="13:05" <?php if (!(strcmp("13:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>1:05 PM</option>
          <option value="13:10" <?php if (!(strcmp("13:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>1:10 PM</option>
          <option value="13:15" <?php if (!(strcmp("13:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>1:15 PM</option>
          <option value="13:20" <?php if (!(strcmp("13:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>1:20 PM</option>
          <option value="13:25" <?php if (!(strcmp("13:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>1:25 PM</option>
          <option value="13:30" <?php if (!(strcmp("13:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>1:30 PM</option>
          <option value="13:35" <?php if (!(strcmp("13:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>1:35 PM</option>
          <option value="13:40" <?php if (!(strcmp("13:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>1:40 PM</option>
          <option value="13:45" <?php if (!(strcmp("13:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>1:45 PM</option>
          <option value="13:50" <?php if (!(strcmp("13:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>1:50 PM</option>
          <option value="13:55" <?php if (!(strcmp("13:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>1:55 PM</option>
          <option value="14:00" <?php if (!(strcmp("14:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>2:00 PM</option>
          <option value="14:05" <?php if (!(strcmp("14:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>2:05 PM</option>
          <option value="14:10" <?php if (!(strcmp("14:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>2:10 PM</option>
          <option value="14:15" <?php if (!(strcmp("14:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>2:15 PM</option>
          <option value="14:20" <?php if (!(strcmp("14:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>2:20 PM</option>
          <option value="14:25" <?php if (!(strcmp("14:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>2:25 PM</option>
          <option value="14:30" <?php if (!(strcmp("14:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>2:30 PM</option>
          <option value="14:35" <?php if (!(strcmp("14:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>2:35 PM</option>
          <option value="14:40" <?php if (!(strcmp("14:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>2:40 PM</option>
          <option value="14:45" <?php if (!(strcmp("14:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>2:45 PM</option>
          <option value="14:50" <?php if (!(strcmp("14:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>2:50 PM</option>
          <option value="14:55" <?php if (!(strcmp("14:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>2:55 PM</option>
          <option value="15:00" <?php if (!(strcmp("15:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>3:00 PM</option>
          <option value="15:05" <?php if (!(strcmp("15:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>3:05 PM</option>
          <option value="15:10" <?php if (!(strcmp("15:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>3:10 PM</option>
          <option value="15:15" <?php if (!(strcmp("15:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>3:15 PM</option>
          <option value="15:20" <?php if (!(strcmp("15:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>3:20 PM</option>
          <option value="15:25" <?php if (!(strcmp("15:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>3:25 PM</option>
          <option value="15:30" <?php if (!(strcmp("15:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>3:30 PM</option>
          <option value="15:35" <?php if (!(strcmp("15:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>3:35 PM</option>
          <option value="15:40" <?php if (!(strcmp("15:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>3:40 PM</option>
          <option value="15:45" <?php if (!(strcmp("15:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>3:45 PM</option>
          <option value="15:50" <?php if (!(strcmp("15:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>3:50 PM</option>
          <option value="15:55" <?php if (!(strcmp("15:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>3:55 PM</option>
          <option value="16:00" <?php if (!(strcmp("16:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>4:00 PM</option>
          <option value="16:05" <?php if (!(strcmp("16:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>4:05 PM</option>
          <option value="16:10" <?php if (!(strcmp("16:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>4:10 PM</option>
          <option value="16:15" <?php if (!(strcmp("16:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>4:15 PM</option>
          <option value="16:20" <?php if (!(strcmp("16:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>4:20 PM</option>
          <option value="16:25" <?php if (!(strcmp("16:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>4:25 PM</option>
          <option value="16:30" <?php if (!(strcmp("16:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>4:30 PM</option>
          <option value="16:35" <?php if (!(strcmp("16:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>4:35 PM</option>
          <option value="16:40" <?php if (!(strcmp("16:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>4:40 PM</option>
          <option value="16:45" <?php if (!(strcmp("16:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>4:45 PM</option>
          <option value="16:50" <?php if (!(strcmp("16:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>4:50 PM</option>
          <option value="16:55" <?php if (!(strcmp("16:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>4:55 PM</option>
          <option value="17:00" <?php if (!(strcmp("17:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:00 PM</option>
          <option value="17:05" <?php if (!(strcmp("17:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:05 PM</option>
          <option value="17:10" <?php if (!(strcmp("17:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:10 PM</option>
          <option value="17:15" <?php if (!(strcmp("17:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:15 PM</option>
          <option value="17:20" <?php if (!(strcmp("17:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:20 PM</option>
          <option value="17:25" <?php if (!(strcmp("17:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:25 PM</option>
          <option value="17:30" <?php if (!(strcmp("17:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:30 PM</option>
          <option value="17:35" <?php if (!(strcmp("17:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:35 PM</option>
          <option value="17:40" <?php if (!(strcmp("17:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:40 PM</option>
          <option value="17:45" <?php if (!(strcmp("17:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:45 PM</option>
          <option value="17:50" <?php if (!(strcmp("17:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:50 PM</option>
          <option value="17:55" <?php if (!(strcmp("17:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>5:55 PM</option>
          <option value="18:00" <?php if (!(strcmp("18:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:00 PM</option>
          <option value="18:05" <?php if (!(strcmp("18:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:05 PM</option>
          <option value="18:10" <?php if (!(strcmp("18:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:10 PM</option>
          <option value="18:15" <?php if (!(strcmp("18:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:15 PM</option>
          <option value="18:20" <?php if (!(strcmp("18:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:20 PM</option>
          <option value="18:25" <?php if (!(strcmp("18:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:25 PM</option>
          <option value="18:30" <?php if (!(strcmp("18:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:30 PM</option>
          <option value="18:35" <?php if (!(strcmp("18:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:35 PM</option>
          <option value="18:40" <?php if (!(strcmp("18:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:40 PM</option>
          <option value="18:45" <?php if (!(strcmp("18:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:45 PM</option>
          <option value="18:50" <?php if (!(strcmp("18:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:50 PM</option>
          <option value="18:55" <?php if (!(strcmp("18:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>6:55 PM</option>
          <option value="19:00" <?php if (!(strcmp("19:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:00 PM</option>
          <option value="19:05" <?php if (!(strcmp("19:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:05 PM</option>
          <option value="19:10" <?php if (!(strcmp("19:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:10 PM</option>
          <option value="19:15" <?php if (!(strcmp("19:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:15 PM</option>
          <option value="19:20" <?php if (!(strcmp("19:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:20 PM</option>
          <option value="19:25" <?php if (!(strcmp("19:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:25 PM</option>
          <option value="19:30" <?php if (!(strcmp("19:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:30 PM</option>
          <option value="19:35" <?php if (!(strcmp("19:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:35 PM</option>
          <option value="19:40" <?php if (!(strcmp("19:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:40 PM</option>
          <option value="19:45" <?php if (!(strcmp("19:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:45 PM</option>
          <option value="19:50" <?php if (!(strcmp("19:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:50 PM</option>
          <option value="19:55" <?php if (!(strcmp("19:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>7:55 PM</option>
          <option value="20:00" <?php if (!(strcmp("20:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:00 PM</option>
          <option value="20:05" <?php if (!(strcmp("20:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:05 PM</option>
          <option value="20:10" <?php if (!(strcmp("20:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:10 PM</option>
          <option value="20:15" <?php if (!(strcmp("20:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:15 PM</option>
          <option value="20:20" <?php if (!(strcmp("20:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:20 PM</option>
          <option value="20:25" <?php if (!(strcmp("20:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:25 PM</option>
          <option value="20:30" <?php if (!(strcmp("20:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:30 PM</option>
          <option value="20:35" <?php if (!(strcmp("20:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:35 PM</option>
          <option value="20:40" <?php if (!(strcmp("20:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:40 PM</option>
          <option value="20:45" <?php if (!(strcmp("20:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:45 PM</option>
          <option value="20:50" <?php if (!(strcmp("20:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:50 PM</option>
          <option value="20:55" <?php if (!(strcmp("20:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>8:55 PM</option>
          <option value="21:00" <?php if (!(strcmp("21:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:00 PM</option>
          <option value="21:05" <?php if (!(strcmp("21:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:05 PM</option>
          <option value="21:10" <?php if (!(strcmp("21:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:10 PM</option>
          <option value="21:15" <?php if (!(strcmp("21:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:15 PM</option>
          <option value="21:20" <?php if (!(strcmp("21:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:20 PM</option>
          <option value="21:25" <?php if (!(strcmp("21:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:25 PM</option>
          <option value="21:30" <?php if (!(strcmp("21:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:30 PM</option>
          <option value="21:35" <?php if (!(strcmp("21:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:35 PM</option>
          <option value="21:40" <?php if (!(strcmp("21:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:40 PM</option>
          <option value="21:45" <?php if (!(strcmp("21:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:45 PM</option>
          <option value="21:50" <?php if (!(strcmp("21:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:50 PM</option>
          <option value="21:55" <?php if (!(strcmp("21:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>9:55 PM</option>
          <option value="22:00" <?php if (!(strcmp("22:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:00 PM</option>
          <option value="22:05" <?php if (!(strcmp("22:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:05 PM</option>
          <option value="22:10" <?php if (!(strcmp("22:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:10 PM</option>
          <option value="22:15" <?php if (!(strcmp("22:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:15 PM</option>
          <option value="22:20" <?php if (!(strcmp("22:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:20 PM</option>
          <option value="22:25" <?php if (!(strcmp("22:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:25 PM</option>
          <option value="22:30" <?php if (!(strcmp("22:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:30 PM</option>
          <option value="22:35" <?php if (!(strcmp("22:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:35 PM</option>
          <option value="22:40" <?php if (!(strcmp("22:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:40 PM</option>
          <option value="22:45" <?php if (!(strcmp("22:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:45 PM</option>
          <option value="22:50" <?php if (!(strcmp("22:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:50 PM</option>
          <option value="22:55" <?php if (!(strcmp("22:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>10:55 PM</option>
          <option value="23:00" <?php if (!(strcmp("23:00:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:00 PM</option>
          <option value="23:05" <?php if (!(strcmp("23:05:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:05 PM</option>
          <option value="23:10" <?php if (!(strcmp("23:10:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:10 PM</option>
          <option value="23:15" <?php if (!(strcmp("23:15:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:15 PM</option>
          <option value="23:20" <?php if (!(strcmp("23:20:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:20 PM</option>
          <option value="23:25" <?php if (!(strcmp("23:25:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:25 PM</option>
          <option value="23:30" <?php if (!(strcmp("23:30:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:30 PM</option>
          <option value="23:35" <?php if (!(strcmp("23:35:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:35 PM</option>
          <option value="23:40" <?php if (!(strcmp("23:40:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:40 PM</option>
          <option value="23:45" <?php if (!(strcmp("23:45:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:45 PM</option>
          <option value="23:50" <?php if (!(strcmp("23:50:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:50 PM</option>
          <option value="23:55" <?php if (!(strcmp("23:55:00", $row_event['eventStartTime']))) {echo "selected=\"selected\"";} ?>>11:55 PM</option>
</select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">End Time</td>
        <td><select name="eventEndTime">
          <option value="" <?php if (!(strcmp("", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>></option>
          <option value="5:00" <?php if (!(strcmp("05:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:00 AM</option>
          <option value="5:05" <?php if (!(strcmp("05:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:05 AM</option>
          <option value="5:10" <?php if (!(strcmp("05:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:10 AM</option>
          <option value="5:15" <?php if (!(strcmp("05:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:15 AM</option>
          <option value="5:20" <?php if (!(strcmp("05:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:20 AM</option>
          <option value="5:25" <?php if (!(strcmp("05:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:25 AM</option>
          <option value="5:30" <?php if (!(strcmp("05:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:30 AM</option>
          <option value="5:35" <?php if (!(strcmp("05:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:35 AM</option>
          <option value="5:40" <?php if (!(strcmp("05:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:40 AM</option>
          <option value="5:45" <?php if (!(strcmp("05:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:45 AM</option>
          <option value="5:50" <?php if (!(strcmp("05:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:50 AM</option>
          <option value="5:55" <?php if (!(strcmp("05:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:55 AM</option>
          <option value="6:00" <?php if (!(strcmp("06:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:00 AM</option>
          <option value="6:05" <?php if (!(strcmp("06:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:05 AM</option>
          <option value="6:10" <?php if (!(strcmp("06:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:10 AM</option>
          <option value="6:15" <?php if (!(strcmp("06:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:15 AM</option>
          <option value="6:20" <?php if (!(strcmp("06:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:20 AM</option>
          <option value="6:25" <?php if (!(strcmp("06:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:25 AM</option>
          <option value="6:30" <?php if (!(strcmp("06:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:30 AM</option>
          <option value="6:35" <?php if (!(strcmp("06:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:35 AM</option>
          <option value="6:40" <?php if (!(strcmp("06:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:40 AM</option>
          <option value="6:45" <?php if (!(strcmp("06:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:45 AM</option>
          <option value="6:50" <?php if (!(strcmp("06:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:50 AM</option>
          <option value="6:55" <?php if (!(strcmp("06:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:55 AM</option>
          <option value="7:00" <?php if (!(strcmp("07:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:00 AM</option>
          <option value="7:05" <?php if (!(strcmp("07:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:05 AM</option>
          <option value="7:10" <?php if (!(strcmp("07:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:10 AM</option>
          <option value="7:15" <?php if (!(strcmp("07:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:15 AM</option>
          <option value="7:20" <?php if (!(strcmp("07:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:20 AM</option>
          <option value="7:25" <?php if (!(strcmp("07:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:25 AM</option>
          <option value="7:30" <?php if (!(strcmp("07:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:30 AM</option>
          <option value="7:35" <?php if (!(strcmp("07:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:35 AM</option>
          <option value="7:40" <?php if (!(strcmp("07:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:40 AM</option>
          <option value="7:45" <?php if (!(strcmp("07:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:45 AM</option>
          <option value="7:50" <?php if (!(strcmp("07:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:50 AM</option>
          <option value="7:55" <?php if (!(strcmp("07:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:55 AM</option>
          <option value="8:00" <?php if (!(strcmp("08:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:00 AM</option>
          <option value="8:05" <?php if (!(strcmp("08:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:05 AM</option>
          <option value="8:10" <?php if (!(strcmp("08:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:10 AM</option>
          <option value="8:15" <?php if (!(strcmp("08:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:15 AM</option>
          <option value="8:20" <?php if (!(strcmp("08:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:20 AM</option>
          <option value="8:25" <?php if (!(strcmp("08:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:25 AM</option>
          <option value="8:30" <?php if (!(strcmp("08:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:30 AM</option>
          <option value="8:35" <?php if (!(strcmp("08:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:35 AM</option>
          <option value="8:40" <?php if (!(strcmp("08:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:40 AM</option>
          <option value="8:45" <?php if (!(strcmp("08:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:45 AM</option>
          <option value="8:50" <?php if (!(strcmp("08:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:50 AM</option>
          <option value="8:55" <?php if (!(strcmp("08:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:55 AM</option>
          <option value="9:00" <?php if (!(strcmp("09:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:00 AM</option>
          <option value="9:05" <?php if (!(strcmp("09:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:05 AM</option>
          <option value="9:10" <?php if (!(strcmp("09:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:10 AM</option>
          <option value="9:15" <?php if (!(strcmp("09:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:15 AM</option>
          <option value="9:20" <?php if (!(strcmp("09:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:20 AM</option>
          <option value="9:25" <?php if (!(strcmp("09:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:25 AM</option>
          <option value="9:30" <?php if (!(strcmp("09:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:30 AM</option>
          <option value="9:35" <?php if (!(strcmp("09:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:35 AM</option>
          <option value="9:40" <?php if (!(strcmp("09:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:40 AM</option>
          <option value="9:45" <?php if (!(strcmp("09:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:45 AM</option>
          <option value="9:50" <?php if (!(strcmp("09:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:50 AM</option>
          <option value="9:55" <?php if (!(strcmp("09:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:55 AM</option>
          <option value="10:00" <?php if (!(strcmp("10:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:00 AM</option>
          <option value="10:05" <?php if (!(strcmp("10:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:05 AM</option>
          <option value="10:10" <?php if (!(strcmp("10:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:10 AM</option>
          <option value="10:15" <?php if (!(strcmp("10:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:15 AM</option>
          <option value="10:20" <?php if (!(strcmp("10:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:20 AM</option>
          <option value="10:25" <?php if (!(strcmp("10:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:25 AM</option>
          <option value="10:30" <?php if (!(strcmp("10:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:30 AM</option>
          <option value="10:35" <?php if (!(strcmp("10:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:35 AM</option>
          <option value="10:40" <?php if (!(strcmp("10:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:40 AM</option>
          <option value="10:45" <?php if (!(strcmp("10:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:45 AM</option>
          <option value="10:50" <?php if (!(strcmp("10:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:50 AM</option>
          <option value="10:55" <?php if (!(strcmp("10:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:55 AM</option>
          <option value="11:00" <?php if (!(strcmp("11:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:00 AM</option>
          <option value="11:05" <?php if (!(strcmp("11:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:05 AM</option>
          <option value="11:10" <?php if (!(strcmp("11:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:10 AM</option>
          <option value="11:15" <?php if (!(strcmp("11:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:15 AM</option>
          <option value="11:20" <?php if (!(strcmp("11:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:20 AM</option>
          <option value="11:25" <?php if (!(strcmp("11:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:25 AM</option>
          <option value="11:30" <?php if (!(strcmp("11:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:30 AM</option>
          <option value="11:35" <?php if (!(strcmp("11:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:35 AM</option>
          <option value="11:40" <?php if (!(strcmp("11:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:40 AM</option>
          <option value="11:45" <?php if (!(strcmp("11:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:45 AM</option>
          <option value="11:50" <?php if (!(strcmp("11:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:50 AM</option>
          <option value="11:55" <?php if (!(strcmp("11:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:55 AM</option>
          <option value="12:00" <?php if (!(strcmp("12:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>12:00 AM</option>
          <option value="12:05" <?php if (!(strcmp("12:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>12:05 AM</option>
          <option value="12:10" <?php if (!(strcmp("12:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>12:10 AM</option>
          <option value="12:15" <?php if (!(strcmp("12:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>12:15 AM</option>
          <option value="12:20" <?php if (!(strcmp("12:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>12:20 AM</option>
          <option value="12:25" <?php if (!(strcmp("12:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>12:25 AM</option>
          <option value="12:30" <?php if (!(strcmp("12:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>12:30 AM</option>
          <option value="12:35" <?php if (!(strcmp("12:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>12:35 AM</option>
          <option value="12:40" <?php if (!(strcmp("12:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>12:40 AM</option>
          <option value="12:45" <?php if (!(strcmp("12:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>12:45 AM</option>
          <option value="12:50" <?php if (!(strcmp("12:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>12:50 AM</option>
          <option value="12:55" <?php if (!(strcmp("12:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>12:55 AM</option>
          <option value="13:00" <?php if (!(strcmp("13:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>1:00 PM</option>
          <option value="13:05" <?php if (!(strcmp("13:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>1:05 PM</option>
          <option value="13:10" <?php if (!(strcmp("13:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>1:10 PM</option>
          <option value="13:15" <?php if (!(strcmp("13:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>1:15 PM</option>
          <option value="13:20" <?php if (!(strcmp("13:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>1:20 PM</option>
          <option value="13:25" <?php if (!(strcmp("13:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>1:25 PM</option>
          <option value="13:30" <?php if (!(strcmp("13:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>1:30 PM</option>
          <option value="13:35" <?php if (!(strcmp("13:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>1:35 PM</option>
          <option value="13:40" <?php if (!(strcmp("13:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>1:40 PM</option>
          <option value="13:45" <?php if (!(strcmp("13:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>1:45 PM</option>
          <option value="13:50" <?php if (!(strcmp("13:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>1:50 PM</option>
          <option value="13:55" <?php if (!(strcmp("13:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>1:55 PM</option>
          <option value="14:00" <?php if (!(strcmp("14:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>2:00 PM</option>
          <option value="14:05" <?php if (!(strcmp("14:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>2:05 PM</option>
          <option value="14:10" <?php if (!(strcmp("14:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>2:10 PM</option>
          <option value="14:15" <?php if (!(strcmp("14:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>2:15 PM</option>
          <option value="14:20" <?php if (!(strcmp("14:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>2:20 PM</option>
          <option value="14:25" <?php if (!(strcmp("14:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>2:25 PM</option>
          <option value="14:30" <?php if (!(strcmp("14:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>2:30 PM</option>
          <option value="14:35" <?php if (!(strcmp("14:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>2:35 PM</option>
          <option value="14:40" <?php if (!(strcmp("14:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>2:40 PM</option>
          <option value="14:45" <?php if (!(strcmp("14:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>2:45 PM</option>
          <option value="14:50" <?php if (!(strcmp("14:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>2:50 PM</option>
          <option value="14:55" <?php if (!(strcmp("14:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>2:55 PM</option>
          <option value="15:00" <?php if (!(strcmp("15:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>3:00 PM</option>
          <option value="15:05" <?php if (!(strcmp("15:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>3:05 PM</option>
          <option value="15:10" <?php if (!(strcmp("15:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>3:10 PM</option>
          <option value="15:15" <?php if (!(strcmp("15:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>3:15 PM</option>
          <option value="15:20" <?php if (!(strcmp("15:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>3:20 PM</option>
          <option value="15:25" <?php if (!(strcmp("15:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>3:25 PM</option>
          <option value="15:30" <?php if (!(strcmp("15:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>3:30 PM</option>
          <option value="15:35" <?php if (!(strcmp("15:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>3:35 PM</option>
          <option value="15:40" <?php if (!(strcmp("15:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>3:40 PM</option>
          <option value="15:45" <?php if (!(strcmp("15:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>3:45 PM</option>
          <option value="15:50" <?php if (!(strcmp("15:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>3:50 PM</option>
          <option value="15:55" <?php if (!(strcmp("15:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>3:55 PM</option>
          <option value="16:00" <?php if (!(strcmp("16:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>4:00 PM</option>
          <option value="16:05" <?php if (!(strcmp("16:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>4:05 PM</option>
          <option value="16:10" <?php if (!(strcmp("16:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>4:10 PM</option>
          <option value="16:15" <?php if (!(strcmp("16:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>4:15 PM</option>
          <option value="16:20" <?php if (!(strcmp("16:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>4:20 PM</option>
          <option value="16:25" <?php if (!(strcmp("16:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>4:25 PM</option>
          <option value="16:30" <?php if (!(strcmp("16:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>4:30 PM</option>
          <option value="16:35" <?php if (!(strcmp("16:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>4:35 PM</option>
          <option value="16:40" <?php if (!(strcmp("16:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>4:40 PM</option>
          <option value="16:45" <?php if (!(strcmp("16:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>4:45 PM</option>
          <option value="16:50" <?php if (!(strcmp("16:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>4:50 PM</option>
          <option value="16:55" <?php if (!(strcmp("16:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>4:55 PM</option>
          <option value="17:00" <?php if (!(strcmp("17:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:00 PM</option>
          <option value="17:05" <?php if (!(strcmp("17:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:05 PM</option>
          <option value="17:10" <?php if (!(strcmp("17:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:10 PM</option>
          <option value="17:15" <?php if (!(strcmp("17:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:15 PM</option>
          <option value="17:20" <?php if (!(strcmp("17:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:20 PM</option>
          <option value="17:25" <?php if (!(strcmp("17:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:25 PM</option>
          <option value="17:30" <?php if (!(strcmp("17:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:30 PM</option>
          <option value="17:35" <?php if (!(strcmp("17:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:35 PM</option>
          <option value="17:40" <?php if (!(strcmp("17:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:40 PM</option>
          <option value="17:45" <?php if (!(strcmp("17:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:45 PM</option>
          <option value="17:50" <?php if (!(strcmp("17:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:50 PM</option>
          <option value="17:55" <?php if (!(strcmp("17:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>5:55 PM</option>
          <option value="18:00" <?php if (!(strcmp("18:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:00 PM</option>
          <option value="18:05" <?php if (!(strcmp("18:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:05 PM</option>
          <option value="18:10" <?php if (!(strcmp("18:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:10 PM</option>
          <option value="18:15" <?php if (!(strcmp("18:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:15 PM</option>
          <option value="18:20" <?php if (!(strcmp("18:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:20 PM</option>
          <option value="18:25" <?php if (!(strcmp("18:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:25 PM</option>
          <option value="18:30" <?php if (!(strcmp("18:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:30 PM</option>
          <option value="18:35" <?php if (!(strcmp("18:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:35 PM</option>
          <option value="18:40" <?php if (!(strcmp("18:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:40 PM</option>
          <option value="18:45" <?php if (!(strcmp("18:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:45 PM</option>
          <option value="18:50" <?php if (!(strcmp("18:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:50 PM</option>
          <option value="18:55" <?php if (!(strcmp("18:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>6:55 PM</option>
          <option value="19:00" <?php if (!(strcmp("19:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:00 PM</option>
          <option value="19:05" <?php if (!(strcmp("19:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:05 PM</option>
          <option value="19:10" <?php if (!(strcmp("19:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:10 PM</option>
          <option value="19:15" <?php if (!(strcmp("19:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:15 PM</option>
          <option value="19:20" <?php if (!(strcmp("19:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:20 PM</option>
          <option value="19:25" <?php if (!(strcmp("19:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:25 PM</option>
          <option value="19:30" <?php if (!(strcmp("19:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:30 PM</option>
          <option value="19:35" <?php if (!(strcmp("19:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:35 PM</option>
          <option value="19:40" <?php if (!(strcmp("19:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:40 PM</option>
          <option value="19:45" <?php if (!(strcmp("19:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:45 PM</option>
          <option value="19:50" <?php if (!(strcmp("19:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:50 PM</option>
          <option value="19:55" <?php if (!(strcmp("19:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>7:55 PM</option>
          <option value="20:00" <?php if (!(strcmp("20:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:00 PM</option>
          <option value="20:05" <?php if (!(strcmp("20:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:05 PM</option>
          <option value="20:10" <?php if (!(strcmp("20:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:10 PM</option>
          <option value="20:15" <?php if (!(strcmp("20:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:15 PM</option>
          <option value="20:20" <?php if (!(strcmp("20:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:20 PM</option>
          <option value="20:25" <?php if (!(strcmp("20:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:25 PM</option>
          <option value="20:30" <?php if (!(strcmp("20:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:30 PM</option>
          <option value="20:35" <?php if (!(strcmp("20:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:35 PM</option>
          <option value="20:40" <?php if (!(strcmp("20:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:40 PM</option>
          <option value="20:45" <?php if (!(strcmp("20:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:45 PM</option>
          <option value="20:50" <?php if (!(strcmp("20:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:50 PM</option>
          <option value="20:55" <?php if (!(strcmp("20:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>8:55 PM</option>
          <option value="21:00" <?php if (!(strcmp("21:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:00 PM</option>
          <option value="21:05" <?php if (!(strcmp("21:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:05 PM</option>
          <option value="21:10" <?php if (!(strcmp("21:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:10 PM</option>
          <option value="21:15" <?php if (!(strcmp("21:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:15 PM</option>
          <option value="21:20" <?php if (!(strcmp("21:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:20 PM</option>
          <option value="21:25" <?php if (!(strcmp("21:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:25 PM</option>
          <option value="21:30" <?php if (!(strcmp("21:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:30 PM</option>
          <option value="21:35" <?php if (!(strcmp("21:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:35 PM</option>
          <option value="21:40" <?php if (!(strcmp("21:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:40 PM</option>
          <option value="21:45" <?php if (!(strcmp("21:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:45 PM</option>
          <option value="21:50" <?php if (!(strcmp("21:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:50 PM</option>
          <option value="21:55" <?php if (!(strcmp("21:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>9:55 PM</option>
          <option value="22:00" <?php if (!(strcmp("22:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:00 PM</option>
          <option value="22:05" <?php if (!(strcmp("22:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:05 PM</option>
          <option value="22:10" <?php if (!(strcmp("22:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:10 PM</option>
          <option value="22:15" <?php if (!(strcmp("22:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:15 PM</option>
          <option value="22:20" <?php if (!(strcmp("22:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:20 PM</option>
          <option value="22:25" <?php if (!(strcmp("22:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:25 PM</option>
          <option value="22:30" <?php if (!(strcmp("22:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:30 PM</option>
          <option value="22:35" <?php if (!(strcmp("22:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:35 PM</option>
          <option value="22:40" <?php if (!(strcmp("22:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:40 PM</option>
          <option value="22:45" <?php if (!(strcmp("22:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:45 PM</option>
          <option value="22:50" <?php if (!(strcmp("22:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:50 PM</option>
          <option value="22:55" <?php if (!(strcmp("22:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>10:55 PM</option>
          <option value="23:00" <?php if (!(strcmp("23:00:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:00 PM</option>
          <option value="23:05" <?php if (!(strcmp("23:05:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:05 PM</option>
          <option value="23:10" <?php if (!(strcmp("23:10:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:10 PM</option>
          <option value="23:15" <?php if (!(strcmp("23:15:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:15 PM</option>
          <option value="23:20" <?php if (!(strcmp("23:20:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:20 PM</option>
          <option value="23:25" <?php if (!(strcmp("23:25:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:25 PM</option>
          <option value="23:30" <?php if (!(strcmp("23:30:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:30 PM</option>
          <option value="23:35" <?php if (!(strcmp("23:35:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:35 PM</option>
          <option value="23:40" <?php if (!(strcmp("23:40:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:40 PM</option>
          <option value="23:45" <?php if (!(strcmp("23:45:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:45 PM</option>
          <option value="23:50" <?php if (!(strcmp("23:50:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:50 PM</option>
          <option value="23:55" <?php if (!(strcmp("23:55:00", $row_event['eventEndTime']))) {echo "selected=\"selected\"";} ?>>11:55 PM</option>
</select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Require Registration?</td>
        <td><label for="requireRegistration"></label>
          <select name="requireRegistration" id="requireRegistration">
            <option value="0" <?php if (!(strcmp(0, $row_event['requireRegistration']))) {echo "selected=\"selected\"";} ?>>No</option>
            <option value="1" <?php if (!(strcmp(1, $row_event['requireRegistration']))) {echo "selected=\"selected\"";} ?>>Yes</option>
        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Description</td>
        <td><textarea name="description" cols="32" rows="5"><?php echo htmlentities($row_event['description'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Event Capacity</td>
        <td><input type="number" name="eventCapacity" value="<?php echo htmlentities($row_event['eventCapacity'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Fee</td>
        <td><input type="number" name="eventFee" value="<?php echo htmlentities($row_event['eventFee'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Payment Button Code</td>
        <td><textarea name="paymentCode" cols="32" rows="5"><?php echo htmlentities($row_event['paymentCode'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Instructor</td>
        <td><select name="instructorID"><option value="" selected="selected" <?php if (!(strcmp("", $row_event['instructorID']))) {echo "selected=\"selected\"";} ?>>None</option>
          <?php
do {  
?>
          <option value="<?php echo $row_instructors['instructorID']?>"<?php if (!(strcmp($row_instructors['instructorID'], $row_event['instructorID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_instructors['lastName']?>, <?php echo $row_instructors['firstName']?></option>
          <?php
} while ($row_instructors = mysql_fetch_assoc($instructors));
  $rows = mysql_num_rows($instructors);
  if($rows > 0) {
      mysql_data_seek($instructors, 0);
	  $row_instructors = mysql_fetch_assoc($instructors);
  }
?>
        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Guest Instructor</td>
        <td><input type="text" name="guestInstructor" value="<?php echo htmlentities($row_event['guestInstructor'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><input type="submit" value="Save Changes" /></td>
      </tr>
    </table>
    <input type="hidden" name="MM_update" value="form1" />
    <input type="hidden" name="eventID" value="<?php echo $row_event['eventID']; ?>" />
  </form>
  <p>&nbsp;</p>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript" src="datePicker/picker.js"></script> 
<script>
/**
 * pick a date
 */
$('#eventDate').pickadate({
  onOpen: function() {
    scrollIntoView( this.$node )
  },
        format: 'yyyy-mm-dd',
        formatSubmit: 'yyyy-mm-dd',
});

Dropzone.autoDiscover = false;
$(function() {
  var myDropzone = new Dropzone("#myAwesomeForm");
  myDropzone.on("queuecomplete", function(file) {
		location.reload(); 
  });
})
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($event);

mysql_free_result($eventAttendance);
?>