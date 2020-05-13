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

mysql_select_db($database_wotg, $wotg);
$query_wotg = "SELECT * FROM studios WHERE studioID = ".$_GET['studioID'];
$wotg = mysql_query($query_wotg, $wotg) or die(mysql_error());
$row_wotg = mysql_fetch_assoc($wotg);
$totalRows_wotg = mysql_num_rows($wotg);
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
<title><?php echo $row_wotg['studioName']; ?> |  Waiver</title>
<?php
//get colors
$color = '#70adc7';
$colorFont = '#ffffff';
if ($row_wotg['color']!='') $color = $row_wotg['color'];
if ($row_wotg['colorFont']!='') $colorFont = $row_wotg['colorFont'];
?>
<style>
h1 { background-color:<?php echo $color; ?>; color:<?php echo $colorFont; ?>; }
h2 { color:<?php echo $color; ?>; }
a, a:visited, a:active { color:<?php echo $color; ?>; }
.button {
	color: <?php echo $color; ?>!important;
	border: 2px solid <?php echo $color; ?>;
}
</style>
</head>
<body>
<h1 class="twd_centered"><?php echo $row_wotg['studioName']; ?> |  Waiver</h1>
<div class="twd_container">
<?php echo $row_wotg['waiverCopy']; ?>
</div>
</body>
</html>
<?php
mysql_free_result($wotg);
?>