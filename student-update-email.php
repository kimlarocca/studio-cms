<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
?>
<?php require_once('Connections/wotg.php'); ?>
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

$colname_emailAddresses = "-1";
if (isset($_POST['emailAddress'])) {
  $colname_emailAddresses = $_POST['emailAddress'];
}
mysql_select_db($database_wotg, $wotg);
$query_emailAddresses = sprintf("SELECT * FROM students WHERE emailAddress = %s", GetSQLValueString($colname_emailAddresses, "text"));
$emailAddresses = mysql_query($query_emailAddresses, $wotg) or die(mysql_error());
$row_emailAddresses = mysql_fetch_assoc($emailAddresses);
$totalRows_emailAddresses = mysql_num_rows($emailAddresses);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
if($totalRows_emailAddresses==0){
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE students SET emailAddress=%s WHERE studentID=%s",
                       GetSQLValueString($_POST['emailAddress'], "text"),
                       GetSQLValueString($_POST['studentID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "student-update-email.php?action=saved";
  //if (isset($_SERVER['QUERY_STRING'])) {
  //  $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
  //  $updateGoTo .= $_SERVER['QUERY_STRING'];
  //}
  header(sprintf("Location: %s", $updateGoTo));
}
} else {
  header(sprintf("Location: student-update-email.php?action=error&studentID=".$_GET['studentID']));
}

$colname_student = "-1";
if (isset($_GET['studentID'])) {
  $colname_student = $_GET['studentID'];
}
mysql_select_db($database_wotg, $wotg);
$query_student = sprintf("SELECT * FROM students WHERE studentID = %s", GetSQLValueString($colname_student, "int"));
$student = mysql_query($query_student, $wotg) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);
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
<title>Update Email Address</title>
</head>
<body>
<?php 
//get logo if it exists
if($row_student['logoURL']!=''){ 
?>
<div class="twd_centered twd_margin20" style="padding-top:20px; clear:both"><img src="uploads/<?php echo $row_student['logoURL']; ?>" /></div>
<?php 
}
?>
<div class="twd_container">
  <h2>Update Email Address</h2>
<?php
//check url parameters
if ($_GET['action'] == 'error') print '<p style="color:red;">That email address already exists! Please try again.</p>';
?>
  <p><?php echo $row_student['firstName']; ?> <?php echo $row_student['lastName']; ?></p>
<?php
if ($_GET['action'] == 'saved') print '<p>Your changes have been saved! <a href="#" onclick="javascript:closeMe();">Click here to close this window.</a></p>'; 
?>
  <form id="form1" name="form1" method="POST" action="<?php echo $editFormAction; ?>">
    <input name="emailAddress" type="text" value="<?php echo $row_student['emailAddress']; ?>" />
    <input name="submit" type="submit" value="Save Changes" />
    <input name="action" type="hidden" value="saved" />
    <input name="studentID" type="hidden" id="studentID" value="<?php echo $_GET['studentID']; ?>" />
    <input type="hidden" name="MM_update" value="form1" />
  </form>
</div>
<script>
function closeMe(){
	 window.opener.location.reload();
	 window.close();
}
</script>
</body>
</html>
<?php
mysql_free_result($student);

mysql_free_result($emailAddresses);
?>