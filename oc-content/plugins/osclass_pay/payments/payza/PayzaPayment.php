<?php
  class PayzaPayment {
    public function __construct() {}

    public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
      $extra = osp_prepare_custom($extra_array) . '|';
      $r = rand(0,1000);
      $extra .= 'concept,'.$description.'|';
      $extra .= 'product,'.$itemnumber.'|';
      $extra .= '|random,'.$r;
      $apcs = self::custom_to_apc($extra);

      $RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php?custom=' . $extra;
      $CANCELURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php?custom=' . $extra;
      $IPNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'ipn.php';


    ?>
    <li class="payza-btn">
      <form class="nocsrf" method="post" action="https://secure.payza.com/checkout" id="payza_<?php echo $r; ?>_block">
        <input type="hidden" name="ap_merchant" value="<?php echo osp_param('payza_email'); ?>"/>
        <input type="hidden" name="ap_purchasetype" value="service"/>
        <input type="hidden" name="ap_itemname" value="<?php echo osc_esc_html(sprintf(__('Listing Promotion (%s)', 'osclass_pay'), $itemnumber)); ?>"/>
        <input type="hidden" name="ap_amount" value="<?php echo round($amount, 2); ?>"/>
        <input type="hidden" name="ap_taxamount" value="0"/>
        <input type="hidden" name="ap_currency" value="<?php echo osp_currency(); ?>"/>
        <input type="hidden" name="ap_inpage" value="1"/>
        <input type="hidden" name="ap_ipnversion" value="2"/>
        <input type="hidden" name="ap_quantity" value="1"/>
        <input type="hidden" name="ap_itemcode" value="<?php echo $itemnumber; ?>"/>
        <input type="hidden" name="ap_description" value="<?php echo urlencode(osc_esc_html($description)); ?>"/>
        <input type="hidden" name="ap_returnurl" value="<?php echo $RETURNURL; ?>"/>
        <input type="hidden" name="ap_cancelurl" value="<?php echo $CANCELURL; ?>"/>
        <input type="hidden" name="ap_alerturl" value="<?php echo $IPNURL; ?>"/>


        <?php foreach($apcs as $k => $v) {
          echo '<input type="hidden" name="apc_'.$k.'" value="'.$v.'"/>';
        } ?>
    
        <a id="payza-button-confirm" class="button btn-payza osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to Payza', 'osclass_pay')); ?>" onclick="$('#payza_<?php echo $r; ?>').submit();">
          <span><input name="ap_image" type="image" src="<?php echo osp_url(); ?>img/payments/payza.png"/></span>
          <strong><?php _e('Pay with Payza', 'osclass_pay'); ?></strong>
        </a>
      </form>
      </li>
    <?php
    }

  
    // PROCESS PAYMENT ON PLUGIN SIDE
    public static function processPayment($info) {
      $APIEMAIL = osp_param('payza_email');

      $data = osp_get_custom($info['custom']);
      $product_type = explode('x', $info['ap_itemcode']);
      $amount = round($data['amount'], 2); 

      if($amount<=0) { 
        return OSP_STATUS_AMOUNT_ZERO; 
      } else if ($amount < 1) {
        return OSP_STATUS_AMOUNT_SMALL;
      }

      if($APIEMAIL == $info['ap_merchant']) {
        $tx = $info['ap_referencenumber'];
        $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'PAYZA');


        if (!$payment) {
          // SAVE TRANSACTION LOG
          $payment_id = ModelOSP::newInstance()->saveLog(
            urldecode($info['ap_description']), //concept
            $tx, // payment id
            $info['ap_totalamount'], //amount
            $info['ap_currency'], //currency
            $info['ap_custemailaddress'], // payer's email
            $data['user'], //user
            osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
            $product_type[0], //product type
            'PAYZA' //source
          ); 


          // Pay it!
          $payment_details = osp_prepare_payment_data($info['ap_totalamount'], $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
          $pay_item = osp_pay_fee($payment_details);

          return OSP_STATUS_COMPLETED;
        } 

        return OSP_STATUS_ALREADY_PAID;
      }

      return OSP_STATUS_FAILED;
    }


    // PAYZA SET MAXIMUM OF 100 CHARS IN EACH apc_X VARIABLE
    // $extra WILL BE SPLIT INTO THESE 6 VARIABLES
    public static function custom_to_apc($extra) {
      $apc = array();
      $min = min(6, ceil(strlen($extra)));
      for($k=0;$k<$min;$k++) {
        $apc[$k+1] = substr($extra, 100*$k, 100);
      }

      return $apc;
    }


    // GENERATE EXTRA-CUSTOM BASED ON APCs FROM PAYZA
    public static function apc_to_custom() {
      $extra = '';
      for($i=1;$i<=6;$i++) {
        $extra .= Params::getParam('apc_' . $i);
      }

      return $extra;
    }
  }
?>