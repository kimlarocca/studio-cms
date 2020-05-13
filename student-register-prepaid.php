<?php require_once('Connections/wotg.php'); ?>
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
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
$query_orderInfo = "SELECT * FROM orders,classes WHERE orders.orderID = '".$_GET['orderID']."' AND orders.classID=classes.classID";
$orderInfo = mysql_query($query_orderInfo, $wotg) or die(mysql_error());
$row_orderInfo = mysql_fetch_assoc($orderInfo);
$totalRows_orderInfo = mysql_num_rows($orderInfo);

mysql_select_db($database_wotg, $wotg);
$query_student = "SELECT * FROM students WHERE studentID = ".$row_orderInfo['studentID'];
$student = mysql_query($query_student, $wotg) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);
?>
<?php
	//update order record
	$wotg = mysql_pconnect($hostname_wotg, $username_wotg, $password_wotg) or trigger_error(mysql_error(),E_USER_ERROR); 
	mysql_select_db($database_wotg, $wotg);
	$query_order = "UPDATE orders SET orderInfo = 'Pre Paid', orderStatus = 'complete' WHERE orderID='".$_GET['orderID']."'";
	$order = mysql_query($query_order, $wotg) or die(mysql_error());
	
	//update attendance records
	$addrecords = "INSERT INTO attendance(studentID, classID, instructorID, dateAdded, attendanceType, studioID) VALUES (".$row_orderInfo['studentID'].",".$row_orderInfo['classID'].",".$row_orderInfo['instructorID'].",'".$row_orderInfo['classDate']."','Pre Paid',".$row_orderInfo['studioID'].")";
	mysql_select_db($database_wotg, $wotg);
	mysql_query($addrecords, $wotg) or die(mysql_error());	

	//update student record
	$classesLeft = $row_student['classesLeft']-1;
	$wotg = mysql_pconnect($hostname_wotg, $username_wotg, $password_wotg) or trigger_error(mysql_error(),E_USER_ERROR); 
	mysql_select_db($database_wotg, $wotg);
	$query_order = "UPDATE students SET classesLeft = ".$classesLeft." WHERE studentID=".$row_orderInfo['studentID'];
	$order = mysql_query($query_order, $wotg) or die(mysql_error());
	
	header("Location: student-home.php?action=reserved");

mysql_free_result($orderInfo);
mysql_free_result($student);
?>