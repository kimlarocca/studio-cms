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

$sortBy = "timeSheets.instructorID, timeSheets.entryDate DESC";
if ($_GET['sortBy'] != '') $sortBy = $_GET['sortBy'];

mysql_select_db($database_wotg, $wotg);
$query_timesheet = "SELECT * FROM timeSheets,instructors WHERE instructors.instructorID=timeSheets.instructorID ORDER BY ".$sortBy;
$timesheet = mysql_query($query_timesheet, $wotg) or die(mysql_error());
$row_timesheet = mysql_fetch_assoc($timesheet);
$totalRows_timesheet = mysql_num_rows($timesheet);
?>
<?php
if ($totalRows_timesheet == 0) echo '<p class="twd_centered" style="color:red">There are no unapproved time sheet entries at this time.</p>';
else {
?>

<table border="0" align="center" cellpadding="5" cellspacing="0" class="twd_margin20 twd_centered">
  <tr>
    <td align="left"><strong><a href="?sortBy=instructors.lastName">Name</a></strong></td>
    <td align="left"><a href="?sortBy=timeSheets.entryDate"><strong>Entry Date</strong></a></td>
    <td align="left"><strong>Hours</strong></td>
    <td colspan="2" align="left"><strong>Last Modified By</strong></td>
    <td align="left"><strong><a href="?sortBy=timeSheets.entryStatus">Status</a></strong></td>
    <td align="left">&nbsp;</td>
    <td align="left">&nbsp;</td>
  </tr>
  <?php 
  do { 
$phpdate = strtotime( $row_timesheet['dateAdded'] );
$mysqldate = date( 'm/d/Y H:ia', $phpdate );
  ?>
    <tr>
      <td><?php echo $row_timesheet['lastName']; ?>, <?php echo $row_timesheet['firstName']; ?></td>
      <td><?php echo date('m/d/Y', strtotime($row_timesheet['entryDate'])); ?></td>
      <td><?php echo $row_timesheet['entryHours']; ?></td>
      <td><?php echo $row_timesheet['lastModifiedBy']; ?></td>
      <td><em><?php echo $mysqldate; ?></em></td>
      <td><?php echo $row_timesheet['entryStatus']; ?></td>
      <td><a class="tooltip" title="update this record" href="timesheets-update.php?sheetID=<?php echo $row_timesheet['sheetID']; ?>"><img src="images/edit.png" alt="" width="20" height="20"/></a> <a class="tooltip" title="delete this record" href="timesheets-delete.php?sheetID=<?php echo $row_timesheet['sheetID']; ?>"><img src="images/delete.png" width="20" height="20" /></a></td>
    </tr>
    <?php } while ($row_timesheet = mysql_fetch_assoc($timesheet)); ?>
    </table>
<?php }  ?>
<?php
mysql_free_result($timesheet);
?>
