<?php
ob_start();
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

$colname_currentUser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_currentUser = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_currentUser = sprintf("SELECT * FROM instructors,studios WHERE instructors.studioID=studios.studioID AND instructors.username = %s", GetSQLValueString($colname_currentUser, "text"));
$currentUser = mysql_query($query_currentUser, $wotg) or die(mysql_error());
$row_currentUser = mysql_fetch_assoc($currentUser);
$totalRows_currentUser = mysql_num_rows($currentUser);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	
	
  $updateSQL = sprintf("UPDATE instructors SET firstName=%s, lastName=%s, emailAddress=%s, phoneNumber=%s, username=%s, password=%s, active=%s, securityLevel=%s, instructorBio=%s WHERE instructorID=%s",
                       GetSQLValueString($_POST['firstName'], "text"),
                       GetSQLValueString($_POST['lastName'], "text"),
                       GetSQLValueString($_POST['emailAddress'], "text"),
                       GetSQLValueString($_POST['phoneNumber'], "text"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['active'], "int"),
                       GetSQLValueString($_POST['securityLevel'], "text"),
                       GetSQLValueString($_POST['instructorBio'], "text"),
                       GetSQLValueString($_POST['instructorID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "instructors-update.php?action=saved";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$colname_instructors = "-1";
if (isset($_GET['instructorID'])) {
  $colname_instructors = $_GET['instructorID'];
}
mysql_select_db($database_wotg, $wotg);
$query_instructors = sprintf("SELECT * FROM instructors WHERE instructorID = %s", GetSQLValueString($colname_instructors, "int"));
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
<title><?php echo $row_currentUser['studioName']; ?> | Instructors</title>
<link rel="stylesheet" type="text/css" href="dropzone.css"/>
<script src="dropzone.js"></script>
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
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Instructors</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">

  <h2 class="twd_centered">Update Instructor</h2>
  <?php
  //check to be sure they aren't deleting the last instructor
	$conn = new mysqli($hostname_wotg, $username_wotg, $password_wotg, $database_wotg);
    if($mysqli->connect_errno){
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
	$result = $conn->query("SELECT count(*) FROM instructors WHERE active=1 AND securityLevel = 'administrator' AND studioID=".$row_currentUser['studioID']);
	$row = $result->fetch_row();
	$num = $row[0];
	
	if($num<=1 && $row_instructors['securityLevel']=='administrator'){
		echo "<p class='twd_centered twd_margin20' style='color:red'>Note: this is the only administrator active on this account, so you may not delete this instructor.</p>";
	} else {
		?>
  <div class="twd_centered twd_margin20">
  <a class="button" href="instructors.php?active=1&action=delete&instructorID=<?php echo $_GET['instructorID']; ?>"><img src="images/delete.png" width="20" height="20" /> Delete This Instructor</a></div><?php } ?>
  <p class="twd_centered twd_margin20">Fill out the form below to update the instructor's information.</p>
  <?php
//check if changes were saved
if ($_GET['action'] == 'saved') print '<p class="twd_centered twd_margin20" style="color:red">Your changes have been saved!</p>'; 
?>
<h3 class="twd_centered twd_margin20">instructor icon / pic</h3>
<p class="twd_centered twd_margin20">For best results, images should be a minimum of 200px wide by 200px tall and be in a square format. Max file upload size is 2MB.</p>
<div style="margin:auto; width:420px;">
<div class="image_upload_div" style="width:200px; height:200px; margin:auto; float:left">
    <form action="upload-instructors.php" class="dropzone" id="myAwesomeForm">
    	<input name="file_name" type="hidden" value="<?php echo $now; ?>" />
      <input name="instructorID" type="hidden" value="<?php echo $row_instructors['instructorID']; ?>" />
    </form></div>
<div style="width:200px; height:200px; margin:auto; padding:10px 0 0 20px; float:left">
 <?php if($row_instructors['thumbnail']!=''){ ?>
        <img height="200" width="200" src="uploads/<?php echo $row_instructors['thumbnail']; ?>" />
        <?php } else { ?>
        <img height="200" width="200" src="uploads/unavailable.gif" />
        <?php } ?>
        </div>
  </div>
  <div class="twd_clearfloat" style="padding-top:20px"></div> 
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" onsubmit="MM_validateForm('firstName','','R','lastName','','R','emailAddress','','NisEmail','username','','R','password','','R');return document.MM_returnValue">
  <table border="0" cellpadding="5" cellspacing="0" align="center">
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">First Name *</td>
      <td><input name="firstName" type="text" id="firstName" value="<?php echo htmlentities($row_instructors['firstName'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Last Name *</td>
      <td><input name="lastName" type="text" id="lastName" value="<?php echo htmlentities($row_instructors['lastName'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Email</td>
      <td><input name="emailAddress" type="text" id="emailAddress" value="<?php echo htmlentities($row_instructors['emailAddress'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Phone</td>
      <td><input type="text" name="phoneNumber" value="<?php echo htmlentities($row_instructors['phoneNumber'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Username *</td>
      <td><input name="username" type="text" id="username" value="<?php echo htmlentities($row_instructors['username'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Password *</td>
      <td><input name="password" type="text" id="password" value="<?php echo htmlentities($row_instructors['password'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Active?</td>
      <td align="left"><input <?php if (!(strcmp($row_instructors['active'],1))) {echo "checked=\"checked\"";} ?> name="active" type="checkbox" id="active" value="1" /></td>
      </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right"><a href="securityLevel.php">Security Level</a></td>
      <td>
        <select name="securityLevel" id="securityLevel">
          <option value="instructor" <?php if (!(strcmp("instructor", $row_instructors['securityLevel']))) {echo "selected=\"selected\"";} ?>>instructor</option>
          <?php if ($row_currentUser['securityLevel'] == 'super') { ?>
          <option value="administrator" <?php if (!(strcmp("administrator", $row_instructors['securityLevel']))) {echo "selected=\"selected\"";} ?>>administrator</option>
          <option value="super" <?php if (!(strcmp("super", $row_instructors['securityLevel']))) {echo "selected=\"selected\"";} ?>>super user</option>
          <?php } ?>
        </select></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" valign="top" align="right">Instructor Bio</td>
      <td><textarea name="instructorBio" rows="5" id="instructorBio" type="text" value="" size="32"><?php echo htmlentities($row_instructors['instructorBio'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><input type="submit" value="Save Changes" /></td>
    </tr>
  </table>
  <input type="hidden" name="MM_update" value="form1" />
  <input type="hidden" name="instructorID" value="<?php echo $row_instructors['instructorID']; ?>" />
</form>
<p class="twd_centered twd_margin20"><br />* <em>required information</em></em>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript">
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

mysql_free_result($instructors);
?>