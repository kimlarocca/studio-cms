<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
?>
<?php require_once('Connections/wotg.php'); ?>
<?php
$sortBy = 'lastName';
if ($_GET['sortBy'] != '') $sortBy = $_GET['sortBy'];
if($sortBy == 'dateAdded') $sortBy = 'dateAdded DESC';
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
$query_students = "SELECT * FROM students WHERE (lastName LIKE '%".$_GET['lastName']."%' OR firstName LIKE '%".$_GET['lastName']."%' OR emailAddress LIKE '%".$_GET['lastName']."%') AND studioID = ".$row_currentUser['studioID']." ORDER BY ".$sortBy;
$students = mysql_query($query_students, $wotg) or die(mysql_error());
$row_students = mysql_fetch_assoc($students);
$totalRows_students = mysql_num_rows($students);
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
<title><?php echo $row_currentUser['studioName']; ?> | Students</title>
</head>
<body>
<?php include("header.php"); ?>

<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Students</h1>

<?php include("navigation.php"); ?>
<div class="twd_container">
  <h2 class="twd_centered">Complete Student List&nbsp;&nbsp;<a href="javescript:void();" class="tooltip" title="add a new student" onclick="window.open('students-add.php','Add Student','height=500,width=300');return false;"> <img src="images/addPerson.png" width="22" height="22" alt="add a new student" /></a>
  <!--&nbsp;&nbsp;<a id="addClass" href="javascript:void();" class="tooltip" title="search students"><img src="images/search.png" width="22" height="22" /></a>--></h2>
  <?php
//check if changes were saved
if ($_GET['action'] == 'deleted') print '<p class="twd_centered twd_margin20" style="color:red">Student has been deleted.</p>'; 
?>

<div id="addClassForm2">
<p class="twd_centered twd_margin20">Use the form below to search for a student! <a href="students.php">Click here to reset the search.</a></p>
<form action="students.php" method="get" name="searchForm" class="twd_margin20">
<input class="twd_centered" name="lastName" type="text" placeholder="name or email address" /><br />
<input class="twd_centered" type="submit" value="search" />
</form>
</div>
<?php if ($totalRows_students==0) { echo '<p class="twd_centered twd_margin20">No students were found! Add a new student by click this icon: <a href="javescript:void();" class="tooltip" title="add a new student" onclick="window.open(\'students-add.php\',\'Add Student\',\'height=500,width=300\');return false;"> <img src="images/addPerson.png" width="22" height="22" alt="add a new student" /></a></p>'; } else { ?>
<p class="twd_centered twd_margin20">Click on any student name to update or delete.</p>

  <table border="0" cellpadding="5" cellspacing="0" align="center">
  <thead>
    <tr>
      <th><strong><a href="?sortBy=lastName">name</a></strong></th>
      <th><strong><a href="?sortBy=emailAddress">email</a></strong></th>
      <th><a href="?sortBy=dateAdded"><strong>date added</strong></a></th>
      <th><a href="?sortBy=classesLeft DESC"><strong>classes left</strong></a></th>
      <th>&nbsp;</th>
    </tr>
    </thead>
    <?php do { ?>
      <tr>
        <td><a href="students-update.php?studentID=<?php echo $row_students['studentID']; ?>"><?php echo $row_students['lastName']; ?>, <?php echo $row_students['firstName']; ?></a></td>
        <td><?php echo $row_students['emailAddress']; ?></td>
        <td><?php echo $row_students['dateAdded']; ?></td>
        <td><?php echo $row_students['classesLeft']; ?></td>
        <td><a href="waiver.php?studentID=<?php echo $row_students['studentID']; ?>">waiver</a></td>
      </tr>
      <?php } while ($row_students = mysql_fetch_assoc($students)); ?>
  </table>
  <?php } ?>
</div>
<?php include("footer.php"); ?>
<script>
$("#addClass").click(function() {
		if ($("#addClassForm2").is(':visible')) {
			$("#addClassForm2").slideUp("slow");
			$('#addClass img').attr('src', 'images/search.png');
		}
		else {
			$("#addClassForm2").slideDown("slow");
			$('#addClass img').attr('src', 'images/minus.png');
		}
});
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($students);
?>