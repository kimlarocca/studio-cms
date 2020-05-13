<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
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

$colname_currentUser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_currentUser = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_currentUser = sprintf("SELECT * FROM instructors,studios WHERE instructors.studioID=studios.studioID AND instructors.username = %s", GetSQLValueString($colname_currentUser, "text"));
$currentUser = mysql_query($query_currentUser, $wotg) or die(mysql_error());
$row_currentUser = mysql_fetch_assoc($currentUser);
$totalRows_currentUser = mysql_num_rows($currentUser);

$studioID = $row_currentUser['studioID'];

mysql_select_db($database_wotg, $wotg);
$query_monday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Monday' ORDER BY classes.startTime ASC", $studioID);
$monday = mysql_query($query_monday, $wotg) or die(mysql_error());
$row_monday = mysql_fetch_assoc($monday);
$totalRows_monday = mysql_num_rows($monday);

$colname_tuesday = "-1";
if (isset($_GET['studioID'])) {
  $colname_tuesday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_tuesday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Tuesday' ORDER BY classes.startTime ASC", $studioID);
$tuesday = mysql_query($query_tuesday, $wotg) or die(mysql_error());
$row_tuesday = mysql_fetch_assoc($tuesday);
$totalRows_tuesday = mysql_num_rows($tuesday);

$colname_wednesday = "-1";
if (isset($_GET['studioID'])) {
  $colname_wednesday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_wednesday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Wednesday' ORDER BY classes.startTime ASC", $studioID);
$wednesday = mysql_query($query_wednesday, $wotg) or die(mysql_error());
$row_wednesday = mysql_fetch_assoc($wednesday);
$totalRows_wednesday = mysql_num_rows($wednesday);

$colname_thursday = "-1";
if (isset($_GET['studioID'])) {
  $colname_thursday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_thursday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Thursday' ORDER BY classes.startTime ASC", $studioID);
$thursday = mysql_query($query_thursday, $wotg) or die(mysql_error());
$row_thursday = mysql_fetch_assoc($thursday);
$totalRows_thursday = mysql_num_rows($thursday);

$colname_friday = "-1";
if (isset($_GET['studioID'])) {
  $colname_friday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_friday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Friday' ORDER BY classes.startTime ASC", $studioID);
$friday = mysql_query($query_friday, $wotg) or die(mysql_error());
$row_friday = mysql_fetch_assoc($friday);
$totalRows_friday = mysql_num_rows($friday);

$colname_saturday = "-1";
if (isset($_GET['studioID'])) {
  $colname_saturday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_saturday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Saturday' ORDER BY classes.startTime ASC", $studioID);
$saturday = mysql_query($query_saturday, $wotg) or die(mysql_error());
$row_saturday = mysql_fetch_assoc($saturday);
$totalRows_saturday = mysql_num_rows($saturday);

$colname_sunday = "-1";
if (isset($_GET['studioID'])) {
  $colname_sunday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_sunday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Sunday' ORDER BY classes.startTime ASC", $studioID);
$sunday = mysql_query($query_sunday, $wotg) or die(mysql_error());
$row_sunday = mysql_fetch_assoc($sunday);
$totalRows_sunday = mysql_num_rows($sunday);

//create day arrays
//monday
$mondayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_monday['startTime']));
array_push($mondayClasses, $tempTime.': '.$row_monday['firstName']);
} while ($row_monday = mysql_fetch_assoc($monday));

//tuesday
$tuesdayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_tuesday['startTime']));
array_push($tuesdayClasses, $tempTime.': '.$row_tuesday['firstName']);
} while ($row_tuesday = mysql_fetch_assoc($tuesday));

//wednesday
$wednesdayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_wednesday['startTime']));
array_push($wednesdayClasses, $tempTime.': '.$row_wednesday['firstName']);
} while ($row_wednesday = mysql_fetch_assoc($wednesday));

//thursday
$thursdayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_thursday['startTime']));
array_push($thursdayClasses, $tempTime.': '.$row_thursday['firstName']);
} while ($row_thursday = mysql_fetch_assoc($thursday));

//friday
$fridayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_friday['startTime']));
array_push($fridayClasses, $tempTime.': '.$row_friday['firstName']);
} while ($row_friday = mysql_fetch_assoc($friday));

//saturday
$saturdayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_saturday['startTime']));
array_push($saturdayClasses, $tempTime.': '.$row_saturday['firstName']);
} while ($row_saturday = mysql_fetch_assoc($saturday));

//sunday
$sundayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_sunday['startTime']));
array_push($sundayClasses, $tempTime.': '.$row_sunday['firstName']);
} while ($row_sunday = mysql_fetch_assoc($sunday));
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
<title><?php echo $row_currentUser['studioName']; ?> | Instructor Calendar</title></head>
<body>

<?php include("header.php"); ?>

<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Instructor Calendar</h1>

<?php include("navigation.php"); ?>
<div class="twd_container">


<?php 
/* date settings */
$month = (int) ($_GET['month'] ? $_GET['month'] : date('m'));
$year = (int)  ($_GET['year'] ? $_GET['year'] : date('Y'));

/* draw table */
$calendar = '<table cellpadding="0" cellspacing="0" class="calendar">';

/* table headings */
$headings = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	
$calendar.= '<tr class="calendar-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">',$headings).'</td></tr>';

/* days and weeks vars now ... */
$running_day = date('w',mktime(0,0,0,$month,1,$year));
$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
$days_in_this_week = 1;
$day_counter = 0;
$dates_array = array();

/* row for week one */
$calendar.= '<tr class="calendar-row">';

/* print "blank" days until the first of the current week */
for($x = 0; $x < $running_day; $x++):
	$calendar.= '<td class="calendar-day-np"> </td>';
	$days_in_this_week++;
endfor;

/* keep going with days.... */
for($list_day = 1; $list_day <= $days_in_month; $list_day++):
	$calendar.= '<td class="calendar-day">';
		/* add in the day number */
		$calendar.= '<div class="day-number">'.$list_day.'</div>';

		/** QUERY THE DATABASE FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
		$tempDate = $year.'-'.$month.'-'.$list_day;
		$tempDay = date('l', strtotime( $tempDate));
		$tempEvents = '<p>';
		if($tempDay=='Monday' && $totalRows_monday>0){
		  for($x = 0; $x < count($mondayClasses); $x++) {
			  $tempEvents.=$mondayClasses[$x];
			  $tempEvents.="<br>";
		  }
		}
		if($tempDay=='Tuesday' && $totalRows_tuesday>0){
		  for($x = 0; $x < count($tuesdayClasses); $x++) {
			  $tempEvents.=$tuesdayClasses[$x];
			  $tempEvents.="<br>";
		  }
		}
		if($tempDay=='Wednesday' && $totalRows_wednesday>0){
		  for($x = 0; $x < count($wednesdayClasses); $x++) {
			  $tempEvents.=$wednesdayClasses[$x];
			  $tempEvents.="<br>";
		  }
		}
		if($tempDay=='Thursday' && $totalRows_thursday>0){
		  for($x = 0; $x < count($thursdayClasses); $x++) {
			  $tempEvents.=$thursdayClasses[$x];
			  $tempEvents.="<br>";
		  }
		}
		if($tempDay=='Friday' && $totalRows_friday>0){
		  for($x = 0; $x < count($fridayClasses); $x++) {
			  $tempEvents.=$fridayClasses[$x];
			  $tempEvents.="<br>";
		  }
		}
		if($tempDay=='Saturday' && $totalRows_saturday>0){
		  for($x = 0; $x < count($saturdayClasses); $x++) {
			  $tempEvents.=$saturdayClasses[$x];
			  $tempEvents.="<br>";
		  }
		}
		if($tempDay=='Sunday' && $totalRows_sunday>0){
		  for($x = 0; $x < count($sundayClasses); $x++) {
			  $tempEvents.=$sundayClasses[$x];
			  $tempEvents.="<br>";
		  }
		}
		$calendar.= $tempEvents.'</p>';
		
	$calendar.= '</td>';
	if($running_day == 6):
		$calendar.= '</tr>';
		if(($day_counter+1) != $days_in_month):
			$calendar.= '<tr class="calendar-row">';
		endif;
		$running_day = -1;
		$days_in_this_week = 0;
	endif;
	$days_in_this_week++; $running_day++; $day_counter++;
endfor;

/* finish the rest of the days in the week */
if($days_in_this_week < 8):
	for($x = 1; $x <= (8 - $days_in_this_week); $x++):
		$calendar.= '<td class="calendar-day-np"> </td>';
	endfor;
endif;

/* final row */
$calendar.= '</tr>';

/* end the table */
$calendar.= '</table>';
	
	

/* "next month" control */
$next_month_link = '<a href="?studioID='.$_GET['studioID'].'&month='.($month != 12 ? $month + 1 : 1).'&year='.($month != 12 ? $year : $year + 1).'" class="control">NEXT >></a>';

/* "previous month" control */
$previous_month_link = '<a href="?studioID='.$_GET['studioID'].'&month='.($month != 1 ? $month - 1 : 12).'&year='.($month != 1 ? $year : $year - 1).'" class="control"><< PREV</a>';

/* bringing the controls together */
//$controls = '<form method="get">'.$select_month_control.$select_year_control.' <input type="submit" name="submit" value="Go" />      '.$previous_month_link.'     '.$next_month_link.' </form>';
$controls = '<table width="100%" style="margin-bottom:20px"><tr><td class="previous">'.$previous_month_link.'</td><td class="next">'.$next_month_link.'</td></tr></table>';

echo '<h2>'.date('F',mktime(0,0,0,$month,1,$year)).' '.$year.'</h2>';
echo $controls;
	
/* all done, return result */
echo $calendar;
?>

</div>

<?php include("footer.php"); ?>
<script>
$("#addClass").click(function() {
		if ($("#addClassForm").is(':visible')) {
			$("#addClassForm").slideUp("slow");
			$('#addClass img').attr('src', 'images/plus.png');
		}
		else {
			$("#addClassForm").slideDown("slow");
			$('#addClass img').attr('src', 'images/minus.png');
		}
});
</script>
</body>
</html>
<?php
mysql_free_result($monday);
mysql_free_result($tuesday);
mysql_free_result($wednesday);
mysql_free_result($thursday);
mysql_free_result($friday);
mysql_free_result($saturday);
mysql_free_result($sunday);
mysql_free_result($currentUser);
?>