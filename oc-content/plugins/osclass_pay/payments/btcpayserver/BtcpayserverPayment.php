<?php
// https://docs.btcpayserver.org/Development/GreenfieldExample-PHP/
// https://github.com/btcpayserver/btcpayserver-greenfield-php
// https://github.com/btcpayserver/btcpayserver-greenfield-php/blob/master/examples/create_invoice.php

require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib/btcpayserver-greenfield-php-master/src/autoload.php';

use BTCPayServer\Client\Invoice;
use BTCPayServer\Client\InvoiceCheckoutOptions;
use BTCPayServer\Client\Webhook;
use BTCPayServer\Util\PreciseNumber;

class BtcpayserverPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber;


    // 'secret_key' => osp_decrypt(osp_param('stripe_secret_key_test')),
    // 'publishable_key' => osp_decrypt(osp_param('stripe_public_key_test'))


    $pending_data = array(
      'fk_i_user_id' => osc_logged_user_id(),
      's_email' => osc_logged_user_email(),
      's_extra' => $extra,
      's_source' => 'BTCPAYSERVER',
      'dt_date' => date('Y-m-d h:i:s')
    );

    $order_id = ModelOSP::newInstance()->insertPending($pending_data);

    $SUCCESSURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'success.php?orderId=' . $order_id;
    $CANCELURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php';
    $WEBHOOK = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'webhook.php';

    
    // Fill in with your BTCPay Server data.
    // $api_key = '372a2f575d2ee2a9f6b31d58a2dd281fb77e95f4';
    // $host = 'https://btcpay525902.lndyn.com/';
    // $store_id = '9sHXcKe4UvBcskCJ7CHWPfVxk9uJ4sGK7SmakWvDci4L';

    $api_key = osp_param('btcpayserver_api_key');
    $host = osp_param('btcpayserver_host');
    $store_id = osp_param('btcpayserver_store_id');

    $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
    
    try {
      $client = new Invoice($host, $api_key);

      // Setup custom metadata. This will be visible in the invoice and can include
      // arbitrary data. Example below will show up on the invoice details page on
      // BTCPay Server.
      $metaData = [
        'buyerName' => $user['s_name'],
        'buyerAddress1' => $user['s_address'],
        //'buyerAddress2' => 'Door 3',
        'buyerCity' => $user['s_city'],
        //'buyerState' => 'IL',
        'buyerZip' => $user['s_zip'],
        'buyerCountry' => $user['fk_c_country_code'],
        'buyerPhone' => $user['s_phone_mobile'],
        //'posData' => 'Data shown on the invoice details go here. Can be JSON encoded string',
        'itemDesc' => $description,
        'itemCode' => $itemnumber,
        'physical' => false, // indicates if physical product
        'taxIncluded' => $amount, // tax amount (included in the total amount).
      ];

      // Setup custom checkout options, defaults get picked from store config.
      $checkoutOptions = new InvoiceCheckoutOptions();
      $checkoutOptions
        ->setSpeedPolicy($checkoutOptions::SPEED_HIGH)
        //->setPaymentMethods(['BTC'])
        ->setRedirectURL($SUCCESSURL);

      $invoice = $client->createInvoice(
        $store_id,
        osp_currency(),
        PreciseNumber::parseString($amount),
        $order_id,
        osc_logged_user_email(),
        $metaData,
        $checkoutOptions
      );
    } catch (\Throwable $e) {
      echo "Error: " . $e->getMessage();
    }


    ModelOSP::newInstance()->updatePendingTransaction($order_id, $invoice->getId());

    
    echo '<li class="payment btcpayserver-btn"><a class="osp-has-tooltip" title="' . osc_esc_html(__('Form to enter payment details will pop-up', 'osclass_pay')) . '" href="' . $invoice->getCheckoutLink() . '"><span><img src="' . osp_url() . 'img/payments/btcpayserver.png"/></span><strong>' . __('Pay with BTCPayServer', 'osclass_pay') . '</strong></a></li>';
  }


  
  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment($return = false) {
    // $api_key = '372a2f575d2ee2a9f6b31d58a2dd281fb77e95f4';
    // $host = 'https://btcpay525902.lndyn.com/';
    // $store_id = '9sHXcKe4UvBcskCJ7CHWPfVxk9uJ4sGK7SmakWvDci4L';
    // $secret = '47jQGv7LJbvFJ4AuERh23vptmmD2';

    $api_key = osp_param('btcpayserver_api_key');
    $host = osp_param('btcpayserver_host');
    $store_id = osp_param('btcpayserver_store_id');
    $secret = osp_decrypt(osp_param('btcpayserver_webhook_secret'));

    
    if($return) {
      $order_id = Params::getParam('orderId');
      $pending = ModelOSP::newInstance()->getPendingById($order_id);
      $invoice_id = isset($pending['s_transaction_id']) ? $pending['s_transaction_id'] : '';
      
    } else {
      
      // Get response from IPN
      $raw_post_data = file_get_contents('php://input');

      if (false === $raw_post_data || $raw_post_data == '') {
        return array(OSP_STATUS_FAILED, __('Could not read from the php://input stream or invalid BTCPayServer payload received.', 'osclass_pay')); 
      }

      $payload = json_decode($raw_post_data, false, 512, JSON_THROW_ON_ERROR);

      if (empty($payload)) {
        return array(OSP_STATUS_FAILED, __('Could not decode the JSON payload from BitPay.', 'osclass_pay')); 
      }


      // verify hmac256
      $headers = getallheaders();
      foreach ($headers as $key => $value) {
        if (strtolower($key) === 'btcpay-sig') {
          $sig = $value;
        }
      }

      
      $webhookClient = new Webhook($host, $api_key);


      if (!$webhookClient->isIncomingWebhookRequestValid($raw_post_data, $sig, $secret)) {
        return array(OSP_STATUS_FAILED, __('Invalid BTCPayServer payment notification message received - signature did not match.', 'osclass_pay')); 
      }
      
      
      if (true === empty($payload->invoiceId)) {
        return array(OSP_STATUS_FAILED, __('Invalid BTCPayServer payment notification message received - did not receive invoice ID.', 'osclass_pay')); 
      }

      // optional: check whether your webhook is of the desired type
      if ($payload->type !== "InvoiceSettled") {
        return array(OSP_STATUS_FAILED, __('Invalid payload message type. Only InvoiceSettled is supported, check the configuration of the webhook.', 'osclass_pay')); 
      }
    
      $invoice_id = $payload->invoiceId;
      $pending = ModelOSP::newInstance()->getPendingByTransactionId($invoice_id);
    }
    

    // Load an existing invoice with the provided invoiceId.
    // Most of the time this is not needed as you can listen to specific webhook events
    // See: https://docs.btcpayserver.org/API/Greenfield/v1/#tag/Webhooks/paths/InvoiceCreated/post
    try {
      $client = new Invoice($host, $api_key);
      $invoice = $client->getInvoice($store_id, $invoice_id);
    } catch (\Throwable $e) {
      throw $e;
    }
    

    // $invoicePrice = $invoice->getData()['amount'];
    // $buyerEmail = $invoice->getData()['metadata']['buyerEmail'];
    
    if($invoice_id == '') {
      return array(OSP_STATUS_FAILED, __('Failed - invoice ID is empty', 'osclass_pay')); 
    }
    
    if(strtolower($invoice->getStatus()) == 'processing' || strtolower($invoice->getStatus()) == 'new') {
      return array(OSP_STATUS_PENDING, ''); 
    }

    if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
      return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
    }

    $data = osp_get_custom($pending['s_extra']);
    $product_type = explode('x', $data['product']);
    $amount = round($data['amount'], 2);   // stripe accept just integers up to 2 decimals
    
    if($amount<=0) { 
      return array(OSP_STATUS_AMOUNT_ZERO); 
    }

    $payment = ModelOSP::newInstance()->getPaymentByCode($pending['s_transaction_id'], 'BTCPAYSERVER');


    Params::setParam('btcpayserver_transaction_id', $invoice_id);
    Params::setParam('btcpayserver_product_type', $product_type);

    $status = strtolower($invoice->getData()['status']);

    if($status == 'settled' || $status == 'paid' || $status == 'confirmed') {
      if(!$payment) {
        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $data['concept'], //concept
          $invoice_id, // transaction code (payment intent starting with pi_)
          $amount, //amount
          strtoupper(osp_currency()), //currency
          $data['email'], // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $product_type[2]), // cart string
          $product_type[0], //product type
          'BTCPAYSERVER' //source
        );


        // Pay it!
        $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);


        // Remove pending row
        //ModelOSP::newInstance()->deletePending($pending['pk_i_id']);

        return array(OSP_STATUS_COMPLETED, '');
      }

      if($return) {
        return array(OSP_STATUS_COMPLETED, '');
      } else {
        return array(OSP_STATUS_ALREADY_PAID, ''); 
      }
      
    } else {
      return array(OSP_STATUS_FAILED);
      
    }
  }
}
?>