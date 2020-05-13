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

mysql_select_db($database_wotg, $wotg);
$query_studios = "SELECT * FROM studios ORDER BY studioName ASC";
$studios = mysql_query($query_studios, $wotg) or die(mysql_error());
$row_studios = mysql_fetch_assoc($studios);
$totalRows_studios = mysql_num_rows($studios);

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
<title>Slate It | My Account</title>
</head>

<body>
<div class="logo twd_centered"><img src="images/logo.png" width="245" height="120" /></div>
<h1 class="twd_centered twd_margin20">Account Login</h1>
<div class="twd_container">
     <h2 class="twd_centered">Find your studio:</h2>
  <table border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
      <td><strong>Studio Name</strong></td>
      <td><strong>Website</strong></td>
      <td>&nbsp;</td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo $row_studios['studioName']; ?></td>
        <td><a href="<?php echo $row_studios['url']; ?>"><?php echo $row_studios['url']; ?></a></td>
        <td><a href="student-login.php?studioID=<?php echo $row_studios['studioID']; ?>">login</a></td>
      </tr>
      <?php } while ($row_studios = mysql_fetch_assoc($studios)); ?>
  </table>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($studios);
?>
