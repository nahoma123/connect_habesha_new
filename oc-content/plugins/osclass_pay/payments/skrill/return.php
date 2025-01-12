<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $data = osp_get_custom(Params::getParam('extra'));
  $product_type = explode('x', $data['product']);

  osc_add_flash_info_message(__('We are processing your payment, if we did not finish in a few seconds, please contact us', 'osclass_pay'));
  osp_js_redirect_to(osp_pay_url_redirect($product_type));
?>