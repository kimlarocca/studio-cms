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

$colname_studio = "-1";
if (isset($_GET['studioID'])) {
  $colname_studio = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_studio = sprintf("SELECT * FROM studios WHERE studioID = %s", GetSQLValueString($colname_studio, "int"));
$studio = mysql_query($query_studio, $wotg) or die(mysql_error());
$row_studio = mysql_fetch_assoc($studio);
$totalRows_studio = mysql_num_rows($studio);
?>
<?php
//send email
require_once 'swift/swift_required.php';

$email = 'noreply@slateit.com';
$fromEmail = 'noreply@slateit.com';
$toEmail = $row_studio['email'];
$message =  'Thank you for signing up for your free trial with Slate It! Please click here to verify your email address: <a href="https://my.slateit.com/onboard3.php?studioID='.$row_studio['studioID'].'&ID='.$row_studio['dateAdded'].'">https://my.slateit.com/onboard3.php?studioID='.$row_studio['studioID'].'&ID='.$row_studio['dateAdded'].'</a>';
$subject = 'Please verify your SlateIt.com email address!';

$username = "711ee8490b48e5120b50dd66ff9f0ac9";
$password = "40cb03aee60ee4a06f34fb62800c04af";
 
//godaddy $transport = new \Swift_SmtpTransport("smtpout.secureserver.net", 25);
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
<title>Slate It | Start Your Free Trial</title>
</head>

<body>
<div class="navLogo"><img src="images/logo-mobile.png" width="101" height="50" /></div>
<div class="logo twd_centered twd_margin20"><img src="images/logo.png" width="245" height="120" /></div>
<h1 class="twd_centered">Start Your Free Trial!</h1>
<div class="twd_container">
  <h2 class="twd_centered">Step 2: Verify Your Email Address</h2>
  <div class="twd_centered twd_margin20"><img src="images/onboarding-step2.png" width="320" height="35" /></div>
  <p class="twd_centered twd_margin20">An email was sent to  <strong><?php echo $row_studio['email']; ?></strong>. Please click on the link to verify your account, and be sure to check your spam or bulk folder!</p>
  <p class="twd_centered twd_margin20">In the meantime, <a href="onboard4.php?studioID=<?php echo $row_studio['studioID']; ?>">let's keep setting up your new account!</a></p>
  <p class="twd_centered twd_margin20"><a class="button" href="onboard4.php?studioID=<?php echo $row_studio['studioID']; ?>">continue to step 3</a></p>
<p class="twd_centered twd_margin20" style="line-height:32px;" id="startOver"><a href="onboard.php"><img src="images/start-over.png" width="32" height="32" /> START OVER</a></p>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($studio);
?>