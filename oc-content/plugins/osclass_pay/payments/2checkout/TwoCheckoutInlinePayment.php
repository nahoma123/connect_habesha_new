<?php

class TwoCheckoutInlinePayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    $r = rand(0,1000);
    $extra .= '|random,'.$r;

    $check_sum = strtoupper(md5($itemnumber . round($amount, 2)));


    $RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php';

    if(osp_param('twocheckout_sandbox') == 1) {
      $ENDPOINT = 'https://sandbox.2checkout.com/checkout/purchase';
    } else {
      $ENDPOINT = 'https://2checkout.com/checkout/purchase';
    }
    ?>

    <li class="payment twocheckout-btn">
      <form action="<?php echo $ENDPOINT; ?>" method="post" id="twocheck" class="nocsrf">
        <?php $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id()); ?>

        <input type="hidden" name="x_receipt_link_url " value="<?php echo $RETURNURL; ?>" />
        <input type="hidden" name="fixed" value="Y" />
        <?php if(osp_param('twocheckout_sandbox') == 1) { ?><input type="hidden" name="demo" value="Y" /><?php } ?>
        <input type="hidden" name="sid" value="<?php echo osp_param('twocheckout_seller_id'); ?>" />
        <input type="hidden" name="mode" value="2CO" />
        <input type="hidden" name="li_0_type" value="product" />
        <input type="hidden" name="li_0_name" value="<?php echo $description; ?>" />
        <input type="hidden" name="li_0_product_id" value="<?php echo $itemnumber; ?>" />
        <input type="hidden" name="li_0__description" value="<?php echo $description; ?>" />
        <input type="hidden" name="li_0_price" value="<?php echo $amount; ?>" />
        <input type="hidden" name="li_0_quantity" value="1" />
        <input type="hidden" name="li_0_tangible" value="N" />
        <input type="hidden" name="currency_code" value="<?php echo osp_currency(); ?>" />
        <input type="hidden" name="card_holder_name" value="<?php echo $user['s_name']; ?>" />
        <input type="hidden" name="street_address" value="<?php echo $user['s_address']; ?>" />
        <input type="hidden" name="city" value="<?php echo $user['s_city']; ?>" />
        <input type="hidden" name="state" value="<?php echo $user['s_region']; ?>" />
        <input type="hidden" name="zip" value="<?php echo $user['s_zip']; ?>" />
        <input type="hidden" name="country" value="<?php echo $user['s_country']; ?>" />
        <input type="hidden" name="email" value="<?php echo $user['s_email']; ?>" />
        <input type="hidden" name="phone" value="<?php echo ($user['s_phone_mobile'] <> '' ? $user['s_phone_mobile'] : $user['s_phone_land']); ?>" />
        <input type="hidden" name="purchase_step" value="payment-method" />
        <input type="hidden" name="extra" value="<?php echo $extra; ?>" />
        <input type="hidden" name="rnd" value="<?php echo $check_sum; ?>" />


        <a class="osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to 2Checkout', 'osclass_pay')); ?>" href="#" onclick="$(this).closest('form').submit();return false;" >
          <span><img src="<?php echo osp_url(); ?>img/payments/2checkout.png"/></span>
          <strong><?php _e('Pay with 2Checkout', 'osclass_pay'); ?></strong>
        </a>
      </form>
    </li>
  <?php

  }


  
  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment() {
    $data = osp_get_custom(Params::getParam('extra'));
    $product_type = explode('x', $data['product']);
    $amount = round($data['amount'], 2); 


    $hashSecretWord = osp_param('twocheckout_secret_word');
    $hashSid = osp_param('twocheckout_seller_id');
    $hashTotal = Params::getParam('total');

    if(osp_param('twocheckout_sandbox') == 1) {
      $hashOrder = 1;
    } else {
      $hashOrder = Params::getParam('order_number');
    }

    $StringToHash = strtoupper(md5($hashSecretWord . $hashSid . $hashOrder . $hashTotal));


    if ($StringToHash != Params::getParam('key') && 1==2) {
      return array(OSP_STATUS_FAILED, __('Fail - Hash Mismatch', 'osclass_pay') . ' - ' . $hashTotal . ' :: ' . $StringToHash . ' :: ' . Params::getParam('key')); 
    }

    if (Params::getParam('rnd') != strtoupper(md5($data['product'] . round(Params::getParam('total'), 2)))) {
      return array(OSP_STATUS_FAILED, __('Checkout price has been modified and does not match.', 'osclass_pay')  . $data['product'] . Params::getParam('total') . ' / ' .   strtoupper(md5($data['product'] . Params::getParam('total'))) ); 
    }




    if($amount<=0) { 
      return array(OSP_STATUS_AMOUNT_ZERO, ''); 
    }

    $tid = Params::getParam('transactionId') <> '' ? Params::getParam('transactionId') : $_REQUEST['order_number'];
    Params::setParam('twocheckout_transaction_id', $tid);
    $payment = ModelOSP::newInstance()->getPaymentByCode($tid, '2CHECKOUT');

    if(!$payment) { 
      // SAVE TRANSACTION LOG
      $payment_id = ModelOSP::newInstance()->saveLog(
        $data['concept'], //concept
        $tid, // transaction code
        Params::getParam('total'), //amount
        strtoupper(Params::getParam('currencyCode')), //currency
        $data['email'], // payer's email
        $data['user'], //user
        osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
        $product_type[0], //product type
        '2CHECKOUT' //source
      );


      // Pay it!
      $payment_details = osp_prepare_payment_data(Params::getParam('total'), $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
      $pay_item = osp_pay_fee($payment_details);

      return array(OSP_STATUS_COMPLETED, '');
    }

    return array(OSP_STATUS_ALREADY_PAID, ''); 
  }
}
?>