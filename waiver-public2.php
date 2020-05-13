<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
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

$colname_student = "-1";
if (isset($_GET['studentID'])) {
  $colname_student = $_POST['studentID'];
}
mysql_select_db($database_wotg, $wotg);
$query_student = "SELECT * FROM studios WHERE studios.studioID = ".$_POST['studioID'];
$student = mysql_query($query_student, $wotg) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);

$emailAddress = $_POST['emailAddress'];
$lastName = $_POST['lastName'];
$firstName = $_POST['firstName'];
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
<title><?php echo $row_student['studioName']; ?> |  Waiver</title>
<?php
//get colors
$color = '#70adc7';
$colorFont = '#ffffff';
if ($row_student['color']!='') $color = $row_student['color'];
if ($row_student['colorFont']!='') $colorFont = $row_student['colorFont'];
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
<h1 class="twd_centered"><?php echo $row_student['studioName']; ?> |  Waiver</h1>
<div class="twd_container">
<?php
//add record
if ($_GET['action'] == 'save') {
    $insertrecord = "INSERT INTO students (dateAdded,waiver,studentSignature,studioID,lastName,firstName,emailAddress) VALUES ('".$_POST['dateAdded']."',1,'".$_POST['signatureInput']."',".$_POST['studioID'].",'".$_POST['lastName']."','".$_POST['firstName']."','".$_POST['emailAddress']."')";
    mysql_select_db($database_wotg, $wotg);
    mysql_query($insertrecord, $wotg) or die(mysql_error());
	echo '<h2 class="twd_centered">Thank you!</h2><p class="twd_margin20 twd_centered">Enjoy your class!</p>';
}
else {
?>

<?php echo $row_student['waiverCopy']; ?>

  <form id="form1" name="form1" method="post" action="?action=save">
    <p><strong>Name: </strong><?php echo $_POST['firstName']; ?> <?php echo $_POST['lastName']; ?></p>
    <p><strong>Email Address: </strong><?php echo $_POST['emailAddress']; ?></p>
    <p><strong>Date: </strong><?php echo date("m/d/y"); ?></p>
    <p><strong>Signature:</strong></p>
    <div id="signature"></div>
    <input id="signatureInput" name="signatureInput" type="hidden" value="" />
    <input id="studioID" name="studioID" type="hidden" value="<?php echo $_POST['studioID']; ?>" />
    <input id="emailAddress" name="emailAddress" type="hidden" value="<?php echo $_POST['emailAddress']; ?>" />
    <input id="lastName" name="lastName" type="hidden" value="<?php echo $_POST['lastName']; ?>" />
    <input id="firstName" name="firstName" type="hidden" value="<?php echo $_POST['firstName']; ?>" />
    <input type="hidden" name="dateAdded" value="<?php echo date("y-m-d"); ?>" />
	<input type="submit" name="submit" id="submit" value="I Agree" />
  </form>
  <?php } ?>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="jSignature.min.js"></script>
<script>
$(document).ready(function() {
	$("#signature").jSignature();
});
$("#signature").bind('change', function(e){ 
	// Get signature as SVG, updates after each stroke
	var datapair = $("#signature").jSignature("getData", "svgbase64");
	// save SVG value
	$("#signatureInput").val(datapair);
});
</script>
</body>
</html>
<?php
mysql_free_result($student);
?>