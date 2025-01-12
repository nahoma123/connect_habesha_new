<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $data = osp_get_custom(Params::getParam('custom'));
  $product_type = explode('x', $data['product']);

  osc_add_flash_info_message(__('You cancel the payment process or there was an error. If the error continue, please contact the administrator', 'osclass_pay'));
  osp_js_redirect_to(osp_pay_url_redirect($product_type));
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html" charset="iso-8859-1" />
    <title><?php echo osc_page_title(); ?></title>
  </head>

  <body><?php _e('Payment has been canceled', 'osclass_pay'); ?></body>
</html>