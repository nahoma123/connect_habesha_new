<?php
class LiqpayPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'src/sdk-php-master/LiqPay.php';

    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber;

    $email = osc_logged_user_email(); //osp_param('paystack_email');

    $public_key = osp_param('liqpay_public_key');
    $private_key = osp_decrypt(osp_param('liqpay_private_key'));

    $pending_data = array(
      'fk_i_user_id' => osc_logged_user_id(),
      's_email' => osc_logged_user_email(),
      's_extra' => $extra,
      's_source' => 'LIQPAY',
      'dt_date' => date('Y-m-d h:i:s')
    );

    $order_id = ModelOSP::newInstance()->insertPending($pending_data);

    $RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php?orderId=' . $order_id;
    $WEBHOOKURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'webhook.php'; //?orderId=' . $order_id;


    $liqpay = new LiqPay($public_key, $private_key);
    
    $data = array(
      'action' => 'pay',
      'amount' => $amount,
      'currency' => osp_currency(),
      'description' => osp_cart_content_name() . ' (' . $description . ' - ' . $itemnumber . ')',
      'order_id' => $order_id,
      'version' => '3',
      'result_url' => $RETURNURL,
      'server_url' => $WEBHOOKURL     
    );
    
    
    $form = $liqpay->cnb_form_raw($data);
    
    if(!isset($form['data'])) {
      if(osc_is_admin_user_logged_in()) {
        echo '<li><a onclick="return false;">' . __('Liqpay button could not be generated', 'osclass_pay') . '</a></li>';
      }
      
      return false;  
    }
    ?>

    <li>
      <form method="POST" action="<?php echo $form['url']; ?>" accept-charset="utf-8" id="liqpay_payment_form">
        <input type="hidden" name="data" value="<?php echo $form['data']; ?>"/>
        <input type="hidden" name="signature" value="<?php echo $form['signature']; ?>"/>
        
        <a id="osp-button-confirm" class="button osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to LiqPay.ua', 'osclass_pay')); ?>" onclick="$('#liqpay_payment_form').submit();">
          <span><img src="<?php echo osp_url(); ?>img/payments/liqpay.svg"/></span>
          <strong><?php _e('Pay with LiqPay.ua', 'osclass_pay'); ?></strong>
        </a>
      </form>

    </li>
    <?php
  }



  // CONFIRM PAYMENT ON PAYSTACK
  public static function processPayment() {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'src/sdk-php-master/LiqPay.php';

    $provider_signature = Params::getParam('signature');
    $params = json_decode(base64_decode(Params::getParam('data')), true);
    
    if(is_array($params) && count($params) > 0) {
      foreach($params as $key => $val) {
        Params::setParam($key, $val);
      }
    }
    
    $order_id = (Params::getParam('order_id') <> '' ? Params::getParam('order_id') : Params::getParam('orderId'));
    $pending = ModelOSP::newInstance()->getPendingById($order_id);

    $data = osp_get_custom($pending['s_extra']);
    $product_type = explode('x', $data['product']);
    $amount = round(Params::getParam('amount') > 0 ? Params::getParam('amount') : $data['amount'], 2);
    
    $status_message = implode(' - ', array_filter(array(Params::getParam('err_code'), Params::getParam('err_description'))));
    
    
    if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || @$pending['pk_i_id'] <= 0) {
      return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
    } else {
      if($pending['s_transaction_id'] <> '') {
        $payment = ModelOSP::newInstance()->getPaymentByCode($pending['s_transaction_id'], 'LIQPAY');
      
        if(isset($payment['s_code']) && $payment['s_code'] <> '') {
          Params::setParam('liqpay_transaction_id', $payment['s_code']);
          Params::setParam('liqpay_product_type', $product_type);

          return array(OSP_STATUS_COMPLETED, '');
        }
      }
    }


    if($amount <= 0) { 
      return array(OSP_STATUS_AMOUNT_ZERO, ''); 
    }

    if(Params::getParam('public_key') == '' || Params::getParam('version') == '' || Params::getParam('action') == '') {    // Return URL
      return array(OSP_STATUS_PENDING, __('We are processing your payment!', 'osclass_pay')); 
    } 
    
    
    // VALIDATE HASH
    $private_key = osp_decrypt(osp_param('liqpay_private_key'));
    $signature = base64_encode(sha1($private_key . Params::getParam('data') . $private_key, 1));
 
    if($provider_signature != $signature) {
      return array(OSP_STATUS_FAILED, __('Failed - security check (signature) does not match', 'osclass_pay')); 
    }
    
    if (Params::getParam('action') == 'pay' && Params::getParam('status') == 'success') {   // success code
      // Have we processed the payment already?
      $tx = Params::getParam('payment_id');
      $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'LIQPAY');

      Params::setParam('liqpay_transaction_id', $tx);
      Params::setParam('liqpay_product_type', $product_type);

      if (!$payment) {
        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $data['concept'],  //concept
          $tx, // payment id
          $amount, //amount
          Params::getParam('currency'), //currency
          $data['email'], // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
          $product_type[0], //product type
          'LIQPAY' //source
        ); 

        // Pay it!
        $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);

        // Remove pending row
        //ModelOSP::newInstance()->deletePending($pending['pk_i_id']);
        ModelOSP::newInstance()->updatePendingTransaction($order_id, $tx);

        return array(OSP_STATUS_COMPLETED, '');
      }

      return array(OSP_STATUS_ALREADY_PAID, ''); 
    }

    return array(OSP_STATUS_FAILED, $status_message);
  }

}
?>