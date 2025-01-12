<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $status = Params::getParam('status');
  $txnid = Params::getParam('txnid');
  $firstname = Params::getParam('firstname');
  $email = Params::getParam('email');
  $productinfo = Params::getParam('productinfo');
  $amount = Params::getParam('amount');
  $posted_hash = Params::getParam('hash');
  $key = Params::getParam('key');
  $extra = Params::getParam('udf1');

  $salt = osp_decrypt(osp_param('payumoney_salt'));

  //hashSequence = additionalCharges|salt|status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|a mount|txnid|key

  if(Params::getParam('additionalCharges') <> '') {
    $additionalCharges = Params::getParam('additionalCharges');

    $retHashSeq = $additionalCharges.'|'.$salt.'|'.$status.'||||||||||'.$extra.'|'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;
  } else {	  
    $retHashSeq = $salt.'|'.$status.'||||||||||'.$extra.'|'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;
  }

  $hash = hash("sha512", $retHashSeq);
  
  if ($hash != $posted_hash) {
    osc_add_flash_error_message(__('Invalid Transaction. Please try again.', 'osclass_pay'));
  } else {
    $status = PayumoneyPayment::processPayment();

    if ($status == OSP_STATUS_COMPLETED) {
      osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), $txnid));
    } else if ($status == OSP_STATUS_ALREADY_PAID) {
      osc_add_flash_warning_message(__('Warning! This payment was already paid.', 'osclass_pay'));
    } else if ($status == OSP_STATUS_AMOUNT_ZERO) {
      osc_add_flash_error_message(__('You are trying to pay zero amount.', 'osclass_pay'));
    } else {
      osc_add_flash_error_message(sprintf(__('There were an error processing your payment (%s).', 'osclass_pay'), Params::getParam('error') . ' - ' . Params::getParam('error_Message')));
    }
  } 

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray('post'));
    mail(osc_contact_email() , 'OSCLASS PAY - PAYUMONEY SUCCESS DEBUG RESPONSE', $emailtext);
  }


  $data = osp_get_custom(Params::getParam('extra'));
  
  if(isset($data['product'])) {
    $product_type = explode('x', $data['product']);
    osp_js_redirect_to(osp_pay_url_redirect($product_type));
  } else {
    osp_js_redirect_to(osc_route_url('osp-cart'));
  }
?>