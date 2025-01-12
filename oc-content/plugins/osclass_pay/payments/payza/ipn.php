<?php
define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
require_once ABS_PATH . 'oc-load.php';


//The value is the url address of IPN V2 handler and the identifier of the token string
define("IPN_V2_HANDLER", "https://secure.payza.com/ipn2.ashx");
define("TOKEN_IDENTIFIER", "token=");

// get the token from Payza
$token = urlencode($_POST['token']);

//preappend the identifier string "token="
$token = TOKEN_IDENTIFIER.$token;

/**
 *
 * Sends the URL encoded TOKEN string to the Payza's IPN handler
 * using cURL and retrieves the response.
 *
 * variable $response holds the response string from the Payza's IPN V2.
 */

$response = '';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, IPN_V2_HANDLER);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

curl_close($ch);

if(strlen($response) > 0) {
  if(urldecode($response) == "INVALID TOKEN") {
    osc_add_flash_error_message(__('There were an error processing your payment (Invalid token).', 'osclass_pay'));
    osp_js_redirect_to(osp_pay_url_redirect());
  } else {
    $response = urldecode($response);
    $aps = explode("&", $response);
    $info = array();
    foreach ($aps as $ap) {
      $ele = explode("=", $ap);
      $info[$ele[0]] = $ele[1];
    }

    //fclose($fh);

    // $info CONTAINS: ap_merchant, ap_status, ap_test, ap_purchasetype, ap_totalamount, ap_feeamount, ap_netamount, ap_referencenumber, ap_currency, ap_transactiondate, ap_transactiontype
    // ap_itemname, ap_itemcode, ap_description, ap_amount, ap_taxamount, apc_1 - apc_6


    $info['custom'] = $info['apc_1'].$info['apc_2'].$info['apc_3'].$info['apc_4'].$info['apc_5'].$info['apc_6'];

    if(isset($info['ap_test']) && $info['ap_test'] == 1) {
      $info['ap_referencenumber'] = 'TESTPAYZAREF_' . rand(0,10000);
    }

    $data = osp_get_custom($info['custom']);

    $product_type = explode('x', $info['ap_itemcode']);
    $tx = $info['ap_referencenumber'];

    $status = PayzaPayment::processPayment($info);
    $info['status'] = $status;

    $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'PAYZA');
    
    if (isset($payment['pk_i_id'])) {
      osc_add_flash_ok_message(__('Payment processed correctly', 'osclass_pay'));
    } else {
      osc_add_flash_info_message(__('We are processing your payment, if we did not finish in a few seconds, please contact us', 'osclass_pay'));
    }


    if(OSP_DEBUG && isset($data)) {
      $emailtext = "status => " . $status . "\r\n";
      $emailtext .= osp_array_to_string($info);
      mail(osc_contact_email() , 'OSCLASS PAY - PAYZA DEBUG RESPONSE (IPN)', $emailtext);
    }


    osp_js_redirect_to(osp_pay_url_redirect($product_type));
  }
} else {
  //something is wrong, no response is received from Payza
  osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay'));
  osp_js_redirect_to(osp_pay_url_redirect());
}


if(OSP_DEBUG) {
  $emailtext = osp_array_to_string($info);
  mail(osc_contact_email() , 'OSCLASS PAY - PAYZA DEBUG RESPONSE (IPN)', $emailtext);
}
?>