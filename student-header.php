<?php
//get colors
$color = '#70adc7';
$colorFont = '#ffffff';
if ($row_student['color']!='') $color = $row_student['color'];
if ($row_student['colorFont']!='') $colorFont = $row_student['colorFont'];
?>
<style>
h1 { background-color:<?php echo $color; ?>; color:<?php echo $colorFont; ?>; }
h2 { color:<?php echo $color; ?>; }
a, a:visited, a:active { color:<?php echo $color; ?>; }
.button, input[type=submit] {
	color: <?php echo $color;?>!important;
	background-color:#fff;
	border: 1px solid <?php echo $color;?>;
	border-radius:3px;
	padding:10px;
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
.button:hover, input[type=submit]:hover {
	color: #fff!important;
	background-color:<?php echo $color;?>;
}
</style>
<div class="nav studentNav"><a class="navItem iconLinks tooltip2" title="home page" href="student-home.php"><img src="images/home.png" /></a> <a class="navItem iconLinks tooltip2" title="change password" href="student-changePassword.php"><img src="images/settings.png" /></a> <a class="navItem iconLinks tooltip2" title="logout" href="student-logout.php"><img src="images/logout.png" /></a></div>