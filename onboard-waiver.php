<?php require_once('Connections/wotg.php'); ?>
<?php
if ((isset($_POST['studioID'])) && ($_POST['studioID'] != "")) {  
  //update studio record
  $updaterecord = "UPDATE studios SET requireWaiver=1, waiverCopy='".$_POST['waiverCopy']."' WHERE studioID = ".$_POST['studioID'];
  mysql_select_db($database_wotg, $wotg);
  mysql_query($updaterecord, $wotg) or die(mysql_error());
}
?>