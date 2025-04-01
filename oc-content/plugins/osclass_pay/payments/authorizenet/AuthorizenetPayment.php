<?php
// https://github.com/vadim-job-hg/Autorize/tree/master/authorizenet/vendor/anet_php_sdk

class AuthorizenetPaymentOSP {
  public function __construct() {}

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    $r = rand(0,1000);
    $extra .= '|random,'.$r;

    echo '<li class="payment authorizenet-btn"><a class="osp-has-tooltip" title="' . osc_esc_html(__('Form to enter credit card details will pop-up', 'osclass_pay')) . '" href="#" onclick="authorizenet_pay(\''.$amount.'\',\''.$description.'\',\''.$itemnumber.'\',\''.$extra.'\');return false;" >';
    echo '<span><img src="' . osp_url() . 'img/payments/authorize.png"/></span>';
    echo '<strong>' . __('Pay with Authorize.net', 'osclass_pay') . '</strong>';
    echo '</a></li>';
  }

  public static function dialogJS() { ?>
    <div id="authorizenet-dialog" title="<?php _e('Authorize.Net', 'osclass_pay'); ?>" style="display:none;">
      <div id="authorizenet-info">
        <div id="authorizenet-data">
          <p id="authorizenet-img"><img src="<?php echo osp_url(); ?>img/payments/authorize-cards.png"/></p>
          <p id="authorizenet-text"></p>
          <label><?php _e('Payment amount', 'osclass_pay'); ?></label>
          <p id="authorizenet-price"></p>
        </div>

        <form class="nocsrf" action="<?php echo osc_base_url(true); ?>" method="POST" id="authorizenet-payment-form">
          <input type="hidden" name="page" value="ajax" />
          <input type="hidden" name="action" value="runhook" />
          <input type="hidden" name="hook" value="authorizenet" />
          <input type="hidden" name="description" id="authorizenet-description" value=""/>
          <input type="hidden" name="extra" id="authorizenet-extra" value=""/>

          <p class="bt1">
            <label><?php _e('Card number', 'osclass_pay'); ?></label>
            <span class="osp-input-box">
              <input id="ccNo" name="ccNo" type="text" size="20" value="" autocomplete="off" required maxlength="20"/>
              <i class="fa fa-credit-card"></i>
            </span>
          </p>

          <p class="bt2">
            <label><?php _e('Expiration (MM/YYYY)', 'osclass_pay'); ?></label>
            <span class="osp-input-box">
              <input type="text" size="2" id="expMonth" name="expMonth" required maxlength="2"/>
              <span class="osp-del">/</span>
              <input type="text" size="4" id="expYear" name="expYear" required maxlength="4"/>
              <i class="fa fa-calendar"></i>
            </span>
          </p>

          <p class="bt3">
            <label><?php _e('CVV', 'osclass_pay'); ?></label>
            <span class="osp-input-box">
              <input id="cvvCode" name="cvvCode" size="4" type="text" value="" autocomplete="off" required maxlength="3"/>
              <i class="fa fa-lock"></i>
            </span>
          </p>

          <button id="authorizenet-submit" type="submit"><?php echo osc_esc_html(__('Pay', 'osclass_pay')); ?> <span></span></button>
        </form>
      </div>

      <div id="authorizenet-results"><i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span><?php echo osc_esc_js(__('Processing the payment, please wait...', 'osclass_pay'));?></span></div>
      <div id="authorizenet-response"></div>
    </div>

    <script type="text/javascript">
      var authorizenet_ajax = function () {
        $("#authorizenet-submit").prop('disabled', true);
        $("#authorizenet-results").html('<i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span><?php echo osc_esc_js(__('Processing the payment, please wait...', 'osclass_pay'));?></span>').show(0);
        $("#authorizenet-dialog form, #authorizenet-info").hide(0);
        $.ajax({
          type: 'POST',
          url: '<?php echo osc_base_url(true); ?>',
          data: $("form#authorizenet-payment-form").serialize(),
          success: function(data) {
            $("#authorizenet-response").html(data);
          }
        });
      };

      $("form#authorizenet-payment-form").submit(function(e) {
        e.preventDefault();
        authorizenet_ajax();
        return false;
      });

      function authorizenet_pay(amount, description, itemnumber, extra) {
        $("#authorizenet-extra").prop('value', extra);
        $("#authorizenet-description").prop('value', description);
        $("#authorizenet-text").html(description);
        $("#authorizenet-price").html(amount + ' <?php echo osp_currency(); ?>');
        $("#authorizenet-results").html('').hide(0);
        $("#authorizenet-submit").prop('disabled', false).removeClass('osp-disabled');
        $("#authorizenet-submit > span").text(amount + ' <?php echo osp_currency(); ?>');
        $("#authorizenet-info, #authorizenet-dialog form").show(0);
        $("#authorizenet-dialog").dialog('open');
      }

      $(document).ready(function(){
        $("#authorizenet-dialog").dialog({
          autoOpen: false,
          dialogClass: "osp-dialog authorizenet-dialog",
          modal: true,
          show: { effect: 'fade', duration: 200 },
          hide: { effect: 'fade', duration: 200 },
          open: function(event, ui) {
            $('.authorizenet-dialog .ui-dialog-title').html('<img src="<?php echo osp_url(); ?>img/payments/white/authorize.png"/>');
          }
        });
      });
    </script>
  <?php
  }


  public static  function ajaxPayment() {
    $response = AuthorizenetPaymentOSP::processPayment();
    $status = $response[0];

    $data = osp_get_custom(Params::getParam('extra'));
    $product_type = explode('x', $data['product']);
      
    if ($status == OSP_STATUS_COMPLETED) {
      osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), Params::getParam('authorizenet_transaction_id')));
    } else if ($status == OSP_STATUS_ALREADY_PAID) {
      osc_add_flash_warning_message(__('Warning! This payment was already paid.', 'osclass_pay'));
    } else if ($status == OSP_STATUS_AMOUNT_ZERO) {
      osc_add_flash_error_message(__('You are trying to pay zero amount.', 'osclass_pay'));
    } else {
      osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay') . ' (' . $response[1] . ')');
    }

    if(OSP_DEBUG) {
      $emailtext = "status => " . $status . "\r\n";
      //$emailtext .= osp_array_to_string(Params::getParamsAsArray('post'));
      if(isset($response[1])) {
        $emailtext .= "\r\n ---------- \r\n" . osp_array_to_string($response[1]);
      }
      mail(osc_contact_email() , 'OSCLASS PAY - AUTHORIZE.NET DEBUG RESPONSE (AJAX)', $emailtext);
    }

    osp_js_redirect_to(osp_pay_url_redirect($product_type));
  }


  public static function processPayment() {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib/_AuthorizeNetLoad.php';

    define("AUTHORIZENET_API_LOGIN_ID", osp_param('authorizenet_merchant_login_id'));
    define("AUTHORIZENET_TRANSACTION_KEY", osp_decrypt(osp_param('authorizenet_merchant_transaction_key')));
    define("AUTHORIZENET_SANDBOX", osp_param('authorizenet_sandbox') == 1 ? 'true' : 'false');

    if(OSP_DEBUG) {
      // define("AUTHORIZENET_LOG_FILE", osc_plugins_path() . osc_plugin_folder(__FILE__) . 'log.txt');
    }


    $data = osp_get_custom(Params::getParam('extra'));
    $product_type = explode('x', $data['product']);
    $amount = round($data['amount'], 2);

    if($amount <= 0) { 
      return OSP_STATUS_AMOUNT_ZERO; 
    }


    $merchant = (object)array();
    $merchant->login = AUTHORIZENET_API_LOGIN_ID;
    $merchant->tran_key = AUTHORIZENET_TRANSACTION_KEY;
    $merchant->allow_partial_auth = "false";

    $transaction = array(
      'amount' => $amount,
      'duplicate_window' => '10'
    );

    $creditCard = array(
      'exp_date' => Params::getParam('expMonth') . Params::getParam('expYear'),
      'card_num' => Params::getParam('ccNo'),
      'card_code' => Params::getParam('cvvCode')
    );

    $charge = new AuthorizeNetAIM;
    $charge->setFields($creditCard);
    $charge->setFields($customer);
    $charge->setFields($merchant);
    $charge->setFields($transaction);
    //$charge->addLineItem($data['product'], osp_product_type_name($product_type[0]), $data['concept'], '1', $amount, 'N');


    $response = $charge->authorizeAndCapture();
  
    if ($response->approved) {
      if($response->transaction_id == '' || $response->transaction_id == 0) {
        Params::setParam('authorizenet_transaction_id', mb_generate_rand_string(10));
      } else {
        Params::setParam('authorizenet_transaction_id', $response->transaction_id);
      }
      

      $payment = ModelOSP::newInstance()->getPaymentByCode(Params::getParam('authorizenet_transaction_id'), 'AUTHORIZE');

      if(!$payment) {
        $product_type = explode('x', $data['product']);
        
        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $data['concept'], //concept
          Params::getParam('authorizenet_transaction_id'), // transaction code
          $response->amount, //amount
          'USD', //currency, just dollar enabled
          $data['email'], // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
          $product_type[0], //product type
          'AUTHORIZE' //source
        );


        // Pay it!
        $payment_details = osp_prepare_payment_data($response->amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);

        return array(OSP_STATUS_COMPLETED, $response);
      }

      return array(OSP_STATUS_ALREADY_PAID, $response); 
    }

    $error = $response->response_reason_code . ' ' . $response->response_reason_text;
    return array(OSP_STATUS_FAILED, $error); 
  }
}     
?>