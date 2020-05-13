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

$startDate = date('Y-m-d', strtotime($_GET['startDate']));
$endDate = date('Y-m-d', strtotime($startDate. ' + 13 days'));

mysql_select_db($database_wotg, $wotg);
$query_timesheet = "SELECT * FROM timeSheets,instructors WHERE timeSheets.instructorID=instructors.instructorID AND (timeSheets.entryDate >= '".$startDate."' AND timeSheets.entryDate <= '".$endDate."') ORDER BY timeSheets.instructorID,timeSheets.entryDate DESC";
$timesheet = mysql_query($query_timesheet, $wotg) or die(mysql_error());
$row_timesheet = mysql_fetch_assoc($timesheet);
$totalRows_timesheet = mysql_num_rows($timesheet);
?>
<p class="twd_centered twd_margin20">Pay Period: <?php echo $startDate; ?> - <?php echo $endDate; ?></p>
<?php
if ($totalRows_timesheet == 0) { echo '<p class="twd_centered" style="color:red">There are no time sheet entries for this pay period.</p>'; }
else {
?>
<table border="0" align="center" cellpadding="3" cellspacing="0" class="twd_margin20 twd_centered">
  <tr>
    <td><strong>Date</strong></td>
    <td><strong>Hours</strong></td>
    <td><strong>Name</strong></td>
  </tr>
  <?php 
  $hours = 0;
  $instructorID = $row_timesheet['instructorID'];
  do { 
  if($instructorID != $row_timesheet['instructorID']){
	  echo '
    <tr>
      <td>&nbsp;</td>
      <td>Total = '.$hours.'</td>
      <td>&nbsp;</td>
    </tr>';
	$instructorID = $row_timesheet['instructorID'];
	$hours = 0;  
  }
  $hours = $hours+$row_timesheet['entryHours'];
$phpdate = strtotime( $row_timesheet['dateAdded'] );
$mysqldate = date( 'm/d/Y H:ia', $phpdate );
  ?>
    <tr>
      <td><?php echo date('m/d/Y', strtotime($row_timesheet['entryDate'])); ?></td>
      <td><?php echo $row_timesheet['entryHours']; ?></td>
      <td><?php echo $row_timesheet['firstName']; ?> <?php echo $row_timesheet['lastName']; ?></td>
    </tr>
    <?php } while ($row_timesheet = mysql_fetch_assoc($timesheet)); 
	
	echo '
    <tr>
      <td>&nbsp;</td>
      <td>Total = '.$hours.'</td>
      <td>&nbsp;</td>
    </tr>';
	?>
    </table>
<?php } ?>
<?php
mysql_free_result($timesheet);
?>
