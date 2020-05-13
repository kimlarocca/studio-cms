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
$query_student = sprintf("SELECT * FROM students WHERE studentID = %s", GetSQLValueString($colname_student, "int"));
$student = mysql_query($query_student, $wotg) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);

//add member to list
$addrecords = "INSERT INTO attendance(studentID, classID, instructorID, dateAdded, attendanceType) VALUES (".$_GET['studentID'].",".$_GET['classID'].",".$_GET['instructorID'].",'".$_GET['dateAdded']."','Member')";
mysql_select_db($database_wotg, $wotg);
mysql_query($addrecords, $wotg) or die(mysql_error());	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="styles.css"/>
<title>Wellness On The Green | Register Online</title>
</head>
<body>
<h1>Wellness On The Green: Online Registration</h1>
<div class="twd_container">
<h2>Step 4: Registration Complete</h2>
<p><strong>Thank you <?php echo $row_student['firstName']; ?>! Your spot has been reserved. </strong> We hope you have fun in class :)</p><br />
<p><a href="register-online.php">Click here to reserve a spot in another class &gt;&gt;</a></p>
<p><a href="http://www.wellnessonthegreen.com/">Click here to visit our website &gt;&gt;</a></p>

</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($student);

mysql_free_result($addrecords);
?>