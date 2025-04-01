<?php
if(!defined('ABS_PATH')) {
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
}

require_once ABS_PATH . 'oc-load.php';

$orders = KomfortkassePayment::readOrders();

//print_r($orders);

if(is_array($orders) && count($orders) > 0) {
  foreach($orders as $o) {
    // Known statues: UNPAID, PAID, CANCELLED
    if($o !== false && isset($o['paymentStatus']) && $o['paymentStatus'] <> 'UNPAID') {
      $status = ($o['paymentStatus'] == 'PAID' ? 'PAID' : 'FAILED');

      KomfortkassePayment::processPayment($o['number'], $status);
    }
  }
}
?>