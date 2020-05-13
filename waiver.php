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

$colname_student = "-1";
if (isset($_GET['studentID'])) {
  $colname_student = $_GET['studentID'];
}
mysql_select_db($database_wotg, $wotg);
$query_student = sprintf("SELECT * FROM students,studios WHERE students.studioID=studios.studioID AND students.studentID = %s", GetSQLValueString($colname_student, "int"));
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
//update record
if ($_GET['action'] == 'save') {
    $insertrecord = "UPDATE students SET waiver = 1, studentSignature = '".$_POST['signatureInput']."' WHERE studentID = ".$_POST['studentID'];
    mysql_select_db($students, $wotg);
    mysql_query($insertrecord, $wotg) or die(mysql_error());
	echo '<h2 class="twd_centered">Thank you!</h2><p class="twd_margin20 twd_centered">If you would like a copy for your records, <a href="waiver-print.php?studioID=1">click here to view and print our waiver.</a></p>';
}
else {
?>

<?php echo $row_student['waiverCopy']; ?>

  <form id="form1" name="form1" method="post" action="?action=save&studentID=<?php echo $row_student['studentID']; ?>">
    <p><strong>Name: </strong><?php echo $row_student['firstName']; ?> <?php echo $row_student['lastName']; ?></p>
    <p><strong>Email Address: </strong><?php echo $row_student['emailAddress']; ?></p>
    <p><strong>Date: </strong><?php echo date("m/d/y"); ?></p>
    <?php if ($row_student['studentSignature']!='') { ?><p><strong>Current Signature:</strong> <img src="data:<?php echo $row_student['studentSignature']; ?>" /></p>
    <p><strong>Update Signature:</strong></p><?php } else { ?>
    <p><strong>Signature:</strong></p><?php } ?>
    <div id="signature"></div>
    <input id="signatureInput" name="signatureInput" type="hidden" value="" />
    <input id="studentID" name="studentID" type="hidden" value="<?php echo $row_student['studentID']; ?>" />
	<input type="submit" name="submit" id="submit" value="I Agree" />
    <!--<p class="twd_centered"><em>A copy of this waiver will be sent to the email address specified above.</em></p>-->
  </form>
  <?php
}
?>
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