<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  osc_add_flash_ok_message(__('Thank you, we are processing your payment. Once it is confirmed, your order will be completed.', 'osclass_pay'));
 
  if(OSP_DEBUG) {
    $emailtext = osp_array_to_string(Params::getParamsAsArray());
    mail(osc_contact_email() , 'OSCLASS PAY - PRZELEWY24 RETURN DEBUG RESPONSE', $emailtext);
  }

  osp_js_redirect_to(osp_pay_url_redirect(osc_user_dashboard_url()));
?>