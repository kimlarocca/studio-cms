<?php require_once('Connections/wotg.php'); ?>
<?php
if ((isset($_POST['attendanceID'])) && ($_POST['attendanceID'] != "")) {
  
	//update record
	$updaterecord = "UPDATE attendance SET checkedIn=1 WHERE attendanceID = ".$_POST['attendanceID'];
	mysql_select_db($database_wotg, $wotg);
	mysql_query($updaterecord, $wotg) or die(mysql_error());
  
}
?>