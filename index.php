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
ini_set('session.save_path',getcwd(). '/../tmp/');
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

if (isset($_POST['username'])) {
  $loginUsername=$_POST['username'];
  $password=$_POST['password'];
  $MM_fldUserAuthorization = "";
  $MM_redirectLoginSuccess = "home.php";
  $MM_redirectLoginFailed = "login-failed.php";
  $MM_redirecttoReferrer = false;
  mysql_select_db($database_wotg, $wotg);
  
  $LoginRS__query=sprintf("SELECT username, password FROM instructors WHERE username=%s AND password=%s",
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

mysql_select_db($database_wotg, $wotg);
$query_studios = "SELECT * FROM studios ORDER BY studioName ASC";
$studios = mysql_query($query_studios, $wotg) or die(mysql_error());
$row_studios = mysql_fetch_assoc($studios);
$totalRows_studios = mysql_num_rows($studios);
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
<title>Slate It | Administrator Login</title>
</head>

<body>
<div class="navLogo"><img src="images/logo-mobile.png" width="101" height="50" /></div>
<div class="logo twd_centered"><img src="images/logo.png" width="245" height="120" /></div>
<h1 class="twd_centered">Administrator Login</h1>
<div class="twd_container">
<?php
//check url parameters
if ($_GET['action'] == 'failed') print '<p class="red twd_centered twd_margin20">LOGIN FAILED! Please try again:</p>';
if ($_GET['action'] == 'denied') print '<p class="red twd_centered twd_margin20">ACCESS DENIED! Please try logging in again:</p>';
if ($_GET['action'] == 'newAccount') print '<p class="twd_centered twd_margin20">Hey, it\'s your first time logging in! Your account is ready to go. Have fun :)</p>';
?>
  <form ACTION="<?php echo $loginFormAction; ?>" id="form1" name="form1" method="POST" style="text-align:center; margin-bottom:20px">
    <p>
      <label for="username"></label>
      Username:<br />
      <input type="text" name="username" id="username" style="margin:auto" value="<?php echo $_GET['emailAddress'] ?>" />
    Password:<br />
      <label for="password"></label>
      <input type="password" name="password" id="password" style="margin:auto" />
    </p>
    <p>
      <input type="submit" name="button" id="button" value="Login"  style="margin:auto"/>
    </p>
  </form>
  <p class="twd_centered"><a href="account-forgot-password.php">Forgot Password?</a></p>
</div>
<?php include("footer.php"); ?>
</body>
</html>