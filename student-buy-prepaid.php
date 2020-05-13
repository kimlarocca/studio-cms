<?php
$today = date("Y-m-d", strtotime("now"));
if (!isset($_SESSION)) {
  session_start();
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
}
?>
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

$colname_package = "-1";
if (isset($_GET['packageID'])) {
  $colname_package = $_GET['packageID'];
}
mysql_select_db($database_wotg, $wotg);
$query_package = sprintf("SELECT * FROM packages WHERE packageID = %s", GetSQLValueString($colname_package, "int"));
$package = mysql_query($query_package, $wotg) or die(mysql_error());
$row_package = mysql_fetch_assoc($package);
$totalRows_package = mysql_num_rows($package);

ini_set('session.save_path',getcwd(). '/../tmp/');
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
<h1 class="studentH1 twd_centered"><?php echo $row_student['studioName']; ?> | Buy Packages</h1>
<?php 
//get logo if it exists
if($row_student['logoURL']!=''){ 
?>
<div class="twd_centered twd_margin20" style="padding-top:20px; clear:both"><img src="uploads/<?php echo $row_student['logoURL']; ?>" /></div>
<?php 
}
?>
<div class="twd_container">

  <h2 class="twd_centered">Choose a Payment Method</h2>
<h3 class="twd_centered twd_margin20"><?php echo $row_package['packageName']; ?>: $<?php echo (int)$row_package['packageCost']; ?></h3>
<!--
<div style="text-align:center"><p><span style="color:#990000">Checkout with Paydunk and get an additional <strong>$5 off all class packages</strong> (for a limited time)!</span><br />
  Paydunk - the Safer, Faster Way to Checkout Online - is sponsoring a promotion for Wellness On The Green students.  All you have to do is click the paydunk button below to get started. <a target="_blank" href="http://www.paydunk.com">Click here to learn more about Paydunk.</a></p><div id="paydunkButton"></div></div>
  -->
<?php

//get order info
$orderDescription = 'Prepaid Package: '.$row_package['packageName'].' for $'.$row_package['packageCost'];
$orderAmount = (int)$row_package['packageCost'];
$studentID = $row_student['studentID'];
$dateAdded = $today;
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

$firstName = str_replace("'","\\'", $row_student['firstName']);//escape single quote
$lastName = str_replace("'","\\'", $row_student['lastName']);//escape single quote

//add incomplete order to database
$wotg = mysql_pconnect($hostname_wotg, $username_wotg, $password_wotg) or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_select_db($database_wotg, $wotg);
$query_order = "INSERT INTO orders (amount, firstName, lastName, emailAddress, studioID, orderInfo, orderStatus, notes, paymentID, studentID, classesAdded) VALUES (".$orderAmount.", '".$firstName."', '".$lastName."','".$row_student['emailAddress']."',".$studioID.",'Pre Paid Package','pending','".$orderDescription."','".$payment->id."',".$studentID.",".$row_package['numberOfSessions'].")";
$order = mysql_query($query_order, $wotg) or die(mysql_error());
$id = mysql_insert_id();
 echo '<div class="twd_centered"><a href='.$approvalUrl.'><img src="../images/paypal.png" width="142" height="27"></a></div>';

//return $payment;
?>	
</div>
<?php include("footer.php"); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="jquery.paydunk.min.js"></script>
<script>
$('#paydunkButton').paydunk({
    appID        : 'KXQl371hm0g9tWCTu48RIYpfys3UObvCnl6j9QeN', //your App ID goes here - required!!
    price        : <?php echo $orderAmount-5; ?>, //required!!
    order_number : <?php echo $id; ?>,
    tax          : 0,
    shipping     : 0
});
$('#paydunkButton form').css('visibility','hidden');
</script>
</body>
</html>
<?php
mysql_free_result($student);

mysql_free_result($package);
?>