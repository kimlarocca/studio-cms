<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
?>
<?php require_once('Connections/wotg.php'); ?><?php
$today = $_GET['datePicked'];
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

mysql_select_db($database_wotg, $wotg);
$query_currentMembers = "SELECT * FROM members,students WHERE members.studentID = students.studentID AND '".$today."' between members.startDate AND members.endDate";
$currentMembers = mysql_query($query_currentMembers, $wotg) or die(mysql_error());
$row_currentMembers = mysql_fetch_assoc($currentMembers);
$totalRows_currentMembers = mysql_num_rows($currentMembers);

$colname_class = "-1";
if (isset($_GET['classID'])) {
  $colname_class = $_GET['classID'];
}
mysql_select_db($database_wotg, $wotg);
$query_class = sprintf("SELECT * FROM classes WHERE classID = %s", GetSQLValueString($colname_class, "int"));
$class = mysql_query($query_class, $wotg) or die(mysql_error());
$row_class = mysql_fetch_assoc($class);
$totalRows_class = mysql_num_rows($class);
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
  <h2>Step 3: Dropin, Class Card or Member?</h2>
<?php
 $formattedDate = date("m/d/Y", strtotime($_GET['datePicked']));
 ?>
<p>Class: <strong><?php echo $row_class['name']; ?></strong></p>
<p>Date:&nbsp; <strong><?php echo $formattedDate; ?></strong></p>
</div>
<hr />
<div class="twd_container">
<h2>DROPINS</h2>
<p>Use the PayPal button below to complete your reservation:</p>
<br />
<h2>CLASS CARDS</h2>  
<p>We don't currently have the ability for class card students to reserve spots online. Please feel free to text 862.485.1233 with your full name and the class, date and time to reserve your spot!</p>
<h2>MEMBERS</strong></h2>
<?php 
  if($totalRows_currentMembers==0) { 
  echo '<p>No members found on that date!</p>'; 
  } 
  else { 
  echo '<p>If you are will be a current member on the date you selected, your name will be displayed below. Please click on your name to reserve your spot:</p>';
  //echo '<p>Click on any member name to add:</p>';
 ?>
  <table border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td><strong>member name</strong></td>
      <td><strong>start date</strong></td>
      <td><strong>end date</strong></td>
    </tr>
      <?php do { ?>
    <tr>
      <td><a href="register-online-member.php?instructorID=<?php echo $row_class['instructorID']; ?>&studentID=<?php echo $row_currentMembers['studentID']; ?>&amp;classID=<?php echo $_GET['classID']; ?>&amp;dateAdded=<?php echo $_GET['datePicked']; ?>"><?php echo $row_currentMembers['lastName']; ?>, <?php echo $row_currentMembers['firstName']; ?></a></td>
      <td><?php echo $row_currentMembers['startDate']; ?></td>
      <td><?php echo $row_currentMembers['endDate']; ?></td>
    </tr>
    <?php } while ($row_currentMembers = mysql_fetch_assoc($currentMembers)); } ?>
  </table>
  
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($currentMembers);

mysql_free_result($class);
?>