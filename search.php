<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
if (!isset($_SESSION)) {
  session_start();
}
?>
<?php require_once('Connections/wotg.php'); ?>
<?php
$studioID = "-1";
if (isset($_SESSION['studioID'])) {
  $studioID = $_SESSION['studioID'];
}

if (isset($_GET['term'])){
	$return_arr = array();
	try {
		
		mysql_select_db($database_wotg, $wotg);
		$stmt = "SELECT * FROM students WHERE studioID = ".$studioID." AND (lastName LIKE '%".$_GET['term']."%' OR firstName LIKE '%".$_GET['term']."%' OR CONCAT (firstName,' ',lastName) LIKE '%".$_GET['term']."%')";
		$stmt = mysql_query($stmt, $wotg) or die(mysql_error());
	    $row_stmt = mysql_fetch_assoc($stmt);
	    
	    do {
			if($row_stmt['classesLeft']>0){
   			$row_stmt['value']=$row_stmt['firstName'].' '.$row_stmt['lastName'].' ('.$row_stmt['classesLeft'].')';
			} else {
   			$row_stmt['value']=$row_stmt['firstName'].' '.$row_stmt['lastName'];				
			}
			$row_stmt['studentID']=(int)$row_stmt['studentID'];
			$row_set[] = $row_stmt; // build an array
		}
		while ($row_stmt = mysql_fetch_assoc($stmt));
  		echo json_encode($row_set); // format the array into json data

		} 
	catch(PDOException $e) {
		echo 'ERROR: ' . $e->getMessage();
	}
}
mysql_free_result($stmt);
?>