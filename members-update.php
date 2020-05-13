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
  $updateSQL = sprintf("UPDATE members SET startDate=%s, endDate=%s, membershipFee=%s WHERE membershipID=%s",
                       GetSQLValueString($_POST['startDate'], "date"),
                       GetSQLValueString($_POST['endDate'], "date"),
                       GetSQLValueString($_POST['membershipFee'], "int"),
                       GetSQLValueString($_POST['membershipID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "members-update.php?action=saved";
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

$colname_member = "-1";
if (isset($_GET['membershipID'])) {
  $colname_member = $_GET['membershipID'];
}
mysql_select_db($database_wotg, $wotg);
$query_member = sprintf("SELECT * FROM members WHERE membershipID = %s", GetSQLValueString($colname_member, "int"));
$member = mysql_query($query_member, $wotg) or die(mysql_error());
$row_member = mysql_fetch_assoc($member);
$totalRows_member = mysql_num_rows($member);

mysql_select_db($database_wotg, $wotg);
$query_student = "SELECT * FROM students WHERE studentID = ".$row_member['studentID'];
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
<title><?php echo $row_currentUser['studioName']; ?> | Members</title>
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
<div class="navLogo"><img src="images/logo-mobile.png" width="101" height="50" /></div>
<div class="logo twd_centered"><img src="images/logo.png" width="245" height="120" /></div>
<div class="nav"> <a class="navItem iconLinks tooltip2" title="home page" href="home.php"><img src="images/home2.png" /></a> <a class="navItem iconLinks tooltip2" title="update your account"  href="settings.php"><img src="images/settings2.png" /></a> </div>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Members</h1>
<div class="twd_container">
  <h2 class="twd_centered">Update Membership for <em><?php echo $row_student['firstName']; ?> <?php echo $row_student['lastName']; ?></em></h2>
  <div class="twd_centered twd_margin20"><a class="button" href="members.php?action=delete&amp;membershipID=<?php echo $_GET['membershipID']; ?>"><img src="images/delete.png" width="20" height="20" /> Delete This Member</a></div>
  <p class="twd_centered twd_margin20">Fill out the form below to update this membership's start date, end date and fee information.</p>
  <?php
//delete record
if ($_GET['action'] == 'saved') {
    print '<p class="twd_centered twd_margin20" style="color:red;">Membership information has been updated!</p>';
}
?>
  <form class="twd_margin20" action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" onsubmit="MM_validateForm('startDate','','R','endDate','','R','membershipFee','','NisNum');return document.MM_returnValue">
    <table border="0" align="center" cellpadding="3" cellspacing="0">
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Student ID:</td>
        <td><?php echo $row_member['studentID']; ?></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Start Date *</td>
        <td><input type="text" name="startDate" id="startDate" placeholder="<?php echo htmlentities($row_member['startDate'], ENT_COMPAT, 'UTF-8'); ?>" value="<?php echo htmlentities($row_member['startDate'], ENT_COMPAT, 'UTF-8'); ?>" data-value="<?php echo htmlentities($row_member['startDate'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">End Date *</td>
        <td><input type="text" name="endDate" id="endDate" placeholder="<?php echo htmlentities($row_member['endDate'], ENT_COMPAT, 'UTF-8'); ?>" value="<?php echo htmlentities($row_member['endDate'], ENT_COMPAT, 'UTF-8'); ?>" data-value="<?php echo htmlentities($row_member['endDate'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Membership Fee</td>
        <td><input name="membershipFee" type="text" id="membershipFee" value="<?php echo htmlentities($row_member['membershipFee'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><input type="submit" value="Save Changes" /></td>
      </tr>
    </table>
    <input type="hidden" name="MM_update" value="form1" />
    <input type="hidden" name="membershipID" value="<?php echo $row_member['membershipID']; ?>" />
</form>
<p class="twd_centered twd_margin20"><em>* required information</em></p>
  <p class="twd_centered twd_margin20"><a href="members.php">&lt;&lt; go back to the Members page</a></p>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script> 
<script type="text/javascript" src="datePicker/picker.js"></script> 
<script>
/**
 * pick a date
 */
$('#startDate').pickadate({
  onOpen: function() {
    scrollIntoView( this.$node )
  },
        format: 'yyyy-mm-dd',
        formatSubmit: 'yyyy-mm-dd',
});
$('#endDate').pickadate({
  onOpen: function() {
    scrollIntoView( this.$node )
  },
        format: 'yyyy-mm-dd',
        formatSubmit: 'yyyy-mm-dd',
		//onStart: function() {
        //  this.set('select', <?php echo ($row_member['endDate']); ?>); // Set to current date on load
        //}
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

mysql_free_result($member);

mysql_free_result($student);
?>