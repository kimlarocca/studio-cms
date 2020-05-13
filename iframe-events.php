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
$query_studio = sprintf("SELECT studioID, color FROM studios WHERE studioID = %s", GetSQLValueString($colname_studio, "int"));
$studio = mysql_query($query_studio, $wotg) or die(mysql_error());
$row_studio = mysql_fetch_assoc($studio);
$totalRows_studio = mysql_num_rows($studio);

mysql_select_db($database_wotg, $wotg);
$query_events = "SELECT * FROM events WHERE eventDate >= CURRENT_DATE() AND studioID=".$_GET['studioID'];
$events = mysql_query($query_events, $wotg) or die(mysql_error());
$row_events = mysql_fetch_assoc($events);
$totalRows_events = mysql_num_rows($events);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Upcoming Events</title>
<style>
@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,600);
* {
	-webkit-box-sizing: border-box; /* Safari/Chrome, other WebKit */
	-moz-box-sizing: border-box;    /* Firefox, other Gecko */
	box-sizing: border-box;         /* Opera/IE 8+ */
}
body {
	font-family: 'Open Sans', sans-serif;
	font-weight: normal;
	color: #484c51;
	background-color: #fff;
	margin: 0;
	padding: 0;
}
img {
	max-width: 100%;
	border: 0;
	height:auto;
}
a, a:visited, a:active {
	color: #70adc7;
	text-decoration: none;
}
a:hover {
	opacity:.75;
	text-decoration: none;
}
p {
	margin:0 0 8px 0;
}
h2 {
 background-color:<?php echo $row_studio['color'];
?>;
	width:100%;
	color:#fff;
	margin:0;
	font-size:20px;
	padding:5px 1% 5px 1%;
	clear:both;
}
.button {
 color: <?php echo $row_studio['color'];
?>!important;
	background-color:#fff;
 border: 1px solid <?php echo $row_studio['color'];
?>;
	border-radius:3px;
	padding:5px;
	text-transform:uppercase;
	font-weight:bold;
	display:inline-block;
	text-align:center;
	min-width:125px;
	cursor:pointer;
	transition: all 0.25s linear;
	-webkit-transition: all 0.25s linear;
	-moz-transition: all 0.25s linear;
	-o-transition: all 0.25s linear;
	-ms-transition: all 0.25s linear;
}
.button:hover {
	color: #fff!important;
 background-color:<?php echo $row_studio['color'];
?>;
}
.sRow {
	margin: 20px 0 30px 0;
	width: 100%;
	clear:both;
}
.sRow p {
	padding:0 10px 10px 10px;
	margin:0;
}
.sColumn1 {
	width: 200 px;
	margin-left: 20px;
	padding-bottom: 10px;
	float: left
}
.sColumn2 {
	margin-left: 240px;
	text-align: left;
}
@media screen and (max-width: 767px) {
.sColumn1 {
	width: 100%;
	text-align:center;
	clear:both;
}
.sColumn2 {
	width: 100%;
	clear:both;
	text-align: center;
	margin-left: 0;
}
}
</style>
</head>

<body>
<?php do { ?>
  <h2><?php echo $row_events['eventName']; ?></h2>
  <div class="sRow">
    <?php
	$newDate = date("m/d/Y", strtotime($row_events['eventDate']));
	$newStartTime = date('g:i A', strtotime($row_events['eventStartTime']));
	$newEndTime = date('g:i A', strtotime($row_events['eventEndTime']));
	if(!isset($row_events['eventEndTime'])) $newEndTime='';
	?>
    <div class="sColumn1">
      <?php if($row_events['thumbnail']!=''){ ?>
      <img height="200" width="200" src="uploads/<?php echo $row_events['thumbnail']; ?>" />
      <?php } else { ?>
      <img height="200" width="200" src="uploads/unavailable.gif" />
      <?php } ?>
    </div>
    <div class="sColumn2"> 
      <strong><?php echo $newDate.", ".$newStartTime." - ".$newEndTime; ?></strong>
      <br /><br />
      <?php echo $row_events['description']; ?>
	  <?php
	  if($row_events['requireRegistration']==1){
	  ?>
      <br /><br /><a class="button" target="_parent" href="https://my.slateit.com/student-login.php?action=event&studioID=<?php echo $row_studio['studioID']; ?>&eventID=<?php echo $row_events['eventID']; ?>">register now</a>
      <?php
      }
	  if($row_events['paymentCode']!=''){
	    echo "<br><br>".$row_events['paymentCode'];
      }
	  ?>
    </div>
  </div>
  <?php } while ($row_events = mysql_fetch_assoc($events)); ?>
</body>
</html>
<?php
mysql_free_result($studio);
mysql_free_result($events);
?>
