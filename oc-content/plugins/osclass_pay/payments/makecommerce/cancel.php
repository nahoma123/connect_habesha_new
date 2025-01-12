<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  if(OSP_DEBUG) {
    $emailtext = osp_array_to_string(Params::getParamsAsArray());
    mail(osc_contact_email() , 'OSCLASS PAY - MAKECOMMERCE CANCEL DEBUG RESPONSE', $emailtext);
  }

  $json = @json_decode(Params::getParam('json'), true);
  $order_id = (isset($json['reference']) ? (int)$json['reference'] : 0);
  $product_type = NULL;
  
  if($order_id > 0) {
    $pending = ModelOSP::newInstance()->getPendingById($order_id);
    $data = osp_get_custom($pending['s_extra']);
    $product_type = explode('x', $data['product']);
  }

  osc_add_flash_info_message(__('Payment has been canceled', 'osclass_pay'));
  osp_js_redirect_to(osp_pay_url_redirect($product_type));
?>