<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $response = BtcpayserverPayment::processPayment();
  $status = @$response[0];
  $message = @$response[1];

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext = "message => " . $message . "\r\n";
    $emailtext .= osp_array_to_string(file_get_contents('php://input'));
    
    mail(osc_contact_email() , 'OSCLASS PAY - BTCPAYSERVER WEBHOOK DEBUG RESPONSE', $emailtext);
  }
  
  header("HTTP/1.1 200 OK");
?>