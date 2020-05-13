<?php
//get info for charts
$conn = new mysqli($hostname_wotg, $username_wotg, $password_wotg, $database_wotg);
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$date7 = date("Y-m-d");
$result7 = $conn->query("SELECT count(*) FROM attendance WHERE studioID = '".$studioID."' AND dateAdded='".$date7."'");
$row7 = $result7->fetch_row();
$attendance7 = $row7[0];

$date6 = strtotime ( '-1 day' , strtotime ( $date7 ) ) ;
$date6 = date ( 'Y-m-d' , $date6 );
$result = $conn->query("SELECT count(*) FROM attendance WHERE studioID = '".$studioID."' AND dateAdded='".$date6."'");
$row = $result->fetch_row();
$attendance6 = $row[0];

$date5 = strtotime ( '-1 day' , strtotime ( $date6 ) ) ;
$date5 = date ( 'Y-m-d' , $date5 );
$result = $conn->query("SELECT count(*) FROM attendance WHERE studioID = '".$studioID."' AND dateAdded='".$date5."'");
$row = $result->fetch_row();
$attendance5 = $row[0];

$date4 = strtotime ( '-1 day' , strtotime ( $date5 ) ) ;
$date4 = date ( 'Y-m-d' , $date4 );
$result = $conn->query("SELECT count(*) FROM attendance WHERE studioID = '".$studioID."' AND dateAdded='".$date4."'");
$row = $result->fetch_row();
$attendance4 = $row[0];

$date3 = strtotime ( '-1 day' , strtotime ( $date4 ) ) ;
$date3 = date ( 'Y-m-d' , $date3 );
$result = $conn->query("SELECT count(*) FROM attendance WHERE studioID = '".$studioID."' AND dateAdded='".$date3."'");
$row = $result->fetch_row();
$attendance3 = $row[0];

$date2 = strtotime ( '-1 day' , strtotime ( $date3 ) ) ;
$date2 = date ( 'Y-m-d' , $date2 );
$result = $conn->query("SELECT count(*) FROM attendance WHERE studioID = '".$studioID."' AND dateAdded='".$date2."'");
$row = $result->fetch_row();
$attendance2 = $row[0];

$date1 = strtotime ( '-1 day' , strtotime ( $date2 ) ) ;
$date1 = date ( 'Y-m-d' , $date1 );
$result = $conn->query("SELECT count(*) FROM attendance WHERE studioID = '".$studioID."' AND dateAdded='".$date1."'");
$row = $result->fetch_row();
$attendance1 = $row[0];
?>
<div class="module">
  <h2 class="twd_centered">This Week's Attendance</h2>
  <div id="canvasAttendance" class="twd_margin20">
    <canvas id="chartAttendance" width="280" height="300"/>
  </div>
  <p class="twd_centered" style="margin-top:0"><?php echo $attendance1+$attendance2+$attendance3+$attendance4+$attendance5+$attendance6+$attendance7; ?> Total Students</p>
</div>
<script>
var dataAttendance = {
    labels: ["<?php echo date('l', strtotime($date1))?>","<?php echo date('l', strtotime($date2))?>","<?php echo date('l', strtotime($date3))?>","<?php echo date('l', strtotime($date4))?>","<?php echo date('l', strtotime($date5))?>","<?php echo date('l', strtotime($date6))?>","Today"],
    datasets: [
        {
            label: "My First dataset",
            fillColor: "rgba(220,220,220,0.2)",
            strokeColor: "rgba(220,220,220,1)",
            pointColor: "rgba(220,220,220,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(220,220,220,1)",
            data: [<?php echo $attendance1; ?>,<?php echo $attendance2; ?>,<?php echo $attendance3; ?>,<?php echo $attendance4; ?>,<?php echo $attendance5; ?>,<?php echo $attendance6; ?>,<?php echo $attendance7; ?>]
        },
        {
            label: "My Second dataset",
            fillColor: "rgba(151,187,205,0.2)",
            strokeColor: "rgba(151,187,205,1)",
            pointColor: "rgba(151,187,205,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(151,187,205,1)",
            data: [<?php echo $attendance1; ?>,<?php echo $attendance2; ?>,<?php echo $attendance3; ?>,<?php echo $attendance4; ?>,<?php echo $attendance5; ?>,<?php echo $attendance6; ?>,<?php echo $attendance7; ?>]
        }
    ]
};
</script>
<?php $conn->close(); ?>