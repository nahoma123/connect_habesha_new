<?php
class PayherelkPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber;

    $pending_data = array(
      'fk_i_user_id' => osc_logged_user_id(),
      's_email' => osc_logged_user_email(),
      's_extra' => $extra,
      's_source' => 'PAYHERELK',
      'dt_date' => date('Y-m-d h:i:s')
    );

    $order_id = ModelOSP::newInstance()->insertPending($pending_data);

    if(osp_param('payherelk_sandbox') == 1) {
      $ENDPOINT   = 'https://sandbox.payhere.lk/pay/checkout';
    } else {
      $ENDPOINT   = 'https://www.payhere.lk/pay/checkout';
    }
      
    $RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php';
    $NOTIFYURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'notify.php';
    $CANCELURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php';
    
    
    $email = osc_logged_user_email(); 
    $merchant_id = osp_param('payherelk_merchant_id');
    $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
    $secret = osp_decrypt(osp_param('payherelk_secret'));
    
    $amount = round($amount, 2);
    $hash = strtoupper(md5($merchant_id . $order_id . number_format($amount, 2, '.', '') . osp_currency() . strtoupper(md5($secret))));
    //$hash_to_check = strtoupper(md5(Params::getParam('merchant_id') . Params::getParam('order_id') . Params::getParam('payhere_amount') . Params::getParam('payhere_currency') . Params::getParam('status_code') . strtoupper(md5($secret))));

    ?>

    <li>
      <form method="post" action="<?php echo $ENDPOINT; ?>" id="payherelk_payment_form" class="nocsrf">   
        <input type="hidden" name="merchant_id" value="<?php echo $merchant_id; ?>">
        <input type="hidden" name="return_url" value="<?php echo $RETURNURL; ?>">
        <input type="hidden" name="cancel_url" value="<?php echo $CANCELURL; ?>">
        <input type="hidden" name="notify_url" value="<?php echo $NOTIFYURL; ?>">  
        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
        <input type="hidden" name="items" value="<?php echo osc_esc_html(osp_cart_content_name() . ' (' . $description . ')'); ?>">
        <input type="hidden" name="currency" value="<?php echo osp_currency(); ?>">
        <input type="hidden" name="amount" value="<?php echo $amount; ?>">  
        <input type="hidden" name="first_name" value="<?php echo osc_logged_user_name(); ?>">
        <input type="hidden" name="last_name" value="<?php echo osc_logged_user_name(); ?>">
        <input type="hidden" name="email" value="<?php echo osc_logged_user_email(); ?>">
        <input type="hidden" name="phone" value="<?php echo (@$user['s_phone_land'] <> '' ? $user['s_phone_land'] : @$user['s_phone_mobile']); ?>">
        <input type="hidden" name="address" value="<?php echo (@$user['s_address'] <> '' ? $user['s_address'] : '-'); ?>">
        <input type="hidden" name="city" value="<?php echo (@$user['s_city'] <> '' ? $user['s_city'] : '-'); ?>">
        <input type="hidden" name="country" value="<?php echo (@$user['s_country'] <> '' ? $user['s_country'] : '-'); ?>">
        <input type="hidden" name="hash" value="<?php echo $hash; ?>">
      </form> 

      <a id="osp-button-confirm" class="button osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to Payhere.lk', 'osclass_pay')); ?>" onclick="$('#payherelk_payment_form').submit();">
        <span><img src="<?php echo osp_url(); ?>img/payments/payherelk.png"/></span>
        <strong><?php _e('Pay with PayHere.lk', 'osclass_pay'); ?></strong>
      </a>
    </li>
    <?php
  }


  public static function processPayment() {
    $order_id = (Params::getParam('order_id') <> '' ? Params::getParam('order_id') : Params::getParam('orderId'));
    $pending = ModelOSP::newInstance()->getPendingById($order_id);

    $payment_status = Params::getParam('status_code');
    $status_message = Params::getParam('status_message');
    
    
    if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || @$pending['pk_i_id'] <= 0) {
      return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
    }

    $data = osp_get_custom($pending['s_extra']);
    $product_type = explode('x', $data['product']);
    $amount = round(Params::getParam('payhere_amount') > 0 ? Params::getParam('payhere_amount') : $data['amount'], 2);
    $secret = osp_decrypt(osp_param('payherelk_secret'));

    if($amount<=0) { 
      return array(OSP_STATUS_AMOUNT_ZERO, ''); 
    }


    if($payment_status == 0) {    // pending code
      return array(OSP_STATUS_PENDING, __('We are processing your payment!', 'osclass_pay')); 
    } 
    
    if ($payment_status == 2) {   // success code
      // Have we processed the payment already?
      $tx = Params::getParam('payment_id');
      $payment_hash = Params::getParam('md5sig');
      $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'PAYHERELK');

      $merchant_id = osp_param('payherelk_merchant_id');

      $hash_to_check = strtoupper(md5(Params::getParam('merchant_id') . Params::getParam('order_id') . Params::getParam('payhere_amount') . Params::getParam('payhere_currency') . Params::getParam('status_code') . strtoupper(md5($secret))));

      if($payment_hash !== $hash_to_check) {
        return array(OSP_STATUS_FAILED, __('Failed - security check (hash) does not match', 'osclass_pay')); 
      }

      Params::setParam('payherelk_transaction_id', $tx);
      Params::setParam('payherelk_product_type', $product_type);


      if (!$payment) {
        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $data['concept'],  //concept
          $tx, // payment id
          $amount, //amount
          Params::getParam('payhere_currency'), //currency
          $data['email'], // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
          $product_type[0], //product type
          'PAYHERELK' //source
        ); 

        // Pay it!
        $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);

        // Remove pending row
        ModelOSP::newInstance()->deletePending($pending['pk_i_id']);
        
        return array(OSP_STATUS_COMPLETED, $status_message);
      }

      return array(OSP_STATUS_ALREADY_PAID, ''); 
    }

    return array(OSP_STATUS_FAILED, $status_message);
  }
}

?>