<?php require_once('Connections/wotg.php'); ?>
<?php
$today = $expiry_date = date("Y-m-d", strtotime("now"));
?>
<?php
//get day of the week selected
$month = date('m', strtotime($_GET['datePicked']));
$day = date('d', strtotime($_GET['datePicked']));
$year = date('Y', strtotime($_GET['datePicked']));
$dateArray=getdate(mktime(0,0,0,$month,$day,$year));
$dayPicked = $dateArray[weekday];
?>
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

$MM_restrictGoTo = "student-login.php?action=failed";
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

$colname_student = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_student = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_student = sprintf("SELECT * FROM students,studios WHERE emailAddress = %s AND students.studioID=studios.studioID", GetSQLValueString($colname_student, "text"));
$student = mysql_query($query_student, $wotg) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);

mysql_select_db($database_wotg, $wotg);
$query_classes = "SELECT * FROM classes WHERE studioID = ".$row_student['studioID']." AND classDay = '".$dayPicked."' AND classActive = 1 ORDER BY startTime ASC";
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);

$colname_attendance = "-1";
if (isset($_GET['datePicked'])) {
  $colname_attendance = $_GET['datePicked'];
}
ini_set('session.save_path',getcwd(). '/../tmp/');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="styles.css"/>
<title><?php echo $row_student['studioName']; ?> | My Account</title>
</head>

<body>
<?php include("student-header.php"); ?>
<h1 class="twd_centered studentH1"><?php echo $row_student['studioName']; ?> | Register Online</h1>
<div class="twd_container">
  <h2>Choose a Class</h2>
<?php
if($today > $_GET['datePicked']){
	echo '<p class="red">Sorry - you cannot choose a date in the past!</p>';
}
else {
 $formattedDate = date("m/d/Y", strtotime($_GET['datePicked']));
 ?>
 <form action="student-register-date2.php" method="get" class="twd_margin40">
   <p>Date Selected: <strong><?php echo $formattedDate ?> </strong></p>
 
 
     <select style="width:auto; max-width:100%" name="classID">
       <?php
do { 
$formattedTime = date("g:i a", strtotime($row_classes['startTime']));
?>
       <option value="<?php echo $row_classes['classID']?>"><?php echo $formattedTime?>: <?php echo $row_classes['name']?></option>
       <?php
} while ($row_classes = mysql_fetch_assoc($classes));
  $rows = mysql_num_rows($classes);
  if($rows > 0) {
      mysql_data_seek($classes, 0);
	  $row_classes = mysql_fetch_assoc($classes);
  }
?>
     </select>
     <br />
     <input name="Continue" type="submit" value="Continue" /><input name="datePicked" type="hidden" value="<?php echo $_GET['datePicked']; ?>" />
 </form>
 <?php } ?>
 
  <h2>Classes on <?php echo $formattedDate; ?></h2>
  <table border="0" cellpadding="3" cellspacing="0">
    <?php do { 
	$formattedTime = date("g:i a", strtotime($row_classes['startTime']));
	?>
      <tr>
        <td><strong><a href="student-register-date2.php?datePicked=<?php echo $_GET['datePicked']; ?>&classID=<?php echo $row_classes['classID']; ?>"><?php echo $row_classes['name']; ?></a></strong></td>
        <td><?php echo $formattedTime; ?></td>
        <td><?php 
		
		mysql_select_db($database_wotg, $wotg);
		$query_attendance = "SELECT * FROM attendance WHERE dateAdded = '".$_GET['datePicked']."' AND classID=".$row_classes['classID'];
		$attendance = mysql_query($query_attendance, $wotg) or die(mysql_error());
		$row_attendance = mysql_fetch_assoc($attendance);
		$totalRows_attendance = mysql_num_rows($attendance);
		
		if($row_classes['classCapacity']==$totalRows_attendance){
			echo '<span class="red">This class is full!</span>';
		} else {
		echo ($row_classes['classCapacity']-$totalRows_attendance).' spots left!'; 
		}
		?></td>
        <td>&nbsp;</td>
      </tr>
      <?php } while ($row_classes = mysql_fetch_assoc($classes)); ?>
  </table>
<p><a href="student-home.php">&lt;&lt; start over &amp; pick a new date</a></p>
 </div>
</body>
</html>
<?php
mysql_free_result($student);

mysql_free_result($attendance);

mysql_free_result($classes);
?>
