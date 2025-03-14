<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';


  if(Params::getParam('status') == 'CANCELLED') {
    osc_add_flash_error_message(__('You cancel the payment process or there was an error. If the error continue, please contact the administrator', 'osclass_pay'));
    osp_js_redirect_to(osp_pay_url_redirect(array(OSP_TYPE_MULTIPLE, 1, 0)));

  } else if(Params::getParam('status') == 'EXPIRED') {
    osc_add_flash_error_message(__('Your order has exprired', 'osclass_pay'));
    osp_js_redirect_to(osp_pay_url_redirect(array(OSP_TYPE_MULTIPLE, 1, 0)));

  } else {    // SUCCESS

    $order_id = (Params::getParam('orderId') > 0 ? Params::getParam('orderId') : Params::getParam('pendingId'));

    $response = RevenuemonsterPayment::processPayment($order_id);
    $status = $response[0];
    $message = @$response[1];

    $tx = Params::getParam('revenuemonster_transaction_id');


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
      $emailtext = 'RETURN STEP 1';
      $emailtext .= "status => " . $status . "\r\n";
      $emailtext .= osp_array_to_string(Params::getParamsAsArray());
      error_log($emailtext);
      mail(osc_contact_email() , 'OSCLASS PAY - REVENUEMONSTER RETURN DEBUG RESPONSE', $emailtext);
    }

    $data = osp_get_custom(Params::getParam('extra'));
    $product_type = explode('x', @$data['product']);

    osp_js_redirect_to(osp_pay_url_redirect($product_type));
  }
?>