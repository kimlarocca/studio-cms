<?php require_once('Connections/wotg.php'); ?>
<?php
if ((isset($_POST['sheetID'])) && ($_POST['sheetID'] != "")) {
	  $updaterecord = "UPDATE timeSheets SET entryStatus = 'approved' WHERE sheetID = ".$_POST['sheetID'];
	  mysql_select_db($database_wotg, $wotg);
	  mysql_query($updaterecord, $wotg) or die(mysql_error());	
	}
?>