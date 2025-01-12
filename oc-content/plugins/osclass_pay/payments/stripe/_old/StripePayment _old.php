<?php
class StripePayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    $r = rand(0,1000);
    $extra .= '|random,'.$r;

    if($amount >= 0.5) {
      echo '<li class="payment stripe-btn"><a class="osp-has-tooltip" title="' . osc_esc_html(__('Form to enter credit card details will pop-up', 'osclass_pay')) . '" href="#" onclick="stripe_pay(\''.$amount.'\',\''.$description.'\',\''.$itemnumber.'\',\''.$extra.'\');return false;" ><img src="' . osp_url() . 'img/payments/stripe.png" ></a></li>';
    } else {
      echo '<li class="payment stripe-btn"><a class="osp-has-tooltip osp-disabled" disabled="disabled" title="' . osc_esc_html(sprintf(__('Stripe accept only payments with amount larger than %s', 'osclass_pay'), osp_format_price(0.5))) . '" href="#" onclick="return false;" ><img src="' . osp_url() . 'img/payments/stripe.png" ></a></li>';
    }
  }


  // POPUP JS DIALOG
  public static function dialogJS() { ?>
    <div id="stripe-dialog" title="<?php _e('Stripe', 'osclass_pay'); ?>" style="display:none;">
      <div id="stripe-dialog-text"></div>
      <div id="stripe-dialog-response"></div>
    </div>

    <form action="<?php echo osc_base_url(true); ?>" method="post" id="stripe-payment-form" class="nocsrf" >
      <input type="hidden" name="page" value="ajax" />
      <input type="hidden" name="action" value="runhook" />
      <input type="hidden" name="hook" value="stripe" />
      <input type="hidden" name="extra" value="" id="stripe-extra" />
    </form>

    <script type="text/javascript">
      function stripe_pay(amount, description, itemnumber, extra) {
        var token = function(res){
          var $input = $('<input type=hidden name=stripeToken />').val(res.id);
          $('#stripe-extra').attr('value', extra);
          $('#stripe-payment-form').append($input);
          $.ajax({
            type: "POST",
            url: '<?php echo osc_base_url(true); ?>',
            data: $("#stripe-payment-form").serialize(),
            success: function(data) {
              $('#stripe-dialog-response').html(data);
            }
          });

          setTimeout(openStripeDialog, 150);
        };


        StripeCheckout.open({
          key: '<?php echo (osp_param('stripe_sandbox') == 0 ? osp_decrypt(osp_param('stripe_public_key')) : osp_decrypt(osp_param('stripe_public_key_test'))); ?>',
          billingAddress: false,
          amount: (amount*<?php echo osp_stripe_multiplier(); ?>),
          currency: '<?php echo osp_currency(); ?>',
          name: description,
          description: amount+' <?php echo osp_currency(); ?> ('+itemnumber+')',
          panelLabel: 'Checkout',
          token: token
        });

        return false;
      };

      function openStripeDialog() {
        $('#stripe-dialog-text').html('<i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span><?php echo osc_esc_js(__('Processing the payment, please wait...', 'osclass_pay'));?></span>').show(0);
        $('#stripe-dialog').dialog('open');
      }

      $(document).ready(function(){
        $("#stripe-dialog").dialog({
          autoOpen: false,
          dialogClass: 'osp-dialog stripe-dialog',
          modal: true,
          show: { effect: 'fade', duration: 200 },
          hide: { effect: 'fade', duration: 200 },
          open: function(event, ui) {
            $('.stripe-dialog .ui-dialog-title').html('<img src="<?php echo osp_url(); ?>img/payments/white/stripe.png"/>');
          }
        });
      });
    </script>
  <?php
  }


  // AJAX FUNCTION TO PROCESS PAYMENT, HOOKED TO AJAX_STRIPE
  public static function ajaxPayment() {
    $response = self::processPayment();      // manage payment itself
    $status = $response[0];

    $data = osp_get_custom(Params::getParam('extra'));
    $product_type = explode('x', $data['product']);
    
    if ($status == OSP_STATUS_COMPLETED) {
      osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), Params::getParam('stripe_transaction_id')));
    } else if ($status == OSP_STATUS_ALREADY_PAID) {
      osc_add_flash_warning_message(__('Warning! This payment was already paid.', 'osclass_pay'));
    } else if ($status == OSP_STATUS_AMOUNT_ZERO) {
      osc_add_flash_error_message(__('You are trying to pay zero amount.', 'osclass_pay'));
    } else if ($status == OSP_STATUS_AMOUNT_SMALL) {
      osc_add_flash_error_message(__('You are trying to pay too small amount. Stripe accept only payments larger than 0.5.', 'osclass_pay'));
    } else {
      osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay') . ' (' . $response[1]->getMessage() . ')');
    }

    if(OSP_DEBUG) {
      $emailtext = "status => " . $status . "\r\n";
      //$emailtext = osp_array_to_string(Params::getParamsAsArray('post'));
      if(isset($response[1])) {
        $emailtext .= "\r\n ---------- \r\n" . osp_array_to_string($response[1]);
      }
      mail(osc_contact_email() , 'OSCLASS PAY - STRIPE DEBUG RESPONSE (AJAX)', $emailtext);
    }

    osp_js_redirect_to(osp_pay_url_redirect($product_type));
  }

  
  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment() {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib/Stripe.php';

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

    Stripe::setApiKey($stripe['secret_key']);

    $token  = Params::getParam('stripeToken');
    $data = osp_get_custom(Params::getParam('extra'));
    $product_type = explode('x', $data['product']);
    $amount = round($data['amount'], 2);   // stripe accept just integers up to 2 decimals
    
    //$amount = osp_get_fee($product_type[0], 1, $product_type[2], (isset($product_type[3]) ? $product_type[3] : ''), (isset($product_type[4]) ? $product_type[4] : ''));
    //$amount = $product_type[4];


    if($amount<=0) { 
      return array(OSP_STATUS_AMOUNT_ZERO); 
    } else if ($amount < 0.5) {
      return array(OSP_STATUS_AMOUNT_SMALL);
    }


    $customer = Stripe_Customer::create(array(
      'email' => $data['email'],
      'card' => $token
    ));


    try {
      $charge = @Stripe_Charge::create(array(
        'customer' => $customer->id,
        'amount' => $amount*osp_stripe_multiplier(),
        'currency' => osp_currency()
      ));

      if($charge->__get('paid')==1) {
        $exists = ModelOSP::newInstance()->getPaymentByCode($charge->__get('id'), 'STRIPE');

        if(isset($exists['pk_i_id'])) { 
          return array(OSP_STATUS_ALREADY_PAID); 
        }
          
        $product_type = explode('x', $data['product']);
        Params::setParam('stripe_transaction_id', $charge->__get('id'));
        $amount = $charge->__get('amount')/osp_stripe_multiplier();

        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $data['concept'], //concept
          $charge->__get('id'), // transaction code
          $amount, //amount
          strtoupper($charge->__get('currency')), //currency
          $data['email'], // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
          $product_type[0], //product type
          'STRIPE' //source
        );


        // Pay it!
        $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);

        return array(OSP_STATUS_COMPLETED, $charge);
      }

      return array(OSP_STATUS_FAILED, $charge);
    } catch(Stripe_CardError $e) {

      return array(OSP_STATUS_FAILED, $e);
    }

    return array(OSP_STATUS_FAILED);
  }
}
?>