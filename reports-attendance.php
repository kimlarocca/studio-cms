<?php
mysql_select_db($database_wotg, $wotg);
$query_classes = "SELECT classDay, startTime, studioID, classActive, classID, name FROM classes WHERE studioID = ".$row_currentUser['studioID']." AND classActive = 1 ORDER BY FIELD(classDay , 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), startTime";
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);
?>
<div class="module">
  <h2 style="padding-bottom:10px">Attendance Sheets</h2>
  <?php
	  if($totalRows_classes==0) { echo '<p>You have not added any classes yet! <a href="classes.php">Click here to manage your classes.</a></p>'; }
	  else {
	  ?>
  <p>View the online attendance sheet for any class, just select a class and a date below:</p>
  <form id="form1" name="form1" method="get" action="attendance-sheet-date.php">
    <select name="classID" id="classID">
      <?php
do {  
$formattedTime = date("g:i a", strtotime($row_classes['startTime']));
?>
      <option value="<?php echo $row_classes['classID']?>"><?php echo $row_classes['classDay']?> <?php echo $formattedTime ?>: <?php echo $row_classes['name']?></option>
      <?php
} while ($row_classes = mysql_fetch_assoc($classes));
  $rows = mysql_num_rows($classes);
  if($rows > 0) {
      mysql_data_seek($classes, 0);
	  $row_classes = mysql_fetch_assoc($classes);
  }
?>
    </select>
    <fieldset>
      <input type="text" id="dateAdded" name="dateAdded" placeholder="Select a Date">
    </fieldset>
    <input name="submit" type="submit" value="view attendance sheet" />
  </form>
  <?php } ?>
</div>
<?php
mysql_free_result($classes);
?>