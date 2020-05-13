<?php require_once('Connections/wotg.php'); ?>
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
ob_start();
if($_GET['paymentId']=='') header("Location: student-home.php?action=error");
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

mysql_select_db($database_wotg, $wotg);
$query_orderInfo = "SELECT * FROM orders WHERE orders.paymentID = '".$_GET['paymentId']."'";
$orderInfo = mysql_query($query_orderInfo, $wotg) or die(mysql_error());
$row_orderInfo = mysql_fetch_assoc($orderInfo);
$totalRows_orderInfo = mysql_num_rows($orderInfo);

if($row_orderInfo['classID']!=''){
mysql_select_db($database_wotg, $wotg);
$query_orderInfo = "SELECT * FROM orders,events WHERE orders.classID=events.eventID AND orders.paymentID = '".$_GET['paymentId']."'";
$orderInfo = mysql_query($query_orderInfo, $wotg) or die(mysql_error());
$row_orderInfo = mysql_fetch_assoc($orderInfo);
$totalRows_orderInfo = mysql_num_rows($orderInfo);
}

mysql_select_db($database_wotg, $wotg);
$query_student = "SELECT * FROM students WHERE studentID = ".$row_orderInfo['studentID'];
$student = mysql_query($query_student, $wotg) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);
?>
<?php
// #Execute Payment Sample
// This is the second step required to complete
// PayPal checkout. Once user completes the payment, paypal
// redirects the browser to "redirectUrl" provided in the request.
// This sample will show you how to execute the payment
// that has been approved by
// the buyer by logging into paypal site.
// You can optionally update transaction
// information by passing in one or more transactions.
// API used: POST '/v1/payments/payment/<payment-id>/execute'.

require __DIR__ . '/PayPal/paypal/rest-api-sdk-php/sample/bootstrap.php';
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\ExecutePayment;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;

//overwrite bootstrap ClientID & Secret
$clientId = $row_currentUser['paymentGatewayID'];
$clientSecret = $row_currentUser['paymentGatewayKey'];

//set bootstrap sandbox mode if necessary
if($row_currentUser['enableSandbox']==1){
    $apiContext->setConfig(
        array(
            'mode' => 'sandbox',
            'log.LogEnabled' => true,
            'log.FileName' => '../PayPal.log',
            'log.LogLevel' => 'FINE', // PLEASE USE `FINE` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS (changed from 'DEBUG')
            'validation.level' => 'log',
            'cache.enabled' => true,
            // 'http.CURLOPT_CONNECTTIMEOUT' => 30
            // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
        )
    );
}

// ### Approval Status
// Determine if the user approved the payment or not
if (isset($_GET['success']) && $_GET['success'] == 'true') {

    // Get the payment Object by passing paymentId
    // payment id was previously stored in session in
    // CreatePaymentUsingPayPal.php
    $paymentId = $_GET['paymentId'];
    $payment = Payment::get($paymentId, $apiContext);

    // ### Payment Execute
    // PaymentExecution object includes information necessary
    // to execute a PayPal account payment.
    // The payer_id is added to the request query parameters
    // when the user is redirected from paypal back to your site
    $execution = new PaymentExecution();
    $execution->setPayerId($_GET['PayerID']);

    // ### Optional Changes to Amount
    // If you wish to update the amount that you wish to charge the customer,
    // based on the shipping address or any other reason, you could
    // do that by passing the transaction object with just `amount` field in it.
    // Here is the example on how we changed the shipping to $1 more than before.
    $transaction = new Transaction();
    $amount = new Amount();
    $details = new Details();

    $details->setShipping(0)
        ->setTax(0)
        ->setSubtotal((int)$row_orderInfo['amount']);

    $amount->setCurrency('USD');
    $amount->setTotal((int)$row_orderInfo['amount']);
    $amount->setDetails($details);
    $transaction->setAmount($amount);

    // Add the above transaction object inside our Execution object.
    $execution->addTransaction($transaction);

    try {
        // Execute the payment
        // (See bootstrap.php for more on `ApiContext`)
        $result = $payment->execute($execution, $apiContext);

        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
        //ResultPrinter::printResult("Executed Payment", "Payment", $payment->getId(), $execution, $result);

        try {
            $payment = Payment::get($paymentId, $apiContext);
			

        } catch (Exception $ex) {
            // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	        ResultPrinter::printError("Get Payment", "Payment", null, null, $ex);
            exit(1);
	header("Location: student-home.php?action=error");
        }
    } catch (Exception $ex) {
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	    ResultPrinter::printError("Executed Payment", "Payment", null, null, $ex);
        exit(1);
	header("Location: student-home.php?action=error");
    }

    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    //ResultPrinter::printResult("Get Payment", "Payment", $payment->getId(), null, $payment);

    //return $payment;


} else {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printResult("User Cancelled the Approval", null);
    exit;
	
	header("Location: student-home.php?action=error");
}

if ($_GET['success']=='true'){
		
	//update order database
	$wotg = mysql_pconnect($hostname_wotg, $username_wotg, $password_wotg) or trigger_error(mysql_error(),E_USER_ERROR); 
	mysql_select_db($database_wotg, $wotg);
	$query_order = "UPDATE orders SET orderStatus = 'complete', paymentType = 'PayPal' WHERE paymentID='".$_GET['paymentId']."'"; //kim changed 11.28.15
	$order = mysql_query($query_order, $wotg) or die(mysql_error());
	
	//update eventsattendance records
	$addrecords = "INSERT INTO eventAttendance(eventID, dateAdded, studioID, studentID, amountPaid, paymentType) VALUES (".$row_orderInfo['classID'].",".$row_orderInfo['classDate'].",".$row_orderInfo['studioID'].",'".$row_orderInfo['studentID']."',".$row_orderInfo['amount'].",'PayPal')";
	mysql_select_db($database_wotg, $wotg);
	mysql_query($addrecords, $wotg) or die(mysql_error());	
	
	header("Location: student-home.php?action=eventreserved");
}
else {
//update order database
$wotg = mysql_pconnect($hostname_wotg, $username_wotg, $password_wotg) or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_select_db($database_wotg, $wotg);
$query_order = "UPDATE orders SET orderStatus = 'cancelled' WHERE paymentID='".$_GET['paymentId']."'";
$order = mysql_query($query_order, $wotg) or die(mysql_error());
	header("Location: student-home.php?action=error");	
}

mysql_free_result($orderInfo);

mysql_free_result($student);
?>
