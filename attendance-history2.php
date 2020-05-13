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
$sortBy = 'attendance.dateAdded DESC';
if ($_GET['sortBy'] != '') $sortBy = $_GET['sortBy'];
if ($sortBy == 'attendanceDate') $sortBy = 'attendanceDate DESC';
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

$maxRows_attendance = 100;
$pageNum_attendance = 0;
if (isset($_GET['pageNum_attendance'])) {
  $pageNum_attendance = $_GET['pageNum_attendance'];
}
$startRow_attendance = $pageNum_attendance * $maxRows_attendance;

mysql_select_db($database_wotg, $wotg);
//$query_attendance = "SELECT * FROM attendance ORDER BY dateAdded DESC";
$query_attendance = "SELECT attendance.dateAdded AS attendanceDate,attendance.attendanceID, attendance.classID,attendance.studentID,attendance.paymentType,attendance.attendanceType, students.studentID, students.firstName,students.lastName,students.emailAddress,students.waiver,classes.classID,classes.name,instructors.instructorID,instructors.firstName AS iFirstName,instructors.lastName AS iLastName FROM attendance,students,classes,instructors WHERE (students.lastName LIKE '%".$_GET['lastName']."%' OR students.firstName LIKE '%".$_GET['lastName']."%' OR students.emailAddress LIKE '%".$_GET['lastName']."%') AND attendance.studioID = ".$row_currentUser['studioID']." AND attendance.studentID = students.studentID AND attendance.classID = classes.classID AND attendance.instructorID=instructors.instructorID ORDER BY ".$sortBy;
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
<title><?php echo $row_currentUser['studioName']; ?> | Attendance</title>
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Attendance</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
  <h2 class="twd_centered" style="padding-bottom:20px">Complete Attendance History</h2>
  <p class="twd_centered twd_margin20">Use the form below to search for attendance records! <a href="attendance-history.php">Click here to reset the search.</a></p>
<form action="attendance-history2.php" method="get" name="searchForm" class="twd_margin20">
<input class="twd_centered" name="lastName" type="text" placeholder="client name or email address" /><br />
<input class="twd_centered" type="submit" value="search" />
</form>
  <?php if ($totalRows_attendance==0) { echo '<p class="twd_centered twd_margin20">No records found.</p>'; } else { ?>
  <table border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
      <th><strong><a href="?sortBy=lastName">student</a></strong></th>
      <th><strong><a href="?sortBy=name">class</a></strong></th>
      <th><strong><a href="?sortBy=iFirstName">instructor</a></strong></th>
      <th><a href="?sortBy=attendanceDate"><strong>date added</strong></a></th>
      <th><strong>attendance type</strong></th>
      <th><strong>payment type</strong></th>
      <th>&nbsp;</th>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo $row_attendance['lastName']; ?>, <?php echo $row_attendance['firstName']; ?></td>
        <td><?php echo $row_attendance['name']; ?></td>
        <td><?php echo $row_attendance['iFirstName']; ?> <?php echo $row_attendance['iLastName']; ?></td>
        <td><?php echo $row_attendance['attendanceDate']; ?></td>
        <td><?php echo $row_attendance['attendanceType']; ?></td>
        <td><?php echo $row_attendance['paymentType']; ?></td>
        <td><a class="tooltip" title="update this record" href="attendance-fullUpdate.php?attendanceID=<?php echo $row_attendance['attendanceID']; ?>"><img src="images/edit.png" alt="" width="20" height="20"/></a> <a class="tooltip" title="delete this record" href="javescript:void();" onclick="window.open('attendance-delete.php?attendanceID=<?php echo $row_attendance['attendanceID']; ?>','Delete Attendance','height=500,width=300');return false;"><img src="images/delete.png" width="20" height="20" /></a></td>
      </tr>
      <?php } while ($row_attendance = mysql_fetch_assoc($attendance)); ?>
  </table>
  <br /><br />
  <table border="0" align="center">
    <tr>
      <td><?php if ($pageNum_attendance > 0) { // Show if not first page ?>
          <a href="<?php printf("%s?pageNum_attendance=%d%s", $currentPage, 0, $queryString_attendance); ?>">First</a>
          <?php } // Show if not first page ?></td>
      <td><?php if ($pageNum_attendance > 0) { // Show if not first page ?>
          <a href="<?php printf("%s?pageNum_attendance=%d%s", $currentPage, max(0, $pageNum_attendance - 1), $queryString_attendance); ?>">Previous</a>
          <?php } // Show if not first page ?></td>
      <td><?php if ($pageNum_attendance < $totalPages_attendance) { // Show if not last page ?>
          <a href="<?php printf("%s?pageNum_attendance=%d%s", $currentPage, min($totalPages_attendance, $pageNum_attendance + 1), $queryString_attendance); ?>">Next</a>
          <?php } // Show if not last page ?></td>
      <td><?php if ($pageNum_attendance < $totalPages_attendance) { // Show if not last page ?>
          <a href="<?php printf("%s?pageNum_attendance=%d%s", $currentPage, $totalPages_attendance, $queryString_attendance); ?>">Last</a>
          <?php } // Show if not last page ?></td>
    </tr>
  </table>
  <br />
  <p class="twd_centered">Total Rows: <?php echo $totalRows_attendance ?></p>
  <?php } ?>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($attendance);
?>