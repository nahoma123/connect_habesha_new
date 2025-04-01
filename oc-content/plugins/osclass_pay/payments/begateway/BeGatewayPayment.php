<?php
require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib/BeGateway.php';

class BeGatewayPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    BeGatewayPayment::loadTranslations();
    BeGatewayPayment::initSettings();

    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber;

    $date = date('Y-m-d h:i:s');
    $transaction_id = md5(mb_generate_rand_string(10).$date);

    $pending_data = array(
      'fk_i_user_id' => osc_logged_user_id(),
      's_transaction_id' => $transaction_id,
      's_email' => osc_logged_user_email(),
      's_extra' => $extra,
      's_source' => 'BEGATEWAY',
      'dt_date' => $date
    );

    $order_id = ModelOSP::newInstance()->insertPending($pending_data);

    $SUCCESSURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'success.php?orderId=' . urlencode($order_id) . '&extra=' . urlencode($itemnumber);
    $CANCELURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php?extra=' . urlencode($itemnumber);
    $WEBHOOKURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'webhook.php';
    $WEBHOOKURL = str_replace('0.0.0.0/oc-content','webhook.begateway.com:8443/oc-content', $WEBHOOKURL);
    $WEBHOOKURL = str_replace('0.0.0.0:8080/oc-content','webhook.begateway.com:8443/oc-content', $WEBHOOKURL);

    $token = new \BeGateway\GetPaymentToken;

    $token->money->setCurrency(osp_currency());
    $token->money->setAmount($amount);
    $token->setDescription(mb_substr($description . ' ( ' . $itemnumber . ' )', 0, 254));
    $token->setTrackingId(implode('|', array($transaction_id, $order_id)));

    $name = osc_logged_user_name() <> '' ? osc_logged_user_name() : null;
    if ($name) {
      list($first_name, $last_name) = explode(' ', $name);
      $token->customer->setFirstName($first_name);
      $token->customer->setLastName($last_name);
    }
    $token->customer->setEmail(osc_logged_user_email() <> '' ? osc_logged_user_email() : null);

    $token->setSuccessUrl($SUCCESSURL);
    $token->setDeclineUrl($CANCELURL);
    $token->setFailUrl($CANCELURL);
    $token->setCancelUrl($CANCELURL);
    $token->setNotificationUrl($WEBHOOKURL);
    $token->setLanguage(osp_get_locale());
    list($locale, $code) = explode('_', osp_get_locale());
    $token->setLanguage($locale);

    # set payment timeout 1 hour
    $timeout = intval(osp_param('begateway_timeout'));
    if ($timeout > 0) {
      $token->setExpiryDate(date("c", $timeout * 60 + time() + 1));
    }

    $token->setTestMode(intval(osp_param('begateway_test_mode')) == 1);

    $response = $token->submit();

    if(!$response->isSuccess()) {
      echo '<li class="payment begateway-btn"><a class="osp-has-tooltip osp-disabled" disabled="disabled" title="' . osc_esc_html(sprintf(__('Error to get a payment token. Reason: %s', 'osclass_pay'), $response->getMessage())) . '" href="#" onclick="return false;" ><span><img src="' . osp_url() . 'payments/begateway/img/paymentlogo.png"/></span><strong>' . __('Pay with BeGateway', 'osclass_pay') . '</strong></a></li>';
    } else {
      echo '<li class="payment stripe-btn"><a class="osp-has-tooltip" title="' . osc_esc_html(__('Form to complete payment will pop-up', 'osclass_pay')) . '" href="#" onclick="start_begateway_payment(\'' . \BeGateway\Settings::$checkoutBase . '\', \''. $response->getToken() . '\', \''. $CANCELURL . '\');return false;" ><span><img src="' . osp_url() . 'payments/begateway/img/paymentlogo.png"/></span><strong>' . __('Pay with BeGateway', 'osclass_pay') . '</strong></a></li>';
      ModelOSP::newInstance()->updatePendingTransaction($order_id, $response->getToken());
    }
  }

  // POPUP JS DIALOG
  public static function dialogJS() { ?>
    <script type="text/javascript">

      this.start_begateway_payment = function(url, token, cancel_url) {
        var params = {
          checkout_url: url,
          token: token,
          closeWidget: function(status) {
            if (status == null) {
              window.location.replace(cancel_url);
            }
          }
        };

        new BeGateway(params).createWidget();
      };
    </script>
  <?php
  }

  // PROCESS PAYMENT NOTIFICATION ON PLUGIN SIDE
  public static function processNotification($webhook) {
    BeGatewayPayment::initSettings();

    if(!$webhook->isAuthorized()) {
      return array(OSP_STATUS_INVALID, __('Failed - unable to authorize webhook', 'osclass_pay'));
    }

    list($transaction_id, $order_id) = explode('|', $webhook->getTrackingId());

    if($order_id > 0) {
      $pending = ModelOSP::newInstance()->getPendingById($order_id);
      $transaction_id = $pending['s_transaction_id'];
    } else {
      $pending = ModelOSP::newInstance()->getPendingByTransactionId($transaction_id, 'BEGATEWAY');
    }

    if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
      return array(OSP_STATUS_INVALID, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay'));
    }

    $money = new \BeGateway\Money;
    $money->setCents($webhook->getResponse()->transaction->amount);
    $money->setCurrency($webhook->getResponse()->transaction->currency);

    if ($money->getCents() <= 0) {
      return array(OSP_STATUS_AMOUNT_ZERO,'');
    }

    $data = osp_get_custom($pending['s_extra']);
    $product_type = explode('x', $data['product']);
    $amount = $money->getAmount();

    $payment = ModelOSP::newInstance()->getPaymentByCode($webhook->getUid(), 'BEGATEWAY');

    if($webhook->isSuccess()) {
      if(!$payment) {
        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $data['concept'], //concept
          $webhook->getUid(), // transaction code
          $amount, //amount
          strtoupper($money->getCurrency()), //currency
          $data['email'], // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $product_type[2]), // cart string
          $product_type[0], //product type
          'BEGATEWAY' //source
        );

        // Pay it!
        $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);

        // Remove pending row
        ModelOSP::newInstance()->deletePending($pending['pk_i_id']);


        return array(OSP_STATUS_COMPLETED, '');
      }

      return array(OSP_STATUS_ALREADY_PAID, '');
    }

    return array(OSP_STATUS_FAILED, __('Payment does not have successful status in BEGATEWAY system', 'osclass_pay'));
  }

  public static function processPayment($uid) {
    $payment = ModelOSP::newInstance()->getPaymentByCode($uid, 'BEGATEWAY');

    if($payment) {
      return array(OSP_STATUS_ALREADY_PAID, '');
    } else {
      return array(OSP_STATUS_PENDING, __('We are processing your payment!', 'osclass_pay'));
    }
  }

  public static function loadTranslations() {
    $locale = osc_current_user_locale();
    $domain = 'osclass_pay_begateway';
    $locale_file = osc_plugins_path() . osc_plugin_folder(__FILE__) . 'languages/' . $locale . '/messages.mo';
    $plugin_file = osc_apply_filter('mo_plugin_path', $locale_file, $locale, $domain);
    if(file_exists($plugin_file) ) {
      Translation::newInstance()->_load($plugin_file, $domain);
    }
  }

  public static function initSettings() {
    \BeGateway\Settings::$checkoutBase = 'https://' . osp_param('begateway_domain_checkout');
    \BeGateway\Settings::$shopId = osp_param('begateway_shop_id');
    \BeGateway\Settings::$shopPubKey = osp_param('begateway_public_key');
    \BeGateway\Settings::$shopKey = osp_decrypt(osp_param('begateway_secret_key'));
  }
}
?>
