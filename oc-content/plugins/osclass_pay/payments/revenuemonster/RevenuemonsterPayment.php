<?php
error_reporting(E_ALL ^ E_DEPRECATED);


require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'src/rm-php-sdk-2.0.3/vendor/autoload.php';

use RevenueMonster\SDK\RevenueMonster;
use RevenueMonster\SDK\Exceptions\ApiException;
use RevenueMonster\SDK\Exceptions\ValidationException;
use RevenueMonster\SDK\Request\WebPayment;
use RevenueMonster\SDK\Request\QRPay;
use RevenueMonster\SDK\Request\QuickPay;

class RevenuemonsterPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
   
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber;

    $store_id = osp_param('revenuemonster_store_id');
    $client_id = osp_param('revenuemonster_client_id');
    $client_secret = osp_decrypt(osp_param('revenuemonster_client_secret'));
   
    $pending_data = array(
      'fk_i_user_id' => osc_logged_user_id(),
      's_email' => osc_logged_user_email(),
      's_extra' => $extra,
      's_source' => 'REVENUEMONSTER',
      'dt_date' => date('Y-m-d h:i:s')
    );

    $order_id = ModelOSP::newInstance()->insertPending($pending_data);

    $SUCCESSURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'success.php?pendingId=' . $order_id;
    $WEBHOOK = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'webhook.php';


    // Initialise sdk instance
    $rm = new RevenueMonster([
      'clientId' => $client_id,
      'clientSecret' => $client_secret,
      'privateKey' => file_get_contents(osc_plugins_path() . osc_plugin_folder(__FILE__) . 'key/private_key.pem'),
      'isSandbox' => false,
    ]);

    $PAYMENT_URL = '';

    // create Web payment
    try {
      $wp = new WebPayment;
      $wp->order->id = strval($order_id);
      $wp->order->title = $description;
      $wp->order->currencyType = osp_currency();
      $wp->order->amount = round($amount*100);
      $wp->order->detail = $itemnumber;
      $wp->order->additionalData = '';
      $wp->storeId = $store_id;
      $wp->redirectUrl = $SUCCESSURL;
      $wp->notifyUrl = $WEBHOOK;
      $wp->layoutVersion = 'v1';
      
      $response = $rm->payment->createWebPayment($wp);
      $checkout_id = $response->checkoutId;
      
      ModelOSP::newInstance()->updatePendingTransaction($order_id, $checkout_id);
      
      $PAYMENT_URL = $response->url; // Payment gateway url
      
    } catch(ApiException $e) {
      echo "statusCode : {$e->getCode()}, errorCode : {$e->getErrorCode()}, errorMessage : {$e->getMessage()}";
    } catch(ValidationException $e) {
      var_dump($e->getMessage());
    } catch(Exception $e) {
      echo $e->getMessage();
    }

    if($PAYMENT_URL != '') {
    ?>
    <li>
      <a id="osp-button-confirm" class="button osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to RevenueMonster', 'osclass_pay')); ?>" href="<?php echo $PAYMENT_URL; ?>">
        <span><img src="<?php echo osp_url(); ?>img/payments/revenuemonster.svg"/></span>
        <strong><?php _e('Pay with RevenueMonster', 'osclass_pay'); ?></strong>
      </a>
    </li>
    <?php
    }
  }

  // GET TRANSACTION BY ID
  public static function getTransactionById($id) {
    $client_id = osp_param('revenuemonster_client_id');
    $client_secret = osp_decrypt(osp_param('revenuemonster_client_secret'));

    $rm = new RevenueMonster([
      'clientId' => $client_id,
      'clientSecret' => $client_secret,
      'privateKey' => file_get_contents(osc_plugins_path() . osc_plugin_folder(__FILE__) . 'key/private_key.pem'),
      'isSandbox' => false,
    ]);
    
    
    // Find transaction by transaction id
    try {
      $transactionId = $id;
      $response = $rm->payment->find($transactionId);
      return array('status' => 'OK', 'data' => json_decode(json_encode($response), true));
      
    } catch(ApiException $e) {
      $message = "statusCode : {$e->getCode()}, errorCode : {$e->getErrorCode()}, errorMessage : {$e->getMessage()}";
      return array('status' => 'ERR', 'message' => $message);

    } catch(Exception $e) {
      $message = $e->getMessage();
      return array('status' => 'ERR', 'message' => $message);
    }
  }

  // GET TRANSACTION BY ID
  public static function getTransactionByOrderId($id) {
    $client_id = osp_param('revenuemonster_client_id');
    $client_secret = osp_decrypt(osp_param('revenuemonster_client_secret'));
    
    $rm = new RevenueMonster([
      'clientId' => $client_id,
      'clientSecret' => $client_secret,
      'privateKey' => file_get_contents(osc_plugins_path() . osc_plugin_folder(__FILE__) . 'key/private_key.pem'),
      'isSandbox' => false,
    ]);
    
    // Find transaction by order id
    try {
      $orderId = $id;
      $response = $rm->payment->findByOrderId($orderId);
      return array('status' => 'OK', 'data' => json_decode(json_encode($response), true));
      
    } catch(ApiException $e) {
      $message = "statusCode : {$e->getCode()}, errorCode : {$e->getErrorCode()}, errorMessage : {$e->getMessage()}";
      return array('status' => 'ERR', 'message' => $message);

    } catch(Exception $e) {
      $message = $e->getMessage();
      return array('status' => 'ERR', 'message' => $message);
    }
  }


  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment($order_id, $webhook_data = array()) {
    $transaction_id = '';
    $transaction = array();
    $client_id = osp_param('revenuemonster_client_id');
    $client_secret = osp_decrypt(osp_param('revenuemonster_client_secret'));

    $pending = ModelOSP::newInstance()->getPendingById($order_id);

    if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
      return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
    }

    $data = osp_get_custom($pending['s_extra']);
    $product_type = explode('x', $data['product']);
    //$amount = round($data['amount']/100, 2); 
    $amount = round($data['amount'], 2); 
    
    if($amount<=0) { 
      return array(OSP_STATUS_AMOUNT_ZERO); 
    }

    $payment = ModelOSP::newInstance()->getPaymentByCode($pending['s_transaction_id'], 'REVENUEMONSTER');
    
    if($pending === false || !isset($pending['s_transaction_id']) || trim((string)$pending['s_transaction_id']) == '') {
      return array(OSP_STATUS_FAILED, __('Pending record does not have properly filled transaction ID (is empty)', 'osclass_pay'));
    }
    
    $is_paid = false;
    $is_pending = false;
    
    if(isset($webhook_data['transactionId'])) {
      $transaction_id = $webhook_data['transactionId'];
    } 
    
    if($transaction_id <> '') {
      $res = RevenuemonsterPayment::getTransactionById($transaction_id);
      
      if(isset($res['status']) && $res['status'] == 'OK') {
        $transaction = $res['data'];
      }
    }
    
    if(empty($transaction)) {
      $res = RevenuemonsterPayment::getTransactionByOrderId($order_id);
      
      if(isset($res['status']) && $res['status'] == 'OK') {
        $transaction = $res['data'];
        $transaction_id = @$transaction['transactionId'];
      }
    }

   
    if(isset($transaction['status']) && $transaction['status'] == 'SUCCESS') {
      $is_paid = true;
    } else {
      $is_pending = true;     // IN_PROCESS
    }
    
    if(isset($transaction['status']) && $transaction['status'] == 'FAILED') {
      return array(OSP_STATUS_FAILED, '');
    }
    
    if(isset($webhook_data['status']) && $webhook_data['status'] == 'SUCCESS') {
      $is_paid = true;
    }

    if($transaction_id == '') {
      return array(OSP_STATUS_FAILED, __('Could not identify transaction ID', 'osclass_pay')); 
    }


    ModelOSP::newInstance()->updatePendingTransaction($order_id, $transaction_id);
    Params::setParam('revenuemonster_transaction_id', $transaction_id);

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
          'REVENUEMONSTER' //source
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