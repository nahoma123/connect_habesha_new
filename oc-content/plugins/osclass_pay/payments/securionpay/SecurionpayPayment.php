<?php
require_once osc_lib_path() . 'plugins/osclass_pay/payments/securionpay/lib/SecurionPay/Util/SecurionPayAutoloader.php';
\SecurionPay\Util\SecurionPayAutoloader::register();

use SecurionPay\SecurionPayGateway;
use SecurionPay\Request\CheckoutRequestCharge;
use SecurionPay\Request\CheckoutRequest;
use SecurionPay\Response\Charge;


class SecurionpayPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {


    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    $r = rand(0,1000);
    
    $amount = round($amount*100);

    $public_key = osp_param('securionpay_public_key');
    $secret_key = osp_decrypt(osp_param('securionpay_secret_key'));

    
    $pending_data = array(
      'fk_i_user_id' => osc_logged_user_id(),
      's_email' => osc_logged_user_email(),
      's_extra' => $extra,
      's_source' => 'SECURIONPAY',
      'dt_date' => date('Y-m-d h:i:s')
    );

    $order_id = ModelOSP::newInstance()->insertPending($pending_data);
    
    $RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php';
    $WEBHOOKURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'webhook.php';
    
    $securionPay = new SecurionPayGateway($secret_key);
    $checkoutCharge = new CheckoutRequestCharge();
    $checkoutCharge->amount($amount)->currency(osp_currency())->metadata(
      array(
        'orderId' => $order_id,
        'amount' => $amount/100,
        'fk_i_user_id' => osc_logged_user_id(),
        's_email' => osc_logged_user_email(),
        's_extra' => $extra,
        's_source' => 'SECURIONPAY',
        'dt_date' => date('Y-m-d h:i:s')
      )
    );

    $checkoutRequest = new CheckoutRequest();
    $checkoutRequest->charge($checkoutCharge);

    $signedCheckoutRequest = $securionPay->signCheckoutRequest($checkoutRequest);
    ?>

    <li class="payment securionpay-btn">
      <script src="https://securionpay.com/checkout.js"></script>
      <script type="text/javascript">
        $(function () {
          SecurionpayCheckout.key = '<?php echo osc_esc_js($public_key); ?>';
          SecurionpayCheckout.success = function (result) {
            // handle successful payment (e.g. send payment data to your server)
            //$.post("<?php echo $RETURNURL; ?>", result, function(result){});
            //window.location.reload();
            if(result.charge.id !== undefined) {
              window.location.replace("<?php echo $RETURNURL; ?>?chargeId=" + result.charge.id);
            } else {
              window.location.replace("<?php echo $RETURNURL; ?>?errorMessage=<?php echo osc_esc_html(__('Charge ID is missing, payment could not be processed.', 'osclass_pay')); ?>");
            }

            return false;
          };
          SecurionpayCheckout.error = function (errorMessage) {
            // handle integration errors (e.g. send error notification to your server)
            //window.location.replace("<?php echo $RETURNURL; ?>?errorMessage=" + errorMessage);
            //console.log(errorMessage);
          };
        
          $('a.securionpay-button').click(function (e) {
            SecurionpayCheckout.open({
              checkoutRequest: '<?php echo $signedCheckoutRequest; ?>',
              name: '<?php echo osc_page_title(); ?>',
              description: '<?php echo osc_esc_js($description); ?>'
            });
          });
        });
      </script>
    
      <a id="osp-button-confirm" href="#" class="button securionpay-button osp-has-tooltip" title="<?php echo osc_esc_html(__('Pop-up payment window will be opened', 'osclass_pay')); ?>">
        <span><img src="<?php echo osp_url(); ?>img/payments/securionpay.svg"/></span>
        <strong><?php _e('Pay with SecurionPay', 'osclass_pay'); ?></strong>
      </a>
    </li>
  <?php
  }


  public static function processPayment($charge_id = '') {
    $public_key = osp_param('securionpay_public_key');
    $secret_key = osp_decrypt(osp_param('securionpay_secret_key'));
    $params = Params::getParamsAsArray();
    
    if($charge_id == '') {
      if(isset($params['charge']) && isset($params['charge']['id'])) {
        $charge_id = $params['charge']['id'];
      } else if(isset($params['chargeId'])) {
        $charge_id = $params['chargeId'];
      } else if(isset($params['charge_id'])) {
        $charge_id = $params['charge_id'];
      } else if(isset($params['data']) && isset($params['data']['id'])) {
        $charge_id = $params['data']['id'];
      }
    }
    
    if($charge_id == '') {
      return array(OSP_STATUS_FAILED, '');
    }

    $securionPay = new SecurionPayGateway($secret_key);
    $charge = $securionPay->retrieveCharge($charge_id);
    
    $metadata = $charge->getMetadata();
    $order_id = @$metadata['orderId'];
    $amount = $charge->getAmount()/100;
    $currency = $charge->getCurrency();
    
    $captured = $charge->getCaptured();
    $tx = $charge->getId();
    
    
    // it is valid response
    if($tx == '' || $order_id == '') {
      return array(OSP_STATUS_FAILED, '');
    } else if ($captured == 1) {
      Params::setParam('securionpay_transaction_id', $tx);
      $pending = ModelOSP::newInstance()->getPendingById($order_id);
      
      if($amount <= 0) { 
        return array(OSP_STATUS_AMOUNT_ZERO, ''); 
      }
      
      $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'SECURIONPAY');

      if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
        if(!$payment) {
          return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
        } else {
          return array(OSP_STATUS_COMPLETED, '');
        }
      }
      
      $extra = $pending['s_extra'];     // get pending row
      Params::setParam('extra', @$pending['s_extra']);
      $data = osp_get_custom($extra);
      $product_type = explode('x', @$data['product']);
    
      if(!$payment) { 
        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $data['concept'], //concept
          $tx, // transaction code
          $amount, //amount
          strtoupper($currency), //currency
          $data['email'], // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
          $product_type[0], //product type
          'SECURIONPAY' //source
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

    return array(OSP_STATUS_PENDING, __('We are processing your payment!', 'osclass_pay')); 
  } 
}
?>