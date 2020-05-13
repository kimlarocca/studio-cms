<?php require_once('simpleimage.php'); ?>
<?php require_once('Connections/wotg.php'); ?>
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
		
	$studioID = $_GET['studioID'];


//update record
$updaterecord = "UPDATE studios SET logoURL=NULL WHERE studioID = ".$studioID;
mysql_select_db($database_wotg, $wotg);
mysql_query($updaterecord, $wotg) or die(mysql_error());

header("Location:settings.php#studioSettings");
?>