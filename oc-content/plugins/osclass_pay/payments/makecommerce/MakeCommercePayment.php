<?php

// https://developer.maksekeskus.ee//reference.php
// https://github.com/maksekeskus/maksekeskus-php

class MakeCommercePayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'src/maksekeskus-1.4.4/vendor/autoload.php';

    // use Maksekeskus\Maksekeskus;


    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber;

    $pending_data = array(
      'fk_i_user_id' => osc_logged_user_id(),
      's_email' => osc_logged_user_email(),
      's_extra' => $extra,
      's_source' => 'MAKECOMMERCE',
      'dt_date' => date('Y-m-d h:i:s')
    );

    $order_id = ModelOSP::newInstance()->insertPending($pending_data);

    $RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php?orderId=' . $order_id;
    $CANCELURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php';
    $NOTIFICATIONURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'notification.php';


    // get your API keys from merchant.test.maksekeskus.ee or merchant.maksekeskus.ee
    // see https://makecommerce.net/en/for-developers/test-environment/
    $shop_id = osp_param('makecommerce_shop_id');
    $publishable_key = osp_param('makecommerce_public_key');
    $secret_key = osp_decrypt(osp_param('makecommerce_secret_key'));
    $is_test = (osp_param('makecommerce_test_mode') == 1 ? true : false);
    
    // Static params
    $country = 'lt';
    $locale = 'en';

    $session = new \Maksekeskus\Maksekeskus($shop_id, $publishable_key, $secret_key, $is_test);

    $data = [
      'transaction' => [
        'amount' => number_format($amount, 2),
        'currency' => osp_currency(),
        'reference' => $order_id, // order ref.
        'merchant_data' => $order_id, // optional order ID or some unique
        'transaction_url' => [
          'return_url' => [
            'url' => $RETURNURL,
            'method' => 'POST'
          ],
          'cancel_url' => [
            'url' => $CANCELURL,
            'method' => 'POST'
          ],
          'notification_url' => [
            'url' => $NOTIFICATIONURL,
            'method' => 'POST'
          ]
        ]
      ],
      'customer' => [ // client data
        'email' => osc_logged_user_email(),
        'name' => osc_logged_user_name(),
        'ip' => osc_get_ip(),
        'country' => $country,
        'locale' => $locale
      ],
      'app_info' => [
        'module' => 'osclass_pay',
        'module_version' => '3.8.6',
        'platform' => 'osclass',
        'platform_version' => osc_version()
      ],
      'method' => 'redirect'
    ];
    
    
    try {

// echo '<pre>';
// print_r($data);
// exit;

      $transaction = $session->createTransaction($data);

      $tran_id = $transaction->id;
      $redirect_url = $transaction->payment_methods->other[0]->url;

      ModelOSP::newInstance()->updatePendingTransaction($order_id, $tran_id);
      
      echo '<li class="payment makecommerce-btn"><a class="osp-has-tooltip" title="' . osc_esc_html(__('You will be redirected to MakeCommerce', 'osclass_pay')) . '" href="' . $redirect_url . '"><span><img src="' . osp_url() . 'img/payments/makecommerce.png"/></span><strong>' . __('Pay with MakeCommerce', 'osclass_pay') . '</strong></a></li>';

    } catch(Maksekeskus\MKException $e) {
      if($e->getMessage() != '') {
        ?>
        <div id="osp-pmnt-err">
          <strong class="osp-ehead"><?php echo sprintf(__('Payment gateway <u>%s</u> returned error!', 'osclass_pay'), 'MAKECOMMERCE'); ?></strong>
          <div class="osp-errm"><?php echo $e->getMessage(); ?></div>
        </div>
        <?php
      }
    }
  }


  
  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment() {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'src/maksekeskus-1.4.4/vendor/autoload.php';

    $shop_id = osp_param('makecommerce_shop_id');
    $publishable_key = osp_param('makecommerce_public_key');
    $secret_key = osp_decrypt(osp_param('makecommerce_secret_key'));
    $is_test = (osp_param('makecommerce_test_mode') == 1 ? true : false);

    $session = new \Maksekeskus\Maksekeskus($shop_id, $publishable_key, $secret_key, $is_test);
    
    $checksum = $session->verifySignature(Params::getParamsAsArray());
    
    if($checksum !== true) {
      return array(OSP_STATUS_FAILED, __('Failed - unable to verify authenticity of payment data', 'osclass_pay')); 
    }
    
    $json = @json_decode(Params::getParam('json'), true);
    $order_id = (int)(isset($json['reference']) ? $json['reference'] : Params::getParam('orderId'));
 
    $pending = ModelOSP::newInstance()->getPendingById($order_id);

    if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
      return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
    }

    $data = osp_get_custom($pending['s_extra']);
    $product_type = explode('x', $data['product']);
    $amount = round($data['amount'], 2);   // stripe accept just integers up to 2 decimals
    
    if($amount<=0) { 
      return array(OSP_STATUS_AMOUNT_ZERO); 
    }

    $payment = ModelOSP::newInstance()->getPaymentByCode($pending['s_transaction_id'], 'STRIPE');

    if($pending === false || !isset($pending['s_transaction_id']) || trim((string)$pending['s_transaction_id']) == '') {
      return array(OSP_STATUS_FAILED, __('Pending record does not have properly filled transaction ID (is empty)', 'osclass_pay'));
    }
    
    $status = isset($json['status']) ? $json['status'] : '';
    $transaction_id = isset($json['transaction']) ? $json['transaction'] : $pending['s_transaction_id'];

    Params::setParam('makecommerce_transaction_id', $transaction_id);


    if($status == 'COMPLETED') {
      if(!$payment) {
        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $data['concept'], //concept
          $transaction_id, // transaction code (payment intent starting with pi_)
          $amount, //amount
          strtoupper(osp_currency()), //currency
          $data['email'], // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $product_type[2]), // cart string
          $product_type[0], //product type
          'MAKECOMMERCE' //source
        );


        // Pay it!
        $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);


        // Don't remove pending row, as "return" and "notofication" rely on order id 

        return array(OSP_STATUS_COMPLETED, '');
      }

      //return array(OSP_STATUS_ALREADY_PAID, ''); 
      return array(OSP_STATUS_COMPLETED, ''); 
      
    } else if($status == 'APPROVED' || $status == 'PENDING' || $status == 'CREATED') {
      return array(OSP_STATUS_PENDING, ''); 
      
    } else {
      return array(OSP_STATUS_FAILED, __('Payment has been canceled or failed', 'osclass_pay'));

    }
  }
}
?>