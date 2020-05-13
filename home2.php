<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
if (!isset($_SESSION)) {
  session_start();
}
?>
<?php require_once('Connections/wotg.php'); ?>
<?php
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

$MM_restrictGoTo = "login-failed.php";
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

mysql_select_db($database_wotg, $wotg);
$query_students = "SELECT * FROM students ORDER BY lastName ASC";
$students = mysql_query($query_students, $wotg) or die(mysql_error());
$row_students = mysql_fetch_assoc($students);
$totalRows_students = mysql_num_rows($students);
?>
<?php
//if ($row_currentUser['securityLevel'] == 'administrator') echo "<a class='button'style='margin:0 10px 20px 0' href='admin.php'>admin home page</a>";
if ($row_currentUser['securityLevel'] == 'administrator') {
	flush();
header("Location: admin.php");
die('redirect error');
}
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
<script src="Chart.min.js"></script>
<title><?php echo $row_currentUser['studioName']; ?> | <?php echo $row_currentUser['firstName']; ?>'s Home Page</title>
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | <?php echo $row_currentUser['firstName']; ?>'s Home Page</h1>
<div class="twd_container">
<div class="twd_centered">
      <a class="button" style="margin:0 10px 20px 0" href="attendance-sheet.php">Today's Attendance Sheet</a>
      <a class="button" style="margin:0 10px 20px 0" href="student-login.php?studioID=<?php echo $row_currentUser['studioID']; ?>">Student Login Page</a>
</div>
  <div class="twd_row">
    <div class="twd_column twd_two twd_margin20">
   
      <h2 class="subheader">Attendance Reports</h2>
      <p>Use the form below to generate your attendance reports:</p>
      <form method="get" action="instructor-report2.php" name="form0">
Pay Period Start Date:      
  <input type="text" id="startDate" name="startDate">
Pay Period End Date:      
<input type="text" id="endDate" name="endDate">
<input name="submit2" type="submit" value="submit" />
      <input name="instructorID" type="hidden" id="instructorID" value="<?php echo $row_currentUser['instructorID']; ?>" />
    </form>
    </div>
    
    <div class="twd_column twd_two twd_margin20">
    <?php if ($row_currentUser['messageInstructors']!=''){ ?>
    <h2 class="subheader">Message To Instructors</h2> 
     <p class="twd_margin30"><?php echo $row_currentUser['messageInstructors']; ?></p>
     <?php } ?>
      <h2 class="subheader">Student Waivers</h2>
      <p>Use the form below to manage digital waivers:</p>
    <form action="waiver.php" method="get">
      Student Name: <a class="tooltip" title="add a new client" href="javescript:void();" onclick="window.open('students-add.php','Add Student','height=500,width=300');return false;"><img src="images/addPerson.png" width="22" height="22" alt="add a new student" /></a><br /><br />
      <select name="studentID">
        <option value="" selected="selected">Select a Student:</option>
        <?php	
do {  
$hasWaiver = '';
if($row_students['waiver']==1) $hasWaiver = ' (waiver already on file)';
?>
        <option value="<?php echo $row_students['studentID']?>"><?php echo $row_students['lastName']?>, <?php echo $row_students['firstName'].$hasWaiver?></option>
        <?php
} while ($row_students = mysql_fetch_assoc($students));
  $rows = mysql_num_rows($students);
  if($rows > 0) {
      mysql_data_seek($students, 0);
	  $row_students = mysql_fetch_assoc($students);
  }
?>
      </select><input value="view waiver" type="submit" />
    </form></div>
 
  </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script> 
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
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($students);
?>