<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
?>
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

$MM_restrictGoTo = "index.php?action=failed";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO videos (websiteID, instructorID, videoTitle, videoURL, thumbnail, videoShortDescription, videoLongDescription) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['websiteID'], "int"),
                       GetSQLValueString($_POST['instructorID'], "int"),
                       GetSQLValueString($_POST['videoTitle'], "text"),
                       GetSQLValueString($_POST['videoURL'], "text"),
                       GetSQLValueString($_POST['thumbnail'], "text"),
                       GetSQLValueString($_POST['videoShortDescription'], "text"),
                       GetSQLValueString($_POST['videoLongDescription'], "text"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());

  $insertGoTo = "videos.php?action=added";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

$colname_currentUser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_currentUser = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_currentUser = sprintf("SELECT * FROM instructors WHERE username = %s", GetSQLValueString($colname_currentUser, "text"));
$currentUser = mysql_query($query_currentUser, $wotg) or die(mysql_error());
$row_currentUser = mysql_fetch_assoc($currentUser);
$totalRows_currentUser = mysql_num_rows($currentUser);

mysql_select_db($database_wotg, $wotg);
$query_videos = "SELECT * FROM videos WHERE instructorID = ".$row_currentUser['instructorID']." ORDER BY videoTitle ASC";
$videos = mysql_query($query_videos, $wotg) or die(mysql_error());
$row_videos = mysql_fetch_assoc($videos);
$totalRows_videos = mysql_num_rows($videos);
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
<title>WOTG Administration</title>
</head>
<body>
<h1>WOTG Admin</h1>
<div class="navLogo"><a href="student-home.php"><img src="images/logo-mobile.png" width="101" height="50" /></a></div>
<div class="nav"> <a class="navItem iconLinks" href="home.php"><img src="images/home.png" /></a> <a class="navItem iconLinks" href="settings.php"><img src="images/settings.png" /></a> </div>
<div class="twd_container">
      <h2>practice videos      </h2>
      <h3>add a new video </h3>
      <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
        <table border="0" cellpadding="3" cellspacing="0">
          <tr valign="baseline">
            <td nowrap="nowrap" align="right">Title:</td>
            <td><input type="text" name="videoTitle" value="" size="32" /></td>
          </tr>
          <tr valign="baseline">
            <td nowrap="nowrap" align="right">Embed Code:</td>
            <td><textarea name="videoURL" cols="50" rows="5"></textarea></td>
          </tr>
          <tr valign="baseline">
            <td nowrap="nowrap" align="right">Thumbnail URL:</td>
            <td><input type="text" name="thumbnail" value="" size="32" /></td>
          </tr>
          <tr valign="baseline">
            <td nowrap="nowrap" align="right" valign="top">Short Description:</td>
            <td><textarea name="videoShortDescription" cols="50" rows="5"></textarea></td>
          </tr>
          <tr valign="baseline">
            <td nowrap="nowrap" align="right" valign="top">Long Description:</td>
            <td><textarea name="videoLongDescription" cols="50" rows="5"></textarea></td>
          </tr>
          <tr valign="baseline">
            <td nowrap="nowrap" align="right">&nbsp;</td>
            <td><input type="submit" value="Add Video" /></td>
          </tr>
        </table>
        <input type="hidden" name="websiteID" value="1" />
        <input type="hidden" name="instructorID" value="<?php echo $row_currentUser['instructorID']; ?>" />
        <input type="hidden" name="MM_insert" value="form1" />
  </form>
  
  
  <h3>manage your videos  </h3>
  <table border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td>videoTitle</td>
      <td>videoURL</td>
      <td>thumbnail</td>
      <td>videoShortDescription</td>
      <td>videoLongDescription</td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo $row_videos['videoTitle']; ?></td>
        <td><?php echo $row_videos['videoURL']; ?></td>
        <td><?php echo $row_videos['thumbnail']; ?></td>
        <td><?php echo $row_videos['videoShortDescription']; ?></td>
        <td><?php echo $row_videos['videoLongDescription']; ?></td>
      </tr>
      <?php } while ($row_videos = mysql_fetch_assoc($videos)); ?>
  </table>
</div>

</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($videos);
?>