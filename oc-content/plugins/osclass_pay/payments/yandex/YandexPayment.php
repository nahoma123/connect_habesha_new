<?php
require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'yandex-checkout-sdk/lib/autoload.php';
use YandexCheckout\Client;

class YandexPayment {

  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {

    //

    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    $r = rand(0,1000);
    $extra .= 'r,'.$r;

    $email = osc_logged_user_email(); 
    $shop_id = osp_param('yandex_shop_id');
    $secret_key = osp_decrypt(osp_param('yandex_api_secret'));
    $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
    $transaction_id = 'ym_' . mb_generate_rand_string(10);

    //$ENDPOINT = 'https://money.yandex.ru/quickpay/confirm.xml';
    $RETURN_URL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php';

    $pending_data = array(
      's_transaction_id' => $transaction_id,
      'fk_i_user_id' => osc_logged_user_id(),
      's_email' => osc_logged_user_email(),
      's_extra' => $extra,
      's_source' => 'YANDEX',
      'dt_date' => date('Y-m-d h:i:s')
    );

    $order_id = ModelOSP::newInstance()->insertPending($pending_data);



    $client = new Client();
    $client->setAuth($shop_id, $secret_key);

    $payment = $client->createPayment(
        array(
            'amount' => array(
                'value' => round($amount, 2),
                'currency' => osp_currency(),
            ),
            'confirmation' => array(
                'type' => 'redirect',
                'return_url' => $RETURN_URL . '?order_id=' . $order_id,
            ),
            'capture' => true,
            'description' => $description . ' (' . $itemnumber . ')',
            'metadata' => array(
                'order_id' => $order_id,
                'item_number' => $itemnumber,
                'extra' => $extra
            )
        ),
        uniqid('', true)
    );

    ModelOSP::newInstance()->updatePendingTransaction($order_id, $payment->id);

    $CONFIRMATION_URL = $payment->confirmation->confirmationUrl;
    ?>

    <li>
      <a id="osp-button-confirm" class="button osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to money.yandex.ru', 'osclass_pay')); ?>" href="<?php echo $CONFIRMATION_URL; ?>">
        <span><img src="<?php echo osp_url(); ?>img/payments/yandex.png"/></span>
        <strong><?php _e('Pay with Yandex', 'osclass_pay'); ?></strong>
      </a>
    </li>
    <?php
  }


  public static function processPayment() {
    $order_id = Params::getParam('order_id');
    $transaction_id = Params::getParam('transaction_id');

    if($order_id > 0) {
      $pending = ModelOSP::newInstance()->getPendingById($order_id);
      $transaction_id = $pending['s_transaction_id'];
    } else {
      $pending = ModelOSP::newInstance()->getPendingByTransactionId($transaction_id);
    }

    if(!$pending) {
      $pending = ModelOSP::newInstance()->getPendingByTransactionId(Params::getParam('order'), 'YANDEX');
    }

    if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
      return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
    }

    $extra = $pending['s_extra'];     // get pending row
    $data = osp_get_custom($extra);
    $product_type = explode('x', $data['product']);
    $amount = $data['amount']; 

    Params::setParam('extra', $extra);


    if($amount <= 0) { 
      return array(OSP_STATUS_AMOUNT_ZERO, ''); 
    }

    $shop_id = osp_param('yandex_shop_id');
    $secret_key = osp_decrypt(osp_param('yandex_api_secret'));
    
    $client = new Client();
    $client->setAuth($shop_id, $secret_key);
    
    $payment_yandex = $client->getPaymentInfo($transaction_id);

    if($payment_yandex->status != 'succeeded' && $payment_yandex->status != 'waiting_for_capture') {
      return array(OSP_STATUS_FAILED, sprintf(__('Payment has not been successfully signed to our account yet. Current payment status is %s', 'osclass_pay'), $payment_yandex->status)); 
    }

    Params::setParam('yandex_transaction_id', $transaction_id);

    $payment = ModelOSP::newInstance()->getPaymentByCode($transaction_id, 'YANDEX');


    if(!$payment) { 
      // SAVE TRANSACTION LOG
      $payment_id = ModelOSP::newInstance()->saveLog(
        $data['concept'], //concept
        $transaction_id, // transaction code
        $amount, //amount
        strtoupper($payment_yandex->currency), //currency
        $data['email'], // payer's email
        $data['user'], //user
        osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
        $product_type[0], //product type
        'YANDEX' //source
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