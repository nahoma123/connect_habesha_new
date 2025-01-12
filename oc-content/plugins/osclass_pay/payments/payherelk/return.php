<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= osp_array_to_string(Params::getParamsAsArray());
    mail(osc_contact_email() , 'OSCLASS PAY - PAYHERELK RETURN DEBUG RESPONSE', $emailtext);
  }

  osc_add_flash_ok_message(__('We are processing your payment!', 'osclass_pay'));
  osp_js_redirect_to(osp_pay_url_redirect(array('OSP_TYPE_MULTIPLE', 1)));
?>