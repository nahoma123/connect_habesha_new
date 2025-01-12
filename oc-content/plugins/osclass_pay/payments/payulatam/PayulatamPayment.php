<?php
  class PayulatamPayment {
    public function __construct() { }
    
    // BUTTON CALLED VIA OSP_BUTTONS FUNCTION TO SHOW PAYMENT OPTIONS
    public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
      $extra = osp_prepare_custom($extra_array) . '|';
      $extra .= 'concept,'.$description.'|';
      $extra .= 'product,'.$itemnumber;


      if(osp_param('payulatam_sandbox') == 1) {
        $ENDPOINT = "https://sandbox.gateway.payulatam.com/ppp-web-gateway/";
      } else {
        $ENDPOINT = "https://gateway.payulatam.com/ppp-web-gateway/";
      }

      $RESPONSE_URL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'response.php';
      $CONFIRM_URL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'confirm.php';

      $api_key = osp_decrypt(osp_param('payulatam_api_key'));

      $merchant_id = osp_param('payulatam_merchant_id');
      $account_id = osp_param('payulatam_account_id');
      $ref_id = mb_generate_rand_string(12);
      $currency = osp_currency();
      $amount = number_format($amount, 1, '.', '');
      
      //hashSequence = ApiKey~merchantId~referenceCode~amount~currency
      $retHashSeq = "$api_key~$merchant_id~$ref_id~$amount~$currency";
      $hash = hash("SHA256", $retHashSeq);

      ?>
      
      <li class="payment payulatam-btn">
        <form method="post" action="<?php echo $ENDPOINT; ?>" class="nocsrf">
          <input name="merchantId" type="hidden" value="<?php echo $merchant_id; ?>">
          <input name="accountId" type="hidden" value="<?php echo $account_id; ?>">
          <input name="description" type="hidden" value="<?php echo osc_esc_html($description); ?>">
          <input name="referenceCode" type="hidden" value="<?php echo $ref_id; ?>">
          <input name="amount" type="hidden" value="<?php echo $amount; ?>">
          <input name="tax" type="hidden" value="0">
          <input name="taxReturnBase" type="hidden" value="0">
          <input name="currency" type="hidden" value="<?php echo osp_currency(); ?>">
          <input name="signature" type="hidden" value="<?php echo $hash; ?>">
          <input name="algorithmSignature" type="hidden" value="SHA256">
          <input name="buyerFullName" type="hidden" value="<?php echo osc_logged_user_name(); ?>">
          <input name="buyerEmail" type="hidden" value="<?php echo osc_logged_user_email(); ?>">
          <input name="mobilePhone" type="hidden" value="<?php echo osc_logged_user_phone(); ?>">
          <input name="responseUrl" type="hidden" value="<?php echo $RESPONSE_URL; ?>">
          <input name="confirmationUrl" type="hidden" value="<?php echo $CONFIRM_URL; ?>">
          <input name="extra1" type="hidden" value="<?php echo $extra; ?>">
          <?php if(osp_param('payulatam_sandbox') == 1) { ?><input name="test" type="hidden" value="1"><?php } ?>
          
          <a class="osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to PayULatam', 'osclass_pay')); ?>" href="#" onclick="$(this).closest('form').submit();return false;" >
            <span><img src="<?php echo osp_url(); ?>img/payments/payulatam.png"/></span>
            <strong><?php _e('Pay with PayULatam', 'osclass_pay'); ?></strong>
          </a>
        </form>
      </li>
    <?php
    }



    public static function processPayment() {
      $state = (Params::getParam('transactionState') <> '' ? Params::getParam('transactionState') : Params::getParam('state_pol'));
      if ($state == 4) {  // approved

        // Have we processed the payment already?
        $tx = (Params::getParam('transactionId') <> '' ? Params::getParam('transactionId') : Params::getParam('transaction_id'));
        $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'PAYULATAM');
        $amount = number_format((Params::getParam('TX_VALUE') <> '' ? Params::getParam('TX_VALUE') : Params::getParam('value')), 1, '.', '');
        $api_key = osp_decrypt(osp_param('payulatam_api_key'));
        $merchant_id = (Params::getParam('merchantId') <> '' ? Params::getParam('merchantId') : Params::getParam('merchant_id'));
        $ref_id = (Params::getParam('referenceCode') <> '' ? Params::getParam('referenceCode') : Params::getParam('reference_sale'));
        $currency = Params::getParam('currency');
        $signature = (Params::getParam('signature') <> '' ? Params::getParam('signature') : Params::getParam('sign'));

        $sig = "$api_key~$merchant_id~$ref_id~$amount~$currency~$state";
        $hash = hash("SHA256", $sig);

        if (strtoupper($hash) != strtoupper($signature)) {
          return OSP_STATUS_INVALID;
        }
        
        if (!$payment) {
          $custom = Params::getParam('extra1');
          $data = osp_get_custom($custom);
          $product_type = explode('x', $data['product']);

          // SAVE TRANSACTION LOG
          $payment_id = ModelOSP::newInstance()->saveLog(
            data['concept'], //concept
            $tx, // payment id
            $amount, //amount
            $currency, //currency
            $data['email'], // payer's email
            $data['user'], //user
            osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
            $product_type[0], //product type
            'PAYULATAM' //source
          ); 


          // Pay it!
          $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
          $pay_item = osp_pay_fee($payment_details);

          return OSP_STATUS_COMPLETED;
        }
        return OSP_STATUS_ALREADY_PAID;
        
      } else if ($state == 7) {  // pending
        return OSP_STATUS_PENDING;
      }
      
      return OSP_STATUS_FAILED;
    }
  }
?>