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

//process dates
date_default_timezone_set($row_student['studioTimezone']);
$today = $expiry_date = date("Y-m-d", strtotime("now"));
$month = date('m', strtotime($today));
$day = date('d', strtotime($today));
$year = date('Y', strtotime($today));
$dateArray=getdate(mktime(0,0,0,$month,$day,$year));
$dayPicked = $dateArray[weekday];

mysql_select_db($database_wotg, $wotg);
$query_classes = "SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studio, classes.classCapacity, classes.studioID, classes.thumbnail, instructors.instructorID, instructors.firstName, instructors.studioID FROM classes,instructors WHERE classes.instructorID=instructors.instructorID AND classes.studioID = ".$_GET['studioID']." AND classes.classDay = '".$dayPicked."' AND classes.classActive = 1 ORDER BY classes.startTime ASC";
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);

mysql_select_db($database_wotg, $wotg);
$query_cancellations = "SELECT cancellationDate,studioID,classID FROM classCancellations WHERE cancellationDate = '".$today."' AND studioID=".$_GET['studioID'];
$cancellations = mysql_query($query_cancellations, $wotg) or die(mysql_error());
$row_cancellations = mysql_fetch_assoc($cancellations);
$totalRows_cancellations = mysql_num_rows($cancellations);

mysql_select_db($database_wotg, $wotg);
$query_cancellations2 = "SELECT cancelAllClasses,cancellationDate,studioID FROM classCancellations WHERE cancelAllClasses = 1 AND cancellationDate = '".$today."' AND studioID=".$_GET['studioID'];
$cancellations2 = mysql_query($query_cancellations2, $wotg) or die(mysql_error());
$row_cancellations2 = mysql_fetch_assoc($cancellations2);
$totalRows_cancellations2 = mysql_num_rows($cancellations2);

$cancellationsArray = array();
do { 
array_push($cancellationsArray, $row_cancellations['classID']);
} while ($row_cancellations = mysql_fetch_assoc($cancellations));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Today's Classes</title>
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
	color: <?php echo $row_studio['color'];?>;
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
 background-color:<?php echo $row_studio['color'];?>;
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
.red {
	color:red;
}
.green {
	color: #390
}
td {
	padding: 0 10px 10px 0;
}
</style>
</head>

<body>
<?php 
	  if ($totalRows_cancellations2>0){
	echo '<p class="red twd_centeredOnTablet">Sorry - all classes have been cancelled today!</p>';
 } else {
	 if ($totalRows_classes==0){?>
<p class="red twd_centeredOnTablet">Sorry - there are no classes today!</p>
<?php } else {?>
<table border="0" cellpadding="0" cellspacing="0" class="twd_margin40" id="todaysClasses">
  <?php do { 
	$formattedTime = date("g:i a", strtotime($row_classes['startTime']));  
	$formattedEndTime = date("g:i a", strtotime($row_classes['endTime'])); 

		//check availability and cancellations
		mysql_select_db($database_wotg, $wotg);
		$query_attendance = "SELECT * FROM attendance WHERE dateAdded = '".$today."' AND classID=".$row_classes['classID'];
		$attendance = mysql_query($query_attendance, $wotg) or die(mysql_error());
		$row_attendance = mysql_fetch_assoc($attendance);
		$totalRows_attendance = mysql_num_rows($attendance);
		$allowReservation = TRUE;
		
		if (in_array($row_classes['classID'], $cancellationsArray)) {
    $classMessage = '<span class="red">This class is has been cancelled!</span>';
		$allowReservation = FALSE;
} else {
		if($row_classes['classCapacity']==$totalRows_attendance){
			$classMessage = '<span class="red">This class is full!</span>';
		$allowReservation = FALSE;
		} else {
		$classMessage = '<p class="twd_centeredOnMobile"><span class="green">'.($row_classes['classCapacity']-$totalRows_attendance).' spots left!</span></p><p class="twd_centeredOnMobile">
<a class="button" target="_parent" href="https://my.slateit.com/student-login.php?action=class&classID='.$row_classes['classID'].'&dateAdded='.$today.'&studioID='.$row_studio['studioID'].'">register now</a></p>'; 
		} }
		?>
  <tr>
    <td class="twd_centered"><?php if ($allowReservation) { ?>
      <?php } if($row_classes['thumbnail']!=''){ ?>
      <img height="200" width="200" src="uploads/<?php echo $row_classes['thumbnail']; ?>" />
      <?php } else { ?>
      <img height="200" width="200" src="uploads/unavailable.gif" />
      <?php } if ($allowReservation) { ?>
      <?php } ?></td>
    <td><p class="twd_centeredOnMobile"><strong><?php echo $formattedTime;?>: <?php echo $row_classes['name']; ?></strong></p>
      <?php echo $classMessage; ?>
      <p class="twd_centeredOnTablet">Instructor: <?php echo $row_classes['firstName']; ?></p></td>
  </tr>
  <?php } while ($row_classes = mysql_fetch_assoc($classes)); ?>
</table>
<?php } } ?>

<p style="padding:20px 0 20px 0"><a target="_parent" href="https://my.slateit.com/student-login.php?studioID=<?php echo $row_studio['studioID']; ?>">Click here to login and register for upcoming classes on any future date!</a></p>

</body>
</html>
<?php
mysql_free_result($studio);
?>
