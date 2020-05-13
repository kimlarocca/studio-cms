<div class="footer">
  <?php
  if(isset($row_currentUser['securityLevel'])){
  if ($row_currentUser['securityLevel'] == 'administrator') {
  ?>
  <div id="footerLinks" class="twd_centered twd_margin20"></div>
  <?php } } ?>
  <div class="twd_centered twd_margin10"><a href="http://www.slateit.com"><img src="images/logo2.png" width="101" height="50" /></a></div>
  <p class="twd_centered">Copyright <?php echo date("Y", strtotime("now"));?> <a href="http://www.SlateIt.com">Slate It</a>, All Rights Reserved</p>
  <div id="gototop"><img src="images/top.png" width="70" height="60" alt="go to the top!" /></div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> 
<script>
$("#gototop").click(function() {
window.scrollTo(0,0);
});
//get screen size
var w = window,
    d = document,
    e = d.documentElement,
    g = d.getElementsByTagName('body')[0],
    x = w.innerWidth || e.clientWidth || g.clientWidth,
    y = w.innerHeight || e.clientHeight || g.clientHeight;

/* set active page */
var currentPage = document.location.pathname.match(/[^\/]+$/)[0];
if (currentPage=='admin.php') {
	$('#dashboard').addClass('active');	
}
if (currentPage.indexOf("attendance") >= 0) {
	$('#attendance').addClass('active');
	//$('.mobileSubMenuItem').hide();
	//$('#attendanceMenuMobile').show();	
	$('#footerLinks').html('<a href="home.php">Dashboard</a> | <a href="attendance-sheet.php">Today&lsquo;s Attendance Sheet</a> | <a href="attendance-daily.php">Daily Attendance Report</a> | <a href="attendance-history.php">Complete Attendance History</a>');
}
if (currentPage.indexOf("classes") >= 0 || currentPage.indexOf("packages") >= 0) {
	$('#classes').addClass('active');
	//$('.mobileSubMenuItem').hide();
	//$('#classesMenuMobile').show();	
	$('#footerLinks').html('<a href="home.php">Dashboard</a> | <a href="classes.php?active=1">Manage Active Classes</a> | <a href="classes.php?active=0">View Inactive Classes</a> | <a href="packages.php">Manage Packages</a>');
}
if (currentPage.indexOf("students") >= 0 || currentPage.indexOf("members") >= 0) {
	$('#students').addClass('active');	
	//$('.mobileSubMenuItem').hide();
	//$('#studentsMenuMobile').show();
	$('#footerLinks').html('<a href="home.php">Dashboard</a> | <a href="students.php">Manage Students</a> | <a href="members.php">Manage Members</a>');
}
if (currentPage.indexOf("instructors") >= 0) {
	$('#instructors').addClass('active');	
	//$('.instructorsMenuMobile').hide();
	//$('#instructorsMenuMobile').show();	
	$('#footerLinks').html('<a href="home.php">Dashboard</a> | <a href="instructors.php?active=1">Manage Active Instructors</a> | <a href="instructors.php?active=0">View Inactive Instructors</a> | <a href="instructor-report.php">Instructor Reports</a>');
}
if (currentPage.indexOf("reports") >= 0) {
	$('#reports').addClass('active');	
	//$('.mobileSubMenuItem').hide();
	//$('#reportsMenuMobile').show();	
	$('#footerLinks').html('<a href="reports.php">View All Reports</a> | <a href="orders.php">Orders</a> | <a href="reports-attendance.php">Attendance Reports</a>');
}
	$('#attendance, #attendanceMenu').hover(function() {
		$('#attendanceMenu').show();
	},
	function() {
		$('#attendanceMenu').hide();
	});

	$('#students, #studentsMenu').hover(function() {
		$('#studentsMenu').show();
	},
	function() {
		$('#studentsMenu').hide();
	});

	$('#instructors, #instructorsMenu').hover(function() {
		$('#instructorsMenu').show();
	},
	function() {
		$('#instructorsMenu').hide();
	});

	$('#classes, #classesMenu').hover(function() {
		$('#classesMenu').show();
	},
	function() {
		$('#classesMenu').hide();
	});

	$('#reports, #reportsMenu').hover(function() {
		$('#reportsMenu').show();
	},
	function() {
		$('#reportsMenu').hide();
	});
	
	//set mobile menu styles
if(x<768){
	d = $('#dashboard')[0].getBoundingClientRect().width;
	d = d*-1;
	$('#studentsMenu,#classesMenu,#attendanceMenu,#reportsMenu,#instructorsMenu').width(x);
	$('#studentsMenu').css('margin-left',d);
	$('#attendanceMenu').css('margin-left',d*2);
	$('#classesMenu').css('margin-left',d*3);
	$('#instructorsMenu').css('margin-left',d*4);
	$('#reportsMenu').css('margin-left',d*5);
	
}
</script>