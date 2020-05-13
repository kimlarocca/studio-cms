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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE timeSheets SET firstName=%s, lastName=%s, entryDate=%s, entryHours=%s, lastModifiedBy=%s WHERE sheetID=%s",
                       GetSQLValueString($_POST['firstName'], "text"),
                       GetSQLValueString($_POST['lastName'], "text"),
                       GetSQLValueString($_POST['entryDate'], "date"),
                       GetSQLValueString($_POST['entryHours'], "double"),
                       GetSQLValueString($_POST['lastModifiedBy'], "text"),
                       GetSQLValueString($_POST['sheetID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "timesheets-update.php?action=saved";
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

$colname_timesheet = "-1";
if (isset($_GET['sheetID'])) {
  $colname_timesheet = $_GET['sheetID'];
}
mysql_select_db($database_wotg, $wotg);
$query_timesheet = sprintf("SELECT * FROM timeSheets,instructors WHERE timeSheets.instructorID=instructors.instructorID AND timeSheets.sheetID = %s", GetSQLValueString($colname_timesheet, "int"));
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
<title><?php echo $row_currentUser['studioName']; ?> | Timesheets</title>
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
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Timesheets</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
<h2 class="twd_centered">Update Timesheet</h2>
<?php if($_GET['action']=='saved') echo '<p class="twd_centered twd_margin20" style="color:red">Your changes have been saved.</p>'; ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" onsubmit="MM_validateForm('entryDate','','R','entryHours','','RisNum');return document.MM_returnValue" class="twd_margin20">
  <table border="0" align="center" cellpadding="3" cellspacing="0">
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><?php echo $row_timesheet['firstName']; ?> <?php echo $row_timesheet['lastName']; ?></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Name:</td>
      <td><input name="firstName" type="text" id="firstName" placeholder="<?php echo htmlentities($row_timesheet['firstName'], ENT_COMPAT, 'UTF-8'); ?>" data-value="<?php echo htmlentities($row_timesheet['firstName'], ENT_COMPAT, 'UTF-8'); ?>" value="<?php echo htmlentities($row_timesheet['firstName'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><input name="lastName" type="text" id="lastName" placeholder="<?php echo htmlentities($row_timesheet['lastName'], ENT_COMPAT, 'UTF-8'); ?>" data-value="<?php echo htmlentities($row_timesheet['lastName'], ENT_COMPAT, 'UTF-8'); ?>" value="<?php echo htmlentities($row_timesheet['lastName'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Entry Date:</td>
      <td><input name="entryDate" type="text" id="entryDate" placeholder="<?php echo htmlentities($row_timesheet['entryDate'], ENT_COMPAT, 'UTF-8'); ?>" data-value="<?php echo htmlentities($row_timesheet['entryDate'], ENT_COMPAT, 'UTF-8'); ?>" value="<?php echo htmlentities($row_timesheet['entryDate'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Hours:</td>
      <td><input name="entryHours" type="text" required="required" id="entryHours" value="<?php echo htmlentities($row_timesheet['entryHours'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><input type="submit" value="Save Changes" /></td>
    </tr>
  </table>
  <input type="hidden" name="lastModifiedBy" value="<?php echo htmlentities($row_currentUser['username']); ?>" />
  <input type="hidden" name="MM_update" value="form1" />
  <input type="hidden" name="sheetID" value="<?php echo $row_timesheet['sheetID']; ?>" />
</form>
<p class="twd_centered"><a href="timesheets.php">&lt;&lt; back to the timesheets page</a></p>
</div>
<?php include("footer.php"); ?><script type="text/javascript" src="datePicker/picker.js"></script> 
<script>
/**
 * pick a date
 */
$('#entryDate').pickadate({
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

mysql_free_result($timesheet);
?>