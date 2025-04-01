<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';
  
  sleep(3);

  $response = json_decode(file_get_contents('php://input'), true);
  $data = array();
  
  if(isset($response['eventType']) && $response['eventType'] == 'PAYMENT_WEB_ONLINE') {
    $data = @$response['data'];
    $order_id = @$data['order']['id'];
    
    if($order_id > 0) {
      $response = RevenuemonsterPayment::processPayment($order_id, $data);
      $status = $response[0];
      $message = @$response[1];
    } else {
      $status = 999;
      $message = 'Order ID is missing or is invalid';
    }
  } else {
    $status = 998;
    $message = 'Invalid webhook data';
  }


  $tx = Params::getParam('revenuemonster_transaction_id');


  if(OSP_DEBUG) {
    $emailtext = 'WEBHOOK STEP 1';
    $emailtext .= "status => " . $status . "\r\n";
    $emailtext .= "message => " . $message . "\r\n";
    $emailtext .= "\r\n---------------------\r\n";
    $emailtext .= osp_array_to_string($response);
    $emailtext .= "\r\n---------------------\r\n";
    $emailtext .= osp_array_to_string($data);
    $emailtext .= "\r\n---------------------\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray());
    error_log($emailtext);
    mail(osc_contact_email() , 'OSCLASS PAY - REVENUEMONSTER WEBHOOK DEBUG RESPONSE', $emailtext);
  }
?>