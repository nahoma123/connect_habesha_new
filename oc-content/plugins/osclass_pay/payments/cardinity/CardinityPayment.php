<?php
// https://developers.cardinity.com/api/v1/?php#checkout-http-request

class CardinityPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    $r = rand(0,1000);

    $project_id = osp_param('cardinity_project_id');
    $project_secret = osp_decrypt(osp_param('cardinity_project_secret'));

    
    $pending_data = array(
      'fk_i_user_id' => osc_logged_user_id(),
      's_email' => osc_logged_user_email(),
      's_extra' => $extra,
      's_source' => 'CARDINITY',
      'dt_date' => date('Y-m-d h:i:s')
    );

    $order_id = ModelOSP::newInstance()->insertPending($pending_data);
    $order_id = 'ospcardinity' . $order_id;
    
    $ENDPOINT = 'https://checkout.cardinity.com';
    $RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php';
    $CANCELURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php?orderId=' . $order_id;
    
    $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
    
    
    $attributes = [
      "amount" => number_format($amount, 2),
      "currency" => osp_currency(),
      "country" => (@$user['fk_c_country_code'] <> '' ? $user['fk_c_country_code'] : 'US'),
      "language" => 'EN',
      "order_id" => $order_id,
      "description" => osc_esc_html($description),
      "project_id" => $project_id,
      "cancel_url" => $CANCELURL,
      "return_url" => $RETURNURL,
    ];

    ksort($attributes);

    $message = '';
    foreach($attributes as $key => $value) {
      $message .= $key.$value;
    }

    $signature = hash_hmac('sha256', $message, $project_secret);
    ?>


    <li class="payment cardinity-btn">
      <form name="checkout" method="POST" action="<?php echo $ENDPOINT; ?>" class="nocsrf" target="_self" id="cardinity_form">
        <input type="hidden" name="amount" value="<?php echo $attributes['amount']; ?>" />
        <input type="hidden" name="cancel_url" value="<?php echo $attributes['cancel_url']; ?>" />
        <input type="hidden" name="country" value="<?php echo $attributes['country']; ?>" />
        <input type="hidden" name="currency" value="<?php echo $attributes['currency']; ?>" />
        <input type="hidden" name="description" value="<?php echo $attributes['description']; ?>" />
        <input type="hidden" name="order_id" value="<?php echo $attributes['order_id']; ?>" />
        <input type="hidden" name="project_id" value="<?php echo $attributes['project_id']; ?>" />
        <input type="hidden" name="return_url" value="<?php echo $attributes['return_url']; ?>" />
        <input type="hidden" name="signature" value="<?php echo $signature; ?>" />
      </form>
    
      <a id="osp-button-confirm" class="button osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to Cardinity', 'osclass_pay')); ?>" onclick="$('#cardinity_form').submit();">
        <span><img src='<?php echo osp_url(); ?>img/payments/cardinity.png'/></span>
        <strong><?php _e('Pay with Cardinity', 'osclass_pay'); ?></strong>
      </a>
    </li>
  <?php
  }


  public static function processPayment() {
    $project_id = osp_param('cardinity_project_id');
    $project_secret = osp_decrypt(osp_param('cardinity_project_secret'));
    
    $message = '';
    ksort($_POST);

    foreach($_POST as $key => $value) {
      if ($key == 'signature') continue;
      $message .= $key.$value;
    }

    $signature = hash_hmac('sha256', $message, $project_secret);

 
    // it is valid response
    if ($signature == $_POST['signature']) {
      $cardinity_id = Params::getParam('id');
      $order_id = str_replace('ospcardinity', '', Params::getParam('order_id'));
      
      $status = Params::getParam('status');      //pending, approved, declined.
      
      Params::setParam('extra', $extra);
      Params::setParam('cardinity_transaction_id', $cardinity_id);

      $pending = ModelOSP::newInstance()->getPendingById($order_id);
      
      $extra = $pending['s_extra'];     // get pending row
      $data = osp_get_custom($extra);
      $product_type = explode('x', $data['product']);
      $amount = Params::getParam('amount'); 

      if($amount <= 0) { 
        return array(OSP_STATUS_AMOUNT_ZERO, ''); 
      }
      
      if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
        return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
      }
      
      if(!$pending) {
        $pending = ModelOSP::newInstance()->getPendingByTransactionId($cardinity_id, 'CARDINITY');
      }
    
      if($status != 'declined') {
        $payment = ModelOSP::newInstance()->getPaymentByCode($cardinity_id, 'CARDINITY');

        if(!$payment) { 
          // SAVE TRANSACTION LOG
          $payment_id = ModelOSP::newInstance()->saveLog(
            $data['concept'], //concept
            $cardinity_id, // transaction code
            $amount, //amount
            strtoupper(Params::getParam('currency')), //currency
            $data['email'], // payer's email
            $data['user'], //user
            osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
            $product_type[0], //product type
            'CARDINITY' //source
          );


          // Pay it!
          $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
          $pay_item = osp_pay_fee($payment_details);


          // Remove pending row
          ModelOSP::newInstance()->deletePending($pending['pk_i_id']);


          return array(OSP_STATUS_COMPLETED, '');
        }
        
        return array(OSP_STATUS_ALREADY_PAID, ''); 
      }

      return array(OSP_STATUS_FAILED, __('Payment failed', 'osclass_pay') . ': ' . Params::getParam('error'));

    } 
    
    return array(OSP_STATUS_FAILED, __('Response validation has failed', 'osclass_pay'));
  }  
}
?>