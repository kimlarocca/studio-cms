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
$query_studio = sprintf("SELECT * FROM studios WHERE studioID = %s", GetSQLValueString($colname_studio, "int"));
$studio = mysql_query($query_studio, $wotg) or die(mysql_error());
$row_studio = mysql_fetch_assoc($studio);
$totalRows_studio = mysql_num_rows($studio);
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
<link rel="stylesheet" type="text/css" href="dropzone.css"/>
<script src="dropzone.js"></script>
<script src="https://cdn.ckeditor.com/4.5.0/standard-all/ckeditor.js"></script>
<title>Slate It | Start Your Free Trial</title>
</head>

<body>
<div class="navLogo"><img src="images/logo-mobile.png" width="101" height="50" /></div>
<div class="logo twd_centered"><img src="images/logo.png" width="245" height="120" /></div>
<h1 class="twd_centered">Start Your Free Trial!</h1>
<div class="twd_container" style="min-height:500px;">
  <h2 class="twd_centered">Step 4: Setup Optional Info</h2>
  <div class="twd_centered twd_margin20"><img src="images/onboarding-step4.png" width="320" height="35" /></div>
  
  <!-- allow reservations -->
  <div id="reservations">
    <h3 class="twd_centered twd_margin20">Are you planning to allow students to reserve their spots in your classes online?</h3>
    <p class="twd_centered twd_margin20"><a class="button" id="allowReservations">yes</a> <a class="button" id="dontAllowReservations">no</a></p>
  </div>
  <div id="customize" style="display:none">
    <h3 class="twd_centered twd_margin20">Upload Your Logo</h3>
    <p class="twd_centered twd_margin20">This is optional. Upload logos as many times are you like here until it's just right! Don't worry, you can remove or change this later. For best results, images should be a minimum of 480px wide. Max file upload size is 2MB.</p>
    <div id="dropzoneDiv"> 
      <!-- desktop/tablet -->
      <div style="margin:auto; width:420px;" class="twd_hideOnMobile">
        <div class="image_upload_div" style="width:200px; height:200px; margin:auto; float:left">
          <form action="upload-logo.php" class="dropzone" id="myAwesomeForm">
            <input name="file_name" type="hidden" value="<?php echo $now; ?>" />
            <input name="studioID" type="hidden" value="<?php echo $row_studio['studioID']; ?>" />
          </form>
        </div>
        <div style="width:200px;  margin:auto; padding:10px 0 0 20px; float:left" id="logo">
          <?php if($row_studio['logoURL']!=''){ ?>
          <img src="uploads/<?php echo $row_studio['logoURL']; ?>" />
          <?php } else { ?>
          <img height="200" width="200" src="uploads/unavailable.gif" />
          <?php } ?>
        </div>
      </div>
      
      <!-- mobile -->
      <div style="margin:auto; width:300px;" class="twd_hideOnTablet twd_hideOnDesktop">
        <div class="image_upload_div" style="width:200px; height:200px; margin:auto;">
          <form action="upload-logo.php" class="dropzone" id="myAwesomeForm2">
            <input name="file_name" type="hidden" value="<?php echo $now; ?>" />
            <input name="studioID" type="hidden" value="<?php echo $row_studio['studioID']; ?>" />
          </form>
        </div>
        <div style="padding:10px 0 0 20px;"></div>
        <div style="width:200px;margin:auto; padding-top:10px" id="logo2">
          <?php if($row_studio['logoURL']!=''){ ?>
          <img src="uploads/<?php echo $row_studio['logoURL']; ?>" />
          <?php } else { ?>
          <img height="200" width="200" src="uploads/unavailable.gif" />
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="twd_clearfloat" style="padding-top:20px"></div>
    <h3 class="twd_centered twd_margin20">Choose An Accent Color</h3>
    <p class="twd_centered twd_margin20">This is optional. Your accent color will help brand your student's online account area. Be sure you choose a color that is dark enough for white text to be readable on top of it!</p>
    <?php 
		$color = '#70adc7';
		$colorFont = '#ffffff';
		if ($row_studio['color']!='') $color = $row_studio['color'];
		if ($row_studio['colorFont']!='') $colorFont = $row_studio['colorFont'];
		?>
    <input onblur="setColors(<?php echo $row_studio['studioID']; ?>);" class="twd_centered" type="text" maxlength="6" size="6" name="color" id="color" value="<?php echo $color; ?>" style="background-color:<?php echo $color; ?>">
    <div class="twd_clearfloat" style="padding-top:20px"></div>
    <p class="twd_centered twd_margin20"><a class="button" id="customizeDone">save &amp; continue</a></p>
  </div>
  <!-- end allow reservations --> 
  
  <!-- allow students to register without paying -->
  <div id="prepay" style="display:none">
    <h3 class="twd_centered twd_margin20">Are you planning to allow students to reserve their spots <u>without</u> pre-paying?</h3>
    <p class="twd_centered twd_margin20" id="prepayButtons"><a class="button" onclick="dontRequirePrepay(<?php echo $row_studio['studioID']; ?>);">yes</a> <a id="requirePrepay" class="button">no</a></p>
  </div>
  <div id="prepayOptions" style="display:none">
    <p class="twd_centered twd_margin20">Ok, for this you will need a payment gateway! Right now we support PayPal Payments Pro. If you have a different gateway, please contact us and we will add support for your gateway.</p>
    <p class="twd_centered twd_margin10">Select Your Payment Gateway:</p>
    <select class="twd_centered twd_margin20" name="paymentGateway" id="paymentGateway">
      <option value="PayPal" <?php if (!(strcmp("PayPal", $row_studio['paymentGateway']))) {echo "selected=\"selected\"";} ?>>PayPal</option>
    </select>
    <p class="twd_centered twd_margin10">Client ID / API Login ID:</p>
    <input class="twd_centered twd_margin20" name="paymentGatewayID" type="text" id="paymentGatewayID" value="<?php echo htmlentities($row_studio['paymentGatewayID']); ?>" size="32" />
    <p class="twd_centered twd_margin10">Client Secret / Transaction Key:</p>
    <input class="twd_centered twd_margin20" name="paymentGatewayKey" type="text" id="paymentGatewayKey" value="<?php echo htmlentities($row_studio['paymentGatewayKey']); ?>" size="32" />
    <p class="twd_centered twd_margin20"><a class="button" onclick="setGateway(<?php echo $row_studio['studioID']; ?>);">save &amp; continue</a> <a class="button" onclick="dontRequirePrepay(<?php echo $row_studio['studioID']; ?>);">Skip This For Now</a></p>
  </div>
  <!-- end allow students to register without paying --> 
  
  <!-- enable memberships -->
  <div id="memberships" style="display:none">
    <h3 class="twd_centered twd_margin20">Do you offer memberships?</h3>
    <p class="twd_centered twd_margin20" id="membershipButtons"><a class="button" id="allowMemberships">yes</a> <a class="button" onclick="disableMemberships(<?php echo $row_studio['studioID']; ?>);">no</a></p>
  </div>
  <div id="membershipFee" style="display:none">
    <p class="twd_centered twd_margin20">Great :) what is your monthly membership fee?</p>
    <input class="twd_centered twd_margin20" type="number" name="membershipFeeAmount" id="membershipFeeAmount" required="required" value="<?php echo $row_studio['membershipFee']; ?>">
    <p class="twd_centered"><a class="button" onclick="setMembershipFee(<?php echo $row_studio['studioID']; ?>);">save &amp; continue</a></p>
  </div>
  <!-- end enable memberships --> 
  
  <!-- enable digital waivers -->
  <div id="waivers" style="display:none">
    <h3 class="twd_centered twd_margin20">Do you want to maintain digital waivers for each student?</h3>
    <p class="twd_centered twd_margin20" id="waiversDescription">Students will be able to sign your waiver with any touch screen device and their digital signature will be saved in their account.</p>
    <p class="twd_centered twd_margin20" id="waiverButtons"><a class="button" id="allowWaivers">yes</a> <a class="button" onclick="disableWaiver(<?php echo $row_studio['studioID']; ?>);">no</a></p>
  </div>
  <div id="waiverInfo" style="display:none">
    <p class="twd_centered twd_margin20">Awesome. You just saved a truck load of trees! Enter in your waiver text below. Need inspiration? <a target="_blank" href="http://my.slateit.com/waiver-print.php?studioID=1">View our sample waiver!</a></p>
    <div class="twd_margin20">
      <textarea name="waiverCopy" id="waiverCopy" cols="32" rows="5"><?php echo $row_studio['waiverCopy']; ?></textarea>
    </div>
    <p class="twd_centered"><a class="button" onclick="setWaiver(<?php echo $row_studio['studioID']; ?>);">save &amp; continue</a></p>
  </div>
  
  <!-- end digital waivers --> 
  
  <!--all done-->
  <div id="allDone" style="display:none">
    <h3 class="twd_centered twd_margin20">You're all done!</h3>
    <p class="twd_centered twd_margin20">Whoo hoo! Your account is setup. Now you can login and get started...</p>
    <p class="twd_centered twd_margin20" id="waiverButtons"><a class="button" href="index.php?action=newAccount&emailAddress=<?php echo $row_studio['email']; ?>">click here to login</a></p>
  </div>
</div>
<hr />
<div class="twd_container">
  <p class="twd_centered twd_margin20" style="line-height:32px; display:none" id="startOver"><a href="onboard5.php?studioID=<?php echo $row_studio['studioID']; ?>"><img src="images/start-over.png" width="32" height="32" /> START OVER</a></p>
  <p class="twd_centered twd_margin20"><a class="button" href="index.php?action=newAccount&emailAddress=<?php echo $row_studio['email']; ?>">Skip &amp; Login To Your New Account</a></p>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript" src="jqColorPicker.min.js"></script> 
<script>
$("#allowReservations").click(function() {
	$("#reservations").slideUp();
	$("#customize").delay(550).slideDown();
	$("#startOver").delay(550).slideDown();
		window.scrollTo(0,0);	
	
});
$("#dontAllowReservations").click(function() {
	$("#reservations").slideUp();
	$("#memberships").delay(550).slideDown();
	$("#startOver").delay(550).slideDown();
		window.scrollTo(0,0);	
});
$("#customizeDone").click(function() {
	$("#customize").slideUp();
	$("#prepay").delay(550).slideDown();
		window.scrollTo(0,0);	
});
$("#requirePrepay").click(function() {
	$("#prepayButtons").slideUp();
	$("#prepayOptions").delay(550).slideDown();
		window.scrollTo(0,0);	
});
$("#allowMemberships").click(function() {
	$("#membershipButtons").slideUp();
	$("#membershipFee").delay(550).slideDown();	
		window.scrollTo(0,0);	
});
$("#allowWaivers").click(function() {
	$("#waiverButtons").slideUp();
	$("#waiversDescription").slideUp();
	$("#waiverInfo").delay(550).slideDown();
		window.scrollTo(0,0);		
});

//ajax functions
function setColors(studioID){
	jQuery.ajax({
	 type: "POST",
	 url: "onboard-colors.php",
	 data: 'studioID='+studioID+'&color='+document.getElementById('color').value,
	 cache: false,
	 success: function(response)
	 {
		$('#color').load(document.URL +  ' #color');
	 }
   });
}
function dontRequirePrepay(studioID){
	jQuery.ajax({
	 type: "POST",
	 url: "onboard-dontRequirePrepay.php",
	 data: 'studioID='+studioID,
	 cache: false,
	 success: function(response)
	 {
		$("#prepay").slideUp();
		$("#prepayOptions").slideUp();
		$("#memberships").delay(550).slideDown();
		window.scrollTo(0,0);	
	 }
   });
}
function setGateway(studioID){
	jQuery.ajax({
	 type: "POST",
	 url: "onboard-gateway.php",
	 data: 'studioID='+studioID+'&paymentGateway='+document.getElementById('paymentGateway').value+'&paymentGatewayID='+document.getElementById('paymentGatewayID').value+'&paymentGatewayKey='+document.getElementById('paymentGatewayKey').value,
	 cache: false,
	 success: function(response)
	 {
		$("#prepay").slideUp();
		$("#prepayOptions").slideUp();
		$("#memberships").delay(550).slideDown();
		window.scrollTo(0,0);	
	 }
   });
}
function setMembershipFee(studioID){
	jQuery.ajax({
	 type: "POST",
	 url: "onboard-membershipFee.php",
	 data: 'studioID='+studioID+'&membershipFee='+document.getElementById('membershipFeeAmount').value,
	 cache: false,
	 success: function(response)
	 {
		$("#memberships").slideUp();
		$("#membershipFee").slideUp();
		$("#waivers").delay(550).slideDown();
		window.scrollTo(0,0);	
	 }
   });
}
function disableMemberships(studioID){
	jQuery.ajax({
	 type: "POST",
	 url: "onboard-disableMemberships.php",
	 data: 'studioID='+studioID,
	 cache: false,
	 success: function(response)
	 {
		$("#memberships").slideUp();
		$("#waivers").delay(550).slideDown();
		window.scrollTo(0,0);	
	 }
   });
}
function setWaiver(studioID){
	jQuery.ajax({
	 type: "POST",
	 url: "onboard-waiver.php",
	 data: 'studioID='+studioID+'&waiverCopy='+CKEDITOR.instances.waiverCopy.getData(),
	 cache: false,
	 success: function(response)
	 {
		$("#waivers").slideUp();
		$("#waiverInfo").slideUp();
		$("#allDone").delay(550).slideDown();
		window.scrollTo(0,0);		
	 }
   });
}
function disableWaiver(studioID){
	jQuery.ajax({
	 type: "POST",
	 url: "onboard-disableWaiver.php",
	 data: 'studioID='+studioID,
	 cache: false,
	 success: function(response)
	 {
		$("#waivers").slideUp();
		$("#allDone").delay(550).slideDown();
		window.scrollTo(0,0);	
	 }
   });
}

$('#color').colorPicker();

Dropzone.autoDiscover = false;
$(function() {
  var myDropzone = new Dropzone("#myAwesomeForm");
  myDropzone.on("queuecomplete", function(file) {
		//location.reload(); 
		$('#logo').load(document.URL +  ' #logo');
		myDropzone.removeFile(file);
		
  });
  myDropzone.on("error", function(file, message) { 
                alert(message);
                myDropzone.removeFile(file); 
    });
  var myDropzone2 = new Dropzone("#myAwesomeForm2");
  myDropzone2.on("queuecomplete", function(file) {
		//location.reload(); 
		$('#logo2').load(document.URL +  ' #logo2');
  });
  myDropzone.on("complete", function(file) {
  myDropzone.removeFile(file);
});
  myDropzone2.on("error", function(file, message) { 
                alert(message);
    });
  myDropzone2.on("complete", function(file) {
  myDropzone2.removeFile(file);
});
})

jQuery(document).ready(function() {
  CKEDITOR.replace( 'waiverCopy' );
  CKEDITOR.MakeEditable();
});
</script>
</body>
</html>
<?php
mysql_free_result($studio);
?>