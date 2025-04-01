<?php
define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
require_once ABS_PATH . 'oc-load.php';

$webhook = new \BeGateway\Webhook;

if(OSP_DEBUG) {
  $emailtext .= osp_array_to_string($webhook->getResponseArray());
  mail(osc_contact_email() , 'BEGATEWAY PAY - BEGATEWAY DEBUG RESPONSE (WEBHOOK)', $emailtext);
}

$status = BeGatewayPayment::processNotification($webhook);

die(implode('|', $status));
?>
