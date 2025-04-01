<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  //print_r(Params::getParamsAsArray());
  //Array ( [orderId] => 941 
  //[json] => {"amount":"29.0","currency":"EUR","customer_name":"Mago Wago","merchant_data":"941","message_time":"2024-08-14T10:27:30+0000",
  //           "message_type":"payment_return","reference":"901x1x5","shop":"9bd6f4d5-1373-4f1e-b83e-6751c6a64888",
  //           "signature":"B2AF36E450BCB191877795F3F003C8C2142024783C9A377C9E07DE48EBA66DC40216CA53170F9C1E02072343CFBC65AAD62B8E42ACF1C3F7FE14C675ED853627",
  //           "status":"COMPLETED","transaction":"1a45c564-8c13-486a-bcf9-4db853905047"} 
  //[mac] => E39E15843A0A38AEFD159F28716EF66673E6CD414F139FCB764EBAA76809010604FA9A7F63230DC82300E2498278945735C847A28D44A9F0A4EF6341156E5119 )
  
  // UPPERCASE(HEX(SHA-512(string(JSON) + string(Secret Key))))  === mac


  // $session->verifySignature($request);


  
  $response = MakeCommercePayment::processPayment();
  $status = $response[0];
  $message = @$response[1];


  $tx = Params::getParam('makecommerce_transaction_id');


  if ($status == OSP_STATUS_COMPLETED) {
    osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), $tx));
  } else if ($status == OSP_STATUS_ALREADY_PAID) {
    osc_add_flash_warning_message(__('Warning! This payment was already paid.', 'osclass_pay'));
  } else if ($status == OSP_STATUS_AMOUNT_ZERO) {
    osc_add_flash_error_message(__('You are trying to pay zero amount.', 'osclass_pay'));
  } else if ($status == OSP_STATUS_INVALID) {
    osc_add_flash_error_message(__('Invalid payment.', 'osclass_pay') . ' (' . $message . ')');
  } else if ($status == OSP_STATUS_PENDING) {
    osc_add_flash_ok_message(sprintf(__('We are processing your order. Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), $tx));
  } else {
    osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay') . ' (' . $message . ')');
  }

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray());
    mail(osc_contact_email() , 'OSCLASS PAY - MAKECOMMERCE RETURN DEBUG RESPONSE', $emailtext);
  }

  $json = @json_decode(Params::getParam('json'), true);
  $order_id = (isset($json['reference']) ? (int)$json['reference'] : 0);
  $product_type = NULL;
  
  if($order_id > 0) {
    $pending = ModelOSP::newInstance()->getPendingById($order_id);
    $data = osp_get_custom($pending['s_extra']);
    $product_type = explode('x', $data['product']);
  }


  osp_js_redirect_to(osp_pay_url_redirect($product_type));
?>