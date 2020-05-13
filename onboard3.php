<?php require_once('Connections/wotg.php'); ?>
<?php
//update studio record
$updaterecord = "UPDATE studios SET accountVerified=1 WHERE dateAdded = '".$_GET['ID']."' AND studioID = ".$_GET['studioID'];
mysql_select_db($database_wotg, $wotg);
mysql_query($updaterecord, $wotg) or die(mysql_error());

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
$query_studio = sprintf("SELECT * FROM studios WHERE accountVerified=1 AND studioID = %s", GetSQLValueString($colname_studio, "int"));
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
  <h2 class="twd_centered">Email Verified</h2>
  <?php 
  if($totalRows_studio>0){
  ?>
  <p class="twd_centered twd_margin20">Thanks! Your account has been verified.</p>
  <?php
  } else {
  ?>
  <p class="twd_centered twd_margin20">We're sorry - your verification link was invalid. <a href="onboard.php">Please try again!</a></p>
  <?php } ?>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($studio);
?>