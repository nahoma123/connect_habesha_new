<?php
  class SkrillPayment {
    public function __construct() { }

    // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
    public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
      $extra = osp_prepare_custom($extra_array) . '|';
      $extra .= 'concept,'.$description.'|';
      $extra .= 'product,'.$itemnumber.'|';
      $r = rand(0,1000);
      $extra .= '|random,'.$r;

      SkrillPayment::standardButton($amount, $description, $itemnumber, $extra_array);
    }


    public static function standardButton($amount = '0.00', $description = '', $itemnumber = '101', $extra_array = null) {
      $extra = osp_prepare_custom($extra_array);
      $r = rand(0,1000);
      $extra .= '|random,'.$r;

      $APIEMAIL = osp_param('skrill_email');
      $ENDPOINT = 'https://www.moneybookers.com/app/payment.pl';

      $RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php?extra=' . $extra;
      $STATUSURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'status.php';
      $CANCELURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php?extra=' . $extra;
      ?>


      <li class="payment skrill-btn">
        <form class="nocsrf" action="<?php echo $ENDPOINT; ?>" target="_self" name="payment_form" id="skrill_<?php echo $r; ?>">
          <input type="hidden" name="pay_to_email" value="<?php echo $APIEMAIL; ?>"/>
          <?php if(osp_param('skrill_notify') == 1) { ?><input type="hidden" name="status_url" value="<?php echo osc_contact_email(); ?>"/><?php } ?>
          <input type="hidden" name="cancel_url" value="<?php echo $CANCELURL; ?>"/> 
          <input type="hidden" name="return_url" value="<?php echo $RETURNURL; ?>"/>
          <input type="hidden" name="status_url" value="<?php echo $STATUSURL; ?>"/>
          <input type="hidden" name="language" value="EN"/>
          <input type="hidden" name="hide_login" value="1">
          <input type="hidden" name="amount" value="<?php echo round($amount, 2); ?>"/>
          <input type="hidden" name="currency" value="<?php echo osp_currency(); ?>"/>
          <input type="hidden" name="detail1_description" value="<?php echo osc_esc_html(__('Listing Promotion', 'osclass_pay')); ?>"/>
          <input type="hidden" name="detail1_text" value="<?php echo osc_esc_html($description); ?>"/>
          <input type="hidden" name="confirmation_note" value="<?php echo osc_esc_html(__('Thanks for publishing on our site!', 'osclass_pay')); ?>">
          <input type="hidden" name="merchant_fields" value="user_id, item_name, item_number, extra">
          <input type="hidden" name="user_id" value="<?php echo osc_logged_user_id(); ?>">
          <input type="hidden" name="item_name" value="<?php echo osc_esc_html(osp_cart_content_name() . ' - ' . $description); ?>" />
          <input type="hidden" name="item_number" value="<?php echo $itemnumber; ?>" />
          <input type="hidden" name="extra" value="<?php echo $extra; ?>">
        </form>

        <a id="osp-button-confirm" class="button osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to Skrill', 'osclass_pay')); ?>" onclick="$('#skrill_<?php echo $r; ?>').submit();">
          <span><img src="<?php echo osp_url(); ?>img/payments/skrill.png"/></span>
          <strong><?php _e('Pay with Skrill', 'osclass_pay'); ?></strong>
        </a>
      </li>
    <?php
    }


    public static function processPayment() {
      if (Params::getParam('status') == 2 || Params::getParam('status') == 0) {  // completed or pending

        // CHECK IF PAYMENT IS GOING TO YOUR WALLET (PROTECTION)
        $APIMERCHANTID = osp_param('skrill_merchant_id');
        $APISECRETWORD = osp_decrypt(osp_param('skrill_secret_word'));
        $APIEMAIL = osp_param('skrill_email');

        $creds = Params::getParam('merchant_id').Params::getParam('transaction_id').strtoupper(md5($APISECRETWORD)).Params::getParam('mb_amount').Params::getParam('mb_currency').Params::getParam('status');

        if (strtoupper(md5($creds)) == Params::getParam('md5sig') && Params::getParam('pay_to_email') == $APIEMAIL) {
          // Have we processed the payment already?
          $tx = Params::getParam('mb_transaction_id') <> '' ? Params::getParam('mb_transaction_id') : Params::getParam('transaction_id');
          $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'SKRILL');

          if (!$payment) {
            $custom = Params::getParam('extra');
            $data = osp_get_custom($custom);
            $product_type = explode('x', Params::getParam('item_number'));
            $amount = Params::getParam('mb_amount') <> '' ? Params::getParam('mb_amount') : Params::getParam('amount');

            // SAVE TRANSACTION LOG
            $payment_id = ModelOSP::newInstance()->saveLog(
              Params::getParam('item_name'), //concept
              $tx, // payment id
              Params::getParam('mb_amount') <> '' ? Params::getParam('mb_amount') : Params::getParam('amount'), //amount
              Params::getParam('mb_currency'), //currency
              Params::getParam('pay_from_email') <> '' ? Params::getParam('pay_from_email') : '', // payer's email
              $data['user'], //user
              osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
              $product_type[0], //product type
              'SKRILL' //source
            ); 


            // Pay it!
            $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
            $pay_item = osp_pay_fee($payment_details);

            return OSP_STATUS_COMPLETED;
          } 

          return OSP_STATUS_ALREADY_PAID;
        }

        return OSP_STATUS_INVALID;
      }

      return OSP_STATUS_FAILED;
    }
  }
?>