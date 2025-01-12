<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $response = PayherelkPayment::processPayment();
  $status = $response[0];
  $message = @$response[1];

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext = "message => " . $message . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray());
    mail(osc_contact_email() , 'OSCLASS PAY - PAYHERELK NOTIFY DEBUG RESPONSE', $emailtext);
  }
?>