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

$errorMessage = FALSE;

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
 if($_POST['password']==$_POST['password2']){
  $insertSQL = sprintf("INSERT INTO instructors (firstName, lastName, emailAddress, username, password, active, securityLevel, studioID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['firstName'], "text"),
                       GetSQLValueString($_POST['lastName'], "text"),
                       GetSQLValueString($_POST['emailAddress'], "text"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['active'], "int"),
                       GetSQLValueString($_POST['securityLevel'], "text"),
                       GetSQLValueString($_POST['studioID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());

  $insertGoTo = "onboard5.php?studioID=".$_POST['studioID'];
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
 } else {
	$errorMessage = TRUE;	
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
  <h2 class="twd_centered">Step 3: Set Your Password</h2>
  <div class="twd_centered twd_margin20"><img src="images/onboarding-step3.png" width="320" height="35" /></div>
  <?php if ($errorMessage) { ?> <p class="red twd_centered twd_margin20">Your passwords do not match, please try again!</p><?php } ?>
  <p class="twd_centered twd_margin20">Choose the password you will use to login to the Slate It administration area.</p>
  <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" class="twd_margin20">
    <table border="0" align="center" cellpadding="3" cellspacing="0">
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Username:</td>
        <td><strong><?php echo $row_studio['email']; ?></strong></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Password *</td>
        <td><input type="password" name="password" size="32" pattern=".{8,}" required title="password must be a minimum of 8 characters" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Re-type Password *</td>
        <td><input type="password" name="password2" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><input type="submit" value="Save &amp; Continue" /></td>
      </tr>
    </table>
    <?php
	//get first & last name
	$fullname = explode(" ", $row_studio['studioContact']);
	$firstName = $fullname[0];
	$lastName = $fullname[1];
	?>
    <input type="hidden" name="firstName" value="<?php echo $firstName; ?>" />
    <input type="hidden" name="lastName" value="<?php echo $lastName; ?>" />
    <input type="hidden" name="emailAddress" value="<?php echo $row_studio['email']; ?>" />
    <input type="hidden" name="username" value="<?php echo $row_studio['email']; ?>" />
    <input type="hidden" name="active" value="1" />
    <input type="hidden" name="securityLevel" value="super" />
    <input type="hidden" name="studioID" value="<?php echo $row_studio['studioID']; ?>" />
    <input type="hidden" name="MM_insert" value="form1" />
  </form>
  <p class="twd_centered twd_margin20"><em>* password must be a minimum of 8 characters</em></p>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($studio);
?>