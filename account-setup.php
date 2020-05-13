<?php
ob_start();
$today = $expiry_date = date("Y-m-d", strtotime("now"));
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

$colname_studio = "-1";
if (isset($_GET['studioID'])) {
  $colname_studio = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_studio = sprintf("SELECT * FROM studios WHERE studioID = %s", GetSQLValueString($colname_studio, "int"));
$studio = mysql_query($query_studio, $wotg) or die(mysql_error());
$row_studio = mysql_fetch_assoc($studio);
$totalRows_studio = mysql_num_rows($studio);

$colname_student = "-1";
if (isset($_GET['emailAddress'])) {
  $colname_student = $_GET['emailAddress'];
}
mysql_select_db($database_wotg, $wotg);
$query_student = sprintf("SELECT * FROM students WHERE emailAddress = %s", GetSQLValueString($colname_student, "text"));
$student = mysql_query($query_student, $wotg) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);

ini_set('session.save_path',getcwd(). '/../tmp/');
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
<title>Slate It | Account Setup</title>
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
<div class="logo twd_centered"><img src="images/logo.png" width="245" height="120" /></div>
<h1 class="twd_centered"><?php echo $row_studio['studioName']; ?> | Account Setup</h1>
<div class="twd_container">
  
  <?php
  
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
  $insertSQL = sprintf("INSERT INTO students (firstName, lastName, emailAddress, phonNumber, dateAdded, studioID) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['firstName'], "text"),
                       GetSQLValueString($_POST['lastName'], "text"),
                       GetSQLValueString($_POST['emailAddress'], "text"),
                       GetSQLValueString($_POST['phonNumber'], "text"),
                       GetSQLValueString($_POST['dateAdded'], "date"),
                       GetSQLValueString($_POST['studioID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());

  $insertGoTo = "account-setup.php?emailAddress=".$_GET['emailAddress'];;
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
  if($_POST['password']==$_POST['password2']){
	
  $updateSQL = sprintf("UPDATE students SET password=%s WHERE studentID=%s",
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['studentID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "student-login.php?action=setup&studioID=".$_POST['studioID']."&emailAddress=".$_POST['emailAddress'];
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
  }
  else {
	echo '<p style="color:red;" class="twd_centered twd_margin20">Passwords do not match! Please try again.</p>';
  }
}

if($_GET['studioID']=='') {
		echo '<p style="color:red;" class="twd_centered twd_margin20">Sorry, we could not find your studio, gym or fitness center. Please go back to your studio website and start over, or contact them for further assistance. Thank you!</p>';

} else {

if ($totalRows_student==0){
	//echo '<p style="color:red;" class="twd_centered twd_margin20">Sorry, the email address <strong>'.$_GET['emailAddress'].'</strong> was not found in our records!</p><p class="twd_centered">Please contact <strong>'.$row_studio['studioName'].'</strong> for more information, or use your back button to try again with a different address.</p>';
	
	//add student to database
	?>
    <P class="twd_centered twd_margin20">Welcome to <?php echo $row_studio['studioName']; ?>! Looks like it's your first time here. Please fill out the form below in order to get started:</P>
    <form action="<?php echo $editFormAction; ?>" method="post" name="form2" id="form2" onsubmit="MM_validateForm('firstName','','R','lastName','','R');return document.MM_returnValue" class="twd_margin20">
      <table border="0" align="center" cellpadding="3" cellspacing="0">
        <tr valign="baseline">
          <td nowrap="nowrap" align="right">First Name *</td>
          <td><input name="firstName" type="text" id="firstName" value="" size="32" /></td>
        </tr>
        <tr valign="baseline">
          <td nowrap="nowrap" align="right">Last Name *</td>
          <td><input name="lastName" type="text" id="lastName" value="" size="32" /></td>
        </tr>
        <tr valign="baseline">
          <td nowrap="nowrap" align="right">Phone Number</td>
          <td><input name="phonNumber" type="text" id="phonNumber" value="" size="32" /></td>
        </tr>
        <tr valign="baseline">
          <td nowrap="nowrap" align="right">&nbsp;</td>
          <td><input type="submit" value="Continue" /></td>
        </tr>
      </table>
      <input type="hidden" name="emailAddress" value="<?php echo $_GET['emailAddress']; ?>" />
      <input type="hidden" name="dateAdded" value="<?php echo $today ?>" />
      <input type="hidden" name="studioID" value="<?php echo $_GET['studioID']; ?>" />
      <input type="hidden" name="MM_insert" value="form2" />
  </form>
    <P class="twd_centered twd_margin20"><em>* required information</em></P>
<?php
}
else {
	if(isset($row_student['password'])){
	echo '<p style="color:red;" class="twd_centered twd_margin20">An account is already setup for the email address <strong>'.$_GET['emailAddress'].'</strong>!</p>
  <p class="twd_centered" style="padding-top:40px"><strong><a href="account-forgot-password.php?studioID='.$_GET['studioID'].'&emailAddress='.$_GET['emailAddress'].'">Did you forget your password?</a></strong></p>';
	}
	else {
?>
  <p class="twd_centered" style="padding-bottom:20px">Hello <?php echo $row_student['firstName']; ?> <?php echo $row_student['lastName']; ?>! </p>
  <p class="twd_centered" style="padding-bottom:20px"><strong>Please enter a password:</strong></p>
  <form class="twd_centered" action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" onsubmit="MM_validateForm('password','','R','password2','','R');return document.MM_returnValue">
    <p>Password:
      <input class="twd_centered" name="password" type="password" id="password" size="32" title="password is required" />
    </p>
    <p>Re-type Password:
      <input class="twd_centered" name="password2" type="password" id="password2" size="32" title="please re-type your password" />
    </p>
    <p>
      <input class="twd_centered" type="submit" value="Save Password" />
    </p>
    <input type="hidden" name="emailAddress" value="<?php echo $row_student['emailAddress']; ?>" />
    <input type="hidden" name="MM_update" value="form1" />
    <input type="hidden" name="studentID" value="<?php echo $row_student['studentID']; ?>" />
    <input type="hidden" name="studioID" value="<?php echo $_GET['studioID']; ?>" />
  </form>
<?php } } } ?>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($studio);

mysql_free_result($student);
?>
