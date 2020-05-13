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

mysql_select_db($database_wotg, $wotg);
$query_students = "SELECT * FROM students WHERE studioID = ".$row_currentUser['studioID']." ORDER BY lastName, firstName ASC";
$students = mysql_query($query_students, $wotg) or die(mysql_error());
$row_students = mysql_fetch_assoc($students);
$totalRows_students = mysql_num_rows($students);

$sortBy = 'members.studentID, startDate DESC';
if ($_GET['sortBy']!='') $sortBy = $_GET['sortBy'];
if ($sortBy == 'startDate') $sortBy = 'startDate DESC';
if ($sortBy == 'endDate') $sortBy = 'endDate DESC';

$startDate = $_GET['memberStartDate'];
$endDate = $_GET['memberEndDate'];

//delete record
if ($_GET['action'] == 'delete') {
    $deleterecords = "DELETE FROM members WHERE membershipID = ".$_GET['membershipID'];
    mysql_select_db($members, $wotg);
    mysql_query($deleterecords, $wotg) or die(mysql_error());
}

mysql_select_db($database_wotg, $wotg);
$query_members = "SELECT * FROM members INNER JOIN students ON members.studentID = students.studentID WHERE members.startDate >= '".$startDate."' AND members.startDate <= '".$endDate."' AND members.studioID = ".$row_currentUser['studioID']." ORDER BY ".$sortBy;
$members = mysql_query($query_members, $wotg) or die(mysql_error());
$row_members = mysql_fetch_assoc($members);
$totalRows_members = mysql_num_rows($members);
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
<title><?php echo $row_currentUser['studioName']; ?> | Member Report</title>
</head>
<body>
<?php include("header.php"); ?>

<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Member Report</h1>

<?php include("navigation.php"); ?>
<div class="twd_container">
  
<h2 class="twd_centered">Member Report</h2>
<h3 class="twd_centered twd_margin20"><?php echo date("m/d/Y", strtotime($startDate)).' - '.date("m/d/Y", strtotime($endDate)); ?></h3>
  <?php
//delete record
if ($_GET['action'] == 'delete') {
    print '<p class="twd_centered twd_margin20" style="color:red;">Member has been deleted!</p>';
}
?>

<?php if ($totalRows_members==0) { echo '<p class="twd_centered twd_margin20">No members were found! Add a new member using the plus sign + above.</p>'; } else { ?>
  <p class="twd_centered twd_margin20">Click on any member's name to update or delete. <span class="greenBG">&nbsp;green indicates an active member&nbsp;</span></p>
  <table border="0" align="center" cellpadding="5" cellspacing="0" class="twd_margin20">
    <thead><tr>
      <th><strong>Name</strong></th>
      <th><strong>Email</strong></th>
      <th><strong>Start Date</strong></th>
      <th><strong>End Date</strong></th>
      <th>Fee</th>
      </tr>
    </thead>
    <?php 
	$today = $expiry_date = date("Y-m-d", strtotime("now"));
	$total = 0;
	$numberOfMembers = 0;
	if ($totalRows_members > 0) $numberOfMembers = 1;
	$studentTotal = 0;
	$lastStudentID = $row_members['studentID'];
	do { 
	if($row_members['studentID'] != $lastStudentID){
	$numberOfMembers++;
		echo '<tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>Total: $'.number_format($studentTotal, 2).'</td>
      </tr>';
		$studentTotal = 0;
		$lastStudentID = $row_members['studentID'];
	}
	$studentTotal = $studentTotal+$row_members['membershipFee'];
	$total = $total+$row_members['membershipFee'];	
	if ($row_members['startDate'] <= $today && $row_members['endDate'] >= $today){
	?>
    <tr class="greenBG">
      <?php
	} else {
	?>
    <tr>
    <?php } ?>
      <td><a href="members-update.php?membershipID=<?php echo $row_members['membershipID']; ?>"><?php echo $row_members['lastName']; ?>, <?php echo $row_members['firstName']; ?></a></td>
      <td><?php echo $row_members['emailAddress']; ?></td>
      <td><?php echo $row_members['startDate']; ?></td>
      <td><?php echo $row_members['endDate']; ?></td>
      <td>$<?php echo number_format($row_members['membershipFee'], 2); ?></td>
      </tr>
      <?php } while ($row_members = mysql_fetch_assoc($members)); ?>
  </table>
  <?php } ?>
  <h2 class="twd_centered">Summary</h2>
  <table width="0" border="0" cellspacing="0" cellpadding="10" align="center">
  <tr>
    <td>Date Range:</td>
    <td><?php echo date("m/d/Y", strtotime($startDate)).' - '.date("m/d/Y", strtotime($endDate)); ?></td>
  </tr>
  <tr>
    <td>Number of Unique Members:</td>
    <td><?php echo $numberOfMembers; ?></td>
  </tr>
  <tr>
    <td>Memberships Purchased:</td>
    <td><?php echo $totalRows_members; ?></td>
  </tr>
  <tr>
    <td>Total Membership Fees:</td>
    <td>$<?php echo number_format($total, 2); ?></td>
  </tr>
</table>
</div>
<?php include("footer.php"); ?>

</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($students);

mysql_free_result($members);
?>