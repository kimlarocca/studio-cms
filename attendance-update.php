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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE attendance SET attendanceType=%s, paymentType=%s WHERE attendanceID=%s",
                       GetSQLValueString($_POST['attendanceType'], "text"),
                       GetSQLValueString($_POST['paymentType'], "text"),
                       GetSQLValueString($_POST['attendanceID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($updateSQL, $wotg) or die(mysql_error());

  $updateGoTo = "attendance-update.php?action=saved";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
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
<title>Update Attendance Record</title>
</head>
<body>
<div class="twd_container"><h2>Update Attendance Record</h2>
<?php
if ($_GET['action'] == 'saved') print '<p>Your changes have been saved! <a href="#" onclick="javascript:closeMe();">Click here to close this window.</a></p>'; 
?>
  <form id="form1" name="form1" method="POST" action="<?php echo $editFormAction; ?>">
    <table width="0" border="0" cellspacing="0" cellpadding="5">
      <tr>
        <td>Select One:
          <select name="attendanceType" id="attendanceType">
          <option selected="selected" value="" <?php if (!(strcmp("", $row_attendance['attendanceType']))) {echo "selected=\"selected\"";} ?>> </option>
          <option value="Drop In" <?php if (!(strcmp("Drop In", $row_attendance['attendanceType']))) {echo "selected=\"selected\"";} ?>>Drop In</option>
          <option value="Pre Paid" <?php if (!(strcmp("Pre Paid", $row_attendance['attendanceType']))) {echo "selected=\"selected\"";} ?>>Pre Paid</option>
        </select></td>
      </tr>
      <tr>
        <td>Payment Type:
          <select name="paymentType" id="paymentType">
          <option selected="selected" value="" <?php if (!(strcmp("", $row_attendance['paymentType']))) {echo "selected=\"selected\"";} ?>> </option>
          <option value="Cash" <?php if (!(strcmp("Cash", $row_attendance['paymentType']))) {echo "selected=\"selected\"";} ?>>Cash</option>
          <option value="Check" <?php if (!(strcmp("Check", $row_attendance['paymentType']))) {echo "selected=\"selected\"";} ?>>Check</option>
          <option value="PayPal" <?php if (!(strcmp("PayPal", $row_attendance['paymentType']))) {echo "selected=\"selected\"";} ?>>PayPal</option>
          <option value="Credit Card" <?php if (!(strcmp("Credit Card", $row_attendance['paymentType']))) {echo "selected=\"selected\"";} ?>>Credit Card</option>
        </select></td>
      </tr>
      <tr>
        <td><input name="attendanceID" type="hidden" id="attendanceID" value="<?php echo $_GET['attendanceID']; ?>" />          <input name="submit" type="submit" value="Save Changes" /></td>
      </tr>
    </table>
    <input type="hidden" name="MM_update" value="form1" />
  </form>
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