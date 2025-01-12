<?php
class StripePayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib-sca/init.php';

    $zero_decimal_cur = array('BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF');
    $min_amount = (in_array(osp_currency(), $zero_decimal_cur) ? 50 : 0.5);
    
    
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber;


    if(osp_param('stripe_sandbox')==0) {
      $stripe = array(
        'secret_key' => osp_decrypt(osp_param('stripe_secret_key')),
        'publishable_key' => osp_decrypt(osp_param('stripe_public_key'))
      );
    } else {
      $stripe = array(
        'secret_key' => osp_decrypt(osp_param('stripe_secret_key_test')),
        'publishable_key' => osp_decrypt(osp_param('stripe_public_key_test'))
      );
    }
    

    if($amount >= $min_amount) {
      $pending_data = array(
        'fk_i_user_id' => osc_logged_user_id(),
        's_email' => osc_logged_user_email(),
        's_extra' => $extra,
        's_source' => 'STRIPE',
        'dt_date' => date('Y-m-d h:i:s')
      );

      $order_id = ModelOSP::newInstance()->insertPending($pending_data);

      $SUCCESSURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'success.php?orderId=' . $order_id;
      $CANCELURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php';

      \Stripe\Stripe::setApiKey($stripe['secret_key']);

      $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
          'price_data' => [
            'currency' => osp_currency(),
            'unit_amount' => round(in_array(osp_currency(), $zero_decimal_cur) ? $amount : $amount*100),
            'product_data' => [
              // 'name' => $itemnumber,
              'name' => (trim(osp_cart_content_name()) <> '' ? osp_cart_content_name() : $itemnumber),
              'description' => $description
            ]
          ],
          'quantity' => 1,
        ]],
        'mode' => 'payment',
        'client_reference_id' => $order_id,
        'customer_email' => (osc_logged_user_email() <> '' ? osc_logged_user_email() : null),
        'success_url' => $SUCCESSURL,
        'cancel_url' => $CANCELURL,
        'metadata' => [
          'order_id' => $order_id
        ]
      ]);
      

      $store_id = ($session->payment_intent <> '' ? $session->payment_intent : $session->id);  // store Session ID or Payment Intent

      ModelOSP::newInstance()->updatePendingTransaction($order_id, $store_id);

      echo '<li class="payment stripe-btn"><a class="osp-has-tooltip" title="' . osc_esc_html(__('Form to enter credit card details will pop-up', 'osclass_pay')) . '" href="#" onclick="stripe_pay(\''.$amount.'\',\''.osc_esc_js($description).'\',\''.$itemnumber.'\',\''.osc_esc_js($extra).'\',\''.$session->id.'\');return false;" ><span><img src="' . osp_url() . 'img/payments/stripe.png"/></span><strong>' . __('Pay with Stripe', 'osclass_pay') . '</strong></a></li>';

    } else {
      echo '<li class="payment stripe-btn"><a class="osp-has-tooltip osp-disabled" disabled="disabled" title="' . osc_esc_html(sprintf(__('Stripe accept only payments with amount larger than %s', 'osclass_pay'), osp_format_price($min_amount))) . '" href="#" onclick="return false;" ><span><img src="' . osp_url() . 'img/payments/stripe.png" ></span><strong>' . __('Pay with Stripe', 'osclass_pay') . '</strong></a></li>';
    }
  }


  // POPUP JS DIALOG
  public static function dialogJS() { ?>
    <script type="text/javascript">
      <?php
        $SUCCESSURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'success.php';
        $CANCELURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php';

        if(osp_param('stripe_sandbox')==0) {
          $stripe = array(
            'secret_key' => osp_decrypt(osp_param('stripe_secret_key')),
            'publishable_key' => osp_decrypt(osp_param('stripe_public_key'))
          );
        } else {
          $stripe = array(
            'secret_key' => osp_decrypt(osp_param('stripe_secret_key_test')),
            'publishable_key' => osp_decrypt(osp_param('stripe_public_key_test'))
          );
        }
      ?>


      function stripe_pay(amount, description, itemnumber, extra, sessionId) {
        var stripe = Stripe('<?php echo $stripe['publishable_key']; ?>');

        stripe.redirectToCheckout({
          sessionId: sessionId
        }).then(function (result) {
          console.log(result.error.message);
        });
      }
    </script>
  <?php
  }


  
  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment() {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib-sca/init.php';

    $zero_decimal_cur = array('BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF');
    $min_amount = (in_array(osp_currency(), $zero_decimal_cur) ? 50 : 0.5);
    
    if(osp_param('stripe_sandbox')==0) {
      $stripe = array(
        'secret_key' => osp_decrypt(osp_param('stripe_secret_key')),
        'publishable_key' => osp_decrypt(osp_param('stripe_public_key'))
      );
    } else {
      $stripe = array(
        'secret_key' => osp_decrypt(osp_param('stripe_secret_key_test')),
        'publishable_key' => osp_decrypt(osp_param('stripe_public_key_test'))
      );
    }

    $order_id = Params::getParam('orderId');
    $pending = ModelOSP::newInstance()->getPendingById($order_id);

    if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
      return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
    }

    $data = osp_get_custom($pending['s_extra']);
    $product_type = explode('x', $data['product']);
    $amount = round($data['amount'], 2);   // stripe accept just integers up to 2 decimals
    
    if($amount<=0) { 
      return array(OSP_STATUS_AMOUNT_ZERO); 
    } else if ($amount < $min_amount) {
      return array(OSP_STATUS_AMOUNT_SMALL);
    }

    $payment = ModelOSP::newInstance()->getPaymentByCode($pending['s_transaction_id'], 'STRIPE');

    \Stripe\Stripe::setApiKey($stripe['secret_key']);
    
    if($pending === false || !isset($pending['s_transaction_id']) || trim((string)$pending['s_transaction_id']) == '') {
      return array(OSP_STATUS_FAILED, __('Pending record does not have properly filled transaction ID (is empty)', 'osclass_pay'));
    }
    
    $is_paid = false;
    $is_pending = false;
    $transaction_id = '';
    
    // Differentiate based on transaction ID. We store Payment Intent ID (pi_) if available, otherwise Checkout Session ID (cs_)
    if(substr((string)$pending['s_transaction_id'], 0, 3) == 'pi_') {
      $intent = \Stripe\PaymentIntent::retrieve($pending['s_transaction_id']);
      $is_paid = ($intent->status == 'succeeded' ? true : false);
      $is_pending = ($intent->status == 'pending' ? true : false);
      $transaction_id = $pending['s_transaction_id'];
      
    } else if(substr((string)$pending['s_transaction_id'], 0, 3) == 'cs_') {
      $session = \Stripe\Checkout\Session::retrieve($pending['s_transaction_id']);
      $is_paid = ($session->payment_status == 'paid' ? true : false);   // or $session->status == 'complete'
      $is_pending = ($session->payment_status == 'pending' ? true : false);   // or $session->status == 'complete'
      $transaction_id = $session->payment_intent;

    }


    ModelOSP::newInstance()->updatePendingTransaction($order_id, $transaction_id);
    Params::setParam('stripe_transaction_id', $transaction_id);

    if($is_paid === true) {
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
          'STRIPE' //source
        );


        // Pay it!
        $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);


        // Remove pending row
        //ModelOSP::newInstance()->deletePending($pending['pk_i_id']);

        return array(OSP_STATUS_COMPLETED, '');
      }

      //return array(OSP_STATUS_ALREADY_PAID, ''); 
      return array(OSP_STATUS_COMPLETED, ''); 
      
    } else if($is_pending === true) {
      return array(OSP_STATUS_PENDING, ''); 
      
    } else {
      // return array(OSP_STATUS_FAILED, __('Payment does not have state SUCCEEDED/PAID in stripe system', 'osclass_pay'));
      return array(OSP_STATUS_PENDING, ''); 

    }
  }
}
?>