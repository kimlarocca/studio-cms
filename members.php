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

$sortBy = 'startDate DESC';
if ($_GET['sortBy']!='') $sortBy = $_GET['sortBy'];
if ($sortBy == 'startDate') $sortBy = 'startDate DESC';
if ($sortBy == 'endDate') $sortBy = 'endDate DESC';

//delete record
if ($_GET['action'] == 'delete') {
    $deleterecords = "DELETE FROM members WHERE membershipID = ".$_GET['membershipID'];
    mysql_select_db($members, $wotg);
    mysql_query($deleterecords, $wotg) or die(mysql_error());
}

mysql_select_db($database_wotg, $wotg);
$query_members = "SELECT * FROM members INNER JOIN students ON members.studentID = students.studentID WHERE members.studioID = ".$row_currentUser['studioID']." ORDER BY ".$sortBy;
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
<title><?php echo $row_currentUser['studioName']; ?> | Members</title>
</head>
<body>
<?php include("header.php"); ?>

<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Members</h1>

<?php include("navigation.php"); ?>
<div class="twd_container">
  
<h2 class="twd_centered">Manage Members&nbsp;&nbsp;<span class="twd_centered twd_margin20" style="padding:20px 0 0 0"><a href="javascript:void();" onclick="window.open('members-add.php','Add Member','height=500,width=350');return false;"><img src="images/addPerson.png" width="20" height="20" alt="add a new member" /></a> &nbsp;&nbsp;<a class="tooltip" title="generate member reports" href="reports.php#memberReport"><img src="images/reports.png" width="22" height="22" /></a></span></h2>
  <?php
//delete record
if ($_GET['action'] == 'delete') {
    print '<p class="twd_centered twd_margin20" style="color:red;">Member has been deleted!</p>';
}
?>
    <div class="twd_clearfloat"></div>
<?php if ($totalRows_members==0) { echo '<p class="twd_centered twd_margin20">No members were found! Add a new member using the plus sign + above.</p>'; } else { ?>
  <p class="twd_centered twd_margin20">Click on any member's name to update or delete. <span class="greenBG">&nbsp;green indicates an active member&nbsp;</span></p>
  <table border="0" align="center" cellpadding="5" cellspacing="0">
    <thead><tr>
      <th><strong><a href="?sortBy=lastName">name</a></strong></th>
      <th><strong>email</strong></th>
      <th><strong><a href="?sortBy=startDate">start date</a></strong></th>
      <th><a href="?sortBy=endDate"><strong>end date</strong></a></th>
      </tr>
    </thead>
    <?php 
	$today = $expiry_date = date("Y-m-d", strtotime("now"));
	do { 
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
      </tr>
      <?php } while ($row_members = mysql_fetch_assoc($members)); ?>
  </table>
  <?php } ?>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript" src="datePicker/picker.js"></script> 
<script>
/**
 * pick a date
 */
$('#memberStartDate').pickadate({
  onOpen: function() {
    scrollIntoView( this.$node )
  },
  format: 'yyyy-mm-dd'
})
$('#memberEndDate').pickadate({
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

mysql_free_result($members);
?>