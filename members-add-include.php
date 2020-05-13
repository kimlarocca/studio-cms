<?php
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO members (studentID, startDate, endDate, membershipFee, studioID) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['studentID'], "int"),
                       GetSQLValueString($_POST['startDate'], "date"),
                       GetSQLValueString($_POST['endDate'], "date"),
                       GetSQLValueString($_POST['membershipFee'], "int"),
                       GetSQLValueString($_POST['studioID'], "int"));

  mysql_select_db($database_wotg, $wotg);
  $Result1 = mysql_query($insertSQL, $wotg) or die(mysql_error());

  $insertGoTo = "members-add.php?action=saved";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

mysql_select_db($database_wotg, $wotg);
$query_students = "SELECT * FROM students WHERE studioID = ".$row_currentUser['studioID']." ORDER BY lastName, firstName ASC";
$students = mysql_query($query_students, $wotg) or die(mysql_error());
$row_students = mysql_fetch_assoc($students);
$totalRows_students = mysql_num_rows($students);
?>
<h3 class="twd_centered twd_margin20">Fill out the form below to add a new member!</h3>
<?php
//check if changes were saved
if ($_GET['action'] == 'saved') print '<p class="twd_centered" style="color:red">The new member has been added!</p>'; 
?>
  <form class="twd_centered" action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1" onsubmit="MM_validateForm('startDate','','R','endDate','','R');MM_validateForm('startDate','','R','endDate','','R','student','','R');return document.MM_returnValue">
    <input class="twd_centered" name="membershipFee" type="hidden" value="150" />
    Start Date *
    <input class="twd_centered" type="text" id="startDate" name="startDate">
    
    End Date *
    <input class="twd_centered" type="text" id="endDate" name="endDate">
    
    Student *  <a href="javescript:void();" onclick="window.open('students-add.php','Add Student','height=500,width=300');return false;"><img src="images/addPerson.png" width="20" height="20" alt="add a new student" /></a>
    <input class="twd_centered twd_margin20" type="text" autocomplete="off" name="student" class="auto" id="student" />
        <input type="hidden" id="studentID" name="studentID" />
    <input type="hidden" name="studioID" value="<?php echo $row_currentUser['studioID']; ?>" />
    <input class="twd_centered twd_margin20" type="submit" value="Add Member" />
    <input type="hidden" name="MM_insert" value="form1" />
  </form>
  <p class="twd_centered">*<em> required information</em></p>
</div>
<?php
mysql_free_result($students);
?>