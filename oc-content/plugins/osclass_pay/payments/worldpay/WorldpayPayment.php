<?php
// https://github.com/Worldpay/worldpay-lib-php

class WorldpayPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    $r = rand(0,1000);
    $extra .= '|random,'.$r;
    echo '<li class="payment twocheckout-btn"><a class="osp-has-tooltip" title="' . osc_esc_html(__('Form to enter credit card details will pop-up', 'osclass_pay')) . '" href="#" onclick="twocheckout_pay(\''.$amount.'\',\''.$description.'\',\''.$itemnumber.'\',\''.$extra.'\');return false;" ><span><img src="' . osp_url() . 'img/payments/2checkout.png" alt="' . osc_esc_html(__('WorldPay Payment', 'osclass_pay')) . '"/></span><strong>' . __('Pay with WorldPay', 'osclass_pay') . '</strong></a></li>';
  }


  // POPUP JS DIALOG
  public static function dialogJS() { ?>
    <div id="twocheckout-overlay" class="osp-custom-overlay"></div>
    <div id="twocheckout-dialog" class="osp-custom-dialog" style="display:none;">
      <div class="osp-inside">
        <div class="osp-top">
          <span><img src="<?php echo osp_url(); ?>img/payments/white/2checkout.png" alt="<?php echo osc_esc_html(__('2Checkout Payment', 'osclass_pay')); ?>"/></span>
          <div class="osp-close"><i class="fa fa-times"></i></div>
        </div>

        <div class="osp-bot">
          <form id="myCCForm" action="<?php echo osc_base_url(true); ?>" method="post" class="nocsrf">
            <input type="hidden" name="token" id="token" value="">
            <input type="hidden" name="page" value="ajax" />
            <input type="hidden" name="action" value="runhook" />
            <input type="hidden" name="hook" value="twocheckout" />
            <input type="hidden" name="itemnumber" id="twocheckout-itemnumber" />
            <input type="hidden" name="amount" id="twocheckout-amount" value=""/>
            <input type="hidden" name="currency" id="twocheckout-currency" value="<?php echo osp_currency(); ?>" />
            <input type="hidden" name="description" id="twocheckout-description" />
            <input type="hidden" name="extra" id="twocheckout-extra"/>

            <p id="twocheckout-img"><img src="<?php echo osp_url(); ?>img/payments/2checkout-cards.png"/></p>
            <p id="twocheckout-desc"></p>

            <p class="bt1">
              <label><?php _e('Card number', 'osclass_pay'); ?></label>
              <span class="osp-input-box">
                <input id="ccNo" type="text" size="20" value="" autocomplete="off" required maxlength="20"/>
                <i class="fa fa-credit-card"></i>
              </span>
            </p>

            <p class="bt2">
              <label><?php _e('Expiration (MM/YYYY)', 'osclass_pay'); ?></label>
              <span class="osp-input-box">
                <input type="text" size="2" id="expMonth" required maxlength="2"/>
                <span class="osp-del">/</span>
                <input type="text" size="4" id="expYear" required maxlength="4"/>
                <i class="fa fa-calendar"></i>
              </span>
            </p>

            <p class="bt3">
              <label><?php _e('CVV', 'osclass_pay'); ?></label>
              <span class="osp-input-box">
                <input id="cvv" size="4" type="text" value="" autocomplete="off" required maxlength="3"/>
                <i class="fa fa-lock"></i>
              </span>
            </p>

            <p id="twocheckout-userdata"><?php _e('Your personal information', 'osclass_pay'); ?></p>

            <?php $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id()); ?>
            <p class="bt4">
              <label><?php _e('Your name', 'osclass_pay'); ?> *</label>
              <span class="osp-input-box">
                <input id="user_name" name="user_name" type="text" value="<?php echo osc_esc_html(isset($user['s_name']) ? $user['s_name'] : ''); ?>" required/>
                <i class="fa fa-user"></i>
              </span>
            </p>

            <p class="bt5">
              <label><?php _e('Country', 'osclass_pay'); ?> *</label>
              <span class="osp-input-box">
                <input id="user_country" name="user_country" type="text" value="<?php echo osc_esc_html(isset($user['s_country']) ? $user['s_country'] : ''); ?>" required/>
                <i class="fa fa-map-marker"></i>
              </span>
            </p>

            <p class="bt6">
              <label><?php _e('State', 'osclass_pay'); ?> *</label>
              <span class="osp-input-box">
                <input id="user_state" name="user_state" type="text" value="<?php echo osc_esc_html(isset($user['s_region']) ? $user['s_region'] : ''); ?>" required/>
                <i class="fa fa-map-pin"></i>
              </span>
            </p>

            <p class="bt7">
              <label><?php _e('City', 'osclass_pay'); ?> *</label>
              <span class="osp-input-box">
                <input id="user_city" name="user_city" type="text" value="<?php echo osc_esc_html(isset($user['s_city']) ? $user['s_city'] : ''); ?>" required/>
                <i class="fa fa-building"></i>
              </span>
            </p>

            <p class="bt8">
              <label><?php _e('ZIP', 'osclass_pay'); ?> *</label>
              <span class="osp-input-box">
                <input id="user_zip" name="user_zip" type="text" value="<?php echo osc_esc_html(isset($user['s_zip']) ? $user['s_zip'] : ''); ?>" required/>
                <i class="fa fa-hashtag"></i>
              </span>
            </p>

            <p class="bt9">
              <label><?php _e('Address', 'osclass_pay'); ?> *</label>
              <span class="osp-input-box">
                <input id="user_address" name="user_address" type="text" value="<?php echo osc_esc_html(isset($user['s_address']) ? $user['s_address'] : ''); ?>" required/>
                <i class="fa fa-map-signs"></i>
              </span>
            </p>

            <p class="bt10">
              <label><?php _e('Phone', 'osclass_pay'); ?> *</label>
              <span class="osp-input-box">
                <input id="user_phone" name="user_phone" type="text" value="<?php echo osc_esc_html(isset($user['s_phone_mobile']) ? $user['s_phone_mobile'] : (isset($user['s_phone_land']) ? $user['s_phone_land'] : '')); ?>" required/>
                <i class="fa fa-phone"></i>
              </span>
            </p>

            <p class="bt11">
              <label><?php _e('Email', 'osclass_pay'); ?> *</label>
              <span class="osp-input-box">
                <input id="user_email" name="user_email" type="text" value="<?php echo osc_esc_html(isset($user['s_email']) ? $user['s_email'] : ''); ?>" required/>
                <i class="fa fa-envelope"></i>
              </span>
            </p>

            <input type="submit" value="<?php echo osc_esc_html(__('Pay', 'osclass_pay')); ?>">
          </form>
        </div>

        <div id="twocheckout-results" style="display:none;"><i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span><?php echo osc_esc_js(__('Processing the payment, please wait...', 'osclass_pay'));?></span></div>
      </div>
    </div>


    <script type="text/javascript">
      // Called when token created successfully.
      var successCallback = function(data) {
        $('#twocheckout-results').html('<i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span><?php echo osc_esc_js(__('Processing the payment, please wait...', 'osclass_pay'));?></span>').show(0);
        $('#twocheckout-dialog .osp-bot').hide(0);
        $('#twocheckout-dialog').css('cssText', $('#twocheckout-dialog').attr('style') + ';height: 300px !important;');

        var myForm = document.getElementById('myCCForm');
        myForm.token.value = data.response.token.token;
        myForm.submit();
      };

      // Called when token creation fails.
      var errorCallback = function(data) {
        if (data.errorCode === 200) {
          tokenRequest();
        } else {
          alert(data.errorMsg);
        }
      };

      var tokenRequest = function() {
        // Setup token request arguments
        var args = {
          sellerId: "<?php echo osp_param('twocheckout_seller_id'); ?>",
          publishableKey: "<?php echo osp_param('twocheckout_publishable_key'); ?>",
          ccNo: $("form#myCCForm #ccNo").val(),
          cvv: $("form#myCCForm #cvv").val(),
          expMonth: $("form#myCCForm #expMonth").val(),
          expYear: $("form#myCCForm #expYear").val()
        };

        TCO.requestToken(successCallback, errorCallback, args);
      };

      $(function() {
        TCO.loadPubKey('<?php echo (osp_param('twocheckout_sandbox') == 1 ? 'sandbox' : 'production'); ?>');

        $("#myCCForm").submit(function(e) {
          tokenRequest();
          $('#twocheckout-dialog input[type="submit"]').addClass('osp-disabled').prop('disabled', true);
          return false;
        });
      });


      function twocheckout_pay(amount, description, itemnumber, extra) {
        $('#twocheckout-itemnumber').attr('value', itemnumber);
        $('#twocheckout-amount').attr('value', amount);
        $('#twocheckout-dialog input[type="submit"]').val($('input[type="submit"]').val() + ' ' + amount + ' <?php echo osp_currency(); ?>');
        $('#twocheckout-description').attr('value', description);
        $('#twocheckout-desc').text(description);
        $('#twocheckout-extra').attr('value', extra);
        //$('#twocheckout-results').html('').hide(0);

        ospTwoCheckoutDialog();
        return false;
      }

      function ospTwoCheckoutDialog() {
        $('#twocheckout-dialog').fadeIn(200).fadeIn(200).css('top', ($(document).scrollTop() + Math.round($(window).height()/10)) + 'px');;
        $('#twocheckout-overlay').fadeIn(200);
        $('.blockchain-btn').css('opacity', '1');
      }

      $('#twocheckout-dialog .osp-close, #twocheckout-overlay').on('click', function(e){ 
        e.stopPropagation();
        $('.osp-custom-dialog').fadeOut(200);
        $('#twocheckout-overlay').fadeOut(200);
        $('.blockchain-btn').css('opacity', '');
      });
    </script>
  <?php
  }


  // AJAX FUNCTION TO PROCESS PAYMENT, HOOKED TO AJAX_TWOCHECKOUT
  public static function ajaxPayment() {
    $response = self::processPayment();      // manage payment itself
    $status = $response[0];

    $data = osp_get_custom(Params::getParam('extra'));
    $product_type = explode('x', $data['product']);
    
    if ($status == OSP_STATUS_COMPLETED) {
      osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), Params::getParam('twocheckout_transaction_id')));
    } else if ($status == OSP_STATUS_ALREADY_PAID) {
      osc_add_flash_warning_message(__('Warning! This payment was already paid.', 'osclass_pay'));
    } else if ($status == OSP_STATUS_AMOUNT_ZERO) {
      osc_add_flash_error_message(__('You are trying to pay zero amount.', 'osclass_pay'));
    } else if ($status == OSP_STATUS_AMOUNT_SMALL) {
      osc_add_flash_error_message(__('You are trying to pay too small amount. 2Checkout accept only payments larger than 0.5.', 'osclass_pay'));
    } else {
      osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay') . ' (' . $response[1] . ')');
    }

    if(OSP_DEBUG) {
      $emailtext = "status => " . $status . "\r\n";
      $emailtext .= osp_array_to_string(Params::getParamsAsArray('post'));
      if(isset($response[1])) {
        $emailtext .= "\r\n ---------- \r\n" . osp_array_to_string($response[1]);
      }
      mail(osc_contact_email() , 'OSCLASS PAY - 2CHECKOUT DEBUG RESPONSE', $emailtext);
    }

    osp_js_redirect_to(osp_pay_url_redirect($product_type));
  }

  
  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment() {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib/Twocheckout.php';
    Twocheckout::verifySSL(false);
    Twocheckout::privateKey(osp_decrypt(osp_param('twocheckout_private_key')));
    Twocheckout::sellerId(osp_param('twocheckout_seller_id'));

    if(osp_param('twocheckout_sandbox') == 1) {
      Twocheckout::sandbox(true);
    } else {
      Twocheckout::sandbox(false);
    }

    $token = Params::getParam('token');
    $data = osp_get_custom(Params::getParam('extra'));
    $product_type = explode('x', $data['product']);
    $amount = round($data['amount'], 2);   // stripe accept just integers up to 2 decimals
    

    if($amount<=0) { 
      return array(OSP_STATUS_AMOUNT_ZERO, ''); 
    }

    try {
      $charge = Twocheckout_Charge::auth(array(
        "sellerId" => osp_param('twocheckout_seller_id'),
        "merchantOrderId" => rand(0, 10000),
        "token" => Params::getParam('token'),
        "currency" => osp_currency(),
        "total" => $amount,
        "billingAddr" => array(
          "name" => Params::getParam('user_name'),
          "addrLine1" => Params::getParam('user_address'),
          "city" => Params::getParam('user_city'),
          "state" => Params::getParam('user_state'),
          "zipCode" => Params::getParam('user_zip'),
          "country" => Params::getParam('user_country'),
          "email" => Params::getParam('user_email'),
          "phone" => Params::getParam('user_phone')
        )
      ));

      if ($charge['response']['responseCode'] == 'APPROVED') {
        Params::setParam('twocheckout_transaction_id', $charge['response']['transactionId']);
        $payment = ModelOSP::newInstance()->getPaymentByCode($charge['response']['transactionId'], '2CHECKOUT');
        $amount = $charge['response']['total'];

        if(!$payment) { 
          // SAVE TRANSACTION LOG
          $payment_id = ModelOSP::newInstance()->saveLog(
            $data['concept'], //concept
            $charge['response']['transactionId'], // transaction code
            $charge['response']['total'], //amount
            strtoupper($charge['response']['currencyCode']), //currency
            $data['email'], // payer's email
            $data['user'], //user
            osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
            $product_type[0], //product type
            '2CHECKOUT' //source
          );


          // Pay it!
          $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
          $pay_item = osp_pay_fee($payment_details);

          return array(OSP_STATUS_COMPLETED, $charge['response']);
        }

        return array(OSP_STATUS_ALREADY_PAID, ''); 
      }
    } catch (Twocheckout_Error $e) {
      return array(OSP_STATUS_FAILED, $e->getMessage()); 
    }
  }
}
?>