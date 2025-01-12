<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';


  $data = $_POST;

  $mac_provided = $data['mac'];
  unset($data['mac']); 
  $ver = explode('.', phpversion());
  $major = (int) $ver[0];
  $minor = (int) $ver[1];

  if($major >= 5 and $minor >= 4){
    ksort($data, SORT_STRING | SORT_FLAG_CASE);
  } else{
    uksort($data, 'strcasecmp');
  }


  $mac_calculated = hash_hmac("sha1", implode("|", $data), osp_decrypt(osp_param('instamojo_salt')));
  if($mac_provided == $mac_calculated){

    // check return data here: https://support.instamojo.com/hc/en-us/articles/208485755-Webhook-URL
    // Payment was successful, mark it as successful in your database.
    if($data['status'] == "Credit"){
      $status = OSP_STATUS_FAILED;

      $custom_data = osp_get_custom($data['purpose']);
      $product_type = explode('x', $custom_data['p']);

      $tx = $data['payment_id'];
      $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'INSTAMOJO');
 
      // Process payment here
      if(!$payment) {

        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          sprintf(__('Pay cart items for %s', 'osclass_pay'), osp_format_price($data['amount'], 2)), //concept
          $data['payment_id'], // transaction code
          $data['amount'], //amount
          strtoupper($data['currency']), //currency
          $data['buyer'], // payer's email
          $custom_data['u'], //user
          osp_create_cart_string($product_type[1], $custom_data['u'], $product_type[2]), // cart string
          $product_type[0], //product type
          'INSTAMOJO' //source
        );


        $payment_details = osp_prepare_payment_data($data['amount'], $payment_id, $custom_data['u'], $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);

        $status = OSP_STATUS_COMPLETED;

      } else {
        $status = OSP_STATUS_ALREADY_PAID;
      }


      if(OSP_DEBUG) {
        $emailtext = "status => " . $status . "\r\n";
        $emailtext .= osp_array_to_string($data);
        mail(osc_contact_email() , 'OSCLASS PAY - INSTAMOJO DEBUG RESPONSE (WEBHOOK)', $emailtext);
      }


    // Payment was unsuccessful, mark it as failed in your database.
    } else {
      // Do nothing for now
    }
  } else{
    echo "MAC mismatch";
  }
?>