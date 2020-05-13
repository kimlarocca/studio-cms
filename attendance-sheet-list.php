<?php require_once('Connections/wotg.php'); ?>
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
mysql_select_db($database_wotg, $wotg);
$query_attendance = "SELECT classes.classID, classes.classCapacity, attendance.checkedIn, attendance.dateAdded AS attendanceDate,attendance.attendanceID, attendance.classID,attendance.studentID,attendance.paymentType,attendance.attendanceType, students.studentID, students.classesLeft, students.firstName,students.lastName,students.emailAddress,students.waiver, instructors.instructorID,instructors.firstName AS instructorName FROM attendance,students,instructors,classes WHERE attendance.classID=classes.classID AND attendance.instructorID=instructors.instructorID AND attendance.studentID = students.studentID AND attendance.classID = ".$_GET['classID']." AND attendance.dateAdded = '".$_GET['today']."' LIMIT 100";
$attendance = mysql_query($query_attendance, $wotg) or die(mysql_error());
$row_attendance = mysql_fetch_assoc($attendance);
$totalRows_attendance = mysql_num_rows($attendance);
?>
<?php
if ($totalRows_attendance == 0 ) echo '<p style="color:red">There are no attendance records for this class.</p>';
else {
	$classCapacity = $row_attendance['classCapacity'];
?>

<table cellpadding="5" cellspacing="0">
  <thead>
    <tr style="background-color:#ccc">
      <th><strong>name</strong></th>
      <th><strong>email</strong></th>
      <th><strong>type</strong></th>
      <th>&nbsp;
        </td>
      
      <th><strong>instructor</strong> </th>
      <th align="center">
      <th>&nbsp;
        </td>
    </tr>
  </thead>
  <?php do { ?>
  <tr>
    <td><a href="students-update.php?studentID=<?php echo $row_attendance['studentID']; ?>" class="tooltip" title="edit student information"><?php echo $row_attendance['firstName']; ?> <?php echo $row_attendance['lastName']; ?></a></td>
    <td><?php 
		echo '<a href="mailto:'.$row_attendance['emailAddress'].'">'.$row_attendance['emailAddress'].'</a>';
		if ($row_attendance['emailAddress'] == '') {
			?>
      </a> <a href="#" onclick="window.open('student-update-email.php?studentID=<?php echo $row_attendance['studentID']; ?>','Update Student','height=500,width=300');return false;" style="color:red">get email address</a>
      <?php }
		?></td>
    <td><?php 
	  echo $row_attendance['attendanceType'];
	  if($row_attendance['attendanceType']=='Pre Paid'){ 
	  echo ' (balance: '.$row_attendance['classesLeft'].')'; 
	  }
	  ?></td>
    <td><?php echo $row_attendance['paymentType']; ?></td>
    <td><?php echo $row_attendance['instructorName']; ?></td>
    <td align="center"> <?php 
		if ($row_attendance['checkedIn']==true) {
			?>
      <div style="text-align:center"><img src="images/checkmark.png" width="20" height="20"></div>
      <?php } else {
		?>
      <button onclick="checkin(<?php echo $row_attendance['attendanceID']; ?>);">check in</button> 
      <?php } ?></td>
    <td><a class="tooltip" title="edit attendance record" href="attendance-fullUpdate.php?attendanceID=<?php echo $row_attendance['attendanceID']; ?>"><img src="images/edit.png" width="20" height="20" />&nbsp;</a><a class="tooltip" title="delete this record" href="javescript:void();" onclick="window.open('attendance-delete.php?attendanceID=<?php echo $row_attendance['attendanceID']; ?>','Delete Attendance','height=500,width=300');return false;"><img src="images/delete.png" width="20" height="20" /></a>
    <!--<a href="javascript:void();" class="tooltip" title="delete attendance record" onClick="deleteRecord(<?php echo $row_attendance['attendanceID']; ?>,'<?php echo $row_attendance['attendanceType']; ?>',<?php echo $row_attendance['studentID']; ?>);"><img src="images/delete.png" width="20" height="20" /></a>-->
      <?php
		if ($row_attendance['waiver'] != 1) {
		?>
      <a href="waiver.php?studentID=<?php echo $row_attendance['studentID']; ?>" style="color:red">get waiver &gt;&gt;</a>
      <?php
	}
	?></td>
  </tr>
  <?php } while ($row_attendance = mysql_fetch_assoc($attendance)); } ?>
</table>
<p><em><?php echo $totalRows_attendance; ?> students out of <?php echo $classCapacity; ?> max</em></p>
<?php
mysql_free_result($attendance);
?>
