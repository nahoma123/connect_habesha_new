<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $status = OSP_STATUS_FAILED;

  $product_type = explode('x', Params::getParam('item_number'));
  $tx = Params::getParam('mb_transaction_id') <> '' ? Params::getParam('mb_transaction_id') : Params::getParam('transaction_id');

  $status = SkrillPayment::processPayment();

  if ($status == OSP_STATUS_COMPLETED) {
    osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), $tx));
  } else if ($status == OSP_STATUS_ALREADY_PAID) {
    osc_add_flash_warning_message(__('Warning! This payment was already paid.', 'osclass_pay'));
  } else if ($status == OSP_STATUS_AMOUNT_ZERO) {
    osc_add_flash_error_message(__('You are trying to pay zero amount.', 'osclass_pay'));
  } else {
    osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay') . ' (' . $status . ')');
  }

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray('post'));
    $emailtext .= osp_array_to_string(Params::getParamsAsArray('get'));
    mail(osc_contact_email() , 'OSCLASS PAY - SKRILL DEBUG RESPONSE (STATUS)', $emailtext);
  }

  //osp_js_redirect_to(osp_pay_url_redirect($product_type));
?>