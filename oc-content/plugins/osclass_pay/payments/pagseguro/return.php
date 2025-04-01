<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib/PagSeguroLibrary.php';

  $transaction_id = (Params::getParam('pagseguro_transaction_id') <> '' ? Params::getParam('pagseguro_transaction_id') : Params::getParam('transaction_id'));

  //$credentials = PagSeguroConfig::getAccountCredentials();
  //$transaction = PagSeguroTransactionSearchService::searchByCode($credentials, $transaction_id);
  //print_r($transaction);


  $status = OSP_STATUS_FAILED;


  if($transaction_id <> '') {
    $response = PagseguroPayment::processPayment('0', $transaction_id);
    $status = $response[0];
    $message = @$response[1];

    if ($status == OSP_STATUS_ALREADY_PAID || $status == OSP_STATUS_COMPLETED) {
      osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), $transaction_id));
    } else if($status == OSP_STATUS_PENDING) {
      osc_add_flash_info_message(sprintf(__('We are processing your payment %s, if it did not finish in a few seconds, please contact us.', 'osclass_pay'), $transaction_id));

      //osp_cart_drop(osc_logged_user_id());  // clear user cart when payment pending?

    } else if ($status == OSP_STATUS_AMOUNT_ZERO) {
      osc_add_flash_error_message(__('You are trying to pay zero amount.', 'osclass_pay'));
    } else {
      osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay') . ' (' . $message . ')');
    }
  } else {
    osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay') . ' (' . __('Invalid or missing transaction ID', 'osclass_pay') . ')');
  }


  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= osp_array_to_string($_POST);
    $emailtext .= osp_array_to_string($_GET);
    if(isset($response[1])) {
      $emailtext .= "\r\n ---------- \r\n" . osp_array_to_string($response[1]);
    }
    mail(osc_contact_email() , 'OSCLASS PAY - PAGSEGURO DEBUG RESPONSE (RETURN)', $emailtext);
  }

  osp_js_redirect_to(osp_pay_url_redirect());
?>