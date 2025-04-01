<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $prepare_extra = Params::getParam('extra');
  $prepare_extra = str_replace('[-]', ' ', $prepare_extra);
  $prepare_extra = str_replace('[p]', '|', $prepare_extra);
  $prepare_extra = str_replace('[o]', ',', $prepare_extra);
  Params::setParam('extra-decoded', $prepare_extra);

  $response = BlockchainPayment::processPayment();


  if(OSP_DEBUG) {
    $emailtext = "status => " . $response[0] . "\r\n";
    $emailtext .= "error => " . @$response[1] . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray());
    mail(osc_contact_email() , 'OSCLASS PAY - BLOCKCHAIN DEBUG RESPONSE (CALLBACK)', $emailtext);
  }


  if($response[0] == OSP_STATUS_COMPLETED || $response[0] == OSP_STATUS_ALREADY_PAID) {
    ob_get_clean();
    echo '*ok*';
    exit;
  }
?>