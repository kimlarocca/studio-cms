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
$query_timeSlots = "
SELECT classDay, startTime, COUNT( attendanceID ) 
FROM classes
LEFT JOIN attendance ON classes.classID = attendance.classID
WHERE classes.studioID =".$studioID."
GROUP BY classDay, startTime
ORDER BY COUNT( attendanceID ) DESC
";
$timeSlots = mysql_query($query_timeSlots, $wotg) or die(mysql_error());
$row_timeSlots = mysql_fetch_assoc($timeSlots);
$totalRows_timeSlots = mysql_num_rows($timeSlots);

$studioID = $row_currentUser['studioID'];

if ($row_currentUser['securityLevel'] == 'instructor') header("Location: index.php?action=denied");
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
<title><?php echo $row_currentUser['studioName']; ?>| Dashboard</title>
<script src="Chart.min.js"></script>
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Dashboard</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
  <h2 class="twd_centered">Attendance: Most Popular Time Slots</h2>
  <p class="twd_centered twd_margin20">Your most popular time slots of all time!</p>
  <div id="canvasAttendance" class="twd_margin20 twd_centered">
    <canvas id="chartAttendance" width="480" height="480" class="twd_centered"/>
  </div>
  <table border="0" cellpadding="5" cellspacing="0" align="center">
    <tr>
      <td><strong>Day</strong></td>
      <td><strong>Time</strong></td>
      <td><strong># Students</strong></td>
    </tr>
    <?php 
	$count = 0;
	$amount1 = 0;
	$amount2 = 0;
	$amount3 = 0;
	$amount4 = 0;
	$amount5 = 0;
	$name1 = '';
	$name2 = '';
	$name3 = '';
	$name4 = '';
	$name5 = '';
	do { 
	++$count;
	if($count==1){
		$amount1 = $row_timeSlots['COUNT( attendanceID )'];
		$name1 = $row_timeSlots['classDay'].' '.date('h:i A', strtotime($row_timeSlots['startTime']));;	
	}
	if($count==2){
		$amount2 = $row_timeSlots['COUNT( attendanceID )'];
		$name2 = $row_timeSlots['classDay'].' '.date('h:i A', strtotime($row_timeSlots['startTime']));;	
	}
	if($count==3){
		$amount3 = $row_timeSlots['COUNT( attendanceID )'];
		$name3 = $row_timeSlots['classDay'].' '.date('h:i A', strtotime($row_timeSlots['startTime']));;	
	}
	if($count==4){
		$amount4 = $row_timeSlots['COUNT( attendanceID )'];
		$name4 = $row_timeSlots['classDay'].' '.date('h:i A', strtotime($row_timeSlots['startTime']));;	
	}
	if($count==5){
		$amount5 = $row_timeSlots['COUNT( attendanceID )'];
		$name5 = $row_timeSlots['classDay'].' '.date('h:i A', strtotime($row_timeSlots['startTime']));;	
	}
	?>
      <tr>
        <td><?php echo $row_timeSlots['classDay']; ?></td>
        <td><?php echo date('h:i A', strtotime($row_timeSlots['startTime'])); ?></td>
        <td><?php echo $row_timeSlots['COUNT( attendanceID )']; ?></td>
      </tr>
      <?php } while ($row_timeSlots = mysql_fetch_assoc($timeSlots)); ?>
  </table>
</div>
<script>
var data = [
    {
        value: <?php echo $amount1; ?>,
        color:"#F7464A",
        highlight: "#FF5A5E",
        label: "<?php echo $name1; ?>"
    },
    {
        value: <?php echo $amount2; ?>,
        color: "#cd7577",
        highlight: "#d89192",
        label: "<?php echo $name2; ?>"
    },
    {
        value: <?php echo $amount3; ?>,
        color: "#70adc7",
        highlight: "#94c3d7",
        label: "<?php echo $name3; ?>"
    },
    {
        value: <?php echo $amount4; ?>,
        color: "#87a3af",
        highlight: "#a3b9c2",
        label: "<?php echo $name4; ?>"
    },
    {
        value: <?php echo $amount5; ?>,
        color: "#ccc",
        highlight: "#c0c0c0",
        label: "<?php echo $name5; ?>"
    }
]
</script>
  
  
</div>
<?php include("footer.php"); ?>
<script>


window.onload = function(){
	var ctxAttendance = document.getElementById("chartAttendance").getContext("2d");
	window.myPieAttendance = new Chart(ctxAttendance).Pie(data);
};
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($timeSlots);
?>