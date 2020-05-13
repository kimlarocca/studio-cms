<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
?>
<?php require_once('Connections/wotg.php'); ?>
<?php
$sortBy = 'eventName';
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO events (studioID, eventName, eventDate, eventStartTime) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['studioID'], "int"),
                       GetSQLValueString($_POST['eventName'], "text"),
                       GetSQLValueString($_POST['eventDate'], "date"),
                       GetSQLValueString($_POST['eventStartTime'], "date"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());

  $id = mysql_insert_id();

  $insertGoTo = "events-update.php?eventID=".$id;
  //if (isset($_SERVER['QUERY_STRING'])) {
  //  $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
  //  $insertGoTo .= $_SERVER['QUERY_STRING'];
  //}
  header(sprintf("Location: %s", $insertGoTo));
}

//delete record
if ($_GET['action'] == 'delete') {
    $deleterecords = "DELETE FROM events WHERE eventID = ".$_GET['eventID'];
    mysql_select_db($database_wotg, $wotg);
    mysql_query($deleterecords, $wotg) or die(mysql_error());
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
$query_events = "SELECT * FROM events WHERE eventDate >= CURRENT_DATE() AND studioID=".$row_currentUser['studioID'];
$events = mysql_query($query_events, $wotg) or die(mysql_error());
$row_events = mysql_fetch_assoc($events);
$totalRows_events = mysql_num_rows($events);

mysql_select_db($database_wotg, $wotg);
$query_instructors = "SELECT instructorID,lastName,firstName,studioID,active,securityLevel FROM instructors WHERE securityLevel = 'Instructor' AND studioID = ".$row_currentUser['studioID']." AND active = 1 ORDER BY lastName";
$instructors = mysql_query($query_instructors, $wotg) or die(mysql_error());
$row_instructors = mysql_fetch_assoc($instructors);
$totalRows_instructors = mysql_num_rows($instructors);
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
<title><?php echo $row_currentUser['studioName']; ?> | Events</title>
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

<?php include("header.php"); ?>

<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Events &amp; Sessions</h1>

<?php include("navigation.php"); ?>
<div class="twd_container">

  <h2 class="twd_centered twd_margin20" style="padding:20px 0 0 0">Upcoming Events &amp; Sessions&nbsp;&nbsp;<a id="addClass" href="javascript:void();" class="tooltip" title="add a new event"><img src="images/plus.png" width="22" height="22" /></a></h2>
<?php
//delete record
if ($_GET['action'] == 'delete') print '<p class="twd_centered twd_margin20" style="color:red;">The event has been deleted!</p>';
//check if changes were saved
if ($_GET['action'] == 'saved') print '<p class="twd_centered twd_margin20" style="color:red">Your event has been added!</p>'; 
?>
<div id="addClassForm">
<p class="twd_centered twd_margin20">Fill out the form below to add a new event!</p>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
  <table align="center">
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Name *</td>
      <td><input type="text" name="eventName" value="" size="32" required="required" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Date *</td>
      <td><input type="text" name="eventDate" id="eventDate" value="" size="32" required="required" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Start Time *</td>
      <td><select name="eventStartTime">

	<option value="5:00">5:00 AM</option>
	<option value="5:05">5:05 AM</option>
	<option value="5:10">5:10 AM</option>
	<option value="5:15">5:15 AM</option>
	<option value="5:20">5:20 AM</option>
	<option value="5:25">5:25 AM</option>
	<option value="5:30">5:30 AM</option>
	<option value="5:35">5:35 AM</option>
	<option value="5:40">5:40 AM</option>
	<option value="5:45">5:45 AM</option>
	<option value="5:50">5:50 AM</option>
	<option value="5:55">5:55 AM</option>

	<option value="6:00">6:00 AM</option>
	<option value="6:05">6:05 AM</option>
	<option value="6:10">6:10 AM</option>
	<option value="6:15">6:15 AM</option>
	<option value="6:20">6:20 AM</option>
	<option value="6:25">6:25 AM</option>
	<option value="6:30">6:30 AM</option>
	<option value="6:35">6:35 AM</option>
	<option value="6:40">6:40 AM</option>
	<option value="6:45">6:45 AM</option>
	<option value="6:50">6:50 AM</option>
	<option value="6:55">6:55 AM</option>

	<option value="7:00">7:00 AM</option>
	<option value="7:05">7:05 AM</option>
	<option value="7:10">7:10 AM</option>
	<option value="7:15">7:15 AM</option>
	<option value="7:20">7:20 AM</option>
	<option value="7:25">7:25 AM</option>
	<option value="7:30">7:30 AM</option>
	<option value="7:35">7:35 AM</option>
	<option value="7:40">7:40 AM</option>
	<option value="7:45">7:45 AM</option>
	<option value="7:50">7:50 AM</option>
	<option value="7:55">7:55 AM</option>

	<option value="8:00">8:00 AM</option>
	<option value="8:05">8:05 AM</option>
	<option value="8:10">8:10 AM</option>
	<option value="8:15">8:15 AM</option>
	<option value="8:20">8:20 AM</option>
	<option value="8:25">8:25 AM</option>
	<option value="8:30">8:30 AM</option>
	<option value="8:35">8:35 AM</option>
	<option value="8:40">8:40 AM</option>
	<option value="8:45">8:45 AM</option>
	<option value="8:50">8:50 AM</option>
	<option value="8:55">8:55 AM</option>

	<option value="9:00">9:00 AM</option>
	<option value="9:05">9:05 AM</option>
	<option value="9:10">9:10 AM</option>
	<option value="9:15">9:15 AM</option>
	<option value="9:20">9:20 AM</option>
	<option value="9:25">9:25 AM</option>
	<option value="9:30">9:30 AM</option>
	<option value="9:35">9:35 AM</option>
	<option value="9:40">9:40 AM</option>
	<option value="9:45">9:45 AM</option>
	<option value="9:50">9:50 AM</option>
	<option value="9:55">9:55 AM</option>

	<option value="10:00">10:00 AM</option>
	<option value="10:05">10:05 AM</option>
	<option value="10:10">10:10 AM</option>
	<option value="10:15">10:15 AM</option>
	<option value="10:20">10:20 AM</option>
	<option value="10:25">10:25 AM</option>
	<option value="10:30">10:30 AM</option>
	<option value="10:35">10:35 AM</option>
	<option value="10:40">10:40 AM</option>
	<option value="10:45">10:45 AM</option>
	<option value="10:50">10:50 AM</option>
	<option value="10:55">10:55 AM</option>

	<option value="11:00">11:00 AM</option>
	<option value="11:05">11:05 AM</option>
	<option value="11:10">11:10 AM</option>
	<option value="11:15">11:15 AM</option>
	<option value="11:20">11:20 AM</option>
	<option value="11:25">11:25 AM</option>
	<option value="11:30">11:30 AM</option>
	<option value="11:35">11:35 AM</option>
	<option value="11:40">11:40 AM</option>
	<option value="11:45">11:45 AM</option>
	<option value="11:50">11:50 AM</option>
	<option value="11:55">11:55 AM</option>

	<option value="12:00">12:00 AM</option>
	<option value="12:05">12:05 AM</option>
	<option value="12:10">12:10 AM</option>
	<option value="12:15">12:15 AM</option>
	<option value="12:20">12:20 AM</option>
	<option value="12:25">12:25 AM</option>
	<option value="12:30">12:30 AM</option>
	<option value="12:35">12:35 AM</option>
	<option value="12:40">12:40 AM</option>
	<option value="12:45">12:45 AM</option>
	<option value="12:50">12:50 AM</option>
	<option value="12:55">12:55 AM</option>

	<option value="13:00">1:00 PM</option>
	<option value="13:05">1:05 PM</option>
	<option value="13:10">1:10 PM</option>
	<option value="13:15">1:15 PM</option>
	<option value="13:20">1:20 PM</option>
	<option value="13:25">1:25 PM</option>
	<option value="13:30">1:30 PM</option>
	<option value="13:35">1:35 PM</option>
	<option value="13:40">1:40 PM</option>
	<option value="13:45">1:45 PM</option>
	<option value="13:50">1:50 PM</option>
	<option value="13:55">1:55 PM</option>

	<option value="14:00">2:00 PM</option>
	<option value="14:05">2:05 PM</option>
	<option value="14:10">2:10 PM</option>
	<option value="14:15">2:15 PM</option>
	<option value="14:20">2:20 PM</option>
	<option value="14:25">2:25 PM</option>
	<option value="14:30">2:30 PM</option>
	<option value="14:35">2:35 PM</option>
	<option value="14:40">2:40 PM</option>
	<option value="14:45">2:45 PM</option>
	<option value="14:50">2:50 PM</option>
	<option value="14:55">2:55 PM</option>

	<option value="15:00">3:00 PM</option>
	<option value="15:05">3:05 PM</option>
	<option value="15:10">3:10 PM</option>
	<option value="15:15">3:15 PM</option>
	<option value="15:20">3:20 PM</option>
	<option value="15:25">3:25 PM</option>
	<option value="15:30">3:30 PM</option>
	<option value="15:35">3:35 PM</option>
	<option value="15:40">3:40 PM</option>
	<option value="15:45">3:45 PM</option>
	<option value="15:50">3:50 PM</option>
	<option value="15:55">3:55 PM</option>

	<option value="16:00">4:00 PM</option>
	<option value="16:05">4:05 PM</option>
	<option value="16:10">4:10 PM</option>
	<option value="16:15">4:15 PM</option>
	<option value="16:20">4:20 PM</option>
	<option value="16:25">4:25 PM</option>
	<option value="16:30">4:30 PM</option>
	<option value="16:35">4:35 PM</option>
	<option value="16:40">4:40 PM</option>
	<option value="16:45">4:45 PM</option>
	<option value="16:50">4:50 PM</option>
	<option value="16:55">4:55 PM</option>

	<option value="17:00">5:00 PM</option>
	<option value="17:05">5:05 PM</option>
	<option value="17:10">5:10 PM</option>
	<option value="17:15">5:15 PM</option>
	<option value="17:20">5:20 PM</option>
	<option value="17:25">5:25 PM</option>
	<option value="17:30">5:30 PM</option>
	<option value="17:35">5:35 PM</option>
	<option value="17:40">5:40 PM</option>
	<option value="17:45">5:45 PM</option>
	<option value="17:50">5:50 PM</option>
	<option value="17:55">5:55 PM</option>

	<option value="18:00">6:00 PM</option>
	<option value="18:05">6:05 PM</option>
	<option value="18:10">6:10 PM</option>
	<option value="18:15">6:15 PM</option>
	<option value="18:20">6:20 PM</option>
	<option value="18:25">6:25 PM</option>
	<option value="18:30">6:30 PM</option>
	<option value="18:35">6:35 PM</option>
	<option value="18:40">6:40 PM</option>
	<option value="18:45">6:45 PM</option>
	<option value="18:50">6:50 PM</option>
	<option value="18:55">6:55 PM</option>

	<option value="19:00">7:00 PM</option>
	<option value="19:05">7:05 PM</option>
	<option value="19:10">7:10 PM</option>
	<option value="19:15">7:15 PM</option>
	<option value="19:20">7:20 PM</option>
	<option value="19:25">7:25 PM</option>
	<option value="19:30">7:30 PM</option>
	<option value="19:35">7:35 PM</option>
	<option value="19:40">7:40 PM</option>
	<option value="19:45">7:45 PM</option>
	<option value="19:50">7:50 PM</option>
	<option value="19:55">7:55 PM</option>

	<option value="20:00">8:00 PM</option>
	<option value="20:05">8:05 PM</option>
	<option value="20:10">8:10 PM</option>
	<option value="20:15">8:15 PM</option>
	<option value="20:20">8:20 PM</option>
	<option value="20:25">8:25 PM</option>
	<option value="20:30">8:30 PM</option>
	<option value="20:35">8:35 PM</option>
	<option value="20:40">8:40 PM</option>
	<option value="20:45">8:45 PM</option>
	<option value="20:50">8:50 PM</option>
	<option value="20:55">8:55 PM</option>

	<option value="21:00">9:00 PM</option>
	<option value="21:05">9:05 PM</option>
	<option value="21:10">9:10 PM</option>
	<option value="21:15">9:15 PM</option>
	<option value="21:20">9:20 PM</option>
	<option value="21:25">9:25 PM</option>
	<option value="21:30">9:30 PM</option>
	<option value="21:35">9:35 PM</option>
	<option value="21:40">9:40 PM</option>
	<option value="21:45">9:45 PM</option>
	<option value="21:50">9:50 PM</option>
	<option value="21:55">9:55 PM</option>

	<option value="22:00">10:00 PM</option>
	<option value="22:05">10:05 PM</option>
	<option value="22:10">10:10 PM</option>
	<option value="22:15">10:15 PM</option>
	<option value="22:20">10:20 PM</option>
	<option value="22:25">10:25 PM</option>
	<option value="22:30">10:30 PM</option>
	<option value="22:35">10:35 PM</option>
	<option value="22:40">10:40 PM</option>
	<option value="22:45">10:45 PM</option>
	<option value="22:50">10:50 PM</option>
	<option value="22:55">10:55 PM</option>

	<option value="23:00">11:00 PM</option>
	<option value="23:05">11:05 PM</option>
	<option value="23:10">11:10 PM</option>
	<option value="23:15">11:15 PM</option>
	<option value="23:20">11:20 PM</option>
	<option value="23:25">11:25 PM</option>
	<option value="23:30">11:30 PM</option>
	<option value="23:35">11:35 PM</option>
	<option value="23:40">11:40 PM</option>
	<option value="23:45">11:45 PM</option>
	<option value="23:50">11:50 PM</option>
	<option value="23:55">11:55 PM</option>
</select></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><input type="submit" value="Add &amp; Continue" /></td>
    </tr>
  </table>
  <input type="hidden" name="studioID" value="1" />
  <input type="hidden" name="MM_insert" value="form1" />
</form>
<p>&nbsp;</p>
</div>
  <?php if ($totalRows_events==0) { echo '<p class="twd_centered twd_margin20">No events were found! Add a new event using the plus sign + above.</p>'; } else { ?>
  <p class="twd_centered twd_margin20">Click on any  name to view or update.</p>
  <table border="0" align="center" cellpadding="5" cellspacing="0">
    <tr>
      <td>&nbsp;</td>
      <td><strong>Name</strong></td>
      <td><strong>Date</strong></td>
      <td><strong>Time</strong></td>
      <td><strong>Registrations</strong></td>
      <td>&nbsp;</td>
    </tr>
    <?php do { 
	$newDate = date("m/d/Y", strtotime($row_events['eventDate']));
	$newStartTime = date('g:i A', strtotime($row_events['eventStartTime']));
	$newEndTime = date('g:i A', strtotime($row_events['eventEndTime']));
	if(!isset($row_events['eventEndTime'])) $newEndTime='';
	?>
      <tr>
        <td><?php if($row_events['thumbnail']!=''){ ?>
          <img height="100" width="100" src="uploads/<?php echo $row_events['thumbnail']; ?>" />
          <?php } else { ?>
          <img height="100" width="100" src="uploads/unavailable.gif" />
          <?php } ?></td>
        <td><a href="events-update.php?eventID=<?php echo $row_events['eventID']; ?>"><?php echo $row_events['eventName']; ?></a></td>
        <td><?php echo $newDate; ?></td>
        <td><?php echo $newStartTime; ?>-<?php echo $newEndTime; ?></td>
        <td class="twd_centered">
        <?php
        //check availability and cancellations
		mysql_select_db($database_wotg, $wotg);
		$query_attendance = "SELECT eventAttendanceID, eventID FROM eventAttendance WHERE eventID = ".$row_events['eventID'];
		$attendance = mysql_query($query_attendance, $wotg) or die(mysql_error());
		$row_attendance = mysql_fetch_assoc($attendance);
		$totalRows_attendance = mysql_num_rows($attendance);
		echo $totalRows_attendance." / ".$row_events['eventCapacity'];
        ?>
        </td>
        <td><a class="tooltip" title="preview this event" href="iframe-event.php?studioID=<?php echo $row_currentUser['studioID']; ?>&eventID=<?php echo $row_events['eventID']; ?>" target="_blank"><img src="images/view.png" width="20" height="20" /></a>&nbsp;<a class="tooltip" title="update this event" href="events-update.php?eventID=<?php echo $row_events['eventID']; ?>"><img src="images/edit.png" width="20" height="20" /></a>&nbsp;<a class="tooltip" title="delete this event" href="events.php?action=delete&eventID=<?php echo $row_events['eventID']; ?>"><img src="images/delete.png" width="20" height="20" /></a></td>
      </tr>
      <?php } while ($row_events = mysql_fetch_assoc($events)); ?>
  </table>
<?php } ?>
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
  format: 'yyyy-mm-dd'
})
</script>
<script>
$("#addClass").click(function() {
		if ($("#addClassForm").is(':visible')) {
			$("#addClassForm").slideUp("slow");
			$('#addClass img').attr('src', 'images/plus.png');
		}
		else {
			$("#addClassForm").slideDown("slow");
			$('#addClass img').attr('src', 'images/minus.png');
		}
});
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);
mysql_free_result($events);
mysql_free_result($instructors);
?>