<?php require_once('Connections/wotg.php'); ?>
<?php
if ((isset($_POST['attendanceID'])) && ($_POST['attendanceID'] != "")) {
  $deleteSQL = "DELETE FROM attendance WHERE attendanceID = ".$_POST['attendanceID'];
  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($deleteSQL, $wotg) or die(mysql_error());
  
  //handle prepaid pakages	  
    if ($_POST["attendanceType"] == 'Pre Paid') {
	  $studentrecord = "SELECT studentID,classesLeft FROM students WHERE studentID = ".$_POST['studentID'];
	  mysql_select_db($database_wotg, $wotg);
	  $studentResult = mysql_query($studentrecord, $wotg) or die(mysql_error());
	  $row_studentrecord = mysql_fetch_assoc($studentResult);
	  if (($row_studentrecord['classesLeft']==0)) {
	  	$classesLeft = 0;
	  } else {
		$classesLeft = $row_studentrecord['classesLeft']+1;
	  }
	  $updaterecord = "UPDATE students SET classesLeft=".$classesLeft." WHERE studentID = ".$_POST['studentID'];
	  mysql_select_db($database_wotg, $wotg);
	  mysql_query($updaterecord, $wotg) or die(mysql_error());	
	}
  
}
?>