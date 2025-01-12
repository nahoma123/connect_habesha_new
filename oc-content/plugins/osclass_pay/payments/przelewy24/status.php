<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $response = Przelewy24Payment::processPayment();
  $status = $response[0];
  $message = @$response[1];

  //$tx = Params::getParam('przelewy24_transaction_id');
  $tx = Params::getParam('przelewy24_session_id');

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= "message => " . $message . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray());
    mail(osc_contact_email() , 'OSCLASS PAY - PRZELEWY24 STATUS DEBUG RESPONSE', $emailtext);
  }

  $data = osp_get_custom(Params::getParam('extra'));
  $product_type = explode('x', $data['product']);

  osp_js_redirect_to(osp_pay_url_redirect($product_type));
?>