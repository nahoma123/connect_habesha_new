<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $data = osp_get_custom(Params::getParam('extra'));
  $product_type = explode('x', $data['product']);

  $status = KomfortkassePayment::preparePayment();

  $transaction_id = $status['transaction_id'];
  $error = $status['error'];
  
  if($error <> '') {
    osc_add_flash_error_message(sprintf(__('There was error while creating order: %s', 'osclass_pay'), $error));
  } else {
    osc_add_flash_info_message(sprintf(__('Your order has been created, you will receive email from Komfortkasse.eu with details about payment instructions. Your transaction ID is: %s', 'osclass_pay'), $transaction_id));
  }

  osp_js_redirect_to(osp_pay_url_redirect($product_type));
?>