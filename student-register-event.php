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

$colname_eventAttendance = "-1";
if (isset($_GET['eventID'])) {
  $colname_eventAttendance = $_GET['eventID'];
}
mysql_select_db($database_wotg, $wotg);
$query_eventAttendance = sprintf("SELECT eventAttendanceID, eventID FROM eventAttendance WHERE eventID = %s", GetSQLValueString($colname_eventAttendance, "int"));
$eventAttendance = mysql_query($query_eventAttendance, $wotg) or die(mysql_error());
$row_eventAttendance = mysql_fetch_assoc($eventAttendance);
$totalRows_eventAttendance = mysql_num_rows($eventAttendance);

mysql_select_db($database_wotg, $wotg);
$query_events = "SELECT * FROM events WHERE eventID=".$_GET['eventID'];
$events = mysql_query($query_events, $wotg) or die(mysql_error());
$row_events = mysql_fetch_assoc($events);
$totalRows_events = mysql_num_rows($events);

ini_set('session.save_path',getcwd(). '/../tmp/');
$_SESSION['Payment_Amount'] = $row_events['eventFee'];

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
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
<title><?php echo $row_student['studioName']; ?> | Event Registration</title>
</head>

<body>
<?php include("student-header.php"); ?>
<h1 class="studentH1 twd_centered"><?php echo $row_student['studioName']; ?> | Event Registration</h1>
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
 $formattedDate = date("l, m/d/Y", strtotime($row_events['eventDate']));
    if($row_events['thumbnail']!=''){ ?>
        <img height="200" width="200" src="uploads/<?php echo $row_events['thumbnail']; ?>" />
        <?php } else { ?>
        <img class="twd_centered" height="200" width="200" src="uploads/unavailable.gif" />
        <?php } ?>
        </div>
  <p class="twd_centered twd_margin20">You have selected:</p>
  <h2 class="twd_centered twd_margin20"><?php echo $row_events['eventName']; ?></strong> on <strong><?php echo $formattedDate; ?></strong></h2>
  <p class="twd_centered twd_margin20" style="color:red;">You may cancel your reservation up to 24 hours in advance.  Reservations are non-refundable within 24 hours of the class start time. Please contact <?php echo $row_student['studioName']; ?> for more information about the cancellation policy. Thank you!</p>
<?php
//checkto be sure there are spots left in the class
if($totalRows_eventAttendance >= $row_events['eventCapacity']){
	echo '<p class="twd_centered" style="color:red;">Sorry, this event is full! Please <a href="student-events.php">return to the events page</a> to view other upcoming events.</p>';
} else {
?>
<!--handle unpaid reservations-->
<?php
if($row_student['allowUnpaidReservations']==1){
$addrecords = "INSERT INTO eventAttendance(eventID, dateAdded, studioID, studentID, amountPaid) VALUES (".$_GET['eventID'].",".$formattedDate.",".$row_student['studioID'].",".$row_student['studentID'].",0)";
mysql_select_db($database_wotg, $wotg);
mysql_query($addrecords, $wotg) or die(mysql_error());	
?>
<h2 class="twd_centered twd_margin20">Registration Complete</h2>
<p class="twd_centered twd_margin20"><strong>Thank you! Your reservation on <?php echo $formattedDate; ?> for <?php echo $row_events['eventName']; ?> is saved.</strong></p>
<p class="twd_centered twd_margin20"><a href="student-events.php">Return to the events page</a> to view other upcoming events.</p>
<?php
} else {
?>

<!-- dropins -->
<?php
setlocale(LC_MONETARY, 'en_US');
if($row_student['allowUnpaidReservations']==0){
?>
  <h2 class="twd_centered" style="padding-top:20px;border-top: #fff solid 2px;">Choose a Payment Method</h2>
  <h3 class="twd_centered twd_margin20"><?php echo $row_events['eventName']; ?>: <?php echo money_format('%.2n', $row_events['eventFee']); ?></h3>

<?php

//get order info
$orderDescription = 'Event Registration: '.$row_events['eventName'].' on '.$formattedDate;
$orderAmount = (int)$row_events['eventFee'];
$studentID = $row_student['studentID'];
$classID = $row_events['eventID'];
$dateAdded = $formattedDate;
$attendanceType = 'Event';
$studioID = $row_student['studioID'];

$payer = new Payer();
$payer->setPaymentMethod("paypal");

$item1 = new Item();
$item1->setName($orderDescription)
    ->setCurrency('USD')
    ->setQuantity(1)
    ->setPrice($orderAmount);
	
$itemList = new ItemList();
$itemList->setItems(array($item1));

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
$redirectUrls->setReturnUrl("$baseUrl/ExecutePayment-events.php?success=true")
    ->setCancelUrl("$baseUrl/ExecutePayment-events.php?success=false");

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
$firstName = str_replace("'","\\'", $row_student['firstName']);//escape single quote
$lastName = str_replace("'","\\'", $row_student['lastName']);//escape single quote

//add incomplete order to database
$wotg = mysql_pconnect($hostname_wotg, $username_wotg, $password_wotg) or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_select_db($database_wotg, $wotg);
$query_order = "INSERT INTO orders (amount, firstName, lastName, emailAddress, studioID, orderInfo, orderStatus, notes, paymentID, classDate, classID, studentID) VALUES (".$orderAmount.", '".$firstName."', '".$lastName."','".$row_student['emailAddress']."',".$studioID.",'Event','pending','".$orderDescription."','".$payment->id."','".$dateAdded."',".$classID.",".$studentID.")";
$order = mysql_query($query_order, $wotg) or die(mysql_error());
$id = mysql_insert_id();
 echo '<div class="twd_centered twd_margin20"><a href='.$approvalUrl.'><img  class="twd_centered" src="../images/paypal.png" width="142" height="27"></a></div>';

//return $payment;
?>
<p class="twd_centered twd_margin20"><em>Please Note: You don't need a PayPal account in order to pay with PayPal. Once you click on the PayPal button, you will see a grey button under the login area “pay with debit or credit card”. Click on that if you do not have a PayPal account and you want to pay with your debit or credit card.</em><p>
 
<?php } } } ?> 

</div>
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysql_free_result($student);
mysql_free_result($eventAttendance);
mysql_free_result($events);
?>