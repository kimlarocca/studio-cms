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
//submitted only
$query_timesheet = "SELECT * FROM timeSheets,instructors WHERE timeSheets.instructorID=instructors.instructorID AND timeSheets.entryStatus = 'submitted' ORDER BY timeSheets.instructorID, timeSheets.entryDate DESC";
//submitted or rejected
//$query_timesheet = "SELECT * FROM timeSheets,instructors WHERE timeSheets.instructorID=instructors.instructorID AND (timeSheets.entryStatus = 'submitted' OR timeSheets.entryStatus = 'rejected') ORDER BY timeSheets.instructorID, timeSheets.entryDate DESC";
$timesheet = mysql_query($query_timesheet, $wotg) or die(mysql_error());
$row_timesheet = mysql_fetch_assoc($timesheet);
$totalRows_timesheet = mysql_num_rows($timesheet);
?>
<?php
if ($totalRows_timesheet == 0) echo '<p class="twd_centered twd_margin20">There are no unapproved time sheet entries at this time.</p>';
else {
?>

<table border="0" align="center" cellpadding="3" cellspacing="0" class="twd_margin20 twd_centered">
  <tr>
    <td><strong>Name</strong></td>
    <td><strong>Entry Date</strong></td>
    <td><strong>Hours</strong></td>
    <td>&nbsp;</td>
  </tr>
  <?php 
  do { 
  ?>
    <tr>
      <td><?php echo $row_timesheet['firstName']; ?> <?php echo $row_timesheet['lastName']; ?></td>
      <td><?php echo date('m/d/Y', strtotime($row_timesheet['entryDate'])); ?></td>
      <td><?php echo $row_timesheet['entryHours']; ?></td>
      <td><button onclick="reject(<?php echo $row_timesheet['sheetID']; ?>);">reject</button> <button onclick="approve(<?php echo $row_timesheet['sheetID']; ?>);">approve</button></td>
    </tr>
    <?php } while ($row_timesheet = mysql_fetch_assoc($timesheet)); ?>
    </table>
    
<p class="twd_centered twd_margin20"><a class="button" href="timesheets-approve-all.php">approve all entries</a></p>
<?php }  ?>
<?php
mysql_free_result($timesheet);
?>
