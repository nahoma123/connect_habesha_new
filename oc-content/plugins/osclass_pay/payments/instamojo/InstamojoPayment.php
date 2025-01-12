<?php
class InstamojoPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    //$extra = osp_prepare_custom($extra_array) . '|';
    $extra = 'u,'.osc_logged_user_id().'|p,'.$itemnumber.'|a,'.$amount;
    ?>

    <li class="payment instamojo-btn">
      <form action="<?php echo osc_base_url(true); ?>" method="POST" class="nocsrf">
        <input type="hidden" name="page" value="ajax" />
        <input type="hidden" name="action" value="runhook" />
        <input type="hidden" name="hook" value="instamojo" />
        <input type="hidden" name="purpose" id="instamojo-extra" value="<?php echo $extra; ?>" />
        <input type="hidden" name="amount" id="instamojo-amount" value="<?php echo $amount; ?>"/>
        <input type="hidden" name="currency" id="instamojo-currency" value="<?php echo osp_currency(); ?>" />
        <input type="hidden" name="buyer_name" id="instamojo-name" value="<?php echo osc_logged_user_name(); ?>"/>
        <input type="hidden" name="email" id="instamojo-email" value="<?php echo osc_logged_user_email(); ?>"/>

        <a class="osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to Instamojo', 'osclass_pay')); ?>" href="#" onclick="$(this).closest('form').submit();return false;" >
          <span><img src="<?php echo osp_url(); ?>img/payments/instamojo.png"/></span>
          <strong><?php _e('Pay with Instamojo', 'osclass_pay'); ?></strong>
        </a>
      </form>
    </li>
  <?php
  }
 

  // AJAX FUNCTION TO PROCESS PAYMENT, HOOKED TO AJAX_TWOCHECKOUT
  public static function ajaxPayment() {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'src/Instamojo.php';

    $RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php?extra=' . Params::getParam('purpose');
    $WEBHOOKURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'webhook.php';

    if(osp_param('instamojo_sandbox') == 1) {
      $ENDPOINT = 'https://test.instamojo.com/api/1.1/';
    } else {
      $ENDPOINT = 'https://www.instamojo.com/api/1.1/';
    }

    $api = new Instamojo\Instamojo(osp_param('instamojo_api_key'), osp_decrypt(osp_param('instamojo_auth_token')), $ENDPOINT);

    try {
      $response = $api->paymentRequestCreate(array(
        "purpose" => Params::getParam('purpose'),
        "amount" => Params::getParam('amount'),
        "buyer_name" => Params::getParam('buyer_name'),
        "phone" => Params::getParam('phone'),
        "email" => Params::getParam('email'),
        "allow_repeated_payments" => false,
        "redirect_url" => $RETURNURL,
        "webhook" => $WEBHOOKURL
      ));

      if(OSP_DEBUG) {
        $emailtext .= osp_array_to_string(Params::getParamsAsArray());
        $emailtext .= '<br/>----<br/>';
        $emailtext .= osp_array_to_string($response);
        $emailtext .= '<br/>----<br/>';
        $emailtext .= osp_array_to_string($api);
        mail(osc_contact_email() , 'OSCLASS PAY - INSTAMOJO DEBUG RESPONSE (AJAX)', $emailtext);
      }


      osp_js_redirect_to($response['longurl'] . '?embed=form');
      exit();

    } catch (Exception $e) {
      osc_add_flash_error_message(sprintf(__('There is problem with payment button, please try again later. (%s)', 'osclass_pay'), $e->getMessage()));

      $data = osp_get_custom(Params::getParam('purpose'));
      $product_type = explode('x', $data['p']);

      osp_js_redirect_to(osp_pay_url_redirect($product_type));

      if(OSP_DEBUG) {
        $emailtext = osp_array_to_string($e->getMessage());
        $emailtext .= '<br/>----<br/>';
        $emailtext .= osp_array_to_string($e);
        $emailtext .= '<br/>----<br/>';
        $emailtext .= osp_array_to_string(Params::getParamsAsArray());
        $emailtext .= '<br/>----<br/>';
        $emailtext .= osp_array_to_string($response);
        $emailtext .= '<br/>----<br/>';
        $emailtext .= osp_array_to_string($api);
        mail(osc_contact_email() , 'OSCLASS PAY - INSTAMOJO DEBUG RESPONSE (AJAX)', $emailtext);
      }
    }
  }     


  
  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment() {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'src/Instamojo.php';

    if(osp_param('instamojo_sandbox') == 1) {
      $ENDPOINT = 'https://test.instamojo.com/api/1.1/';
    } else {
      $ENDPOINT = 'https://www.instamojo.com/api/1.1/';
    }

    $api = new Instamojo\Instamojo(osp_param('instamojo_api_key'), osp_decrypt(osp_param('instamojo_auth_token')), $ENDPOINT);
    $tx = Params::getParam('payment_id');
    $request_id = (Params::getParam('payment_request_id') <> '' ? Params::getParam('payment_request_id') : Params::getParam('id'));

    $data = osp_get_custom(Params::getParam('extra'));
    $product_type = explode('x', $data['p']);
    $amount = round($data['a'], 2);   // stripe accept just integers up to 2 decimals
    

    if($amount<=0) { 
      return array(OSP_STATUS_AMOUNT_ZERO, ''); 
    }


    try {
      $response = $api->paymentRequestStatus($request_id);

      Params::setParam('instamojo_transaction_id', $response['payments'][0]['payment_id']);
      $payment = ModelOSP::newInstance()->getPaymentByCode($response['payments'][0]['payment_id'], 'INSTAMOJO');

      if($response['status'] == 'Completed' || $response['status'] == 'Credit') {
        if(!$payment) { 
          // SAVE TRANSACTION LOG
          $payment_id = ModelOSP::newInstance()->saveLog(
            sprintf(__('Pay cart items for %s', 'osclass_pay'), osp_format_price($response['payments'][0]['amount'], 2)), //concept
            $response['payments'][0]['payment_id'], // transaction code
            $response['payments'][0]['amount'], //amount
            strtoupper($response['payments'][0]['currency']), //currency
            osc_logged_user_email(), // payer's email
            $data['u'], //user
            osp_create_cart_string($product_type[1], $data['u'], $product_type[2]), // cart string
            $product_type[0], //product type
            'INSTAMOJO' //source
          );

          
          // Pay it!
          $payment_details = osp_prepare_payment_data($response['payments'][0]['amount'], $payment_id, $data['u'], $product_type);   //amount, payment_id, user_id, product_type
          $pay_item = osp_pay_fee($payment_details);

          return array(OSP_STATUS_COMPLETED, $response);
        }

        return array(OSP_STATUS_ALREADY_PAID, ''); 
      }
    } catch (Exception $e) {
      return array(OSP_STATUS_FAILED, $e); 
    }
  }
}
?>