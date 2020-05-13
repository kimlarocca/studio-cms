<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
?>
<?php require_once('Connections/wotg.php'); ?>
<?php
//get current day of the week
$currentDate = date("Y-m-d");
if ($_GET['currentDate'] != '') $currentDate = $_GET['currentDate'];
?>
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

$currentPage = $_SERVER["PHP_SELF"];

$colname_currentUser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_currentUser = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_currentUser = sprintf("SELECT * FROM instructors,studios WHERE instructors.studioID=studios.studioID AND instructors.username = %s", GetSQLValueString($colname_currentUser, "text"));
$currentUser = mysql_query($query_currentUser, $wotg) or die(mysql_error());
$row_currentUser = mysql_fetch_assoc($currentUser);
$totalRows_currentUser = mysql_num_rows($currentUser);

$maxRows_attendance = 10000;
$pageNum_attendance = 0;
if (isset($_GET['pageNum_attendance'])) {
  $pageNum_attendance = $_GET['pageNum_attendance'];
}
$startRow_attendance = $pageNum_attendance * $maxRows_attendance;

mysql_select_db($database_wotg, $wotg);
$query_attendance = "SELECT students.studentID,students.waiver,attendance.attendanceID, instructors.instructorID,instructors.firstName AS instructorName, classes.startTime,classes.classFee, classes.prepaidFee, classes.name as className, classes.classID, students.firstName, students.lastName,attendance.dateAdded as attendanceDate,attendance.attendanceType,attendance.addedBy,attendance.dropInName,attendance.dropInEmail,attendance.paymentType,attendance.classID,attendance.instructorID FROM attendance,students,classes,instructors WHERE attendance.studioID = ".$row_currentUser['studioID']." AND date(attendance.dateAdded) = '".$currentDate."' AND attendance.studentID = students.studentID AND attendance.classID=classes.classID AND instructors.instructorID=attendance.instructorID ORDER BY classes.startTime";
$query_limit_attendance = sprintf("%s LIMIT %d, %d", $query_attendance, $startRow_attendance, $maxRows_attendance);
$attendance = mysql_query($query_limit_attendance, $wotg) or die(mysql_error());
$row_attendance = mysql_fetch_assoc($attendance);

if (isset($_GET['totalRows_attendance'])) {
  $totalRows_attendance = $_GET['totalRows_attendance'];
} else {
  $all_attendance = mysql_query($query_attendance);
  $totalRows_attendance = mysql_num_rows($all_attendance);
}
$totalPages_attendance = ceil($totalRows_attendance/$maxRows_attendance)-1;

$queryString_attendance = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_attendance") == false && 
        stristr($param, "totalRows_attendance") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_attendance = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_attendance = sprintf("&totalRows_attendance=%d%s", $totalRows_attendance, $queryString_attendance);
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
<title><?php echo $row_currentUser['studioName']; ?> | Daily Attendance Report</title>
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Daily Attendance Report</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
  <h2 class="twd_centered">Daily Attendance for <?php echo $currentDate ?></h2>
  <div class="twd_centered twd_margin20">
  <form style="width:320px" class="twd_centered">
  <input class="twd_centered twd_margin10" type="text" id="currentDate" name="currentDate" placeholder="change the date...">
      <input class="twd_centered" name="Go" type="submit" value="Go" />
  </form>
  </div>
  <?php
  if  ($totalRows_attendance == 0) echo '<p class="twd_centered" style="color:red">No attendance records found for '.$currentDate.'.</p>';
  else {
  ?>
  <table border="0" cellpadding="5" cellspacing="0" align="center" class="twd_margin20">
    <thead><tr>
      <th><strong>name</strong></th>
      <th><strong>date</strong></th>
      <th><strong>time</strong></th>
      <th><strong>class</strong></th>
      <th><strong>instructor</strong></th>
      <th><strong>type</strong></th>
      <th><strong>payment</strong></th>
      <th><strong>fee</strong></th>
      <th></th>
    </tr></thead>
    <?php 
	$totalRevenue = 0;
	$totalCash = 0;
	do { 
	$formattedTime = date("g:i a", strtotime($row_attendance['startTime']));
	?>
      <tr>
        <td><a href="students-update.php?studentID=<?php echo $row_attendance['studentID']; ?>"><?php echo $row_attendance['firstName']; ?> <?php echo $row_attendance['lastName']; ?></a> (<?php echo $row_attendance['attendanceID']; ?>)</td>
        <td><?php echo $row_attendance['attendanceDate']; ?></td>
        <td><?php echo $formattedTime; ?></td>
        <td><?php echo $row_attendance['className']; ?></td>
        <td><?php echo $row_attendance['instructorName']; ?></td>
        <td><?php echo $row_attendance['attendanceType']; ?></td>
        <td><?php echo $row_attendance['paymentType']; ?></td>
        <td>
		<?php 
		if ($row_attendance['firstName'].' '.$row_attendance['lastName'] != 'No One'){
		if ($row_attendance['attendanceType'] == 'Drop In') {
			echo '$'.$row_attendance['classFee']; 
			$totalRevenue = $totalRevenue+$row_attendance['classFee'];
			if ($row_attendance['paymentType'] == 'Cash') $totalCash = $totalCash+$row_attendance['classFee'];
		}
		if ($row_attendance['attendanceType'] == 'Pre Paid') {
			echo '$'.$row_attendance['prepaidFee']; 
			$totalRevenue = $totalRevenue+$row_attendance['prepaidFee'];
		}
		}
		?>
        </td>
        <td> 
		<a href="attendance-fullUpdate.php?attendanceID=<?php echo $row_attendance['attendanceID']; ?>"><img src="images/edit.png" alt="" width="20" height="20"/></a> <a href="javescript:void();" onclick="window.open('attendance-delete.php?attendanceID=<?php echo $row_attendance['attendanceID']; ?>','Delete Attendance','height=500,width=300');return false;"><img src="images/delete.png" width="20" height="20" /></a> 
		<?php
		if ($row_attendance['waiver'] != 1) {
		?>
        <a href="waiver.php?studentID=<?php echo $row_attendance['studentID']; ?>" style="color:red">Need Waiver</a>
        <?php
	}
	?></td>
      </tr>
      <?php } while ($row_attendance = mysql_fetch_assoc($attendance)); ?>
  </table>
  <p class="twd_centered twd_margin20"><strong>Total Cash: $<?php echo $totalCash; ?></strong></p>
  <p class="twd_centered twd_margin20"><strong>Total Revenue: $<?php echo $totalRevenue; ?></strong> (not including members)</p>
  <?php } ?>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript" src="datePicker/picker.js"></script> 
<script>
/**
 * pick a date
 */
$('#currentDate').pickadate({
  onOpen: function() {
    scrollIntoView( this.$node )
  },
  format: 'yyyy-mm-dd'
})
function scrollIntoView( $node ) {
  $('html,body').animate({
      scrollTop: ~~$node.offset().top - 60
  })
}
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($attendance);
?>