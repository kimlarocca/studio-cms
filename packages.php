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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO packages (studioID, packageName, packageCost, numberOfSessions, daysValidFor) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['studioID'], "int"),
                       GetSQLValueString($_POST['packageName'], "text"),
                       GetSQLValueString($_POST['packageCost'], "int"),
                       GetSQLValueString($_POST['numberOfSessions'], "int"),
                       GetSQLValueString($_POST['daysValidFor'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());

  $insertGoTo = "packages.php?action=added";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
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
$query_packages = "SELECT * FROM packages WHERE studioID = ".$row_currentUser['studioID'];
$packages = mysql_query($query_packages, $wotg) or die(mysql_error());
$row_packages = mysql_fetch_assoc($packages);
$totalRows_packages = mysql_num_rows($packages);
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
<title><?php echo $row_currentUser['studioName']; ?> | Packages</title>
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

<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Packages</h1>

<?php include("navigation.php"); ?>
<div class="twd_container">
 
      <h2 class="twd_centered">Manage Your Packages&nbsp;&nbsp;<a id="addClass" href="javascript:void();" class="tooltip" title="add a new package"><img src="images/plus.png" width="22" height="22" /></a></h2>
<?php
//check url parameters
if ($_GET['action'] == 'added') print '<p class="twd_centered twd_margin20" style="color:red;">Your package has been added!</p>';
if ($_GET['action'] == 'deleted') print '<p class="twd_centered twd_margin20" style="color:red;">Your package has been deleted!</p>';
?>
<div id="addClassForm" class="twd_margin20">
<h3 class="twd_centered twd_margin20">Fill out the form below to add a new package!</h3>
    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" onsubmit="MM_validateForm('packageName','','R','packageCost','','RisNum','numberOfSessions','','RisNum','daysValidFor','','NisNum');return document.MM_returnValue">
      <table border="0" cellpadding="3" cellspacing="0" align="center" class="twd_margin20">
        <tr valign="baseline">
          <td nowrap="nowrap" align="right">Package Name:</td>
          <td><input name="packageName" type="text" id="packageName" value="" size="32" /></td>
        </tr>
        <tr valign="baseline">
          <td nowrap="nowrap" align="right">Package Cost:</td>
          <td>
            <input name="packageCost" type="text" id="packageCost" value="" size="32" /></td>
        </tr>
        <tr valign="baseline">
          <td nowrap="nowrap" align="right">Number Of Sessions:</td>
          <td><input name="numberOfSessions" type="text" id="numberOfSessions" value="" size="32" /></td>
        </tr>
        <tr valign="baseline">
          <td nowrap="nowrap" align="right">Days Package Is Valid For: *</td>
          <td><input name="daysValidFor" type="text" id="daysValidFor" value="" size="32" /></td>
        </tr>
        <tr valign="baseline">
          <td nowrap="nowrap" align="right">&nbsp;</td>
          <td><input type="submit" value="Add Package" /></td>
        </tr>
      </table>
      <input type="hidden" name="studioID" value="<?php echo $row_currentUser['studioID']; ?>" />
      <input type="hidden" name="MM_insert" value="form1" />
      <P class="twd_centered"><em>* leave blank if package never expires</em></p>
    </form>
  </div>
  <?php if ($totalRows_packages==0) echo '<p class="twd_centered twd_margin20">You do not currently have any packages! Please add one by clicking the plus sign + above.</p>'; else { ?>
  <table border="0" cellpadding="3" cellspacing="0" align="center">
    <tr>
      <th><strong>package name</strong></th>
      <th><strong>cost</strong></th>
      <th><strong># of sessions</strong></th>
      <th><strong>days valid for</strong></th>
      <th>&nbsp;</th>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo $row_packages['packageName']; ?></td>
        <td>$<?php echo $row_packages['packageCost']; ?></td>
        <td><?php echo $row_packages['numberOfSessions']; ?></td>
        <td><?php echo $row_packages['daysValidFor']; ?></td>
        <td><a class="tooltip" title="edit this package" href="packages-update.php?packageID=<?php echo $row_packages['packageID']; ?>"><img src="images/edit.png" width="22" height="22" /></a> <a class="tooltip" title="delete this package" href="packages-delete.php?packageID=<?php echo $row_packages['packageID']; ?>"><img src="images/delete.png" width="22" height="22" /></a></td>
      </tr>
      <?php } while ($row_packages = mysql_fetch_assoc($packages)); ?>
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

mysql_free_result($packages);
?>