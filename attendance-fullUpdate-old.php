<?php require_once('Connections/wotg.php'); ?>
<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE attendance SET studentID=%s, classID=%s, instructorID=%s, dateAdded=%s, attendanceType=%s, addedBy=%s, paymentType=%s WHERE attendanceID=%s",
                       GetSQLValueString($_POST['studentID'], "int"),
                       GetSQLValueString($_POST['classID'], "int"),
                       GetSQLValueString($_POST['instructorID'], "int"),
                       GetSQLValueString($_POST['dateAdded'], "date"),
                       GetSQLValueString($_POST['attendanceType'], "text"),
                       GetSQLValueString($_POST['addedBy'], "text"),
                       GetSQLValueString($_POST['paymentType'], "text"),
                       GetSQLValueString(isset($_POST['attendanceID']) ? "true" : "", "defined","1","0"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "attendance-fullUpdate.php?action=saved&attendanceID=".$_GET['attendanceID'];
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

$colname_attendance = "-1";
if (isset($_GET['attendanceID'])) {
  $colname_attendance = $_GET['attendanceID'];
}
mysql_select_db($database_wotg, $wotg);
$query_attendance = sprintf("SELECT * FROM attendance WHERE attendanceID = %s", GetSQLValueString($colname_attendance, "int"));
$attendance = mysql_query($query_attendance, $wotg) or die(mysql_error());
$row_attendance = mysql_fetch_assoc($attendance);
$totalRows_attendance = mysql_num_rows($attendance);

mysql_select_db($database_wotg, $wotg);
$query_instructors = "SELECT * FROM instructors WHERE active=1 AND studioID = ".$row_currentUser['studioID']." ORDER BY lastName ASC";
$instructors = mysql_query($query_instructors, $wotg) or die(mysql_error());
$row_instructors = mysql_fetch_assoc($instructors);
$totalRows_instructors = mysql_num_rows($instructors);

mysql_select_db($database_wotg, $wotg);
$query_classes = "SELECT * FROM classes WHERE classActive=1 AND studioID = ".$row_currentUser['studioID']." ORDER BY name ASC";
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);

mysql_select_db($database_wotg, $wotg);
$query_students = "SELECT * FROM students WHERE studioID = ".$row_currentUser['studioID']." ORDER BY lastName";
$students = mysql_query($query_students, $wotg) or die(mysql_error());
$row_students = mysql_fetch_assoc($students);
$totalRows_students = mysql_num_rows($students);
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
<title><?php echo $row_currentUser['studioName']; ?> | Update Attendance Record</title>
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Update Attendance Record</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
  <p class="twd_centered twd_margin20">Fill out the form below to update this attendance record:</p>
  <?php
if ($_GET['action'] == 'saved') print '<p style="color:red">Your changes have been saved!</p>'; 
?>
  <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
    <table border="0" cellpadding="5" cellspacing="0" align="center">
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Student:</td>
        <td>
        <select name="studentID">
          <?php
do {  
?>
          <option value="<?php echo $row_students['studentID']?>"<?php if (!(strcmp($row_students['studentID'], $row_attendance['studentID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_students['lastName']?>, <?php echo $row_students['firstName']?></option>
          <?php
} while ($row_students = mysql_fetch_assoc($students));
  $rows = mysql_num_rows($students);
  if($rows > 0) {
      mysql_data_seek($students, 0);
	  $row_students = mysql_fetch_assoc($students);
  }
?>
        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Class:</td>
        <td><select name="classID">
          <?php
do {  
?>
          <option value="<?php echo $row_classes['classID']?>"<?php if (!(strcmp($row_classes['classID'], $row_attendance['classID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_classes['name']?></option>
          <?php
} while ($row_classes = mysql_fetch_assoc($classes));
  $rows = mysql_num_rows($classes);
  if($rows > 0) {
      mysql_data_seek($classes, 0);
	  $row_classes = mysql_fetch_assoc($classes);
  }
?>
        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Instructor:</td>
        <td><select name="instructorID">
          <?php
do {  
?>
          <option value="<?php echo $row_instructors['instructorID']?>"<?php if (!(strcmp($row_instructors['instructorID'], $row_attendance['instructorID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_instructors['lastName']?>, <?php echo $row_instructors['firstName']?></option>
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
        <td nowrap="nowrap" align="right">Attendance Date:</td>
        <td>
        <input type="text" name="dateAdded" id="dateAdded" placeholder="<?php echo htmlentities($row_attendance['dateAdded'], ENT_COMPAT, 'UTF-8'); ?>" value="<?php echo htmlentities($row_attendance['dateAdded'], ENT_COMPAT, 'UTF-8'); ?>" data-value="<?php echo htmlentities($row_attendance['dateAdded'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Attendance Type:</td>
        <td><select name="attendanceType" id="attendanceType">
          <option selected="selected" value="" <?php if (!(strcmp("", $row_attendance['attendanceType']))) {echo "selected=\"selected\"";} ?>> </option>
          <option value="Drop In" <?php if (!(strcmp("Drop In", $row_attendance['attendanceType']))) {echo "selected=\"selected\"";} ?>>Drop In</option>
          <option value="Pre Paid" <?php if (!(strcmp("Pre Paid", $row_attendance['attendanceType']))) {echo "selected=\"selected\"";} ?>>Pre Paid</option>
          <option value="Member" <?php if (!(strcmp("Member", $row_attendance['attendanceType']))) {echo "selected=\"selected\"";} ?>>Member</option>
              </select>
        
        </td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Added By:</td>
        <td><input type="text" name="addedBy" value="<?php echo htmlentities($row_attendance['addedBy'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Payment Type:</td>
        <td><select name="paymentType" id="paymentType">
          <option selected="selected" value="" <?php if (!(strcmp("", $row_attendance['paymentType']))) {echo "selected=\"selected\"";} ?>> </option>
          <option value="Cash" <?php if (!(strcmp("Cash", $row_attendance['paymentType']))) {echo "selected=\"selected\"";} ?>>Cash</option>
          <option value="Check" <?php if (!(strcmp("Check", $row_attendance['paymentType']))) {echo "selected=\"selected\"";} ?>>Check</option>
          <option value="PayPal" <?php if (!(strcmp("PayPal", $row_attendance['paymentType']))) {echo "selected=\"selected\"";} ?>>PayPal</option>
          <option value="Credit Card" <?php if (!(strcmp("Credit Card", $row_attendance['paymentType']))) {echo "selected=\"selected\"";} ?>>Credit Card</option>
        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Checked In?</td>
        <td><label for="select"></label>
          <select name="select" id="select">
            <option value="1" <?php if (!(strcmp(1, $row_attendance['checkedIn']))) {echo "selected=\"selected\"";} ?>>Yes</option>
            <option value="0" <?php if (!(strcmp(0, $row_attendance['checkedIn']))) {echo "selected=\"selected\"";} ?>>No</option>
          </select>          
        <label for="checkedIn"></label></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><input type="submit" value="Save Changes" /></td>
      </tr>
    </table>
    <input type="hidden" name="MM_update" value="form1" />
    <input type="hidden" name="attendanceID" value="<?php echo $row_attendance['attendanceID']; ?>" />
  </form>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript" src="datePicker/picker.js"></script> 
<script>
/**
 * pick a date
 */
$('#dateAdded').pickadate({
  onOpen: function() {
    scrollIntoView( this.$node )
  },
        format: 'yyyy-mm-dd',
        formatSubmit: 'yyyy-mm-dd',
});
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

mysql_free_result($attendance);

mysql_free_result($instructors);

mysql_free_result($classes);

mysql_free_result($students);
?>