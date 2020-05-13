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

/* date settings */
$month = (int) ($_GET['month'] ? $_GET['month'] : date('m'));
$year = (int)  ($_GET['year'] ? $_GET['year'] : date('Y'));

$colname_monday = "-1";
if (isset($_GET['studioID'])) {
  $colname_monday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_monday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Monday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_monday, "int"));
$monday = mysql_query($query_monday, $wotg) or die(mysql_error());
$row_monday = mysql_fetch_assoc($monday);
$totalRows_monday = mysql_num_rows($monday);

$colname_tuesday = "-1";
if (isset($_GET['studioID'])) {
  $colname_tuesday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_tuesday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Tuesday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_tuesday, "int"));
$tuesday = mysql_query($query_tuesday, $wotg) or die(mysql_error());
$row_tuesday = mysql_fetch_assoc($tuesday);
$totalRows_tuesday = mysql_num_rows($tuesday);

$colname_wednesday = "-1";
if (isset($_GET['studioID'])) {
  $colname_wednesday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_wednesday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Wednesday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_wednesday, "int"));
$wednesday = mysql_query($query_wednesday, $wotg) or die(mysql_error());
$row_wednesday = mysql_fetch_assoc($wednesday);
$totalRows_wednesday = mysql_num_rows($wednesday);

$colname_thursday = "-1";
if (isset($_GET['studioID'])) {
  $colname_thursday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_thursday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Thursday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_thursday, "int"));
$thursday = mysql_query($query_thursday, $wotg) or die(mysql_error());
$row_thursday = mysql_fetch_assoc($thursday);
$totalRows_thursday = mysql_num_rows($thursday);

$colname_friday = "-1";
if (isset($_GET['studioID'])) {
  $colname_friday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_friday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Friday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_friday, "int"));
$friday = mysql_query($query_friday, $wotg) or die(mysql_error());
$row_friday = mysql_fetch_assoc($friday);
$totalRows_friday = mysql_num_rows($friday);

$colname_saturday = "-1";
if (isset($_GET['studioID'])) {
  $colname_saturday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_saturday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Saturday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_saturday, "int"));
$saturday = mysql_query($query_saturday, $wotg) or die(mysql_error());
$row_saturday = mysql_fetch_assoc($saturday);
$totalRows_saturday = mysql_num_rows($saturday);

$colname_sunday = "-1";
if (isset($_GET['studioID'])) {
  $colname_sunday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_sunday = sprintf("SELECT classes.classID, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Sunday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_sunday, "int"));
$sunday = mysql_query($query_sunday, $wotg) or die(mysql_error());
$row_sunday = mysql_fetch_assoc($sunday);
$totalRows_sunday = mysql_num_rows($sunday);

$colname_studio = "-1";
if (isset($_GET['studioID'])) {
  $colname_studio = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_studio = sprintf("SELECT studioID, color FROM studios WHERE studioID = %s", GetSQLValueString($colname_studio, "int"));
$studio = mysql_query($query_studio, $wotg) or die(mysql_error());
$row_studio = mysql_fetch_assoc($studio);
$totalRows_studio = mysql_num_rows($studio);

$colname_events = "-1";
if (isset($_GET['studioID'])) {
  $colname_events = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_events = sprintf("SELECT eventID, studioID, eventDate, eventStartTime, eventName FROM events WHERE year(eventDate) = ".$year." AND month(eventDate) = ".$month." AND studioID = %s", GetSQLValueString($colname_events, "int"));
$events = mysql_query($query_events, $wotg) or die(mysql_error());
$row_events = mysql_fetch_assoc($events);
$totalRows_events = mysql_num_rows($events);

//create day arrays
//monday
$mondayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_monday['startTime']));
//array_push($mondayClasses, $tempTime.': '.'<a target="_parent" href="https://my.slateit.com/student-login.php?classID='.$row_monday['classID'].'&studioID='.$row_monday['studioID'].'">'.$row_monday['name'].'</a>');
array_push($mondayClasses, $tempTime.': '.$row_monday['name']);
} while ($row_monday = mysql_fetch_assoc($monday));

//tuesday
$tuesdayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_tuesday['startTime']));
array_push($tuesdayClasses, $tempTime.': '.$row_tuesday['name']);
} while ($row_tuesday = mysql_fetch_assoc($tuesday));

//wednesday
$wednesdayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_wednesday['startTime']));
array_push($wednesdayClasses, $tempTime.': '.$row_wednesday['name']);
} while ($row_wednesday = mysql_fetch_assoc($wednesday));

//thursday
$thursdayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_thursday['startTime']));
array_push($thursdayClasses, $tempTime.': '.$row_thursday['name']);
} while ($row_thursday = mysql_fetch_assoc($thursday));

//friday
$fridayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_friday['startTime']));
array_push($fridayClasses, $tempTime.': '.$row_friday['name']);
} while ($row_friday = mysql_fetch_assoc($friday));

//saturday
$saturdayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_saturday['startTime']));
array_push($saturdayClasses, $tempTime.': '.$row_saturday['name']);
} while ($row_saturday = mysql_fetch_assoc($saturday));

//sunday
$sundayClasses = array();
do {
$tempTime = date('g:i A', strtotime($row_sunday['startTime']));
array_push($sundayClasses, $tempTime.': '.$row_sunday['name']);
} while ($row_sunday = mysql_fetch_assoc($sunday));

//check for cancellations
mysql_select_db($database_wotg, $wotg);
$query_cancellations = "SELECT classCancellations.cancellationDate,classCancellations.studioID,classCancellations.classID,classes.name,classes.classID FROM classCancellations,classes WHERE classCancellations.classID = classes.classID AND classCancellations.cancelAllClasses = 0 AND year(classCancellations.cancellationDate) = ".$year." AND month(classCancellations.cancellationDate) = ".$month." AND classCancellations.studioID=".$_GET['studioID'];
$cancellations = mysql_query($query_cancellations, $wotg) or die(mysql_error());
$row_cancellations = mysql_fetch_assoc($cancellations);
$totalRows_cancellations = mysql_num_rows($cancellations);

mysql_select_db($database_wotg, $wotg);
$query_cancellations2 = "SELECT cancelAllClasses,cancellationDate,studioID FROM classCancellations WHERE cancelAllClasses = 1 AND year(cancellationDate) = ".$year." AND month(cancellationDate) = ".$month." AND studioID=".$_GET['studioID'];
$cancellations2 = mysql_query($query_cancellations2, $wotg) or die(mysql_error());
$row_cancellations2 = mysql_fetch_assoc($cancellations2);
$totalRows_cancellations2 = mysql_num_rows($cancellations2);

$cancellationsArray = array();
do { 
array_push($cancellationsArray, array("date"=>$row_cancellations['cancellationDate'], "name"=>$row_cancellations['name']));
} while ($row_cancellations = mysql_fetch_assoc($cancellations));

$cancellationsArray2 = array();
do { 
array_push($cancellationsArray2, $row_cancellations2['cancellationDate']);
} while ($row_cancellations2 = mysql_fetch_assoc($cancellations2));

$eventsArray = array();
do { 
$newTime = date('g:i A', strtotime($row_events['eventStartTime']));
array_push($eventsArray, array("date"=>$row_events['eventDate'], "time"=>$newTime, "name"=>$row_events['eventName']));
} while ($row_events = mysql_fetch_assoc($events));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Studio Calendar</title>
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
	color:<?php echo $row_studio['color'];?>;
	width:100%;
	margin:0;
	font-size:20px;
	padding:0 0 20px 0;
	clear:both;
}
.button {
 color: <?php echo $row_studio['color'];?>!important;
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
 	background-color:<?php echo $row_studio['color'];?>;
}
/* calendar */
table.calendar {
	border-left:1px solid <?php echo $row_studio['color'];?>;
	width:100%;
}
tr.calendar-row {
}
td.calendar-day {
	min-height:80px;
	font-size:11px;
	position:relative;
}
.today { background-color: #f0f0f0; }
* html div.calendar-day {
	height:80px;
}
/*td.calendar-day:hover	{ background:#f0f0f0; }*/
td.calendar-day-np {
	background:#f0f0f0;
	height: 30px;
}
* html div.calendar-day-np {
	height:80px;
}
td.calendar-day-head {
	background:<?php echo $row_studio['color'];?>;
	font-weight:bold;
	color:#fff;
	text-align:center;
	width:120px;
	padding:5px;
	border-bottom:1px solid <?php echo $row_studio['color'];?>;
	border-top:1px solid <?php echo $row_studio['color'];?>;
	border-right:1px solid <?php echo $row_studio['color'];?>;
}
div.day-number {
	background:#bbb;
	padding:5px;
	color:#fff;
	font-weight:bold;
	float:right;
	margin:-5px -5px 0 0;
	width:20px;
	text-align:center;
}
/* shared */
td.calendar-day, td.calendar-day-np {
	width:120px; vertical-align:top;
	padding:5px;
	border-bottom:1px solid <?php echo $row_studio['color'];?>;
	border-right:1px solid <?php echo $row_studio['color'];?>;
}
.previous {
	text-align:left; display:table-cell!important; width:49%!important;
}
.next {
	text-align:right; display:table-cell!important; width:49%!important;
}
@media screen and (max-width: 767px) {
	.calendar-day-head { 
		position: absolute;
		top: -9999px;
		left: -9999px;
	}
	.calendar-day-np { display:none; }
	table {
	width:100%;
}
table, thead, tbody, th, td, tr {
	display: block;
}
tr.calendar-row:first-of-type {
	border-top:1px solid <?php echo $row_studio['color'];?>;
}
td, td.calendar-day, td.calendar-day-np {
	width:100%;
	position: relative;
}
}
</style>
</head>

<body>
<?php 
function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }
    return false;
}

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

//get today's date
$today = date("Y-m-d", strtotime("now"));

/* keep going with days.... */
for($list_day = 1; $list_day <= $days_in_month; $list_day++):
		
		$tempDate = $year.'-'.$month.'-'.$list_day;
		$formattedDate = date("Y-m-d", strtotime($tempDate));
		$tempDay = date('l', strtotime( $tempDate));
		
		//check if this day is today
		if($today==$formattedDate) { $calendar.= '<td class="calendar-day today">'; }
		else $calendar.= '<td class="calendar-day">';
		
		/* add in the day number */
		$calendar.= '<div class="day-number">'.$list_day.'</div>';

		/** QUERY THE DATABASE FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
		$tempEvents = '<p>';
		
		//check for cancellations
		if (in_array($formattedDate, $cancellationsArray2)) {
			$tempEvents.="<span style='color:red'>All classes are cancelled today!</span>";
		} 
		else {
		
		  if($tempDay=='Monday' && $totalRows_monday>0){
			$tempEvents.='<u>Classes:<br></u>';
			for($x = 0; $x < count($mondayClasses); $x++) {	  
				$tempEvents.=$mondayClasses[$x];
				$tempEvents.="<br>";
			}
			$tempEvents.="<br>";
		  }
		  else $tempEvents='';
		  if($tempDay=='Tuesday' && $totalRows_tuesday>0){
			$tempEvents.='<u>Classes:<br></u>';
			for($x = 0; $x < count($tuesdayClasses); $x++) {
				$tempEvents.=$tuesdayClasses[$x];
				$tempEvents.="<br>";
			}
			$tempEvents.="<br>";
		  }
		  if($tempDay=='Wednesday' && $totalRows_wednesday>0){
			$tempEvents.='<u>Classes:<br></u>';
			for($x = 0; $x < count($wednesdayClasses); $x++) {
				$tempEvents.=$wednesdayClasses[$x];
				$tempEvents.="<br>";
			}
			$tempEvents.="<br>";
		  }
		  if($tempDay=='Thursday' && $totalRows_thursday>0){
			$tempEvents.='<u>Classes:<br></u>';
			for($x = 0; $x < count($thursdayClasses); $x++) {
				$tempEvents.=$thursdayClasses[$x];
				$tempEvents.="<br>";
			}
			$tempEvents.="<br>";
		  }
		  if($tempDay=='Friday' && $totalRows_friday>0){
			$tempEvents.='<u>Classes:<br></u>';
			for($x = 0; $x < count($fridayClasses); $x++) {
				$tempEvents.=$fridayClasses[$x];
				$tempEvents.="<br>";
			}
			$tempEvents.="<br>";
		  }
		  if($tempDay=='Saturday' && $totalRows_saturday>0){
			$tempEvents.='<u>Classes:<br></u>';
			for($x = 0; $x < count($saturdayClasses); $x++) {
				$tempEvents.=$saturdayClasses[$x];
				$tempEvents.="<br>";
			}
		  }
		  if($tempDay=='Sunday' && $totalRows_sunday>0){
			$tempEvents.='<u>Classes:<br></u>';
			for($x = 0; $x < count($sundayClasses); $x++) {
				$tempEvents.=$sundayClasses[$x];
				$tempEvents.="<br>";
			}
			$tempEvents.="<br>";
		  }
		  
		}
		
		//check for single class cancellations
		/*
		if(in_array_r($formattedDate, $cancellationsArray)){
			$tempEvents.="<br><u>Cancellations:<br></u>";
			$keys = array_keys($cancellationsArray);
			for($i = 0; $i < count($cancellationsArray); $i++) {
				$next = FALSE;
				foreach($cancellationsArray[$keys[$i]] as $key => $value) {
						if($next==TRUE){
							$tempEvents.="<span style='color:red'>".$value." is cancelled today!</span><br>";
							$next=FALSE;
						}
						if($formattedDate==$value) $next = TRUE;
						
				}
			}
		}
		*/
		if(in_array_r($formattedDate, $cancellationsArray)){
		  $tempEvents.="<br><u>Cancellations:<br></u>";
		  foreach($cancellationsArray as $row) {
			if($formattedDate==$row['date']) {
				$tempEvents.="<span style='color:red'>".$row['name']." is cancelled today!</span><br>";
			}
			}
		}
		
		//check for events
		if(in_array_r($formattedDate, $eventsArray)){
		  $tempEvents.="<u>Events:<br></u>";
		  foreach($eventsArray as $row) {
			if($formattedDate==$row['date']) {
				$tempEvents.=$row['time'].": ".$row['name']."<br>";
			}
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
mysql_free_result($studio);

mysql_free_result($events);
?>