<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';
  BeGatewayPayment::loadTranslations();

  $uid = Params::getParam('uid');
  $response = BeGatewayPayment::processPayment($uid);
  $status = $response[0];
  $message = @$response[1];

  if ($status == OSP_STATUS_ALREADY_PAID) {
    osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), $uid));
  } elseif ($status == OSP_STATUS_PENDING) {
    osc_add_flash_info_message(sprintf(__('We are processing your payment %s, if it did not finish in a few seconds, please contact us', 'osclass_pay'), $uid));
  } else {
    osc_add_flash_error_message(__('There was a problem processing your payment. Please contact the administrators', 'osclass_pay'));
  }

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray());
    mail(osc_contact_email() , 'OSCLASS PAY - BEGATEWAY RETURN DEBUG RESPONSE', $emailtext);
  }

  $data = osp_get_custom(Params::getParam('extra'));
  $product_type = explode('x', $data['product']);

  osp_js_redirect_to(osp_pay_url_redirect($product_type));
?>
