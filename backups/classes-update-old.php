<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE classes SET name=%s, imemo=%s, classDay=%s, startTime=%s, endTime=%s, instructorID=%s, classActive=%s, classFee=%s, prepaidFee=%s, studio=%s, classCapacity=%s WHERE classID=%s",
                       GetSQLValueString($_POST['name'], "text"),
                       GetSQLValueString($_POST['imemo'], "text"),
                       GetSQLValueString($_POST['classDay'], "text"),
                       GetSQLValueString($_POST['startTime'], "text"),
                       GetSQLValueString($_POST['endTime'], "text"),
                       GetSQLValueString($_POST['instructorID'], "int"),
                       GetSQLValueString($_POST['classActive'], "int"),
                       GetSQLValueString($_POST['classFee'], "int"),
                       GetSQLValueString($_POST['prepaidFee'], "int"),
                       GetSQLValueString($_POST['studio'], "text"),
                       GetSQLValueString($_POST['classCapacity'], "int"),
                       GetSQLValueString($_POST['classID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "classes-update.php?action=saved";
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

$colname_classes = "-1";
if (isset($_GET['classID'])) {
  $colname_classes = $_GET['classID'];
}
mysql_select_db($database_wotg, $wotg);
$query_classes = sprintf("SELECT * FROM classes WHERE classID = %s", GetSQLValueString($colname_classes, "int"));
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);

mysql_select_db($database_wotg, $wotg);
$query_instructors = "SELECT * FROM instructors WHERE studioID = ".$row_currentUser['studioID']." AND active = 1 ORDER BY lastName ASC";
$instructors = mysql_query($query_instructors, $wotg) or die(mysql_error());
$row_instructors = mysql_fetch_assoc($instructors);
$totalRows_instructors = mysql_num_rows($instructors);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="styles.css"/>
<title>WOTG Administration</title>
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

<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Classes</h1>

<?php include("navigation.php"); ?>
<div class="twd_container">
<h2 class="twd_centered">Update Class: <?php echo htmlentities($row_classes['name'], ENT_COMPAT, 'UTF-8'); ?></h2>
  <div class="twd_centered twd_margin20"><a class="button twd_margin20" href="classes.php?action=delete&amp;classID=<?php echo $_GET['classID']; ?>">Delete This Class</a></div>
<?php
//check if changes were saved
if ($_GET['action'] == 'saved') print '<p class="twd_centered twd_margin20" style="color:red">Your changes have been saved!</p>'; 
?>
  <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" onsubmit="MM_validateForm('name','','R','classFee','','NisNum','prepaidFee','','NisNum','classCapacity','','NisNum');return document.MM_returnValue">
    <table align="center">
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Class ID:</td>
        <td><?php echo $row_classes['classID']; ?></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Class Name:</td>
        <td><input name="name" type="text" id="name" value="<?php echo htmlentities($row_classes['name'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Day:</td>
        <td><select name="classDay">
          <option value="Monday" <?php if (!(strcmp("Monday", $row_classes['classDay']))) {echo "selected=\"selected\"";} ?>>Monday</option>
          <option value="Tuesday" <?php if (!(strcmp("Tuesday", $row_classes['classDay']))) {echo "selected=\"selected\"";} ?>>Tuesday</option>
          <option value="Wednesday" <?php if (!(strcmp("Wednesday", $row_classes['classDay']))) {echo "selected=\"selected\"";} ?>>Wednesday</option>
          <option value="Thursday" <?php if (!(strcmp("Thursday", $row_classes['classDay']))) {echo "selected=\"selected\"";} ?>>Thursday</option>
          <option value="Friday" <?php if (!(strcmp("Friday", $row_classes['classDay']))) {echo "selected=\"selected\"";} ?>>Friday</option>
          <option value="Saturday" <?php if (!(strcmp("Saturday", $row_classes['classDay']))) {echo "selected=\"selected\"";} ?>>Saturday</option>
          <option value="Sunday" <?php if (!(strcmp("Sunday", $row_classes['classDay']))) {echo "selected=\"selected\"";} ?>>Sunday</option>
        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Starts:</td>
        <td><select name="startTime">
        <option value="00:00:00" <?php if (!(strcmp("00:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>midnight</option><option value="00:30:00" <?php if (!(strcmp("00:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>0:30 am</option><option value="01:00:00" <?php if (!(strcmp("01:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>1:00 am</option><option value="01:30:00" <?php if (!(strcmp("01:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>1:30 am</option><option value="02:00:00" <?php if (!(strcmp("02:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>2:00 am</option><option value="02:30:00" <?php if (!(strcmp("02:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>2:30 am</option><option value="03:00:00" <?php if (!(strcmp("03:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>3:00 am</option><option value="03:30:00" <?php if (!(strcmp("03:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>3:30 am</option><option value="04:00:00" <?php if (!(strcmp("04:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>4:00 am</option><option value="04:30:00" <?php if (!(strcmp("04:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>4:30 am</option><option value="05:00:00" <?php if (!(strcmp("05:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>5:00 am</option><option value="05:30:00" <?php if (!(strcmp("05:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>5:30 am</option><option value="06:00:00" <?php if (!(strcmp("06:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>6:00 am</option><option value="06:30:00" <?php if (!(strcmp("06:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>6:30 am</option><option value="07:00:00" <?php if (!(strcmp("07:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>7:00 am</option><option value="07:30:00" <?php if (!(strcmp("07:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>7:30 am</option><option value="08:00:00" <?php if (!(strcmp("08:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>8:00 am</option><option value="08:30:00" <?php if (!(strcmp("08:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>8:30 am</option><option value="09:00:00" <?php if (!(strcmp("09:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>9:00 am</option><option value="09:30:00" <?php if (!(strcmp("09:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>9:30 am</option><option value="10:00:00" <?php if (!(strcmp("10:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>10:00 am</option><option value="10:30:00" <?php if (!(strcmp("10:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>10:30 am</option><option value="11:00:00" <?php if (!(strcmp("11:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>11:00 am</option><option value="11:30:00" <?php if (!(strcmp("11:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>11:30 am</option><option value="12:00:00" <?php if (!(strcmp("12:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>noon</option><option value="12:30:00" <?php if (!(strcmp("12:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>12:30 pm</option><option value="13:00:00" <?php if (!(strcmp("13:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>1:00 pm</option><option value="13:30:00" <?php if (!(strcmp("13:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>1:30 pm</option><option value="14:00:00" <?php if (!(strcmp("14:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>2:00 pm</option><option value="14:30:00" <?php if (!(strcmp("14:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>2:30 pm</option><option value="15:00:00" <?php if (!(strcmp("15:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>3:00 pm</option><option value="15:30:00" <?php if (!(strcmp("15:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>3:30 pm</option><option value="16:00:00" <?php if (!(strcmp("16:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>4:00 pm</option><option value="16:30:00" <?php if (!(strcmp("16:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>4:30 pm</option><option value="17:00:00" <?php if (!(strcmp("17:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>5:00 pm</option><option value="17:30:00" <?php if (!(strcmp("17:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>5:30 pm</option><option value="18:00:00" <?php if (!(strcmp("18:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>6:00 pm</option><option value="18:30:00" <?php if (!(strcmp("18:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>6:30 pm</option><option value="19:00:00" <?php if (!(strcmp("19:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>7:00 pm</option><option value="19:30:00" <?php if (!(strcmp("19:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>7:30 pm</option><option value="20:00:00" <?php if (!(strcmp("20:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>8:00 pm</option><option value="20:30:00" <?php if (!(strcmp("20:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>8:30 pm</option><option value="21:00:00" <?php if (!(strcmp("21:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>9:00 pm</option><option value="21:30:00" <?php if (!(strcmp("21:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>9:30 pm</option><option value="22:00:00" <?php if (!(strcmp("22:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>10:00 pm</option><option value="22:30:00" <?php if (!(strcmp("22:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>10:30 pm</option><option value="23:00:00" <?php if (!(strcmp("23:00:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>11:00 pm</option><option value="23:30:00" <?php if (!(strcmp("23:30:00", $row_classes['startTime']))) {echo "selected=\"selected\"";} ?>>11:30 pm</option>        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Ends:</td>
        <td><select name="endTime">
        <option value="00:00:00" <?php if (!(strcmp("00:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>midnight</option><option value="00:30:00" <?php if (!(strcmp("00:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>0:30 am</option><option value="01:00:00" <?php if (!(strcmp("01:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>1:00 am</option><option value="01:30:00" <?php if (!(strcmp("01:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>1:30 am</option><option value="02:00:00" <?php if (!(strcmp("02:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>2:00 am</option><option value="02:30:00" <?php if (!(strcmp("02:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>2:30 am</option><option value="03:00:00" <?php if (!(strcmp("03:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>3:00 am</option><option value="03:30:00" <?php if (!(strcmp("03:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>3:30 am</option><option value="04:00:00" <?php if (!(strcmp("04:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>4:00 am</option><option value="04:30:00" <?php if (!(strcmp("04:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>4:30 am</option><option value="05:00:00" <?php if (!(strcmp("05:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>5:00 am</option><option value="05:30:00" <?php if (!(strcmp("05:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>5:30 am</option><option value="06:00:00" <?php if (!(strcmp("06:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>6:00 am</option><option value="06:30:00" <?php if (!(strcmp("06:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>6:30 am</option><option value="07:00:00" <?php if (!(strcmp("07:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>7:00 am</option><option value="07:30:00" <?php if (!(strcmp("07:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>7:30 am</option><option value="08:00:00" <?php if (!(strcmp("08:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>8:00 am</option><option value="08:30:00" <?php if (!(strcmp("08:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>8:30 am</option><option value="09:00:00" <?php if (!(strcmp("09:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>9:00 am</option><option value="09:30:00" <?php if (!(strcmp("09:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>9:30 am</option><option value="10:00:00" <?php if (!(strcmp("10:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>10:00 am</option><option value="10:30:00" <?php if (!(strcmp("10:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>10:30 am</option><option value="11:00:00" <?php if (!(strcmp("11:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>11:00 am</option><option value="11:30:00" <?php if (!(strcmp("11:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>11:30 am</option><option value="12:00:00" <?php if (!(strcmp("12:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>noon</option><option value="12:30:00" <?php if (!(strcmp("12:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>12:30 pm</option><option value="13:00:00" <?php if (!(strcmp("13:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>1:00 pm</option><option value="13:30:00" <?php if (!(strcmp("13:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>1:30 pm</option><option value="14:00:00" <?php if (!(strcmp("14:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>2:00 pm</option><option value="14:30:00" <?php if (!(strcmp("14:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>2:30 pm</option><option value="15:00:00" <?php if (!(strcmp("15:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>3:00 pm</option><option value="15:30:00" <?php if (!(strcmp("15:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>3:30 pm</option><option value="16:00:00" <?php if (!(strcmp("16:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>4:00 pm</option><option value="16:30:00" <?php if (!(strcmp("16:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>4:30 pm</option><option value="17:00:00" <?php if (!(strcmp("17:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>5:00 pm</option><option value="17:30:00" <?php if (!(strcmp("17:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>5:30 pm</option><option value="18:00:00" <?php if (!(strcmp("18:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>6:00 pm</option><option value="18:30:00" <?php if (!(strcmp("18:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>6:30 pm</option><option value="19:00:00" <?php if (!(strcmp("19:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>7:00 pm</option><option value="19:30:00" <?php if (!(strcmp("19:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>7:30 pm</option><option value="20:00:00" <?php if (!(strcmp("20:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>8:00 pm</option><option value="20:30:00" <?php if (!(strcmp("20:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>8:30 pm</option><option value="21:00:00" <?php if (!(strcmp("21:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>9:00 pm</option><option value="21:30:00" <?php if (!(strcmp("21:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>9:30 pm</option><option value="22:00:00" <?php if (!(strcmp("22:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>10:00 pm</option><option value="22:30:00" <?php if (!(strcmp("22:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>10:30 pm</option><option value="23:00:00" <?php if (!(strcmp("23:00:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>11:00 pm</option><option value="23:30:00" <?php if (!(strcmp("23:30:00", $row_classes['endTime']))) {echo "selected=\"selected\"";} ?>>11:30 pm</option>        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Instructor:</td>
        <td><label for="instructorID"></label>
          <select name="instructorID" id="instructorID">
            <?php
do {  
?>
            <option value="<?php echo $row_instructors['instructorID']?>"<?php if (!(strcmp($row_instructors['instructorID'], $row_classes['instructorID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_instructors['lastName']?>, <?php echo $row_instructors['firstName']?></option>
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
        <td nowrap="nowrap" align="right">Class Active?</td>
        <td align="left"><input <?php if (!(strcmp($row_classes['classActive'],1))) {echo "checked=\"checked\"";} ?> name="classActive" type="checkbox" id="classActive" value="1" />
        <label for="classActive"></label></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Dropin Fee:</td>
        <td><input name="classFee" type="text" id="classFee" value="<?php echo htmlentities($row_classes['classFee'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Prepaid Fee:</td>
        <td><input name="prepaidFee" type="text" id="prepaidFee" value="<?php echo htmlentities($row_classes['prepaidFee'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Class Capacity:</td>
        <td><input name="classCapacity" type="text" id="classCapacity" value="<?php echo htmlentities($row_classes['classCapacity'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Studio:</td>
        <td><input type="text" name="studio" value="<?php echo htmlentities($row_classes['studio'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><input type="submit" value="Save Changes" /></td>
      </tr>
    </table>
    <input type="hidden" name="MM_update" value="form1" />
    <input type="hidden" name="classID" value="<?php echo $row_classes['classID']; ?>" />
  </form>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($classes);
?>