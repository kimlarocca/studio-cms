<?php
mysql_select_db($database_wotg, $wotg);
$query_instructors = "SELECT instructorID, studioID, active, lastName, firstName, securityLevel FROM instructors WHERE studioID = ".$row_currentUser['studioID']." AND active = 1 ORDER BY lastName ASC";
$instructors = mysql_query($query_instructors, $wotg) or die(mysql_error());
$row_instructors = mysql_fetch_assoc($instructors);
$totalRows_instructors = mysql_num_rows($instructors);
?>
<div class="module">
  <h2 style="padding-bottom:10px">Instructor Reports</h2>
  <p>Generate attendance reports within any given time frame by selecting an instructor, start and end date below:</p>
  <form method="get" action="instructor-report.php" name="form0">
    <select name="instructorID" id="instructorID">
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
        <option value="all">* view all on 1 page *</option>
    </select>
    <input type="text" id="startDate" name="startDate" placeholder="Select a Start Date">
    <input type="text" id="endDate" name="endDate" placeholder="Select a End Date">
    <input name="submit2" type="submit" value="generate report" />
  </form>
</div>
<?php
mysql_free_result($instructors);
?>