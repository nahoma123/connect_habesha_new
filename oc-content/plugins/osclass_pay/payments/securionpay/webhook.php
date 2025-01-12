<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $data = json_decode(file_get_contents('php://input'), true);
  
  if(isset($data['data']) && isset($data['data']['id']) && strpos($data['data']['id'], 'char_') === 0) {
    $charge_id = $data['data']['id'];
    
    $response = SecurionpayPayment::processPayment($charge_id);
    $status = $response[0];
    $message = @$response[1];
  } else {
    $charge_id = '';  // fail
    
    $status = 999;
    $message = 'Charge ID is missing';
  }



  $tx = Params::getParam('securionpay_transaction_id');


  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= "message => " . $message . "\r\n";
    $emailtext .= osp_array_to_string($data);
    mail(osc_contact_email() , 'OSCLASS PAY - SECURIONPAY WEBHOOK DEBUG RESPONSE', $emailtext);
  }
?>