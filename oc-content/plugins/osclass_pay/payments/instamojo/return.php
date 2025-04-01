<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $status = OSP_STATUS_FAILED;

  $data = osp_get_custom(Params::getParam('extra'));
  $product_type = explode('x', $data['p']);
  $tx = Params::getParam('payment_id');
  $request_id = Params::getParam('payment_request_id');

  $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'INSTAMOJO');
  $res = InstamojoPayment::processPayment();


  if (isset($payment['pk_i_id'])) {
    osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), $tx));
  } else {
    osc_add_flash_info_message(sprintf(__('We are processing your payment %s, if it did not finish in a few seconds, please contact us', 'osclass_pay'), $tx));
  }


  if(OSP_DEBUG && isset($res[1])) {
    $emailtext = osp_array_to_string($res[1]);
    mail(osc_contact_email() , 'OSCLASS PAY - INSTAMOJO DEBUG RESPONSE (RETURN)', $emailtext);
  }

  osp_js_redirect_to(osp_pay_url_redirect($product_type));
?>