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

$colname_studio = "-1";
if (isset($_GET['studioID'])) {
  $colname_studio = $_GET['studioID'];
}
mysql_select_db($database_wotg, $wotg);
$query_studio = sprintf("SELECT studioID, color, studioName FROM studios WHERE studioID = %s", GetSQLValueString($colname_studio, "int"));
$studio = mysql_query($query_studio, $wotg) or die(mysql_error());
$row_studio = mysql_fetch_assoc($studio);
$totalRows_studio = mysql_num_rows($studio);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="styles.css"/>
<title><?php if ($row_studio!=0) echo $row_studio['studioName']." | "; ?>Sign Waiver</title>
<style>
@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,600);
* {
	-webkit-box-sizing: border-box; /* Safari/Chrome, other WebKit */
	-moz-box-sizing: border-box;    /* Firefox, other Gecko */
	box-sizing: border-box;         /* Opera/IE 8+ */
}
body {
	font-family: 'Open Sans', sans-serif;
	font-weight: normal;
	color: #484c51;
	background-color: #fff;
	margin: 0;
	padding: 0;
}
img {
	max-width: 100%;
	border: 0;
	height:auto;
}
a, a:visited, a:active {
	color: <?php echo $row_studio['color'];?>;
	text-decoration: none;
}
a:hover {
	opacity:.75;
	text-decoration: none;
}
p {
	margin:0 0 8px 0;
}
h2 {
 background-color:<?php echo $row_studio['color'];?>;
	width:100%;
	color:#fff;
	margin:0;
	font-size:20px;
	padding:5px 1% 5px 1%;
	clear:both;
}
.button {
 color: <?php echo $row_studio['color'];
?>!important;
	background-color:#fff;
 border: 1px solid <?php echo $row_studio['color'];
?>;
	border-radius:3px;
	padding:5px;
	text-transform:uppercase;
	font-weight:bold;
	display:inline-block;
	text-align:center;
	min-width:125px;
	cursor:pointer;
	transition: all 0.25s linear;
	-webkit-transition: all 0.25s linear;
	-moz-transition: all 0.25s linear;
	-o-transition: all 0.25s linear;
	-ms-transition: all 0.25s linear;
}
.button:hover {
	color: #fff!important;
 background-color:<?php echo $row_studio['color'];
?>;
}
.red {
	color:red;
}
.green {
	color: #390
}
td {
	padding: 0 10px 10px 0;
}
</style>
</head>

<body>

 <?php if($row_studio['logoURL']!=''){ ?>
        <div class="twd_centered twd_margin20" style="padding-top:10px"><img src="uploads/<?php echo $row_studio['logoURL']; ?>" /></div>
        <?php } ?>
<h1 class="studentH1 twd_centered"><?php if ($row_studio!=0) echo $row_studio['studioName']." | "; ?>Sign Waiver</h1>
<form action="waiver-public2.php" method="post">
<table width="300" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td style="padding:5px"><label for="firstName">
      First Name:</label>
      <br /><input type="text" name="firstName" id="firstName" style="width:100%" />
      </td>
  </tr>
  <tr>
    <td style="padding:5px">
      Last Name:<br /><input type="text" name="lastName" id="lastName" style="width:100%" />
      </td>
  </tr>
  <tr>
    <td style="padding:5px">
      Email Address:<br /><input type="text" name="emailAddress" id="emailAddress" style="width:100%" />
      </td>
  </tr>
  <tr>
    <td><input type="submit" name="button" id="button" value="Continue" style="width:100%" /><input name="studioID" type="hidden" value="<?php echo $_GET['studioID']; ?>" /></td>
  </tr>
</table>
</form>

</body>
</html>
<?php
mysql_free_result($studio);
?>