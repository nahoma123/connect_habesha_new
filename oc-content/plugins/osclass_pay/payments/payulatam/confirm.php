<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';
  
  $status = PayulatamPayment::processPayment();

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray('post'));
    mail(osc_contact_email() , 'OSCLASS PAY - PAYULATAM DEBUG RESPONSE (CONFIRM)', $emailtext);
  }
?>