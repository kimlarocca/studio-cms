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
  $insertSQL = sprintf("INSERT INTO classCancellations (studioID, cancellationDate, classID, cancelAllClasses) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['studioID'], "int"),
                       GetSQLValueString($_POST['cancellationDate'], "date"),
                       GetSQLValueString($_POST['classID'], "int"),
                       GetSQLValueString(isset($_POST['cancelAllClasses']) ? "true" : "", "defined","1","0"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());

  $insertGoTo = "cancellations.php?action=added";
  //if (isset($_SERVER['QUERY_STRING'])) {
  //  $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
  //  $insertGoTo .= $_SERVER['QUERY_STRING'];
  //}
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

mysql_select_db($database_wotg, $wotg);
$query_cancellations = "SELECT * FROM classCancellations,classes WHERE classCancellations.cancellationDate >= CURDATE() AND classCancellations.studioID = ".$row_currentUser['studioID']." AND classes.classID=classCancellations.classID ORDER BY classCancellations.cancellationDate ASC";
$cancellations = mysql_query($query_cancellations, $wotg) or die(mysql_error());
$row_cancellations = mysql_fetch_assoc($cancellations);
$totalRows_cancellations = mysql_num_rows($cancellations);

mysql_select_db($database_wotg, $wotg);
$query_classes = "SELECT * FROM classes WHERE classActive=1 AND studioID = ".$row_currentUser['studioID']." ORDER BY FIELD(classDay , 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), startTime";
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);
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
<title><?php echo $row_currentUser['studioName']; ?> | Cancellations</title>
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Cancellations</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
  
      <h2 class="twd_centered">Manage Your Cancellations&nbsp;&nbsp;<a id="addClass" href="javascript:void();" class="tooltip" title="add a new cancellation"><img src="images/plus.png" width="22" height="22" /></a></h2
><?php
//check url parameters
if ($_GET['action'] == 'added') print '<p class="twd_centered twd_margin20" style="color:red;">Your cancellation has been added!</p>';
if ($_GET['action'] == 'delete') print '<p class="twd_centered twd_margin20" style="color:red;">Your cancellation has been deleted!</p>';
?>


<div id="addClassForm" class="twd_margin20">
<h3 class="twd_centered twd_margin20">Fill out the form below to add a new cancellation!</h3>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" class="twd_margin20">
  <table border="0" align="center" cellpadding="5" cellspacing="0">
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Cancellation Date:</td>
      <td><input type="text" name="cancellationDate" id="cancellationDate" value="" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Cancel All Classes On This Date?</td>
      <td><input type="checkbox" name="cancelAllClasses" value="" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Or Cancel This Specific Class:</td>
      <td><select name="classID">
        <?php 
do {  
?>
        <option value="<?php echo $row_classes['classID']?>" ><?php echo $row_classes['classDay']?> <?php echo date('g:i A', strtotime($row_classes['startTime']))?>: <?php echo $row_classes['name']?></option>
        <?php
} while ($row_classes = mysql_fetch_assoc($classes));
?>
        </select></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><input type="submit" value="Add Cancellation" /></td>
    </tr>
  </table>
  <input type="hidden" name="studioID" value="<?php echo $row_currentUser['studioID']; ?>" />
  <input type="hidden" name="MM_insert" value="form1" />
</form>
</div>
<h3 class="twd_centered twd_margin20">upcoming CLASS cancellations</h3>
<?php if($totalRows_cancellations==0){?>
<p class="twd_centered">You have no upcoming cancellations. Add a new cancellation by clicking the plus + icon above.</p>
<?php } else { ?>
<table border="0" cellpadding="5" cellspacing="0" align="center">
  <tr>
    <td><strong>Date</strong></td>
    <td><strong>Class</strong></td>
    <td>&nbsp;</td>
  </tr>
  <?php do { ?>
    <tr>
      <td><?php echo date( 'm/d/Y', strtotime($row_cancellations['cancellationDate'])); ?></td>
      <td><?php if($row_cancellations['cancelAllClasses']==1){
		  echo 'All Classes Cancelled';
	  } else {
		  echo $row_cancellations['name']; 
	  }?></td>
      <td><a class="tooltip" title="delete this cancellation" href="cancellations-delete.php?cancellationID=<?php echo $row_cancellations['cancellationID']; ?>"><img src="images/delete.png" width="22" height="22" /></a></td>
    </tr>
    <?php } while ($row_cancellations = mysql_fetch_assoc($cancellations)); } ?>
</table>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript" src="datePicker/picker.js"></script> 
<script>
/**
 * pick a date
 */
$('#cancellationDate').pickadate({
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

mysql_free_result($cancellations);

mysql_free_result($classes);
?>