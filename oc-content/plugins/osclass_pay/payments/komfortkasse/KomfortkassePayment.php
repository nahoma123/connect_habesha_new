<?php
// https://github.com/ltckomfortkasse/komfortkasse-apiv2-php

class KomfortkassePayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    //$r = rand(0,1000);
    //$extra .= '|random,'.$r;

    $PREPAREURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'prepare.php?extra=' . $extra;

    ?>

    <li class="payment komfortkasse-btn">
      <?php if ($amount >= 25) { ?>
        <a id="osp-button-confirm" class="button osp-has-tooltip" title="<?php echo osc_esc_html(__('Komfortkasse payment will be initiated', 'osclass_pay')); ?>" href="<?php echo $PREPAREURL; ?>">
          <span><img src="<?php echo osp_url(); ?>img/payments/komfortkasse.png"/></span>
          <strong><?php _e('Pay with KomfortKasse', 'osclass_pay'); ?></strong>
        </a>
      <?php } else { ?>
        <a id="osp-button-confirm" class="button osp-has-tooltip osp-disabled" title="<?php echo osc_esc_html(sprintf(__('Minimum order about for Komfortkasse.eu is %s%s', 'osclass_pay'), 25, osp_currency())); ?>" href="#" onclick="return false;">
          <span><img src="<?php echo osp_url(); ?>img/payments/komfortkasse.png"/></span>
          <strong><?php _e('Pay with KomfortKasse', 'osclass_pay'); ?></strong>
        </a>
      <?php } ?>
    </li>

    <?php
  }



  // PREPARE PAYMENT ON PLUGIN SIDE
  public static function preparePayment() {
    $extra = Params::getParam('extra');
    $data = osp_get_custom($extra);
    $amount = round($data['amount'], 2);
    $product_type = explode('x', $data['product']);

    $api_key = osp_decrypt(osp_param('komfortkasse_api_key'));
    $transaction_id = 'kk_' . mb_generate_rand_string(16);

    $pending_data = array(
      'fk_i_user_id' => $data['user'],
      's_transaction_id' => $transaction_id,
      's_email' => $data['email'],
      's_extra' => $extra,
      's_source' => 'KOMFORTKASSE',
      'dt_date' => date('Y-m-d h:i:s')
    );

    $user = User::newInstance()->findByPrimaryKey($data['user']);

    $order = array(
      'number' => $transaction_id,
      'date' => date('d-m-Y'),
      'customerEmail' => $data['email'],
      'customerNumber' => $data['user'] . '/' . (@$user['s_name'] <> '' ? $user['s_name'] : osc_logged_user_name()),
      'paymentMethod' => 'prepayment',
      'paymentMethodDescription' => __('Payment by Bank Transfer', 'osclass_pay'),
      'type' => 'PREPAYMENT',
      'amount' => round($amount, 2),
      'currency' => osp_currency(),
      'language' => 'de',
      'billing' => array(
        'lastName' => (@$user['s_name'] <> '' ? $user['s_name'] : osc_logged_user_name()),
        'countryCode' => 'DE'
      ),
      'itemNumbers' => array(
        // $data['concept'] . ' - ' . osc_page_title()
        osp_cart_content_name()
      )
    );


    // send data to komfortkasse
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://ssl.komfortkasse.eu/api/v2/order/' . $transaction_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order));

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'X-Komfortkasse-API-Key: ' . $api_key
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    $gateway_response = json_decode($response, true);

    if(!isset($gateway_response['error']) || @$gateway_response['error'] == '') {
      $order_id = ModelOSP::newInstance()->insertPending($pending_data);
      osp_cart_drop($data['user']);
    }


    if(OSP_DEBUG) {
      $emailtext = osp_array_to_string($gateway_response);
      mail(osc_contact_email() , 'OSCLASS PAY - KOMFORTKASSE DEBUG RESPONSE (PREPARE)', $emailtext);
    }


    return array(
      'transaction_id' => $transaction_id,
      'error' => @$gateway_response['error']
    );
  }



  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment($transaction_id, $status) {
    $pending = ModelOSP::newInstance()->getPendingByTransactionId($transaction_id, 'KOMFORTKASSE');

    if($status == 'FAILED') {
      ModelOSP::newInstance()->deletePending($pending['pk_i_id']);
      return array(OSP_STATUS_FAILED, __('Order has been cancelled', 'osclass_pay')); 
    }

    if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
      return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
    }


    $extra = $pending['s_extra'];     // get pending row
    $data = osp_get_custom($extra);
    $product_type = explode('x', $data['product']);
    $amount = round($data['amount'], 2); 

    Params::setParam('extra', $extra);


    if($amount <= 0) { 
      return array(OSP_STATUS_AMOUNT_ZERO, ''); 
    }


    Params::setParam('komfortkasse_transaction_id', $transaction_id);
    $payment = ModelOSP::newInstance()->getPaymentByCode($transaction_id, 'KOMFORTKASSE');


    if(!$payment) { 
      // SAVE TRANSACTION LOG
      $payment_id = ModelOSP::newInstance()->saveLog(
        $data['concept'], //concept
        $transaction_id, // transaction code
        $amount, //amount
        strtoupper(osp_currency()), //currency
        $data['email'], // payer's email
        $data['user'], //user
        osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
        $product_type[0], //product type
        'KOMFORTKASSE' //source
      );


      // Pay it!
      $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
      $pay_item = osp_pay_fee($payment_details);


      // Remove pending row
      ModelOSP::newInstance()->deletePending($pending['pk_i_id']);
      return array(OSP_STATUS_COMPLETED, '');

    } else {

      ModelOSP::newInstance()->deletePending($pending['pk_i_id']);
      return array(OSP_STATUS_ALREADY_PAID, ''); 
    }

  }


  // READ ORDERS
  public static function readOrders() {
    $api_key = osp_decrypt(osp_param('komfortkasse_api_key'));

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://ssl.komfortkasse.eu/api/v2/orders?maxAge=120");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'X-Komfortkasse-API-Key: ' . $api_key
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
  }
}
?>