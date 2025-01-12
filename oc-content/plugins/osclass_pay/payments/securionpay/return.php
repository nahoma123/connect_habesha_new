<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $response = SecurionpayPayment::processPayment(Params::getParam('chargeId'));
  $status = $response[0];
  $message = @$response[1];

  $tx = Params::getParam('securionpay_transaction_id');

  if ($status == OSP_STATUS_COMPLETED) {
    osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), $tx));
  } else if ($status == OSP_STATUS_ALREADY_PAID) {
    osc_add_flash_warning_message(__('Warning! This payment was already paid.', 'osclass_pay'));
  } else if ($status == OSP_STATUS_AMOUNT_ZERO) {
    osc_add_flash_error_message(__('You are trying to pay zero amount.', 'osclass_pay'));
  } else if ($status == OSP_STATUS_INVALID) {
    osc_add_flash_error_message(__('Invalid payment.', 'osclass_pay') . ' (' . $message . ')');
  } else {
    osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay') . ' (' . $message . ')');
  }
  
  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= "message => " . $message . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray());
    mail(osc_contact_email() , 'OSCLASS PAY - SECURIONPAY RETURN DEBUG RESPONSE', $emailtext);
  }

  $data = osp_get_custom(Params::getParam('extra'));
  $product_type = explode('x', @$data['product']);

  osp_js_redirect_to(osp_pay_url_redirect($product_type));
?>