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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO eventAttendance (eventID, dateAdded, studioID, studentID, amountPaid, paymentType) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['eventID'], "int"),
                       GetSQLValueString($_POST['dateAdded'], "date"),
                       GetSQLValueString($_POST['studioID'], "int"),
                       GetSQLValueString($_POST['studentID'], "int"),
                       GetSQLValueString($_POST['amountPaid'], "int"),
                       GetSQLValueString($_POST['paymentType'], "text"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());

  $insertGoTo = "events-update.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
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

$_SESSION['studioID'] = $row_currentUser['studioID'];

$colname_event = "-1";
if (isset($_GET['eventID'])) {
  $colname_event = $_GET['eventID'];
}
mysql_select_db($database_wotg, $wotg);
$query_event = sprintf("SELECT * FROM events WHERE eventID = %s", GetSQLValueString($colname_event, "int"));
$event = mysql_query($query_event, $wotg) or die(mysql_error());
$row_event = mysql_fetch_assoc($event);
$totalRows_event = mysql_num_rows($event);
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
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/minified/jquery-ui.min.css" type="text/css" />
<title><?php echo $row_currentUser['studioName']; ?> | Manually Add An Event Registrant</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script> 
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Manually Add An Event Registrant</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
  <h2 class="twd_centered twd_margin20"><?php echo $row_event['eventName']; ?></h2>
  <p class="twd_centered twd_margin20">Fill out the form below to manually add a registrant to this event!</p>
  <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
    <table align="center">
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Student:</td>
        <td>
        <input type="text" autocomplete="off" name="student" class="auto" id="student" required="required"  />
        <input type="hidden" id="studentID" name="studentID" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Amount Paid:</td>
        <td><input type="number" name="amountPaid" value="0" required="required" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Payment Type:</td>
        <td><select name="paymentType" id="paymentType">
          <option selected="selected" value="None">None</option>
          <option value="Cash">Cash</option>
          <option value="Check">Check</option>
          <option value="PayPal">PayPal</option>
          <option value="Credit Card">Credit Card</option>
        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><input type="submit" value="Add Registrant" /></td>
      </tr>
    </table>
    <input type="hidden" name="eventID" value="<?php echo $row_event['eventID']; ?>" />
    <input type="hidden" name="dateAdded" value="<?php echo $row_event['eventDate']; ?>" />
    <input type="hidden" name="studioID" value="<?php echo $row_event['studioID']; ?>" />
    <input type="hidden" name="MM_insert" value="form1" />
  </form>
  <p>&nbsp;</p>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript" src="https://code.jquery.com/ui/1.10.1/jquery-ui.min.js"></script> 
<script type="text/javascript">
$(function() {	
	//autocomplete
	$(".auto").autocomplete({
		source: "search.php",
		minLength: 1, //search after 1 character
      select: function(event,ui){
		  $("#studentID").attr("value",ui.item.studentID);
      }
	});				
});
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($event);
?>