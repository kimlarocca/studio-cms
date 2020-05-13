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
?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['emailAddress'])) {
  $loginUsername=$_POST['emailAddress'];
  $password=$_POST['password'];
  $MM_fldUserAuthorization = "";
  $MM_redirectLoginSuccess = "student-home.php";
  $MM_redirectLoginFailed = "student-login-universal.php?action=failed";
  $MM_redirecttoReferrer = false;
  mysql_select_db($database_wotg, $wotg);
  
  $LoginRS__query=sprintf("SELECT emailAddress, password FROM students WHERE emailAddress=%s AND password=%s",
    GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text")); 
   
  $LoginRS = mysql_query($LoginRS__query, $wotg) or die(mysql_error());
  $loginFoundUser = mysql_num_rows($LoginRS);
  if ($loginFoundUser) {
     $loginStrGroup = "";
    
	if (PHP_VERSION >= 5.1) {session_regenerate_id(true);} else {session_regenerate_id();}
    //declare two session variables and assign them
    $_SESSION['MM_Username'] = $loginUsername;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;	      

    if (isset($_SESSION['PrevUrl']) && false) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
    header("Location: " . $MM_redirectLoginSuccess );
  }
  else {
    header("Location: ". $MM_redirectLoginFailed );
  }
}

ini_set('session.save_path',getcwd(). '/../tmp/');
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
<title>Slate It | Account Login</title>
</head>
<body>
<?php
//get colors
$color = '#70adc7';
$colorFont = '#ffffff';
?>
<style>
h1 { background-color:<?php echo $color; ?>; color:<?php echo $colorFont; ?>; }
h2 { color:<?php echo $color; ?>; }
a, a:visited, a:active { color:<?php echo $color; ?>; }
.button {
	color: <?php echo $color; ?>!important;
	border: 2px solid <?php echo $color; ?>;
}
</style>
        <div class="twd_centered twd_margin20" style="padding-top:10px"><img src="images/logo.png" width="197" height="97" alt="Slate It | Scheduling Made Simple" /></div>
<h1 class="studentH1 twd_centered">Account Login</h1>
<?php
//check url parameters
if ($_GET['action'] == 'failed') print '<p style="color:red; padding-top:20px" class="twd_centered">LOGIN FAILED - Please try again!</p>';
?>
<div class="twd_container">
  <p class="twd_centered" style="padding:20px 0 20px 0">Login below to reserve a spot in one of our classes or to view your upcoming reservations.</p> 
  <form ACTION="<?php echo $loginFormAction; ?>" id="form1" name="form1" method="POST" class="twd_centered twd_margin20">
    <p>
      <label for="username"></label>
      Email Address:<br />
      <input name="emailAddress" type="text" id="emailAddress" style="margin:auto"/><br />
    Password:<br />
      <label for="password"></label>
      <input type="password" name="password" id="password" style="margin:auto" />
    </p>
    <p>
      <input type="submit" name="button" id="button" value="Login"  style="margin:auto"/>
    </p>
  </form>
  <p class="twd_centered" style="padding-top:30px"><a href="account-forgot-password.php">Forgot Password?</a></p>
</div>
<?php include("footer.php"); ?>
</body>
</html>