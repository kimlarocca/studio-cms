<div class="tabContainer">
  <div id="dashboard" class="tab twd_centered"> <a href="admin.php">
    <div class="tabContent"><img src="images/dashboard.png" width="32" height="32" />
      <p>Dashboard</p>
    </div>
    </a> </div>
  <div id="students" class="tab twd_centered"> <a href="students.php">
    <div class="tabContent"><img src="images/members.png" width="32" height="32" />
      <p>Students & Members</p>
    </div>
    </a>
    <div id="studentsMenu" class="tab2 twd_centered">
      <div class="tabContent2"><a href="student-login.php?studioID=<?php echo $row_currentUser['studioID']; ?>">
        <div class="tabMenuLink">Student Login Page</div>
        </a><a href="students.php">
        <div class="tabMenuLink">Manage Students</div>
        </a><a href="members.php">
        <div class="tabMenuLink">Manage Members</div>
        </a></div>
    </div>
  </div>
  <div id="attendance" class="tab twd_centered" data-menu="attendanceMenu"> <a href="attendance-daily.php">
    <div class="tabContent"><img src="images/attendance.png" width="32" height="32" />
      <p>Attendance</p>
    </div>
    </a>
    <div id="attendanceMenu" class="tab2 twd_centered">
      <div class="tabContent2">
        <a href="attendance-sheet.php"><div class="tabMenuLink">Today&lsquo;s Attendance</div></a>
        <a href="attendance-daily.php"><div class="tabMenuLink">Daily Report</div></a>
        <a href="attendance-history.php"><div class="tabMenuLink">Attendance History</div></a>
      </div>
    </div>
  </div>
  <div id="classes" class="tab twd_centered">
    <div class="tabContent"><a href="classes.php?active=1"><img src="images/classes.png" width="32" height="32" />
      <p>Classes &amp; Events</p>
    </div>
    <div id="classesMenu" class="tab2 twd_centered">
      <div class="tabContent2">
        <a href="classes.php?active=1"><div class="tabMenuLink">Manage Classes</div></a>
        <a href="events.php"><div class="tabMenuLink">Manage Events / Sessions</div></a>
        <a href="cancellations.php"><div class="tabMenuLink">Manage Class Cancellations</div></a>
        <a href="packages.php"><div class="tabMenuLink">Manage Class Packages</div></a>
      </div>
    </div>
  </div>
  <div id="instructors" class="tab twd_centered">
    <div class="tabContent"><a href="instructors.php?active=1"><img src="images/instructors.png" width="32" height="32" />
      <p>Staff Members</p>
    </div>
    <div id="instructorsMenu" class="tab2 twd_centered">
      <div class="tabContent2">
        <a href="instructors.php?active=1"><div class="tabMenuLink">Active Instructors/Staff</div></a>
        <a href="instructors.php?active=0"><div class="tabMenuLink">Inactive Instructors/Staff</div></a>
        <a href="instructors-calendar.php"><div class="tabMenuLink">Instructor Calendar</div></a>
      </div>
    </div>
  </div>
  <div id="reports" class="tab twd_centered">
    <a href="reports.php"><div class="tabContent"><img src="images/reports.png" width="32" height="32" />
      <p>Reports</p>
    </div></a>
    <div id="reportsMenu" class="tab2 twd_centered">
      <div class="tabContent2">
        <a href="reports.php"><div class="tabMenuLink">View All Reports</div></a>
        <a href="orders.php"><div class="tabMenuLink">Orders</div></a>
        <a href="timesheets.php"><div class="tabMenuLink">Timesheets</div></a>
      </div>
    </div>
  </div>
</div>
