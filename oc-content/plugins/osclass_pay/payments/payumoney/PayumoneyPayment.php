<?php
  class PayumoneyPayment {
    public function __construct() { }
    
    // BUTTON CALLED VIA OSP_BUTTONS FUNCTION TO SHOW PAYMENT OPTIONS
    public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
      $extra = osp_prepare_custom($extra_array);
      $extra .= 'concept,'.$description.'|';
      $extra .= 'product,'.$itemnumber.'|';
      $r = rand(0,1000);
      $extra .= '|random,'.$r;

      echo '<li class="payment payumoney-btn"><a class="osp-has-tooltip" title="' . osc_esc_html(__('Form to enter your personal details will pop-up', 'osclass_pay')) . '" href="#" onclick="payumoney_pay(\''.$amount.'\',\''.$description.'\',\''.$itemnumber.'\',\''.$extra.'\');return false;" ><span><img src="' . osp_url() . 'img/payments/payumoney.png"/></span><strong>' . __('Pay with PayUMoney', 'osclass_pay') . '</strong></a></li>';
    }


    public static function dialogJS() {
      $MERCHANT_KEY = osp_decrypt(osp_param('payumoney_merchant_key'));
      $SALT  = osp_decrypt(osp_param('payumoney_salt'));

      if(osp_param('payumoney_sandbox') == 1) {
        $PAYU_BASE_URL = "https://test.payu.in";
      } else {
        $PAYU_BASE_URL = "https://secure.payu.in";
      }

      $SUCCESS_URL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'success.php';
      $FAILURE_URL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'failure.php';

      $posted = array();
      if(!empty($_POST)) {
        foreach($_POST as $key => $value) {    
          $posted[$key] = $value; 
        }
      }

      $formError = 0;

      if(empty($posted['txnid'])) {
        $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
      } else {
        $txnid = $posted['txnid'];
      }

      $action = '';
      $hash = '';
      $hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
      if(empty($posted['hash']) && sizeof($posted) > 0) {
        if(
          empty($posted['key'])
          || empty($posted['txnid'])
          || empty($posted['amount'])
          || empty($posted['firstname'])
          || empty($posted['email'])
          || empty($posted['phone'])
          || empty($posted['productinfo'])
          || empty($posted['surl'])
          || empty($posted['furl'])
          || empty($posted['service_provider'])
        ) {
          $formError = 1;
        } else {
          $hashVarsSeq = explode('|', $hashSequence);
          $hash_string = '';	
          
          foreach($hashVarsSeq as $hash_var) {
            $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
            $hash_string .= '|';
          }
          
          $hash_string .= $SALT;
          $hash = strtolower(hash('sha512', $hash_string));
          $action = $PAYU_BASE_URL . '/_payment';
        }
      } elseif(!empty($posted['hash'])) {
        $hash = $posted['hash'];
        $action = $PAYU_BASE_URL . '/_payment';
      }

      ?>

      <div id="payumoney-dialog" title="<?php _e('PayUMoney', 'osclass_pay'); ?>" style="display:none;">
        <form action="<?php echo $action; ?>" method="POST" name="payuForm" class="nocsrf" id="payumoney-payment-form">
          <input type="hidden" name="key" value="<?php echo $MERCHANT_KEY; ?>" />
          <input type="hidden" name="hash" value="<?php echo $hash; ?>"/>
          <input type="hidden" name="txnid" value="<?php echo $txnid; ?>" />
          <input type="hidden" name="amount" value="<?php echo (empty($posted['amount'])) ? '' : $posted['amount']; ?>" id="payumoney-amount"/>
          <input type="hidden" name="productinfo" value="<?php echo (empty($posted['productinfo'])) ? '' : $posted['productinfo']; ?>" id="payumoney-productinfo"/>
          <input type="hidden" name="udf1" value="<?php echo (empty($posted['udf1'])) ? '' : $posted['udf1']; ?>" id="payumoney-extra"/>
          <input type="hidden" name="surl" value="<?php echo $SUCCESS_URL; ?>" />
          <input type="hidden" name="furl" value="<?php echo $FAILURE_URL; ?>" />
          <input type="hidden" name="service_provider" value="payu_paisa" size="64" />

          <?php if($formError) { ?>
            <p class="bt0"><?php _e('Please fill all mandatory fields', 'osclass_pay'); ?></p>
          <?php } ?>


          <div id="payumoney-info">
            <p id="payumoney-desc"><?php echo (empty($posted['productinfo'])) ? '' : $posted['productinfo']; ?></p>

            <label><?php _e('Payment amount', 'osclass_pay'); ?></label>
            <p id="payumoney-price"><?php echo (empty($posted['amount'])) ? '' : osp_format_price($posted['amount']); ?></p>
          </div>

          <p class="bt1">
            <label><?php _e('First Name', 'osclass_pay'); ?> *</label>
            <span class="osp-input-box">
              <input type="text" name="firstname" id="firstname" value="<?php echo (empty($posted['firstname'])) ? osc_logged_user_name() : $posted['firstname']; ?>" required/>
              <i class="fa fa-user"></i>
            </span>
          </p>

          <p class="bt2">
            <label><?php _e('Email', 'osclass_pay'); ?> *</label>
            <span class="osp-input-box">
              <input type="text" name="email" id="email" value="<?php echo (empty($posted['email'])) ? osc_logged_user_email() : $posted['email']; ?>" required/>
              <i class="fa fa-envelope"></i>
            </span>
          </p>

          <p class="bt3">
            <label><?php _e('Phone', 'osclass_pay'); ?> *</label>
            <span class="osp-input-box">
              <input type="text" name="phone" id="phone" value="<?php echo (empty($posted['phone'])) ? osc_logged_user_phone() : $posted['phone']; ?>" required/>
              <i class="fa fa-phone"></i>
            </span>
          </p>

          <button type="submit" id="payumoney-submit"><?php echo osc_esc_html(__('Pay', 'osclass_pay')); ?> <span></span></button>
        </form>

        <div id="payumoney-results" class="payumoney-results" style="display:none;"><i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span><?php echo osc_esc_js(__('Processing the payment, please wait...', 'osclass_pay'));?></span></div>
      </div>


      <script>
        $(document).ready(function(){
          $("#payumoney-dialog").dialog({
            autoOpen: false,
            dialogClass: "osp-dialog payumoney-dialog",
            modal: true,
            show: { effect: 'fade', duration: 200 },
            hide: { effect: 'fade', duration: 200 },
            open: function(event, ui) {
              $('.payumoney-dialog .ui-dialog-title').html('<img src="<?php echo osp_url(); ?>img/payments/white/payumoney.png"/>');
            }
          });
        });

        function payumoney_pay(amount, description, itemnumber, extra) {
          $("#payumoney-productinfo").val(description);
          $("#payumoney-amount").val(amount);
          $("#payumoney-extra").val(extra);
          $("#payumoney-desc").html(description);
          $("#payumoney-price").html(amount+" <?php echo osp_currency(); ?>");
          $("button#payumoney-submit span").text(amount+" <?php echo osp_currency(); ?>");
          $(".payumoney-results").html('').hide(0);
          $(".payumoney-results").eq(2).remove();          
          $("#payumoney-submit").prop('disabled', false).removeClass('osp-disabled');
          $("#payumoney-info, #payumoney-dialog .bt1, #payumoney-dialog .bt2, #payumoney-dialog .bt3, #payumoney-dialog button, form#payumoney-payment-form").show();
          $("#payumoney-dialog").dialog('open');
        }


        var ajax_submit_payumoney = function () {
          form = $('form#payumoney-payment-form');
          $("#submit").prop('disabled', true);
          $("#payumoney-info, #payumoney-dialog .bt1, #payumoney-dialog .bt2, #payumoney-dialog .bt3, #payumoney-dialog button, form#payumoney-payment-form").hide();
          $(".payumoney-results").html('<i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span><?php echo osc_esc_js(__('Processing the payment, please wait...', 'osclass_pay'));?></span>').show(0);
          $.post(form.attr('action'), form.serialize(), function (data) {
            var newFormHTML = $(data).contents().find('form#payumoney-payment-form').parent().html();
            $('form#payumoney-payment-form').replaceWith(newFormHTML);
            $('form#payumoney-payment-form').hide(0);

            $('form#payumoney-payment-form').submit();
          });

        };


        $('form#payumoney-payment-form').submit(function(e){
          var hashPYM = '<?php echo $hash; ?>';

          if(hashPYM == '') {
            e.preventDefault();
            ajax_submit_payumoney();
          }
        });


        // ON LOAD IF ALL FILLED, SUBMIT TO PAYUMONEY TO PROCESS
        var hashPYM = '<?php echo $hash; ?>';
        if(hashPYM != '') {
          $('form#payumoney-payment-form').submit();
          //$("#payumoney-dialog").dialog('open');
          //$("#payumoney-submit").addClass('osp-disabled').prop('disabled', true).text('<?php echo osc_esc_js(__('Processing...', 'osclass_pay'));?>');
        }
      </script>
      <?php
    }


    public static function processPayment() {
      if (in_array(strtolower(Params::getParam('status')), array('success', 'initiated', 'completed'))) {  // initiated or completed

        // Have we processed the payment already?
        $tx = Params::getParam('txnid');
        $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'PAYUMONEY');

        if (!$payment) {
          $data = osp_get_custom(urldecode(Params::getParam('udf1')));
          $product_type = explode('x', $data['product']);
    
          $amount = Params::getParam('amount');

          // SAVE TRANSACTION LOG
          $payment_id = ModelOSP::newInstance()->saveLog(
            Params::getParam('productinfo'), //concept
            $tx, // payment id
            $amount, //amount
            'INR', //currency
            Params::getParam('email'), // payer's email
            $data['user'], //user
            osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
            $product_type[0], //product type
            'PAYUMONEY' //source
          ); 


          // Pay it!
          $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
          $pay_item = osp_pay_fee($payment_details);

          return OSP_STATUS_COMPLETED;
        }

        return OSP_STATUS_ALREADY_PAID;
      }

      return OSP_STATUS_FAILED;
    }
  }
?>