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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form")) {
  $updateSQL = sprintf("UPDATE studios SET timesheetPayPeriod=%s, timesheetStartDate=%s WHERE studioID=%s",
                       GetSQLValueString($_POST['timesheetPayPeriod'], "text"),
                       GetSQLValueString($_POST['timesheetStartDate'], "date"),
                       GetSQLValueString($_POST['studioID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "timesheets-admin.php?action=saved";
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
<h2 class="twd_centered">Timesheet Entries <a id="payScheduleSetup" href="javascript:void();" class="tooltip" title="setup your pay schedule"><img src="images/settings3.png" width="22" height="22" /></a></h2>

<div id="paySchedule" style="display:none">
<h3 class="twd_centered twd_margin20">setup your pay schedule:</h3>
<?php
//check url parameters
if ($_GET['action'] == 'saved') print '<p style="color:red;" class="twd_centered twd_margin20">Your changes have been saved!</p>';
?>
<form name="form" action="<?php echo $editFormAction; ?>" method="POST" class="twd_margin20"><input name="studioID" type="hidden" value="<?php echo $row_currentUser['studioID']; ?>" />
<table width="100%" border="0" cellspacing="0" cellpadding="3" class="twd_centered" style="max-width:480px">
  <tr>
    <td>Pay Period:</td>
    <td><select name="timesheetPayPeriod"=>
  <!--<option value="weekly" <?php if (!(strcmp("weekly", $row_currentUser['timesheetPayPeriod']))) {echo "selected=\"selected\"";} ?>>Every Week</option>-->
  <option value="everyotherweek" <?php if (!(strcmp("everyotherweek", $row_currentUser['timesheetPayPeriod']))) {echo "selected=\"selected\"";} ?>>Every Other Week</option>
  <!--<option value="monthly" <?php if (!(strcmp("monthly", $row_currentUser['timesheetPayPeriod']))) {echo "selected=\"selected\"";} ?>>Monthly</option>-->
</select></td>
  </tr>
  <tr>
    <td>Start Date:</td>
    <td><input type="text" id="timesheetStartDate" name="timesheetStartDate" placeholder="<?php echo htmlentities($row_currentUser['timesheetStartDate'], ENT_COMPAT, 'UTF-8'); ?>" data-value="<?php echo htmlentities($row_currentUser['timesheetStartDate'], ENT_COMPAT, 'UTF-8'); ?>" value="<?php echo htmlentities($row_currentUser['timesheetStartDate'], ENT_COMPAT, 'UTF-8'); ?>"></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>
<input name="submit" type="submit" value="save changes"/>
<input type="hidden" name="MM_update" value="form" /></td>
  </tr>
</table>
</form>
</div>

<h3 class="twd_centered twd_margin20">time sheet entries for review:</h3>
  <div id="timeSheetPendingList"></div>

<h3 class="twd_centered twd_margin20">select a pay period:</h3>
<form action="timesheets-admin.php" method="get" class="twd_margin20">
 
 <select name="payPeriodStart" id="payPeriodStart" class="twd_centered twd_margin10">
 
<?php
//calculate dates - every 2 weeks
date_default_timezone_set($row_currentUser['studioTimezone']);
$endDate = date("Y-m-d", strtotime("now"));
$startDate = date("Y-m-d", strtotime($row_currentUser['timesheetStartDate']));
$nextDate = date('Y-m-d', strtotime($startDate. ' + 14 days'));
$payPeriods=array($startDate);

do {
array_push($payPeriods,$nextDate);
$nextDate = date('Y-m-d', strtotime($nextDate. ' + 14 days'));
} while ($nextDate <= $endDate);

//print_r($payPeriods);
$numberOfPayPeriods = count($payPeriods)-1;
$numberOfPayPeriods2 = count($payPeriods)-12;
$payPeriodStart = $payPeriods[$numberOfPayPeriods]; //default, set to current pay period
if($_GET['payPeriodStart']!='') $payPeriodStart=$_GET['payPeriodStart'];
if ($numberOfPayPeriods2 < 0) $numberOfPayPeriods2 = 0;
do {
	if($payPeriodStart == $payPeriods[$numberOfPayPeriods]) {
echo '<option selected value="'.$payPeriods[$numberOfPayPeriods].'">'.$payPeriods[$numberOfPayPeriods].'</option>';
	} else {
echo '<option value="'.$payPeriods[$numberOfPayPeriods].'">'.$payPeriods[$numberOfPayPeriods].'</option>';
		
	}
--$numberOfPayPeriods;
} while ($numberOfPayPeriods >= $numberOfPayPeriods2);
?> 
 </select>
<input name="submit" type="submit" value="submit" class="twd_centered" /></form>
<h3 class="twd_centered twd_margin20">time sheet entries:</h3>
  <div id="timeSheetList"></div>
</div>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript" src="http://code.jquery.com/ui/1.10.1/jquery-ui.min.js"></script> 
<script type="text/javascript">
$(document).ready(function(){
	$("#timeSheetList").load('timesheets-list.php?startDate=<?php echo $payPeriodStart ?>');
	$("#timeSheetPendingList").load('timesheets-list-pending.php');
	$("#payScheduleSetup").click(function() {
		$('#paySchedule').slideToggle();
	});
});
function reject(sheetID){
	jQuery.ajax({
	 type: "POST",
	 url: "timesheet-reject.php",
	 data: 'sheetID='+sheetID,
	 cache: false,
	 success: function(response)
	 {
	   $("#timeSheetPendingList").load('timesheets-list-pending.php');
	 }
   });
}
function approve(sheetID){
	jQuery.ajax({
	 type: "POST",
	 url: "timesheet-approve.php",
	 data: 'sheetID='+sheetID,
	 cache: false,
	 success: function(response)
	 {
	   $("#timeSheetPendingList").load('timesheets-list-pending.php');
	 }
   });
}
</script>
<script type="text/javascript" src="datePicker/picker.js"></script> 
<script>
/**
 * pick a date
 */
$('#timesheetStartDate').pickadate({
  onOpen: function() {
    scrollIntoView( this.$node )
  },
  format: 'yyyy-mm-dd',
formatSubmit: 'yyyy-mm-dd'
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
?>