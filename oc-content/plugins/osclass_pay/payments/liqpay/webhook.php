<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $response = LiqpayPayment::processPayment();
  $status = @$response[0];
  $message = @$response[1];

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext = "message => " . $message . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray());
    $emailtext .= osp_array_to_string(json_decode(base64_decode(Params::getParam('data')), true));
    
    mail(osc_contact_email() , 'OSCLASS PAY - LIQPAY WEBHOOK DEBUG RESPONSE', $emailtext);
  }
?>