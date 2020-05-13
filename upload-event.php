<?php require_once('simpleimage.php'); ?>
<?php require_once('Connections/wotg.php'); ?>
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
ini_set('memory_limit', '1024M');
if(!empty($_FILES)){
		
	$studioID = $_POST['studioID'];
	$eventID = $_POST['eventID'];
    $targetDir = "uploads/";
    $fileName = $_FILES['file']['name'];
	
    //connect with the database
    $conn = new mysqli($hostname_wotg, $username_wotg, $password_wotg, $database_wotg);
    if($mysqli->connect_errno){
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
	
	//rename file
	rename($targetDir.$fileName, $targetDir.$studioID."-".$fileName);
	$fileName = $studioID."-".$fileName;
	
    $targetFile = $targetDir.$fileName;
    
    if(move_uploaded_file($_FILES['file']['tmp_name'],$targetFile)){
        //insert file information into db table
        $conn->query("UPDATE events SET thumbnail='".$fileName."' WHERE eventID = ".$eventID);
    }
	
	//resize image
	list($width, $height) = getimagesize($targetFile);
	if($width > 480){
	  $image = new SimpleImage($targetFile);
	  $image->resizeToWidth(480);
	  $image->save($targetFile);
	}
}
?>