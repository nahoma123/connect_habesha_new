<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $response = CcavenuePayment::processPayment();
  $status = $response[0];
  $data = @$response[1];

  $tx = Params::getParam('ccavenue_transaction_id');


  if ($status == OSP_STATUS_COMPLETED) {
    osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), $tx));
  } else if ($status == OSP_STATUS_ALREADY_PAID) {
    osc_add_flash_warning_message(__('Warning! This payment was already paid.', 'osclass_pay'));
  } else if ($status == OSP_STATUS_AMOUNT_ZERO) {
    osc_add_flash_error_message(__('You are trying to pay zero amount.', 'osclass_pay'));
  } else if ($status == OSP_STATUS_INVALID) {
    osc_add_flash_error_message(__('Invalid payment.', 'osclass_pay') . ' (' . $data['response_code'] . ' - ' . $data['failure_message'] . ')');
  } else {
    osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay') . ' (' . $data['response_code'] . ' - ' . $data['failure_message'] . ')');
  }

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= osp_array_to_string($data);
    mail(osc_contact_email() , 'OSCLASS PAY - CCAVENUE DEBUG RESPONSE', $emailtext);
  }

  osp_js_redirect_to(osp_pay_url_redirect($product_type));
?>