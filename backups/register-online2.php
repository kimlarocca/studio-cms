<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
?>
<?php require_once('Connections/wotg.php'); ?>
<?php
//get day of the week selected
$month = date('m', strtotime($_GET['datePicked']));
$day = date('d', strtotime($_GET['datePicked']));
$year = date('Y', strtotime($_GET['datePicked']));
$dateArray=getdate(mktime(0,0,0,$month,$day,$year));
$dayPicked = $dateArray[weekday];
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

mysql_select_db($database_wotg, $wotg);
$query_classes = "SELECT * FROM classes WHERE classDay = '".$dayPicked."' AND classActive = 1 ORDER BY startTime ASC";
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="styles.css"/>
<title>Wellness On The Green | Register Online</title>
</head>
<body>
<h1>Wellness On The Green: Online Registration</h1>
<div class="twd_container">
  <h2>Step 2: Choose a Class</h2>
<?php
 $formattedDate = date("m/d/Y", strtotime($_GET['datePicked']));
 ?>
   <p>Date Selected: <strong><?php echo $formattedDate ?> </strong></p>
 <form action="register-online3.php" method="get">
 
 
     <select name="classID">
       <?php
do { 
$formattedTime = date("g:i a", strtotime($row_classes['startTime']));
?>
       <option value="<?php echo $row_classes['classID']?>"><?php echo $formattedTime?>: <?php echo $row_classes['name']?></option>
       <?php
} while ($row_classes = mysql_fetch_assoc($classes));
  $rows = mysql_num_rows($classes);
  if($rows > 0) {
      mysql_data_seek($classes, 0);
	  $row_classes = mysql_fetch_assoc($classes);
  }
?>
     </select>
     <br />
     <input name="Continue" type="submit" value="Continue" /><input name="datePicked" type="hidden" value="<?php echo $_GET['datePicked']; ?>" />
 </form>
  <p><a href="student-home.php">&lt;&lt; start over / pick a new date</a></p>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($classes);
?>