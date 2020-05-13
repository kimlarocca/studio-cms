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

mysql_select_db($database_wotg, $wotg);
$query_studioList = "SELECT studioID, studioName FROM studios ORDER BY studioName ASC";
$studioList = mysql_query($query_studioList, $wotg) or die(mysql_error());
$row_studioList = mysql_fetch_assoc($studioList);
$totalRows_studioList = mysql_num_rows($studioList);
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
  $MM_redirectLoginFailed = "student-login.php?error=studentlogin&action=failed&studioID=".$_GET['studioID']."&emailAddress=".$_GET['emailAddress'];
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
	  error_log('login successful');
    header("Location: " . $MM_redirectLoginSuccess );
	
  }
  else {
	    error_log('login failed '.$MM_redirectLoginFailed);
    header("Location: ". $MM_redirectLoginFailed );
  }
}
?>
<?php
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
<title><?php if ($row_studio!=0) echo $row_studio['studioName']." | "; ?>Account Login</title>
</head>
<body>
<?php
//get colors
$color = '#70adc7';
$colorFont = '#ffffff';
if ($row_studio['color']!='') $color = $row_studio['color'];
if ($row_studio['colorFont']!='') $colorFont = $row_studio['colorFont'];
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
 <?php if($row_studio['logoURL']!=''){ ?>
        <div class="twd_centered twd_margin20" style="padding-top:10px"><img src="uploads/<?php echo $row_studio['logoURL']; ?>" /></div>
        <?php } ?>
<h1 class="studentH1 twd_centered"><?php if ($row_studio!=0) echo $row_studio['studioName']." | "; ?>Account Login</h1>
<?php
//check url parameters
if ($_GET['action'] == 'failed') print '<p style="color:red; padding-top:20px" class="twd_centered">LOGIN FAILED - Please try again!</p>';
if ($_GET['action'] == 'setup') print '<p style="color:red; padding-top:20px" class="twd_centered">Your account has been setup! Please login using the form below.</p>';
?>
<?php 
//get logo if it exists
if($row_student['logoURL']!=''){ 
?>
<div class="twd_centered twd_margin20" style="padding-top:20px; clear:both"><img src="uploads/<?php echo $row_student['logoURL']; ?>" /></div>
<?php 
}
?>
<div class="twd_container">
<?php if (isset($_GET['studioID'])) { ?>
  <p class="twd_centered" style="padding:20px 0 20px 0">Login below to reserve a spot in one of our classes or to view your upcoming reservations.</p> 
  <form ACTION="student-login-process.php" id="form1" name="form1" method="POST" class="twd_centered twd_margin20"><input name="url" type="hidden" value="<?php echo $_GET['url']; ?>" />
    <p>
      <label for="username"></label>
      Email Address:<br />
      <input name="emailAddress" type="text" id="emailAddress" style="margin:auto" value="<?php echo $_GET['emailAddress']; ?>" /><br />
    Password:<br />
      <label for="password"></label>
      <input type="password" name="password" id="password" style="margin:auto" />
    </p>
    <p>
      <input type="submit" name="button" id="button" value="Login"  style="margin:auto"/>
    </p>
  </form>
  <p class="twd_centered" style="padding:20px 0 20px 0"><strong>First time logging in? </strong>Enter your email address below to setup your account!</p> 
  <form ACTION="account-setup.php" id="form2" name="form2" method="GET" class="twd_centered">
    <p>
      <label for="username"></label>
      Email Address:<br />
      <input type="text" name="emailAddress" id="emailAddress" style="margin:auto" /><input name="studioID" type="hidden" value="<?php echo $row_studio['studioID']; ?>" />
    </p>
    <p>
      <input type="submit" name="button" id="button" value="Continue"  style="margin:auto"/>
    </p>
  </form>
  <p class="twd_centered" style="padding-top:40px"><a href="account-forgot-password.php?studioID=<?php echo $_GET['studioID']; ?>&emailAddress=<?php echo $_GET['emailAddress']; ?>">Forgot Password</a> | <a href="index.php">Admin Login</a> | Back To <a href="<?php echo $row_studio['url']; ?>"><?php echo $row_studio['url']; ?></a></p>
  <?php
} else {
	?>
    <p style="color:red; padding-top:20px" class="twd_centered twd_margin20">Sorry! There was an issue finding your studio. Please start over using the link provided to you by your studio, or select your studio from the list below:</p>
    <table border="0" align="center" cellpadding="3" cellspacing="0" class="twd_margin20">
      <?php do { ?>
        <tr>
          <td><a href="student-login.php?studioID=<?php echo $row_studioList['studioID']; ?>"><?php echo $row_studioList['studioName']; ?></a></td>
        </tr>
        <?php } while ($row_studioList = mysql_fetch_assoc($studioList)); ?>
    </table>
    <p class="twd_centered">
    <a href="index.php">Admin Login</a></p>
<?php } ?>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($studio);

mysql_free_result($studioList);
?>
