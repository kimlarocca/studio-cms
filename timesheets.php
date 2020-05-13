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

if (isset($_POST["MM_insert"]) && $_POST["entryDate"]=="") {
$messageText = "Please select a date! ";	
}
if (isset($_POST["MM_insert"]) && !is_numeric($_POST['entryHours'])) {
$messageText = $messageText."Hours must be a valid number! ";	
}
if (isset($_POST["MM_insert"]) && preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$_POST["entryDate"])){	
  if (is_numeric($_POST['entryHours']) && $_POST["entryDate"]!="" && (isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	  $messageText = "Your entry has been added.";
	$insertSQL = sprintf("INSERT INTO timeSheets (instructorID, entryDate, entryHours, lastModifiedBy, entryStatus) VALUES (%s, %s, %s, %s, %s)",
						 GetSQLValueString($_POST['instructorID'], "int"),
						 GetSQLValueString($_POST['entryDate'], "date"),
						 GetSQLValueString($_POST['entryHours'], "double"),
						 GetSQLValueString($_POST['lastModifiedBy'], "text"),
						 GetSQLValueString($_POST['entryStatus'], "text"));
  
	mysql_select_db($database_wotg, $wotg);
	$Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());
  }
} else{
	$messageText = "Date must be in YYYY-MM-DD format!";
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
$query_timesheet = "SELECT * FROM timeSheets WHERE entryStatus IS NULL AND instructorID = ".$row_currentUser['instructorID']." ORDER BY entryDate DESC";
$timesheet = mysql_query($query_timesheet, $wotg) or die(mysql_error());
$row_timesheet = mysql_fetch_assoc($timesheet);
$totalRows_timesheet = mysql_num_rows($timesheet);

mysql_select_db($database_wotg, $wotg);
$query_timesheet1 = "SELECT * FROM timeSheets WHERE entryStatus='submitted' AND instructorID = ".$row_currentUser['instructorID']." ORDER BY entryDate DESC";
$timesheet1 = mysql_query($query_timesheet1, $wotg) or die(mysql_error());
$row_timesheet1 = mysql_fetch_assoc($timesheet1);
$totalRows_timesheet1 = mysql_num_rows($timesheet1);

mysql_select_db($database_wotg, $wotg);
$query_timesheet2 = "SELECT * FROM timeSheets WHERE entryStatus='approved' AND instructorID = ".$row_currentUser['instructorID']." ORDER BY entryDate DESC";
$timesheet2 = mysql_query($query_timesheet2, $wotg) or die(mysql_error());
$row_timesheet2 = mysql_fetch_assoc($timesheet2);
$totalRows_timesheet2 = mysql_num_rows($timesheet2);

mysql_select_db($database_wotg, $wotg);
$query_timesheet3 = "SELECT * FROM timeSheets WHERE entryStatus='rejected' AND instructorID = ".$row_currentUser['instructorID']." ORDER BY entryDate DESC";
$timesheet3 = mysql_query($query_timesheet3, $wotg) or die(mysql_error());
$row_timesheet3 = mysql_fetch_assoc($timesheet3);
$totalRows_timesheet3 = mysql_num_rows($timesheet3);

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
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Timesheets</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
          <?php if ($row_currentUser['securityLevel'] == 'super') { ?>
<p class="twd_centered twd_margin20"><a class="button" href="timesheets-admin.php">timesheet administration</a></p>
          <?php } ?>
  <h2 class="twd_centered">Add a New Entry</h2>
  <?php if ($messageText!=''){  echo '<p class="twd_centered twd_margin20" style="color:red">'.$messageText.'</p>';} else { ?>
  <?php if ($_GET['action']=='deleted') echo '<p class="twd_centered twd_margin20" style="color:red">Your entry has been deleted.</p>'; ?>
  <?php if ($_GET['action']=='submitted') echo '<p class="twd_centered twd_margin20" style="color:red">Your timesheet has been submitted for approval.</p>'; } ?>
  <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" class="twd_centered">
    <input class="twd_centered" type="date" name="entryDate" id="entryDate" value="" size="32" required="required" placeholder="Select a Date" />
    <br /><input class="twd_centered" required="required" name="entryHours" value="" size="32" placeholder="Number of Hours" />
    <br /><input class="twd_centered" type="submit" value="Add Entry" />
    <input type="hidden" name="instructorID" value="<?php echo $row_currentUser['instructorID']; ?>" />
    <input type="hidden" name="entryStatus" value="submitted" />
    <input type="hidden" name="lastModifiedBy" value="<?php echo $row_currentUser['username']; ?>" />
    <input type="hidden" name="MM_insert" value="form1" />
  </form>
  <p>&nbsp;</p>
<?php 
  if ($totalRows_timesheet>0 OR $totalRows_timesheet1>0 OR $totalRows_timesheet3>0){ ?>
<h2 class="twd_centered">Current Timesheet Entries</h2>
<?php 
  }
  if ($totalRows_timesheet>0){ ?>
<table border="0" align="center" cellpadding="3" cellspacing="0" class="twd_margin20">
  <tr>
    <td><strong>Entry Date</strong></td>
    <td><strong>Hours</strong></td>
    <td colspan="2"><strong>Last Modified By</strong></td>
    <td>&nbsp;</td>
  </tr>
  <?php 
  $totalHours = 0;
  do { 
$phpdate = strtotime( $row_timesheet['dateAdded'] );
$mysqldate = date( 'm/d/Y H:ia', $phpdate );
$totalHours = $totalHours+$row_timesheet['entryHours'];
  ?>
    <tr>
      <td><?php echo date('m/d/Y', strtotime($row_timesheet['entryDate'])); ?></td>
      <td><?php echo $row_timesheet['entryHours']; ?></td>
      <td><?php echo $row_timesheet['lastModifiedBy']; ?></td>
      <td><em><?php echo $mysqldate; ?></em></td>
      <td><a class="tooltip" title="update this record" href="timesheets-update.php?sheetID=<?php echo $row_timesheet['sheetID']; ?>"><img src="images/edit.png" alt="" width="20" height="20"/></a> <a class="tooltip" title="delete this record" href="timesheets-delete.php?sheetID=<?php echo $row_timesheet['sheetID']; ?>"><img src="images/delete.png" width="20" height="20" /></a></td>
    </tr>
    <?php } while ($row_timesheet = mysql_fetch_assoc($timesheet)); ?>
</table>
<p class="twd_margin20 twd_centered">Total Hours: <?php echo $totalHours; ?></p>
<p class="twd_centered twd_margin20"><a class="button" href="timesheets-submit.php">submit hours</a></p>
<?php } ?>
<?php 
  if ($totalRows_timesheet1>0){ ?>
<h3 class="twd_centered twd_margin20">Not Yet Approved</h3>
<p class="twd_centered twd_margin20">Below are your timesheet entries that have been submitted but are not yet approved:</p>
<table border="0" align="center" cellpadding="3" cellspacing="0" class="twd_margin20">
  <tr>
    <td><strong>Entry Date</strong></td>
    <td><strong>Hours</strong></td>
    <td colspan="2"><strong>Last Modified By</strong></td>
  </tr>
  <?php 
  do { 
$phpdate1 = strtotime( $row_timesheet1['dateAdded'] );
$mysqldate1 = date( 'm/d/Y H:ia', $phpdate1 );
  ?>
    <tr>
      <td><?php echo date('m/d/Y', strtotime($row_timesheet1['entryDate'])); ?></td>
      <td><?php echo $row_timesheet1['entryHours']; ?></td>
      <td><?php echo $row_timesheet1['lastModifiedBy']; ?></td>
      <td><em><?php echo $mysqldate1; ?></em></td>
    </tr>
    <?php } while ($row_timesheet1 = mysql_fetch_assoc($timesheet1)); ?></table>
<?php } 
  if ($totalRows_timesheet3>0){?>
<h3 class="twd_centered twd_margin20">Rejected</h3>
<p class="twd_centered twd_margin20">Below are your timesheet entries that have been rejected. Please update them and re-submit!</p>
<table border="0" align="center" cellpadding="3" cellspacing="0" class="twd_margin20">
  <tr>
    <td><strong>Entry Date</strong></td>
    <td><strong>Hours</strong></td>
    <td colspan="2"><strong>Last Modified By</strong></td>
    <td>&nbsp;</td>
  </tr>
  <?php 
  do { 
$phpdate3 = strtotime( $row_timesheet3['dateAdded'] );
$mysqldate3 = date( 'm/d/Y H:ia', $phpdate3 );
  ?>
    <tr>
      <td><?php echo date('m/d/Y', strtotime($row_timesheet3['entryDate'])); ?></td>
      <td><?php echo $row_timesheet3['entryHours']; ?></td>
      <td><?php echo $row_timesheet3['lastModifiedBy']; ?></td>
      <td><em><?php echo $mysqldate3; ?></em></td>
      <td><a class="tooltip" title="update this record" href="timesheets-update.php?sheetID=<?php echo $row_timesheet3['sheetID']; ?>"><img src="images/edit.png" alt="" width="20" height="20"/></a> <a class="tooltip" title="delete this record" href="timesheets-delete.php?sheetID=<?php echo $row_timesheet3['sheetID']; ?>"><img src="images/delete.png" width="20" height="20" /></a></td>
    </tr>
    <?php } while ($row_timesheet3 = mysql_fetch_assoc($timesheet3)); ?>
    </table>
<p class="twd_centered twd_margin20"><a class="button" href="timesheets-submit-rejected.php">re-submit rejected hours</a></p>
<?php } ?>
<p>&nbsp;</p>
<h2 class="twd_centered">Approved Timesheet History</h2>
<?php 
  if ($totalRows_timesheet2==0){
	  echo '<p class="twd_centered">You have no approved timesheet entries.</p>';
  } else { ?>
<table border="0" align="center" cellpadding="3" cellspacing="0" class="twd_margin20">
  <tr>
    <td><strong>Entry Date</strong></td>
    <td><strong>Hours</strong></td>
    <td colspan="2"><strong>Last Modified By</strong></td>
  </tr>
  <?php 
  do { 
$phpdate2 = strtotime( $row_timesheet2['dateAdded'] );
$mysqldate2 = date( 'm/d/Y H:ia', $phpdate2 );
  ?>
    <tr>
      <td><?php echo date('m/d/Y', strtotime($row_timesheet2['entryDate'])); ?></td>
      <td><?php echo $row_timesheet2['entryHours']; ?></td>
      <td><?php echo $row_timesheet2['lastModifiedBy']; ?></td>
      <td><em><?php echo $mysqldate2; ?></em></td>
    </tr>
    <?php } while ($row_timesheet2 = mysql_fetch_assoc($timesheet2)); ?>
    </table>
<?php } ?>
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