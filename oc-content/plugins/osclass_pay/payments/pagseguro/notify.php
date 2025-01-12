<?php 
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';
  require_once 'PagseguroPayment.php';

  $response = PagseguroPayment::processNotification(); 
  $status = $response[0];

  if(OSP_DEBUG) {
    $emailtext = "status => " . $status . "\r\n";
    $emailtext .= osp_array_to_string($_POST);
    $emailtext .= osp_array_to_string($_GET);
    if(isset($response[1])) {
      $emailtext .= "\r\n ---------- \r\n" . osp_array_to_string($response[1]);
    }
    mail(osc_contact_email() , 'OSCLASS PAY - PAGSEGURO DEBUG RESPONSE (NOTIFY)', $emailtext);
  }
?>