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

$colname_monday = "-1";
if (isset($_GET['studioID'])) {
  $colname_monday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_monday = sprintf("SELECT classes.classID, classes.imemo, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Monday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_monday, "int"));
$monday = mysql_query($query_monday, $wotg) or die(mysql_error());
$row_monday = mysql_fetch_assoc($monday);
$totalRows_monday = mysql_num_rows($monday);

$colname_tuesday = "-1";
if (isset($_GET['studioID'])) {
  $colname_tuesday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_tuesday = sprintf("SELECT classes.classID, classes.imemo, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Tuesday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_tuesday, "int"));
$tuesday = mysql_query($query_tuesday, $wotg) or die(mysql_error());
$row_tuesday = mysql_fetch_assoc($tuesday);
$totalRows_tuesday = mysql_num_rows($tuesday);

$colname_wednesday = "-1";
if (isset($_GET['studioID'])) {
  $colname_wednesday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_wednesday = sprintf("SELECT classes.classID, classes.imemo, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Wednesday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_wednesday, "int"));
$wednesday = mysql_query($query_wednesday, $wotg) or die(mysql_error());
$row_wednesday = mysql_fetch_assoc($wednesday);
$totalRows_wednesday = mysql_num_rows($wednesday);

$colname_thursday = "-1";
if (isset($_GET['studioID'])) {
  $colname_thursday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_thursday = sprintf("SELECT classes.classID, classes.imemo, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Thursday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_thursday, "int"));
$thursday = mysql_query($query_thursday, $wotg) or die(mysql_error());
$row_thursday = mysql_fetch_assoc($thursday);
$totalRows_thursday = mysql_num_rows($thursday);

$colname_friday = "-1";
if (isset($_GET['studioID'])) {
  $colname_friday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_friday = sprintf("SELECT classes.classID, classes.imemo, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Friday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_friday, "int"));
$friday = mysql_query($query_friday, $wotg) or die(mysql_error());
$row_friday = mysql_fetch_assoc($friday);
$totalRows_friday = mysql_num_rows($friday);

$colname_saturday = "-1";
if (isset($_GET['studioID'])) {
  $colname_saturday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_saturday = sprintf("SELECT classes.classID, classes.imemo, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Saturday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_saturday, "int"));
$saturday = mysql_query($query_saturday, $wotg) or die(mysql_error());
$row_saturday = mysql_fetch_assoc($saturday);
$totalRows_saturday = mysql_num_rows($saturday);

$colname_sunday = "-1";
if (isset($_GET['studioID'])) {
  $colname_sunday = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_sunday = sprintf("SELECT classes.classID, classes.imemo, classes.name, classes.classDay, classes.startTime, classes.endTime, classes.instructorID, classes.classActive, classes.studioID, classes.thumbnail, instructors.firstName FROM classes,instructors WHERE classes.classActive=1 AND classes.studioID = %s AND classes.instructorID=instructors.instructorID AND classes.classDay='Sunday' ORDER BY classes.startTime ASC", GetSQLValueString($colname_sunday, "int"));
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Schedule Of Classes</title>
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
	background-color:<?php echo $row_studio['color'];?>;
	width:100%;
	color:#fff;
	margin:0 0 10px 0; font-size:20px;
	padding:5px 1% 5px 1%; clear:both;
}
.button {
	color: <?php echo $row_studio['color'];?>!important;
	background-color:#fff;
	border: 1px solid <?php echo $row_studio['color'];?>;
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
.sRow {
	margin: 0 1% 20px;
	float: left;
	width: 48%
}
.sRow p {
	padding:10px 10px 0 10px; margin:0;
}
.sColumn1 {
	width: 100 px;
	margin-left: 0;
	padding-top: 10px;
	float: left
}
.sColumn2 {
	margin-left: 100px;
	text-align: left
}
@media screen and (max-width: 767px) {
.sRow {
	width: 95%
}
}
</style>
</head>

<body>

<?php if($totalRows_monday>0) { ?>
<h2>Mondays</h2>
<?php do { ?>
<div class="sRow">
    <div class="sColumn1"><img src="uploads/<?php echo $row_monday['thumbnail']; ?>" width="100" height="100" /></div>
    <div class="sColumn2">
      <p><?php echo date('g:i A', strtotime($row_monday['startTime'])); ?> <strong><?php echo $row_monday['name']; ?></strong></p>
        <p><em>Instructor: <?php echo $row_monday['firstName']; ?></em></p>
        <?php if($row_monday['imemo']!=NULL){ ?>
        <p class="showDescription"><a class="button" href="javascript:void();">class description</a></p>
        	<p class="classDescription" style="display:none"><?php echo $row_monday['imemo']; ?></p>
		<?php } ?>
        <p><a class="button" target="_parent" style="margin-bottom:10px" href="https://my.slateit.com/student-login.php?action=class&classID=<?php echo $row_monday['classID']; ?>&studioID=<?php echo $row_studio['studioID']; ?>">register now</a></p>
    </div>    
</div>
<?php } while ($row_monday = mysql_fetch_assoc($monday)); }?>

<?php if($totalRows_tuesday>0) { ?>
<h2>Tuesdays</h2>
<?php do { ?>
<div class="sRow">
    <div class="sColumn1"><img src="uploads/<?php echo $row_tuesday['thumbnail']; ?>" width="100" height="100" /></div>
    <div class="sColumn2">
      <p><?php echo date('g:i A', strtotime($row_tuesday['startTime'])); ?> <strong><?php echo $row_tuesday['name']; ?></strong></p>
        <p><em>Instructor: <?php echo $row_tuesday['firstName']; ?></em></p>
        <?php if($row_tuesday['imemo']!=NULL){ ?>
        <p class="showDescription"><a class="button" href="javascript:void();">class description</a></p>
        	<p class="classDescription" style="display:none"><?php echo $row_tuesday['imemo']; ?></p>
		<?php } ?>
        <p><a class="button" target="_parent" style="margin-bottom:10px" href="https://my.slateit.com/student-login.php?action=class&classID=<?php echo $row_tuesday['classID']; ?>&studioID=<?php echo $row_studio['studioID']; ?>">register now</a></p>
    </div>    
</div>
<?php } while ($row_tuesday = mysql_fetch_assoc($tuesday)); }?>

<?php if($totalRows_wednesday>0) { ?>
<h2>Wednesdays</h2>
<?php do { ?>
<div class="sRow">
    <div class="sColumn1"><img src="uploads/<?php echo $row_wednesday['thumbnail']; ?>" width="100" height="100" /></div>
    <div class="sColumn2">
      <p><?php echo date('g:i A', strtotime($row_wednesday['startTime'])); ?> <strong><?php echo $row_wednesday['name']; ?></strong></p>
        <p><em>Instructor: <?php echo $row_wednesday['firstName']; ?></em></p>
        <?php if($row_wednesday['imemo']!=NULL){ ?>
        <p class="showDescription"><a class="button" href="javascript:void();">class description</a></p>
        	<p class="classDescription" style="display:none"><?php echo $row_wednesday['imemo']; ?></p>
		<?php } ?>
        <p><a class="button" target="_parent" style="margin-bottom:10px" href="https://my.slateit.com/student-login.php?action=class&classID=<?php echo $row_wednesday['classID']; ?>&studioID=<?php echo $row_studio['studioID']; ?>">register now</a></p>
    </div>    
</div>
<?php } while ($row_wednesday = mysql_fetch_assoc($wednesday)); }?>

<?php if($totalRows_thursday>0) { ?>
<h2>Thursdays</h2>
<?php do { ?>
<div class="sRow">
    <div class="sColumn1"><img src="uploads/<?php echo $row_thursday['thumbnail']; ?>" width="100" height="100" /></div>
    <div class="sColumn2">
      <p><?php echo date('g:i A', strtotime($row_thursday['startTime'])); ?> <strong><?php echo $row_thursday['name']; ?></strong></p>
        <p><em>Instructor: <?php echo $row_thursday['firstName']; ?></em></p>
        <?php if($row_thursday['imemo']!=NULL){ ?>
        <p class="showDescription"><a class="button" href="javascript:void();">class description</a></p>
        	<p class="classDescription" style="display:none"><?php echo $row_thursday['imemo']; ?></p>
		<?php } ?>
        <p><a class="button" target="_parent" style="margin-bottom:10px" href="https://my.slateit.com/student-login.php?action=class&classID=<?php echo $row_thursday['classID']; ?>&studioID=<?php echo $row_studio['studioID']; ?>">register now</a></p>
    </div>    
</div>
<?php } while ($row_thursday = mysql_fetch_assoc($thursday)); }?>

<?php if($totalRows_friday>0) { ?>
<h2>Fridays</h2>
<?php do { ?>
<div class="sRow">
    <div class="sColumn1"><img src="uploads/<?php echo $row_friday['thumbnail']; ?>" width="100" height="100" /></div>
    <div class="sColumn2">
      <p><?php echo date('g:i A', strtotime($row_friday['startTime'])); ?> <strong><?php echo $row_friday['name']; ?></strong></p>
        <p><em>Instructor: <?php echo $row_friday['firstName']; ?></em></p>
        <?php if($row_friday['imemo']!=NULL){ ?>
        <p class="showDescription"><a class="button" href="javascript:void();">class description</a></p>
        	<p class="classDescription" style="display:none"><?php echo $row_friday['imemo']; ?></p>
		<?php } ?>
        <p><a class="button" target="_parent" style="margin-bottom:10px" href="https://my.slateit.com/student-login.php?action=class&classID=<?php echo $row_friday['classID']; ?>&studioID=<?php echo $row_studio['studioID']; ?>">register now</a></p>
    </div>    
</div>
<?php } while ($row_friday = mysql_fetch_assoc($friday)); }?>

<?php if($totalRows_saturday>0) { ?>
<h2>Saturdays</h2>
<?php do { ?>
<div class="sRow">
    <div class="sColumn1"><img src="uploads/<?php echo $row_saturday['thumbnail']; ?>" width="100" height="100" /></div>
    <div class="sColumn2">
      <p><?php echo date('g:i A', strtotime($row_saturday['startTime'])); ?> <strong><?php echo $row_saturday['name']; ?></strong></p>
        <p><em>Instructor: <?php echo $row_saturday['firstName']; ?></em></p>
        <?php if($row_saturday['imemo']!=NULL){ ?>
        <p class="showDescription"><a class="button" href="javascript:void();">class description</a></p>
        	<p class="classDescription" style="display:none"><?php echo $row_saturday['imemo']; ?></p>
		<?php } ?>
        <p><a class="button" target="_parent" style="margin-bottom:10px" href="https://my.slateit.com/student-login.php?action=class&classID=<?php echo $row_saturday['classID']; ?>&studioID=<?php echo $row_studio['studioID']; ?>">register now</a></p>
    </div>    
</div>
<?php } while ($row_saturday = mysql_fetch_assoc($saturday)); } ?>

<?php if($totalRows_sunday>0) { ?>
<h2>Sundays</h2>
<?php do { ?>
<div class="sRow">
    <div class="sColumn1"><img src="uploads/<?php echo $row_sunday['thumbnail']; ?>" width="100" height="100" /></div>
    <div class="sColumn2">
      <p><?php echo date('g:i A', strtotime($row_sunday['startTime'])); ?> <strong><?php echo $row_sunday['name']; ?></strong></p>
        <p><em>Instructor: <?php echo $row_sunday['firstName']; ?></em></p>
        <?php if($row_sunday['imemo']!=NULL){ ?>
        <p class="showDescription"><a class="button" href="javascript:void();">class description</a></p>
        	<p class="classDescription" style="display:none"><?php echo $row_sunday['imemo']; ?></p>
		<?php } ?>
        <p><a class="button" target="_parent" style="margin-bottom:10px" href="https://my.slateit.com/student-login.php?action=class&classID=<?php echo $row_sunday['classID']; ?>&studioID=<?php echo $row_studio['studioID']; ?>">register now</a></p>
    </div>    
</div>
<?php } while ($row_sunday = mysql_fetch_assoc($sunday)); } ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
$(".showDescription").click(function() {
	$(this).next(".classDescription").slideToggle();
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
mysql_free_result($studio);
?>
