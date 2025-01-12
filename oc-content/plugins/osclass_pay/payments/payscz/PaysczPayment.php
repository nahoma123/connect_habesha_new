<?php
  class PaysczPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    //$extra = osp_prepare_custom($extra_array) . '|';

    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    $r = rand(0,1000);
    $extra .= 'r,'.$r;

    $email = osc_logged_user_email(); 
    $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
    $transaction_id = 'pc_' . mb_generate_rand_string(10);


    $pending_data = array(
      's_transaction_id' => $transaction_id,
      'fk_i_user_id' => osc_logged_user_id(),
      's_email' => osc_logged_user_email(),
      's_extra' => $extra,
      's_source' => 'PAYSCZ',
      'dt_date' => date('Y-m-d h:i:s')
    );

    $order_id = ModelOSP::newInstance()->insertPending($pending_data);


    $link  = 'https://www.pays.cz/paymentorder?Merchant=' . osp_param('payscz_merchant_id') . '&Shop=' . osp_param('payscz_shop_id');
    $link .= '&Currency=' . osp_currency() . '&Amount=' . round($amount*100) . '&MerchantOrderNumber=' . $order_id . '&Email=' . osc_logged_user_email();


    ?>

    <li class="payment payscz-btn">
      <a class="osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to Pays.cz', 'osclass_pay')); ?>" href="<?php echo $link; ?>">
        <span><img src="<?php echo osp_url(); ?>img/payments/payscz.png"/></span>
        <strong><?php _e('Pay with Pays.cz', 'osclass_pay'); ?></strong>
      </a>
    </li>
  <?php
  }



  public static function processPayment() {

    if (Params::getParam('PaymentOrderStatusID') == 3) {  // completed

      $shop_id = osp_param('payscz_shop_id');
      $merchant_id = osp_param('payscz_merchant_id');
      $api_pass = osp_decrypt(osp_param('payscz_api_pass'));

      $hashString = implode('', [Params::getParam('PaymentOrderID') . Params::getParam('MerchantOrderNumber') . Params::getParam('PaymentOrderStatusID') . Params::getParam('CurrencyID') . Params::getParam('Amount') . Params::getParam('CurrencyBaseUnits')]);
      $paymentHash = Params::getParam('hash') <> '' ? Params::getParam('hash') : Params::getParam('Hash');


      if(hash_hmac('md5', $hashString, $api_pass) == $paymentHash) {  // hash match
        $pending = ModelOSP::newInstance()->getPendingById(Params::getParam('MerchantOrderNumber'));
 
        // Pending order does not exists
        if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
          return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
        }

        $extra = $pending['s_extra'];     // get pending row
        $data = osp_get_custom($extra);
        $product_type = explode('x', $data['product']);
        $amount = $data['amount']; 

        Params::setParam('extra', $extra);

        if(round($amount) <> round(Params::getParam('Amount')/100)) {
          return array(OSP_STATUS_AMOUNT_SMALL, __('Amounts does not match', 'osclass_pay')); 
        }


        // Have we processed the payment already?
        $tx = Params::getParam('PaymentOrderID');
        $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'PAYSCZ');


        if (!$payment) {
          // SAVE TRANSACTION LOG
          $payment_id = ModelOSP::newInstance()->saveLog(
            $data['concept'], //concept
            $tx, // payment id
            $amount, //amount
            Params::getParam('CurrencyID'), //currency
            $data['email'], // payer's email
            $data['user'], //user
            osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
            $product_type[0], //product type
            'PAYSCZ' //source
          ); 


          // Pay it!
          $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
          $pay_item = osp_pay_fee($payment_details);

          return OSP_STATUS_COMPLETED;
        } 

        return OSP_STATUS_ALREADY_PAID;
      }

      return array(OSP_STATUS_INVALID, __('Hash does not match, invalid payment', 'osclass_pay'));
    }

    return array(OSP_STATUS_FAILED, __('Payment status from gateway has not COMPLETED state', 'osclass_pay'));
  }
}
?>