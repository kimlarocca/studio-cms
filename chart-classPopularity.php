<?php
//get info for charts
	$conn = new mysqli($hostname_wotg, $username_wotg, $password_wotg, $database_wotg);
    if($mysqli->connect_errno){
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
	
$result = $conn->query("SELECT classes.name, attendance.classID FROM classes, attendance WHERE attendance.dateAdded >= DATE_SUB(NOW(), INTERVAL 1 MONTH) AND attendance.studioID = '".$studioID."' AND classes.classActive = 1 AND attendance.classID=classes.classID GROUP BY classes.name ORDER BY SUM(ABS(attendance.classID)) DESC LIMIT 5;");
$row = $result->fetch_row();
$name1 = $row[0];
$amount1 = $row[1];
$row = $result->fetch_row();
$name2 = $row[0];
$amount2 = $row[1];
$row = $result->fetch_row();
$name3 = $row[0];
$amount3 = $row[1];
$row = $result->fetch_row();
$name4 = $row[0];
$amount4 = $row[1];
$row = $result->fetch_row();
$name5 = $row[0];
$amount5 = $row[1];
?>
<div class="module">
<h2>Class Popularity</h2>
<?php if ($amount1==NULL) { echo '<p class="twd_centered" style="margin-top:0">You do not have any data to display in this chart.</p>'; } else {
  echo '<p class="twd_centered" style="margin-top:0">Most Popular Classes This Month</p>'; ?>
<div id="canvasClassPopularity" class="twd_margin20">
  <canvas id="chartClassPopularity" width="280" height="300"/>
</div>
<?php } ?>
</div>
<script>
var dataClassPopularity = [
    {
        value: <?php echo $amount5; ?>,
        color:"#F7464A",
        highlight: "#FF5A5E",
        label: "<?php echo $name5; ?>"
    },
    {
        value: <?php echo $amount4; ?>,
        color: "#cd7577",
        highlight: "#d89192",
        label: "<?php echo $name4; ?>"
    },
    {
        value: <?php echo $amount3; ?>,
        color: "#70adc7",
        highlight: "#94c3d7",
        label: "<?php echo $name3; ?>"
    },
    {
        value: <?php echo $amount2; ?>,
        color: "#87a3af",
        highlight: "#a3b9c2",
        label: "<?php echo $name2; ?>"
    },
    {
        value: <?php echo $amount1; ?>,
        color: "#ccc",
        highlight: "#c0c0c0",
        label: "<?php echo $name1; ?>"
    }
]
</script>
<?php $conn->close(); ?>