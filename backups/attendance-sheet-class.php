<?php
ini_set('session.save_path',getcwd(). '/../tmp/');
session_start();
?>
<?php require_once('Connections/wotg.php'); ?>
<?php
$today = date("Y-m-d", strtotime("now"));
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
  $insertSQL = sprintf("INSERT INTO attendance (studentID, classID, instructorID, dateAdded, attendanceType, paymentType) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['studentID'], "int"),
                       GetSQLValueString($_POST['classID'], "int"),
                       GetSQLValueString($_POST['instructorID'], "int"),
                       GetSQLValueString($_POST['dateAdded'], "date"),
                       GetSQLValueString($_POST['attendanceType'], "text"),
                       GetSQLValueString($_POST['paymentType'], "text"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());

  $insertGoTo = "attendance-sheet-class.php?classID=".$_GET['classID'];
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

$colname_classes = "-1";
if (isset($_GET['classID'])) {
  $colname_classes = $_GET['classID'];
}
mysql_select_db($database_wotg, $wotg);
$query_classes = sprintf("SELECT * FROM classes INNER JOIN instructors ON classes.instructorID = instructors.instructorID WHERE classID = %s", GetSQLValueString($colname_classes, "int"));
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);

mysql_select_db($database_wotg, $wotg);
$query_allStudents = "SELECT studentID, firstName, lastName FROM students ORDER BY lastName ASC";
$allStudents = mysql_query($query_allStudents, $wotg) or die(mysql_error());
$row_allStudents = mysql_fetch_assoc($allStudents);
$totalRows_allStudents = mysql_num_rows($allStudents);

mysql_select_db($database_wotg, $wotg);
$query_currentMembers = "SELECT * FROM members,students WHERE members.studentID = students.studentID AND '".$today."' between members.startDate AND members.endDate";
$currentMembers = mysql_query($query_currentMembers, $wotg) or die(mysql_error());
$row_currentMembers = mysql_fetch_assoc($currentMembers);
$totalRows_currentMembers = mysql_num_rows($currentMembers);

mysql_select_db($database_wotg, $wotg);
$query_instructors = "SELECT * FROM instructors WHERE active = TRUE ORDER BY lastName ASC";
$instructors = mysql_query($query_instructors, $wotg) or die(mysql_error());
$row_instructors = mysql_fetch_assoc($instructors);
$totalRows_instructors = mysql_num_rows($instructors);

mysql_select_db($database_wotg, $wotg);
$query_attendance = "SELECT attendance.dateAdded AS attendanceDate,attendance.classID,attendance.studentID,attendance.paymentType,attendance.attendanceType, attendance.dropInName,attendance.dropInEmail,students.studentID,students.firstName,students.lastName,students.emailAddress FROM attendance,students WHERE attendance.studentID=students.studentID AND attendance.classID = ".$_GET['classID']." AND attendance.dateAdded = '".$today."'";
$attendance = mysql_query($query_attendance, $wotg) or die(mysql_error());
$row_attendance = mysql_fetch_assoc($attendance);
$totalRows_attendance = mysql_num_rows($attendance);
?>
<?php
$instructorID=$row_classes['instructorID'];
if ($_GET['instructorID'] != '') $instructorID=$_GET['instructorID'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="styles.css"/>
<title>WOTG Online Attendance</title>
</head>

<body>
<h1>WOTG Online Attendance</h1>
<div class="navLogo"><a href="student-home.php"><img src="images/logo-mobile.png" width="101" height="50" /></a></div>
<div class="nav"><a class="navItem iconLinks" href="attendance-sheet.php"><img src="images/home.png" /></a></div>
<div class="twd_container">
  <div class="twd_row">
    <div class="twd_column twd_two">
      <h2 style="padding-bottom:20px"><?php echo $row_classes['name']; ?></h2>
      <form name="form1" action="<?php echo $editFormAction; ?>" method="POST">
        <table width="0" border="0" cellspacing="0" cellpadding="5">
          <tr>
            <td>Instructor:</td>
            <td><select name="instructorID" id="instructorID">
              <?php
do {  
?>
              <option value="<?php echo $row_instructors['instructorID']?>"<?php if (!(strcmp($row_instructors['instructorID'], $instructorID))) {echo "selected=\"selected\"";} ?>><?php echo $row_instructors['lastName']?>, <?php echo $row_instructors['firstName']?></option>
              <?php
} while ($row_instructors = mysql_fetch_assoc($instructors));
  $rows = mysql_num_rows($instructors);
  if($rows > 0) {
      mysql_data_seek($instructors, 0);
	  $row_instructors = mysql_fetch_assoc($instructors);
  }
?>
              </select></td>
          </tr>
          <tr>
            <td>Drop In Fee:</td>
            <td>$<?php echo $row_classes['classFee']; ?></td>
          </tr>
          <tr>
            <td>Student Name:</td>
            <td><select name="studentID">
                <option value="" selected="selected">Select a Student:</option>
                <?php
do {  
?>
                <option value="<?php echo $row_allStudents['studentID']?>"><?php echo $row_allStudents['lastName']?>, <?php echo $row_allStudents['firstName']?></option>
                <?php
} while ($row_allStudents = mysql_fetch_assoc($allStudents));
  $rows = mysql_num_rows($allStudents);
  if($rows > 0) {
      mysql_data_seek($allStudents, 0);
	  $row_allStudents = mysql_fetch_assoc($allStudents);
  }
?>
              </select></td>
            <td width="25"><a href="javescript:void();" onclick="window.open('students-add.php','Add Student','height=500,width=300');return false;"><img src="images/addPerson.png" width="28" height="28" alt="add a new student" /></a></td>
          </tr>
          <tr>
            <td>Select One:</td>
            <td><select name="attendanceType" id="attendanceType">
                <option selected="selected"> </option>
                <option value="Drop In">Drop In</option>
                <option value="Pre Paid">Pre Paid</option>
              </select></td>
            <td width="25">&nbsp;</td>
          </tr>
          <tr>
            <td>Payment Type:</td>
            <td><select name="paymentType" id="paymentType">
                <option selected="selected"> </option>
                <option value="Cash">Cash</option>
                <option value="Check">Check</option>
                <option value="PayPal">PayPal</option>
              </select></td>
            <td width="25">&nbsp;</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td><input name="submit" type="submit" value="Add Student" />
              <input name="action" type="hidden" value="add" /><input name="classID" type="hidden" value="<?php echo $_GET['classID']; ?>" /></td>
            <td width="25">&nbsp;</td>
          </tr>
        </table>
        <input type="hidden" name="MM_insert" value="form" />
		<input type="hidden" name="dateAdded" value="<?php echo $today ?>" />
		<input type="hidden" name="MM_insert" value="form1" />
      </form>
    </div>
    <div class="twd_column twd_two">
  <h2>Current Members</h2>
  <?php 
  if($totalRows_currentMembers==0) { 
  echo '<p>No members found.</p>'; 
  } 
  else { 
  echo '<p>Memberships are not valid unless they are listed below.</p>';
  //echo '<p>Click on any member name to add:</p>';
 ?>
  <table border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td><strong>name</strong></td>
      <td><strong>start date</strong></td>
      <td><strong>end date</strong></td>
    </tr>
      <?php do { ?>
    <tr>
      <td><?php echo $row_currentMembers['lastName']; ?>, <?php echo $row_currentMembers['firstName']; ?></td>
      <td><?php echo $row_currentMembers['startDate']; ?></td>
      <td><?php echo $row_currentMembers['endDate']; ?></td>
    </tr>
    <?php } while ($row_currentMembers = mysql_fetch_assoc($currentMembers)); } ?>
  </table>
  </div>
</div>
</div>
<hr style="margin:20px 0 20px 0" />
<div class="twd_container" style="padding-left:1%">
 <table cellpadding="5" cellspacing="0">
    <tr style="background-color:#ccc">
      <td><strong>name</strong></td>
      <td><strong>email</strong></td>
      <td><strong>type</strong></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo $row_attendance['firstName']; ?> <?php echo $row_attendance['lastName']; ?></td>
        <td><?php echo $row_attendance['emailAddress']; ?></td>
        <td><?php echo $row_attendance['attendanceType']; ?></td>
        <td><?php echo $row_attendance['paymentType']; ?></td>
        <td><img src="images/edit.png" width="16" height="16" /></td>
        <td><img src="images/delete.png" width="16" height="16" /></td>
      </tr>
      <?php } while ($row_attendance = mysql_fetch_assoc($attendance)); ?>
  </table>
</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($classes);

mysql_free_result($allStudents);

mysql_free_result($currentMembers);

mysql_free_result($instructors);

mysql_free_result($attendance);

mysql_free_result($students);
?>