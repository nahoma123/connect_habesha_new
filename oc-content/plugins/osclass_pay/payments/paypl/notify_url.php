<?php
  //set include
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $sandbox = false; 

  if(osp_param('paypal_sandbox') == 1 ) {
    $sandbox = true;
  }

  // Read the post from PayPal and add 'cmd'
  $header = '';
  $req = 'cmd=_notify-validate';

  // if(function_exists('get_magic_quotes_gpc')) {
    // $get_magic_quotes_exists = true;
  // } else {
    // $get_magic_quotes_exists = false;
  // }
  
  foreach ($_POST as $key => $value) {
    // Handle escape characters, which depends on setting of magic quotes 
    // if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
      // $value = urlencode(stripslashes($value));
    // } else {
      // $value = urlencode($value);
    // }
    
    $value = urlencode($value);

    if($key != 'extra') {
      $req .= "&$key=$value";
    }
  }

  // Post back to PayPal to validate
  if(!$sandbox) {
    //$curl = curl_init('https://www.paypal.com/cgi-bin/webscr');
    $curl = curl_init('https://ipnpb.paypal.com/cgi-bin/webscr');
  } else {
    //$curl = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
    $curl = curl_init('https://ipnpb.sandbox.paypal.com/cgi-bin/webscr');
  }

  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $req);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_TIMEOUT, 30);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

  $res = curl_exec($curl);

  if (strcmp($res, 'VERIFIED') == 0) {
    PaypalPayment::processPayment();

    if(OSP_DEBUG) {
      $emailtext = osp_array_to_string($_REQUEST);
      mail(osc_contact_email() , 'OSCLASS PAY - PAYPAL DEBUG RESPONSE (NOTIFY)', $emailtext . '\n\n ---------------- \n\n' . $req);
    }
  } else if (strcmp($res, 'INVALID') == 0) {
    osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay'));
    osp_js_redirect_to(osp_pay_url_redirect());
  }
?>