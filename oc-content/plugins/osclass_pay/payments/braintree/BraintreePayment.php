<?php
  class BraintreePayment {

    public function __construct() {
      Braintree_Configuration::environment(osp_param('braintree_sandbox') == 1 ? 'sandbox' : 'production');
      Braintree_Configuration::merchantId(osp_param('braintree_merchant_id'));
      Braintree_Configuration::publicKey(osp_param('braintree_public_key'));
      Braintree_Configuration::privateKey(osp_decrypt(osp_param('braintree_private_key')));
    }

    public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
      $extra = osp_prepare_custom($extra_array).'|';
      $extra .= 'concept,'.$description.'|';
      $extra .= 'product,'.$itemnumber.'|';
      $r = rand(0,1000);
      $extra .= '|random,'.$r;

      $CALLBACK_URL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'callback.php?extra=' . $extra;
      echo '<li class="payment braintree-btn"><a class="osp-has-tooltip" title="' . osc_esc_html(__('Form to enter credit card details will pop-up', 'osclass_pay')) . '" href="#" onclick="braintree_pay(\''.$amount.'\',\''.osc_esc_js($description).'\',\''.$itemnumber.'\',\''.$extra.'\');return false;" ><span><img src="' . osp_url() . 'img/payments/braintree.png"/></span><strong>' . __('Pay with BrainTree', 'osclass_pay') . '</strong></a></li>';
    }

    public static function dialogJS() { ?>
      <div id="braintree-dialog" title="<?php _e('Braintree', 'osclass_pay'); ?>" style="display:none;">
        <div id="braintree-info">
          <div id="braintree-data">
            <p id="braintree-img"><img src="<?php echo osp_url(); ?>img/payments/braintree-cards.png"/></p>
            <p id="braintree-desc"></p>
            <label><?php _e('Payment amount', 'osclass_pay'); ?></label>
            <p id="braintree-price"></p>
          </div>

          <form class="nocsrf" action="<?php echo osc_base_url(true); ?>" method="POST" id="braintree-payment-form" >
            <input type="hidden" name="page" value="ajax" />
            <input type="hidden" name="action" value="runhook" />
            <input type="hidden" name="hook" value="braintree" />
            <input type="hidden" name="extra" id="braintree-extra" value="" />

            <p class="bt1">
              <label><?php _e('Card number', 'osclass_pay'); ?></label>
              <span class="osp-input-box">
                <input type="text" size="20" autocomplete="off" data-encrypted-name="braintree_number" maxlength="20" required/>
                <i class="fa fa-credit-card"></i>
              </span>
            </p>

            <p class="bt2">
              <label><?php _e('Expiration (MM/YYYY)', 'osclass_pay'); ?></label>
              <span class="osp-input-box">
                <input type="text" size="2" data-encrypted-name="braintree_month" maxlength="2" required/>
                <span class="osp-del">/</span>
                <input type="text" size="4" data-encrypted-name="braintree_year" maxlength="4" required/>
                <i class="fa fa-calendar"></i>
              </span>
            </p>

            <p class="bt3">
              <label><?php _e('CVV', 'osclass_pay'); ?></label>
              <span class="osp-input-box">
                <input type="text" size="4" autocomplete="off" data-encrypted-name="braintree_cvv" maxlength="3" required/>
                <i class="fa fa-lock"></i>
              </span>
            </p>

            <button type="submit" id="submit"><?php echo osc_esc_html(__('Pay', 'osclass_pay')); ?> <span></span></button>
          </form>
        </div>

        <div id="braintree-results"><i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span><?php echo osc_esc_js(__('Processing the payment, please wait...', 'osclass_pay'));?></span></div>
        <div id="braintree-response"></div>
      </div>

      <script type="text/javascript" src="https://js.braintreegateway.com/v2/braintree.js"></script>
      <script type="text/javascript">
        $(document).ready(function(){
          $("#braintree-dialog").dialog({
            autoOpen: false,
            dialogClass: "osp-dialog braintree-dialog",
            modal: true,
            show: { effect: 'fade', duration: 200 },
            hide: { effect: 'fade', duration: 200 },
            open: function(event, ui) {
              $('.braintree-dialog .ui-dialog-title').html('<img src="<?php echo osp_url(); ?>img/payments/white/braintree.png"/>');
            }
          });
        });

        var ajax_submit_braintree = function (e) {
          form = $('#braintree-payment-form');
          e.preventDefault();
          $("button#submit").attr("disabled", "disabled");
          $("#braintree-info").hide();
          $("#braintree-results").html('<i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span><?php echo osc_esc_js(__('Processing the payment, please wait...', 'osclass_pay'));?></span>').show(0);
          $.post(form.attr('action'), form.serialize(), function (data) {
            $("#braintree-response").html(data);
          });
        };


        var braintree = Braintree.create('<?php echo osp_decrypt(osp_param('braintree_encryption_key')); ?>');
        braintree.onSubmitEncryptForm('braintree-payment-form', ajax_submit_braintree);

        function braintree_pay(amount, description, itemnumber, extra) {
          $("#braintree-extra").prop('value', extra);
          $("#braintree-desc").html(description);
          $("#braintree-price").html(amount+" <?php echo osp_currency(); ?>");
          $("button#submit").removeAttr('disabled');
          $("button#submit span").text(amount+" <?php echo osp_currency(); ?>");
          $("#braintree-results").html('').hide(0);
          $("#braintree-info").show();
          $("#braintree-dialog").dialog('open');
        }
      </script>
    <?php
    }

    public static  function ajaxPayment() {
      $result = self::processPayment();
      $status = $result[0];

      $data = osp_get_custom(Params::getParam('extra'));
      $product_type = explode('x', $data['product']);
      
      if ($status == OSP_STATUS_COMPLETED) {
        osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), Params::getParam('braintree_transaction_id')));
      } else if ($status == OSP_STATUS_ALREADY_PAID) {
        osc_add_flash_warning_message(__('Warning! This payment was already paid.', 'osclass_pay'));
      } else if ($status == OSP_STATUS_AMOUNT_ZERO) {
        osc_add_flash_error_message(__('You are trying to pay zero amount.', 'osclass_pay'));
      } else {
        osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay') . ' (' . $result[1] . ')');
      }

      if(OSP_DEBUG) {
        //$emailtext = osp_array_to_string(Params::getParamsAsArray('post'));
        $emailtext = osp_array_to_string($result[1]);
        mail(osc_contact_email() , 'OSCLASS PAY - BRAINTREE DEBUG RESPONSE (AJAX)', $emailtext);
      }

      osp_js_redirect_to(osp_pay_url_redirect($product_type));
    }


    public static function processPayment() {
      require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib/Braintree.php';

      Braintree_Configuration::environment(osp_param('braintree_sandbox') == 1 ? 'sandbox' : 'production');
      Braintree_Configuration::merchantId(osp_param('braintree_merchant_id'));
      Braintree_Configuration::publicKey(osp_param('braintree_public_key'));
      Braintree_Configuration::privateKey(osp_decrypt(osp_param('braintree_private_key')));

      $data = osp_get_custom(Params::getParam('extra'));
      $product_type = explode('x', $data['product']);

      $amount = round($data['amount'], 2);
      
      if($amount <= 0) { 
        return array(OSP_STATUS_AMOUNT_ZERO, ''); 
      }

      $result = Braintree_Transaction::sale([
        'amount' => $amount,
        'creditCard' => array(
          'number' => Params::getParam('braintree_number'),
          'cvv' => Params::getParam('braintree_cvv'),
          'expirationMonth' => Params::getParam('braintree_month'),
          'expirationYear' => Params::getParam('braintree_year')
        ),
        'options' => [
          'submitForSettlement' => true
        ]
      ]);


      //$result->_attributes->message;   // Error message from braintree


      if($result->success==1) {
        Params::setParam('braintree_transaction_id', $result->transaction->id);
        $payment = ModelOSP::newInstance()->getPaymentByCode($result->transaction->id, 'BRAINTREE');
        $product_type = explode('x', $data['product']);

        if(!$payment) { 
          
          // SAVE TRANSACTION LOG
          $payment_id = ModelOSP::newInstance()->saveLog(
            $data['concept'], //concept
            $result->transaction->id, // transaction code
            $result->transaction->amount, //amount
            $result->transaction->currencyIsoCode, //currency
            $data['email'], // payer's email
            $data['user'], //user
            osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
            $product_type[0], //product type
            'BRAINTREE' //source
          );
          
          
          // Pay it!
          $payment_details = osp_prepare_payment_data($result->transaction->amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
          $pay_item = osp_pay_fee($payment_details);        

          return array(OSP_STATUS_COMPLETED, $result);
        }

        return array(OSP_STATUS_ALREADY_PAID, $result); 
      }
 
      if ($result->transaction) {
        $error = '[' . $result->transaction->processorResponseCode . '] ' . $result->transaction->processorResponseText;
      } else {
        $error = '';
        foreach($result->errors->deepAll() as $e) {
          $error .= $e->code . ': ' . $e->message . ' ';
        }
      } 

      return array(OSP_STATUS_FAILED, $error);
    }
  }
?>