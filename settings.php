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
	
	//check passwords
	if($_POST["password"]==$_POST["password2"]){
  $updateSQL = sprintf("UPDATE instructors SET firstName=%s, lastName=%s, emailAddress=%s, phoneNumber=%s, password=%s WHERE instructorID=%s",
                       GetSQLValueString($_POST['firstName'], "text"),
                       GetSQLValueString($_POST['lastName'], "text"),
                       GetSQLValueString($_POST['emailAddress'], "text"),
                       GetSQLValueString($_POST['phoneNumber'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['instructorID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "settings.php?action=saved";
  //if (isset($_SERVER['QUERY_STRING'])) {
  //  $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
  //  $updateGoTo .= $_SERVER['QUERY_STRING'];
  //}
  header(sprintf("Location: %s", $updateGoTo));
	}
else {
header("Location: settings.php?action=error");	
}
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form2")) {
  $updateSQL = sprintf("UPDATE studios SET studioName=%s, studioContact=%s, email=%s, phone=%s, url=%s, color=%s, allowUnpaidReservations=%s, messageStudents=%s, messageInstructors=%s, messangeAdmins=%s, reviews=%s, yelpLink=%s, googleLink=%s, facebookLink=%s, requireWaiver=%s, paymentGateway=%s, paymentGatewayID=%s, paymentGatewayKey=%s, studioTimezone=%s, enableSandbox=%s, enableMemberships=%s, allowStudentSelfCheckin=%s, membershipFee=%s WHERE studioID=%s",
                       GetSQLValueString($_POST['studioName'], "text"),
                       GetSQLValueString($_POST['studioContact'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['phone'], "text"),
                       GetSQLValueString($_POST['url'], "text"),
                       GetSQLValueString($_POST['color'], "text"),
                       GetSQLValueString(isset($_POST['allowUnpaidReservations']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['messageStudents'], "text"),
                       GetSQLValueString($_POST['messageInstructors'], "text"),
                       GetSQLValueString($_POST['messageAdmins'], "text"),
                       GetSQLValueString($_POST['reviews'], "text"),
                       GetSQLValueString($_POST['yelpLink'], "text"),
                       GetSQLValueString($_POST['googleLink'], "text"),
                       GetSQLValueString($_POST['facebookLink'], "text"),
                       GetSQLValueString(isset($_POST['requireWaiver']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['paymentGateway'], "text"),
                       GetSQLValueString($_POST['paymentGatewayID'], "text"),
                       GetSQLValueString($_POST['paymentGatewayKey'], "text"),
                       GetSQLValueString($_POST['studioTimezone'], "text"),
                       GetSQLValueString(isset($_POST['enableSandbox']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['enableMemberships']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['allowStudentSelfCheckin']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['membershipFee'], "int"),
                       GetSQLValueString($_POST['studioID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "settings.php?action=saved";
  //if (isset($_SERVER['QUERY_STRING'])) {
  //  $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
  //  $updateGoTo .= $_SERVER['QUERY_STRING'];
  //}
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

mysql_select_db($database_wotg, $wotg);
$query_currentStudio = "SELECT * FROM studios WHERE studioID = ".$row_currentUser['studioID'];
$currentStudio = mysql_query($query_currentStudio, $wotg) or die(mysql_error());
$row_currentStudio = mysql_fetch_assoc($currentStudio);
$totalRows_currentStudio = mysql_num_rows($currentStudio);
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
<link rel="stylesheet" type="text/css" href="dropzone.css"/>
<script src="dropzone.js"></script>
<title><?php echo $row_currentUser['studioName']; ?> | <?php echo $row_currentUser['firstName']; ?>'s Account Settings</title>
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

<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | <?php echo $row_currentUser['firstName']; ?>'s Account Settings</h1>
<?php
if ($row_currentUser['securityLevel'] == 'administrator' OR $row_currentUser['securityLevel'] == 'super') {
?>
<?php include("navigation.php"); ?>
<?php } ?>
<div class="twd_container">
  <h2 class="twd_centered">Update Your Personal Account Settings</h2>
<?php
//check if changes were saved
if ($_GET['action'] == 'saved') print '<p class="twd_centered twd_margin20" style="color:red; padding-top:0">Your changes have been saved!</p>'; 
if($_GET['action'] == 'error') print '<p class="twd_centered twd_margin20" style="color:red; padding-top:0">Passwords do not match!</p>';
?>
  <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" class="twd_margin20">
    <table border="0" align="center" cellpadding="3" cellspacing="0">
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Username</td>
        <td><?php echo $row_currentUser['username']; ?></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Password *</td>
        <td><input name="password" required type="password" id="password" value="<?php echo htmlentities($row_currentUser['password'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Retype Password *</td>
        <td><input name="password2" required type="password" id="password2" value="<?php echo htmlentities($row_currentUser['password'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">First Name *</td>
        <td><input name="firstName" required type="text" id="firstName" value="<?php echo htmlentities($row_currentUser['firstName'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Last Name *</td>
        <td><input name="lastName" required type="text" id="lastName" value="<?php echo htmlentities($row_currentUser['lastName'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Email Address</td>
        <td><input name="emailAddress" type="email" id="emailAddress" value="<?php echo htmlentities($row_currentUser['emailAddress'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Phone Number</td>
        <td><input type="text" name="phoneNumber" value="<?php echo htmlentities($row_currentUser['phoneNumber'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><input type="submit" value="Save Changes" /></td>
      </tr>
    </table>
    <input type="hidden" name="MM_update" value="form1" />
    <input type="hidden" name="instructorID" value="<?php echo $row_currentUser['instructorID']; ?>" />
  </form>
  
<?php
if ($row_currentUser['securityLevel'] == 'super') {
?>
  <h2 class="twd_centered" id="studioSettings">Update Your Studio Settings</h2>
  <h3 class="twd_centered twd_margin20">studio logo</h3>
<p class="twd_centered twd_margin20">For best results, images should be a minimum of 480px wide. Max file upload size is 2MB. <a href="settings-removeLogo.php?studioID=<?php echo $row_currentStudio['studioID']; ?>">Click here to remove your logo &gt;&gt;</a></p>
<div style="margin:auto; width:420px;">
<div class="image_upload_div" style="width:200px; height:200px; margin:auto; float:left">
    <form action="upload-logo.php" class="dropzone" id="myAwesomeForm">
    	<input name="file_name" type="hidden" value="<?php echo $now; ?>" />
      <input name="studioID" type="hidden" value="<?php echo $row_currentStudio['studioID']; ?>" />
    </form></div>
<div style="width:200px;  margin:auto; padding:10px 0 0 20px; float:left" id="logo">
 <?php if($row_currentStudio['logoURL']!=''){ ?>
        <img src="uploads/<?php echo $row_currentStudio['logoURL']; ?>" />
        <?php } else { ?>
        <img height="200" width="200" src="uploads/unavailable.gif" />
        <?php } ?>
    </div>
  </div>
  <div class="twd_clearfloat" style="padding-top:20px"></div> 
  <form action="<?php echo $editFormAction; ?>" method="post" name="form2" id="form2" onsubmit="MM_validateForm('studioName','','R','studioContact','','R','timezone','','R','email','','NisEmail');return document.MM_returnValue" class="twd_margin20">
  
    <h3 class="twd_centered twd_margin20"><br />Studio Details</h3>
    <p class="twd_centered twd_margin20"><a class="button" href="resources.php">Slate It Resources &amp; Widgets</a></p>
    <table border="0" align="center" cellpadding="3" cellspacing="0">
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Studio Name *</td>
        <td><input type="text" name="studioName" required value="<?php echo htmlentities($row_currentStudio['studioName'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right"> Contact Person *</td>
        <td><input type="text" name="studioContact" required value="<?php echo htmlentities($row_currentStudio['studioContact'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Studio Email Address *</td>
        <td><input type="email" name="email" required value="<?php echo htmlentities($row_currentStudio['email'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Studio Phone</td>
        <td><input type="text" name="phone" value="<?php echo htmlentities($row_currentStudio['phone'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Timezone *</td>
        <td><label for="studioTimezone"></label>
          <select name="studioTimezone" id="studioTimezone">
            <option value="America/New_York" <?php if (!(strcmp("America/New_York", $row_currentStudio['studioTimezone']))) {echo "selected=\"selected\"";} ?>>America/New_York</option>
            <option value="America/Chicago" <?php if (!(strcmp("America/Chicago", $row_currentStudio['studioTimezone']))) {echo "selected=\"selected\"";} ?>>America/Chicago</option>
            <option value="America/Denver" <?php if (!(strcmp("America/Denver", $row_currentStudio['studioTimezone']))) {echo "selected=\"selected\"";} ?>>America/Denver</option>
            <option value="America/Phoenix" <?php if (!(strcmp("America/Phoenix", $row_currentStudio['studioTimezone']))) {echo "selected=\"selected\"";} ?>>America/Phoenix</option>
            <option value="America/Los_Angeles" <?php if (!(strcmp("America/Los_Angeles", $row_currentStudio['studioTimezone']))) {echo "selected=\"selected\"";} ?>>America/Los_Angeles</option>
            <option value="America/Anchorage" <?php if (!(strcmp("America/Anchorage", $row_currentStudio['studioTimezone']))) {echo "selected=\"selected\"";} ?>>America/Anchorage</option>
            <option value="America/Adak" <?php if (!(strcmp("America/Adak", $row_currentStudio['studioTimezone']))) {echo "selected=\"selected\"";} ?>>America/Adak</option>
            <option value="America/Honolulu" <?php if (!(strcmp("America/Honolulu", $row_currentStudio['studioTimezone']))) {echo "selected=\"selected\"";} ?>>America/Honolulu</option>
        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Studio Website URL</td>
        <td><input type="text" name="url" value="<?php echo htmlentities($row_currentStudio['url'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Enable Digital Waivers?</td>
        <td><input <?php if (!(strcmp($row_currentStudio['requireWaiver'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="requireWaiver" id="requireWaiver" style="float:left" /> 
          <a href="waiver-copy.php">&nbsp;&nbsp;update your waiver &gt;&gt;
          <label for="requireWaiver"></label>
          </a></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Enable student self check-in?</td>
        <td><input <?php if (!(strcmp($row_currentStudio['allowStudentSelfCheckin'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="allowStudentSelfCheckin" id="allowStudentSelfCheckin" />
        <label for="allowStudentSelfCheckin"></label></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Choose Your Accent Color **</td>
        <?php 
		$color = '#70adc7';
		$colorFont = '#ffffff';
		if ($row_currentStudio['color']!='') $color = $row_currentStudio['color'];
		if ($row_currentStudio['colorFont']!='') $colorFont = $row_currentStudio['colorFont'];
		?>
        <td><input type="text" maxlength="6" size="6" name="color" id="color" value="<?php echo $color; ?>" style="background-color:<?php echo $color; ?>"></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Message To Students:</td>
        <td><textarea name="messageStudents" rows="5"><?php echo htmlentities($row_currentStudio['messageStudents'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Message to Instructors:</td>
        <td><textarea name="messageInstructors" rows="5"><?php echo htmlentities($row_currentStudio['messageInstructors'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Message to 4 &amp; 5 Star Reviewers:</td>
        <td><textarea name="reviews" rows="5"><?php echo htmlentities($row_currentStudio['reviews'], ENT_COMPAT, 'UTF-8'); ?></textarea></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Yelp Link (for reviews):</td>
        <td><input type="url" name="yelpLink" value="<?php echo htmlentities($row_currentStudio['yelpLink'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Google Link (for reviews):</td>
        <td><input type="url" name="googleLink" value="<?php echo htmlentities($row_currentStudio['googleLink'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Facebook Link (for reviews):</td>
        <td><input type="url" name="facebookLink" value="<?php echo htmlentities($row_currentStudio['facebookLink'], ENT_COMPAT, 'UTF-8'); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><input type="submit" value="Save Changes" /></td>
      </tr>
      <tr valign="baseline">
        <td colspan="2" align="right" nowrap="nowrap" style="background-color:#e0e0e0"><h3 class="twd_centered twd_margin20"><br />Payment Details</h3></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Enable  memberships?</td>
        <td><input <?php if (!(strcmp($row_currentStudio['enableMemberships'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="enableMemberships" id="enableMemberships" />
          <label for="allowStudentSelfCheckin2"></label></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Membership Fee</td>
        <td><input name="membershipFee" type="number" id="membershipFee" value="<?php echo htmlentities($row_currentStudio['membershipFee']); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Allow students to register without paying?</td>
        <td><input <?php if (!(strcmp($row_currentStudio['allowUnpaidReservations'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="allowUnpaidReservations" id="allowUnpaidReservations" />
          <label for="allowUnpaidReservations"></label></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Payment Gateway</td>
        <td><label for="paymentGateway"></label>
          <select name="paymentGateway" id="paymentGateway">
            <option value="PayPal" <?php if (!(strcmp("PayPal", $row_currentStudio['paymentGateway']))) {echo "selected=\"selected\"";} ?>>PayPal</option>
        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Client ID / API Login ID</td>
        <td><input name="paymentGatewayID" type="text" id="paymentGatewayID" value="<?php echo htmlentities($row_currentStudio['paymentGatewayID']); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Client Secret / Transaction Key</td>
        <td><input name="paymentGatewayKey" type="text" id="paymentGatewayKey" value="<?php echo htmlentities($row_currentStudio['paymentGatewayKey']); ?>" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Enable Sandbox/Test Mode</td>
        <td><input <?php if (!(strcmp($row_currentStudio['enableSandbox'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="enableSandbox" id="enableSandbox" />
        <label for="enableSandbox"></label></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><input type="submit" value="Save Changes" /></td>
      </tr>
    </table>
    <input type="hidden" name="MM_update" value="form2" />
    <input type="hidden" name="studioID" value="<?php echo $row_currentStudio['studioID']; ?>" />
  </form>
<?php } ?>
 
  <p class="twd_centered twd_margin20"><em>* required information<br />
    <br />
** this color will help brand your student's online account area - make sure you choose a color that is dark enough for white text to be readable on top of it!<br />
<br />
<a href="student-login.php?studioID=<?php echo $row_currentStudio['studioID']; ?>">click here to view your student login page</a></em></p>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript" src="jqColorPicker.min.js"></script>
<script>
    $('#color').colorPicker();
    $('#colorFont').colorPicker();
Dropzone.autoDiscover = false;
$(function() {
  var myDropzone = new Dropzone("#myAwesomeForm");
  myDropzone.on("queuecomplete", function(file) {
		//location.reload();
		$('#logo').load(document.URL +  ' #logo'); 
  });
})
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($currentStudio);
?>