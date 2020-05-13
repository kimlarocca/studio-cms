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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE students SET firstName=%s, lastName=%s, emailAddress=%s, classesLeft=%s, waiver=%s, studentNotes=%s WHERE studentID=%s",
                       GetSQLValueString($_POST['firstName'], "text"),
                       GetSQLValueString($_POST['lastName'], "text"),
                       GetSQLValueString($_POST['emailAddress'], "text"),
                       GetSQLValueString($_POST['classesLeft'], "int"),
                       GetSQLValueString($_POST['waiver'], "int"),
                       GetSQLValueString($_POST['studentNotes'], "text"),
                       GetSQLValueString($_POST['studentID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "students-update.php?action=saved";
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

$colname_students = "-1";
if (isset($_GET['studentID'])) {
  $colname_students = $_GET['studentID'];
}
mysql_select_db($database_wotg, $wotg);
$query_students = sprintf("SELECT * FROM students WHERE studentID = %s", GetSQLValueString($colname_students, "int"));
$students = mysql_query($query_students, $wotg) or die(mysql_error());
$row_students = mysql_fetch_assoc($students);
$totalRows_students = mysql_num_rows($students);

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
<title><?php echo $row_currentUser['studioName']; ?> | Students</title>
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
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Students</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
  <h2 class="twd_centered">Update Student Information</h2>
  <div class="twd_centered twd_margin20"><a class="button" href="students-delete.php?studentID=<?php echo $row_students['studentID']; ?>"><img src="images/delete.png" width="20" height="20" /> Delete This Student</a>&nbsp;<a class="button" href="attendance-history.php?studentID=<?php echo $row_students['studentID']; ?>"><img src="images/view.png" width="20" height="20" /> View Attendance History</a>&nbsp;<a class="button" href="students-resetPassword.php?studentID=<?php echo $row_students['studentID']; ?>"><img src="images/edit.png" width="20" height="20" /> Reset Password</a></div>
  <?php
//check if changes were saved
if ($_GET['action'] == 'saved') print '<p class="twd_centered twd_margin20" style="color:red">Your changes have been saved!</p>'; 
?>
  <p class="twd_centered twd_margin20">Use the form below to update this student's information.</p>
  <form class="twd_margin20" action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" onsubmit="MM_validateForm('firstName','','R','lastName','','R','emailAddress','','NisEmail');return document.MM_returnValue">
    <table border="0" cellpadding="6" cellspacing="0" align="center">
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">First Name *</td>
        <td><input name="firstName" type="text" id="firstName" value="<?php echo htmlentities($row_students['firstName'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Last Name *</td>
        <td><input name="lastName" type="text" id="lastName" value="<?php echo htmlentities($row_students['lastName'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Email</td>
        <td><input name="emailAddress" type="text" id="emailAddress" value="<?php echo htmlentities($row_students['emailAddress'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Prepaid Classes Left</td>
        <td align="left"><input name="classesLeft" type="text" id="classesLeft" value="<?php echo htmlentities($row_students['classesLeft'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Waiver On File?</td>
        <td align="left"><input <?php if (!(strcmp($row_students['waiver'],1))) {echo "checked=\"checked\"";} ?> name="waiver" type="checkbox" id="waiver" value="1" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Signature</td>
        <td>
        <?php if($row_students['studentSignature']==''){ echo '<em>No signature on file.</em>'; } else { ?>
        <img src="data:<?php echo $row_students['studentSignature']; ?>" />
        <?php } ?>
        </td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Notes</td>
        <td>
        <textarea cols="45" rows="5" name="studentNotes" id="studentNotes"><?php echo $row_students['studentNotes']; ?></textarea></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><input type="submit" value="Save Changes" /></td>
      </tr>
    </table>
    <input type="hidden" name="MM_update" value="form1" />
    <input type="hidden" name="studentID" value="<?php echo $row_students['studentID']; ?>" />
  </form>
  <p class="twd_centered">* <em>required information</em></p>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($currentUser);
mysql_free_result($students);
?>