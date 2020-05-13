<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
?>
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

$colname_student = "-1";
if (isset($_GET['emailAddress'])) {
  $colname_student = $_GET['emailAddress'];
}
mysql_select_db($database_wotg, $wotg);
$query_student = sprintf("SELECT * FROM students WHERE emailAddress = %s", GetSQLValueString($colname_student, "text"));
$student = mysql_query($query_student, $wotg) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);
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
<title>Slate It | Forgot Password</title>
</head>

<body>
<div class="logo twd_centered"><img src="images/logo.png" width="245" height="120" /></div>
<h1 class="twd_centered">Password Request</h1>
<div class="twd_container">
  <?php 
  if ($_GET['action'] == 'sent') { 
  if($totalRows_student==0) { echo '<br><br><p class="twd_centered"><span class="red">There is no account associated with the email address '.$_GET["emailAddress"].'.</span> <a href="javascript:history.back()">Click here to try again!</a>';
  if ($totalRows_studio>0) echo '<br><br>You can also <a href="student-login.php?studioID='.$_GET['studioID'].'">click here to create a new account!</a><br><br /></p>';
  } else {
  echo '<br><br><p class="twd_centered">Your password has been sent to <strong>'.$_GET["emailAddress"].'</strong>. Please check your email.<br><br /></p>'; 
  
  require_once 'swift/swift_required.php';

$email = 'noreply@slateit.com';
$fromEmail = 'noreply@slateit.com';
$toEmail = $_GET['emailAddress'];
$message =  'Your password is '.$row_student['password'].'<br><br>Please <a href="https://my.slateit.com/student-login.php?studioID='.$row_student['studioID'].'">click here to login!</a>';
$subject = 'Password Request';

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
  
  } }
  else { 
  ?>
<form ACTION="account-forgot-password.php" id="form2" name="form2" method="GET" class="twd_centered">
    <p>
      <label for="username"></label>
      Email Address:<br />
      <input type="text" name="emailAddress" id="emailAddress" style="margin:auto" />
      <input name="studioID" type="hidden" value="<?php echo $row_studio['studioID']; ?>" />
      <input name="action" type="hidden" value="sent" />
    </p>
    <p>
      <input type="submit" name="button" id="button" value="Continue"  style="margin:auto"/>
    </p>
  </form>
<?php } ?>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($studio);

mysql_free_result($student);
?>
