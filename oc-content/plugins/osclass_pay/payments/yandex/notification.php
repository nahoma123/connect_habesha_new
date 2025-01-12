// Obtain data from a POST request made by Yandex.Checkout.

<?php
    $source = file_get_contents('php://input');
    $requestBody = json_decode($source, true);
?>

// Create a notification class object depending on the event
// NotificationSucceeded, NotificationWaitingForCapture,
// NotificationCanceled,  NotificationRefundSucceeded

<?php
    use YandexCheckout\Model\Notification\NotificationSucceeded;
    use YandexCheckout\Model\Notification\NotificationWaitingForCapture;
    use YandexCheckout\Model\NotificationEventType;

    try {
      $notification = ($requestBody['event'] === NotificationEventType::PAYMENT_SUCCEEDED)
        ? new NotificationSucceeded($requestBody)
        : new NotificationWaitingForCapture($requestBody);
    } catch (Exception $e) {
        // Processing errors
    }
?>

// Obtain the Payment object

<?php
  $payment = $notification->getObject();
  $transaction_id = $payment->id;

  Params::setParam('transaction_id', $transaction_id);   // transaction ID stored in label

  $response = YandexPayment::processPayment();
  $status = $response[0];
  $message = @$response[1];

  $tx = Params::getParam('yandex_transaction_id');

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= "message => " . $message . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray());
    $emailtext .= osp_array_to_string($payment);
    mail(osc_contact_email() , 'OSCLASS PAY - YANDEX MONEY (NOTIFICATION) DEBUG RESPONSE', $emailtext);
  }
?>