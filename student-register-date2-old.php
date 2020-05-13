<?php require_once('Connections/wotg.php'); ?>
<?php
require __DIR__ . '/PayPal/paypal/rest-api-sdk-php/sample/bootstrap.php';
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
$today = $_GET['datePicked'];
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
?>
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

$MM_restrictGoTo = "student-login.php?action=failed";
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

$colname_student = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_student = $_SESSION['MM_Username'];
}
mysql_select_db($database_wotg, $wotg);
$query_student = sprintf("SELECT * FROM students,studios WHERE emailAddress = %s AND students.studioID=studios.studioID", GetSQLValueString($colname_student, "text"));
$student = mysql_query($query_student, $wotg) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);

mysql_select_db($database_wotg, $wotg);
$query_classes = "SELECT * FROM classes WHERE classID = ".$_GET['classID'];
$classes = mysql_query($query_classes, $wotg) or die(mysql_error());
$row_classes = mysql_fetch_assoc($classes);
$totalRows_classes = mysql_num_rows($classes);

mysql_select_db($database_wotg, $wotg);
$query_currentMembers = "SELECT * FROM members,students WHERE students.studentID = ".$row_student['studentID']." AND members.studentID = students.studentID AND '".$today."' between members.startDate AND members.endDate";
$currentMembers = mysql_query($query_currentMembers, $wotg) or die(mysql_error());
$row_currentMembers = mysql_fetch_assoc($currentMembers);
$totalRows_currentMembers = mysql_num_rows($currentMembers);

$colname_attendance = "-1";
if (isset($_GET['classID'])) {
  $colname_attendance = $_GET['classID'];
}
mysql_select_db($database_wotg, $wotg);
$query_attendance = sprintf("SELECT * FROM attendance WHERE dateAdded = '".$today."' AND classID = %s", GetSQLValueString($colname_attendance, "int"));
$attendance = mysql_query($query_attendance, $wotg) or die(mysql_error());
$row_attendance = mysql_fetch_assoc($attendance);
$totalRows_attendance = mysql_num_rows($attendance);

ini_set('session.save_path',getcwd(). '/../tmp/');
$_SESSION['Payment_Amount'] = $row_classes['classFee'];
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
<title><?php echo $row_student['studioName']; ?> | My Account</title>
</head>

<body>
<?php include("student-header.php"); ?>
<h1 class="studentH1 twd_centered"><?php echo $row_student['studioName']; ?> | Register Online</h1>
<?php 
//get logo if it exists
if($row_student['logoURL']!=''){ 
?>
<div class="twd_centered twd_margin20" style="padding-top:20px; clear:both"><img src="uploads/<?php echo $row_student['logoURL']; ?>" /></div>
<?php 
}
?>
<div class="twd_container">
<div class="twd_centered twd_margin20">
  <?php 
  
 $formattedDate = date("l, m/d/Y", strtotime($_GET['datePicked']));
  if($row_classes['thumbnail']!=''){ ?>
        <img height="200" width="200" src="uploads/<?php echo $row_classes['thumbnail']; ?>" />
        <?php } else { ?>
        <img class="twd_centered" height="200" width="200" src="uploads/unavailable.gif" />
        <?php } ?>
        </div>
  <p class="twd_centered twd_margin20">You have selected <strong><?php echo $row_classes['name']; ?></strong> on <strong><?php echo $formattedDate; ?></strong></p>
<?php
//checkto be sure there are spots left in the class
if($totalRows_attendance >= $row_classes['classCapacity']){
	echo '<p class="twd_centered" style="color:red;">Sorry, this class is full! Please <a href="student-home.php">return to the home page</a> to make a new reservation.</p>';
} else {
?>
<!-- members -->
<?php
if($row_currentMembers>0){
$addrecords = "INSERT INTO attendance(studentID, classID, instructorID, dateAdded, attendanceType, studioID) VALUES (".$row_student['studentID'].",".$row_classes['classID'].",".$row_classes['instructorID'].",'".$_GET['datePicked']."','Member',".$row_student['studioID'].")";
mysql_select_db($database_wotg, $wotg);
mysql_query($addrecords, $wotg) or die(mysql_error());	
?>
<h2 class="twd_centered twd_margin20">Registration Complete</h2>
<p class="twd_centered twd_margin20"><strong>Thank you! Your reservation on <?php echo $_GET['datePicked']; ?> for <?php echo $row_classes['name']; ?> is saved.</strong></p>
<p class="twd_centered twd_margin20"><a href="student-home.php">Return to the home page</a> if you'd like to view your upcoming reservations or make a new reservation.</p>
<?php } ?>

<!-- dropins -->
<?php
if($row_currentMembers==0){
?>
  <h2 class="twd_centered" style="padding-top:20px;border-top: #fff solid 2px;">Choose a Payment Method</h2>
  <h3 class="twd_centered twd_margin20">single session: $<?php echo $row_classes['classFee']; ?></h3>


<?php

//get order info
$orderDescription = 'Single Session: '.$row_classes['name'].' on '.$_GET['datePicked'];
$orderAmount = (int)$row_classes['classFee'];
$studentID = $row_student['studentID'];
$classID = $row_classes['classID'];
$instructorID = $row_classes['instructorID'];
$dateAdded = $_GET['datePicked'];
$attendanceType = 'Drop In';
$studioID = $row_student['studioID'];

$payer = new Payer();
$payer->setPaymentMethod("paypal");

$item1 = new Item();
$item1->setName($orderDescription)
    ->setCurrency('USD')
    ->setQuantity(1)
    ->setPrice($orderAmount);
	/*
$item2 = new Item();
$item2->setName('Granola bars')
    ->setCurrency('USD')
    ->setQuantity(5)
    ->setPrice(2);
*/
$itemList = new ItemList();
$itemList->setItems(array($item1));
//$itemList->setItems(array($item1, $item2));

$details = new Details();
$details->setShipping(0)
    ->setTax(0)
    ->setSubtotal($orderAmount);

$amount = new Amount();
$amount->setCurrency("USD")
    ->setTotal($orderAmount)
    ->setDetails($details);

$transaction = new Transaction();
$transaction->setAmount($amount)
    ->setItemList($itemList)
    ->setDescription($orderDescription)
    ->setInvoiceNumber(uniqid());

$baseUrl = getBaseUrl();
$redirectUrls = new RedirectUrls();
$redirectUrls->setReturnUrl("$baseUrl/ExecutePayment.php?success=true")
    ->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");

$payment = new Payment();
$payment->setIntent("sale")
    ->setPayer($payer)
    ->setRedirectUrls($redirectUrls)
    ->setTransactions(array($transaction));

// For Sample Purposes Only.
$request = clone $payment;

try {
    $payment->create($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Created Payment Authorization Using PayPal. Please visit the URL to Authorize.", "Payment", null, $request, $ex);
    exit(1);
}

$approvalUrl = $payment->getApprovalLink();

//add incomplete order to database
$wotg = mysql_pconnect($hostname_wotg, $username_wotg, $password_wotg) or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_select_db($database_wotg, $wotg);
$query_order = "INSERT INTO orders (amount, firstName, lastName, emailAddress, studioID, orderInfo, orderStatus, notes, paymentID, classDate, classID, studentID) VALUES (".$orderAmount.", '".$row_student['firstName']."', '".$row_student['lastName']."','".$row_student['emailAddress']."',".$studioID.",'Drop In','pending','".$orderDescription."','".$payment->id."','".$dateAdded."',".$classID.",".$studentID.")";
$order = mysql_query($query_order, $wotg) or die(mysql_error());
$id = mysql_insert_id();
 echo '<div class="twd_centered twd_margin20"><a href='.$approvalUrl.'><img  class="twd_centered" src="../images/paypal.png" width="142" height="27"></a></div>';

//return $payment;
?>

  <h3 class="twd_centered twd_margin20" style="padding-top:20px;">use your prepaid package</h3>
  <?php 
	  if($row_student['classesLeft']==0){
	  ?>
      <p class="twd_centered twd_margin20">You do not currently have any prepaid classes available.</p>
      <?php
	  } else {
	  ?>
      <p class="twd_margin20 twd_centered">You have<strong> <?php echo $row_student['classesLeft']; ?> </strong>prepaid classes left on your account.</p>
  <p class="twd_margin20 twd_centered"><a href="student-register-prepaid.php?orderID=<?php echo $id; ?>" class="button">register now</a></p>
      <?php } ?>
  <p style="margin-top:40px;" class="twd_centered twd_margin20"><a href="student-home.php">&lt;&lt; start over and choose a different date/class</a></p>
<?php } } ?> 

</div>
<?php include("footer.php"); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="jquery.paydunk.min.js"></script>
<script>
$('#paydunkButton').paydunk({
    appID        : 'KXQl371hm0g9tWCTu48RIYpfys3UObvCnl6j9QeN', //your App ID goes here - required!!
    price        : <?php echo $orderAmount; ?>, //required!!
    order_number : <?php echo $id; ?>,
    tax          : 0,
    shipping     : 0
});
</script>
</body>
</html>
<?php
mysql_free_result($student);

mysql_free_result($attendance);
?>
