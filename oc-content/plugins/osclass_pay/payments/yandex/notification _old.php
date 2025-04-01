<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $bStatus = false;
  $sSha1_hash = Params::getParam('sha1_hash'); 
    
  $sStr =  Params::getParam('notification_type').'&'
          .Params::getParam('operation_id').'&'
          .Params::getParam('amount').'&'
          .Params::getParam('currency').'&'
          .Params::getParam('datetime').'&'
          .Params::getParam('sender').'&'
          .Params::getParam('codepro').'&'
          .osp_decrypt(osp_param('yandex_api_secret')).'&'
          .Params::getParam('label');
    
  $sSha1 = sha1($sStr);

  if($sSha1_hash == $sSha1) {
    $bStatus = true;    
  }
   

  if($bStatus) {
    Params::setParam('transaction_id', Params::getParam('label'));   // transaction ID stored in label

    $response = YandexPayment::processPayment();
    $status = $response[0];
    $message = @$response[1];

    $tx = Params::getParam('yandex_transaction_id');

    if(OSP_DEBUG) {
      $emailtext = "status => " . $status . "\r\n";
      $emailtext .= "message => " . $message . "\r\n";
      $emailtext .= osp_array_to_string(Params::getParamsAsArray());
      mail(osc_contact_email() , 'OSCLASS PAY - YANDEX MONEY NOTIFICATION DEBUG RESPONSE', $emailtext);
    }
  }
?>