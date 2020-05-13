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

$colname_currentUser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_currentUser = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_currentUser = sprintf("SELECT * FROM instructors WHERE username = %s", GetSQLValueString($colname_currentUser, "text"));
$currentUser = mysql_query($query_currentUser, $wotg) or die(mysql_error());
$row_currentUser = mysql_fetch_assoc($currentUser);
$totalRows_currentUser = mysql_num_rows($currentUser);

$colname_attendance = "-1";
if (isset($_GET['attendanceID'])) {
  $colname_attendance = $_GET['attendanceID'];
}
mysql_select_db($database_wotg, $wotg);
$query_attendance = sprintf("SELECT * FROM attendance WHERE attendanceID = %s", GetSQLValueString($colname_attendance, "int"));
$attendance = mysql_query($query_attendance, $wotg) or die(mysql_error());
$row_attendance = mysql_fetch_assoc($attendance);
$totalRows_attendance = mysql_num_rows($attendance);
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
<title>Delete Attendance Record</title>
</head>
<body>
<div class="twd_container"><h2>Delete Attendance Record</h2>
<?php
if ($_GET['action'] == 'delete') { 
	$deleterecords = "DELETE FROM attendance WHERE attendanceID = ".$_GET['attendanceID'];
	mysql_select_db($query_attendance, $wotg);
	mysql_query($deleterecords, $wotg) or die(mysql_error());
	if ($_GET['credit'] == 'true') { 
		$creditstudent = "UPDATE students SET classesLeft=classesLeft+1 WHERE studentID = ".$_GET['studentID'];
		mysql_select_db($query_attendance, $wotg);
		mysql_query($creditstudent, $wotg) or die(mysql_error());
		print '<p>The student account has been credited with 1 class.</p>'; 
	}
	print '<p>The attendance record has been deleted!<br><br><a href="#" onclick="javascript:closeMe();">Click here to close this window.</a></p>'; 
}
else { 
?>
<p>Are you sure you want to delete this attendance record? This cannot be undone!</p>
  <form id="form1" name="form1" method="get" action="attendance-delete.php">
    <input name="submit" type="submit" value="Delete Now" /><input name="action" type="hidden" value="delete" /><input name="studentID" type="hidden" value="<?php echo $row_attendance['studentID']; ?>" />
        <input name="attendanceID" type="hidden" id="attendanceID" value="<?php echo $_GET['attendanceID']; ?>" />

  </form>
  <form id="form1" name="form1" method="get" action="attendance-delete.php">
    <input name="submit" type="submit" value="Delete & Credit Account" /><input name="action" type="hidden" value="delete" /><input name="credit" type="hidden" value="true" /><input name="studentID" type="hidden" value="<?php echo $row_attendance['studentID']; ?>" />
        <input name="attendanceID" type="hidden" id="attendanceID" value="<?php echo $_GET['attendanceID']; ?>" />

  </form>
<?php
}
?>
</div>
<script>
function closeMe(){
	 window.opener.location.reload();
	 window.close();
}
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($attendance);
?>