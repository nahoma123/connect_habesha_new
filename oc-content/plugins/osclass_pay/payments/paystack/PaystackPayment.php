<?php
class PaystackPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    $r = rand(0,1000);
    $extra .= 'r,'.$r;


    $email = osc_logged_user_email(); //osp_param('paystack_email');

    if(osp_param('paystack_sandbox') <> 1) {
      $public_key = osp_param('paystack_public_key');
    } else {
      $public_key = osp_param('paystack_test_public_key');
    }
    ?>

    <li>
      <form action="<?php echo osc_base_url(true); ?>" method="POST" >
        <input type="hidden" name="page" value="ajax" />
        <input type="hidden" name="action" value="runhook" />
        <input type="hidden" name="hook" value="paystack" />
        <input type="hidden" name="amount" value="" id="paystack-amount"/>
        <input type="hidden" name="extra" value="<?php echo $extra;?>"  />
        <script src="https://js.paystack.co/v1/inline.js" data-key="<?php echo $public_key;?>" data-email="<?php echo $email;?>" data-amount="<?php echo $amount*100;?>" data-currency="<?php echo osp_currency(); ?>"></script>
      </form>
    </li>
    <?php
  }



  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment() {
    $result = self::confirmPayment();
    $status = $result[0];

    $data = osp_get_custom(Params::getParam('extra'));
    $product_type = explode('x', $data['product']);


    if ($status == OSP_STATUS_COMPLETED) {
      osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), Params::getParam('paystack_reference')));


    } else if ($status == OSP_STATUS_ALREADY_PAID) {
      osc_add_flash_warning_message(__('Warning! This payment was already paid.', 'osclass_pay'));
    } else if ($status == OSP_STATUS_AMOUNT_ZERO) {
      osc_add_flash_error_message(__('You are trying to pay zero amount.', 'osclass_pay'));
    } else {
      osc_add_flash_error_message(__('There were an error processing your payment.', 'osclass_pay') . ' (' . $result[1]['message'] . ')');
    }


    if(OSP_DEBUG) {
      $emailtext = "status => " . $status . "\r\n";
      //$emailtext = osp_array_to_string(Params::getParamsAsArray('post'));
      if(isset($result[1])) {
        $emailtext .= "\r\n ---------- \r\n" . osp_array_to_string($result[1]);
      }
      mail(osc_contact_email() , 'OSCLASS PAY - PAYSTACK DEBUG RESPONSE', $emailtext);
    }

    osp_js_redirect_to(osp_pay_url_redirect($product_type));

  }




  // CONFIRM PAYMENT ON PAYSTACK
  public static function confirmPayment() {
    $reference = $_POST['paystack-trxref'];

    if(osp_param('paystack_sandbox') <> 1) {
      $key = osp_decrypt(osp_param('paystack_secret_key'));
    } else {
      $key = osp_decrypt(osp_param('paystack_test_secret_key'));
    }

    $result = array();
    /// Verify Transaction was successful
    $url = 'https://api.paystack.co/transaction/verify/'.$reference;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$key]);
    $request = curl_exec($ch);
    curl_close($ch);

    if ($request) {
      $result = json_decode($request, true);
    }

    if(array_key_exists('data', $result) && array_key_exists('status', $result['data']) && ($result['data']['status'] === 'success')) {
      $status = 'success';
    } else {
      $status = 'failed';
    }


    if($status == 'success'){
      Params::setParam('paystack_reference', $reference);

      $exists = ModelOSP::newInstance()->getPaymentByCode($reference, 'PAYSTACK');

      if(isset($exists['pk_i_id'])) { 
        return array(OSP_STATUS_ALREADY_PAID); 
      }
        
      $data = osp_get_custom(Params::getParam('extra'));
      $product_type = explode('x', $data['product']);
      $amount = $result['data']['amount']/100;

      // SAVE TRANSACTION LOG
      $payment_id = ModelOSP::newInstance()->saveLog(
        $data['concept'], //concept
        $result['data']['reference'], // transaction code
        $amount, //amount
        osp_currency(), //currency
        $data['email'], // payer's email
        $data['user'], //user
        osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
        $product_type[0], //product type
        'PAYSTACK' //source
      );


      // Pay it!
      $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
      $pay_item = osp_pay_fee($payment_details);

      return array(OSP_STATUS_COMPLETED, $result);
    }

    return array(OSP_STATUS_FAILED, $result);
  }

}
?>