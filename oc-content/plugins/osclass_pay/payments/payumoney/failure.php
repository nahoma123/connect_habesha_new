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
    osc_add_flash_warning_message(__('Your transaction has been cancelled.', 'osclass_pay'));
  } 
  
  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray('post'));
    mail(osc_contact_email() , 'OSCLASS PAY - PAYUMONEY FAILURE DEBUG RESPONSE', $emailtext);
  }

  $data = osp_get_custom(Params::getParam('extra'));
  $product_type = explode('x', $data['product']);

  osp_js_redirect_to(osp_pay_url_redirect($product_type));
?>