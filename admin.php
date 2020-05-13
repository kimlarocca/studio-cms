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

if ($row_currentUser['securityLevel'] != 'administrator' AND $row_currentUser['securityLevel'] != 'super') header("Location: index.php?action=denied");
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
<title><?php echo $row_currentUser['studioName']; ?> | Dashboard</title>
<script src="Chart.min.js"></script>
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Dashboard</h1>
<?php include("navigation.php"); ?>
<?php
if ($row_currentUser['accountVerified']==0) {
?>
<div style="background-color:#C00; padding:10px; margin:20px 0 20px 0" class="twd_centered"><a href="verify.php" style="color:#FFF;">PLEASE VERIFY YOUR EMAIL ADDRESS! Click here to resend the verification email.</a></div>
<?php } ?>
<div class="moduleContainer">
  <?php if ($row_currentUser['hideToDoList']==0) include("reports-toDoList.php"); ?>
  <div class="module">
    <h2>Quick Links</h2>
    <p><a class="button" style="margin-bottom:10px" href="attendance-sheet.php">Today's Online Attendance</a></p>
    <p><a class="button" style="margin-bottom:10px" href="attendance-daily.php">Daily Attendance Report</a></p>
    <p><a class="button" style="margin-bottom:10px" href="student-login.php?studioID=<?php echo $studioID; ?>">Student Login Page</a></p>
    <p><a class="button" style="margin-bottom:10px" href="settings.php#studioSettings">Studio Settings</a></p>
    <p><a class="button" style="margin-bottom:10px" href="resources.php">Resources &amp; Widgets</a></p>
  </div>
  <?php include("reports-instructors.php"); ?>
  <?php include("reports-attendance.php"); ?>
  <?php include("chart-classPopularity.php"); ?>
  <?php include("chart-attendance.php"); ?>
  <?php include("chart-instructorPopularity.php"); ?>
</div>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript" src="datePicker/picker.js"></script> 
<script>
/**
 * pick a date
 */
$('#dateAdded').pickadate({
  onOpen: function() {
    scrollIntoView( this.$node )
  },
  format: 'yyyy-mm-dd'
})
$('#startDate').pickadate({
  onOpen: function() {
    scrollIntoView( this.$node )
  },
  format: 'yyyy-mm-dd'
})
$('#endDate').pickadate({
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
function hideToDoList(studioID){
	jQuery.ajax({
	 type: "POST",
	 url: "reports-toDoList-hide.php",
	 data: 'studioID='+studioID,
	 cache: false,
	 success: function(response)
	 {
		$("#toDoList").hide()	
	 }
   });
}
window.onload = function(){
	var ctxInstructorPopularity = document.getElementById("chartInstructorPopularity").getContext("2d");
	window.myPieInstructorPopularity = new Chart(ctxInstructorPopularity).Pie(dataInstructorPopularity);
	var ctxClassPopularity = document.getElementById("chartClassPopularity").getContext("2d");
	window.myPieClassPopularity = new Chart(ctxClassPopularity).Pie(dataClassPopularity);
	var ctxAttendance = document.getElementById("chartAttendance").getContext("2d");
	window.myPie = new Chart(ctxAttendance).Bar(dataAttendance);
};
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);
?>