<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
?>
<?php require_once('Connections/wotg.php'); ?>
<?php
$sortBy = 'name';
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
  $insertSQL = sprintf("INSERT INTO classes (name, classDay, startTime, endTime, instructorID, classActive, classFee, prepaidFee, studio, classCapacity, studioID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['name'], "text"),
                       GetSQLValueString($_POST['classDay'], "text"),
                       GetSQLValueString($_POST['startTime'], "text"),
                       GetSQLValueString($_POST['endTime'], "text"),
                       GetSQLValueString($_POST['instructorID'], "int"),
                       GetSQLValueString($_POST['classActive'], "int"),
                       GetSQLValueString($_POST['classFee'], "int"),
                       GetSQLValueString($_POST['prepaidFee'], "int"),
                       GetSQLValueString($_POST['studio'], "text"),
                       GetSQLValueString($_POST['classCapacity'], "int"),
                       GetSQLValueString($_POST['studioID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());

  $insertGoTo = "classes.php?action=saved";
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

//delete record
if ($_GET['action'] == 'delete') {
    $deleterecords = "DELETE FROM classes WHERE classID = ".$_GET['classID'];
    mysql_select_db($classes, $wotg);
    mysql_query($deleterecords, $wotg) or die(mysql_error());
}

mysql_select_db($database_wotg, $wotg);
$query_classes = "SELECT * FROM classes, instructors WHERE classes.studioID = ".$row_currentUser['studioID']." AND classes.instructorID=instructors.instructorID AND classes.classActive = ".$classActive." ORDER BY ".$sortBy;
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);

mysql_select_db($database_wotg, $wotg);
$query_instructors = "SELECT * FROM instructors WHERE studioID = ".$row_currentUser['studioID']." AND active = 1 ORDER BY lastName ASC";
$instructors = mysql_query($query_instructors, $wotg) or die(mysql_error());
$row_instructors = mysql_fetch_assoc($instructors);
$totalRows_instructors = mysql_num_rows($instructors);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="styles.css"/>
<title>WOTG Administration</title>
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
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Manage Classes</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
  <h2 class="twd_centered twd_margin20" style="padding:20px 0 0 0"><?php echo $classesShown ?> Classes <a id="addClass" href="javascript:void();" class="tooltip" title="add a new class"><img src="images/plus.png" width="22" height="22" /></a></h2>
<?php
//delete record
if ($_GET['action'] == 'delete') print '<p class="twd_centered twd_margin20" style="color:red;">The class has been deleted!</p>';
//check if changes were saved
if ($_GET['action'] == 'saved') print '<p class="twd_centered twd_margin20" style="color:red">The class has been added!</p>'; 
?>
<div id="addClassForm">
<h3 class="twd_centered twd_margin20">Fill out the form below to add a new class!</h3>
<form class="twd_margin20" action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" onsubmit="MM_validateForm('name','','R','classFee','','NisNum','prepaidFee','','NisNum','classCapacity','','NisNum');return document.MM_returnValue">
  <table align="center">
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Class Name:</td>
      <td><input name="name" type="text" id="name" value="" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Day:</td>
      <td><select name="classDay">
        <option value="Monday">Monday</option>
        <option value="Tuesday">Tuesday</option>
        <option value="Wednesday">Wednesday</option>
        <option value="Thursday">Thursday</option>
        <option value="Friday">Friday</option>
        <option value="Saturday">Saturday</option>
        <option value="Sunday">Sunday</option>
      </select></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Starts:</td>
      <td><select name="startTime">
<?php 
foreach (range(0,23) as $fullhour) {
$fullhour2digit = strlen($fullhour)==1 ? '0' . $fullhour : $fullhour;
$parthour = $fullhour > 12 ? $fullhour - 12 : $fullhour;
$parthour .= $fullhour > 11 ? ":00 pm" : ":00 am";
$parthour = $parthour=='0:00 am' ? 'midnight' : $parthour;
$parthour = $parthour=='12:00 pm' ? 'noon' : $parthour;

$parthalf = $fullhour > 12 ? $fullhour - 12 : $fullhour;
$parthalf .= $fullhour > 11 ? ":30 pm" : ":30 am";


//SHOWS THE TEST FOR 'SELECTED' IN THE OPTION TAG
     echo '<option ';
     if (date("H:i:s", strtotime($startdate)) === $fullhour2digit . ':00:00')
        {echo ' SELECTED ';}
     echo 'value="' . $fullhour2digit . ':00:00">' .  $parthour . '</option>';
     echo '<option ';
     if (date("H:i:s", strtotime($startdate)) === $fullhour2digit  . ':30:00')
        {echo ' SELECTED ';}
     echo 'value="' . $fullhour2digit . ':30:00">' .  $parthalf . '</option>';
}
?>
</select></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Ends:</td>
      <td><select name="endTime">
<?php 
foreach (range(0,23) as $fullhour) {
$fullhour2digit = strlen($fullhour)==1 ? '0' . $fullhour : $fullhour;
$parthour = $fullhour > 12 ? $fullhour - 12 : $fullhour;
$parthour .= $fullhour > 11 ? ":00 pm" : ":00 am";
$parthour = $parthour=='0:00 am' ? 'midnight' : $parthour;
$parthour = $parthour=='12:00 pm' ? 'noon' : $parthour;

$parthalf = $fullhour > 12 ? $fullhour - 12 : $fullhour;
$parthalf .= $fullhour > 11 ? ":30 pm" : ":30 am";


//SHOWS THE TEST FOR 'SELECTED' IN THE OPTION TAG
     echo '<option ';
     if (date("H:i:s", strtotime($startdate)) === $fullhour2digit . ':00:00')
        {echo ' SELECTED ';}
     echo 'value="' . $fullhour2digit . ':00:00">' .  $parthour . '</option>';
     echo '<option ';
     if (date("H:i:s", strtotime($startdate)) === $fullhour2digit  . ':30:00')
        {echo ' SELECTED ';}
     echo 'value="' . $fullhour2digit . ':30:00">' .  $parthalf . '</option>';
}
?>
</select></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Instructor:</td>
      <td><select name="instructorID">
        <?php
do {  
?>
        <option value="<?php echo $row_instructors['instructorID']?>"><?php echo $row_instructors['lastName']?>, <?php echo $row_instructors['firstName']?></option>
        <?php
} while ($row_instructors = mysql_fetch_assoc($instructors));
  $rows = mysql_num_rows($instructors);
  if($rows > 0) {
      mysql_data_seek($instructors, 0);
	  $row_instructors = mysql_fetch_assoc($instructors);
  }
?>
      </select>
      </td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Dropin Fee:</td>
      <td><input name="classFee" type="text" id="classFee" value="20" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Prepaid Fee:</td>
      <td><input name="prepaidFee" type="text" id="prepaidFee" value="15" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Class Capacity:</td>
      <td><input name="classCapacity" type="text" id="classCapacity" value="15" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">Studio:</td>
      <td><input name="studio" type="text" id="studio" value="Studio 1" size="32" /></td>
    </tr>
    <tr valign="baseline">
      <td nowrap="nowrap" align="right">&nbsp;</td>
      <td><input type="submit" value="Add Class" /></td>
    </tr>
  </table>
  <input type="hidden" name="classActive" value="1" />
  <input type="hidden" name="studioID" value="<?php echo $row_currentUser['studioID']; ?>" />
  <input type="hidden" name="MM_insert" value="form1" />
</form>
  </div>
<p class="twd_centered twd_margin20">Click on any class name to update or delete.</p>
  <table border="0" align="center" cellpadding="5" cellspacing="0">
    <tr>
      <td><strong><a href="?sortBy=name">name</a></strong></td>
      <td><strong><a href="?sortBy=firstName">instructor</a></strong></td>
      <td><strong><a href="?sortBy=classDay">day</a></strong></td>
      <td><strong>time</strong></td>
      <td><strong>dropin fee</strong></td>
      <td><strong>capacity</strong></td>
    </tr>
    <?php 
	do { 
		$formattedTime = date("g:i a", strtotime($row_classes['startTime']));
	?>
    <tr>
        <td><a href="classes-update.php?classID=<?php echo $row_classes['classID']; ?>"><?php echo $row_classes['name']; ?></a></td>
        <td><?php echo $row_classes['firstName']; ?> <?php echo $row_classes['lastName']; ?></td>
        <td><?php echo $row_classes['classDay']; ?></td>
        <td><?php echo $formattedTime; ?></td>
        <td>$<?php echo $row_classes['classFee']; ?></td>
        <td><?php echo $row_classes['classCapacity']; ?></td>
    </tr>
      <?php } while ($row_classes = mysql_fetch_assoc($classes)); ?>
  </table>
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

mysql_free_result($classes);

mysql_free_result($instructors);
?>