<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
?>
<?php require_once('Connections/wotg.php'); ?>
<?php
$sortBy = 'lastName';
if ($_GET['sortBy'] != '') $sortBy = $_GET['sortBy'];
$classActive = 1;
if ($_GET['active'] != '') $classActive = $_GET['active'];
$classesShown = "Active";
if ($classActive == 0) $classesShown = "Inactive";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	//check for duplicate username
	$conn = new mysqli($hostname_wotg, $username_wotg, $password_wotg, $database_wotg);
    if($mysqli->connect_errno){
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
	$result = $conn->query("SELECT count(*) FROM instructors WHERE username = '".$_POST['username']."'");
	$row = $result->fetch_row();
	$num = $row[0];
	if($num>0){
		echo "<script>alert('Sorry - that username already exists! Please try with another one or use an email address.');</script>";
	} else {
	
  $insertSQL = sprintf("INSERT INTO instructors (firstName, lastName, emailAddress, phoneNumber, username, password, active, securityLevel, studioID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['firstName'], "text"),
                       GetSQLValueString($_POST['lastName'], "text"),
                       GetSQLValueString($_POST['emailAddress'], "text"),
                       GetSQLValueString($_POST['phoneNumber'], "text"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['active'], "int"),
                       GetSQLValueString($_POST['securityLevel'], "text"),
                       GetSQLValueString($_POST['studioID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());

  $insertGoTo = "instructors.php?active=1&action=saved";
  //if (isset($_SERVER['QUERY_STRING'])) {
  //  $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
  //  $insertGoTo .= $_SERVER['QUERY_STRING'];
  //}
  header(sprintf("Location: %s", $insertGoTo));
	}//end username check if
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

//delete record
if ($_GET['action'] == 'delete') {
    $deleterecords = "DELETE FROM instructors WHERE instructorID = ".$_GET['instructorID'];
    mysql_select_db($database_wotg, $wotg);
    mysql_query($deleterecords, $wotg) or die(mysql_error());
}

mysql_select_db($database_wotg, $wotg);
$query_instructors = "SELECT * FROM instructors WHERE studioID = ".$row_currentUser['studioID']." AND active = ".$classActive." ORDER BY ".$sortBy;
$instructors = mysql_query($query_instructors, $wotg) or die(mysql_error());
$row_instructors = mysql_fetch_assoc($instructors);
$totalRows_instructors = mysql_num_rows($instructors);
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
<title><?php echo $row_currentUser['studioName']; ?> | Instructors</title>
<script type="text/javascript">
function MM_validateForm() { //v4.0
  if (document.getElementById){
    var i,p,q,nm,test,num,min,max,errors='',args=MM_validateForm.arguments;
    for (i=0; i<(args.length-2); i+=3) { test=args[i+2]; val=document.getElementById(args[i]);
      if (val) { nm=val.name; if ((val=val.value)!="") {
        if (test.indexOf('isEmail')!=-1) { p=val.indexOf('@');
          if (p<1 || p==(val.length-1)) errors+='- '+nm+' must contain an e-mail address.\n';
        } else if (test!='R') { num = parseFloat(val);
          if (isNaN(val)) errors+='- '+nm+' must contain a number.\n';
          if (test.indexOf('inRange') != -1) { p=test.indexOf(':');
            min=test.substring(8,p); max=test.substring(p+1);
            if (num<min || max<num) errors+='- '+nm+' must contain a number between '+min+' and '+max+'.\n';
      } } } else if (test.charAt(0) == 'R') errors += '- '+nm+' is required.\n'; }
    } if (errors) alert('The following error(s) occurred:\n'+errors);
    document.MM_returnValue = (errors == '');
} }
</script>
</head>
<body>

<?php include("header.php"); ?>

<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Instructors</h1>

<?php include("navigation.php"); ?>
<div class="twd_container">

  <h2 class="twd_centered twd_margin20" style="padding:20px 0 0 0"><?php echo $classesShown ?> Instructors &amp; Staff Members&nbsp;&nbsp;<a id="addClass" href="javascript:void();" class="tooltip" title="add a new instructor"><img src="images/plus.png" width="22" height="22" /></a></h2>
<?php
//delete record
if ($_GET['action'] == 'delete') print '<p class="twd_centered twd_margin20" style="color:red;">The instructor has been deleted!</p>';
//check if changes were saved
if ($_GET['action'] == 'saved') print '<p class="twd_centered twd_margin20" style="color:red">The instructor has been added!</p>'; 
?>
<div id="addClassForm">
<p class="twd_centered twd_margin20">Fill out the form below to add a new instructor!</p>
  <form class="twd_margin20" action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" onsubmit="MM_validateForm('firstName','','R','lastName','','R','emailAddress','','NisEmail','username','','R','password','','R');return document.MM_returnValue">
    <table border="0" cellpadding="5" cellspacing="0" align="center">
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">First Name *</td>
        <td><input name="firstName" type="text" id="firstName" value="" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Last Name *</td>
        <td><input name="lastName" type="text" id="lastName" value="" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Email</td>
        <td><input name="emailAddress" type="text" id="emailAddress" value="" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Phone</td>
        <td><input type="text" name="phoneNumber" value="" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Username *</td>
        <td><input name="username" type="text" id="username" value="" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Password *</td>
        <td><input name="password" type="text" id="password" value="" size="32" /></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right"><a href="securityLevels.php">Security Level</a></td>
        <td><select name="securityLevel">
          <option value="instructor" <?php if (!(strcmp("instructor", ""))) {echo "SELECTED";} ?>>instructor</option>
          <?php if ($row_currentUser['securityLevel'] == 'super') { ?>
          <option value="administrator" <?php if (!(strcmp("administrator", ""))) {echo "SELECTED";} ?>>administrator</option>
          <option value="super" <?php if (!(strcmp("super", ""))) {echo "SELECTED";} ?>>super user</option>
          <?php } ?>
        </select></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><input type="submit" value="Add Instructor" /></td>
      </tr>
    </table>
    <input type="hidden" name="active" value="1" />
    <input type="hidden" name="studioID" value="<?php echo $row_currentUser['studioID']; ?>" />
    <input type="hidden" name="MM_insert" value="form1" />
  </form>
  </div>
  <?php
  //check if changes were saved
  if ($_GET['action'] == 'deleted') print '<p class="twd_centered twd_margin20" style="color:red">Instructor has been deleted.</p>'; 
  ?>
  <?php if ($totalRows_instructors==0) { echo '<p class="twd_centered twd_margin20">No instructors were found! Add a new instructor using the plus sign + above.</p>'; } else { ?>
  <p class="twd_centered twd_margin20">Click on any instructor name to update or delete.</p>
  <table class="twd_margin20" border="0" cellpadding="5" cellspacing="0" align="center">
    <thead><tr>
      <th>&nbsp;</th>
      <th><strong>name</strong></th>
      <th><strong>email</strong></th>
      <th><strong>username</strong></th>
      <th><strong>security</strong></th>
    </tr></thead>
    <?php do { ?>
      <tr>
        <td><?php if($row_instructors['thumbnail']!=''){ ?>
        <img height="100" width="100" src="uploads/<?php echo $row_instructors['thumbnail']; ?>" />
        <?php } else { ?>
        <img height="100" width="100" src="uploads/unavailable.gif" />
        <?php } ?></td>
        <td><a href="instructors-update.php?instructorID=<?php echo $row_instructors['instructorID']; ?>"><?php echo $row_instructors['lastName']; ?>, <?php echo $row_instructors['firstName']; ?></a></td>
        <td><?php echo $row_instructors['emailAddress']; ?></td>
        <td><?php echo $row_instructors['username']; ?></td>
        <td><?php echo $row_instructors['securityLevel']; ?></td>
      </tr>
      <?php } while ($row_instructors = mysql_fetch_assoc($instructors)); ?>
  </table>
  <?php } ?>
</div>

<?php include("footer.php"); ?>
<script>
$("#addClass").click(function() {
		if ($("#addClassForm").is(':visible')) {
			$("#addClassForm").slideUp("slow");
			$('#addClass img').attr('src', 'images/plus.png');
		}
		else {
			$("#addClassForm").slideDown("slow");
			$('#addClass img').attr('src', 'images/minus.png');
		}
});
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);
mysql_free_result($instructors);
?>