<?php
// Create a notification class object depending on the event
// NotificationSucceeded, NotificationWaitingForCapture,NotificationEventType
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\Notification\NotificationWaitingForCapture;
use YooKassa\Model\NotificationEventType;


// Obtain data from a POST request made by Yoomoney.Checkout.
$source = file_get_contents('php://input');
$requestBody = json_decode($source, true);

try {
  $notification = ($requestBody['event'] === NotificationEventType::PAYOUT_SUCCEEDED) ? new NotificationSucceeded($requestBody) : new NotificationWaitingForCapture($requestBody);
} catch (Exception $e) {
  // Processing errors
}


// Obtain the Payment object
$payment = $notification->getObject();
$transaction_id = $payment->id;

Params::setParam('transaction_id', $transaction_id);   // transaction ID stored in label

$response = YookassaPayment::processPayment();
$status = $response[0];
$message = @$response[1];

$tx = Params::getParam('yoomoney_transaction_id');

if(OSP_DEBUG) {
  $emailtext = "status => " . $status . "\r\n";
  $emailtext .= "message => " . $message . "\r\n";
  $emailtext .= osp_array_to_string(Params::getParamsAsArray());
  $emailtext .= osp_array_to_string($payment);
  mail(osc_contact_email() , 'OSCLASS PAY - YOOKASSA (NOTIFICATION) DEBUG RESPONSE', $emailtext);
}
?>