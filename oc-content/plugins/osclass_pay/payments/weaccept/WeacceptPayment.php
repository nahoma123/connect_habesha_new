<?php
// https://github.com/baklysystems/laravel-paymob

class WeacceptPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    //$r = rand(0,1000);
    //$extra .= '|random,'.$r;

    $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());


    // 0 - PREPARE AUTHENTICATION
    require_once osc_lib_path() . 'plugins/osclass_pay/payments/weaccept/lib/WeacceptLibrary.php';

    $integration_id = osp_param('weaccept_integration_id');
    $iframe_id = osp_param('weaccept_iframe_id');
    $api_key = osp_decrypt(osp_param('weaccept_api_key'));

    $pending_data = array(
      'fk_i_user_id' => osc_logged_user_id(),
      's_email' => osc_logged_user_email(),
      's_extra' => $extra,
      's_source' => 'WEACCEPT',
      'dt_date' => date('Y-m-d h:i:s')
    );

    $order_id = ModelOSP::newInstance()->insertPending($pending_data);


    $weaccept = new WeacceptLibrary();


    // 1. AUTHENTICATE
    $auth = $weaccept->authPaymob($api_key);

    // 2. ORDER REGISTRATION REQUEST
    $order = $weaccept->makeOrderPaymob(
      $auth->token, // this is token from step 1.
      $auth->profile->id, // this is the merchant id from step 1.
      $amount * 100, // total amount by cents/piasters.
      $order_id // your (merchant) order id.
    );


    // 3. PAYMENT KEY GENERATION REQUEST
    $payment_key = $weaccept->getPaymentKeyPaymob(
      $integration_id,  // your integration ID
      $auth->token, // from step 1.
      $order->amount_cents, // total amount by cents/piasters.
      $order->id, // paymob order id from step 2.
      $user['s_email'], // optional
      $user['s_name'], // optional, firstname
      $user['s_name'], // optional, lastname
      ($user['s_phone_mobile'] <> '' ? $user['s_phone_mobile'] : ($user['s_phone_land'] <> '' ? $user['s_phone_land'] : '-')),
      ($user['s_city'] <> '' ? $user['s_city'] : '-'), // optional
      ($user['s_country'] <> '' ? $user['s_country'] : '-') // optional
    );


    // Add transaction ID to pending record
    ModelOSP::newInstance()->updatePendingTransaction($order_id, $order->id);


    ?>

    <li class="payment weaccept-btn">
      <div id="weaccept-overlay" class="osp-custom-overlay" style="display: none;"></div>
      <i id="weaccept-close" class="fa fa-close" style="display: none;"></i>
      <iframe width="480" height="600" id="weaccept-iframe" style="display:none;" src="https://accept.paymobsolutions.com/api/acceptance/iframes/<?php echo $iframe_id; ?>?payment_token=<?php echo $payment_key->token; ?>"></iframe>

      <a class="osp-has-tooltip weaccept-btn-iframe" title="<?php echo osc_esc_html(__('WeAccept pop-up will be shown', 'osclass_pay')); ?>" href="#" >
        <span><img src="<?php echo osp_url(); ?>img/payments/weaccept.png"/></span>
        <strong><?php _e('Pay with WeAccept', 'osclass_pay'); ?></strong>
      </a>
    </li>

    <?php
  }


  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment() {
    $pending = ModelOSP::newInstance()->getPendingById(Params::getParam('merchant_order_id'));

    if(Params::getParam('success') == false) {
      return array(OSP_STATUS_FAILED, sprintf(__('Failed - %s', 'osclass_pay'), Params::getParam('data_message') <> '' ? Params::getParam('data_message') : __('There was problem on gateway side', 'osclass_pay'))); 
    }

    if(!$pending) {
      $pending = ModelOSP::newInstance()->getPendingByTransactionId(Params::getParam('order'), 'WEACCEPT');
    }

    if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
      return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
    }


    $extra = $pending['s_extra'];     // get pending row
    $data = osp_get_custom($extra);
    $product_type = explode('x', $data['product']);
    $amount = round(Params::getParam('amount_cents')/100, 2); 

    Params::setParam('extra', $extra);


    if($amount <= 0) { 
      return array(OSP_STATUS_AMOUNT_ZERO, ''); 
    }


    $tid = Params::getParam('order');
    Params::setParam('weaccept_transaction_id', $tid);
    $payment = ModelOSP::newInstance()->getPaymentByCode($tid, 'WEACCEPT');


    if(!$payment) { 
      // SAVE TRANSACTION LOG
      $payment_id = ModelOSP::newInstance()->saveLog(
        $data['concept'], //concept
        $tid, // transaction code
        $amount, //amount
        strtoupper(Params::getParam('currency')), //currency
        $data['email'], // payer's email
        $data['user'], //user
        osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
        $product_type[0], //product type
        'WEACCEPT' //source
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
}
?>