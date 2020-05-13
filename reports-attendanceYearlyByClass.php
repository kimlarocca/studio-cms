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

$colname_currentUser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_currentUser = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_currentUser = sprintf("SELECT * FROM instructors,studios WHERE instructors.studioID=studios.studioID AND instructors.username = %s", GetSQLValueString($colname_currentUser, "text"));
$currentUser = mysql_query($query_currentUser, $wotg) or die(mysql_error());
$row_currentUser = mysql_fetch_assoc($currentUser);
$totalRows_currentUser = mysql_num_rows($currentUser);

$studioID = $row_currentUser['studioID'];
$classID = -1;
if($_GET['classID']!='') $classID = $_GET['classID'];

mysql_select_db($database_wotg, $wotg);
$query_classes = "SELECT classID, name, classDay, startTime, studioID FROM classes WHERE classActive = 1 AND studioID = '".$studioID."' ORDER BY name ASC";
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);

mysql_select_db($database_wotg, $wotg);
$query_classCount = "SELECT dateAdded,classID FROM attendance WHERE classID=".$classID." GROUP BY dateAdded";
$classCount = mysql_query($query_classCount, $wotg) or die(mysql_error());
$row_classCount = mysql_fetch_assoc($classCount);
$totalRows_classCount = mysql_num_rows($classCount);

mysql_select_db($database_wotg, $wotg);
$query_attendanceCount = "SELECT count(*) from attendance where classID=".$classID;
$attendanceCount = mysql_query($query_attendanceCount, $wotg) or die(mysql_error());
$row_attendanceCount = mysql_fetch_assoc($attendanceCount);
$totalRows_attendanceCount = mysql_num_rows($attendanceCount);
if ($row_currentUser['securityLevel'] == 'instructor') header("Location: index.php?action=denied");
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
<title><?php echo $row_currentUser['studioName']; ?>| Dashboard</title>
<script src="Chart.min.js"></script>
</head>
<body>
<?php include("header.php"); ?>
<h1 class="twd_centered"><?php echo $row_currentUser['studioName']; ?> | Dashboard</h1>
<?php include("navigation.php"); ?>
<div class="twd_container">
  
  
  <?php
//get info for charts
$conn = new mysqli($hostname_wotg, $username_wotg, $password_wotg, $database_wotg);
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$date12 = date("Y-m-d");
$result12 = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND dateAdded > DATE_SUB(CURDATE(), INTERVAL 1 MONTH)");
$row12 = $result12->fetch_row();
$attendance12 = $row12[0];

$date12b = strtotime ( '-12 months' , strtotime ( $date12 ) ) ;
$date12b = date( 'Y-m-d', $date12b );
$result12b = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date12b."' AND dateAdded > DATE_SUB('".$date12b."', INTERVAL 1 MONTH))");
$row12b = $result12b->fetch_row();
$attendance12b = $row12b[0];

$date11 = strtotime ( '-1 months' , strtotime ( $date12 ) ) ;
$date11 = date( 'Y-m-d', $date11 );
$result11 = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date11."' AND dateAdded > DATE_SUB('".$date11."', INTERVAL 1 MONTH))");
$row11 = $result11->fetch_row();
$attendance11 = $row11[0];

$date11b = strtotime ( '-12 months' , strtotime ( $date11 ) ) ;
$date11b = date( 'Y-m-d', $date11b );
$result11b = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date11b."' AND dateAdded > DATE_SUB('".$date11b."', INTERVAL 1 MONTH))");
$row11b = $result11b->fetch_row();
$attendance11b = $row11b[0];

$date10 = strtotime ( '-1 months' , strtotime ( $date11 ) ) ;
$date10 = date( 'Y-m-d', $date10 );
$result10 = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date10."' AND dateAdded > DATE_SUB('".$date10."', INTERVAL 1 MONTH))");
$row10 = $result10->fetch_row();
$attendance10 = $row10[0];

$date10b = strtotime ( '-12 months' , strtotime ( $date10 ) ) ;
$date10b = date( 'Y-m-d', $date10b );
$result10b = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date10b."' AND dateAdded > DATE_SUB('".$date10b."', INTERVAL 1 MONTH))");
$row10b = $result10b->fetch_row();
$attendance10b = $row10b[0];

$date9 = strtotime ( '-1 months' , strtotime ( $date10 ) ) ;
$date9 = date( 'Y-m-d', $date9 );
$result9 = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date9."' AND dateAdded > DATE_SUB('".$date9."', INTERVAL 1 MONTH))");
$row9 = $result9->fetch_row();
$attendance9 = $row9[0];

$date9b = strtotime ( '-12 months' , strtotime ( $date9 ) ) ;
$date9b = date( 'Y-m-d', $date9b );
$result9b = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date9b."' AND dateAdded > DATE_SUB('".$date9b."', INTERVAL 1 MONTH))");
$row9b = $result9b->fetch_row();
$attendance9b = $row9b[0];

$date8 = strtotime ( '-1 months' , strtotime ( $date9 ) ) ;
$date8 = date( 'Y-m-d', $date8 );
$result8 = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date8."' AND dateAdded > DATE_SUB('".$date8."', INTERVAL 1 MONTH))");
$row8 = $result8->fetch_row();
$attendance8 = $row8[0];

$date8b = strtotime ( '-12 months' , strtotime ( $date8 ) ) ;
$date8b = date( 'Y-m-d', $date8b );
$result8b = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date8b."' AND dateAdded > DATE_SUB('".$date8b."', INTERVAL 1 MONTH))");
$row8b = $result8b->fetch_row();
$attendance8b = $row8b[0];

$date7 = strtotime ( '-1 months' , strtotime ( $date8 ) ) ;
$date7 = date( 'Y-m-d', $date7 );
$result7 = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date7."' AND dateAdded > DATE_SUB('".$date7."', INTERVAL 1 MONTH))");
$row7 = $result7->fetch_row();
$attendance7 = $row7[0];

$date7b = strtotime ( '-12 months' , strtotime ( $date7 ) ) ;
$date7b = date( 'Y-m-d', $date7b );
$result7b = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date7b."' AND dateAdded > DATE_SUB('".$date7b."', INTERVAL 1 MONTH))");
$row7b = $result7b->fetch_row();
$attendance7b = $row7b[0];

$date6 = strtotime ( '-1 months' , strtotime ( $date7 ) ) ;
$date6 = date( 'Y-m-d', $date6 );
$result6 = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date6."' AND dateAdded > DATE_SUB('".$date6."', INTERVAL 1 MONTH))");
$row6 = $result6->fetch_row();
$attendance6 = $row6[0];

$date6b = strtotime ( '-12 months' , strtotime ( $date6 ) ) ;
$date6b = date( 'Y-m-d', $date6b );
$result6b = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date6b."' AND dateAdded > DATE_SUB('".$date6b."', INTERVAL 1 MONTH))");
$row6b = $result6b->fetch_row();
$attendance6b = $row6b[0];

$date5 = strtotime ( '-1 months' , strtotime ( $date6 ) ) ;
$date5 = date( 'Y-m-d', $date5 );
$result5 = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date5."' AND dateAdded > DATE_SUB('".$date5."', INTERVAL 1 MONTH))");
$row5 = $result5->fetch_row();
$attendance5 = $row5[0];

$date5b = strtotime ( '-12 months' , strtotime ( $date5 ) ) ;
$date5b = date( 'Y-m-d', $date5b );
$result5b = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date5b."' AND dateAdded > DATE_SUB('".$date5b."', INTERVAL 1 MONTH))");
$row5b = $result5b->fetch_row();
$attendance5b = $row5b[0];

$date4 = strtotime ( '-1 months' , strtotime ( $date5 ) ) ;
$date4 = date( 'Y-m-d', $date4 );
$result4 = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date4."' AND dateAdded > DATE_SUB('".$date4."', INTERVAL 1 MONTH))");
$row4 = $result4->fetch_row();
$attendance4 = $row4[0];

$date4b = strtotime ( '-12 months' , strtotime ( $date4 ) ) ;
$date4b = date( 'Y-m-d', $date4b );
$result4b = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date4b."' AND dateAdded > DATE_SUB('".$date4b."', INTERVAL 1 MONTH))");
$row4b = $result4b->fetch_row();
$attendance4b = $row4b[0];

$date3 = strtotime ( '-1 months' , strtotime ( $date4 ) ) ;
$date3 = date( 'Y-m-d', $date3 );
$result3 = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date3."' AND dateAdded > DATE_SUB('".$date3."', INTERVAL 1 MONTH))");
$row3 = $result3->fetch_row();
$attendance3 = $row3[0];

$date3b = strtotime ( '-12 months' , strtotime ( $date3 ) ) ;
$date3b = date( 'Y-m-d', $date3b );
$result3b = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date3b."' AND dateAdded > DATE_SUB('".$date3b."', INTERVAL 1 MONTH))");
$row3b = $result3b->fetch_row();
$attendance3b = $row3b[0];

$date2 = strtotime ( '-1 months' , strtotime ( $date3 ) ) ;
$date2 = date( 'Y-m-d', $date2 );
$result2 = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date2."' AND dateAdded > DATE_SUB('".$date2."', INTERVAL 1 MONTH))");
$row2 = $result2->fetch_row();
$attendance2 = $row2[0];

$date2b = strtotime ( '-12 months' , strtotime ( $date2 ) ) ;
$date2b = date( 'Y-m-d', $date2b );
$result2b = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date2b."' AND dateAdded > DATE_SUB('".$date2b."', INTERVAL 1 MONTH))");
$row2b = $result2b->fetch_row();
$attendance2b = $row2b[0];

$date1 = strtotime ( '-1 months' , strtotime ( $date2 ) ) ;
$date1 = date( 'Y-m-d', $date1 );
$result1 = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date1."' AND dateAdded > DATE_SUB('".$date1."', INTERVAL 1 MONTH))");
$row1 = $result1->fetch_row();
$attendance1 = $row1[0];

$date1b = strtotime ( '-12 months' , strtotime ( $date1 ) ) ;
$date1b = date( 'Y-m-d', $date1b );
$result1b = $conn->query("SELECT count(*) FROM attendance WHERE classID = '".$classID."' AND studioID = '".$studioID."' AND (dateAdded <= '".$date1b."' AND dateAdded > DATE_SUB('".$date1b."', INTERVAL 1 MONTH))");
$row1b = $result1b->fetch_row();
$attendance1b = $row1b[0];

?>
  <h2 class="twd_centered">Class Attendance: Year Over Year Comparision</h2>
  <form id="form1" name="form1" method="get" action="?">
    <table width="0%" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td><span class="twd_centered">Select a Class:<br />
        </span></td>
        <td><select name="classID" class="twd_centered" id="classID">
          <?php
do {  

$formattedTime = date("g:i a", strtotime($row_classes['startTime']));
?>
          <option value="<?php echo $row_classes['classID']?>"<?php if (!(strcmp($row_classes['classID'], $_GET['classID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_classes['name']?>: <?php echo $row_classes['classDay']?> <?php echo $formattedTime?></option>
          <?php
} while ($row_classes = mysql_fetch_assoc($classes));
  $rows = mysql_num_rows($classes);
  if($rows > 0) {
      mysql_data_seek($classes, 0);
	  $row_classes = mysql_fetch_assoc($classes);
  }
?>
        </select></td>
        <td><input type="submit" class="twd_centered" /></td>
      </tr>
    </table>
    <div class="twd_centered"></div>
  </form>
  <div id="canvasAttendance" class="twd_margin20 largeChart">
    <br />
    <canvas id="chartAttendance" width="1000" height="600"/>
  </div>
</div>
<script>
var dataAttendance = {
	responsive: true,
    labels: ["<?php echo date('M', strtotime($date1))?>","<?php echo date('M', strtotime($date2))?>","<?php echo date('M', strtotime($date3))?>","<?php echo date('M', strtotime($date4))?>","<?php echo date('M', strtotime($date5))?>","<?php echo date('M', strtotime($date6))?>","<?php echo date('M', strtotime($date7))?>","<?php echo date('M', strtotime($date8))?>","<?php echo date('M', strtotime($date9))?>","<?php echo date('M', strtotime($date10))?>","<?php echo date('M', strtotime($date11))?>","<?php echo date('M', strtotime($date12))?>"],
    datasets: [
        {
            label: "<?php echo date('m/Y', strtotime($date1))?>, <?php echo date('m/Y', strtotime($date2))?>, <?php echo date('m/Y', strtotime($date3))?>, <?php echo date('m/Y', strtotime($date4))?>, <?php echo date('m/Y', strtotime($date5))?>, <?php echo date('m/Y', strtotime($date6))?>, <?php echo date('m/Y', strtotime($date7))?>, <?php echo date('m/Y', strtotime($date8))?>, <?php echo date('m/Y', strtotime($date9))?>, <?php echo date('m/Y', strtotime($date10))?>, <?php echo date('m/Y', strtotime($date11))?>, <?php echo date('m/Y', strtotime($date12))?>",
            fillColor: "rgba(177,177,177,.5)",
            strokeColor: "rgba(220,220,220,1)",
            pointColor: "rgba(220,220,220,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(220,220,220,1)",
            data: [<?php echo $attendance1; ?>,<?php echo $attendance2; ?>,<?php echo $attendance3; ?>,<?php echo $attendance4; ?>,<?php echo $attendance5; ?>,<?php echo $attendance6; ?>,<?php echo $attendance7; ?>,<?php echo $attendance8; ?>,<?php echo $attendance9; ?>,<?php echo $attendance10; ?>,<?php echo $attendance11; ?>,<?php echo $attendance12; ?>]
        },
        {
            label: "<?php echo date('m/Y', strtotime($date1))?>, <?php echo date('m/Y', strtotime($date2))?>, <?php echo date('m/Y', strtotime($date3))?>, <?php echo date('m/Y', strtotime($date4))?>, <?php echo date('m/Y', strtotime($date5))?>, <?php echo date('m/Y', strtotime($date6))?>, <?php echo date('m/Y', strtotime($date7))?>, <?php echo date('m/Y', strtotime($date8))?>, <?php echo date('m/Y', strtotime($date9))?>, <?php echo date('m/Y', strtotime($date11b))?>, <?php echo date('m/Y', strtotime($date12b))?>",
            fillColor: "rgba(112,173,199,.5)",
            strokeColor: "rgba(151,187,205,1)",
            pointColor: "rgba(151,187,205,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(151,187,205,1)",
            data: [<?php echo $attendance1b; ?>,<?php echo $attendance2b; ?>,<?php echo $attendance3b; ?>,<?php echo $attendance4b; ?>,<?php echo $attendance5b; ?>,<?php echo $attendance6b; ?>,<?php echo $attendance7b; ?>,<?php echo $attendance8b; ?>,<?php echo $attendance9b; ?>,<?php echo $attendance10b; ?>,<?php echo $attendance11b; ?>,<?php echo $attendance12b; ?>]
        }
    ]
};
</script>
<?php $conn->close(); ?>
  
  
</div>
<?php
if($totalRows_classCount>0){
	?>
<p class="twd_centered twd_margin20"><strong><span style="background-color:rgba(112,173,199,.5)">LAST YEAR</span>&nbsp;&nbsp;&nbsp;&nbsp;<span style="background-color:rgba(177,177,177,.5)">THIS YEAR</span></strong></p>
<p class="twd_centered twd_margin20">Total Students = <?php echo $row_attendanceCount['count(*)']; ?> | Total Classes Taught = <?php echo $totalRows_classCount; ?> | Average Students / Class = <?php echo $row_attendanceCount['count(*)']/$totalRows_classCount; ?></p>
<?php } ?>
<?php include("footer.php"); ?>
<script>


window.onload = function(){
	var ctxAttendance = document.getElementById("chartAttendance").getContext("2d");
	window.myPieAttendance = new Chart(ctxAttendance).Line(dataAttendance);
};
</script>
</body>
</html>
<?php
mysql_free_result($currentUser);

mysql_free_result($classes);

mysql_free_result($classCount);

mysql_free_result($attendanceCount);
?>