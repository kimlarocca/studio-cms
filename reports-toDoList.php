<?php
mysql_select_db($database_wotg, $wotg);
$query_classes = "SELECT classID FROM classes WHERE studioID = ".$row_currentUser['studioID']." AND classActive = 1";
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);

$query_instructors = "SELECT instructorID FROM instructors WHERE studioID = ".$row_currentUser['studioID']." AND active = 1";
$instructors = mysql_query($query_instructors, $wotg) or die(mysql_error());
$row_instructors = mysql_fetch_assoc($instructors);
$totalRows_instructors = mysql_num_rows($instructors);

$query_packages = "SELECT packageID FROM packages WHERE studioID = ".$row_currentUser['studioID'];
$packages = mysql_query($query_packages, $wotg) or die(mysql_error());
$row_packages = mysql_fetch_assoc($packages);
$totalRows_packages = mysql_num_rows($packages);

if($totalRows_packages==0||$totalRows_instructors<2||$totalRows_classes==0) { 
?>
<div class="module" id="toDoList">
  <h2 style="padding-bottom:10px">To Do List</h2>
  <?php
	  if($totalRows_classes==0) { echo '<p class="twd_centered"><a class="button" href="classes.php">Add Classes</a></p>'; }
	  if($totalRows_instructors<2) { echo '<p class="twd_centered"><a class="button" href="instructors.php">Add Instructors</a></p>'; }
	  if($totalRows_packages==0) { echo '<p class="twd_centered"><a class="button" href="packages.php">Add Packages</a></p>'; }
	  echo '<p class="twd_centered"><a class="button" onclick="hideToDoList('.$row_currentUser['studioID'].');">Hide This To Do List</a></p>';
}
mysql_free_result($classes);
?>
</div>