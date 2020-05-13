<?php require_once('Connections/wotg.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "student-login.php?action=failed";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
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

$colname_student = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_student = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_student = sprintf("SELECT * FROM students,studios WHERE emailAddress = %s AND students.studioID=studios.studioID", GetSQLValueString($colname_student, "text"));
$student = mysql_query($query_student, $wotg) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);

ini_set('session.save_path',getcwd(). '/../tmp/');

//send email
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

$emailSent = FALSE;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  require_once 'swift/swift_required.php';
  $email = 'noreply@slateit.com';
  $fromEmail = $_POST['email'];
  $toEmail = $row_student['email'];
  $message =  'Name: '.$_POST['name'].'<br><br>Email: '.$_POST['email'].'<br><br>Message:<br><br>'.$_POST['message'];
  $subject = 'Contact from SlateIt.com';
  
  $username = "711ee8490b48e5120b50dd66ff9f0ac9";
  $password = "40cb03aee60ee4a06f34fb62800c04af";
   
  $transport = new \Swift_SmtpTransport("in-v3.mailjet.com", 25);
  $transport->setUsername($username);
  $transport->setPassword($password);
   
  $mailer = new \Swift_Mailer($transport);
   
  // Create the message
  $message = Swift_Message::newInstance()
  
	// Give the message a subject
	->setSubject($subject)
  
	// Set the From address with an associative array
	->setFrom(array($email))
  
	// Set the To addresses with an associative array
	->setTo(array($toEmail))
	
	->setReplyTo($fromEmail)
  
	// Give it a body
	->setBody($message, 'text/html')
	;
	
  $message->setFrom($email);
   
  $mailer->send($message);
  
  $emailSent = TRUE;
  
  flush();
}

//update database with student's rating
if (isset($_GET['stars'])){
  $stars = (int)$_GET['stars'];
  $addrecords = "UPDATE students SET reviewStars = ".$stars." WHERE studentID = ".$row_student['studentID'];
  mysql_select_db($database_wotg, $wotg);
  mysql_query($addrecords, $wotg) or die(mysql_error());	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
<link rel="icon" type="image/png" href="favicon-16x16.png" sizes="16x16" />
<link rel="stylesheet" type="text/css" href="styles.css"/>
<title><?php echo $row_student['studioName']; ?> | Rate Us!</title>
</head>

<body>
<?php include("student-header.php"); ?>
<h1 class="studentH1 twd_centered"><?php echo $row_student['studioName']; ?> | Rate Us!</h1>
<?php 
//get logo if it exists
if($row_student['logoURL']!=''){ 
?>
<div class="twd_centered twd_margin20" style="padding-top:20px; clear:both"><img src="uploads/<?php echo $row_student['logoURL']; ?>" /></div>
<?php 
}
?>
<div class="twd_container">
  <!-- email sent -->
  <?php 
  if($_SERVER['REQUEST_METHOD'] == 'POST') { 
  ?>
  <h2 class="twd_centered">Thank you!</h2>
  <p class="twd_centered twd_margin20">We appreciate your feedback. Your message has been sent to <?php echo $row_student['studioName']; ?>.</p>
  
  <?php } else { 
  if ($_GET['stars'] < 4) {
  ?>
  <!-- 1-3 star review -->
  <h2 class="twd_centered">Thank you for rating us!</h2>
  <p class="twd_centered twd_margin20">Please take a moment and let us know how we can improve your experience:</p>
  <form class="twd_centered" role="form" action="student-rate.php" method="post" id="contact-form">
      <input id="name" name="name" type="hidden" value="<?php echo $row_student['firstName']; ?> <?php echo $row_student['lastName']; ?>" />
      <input id="email" name="email" type="hidden" value="<?php echo $row_student['emailAddress']; ?>" />
      <textarea class="twd_centered" rows="5" id="message" name="message" placeholder=" please enter your message here!" required></textarea>
      <input name="submit" value="submit" type="submit" />
      <input type="hidden" value="<?php echo $row_student['emailAddress']; ?>" name="toEmail" id="toEmail" />
  </form>
  <?php } else { ?>
  <!-- 4 or 5 star review -->   
  <h2 class="twd_centered">Thank you for <?php echo $_GET['stars']; ?> your star rating!</h2> 
  <p class="twd_centered twd_margin20"><?php echo $row_student['reviews']; ?></p>
  <?php if($row_student['yelpLink']!=''){ ?>
  <p class="twd_centered twd_margin20">Yelp Link: <a href="<?php echo $row_student['yelpLink']; ?>" target="_blank"><?php echo $row_student['yelpLink']; ?></a></p>
  <?php } ?>
  <?php if($row_student['googleLink']!=''){ ?>
  <p class="twd_centered twd_margin20">Google Link: <a href="<?php echo $row_student['googleLink']; ?>" target="_blank"><?php echo $row_student['googleLink']; ?></a></p>
  <?php } ?>
  <?php if($row_student['facebookLink']!=''){ ?>
  <p class="twd_centered twd_margin20">Facebook Link: <a href="<?php echo $row_student['facebookLink']; ?>" target="_blank"><?php echo $row_student['facebookLink']; ?></a></p>
  <?php } } } ?>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($student);
?>
