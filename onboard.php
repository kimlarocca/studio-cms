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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
  //check for duplicate username	
  $recordset = "SELECT studios.email,instructors.username,instructors.emailAddress FROM studios,instructors WHERE (studios.email LIKE '%".$_POST['email']."%' OR instructors.emailAddress LIKE '%".$_POST['email']."%' OR instructors.username LIKE '%".$_POST['email']."%')";
  mysql_select_db($database_wotg, $wotg);
  $result = mysql_query($recordset, $wotg) or die(mysql_error());
  $num_rows = mysql_num_rows($result);
  
  if($num_rows==0){
  $insertSQL = sprintf("INSERT INTO studios (studioName, studioContact, email, phone, salesRep, subscriptionLevel, subscriptionFee, url, studioTimezone) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['studioName'], "text"),
                       GetSQLValueString($_POST['studioContact2'].' '.$_POST['studioContact'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['phone'], "text"),
                       GetSQLValueString($_POST['salesRep'], "text"),
                       GetSQLValueString($_POST['subscriptionLevel'], "text"),
                       GetSQLValueString($_POST['subscriptionFee'], "int"),
                       GetSQLValueString($_POST['url'], "text"),
                       GetSQLValueString($_POST['studioTimezone'], "text"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());
  $id = mysql_insert_id();

  $insertGoTo = "onboard2.php?studioID=".$id;
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  //header(sprintf("Location: %s", $insertGoTo));
  echo '<script>window.location = "onboard2.php?studioID='.$id.'";</script>';
  }
}
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
<title>Slate It | Start Your Free Trial</title>
</head>

<body>
<div class="navLogo"><img src="images/logo-mobile.png" width="101" height="50" /></div>
<div class="logo twd_centered"><img src="images/logo.png" width="245" height="120" /></div>
<h1 class="twd_centered">Start Your Free Trial!</h1>
<div class="twd_container">
  <h2 class="twd_centered">Slate It Is Currently In Beta Testing</h2>
  <p class="twd_centered twd_margin20">Slate It is currently in beta testing, and will be FREE for anyone who wants to use it during this time. All we ask is that you report any issues and suggestions to us as you find them. Once beta testing is complete sometime in the 4th quarter of 2016, you can choose to keep using Slate It at our regular monthly fee of $29 (this price point is subject to change and may be lower or higher when we officially launch).</p><p class="twd_centered twd_margin40"><strong>Thank you for helping us make Slate It awesome!</strong></p>
  <h2 class="twd_centered">Step 1: Basic Business Info</h2>
  <div class="twd_centered twd_margin20"><img src="images/onboarding-step1.png" width="320" height="35" /></div>
  <p class="twd_centered twd_margin20">First, let's setup the basic info we need to get you up and running!</p>
  <?php if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && $num_rows>0){ ?>
  <p class="twd_centered twd_margin20 red">Sorry! That email address is already associated with a Slate It account!</p>
  <?php } 
  $url = 'http://';
  if ($_POST['url']!='') $url = $_POST['url'];
  ?>
  <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" class="twd_margin20">
    <table border="0" align="center" cellpadding="3" cellspacing="0">
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Business Name *</td>
        <td><input type="text" name="studioName" required="required" size="32" value="<?php echo $_POST['studioName']; ?>" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Business Email *</td>
        <td><input type="email" required="required" name="email" size="32" value="<?php echo $_POST['email']; ?>" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Business Phone</td>
        <td><input type="tel" name="phone" size="32" value="<?php echo $_POST['phone']; ?>" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Website URL *</td>
        <td><input type="url" name="url" size="32" title="please use the following format: http://www.example.com" required="required" value="<?php echo $url; ?>" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Your First Name *</td>
        <td><input type="text" name="studioContact2" required="required" size="32" value="<?php echo $_POST['studioContact2']; ?>" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Your Last Name *</td>
        <td><input type="text" name="studioContact" required="required" size="32" value="<?php echo $_POST['studioContact']; ?>" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Your Time Zone *</td>
        <td><select name="studioTimezone" id="studioTimezone">
          <option value="America/New_York" <?php if (!(strcmp("America/New_York", $_POST['studioTimezone']))) {echo "selected=\"selected\"";} ?>>Eastern (New York)</option>
          <option value="America/Chicago" <?php if (!(strcmp("America/Chicago", $_POST['studioTimezone']))) {echo "selected=\"selected\"";} ?>>Central (Chicago)</option>
          <option value="America/Denver" <?php if (!(strcmp("America/Denver", $_POST['studioTimezone']))) {echo "selected=\"selected\"";} ?>>Mountain (Denver)</option>
          <option value="America/Phoenix" <?php if (!(strcmp("America/Phoenix", $_POST['studioTimezone']))) {echo "selected=\"selected\"";} ?>>Mountain no DST (Phoenix)</option>
          <option value="America/Los_Angeles" <?php if (!(strcmp("America/Los_Angeles", $_POST['studioTimezone']))) {echo "selected=\"selected\"";} ?>>Pacific (Los Angeles)</option>
          <option value="America/Anchorage" <?php if (!(strcmp("America/Anchorage", $_POST['studioTimezone']))) {echo "selected=\"selected\"";} ?>>Alaska (Anchorage)</option>
          <option value="America/Adak" <?php if (!(strcmp("America/Adak", $_POST['studioTimezone']))) {echo "selected=\"selected\"";} ?>>Hawaii (Adak)</option>
          <option value="America/Honolulu" <?php if (!(strcmp("America/Honolulu", $_POST['studioTimezone']))) {echo "selected=\"selected\"";} ?>>Hawaii no DST (Honolulu)</option>
        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><input type="submit" value="Save &amp; Continue" /></td>
      </tr>
    </table>
    <input type="hidden" name="salesRep" value="Kim Henry" />
    <input type="hidden" name="subscriptionLevel" value="Basic" />
    <input type="hidden" name="subscriptionFee" value="0" />
    <input type="hidden" name="MM_insert" value="form1" />
  </form>
  <p class="twd_centered twd_margin20"><em>* required information</em></p>
</div>
<?php include("footer.php"); ?>
</body>
</html>