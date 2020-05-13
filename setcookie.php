<?php
//start session
header('P3P: CP="CAO PSA OUR"');
session_start();
$_SESSION = array();
//set cookie
$date_of_expiry = time() + 600 ;
setcookie( "slateit", "cookie", $date_of_expiry);
//redirect back to website
echo '<script>window.location = "'.$_GET['url'].'";</script>';
?>