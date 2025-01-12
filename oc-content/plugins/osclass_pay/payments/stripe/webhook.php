<?php
define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
require_once ABS_PATH . 'oc-load.php';
require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'lib-sca/init.php';

// This is your test secret API key.

// Replace this endpoint secret with your endpoint's unique secret
// If you are testing with the CLI, find the secret by running 'stripe listen'
// If you are using an endpoint defined with the API or dashboard, look in your webhook settings
// at https://dashboard.stripe.com/webhooks

if(osp_param('stripe_sandbox') == 0) {
  \Stripe\Stripe::setApiKey(osp_decrypt(osp_param('stripe_secret_key')));
  $endpoint_secret = osp_decrypt(osp_param('stripe_webhook_secret_key'));
} else {
  \Stripe\Stripe::setApiKey(osp_decrypt(osp_param('stripe_secret_key_test')));
  $endpoint_secret = osp_decrypt(osp_param('stripe_webhook_secret_key_test'));
}

$payload = @file_get_contents('php://input');
$event = null;


if(OSP_DEBUG) {
  $emailtext = osp_array_to_string($payload);
  mail(osc_contact_email() , 'OSCLASS PAY - STRIPE WEBHOOK DEBUG RESPONSE', $emailtext);
}


if($payload == '') {
  echo 'Webhook error - no parameters / empty payload.';
  http_response_code(400);
  exit();
}

try {
  $event = \Stripe\Event::constructFrom(
    json_decode($payload, true)
  );
} catch(\UnexpectedValueException $e) {
  // Invalid payload
  echo 'Webhook error while parsing basic request.';
  http_response_code(400);
  exit();
}

if ($endpoint_secret) {
  // Only verify the event if there is an endpoint secret defined
  // Otherwise use the basic decoded event
  $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
  try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
  } catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    echo 'Webhook error while validating signature.';
    http_response_code(400);
    exit();
  }
}

// Handle the event
switch ($event->type) {
  case 'payment_intent.succeeded':
  case 'checkout.session.completed':
  
    // Wait for few seconds to not interfere with user return URL event
    sleep(5);

    $intent = $event->data->object; // contains a \Stripe\PaymentIntent
    // Then define and call a method to handle the successful payment intent.
    // handlePaymentIntentSucceeded($paymentIntent);
    
    $order_id = '';
    $transaction_id = $intent->id;

    // Check if we can find out order ID
    if(isset($intent->metadata) && isset($intent->metadata->order_id) && $intent->metadata->order_id <> '') {
      $order_id = $intent->metadata->order_id;
      $pending = ModelOSP::newInstance()->getPendingById($order_id);
    }

    if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
      // Find pending by payment intent
      $pending = ModelOSP::newInstance()->getPendingByTransactionId($transaction_id, 'STRIPE');

      // Pending by intent not found, try to search by charge ID
      if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
        if($event->type == 'checkout.session.completed' && isset($intent->payment_intent)) {
          
          $pending = ModelOSP::newInstance()->getPendingByTransactionId($intent->payment_intent, 'STRIPE');

          if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
            echo __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay'); 
            break;
          }
        }
      }
    }

    $data = osp_get_custom($pending['s_extra']);
    $product_type = explode('x', $data['product']);
    $amount = round($data['amount'], 2);   // stripe accept just integers up to 2 decimals
    
    $zero_decimal_cur = array('BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF');
    $min_amount = (in_array(osp_currency(), $zero_decimal_cur) ? 50 : 0.5);
    
    if($amount<=0) { 
      echo 'Amount zero';
      break; 
    } else if ($amount < $min_amount) {
      echo 'Amount small';
      break; 
    }


    //$intent = Stripe\PaymentIntent::retrieve($pending['s_transaction_id']);
    // $balance = Stripe\BalanceTransaction::retrieve($intent->charges->data[0]->balance_transaction);
    // $fee = $balance->fee/100;

    Params::setParam('stripe_transaction_id', $pending['s_transaction_id']);

    $payment = ModelOSP::newInstance()->getPaymentByCode($pending['s_transaction_id'], 'STRIPE');

    if($intent->status == 'succeeded' || $intent->status == 'complete' || $intent->payment_status == 'paid') {
      if(!$payment) {
        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $data['concept'], //concept
          $pending['s_transaction_id'], // transaction code
          $amount, //amount
          strtoupper(osp_currency()), //currency
          $data['email'], // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $product_type[2]), // cart string
          $product_type[0], //product type
          'STRIPE' //source
        );

        // Pay it!
        $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);
        $pay_item = osp_pay_fee($payment_details);

        // Remove pending row - disabled due to some issues 
        // ModelOSP::newInstance()->deletePending($pending['pk_i_id']);
      }
    }
    
    break;

  default:
    // Unexpected event type
    echo 'Received unknown event type';
}

http_response_code(200);