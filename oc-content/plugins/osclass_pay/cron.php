<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $log = ModelOSP::newInstance()->purgeExpired();

  if(!empty($log)) {
    echo '<pre>';
    print_r($log);
    echo '</pre>';
  } else {
    //echo '*NO ACTION*';
  }
?>