<?php
  class PaypalPayment {
    public function __construct() { }
    
    // BUTTON CALLED VIA OSP_BUTTONS FUNCTION TO SHOW PAYMENT OPTIONS
    public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
      if($amount > 0) {
        if(osp_param('paypal_standard') == 1) {
          self::standardButton($amount, $description, $itemnumber, $extra_array);
        } else {
          self::dgButton($amount, $description, $itemnumber, $extra_array);
        }
      }
    }


    // DIGITAL GOODS BUTTON (may not be available in country)
    public static function dgButton($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
      $extra = osp_prepare_custom($extra_array);
      $extra .= 'concept,'.$description.'|';
      $extra .= 'product,'.$itemnumber.'|';
      $r = rand(0,1000);
      $extra .= 'random,'.$r;

      $APIUSERNAME = osp_decrypt(osp_param('paypal_api_username'));
      $APIPASSWORD = osp_decrypt(osp_param('paypal_api_password'));
      $APISIGNATURE = osp_decrypt(osp_param('paypal_api_signature'));

      if(osp_param('paypal_sandbox') == 1) {
        $ENDPOINT   = 'https://api-3t.sandbox.paypal.com/nvp';
      } else {
        $ENDPOINT   = 'https://api-3t.paypal.com/nvp';
      }

      $VERSION = '65.1';  // must be >= 65.1
      $REDIRECTURL = 'https://www.paypal.com/incontext?token=';

      if(osp_param('paypal_sandbox')==1) {
        $REDIRECTURL  = "https://www.sandbox.paypal.com/incontext?token=";
      }

      //Build the Credential String:
      $cred_str = 'USER=' . $APIUSERNAME . '&PWD=' . $APIPASSWORD . '&SIGNATURE=' . $APISIGNATURE . '&VERSION=' . $VERSION;

      //For Testing this is hardcoded. You would want to set these variable values dynamically
      $nvp_str  = "&METHOD=SetExpressCheckout"
      . '&RETURNURL=' . osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php?extra=' . $extra //set your Return URL here
      . '&CANCELURL=' . osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php?extra=' . $extra //set your Cancel URL here
      . '&PAYMENTREQUEST_0_CURRENCYCODE=' . osp_currency()
      . '&PAYMENTREQUEST_0_AMT=' . $amount
      . '&PAYMENTREQUEST_0_ITEMAMT=' . $amount
      . '&PAYMENTREQUEST_0_TAXAMT=0'
      . '&PAYMENTREQUEST_0_DESC=' . urlencode($description)
      . '&PAYMENTREQUEST_0_PAYMENTACTION=Sale'
      . '&L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital'
      . '&L_PAYMENTREQUEST_0_NUMBER0=' . $itemnumber
      . '&L_PAYMENTREQUEST_0_NAME0=' . urlencode(__('Listing Promotion', 'osclass_pay'))
      . '&L_PAYMENTREQUEST_0_QTY0=1'
      . '&L_PAYMENTREQUEST_0_TAXAMT0=0'
      . '&L_PAYMENTREQUEST_0_AMT0=' . $amount
      . '&L_PAYMENTREQUEST_0_DESC0=Download'
      . '&CUSTOM=' . $extra
      . '&useraction=commit';

      //combine the two strings and make the API Call
      $req_str = $cred_str . $nvp_str;
      $response = self::httpPost($ENDPOINT, $req_str);


      //check Response
      if($response['ACK'] == "Success" || $response['ACK'] == "SuccessWithWarning") {
        //setup redirect URL
        $redirect_url = $REDIRECTURL . urldecode($response['TOKEN']);
        ?>

        <li class="paypal-btn">
          <a class="osp-has-tooltip" title="<?php echo osc_esc_html(__('Payment form will popup on click', 'osclass_pay')); ?>" href="<?php echo $redirect_url; ?>" id='paypalBtn_<?php echo $r; ?>'>
            <span><img src="<?php echo osp_url(); ?>img/payments/paypl.png"/></span>
            <strong><?php _e('Pay with PayPal', 'osclass_pay'); ?></strong>
          </a>

          <script>
            var dg_<?php echo $r; ?> = new PAYPAL.apps.DGFlow({
              trigger: "paypalBtn_<?php echo $r; ?>"
            });
          </script>
        </li>

        <?php
      //} else if($response['ACK'] == 'Failure' || $response['ACK'] == 'FailureWithWarning') {
      } else {
        error_log(json_encode($response, JSON_PRETTY_PRINT));
        //print_r($response);
        $redirect_url = ''; //SOMETHING FAILED
      }
    }

    public static function standardButton($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
      $extra = osp_prepare_custom($extra_array) . '|';
      $extra .= 'concept,'.$description.'|';
      $extra .= 'product,'.$itemnumber.'|';
      $r = rand(0,1000);
      $extra .= 'random,'.$r;


      if(osp_param('paypal_sandbox')==1) {
        $ENDPOINT   = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
      } else {
        $ENDPOINT   = 'https://www.paypal.com/cgi-bin/webscr';
      }

      $RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php?extra=' . $extra;
      $CANCELURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php?extra=' . $extra;
      $NOTIFYURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'notify_url.php?extra=' . $extra;
      ?>

        <li class="paypal-btn">
          <form class="nocsrf" action="<?php echo $ENDPOINT; ?>" method="post" id="paypal_<?php echo $r; ?>">
            <input type="hidden" name="cmd" value="_xclick" />
            <input type="hidden" name="notify_url" value="<?php echo $NOTIFYURL; ?>" />
            <input type="hidden" name="amount" value="<?php echo $amount; ?>" />
            <input type="hidden" name="item_name" value="<?php echo osc_esc_html($description); ?>" />
            <input type="hidden" name="item_number" value="<?php echo $itemnumber; ?>" />
            <input type="hidden" name="quantity" value="1" />
            <input type="hidden" name="currency_code" value="<?php echo osc_esc_html(osp_currency()); ?>" />
            <input type="hidden" name="custom" value="<?php echo $extra; ?>" />
            <input type="hidden" name="return" value="<?php echo $RETURNURL; ?>" />
            <input type="hidden" name="rm" value="2" />
            <input type="hidden" name="cancel_return" value="<?php echo $CANCELURL; ?>" />
            <input type="hidden" name="business" value="<?php echo osp_param('paypal_email'); ?>" />
            <input type="hidden" name="upload" value="1" />
            <input type="hidden" name="no_note" value="1" />
            <input type="hidden" name="charset" value="utf-8" />
          </form>

          <a id="paypal-button-confirm" class="button osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to PayPal', 'osclass_pay')); ?>" onclick="$('#paypal_<?php echo $r; ?>').submit();">
            <span><img src='<?php echo osp_url(); ?>img/payments/paypl.png' border='0' /></span>
            <strong><?php _e('Pay with PayPal', 'osclass_pay'); ?></strong>
          </a>
        </li>
      <?php
    }


    public static function processPayment() {
      return self::processStandardPayment();
    }


    public static function processStandardPayment() {
      $payment_status = strtolower(Params::getParam('payment_status') <> '' ? Params::getParam('payment_status') : Params::getParam('st'));
  
      if($payment_status == 'completed' || $payment_status == 'pending') {

        // Have we processed the payment already?
        $tx = Params::getParam('txn_id') <> '' ? Params::getParam('txn_id') : Params::getParam('tx');
        $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'PAYPAL');

        if (!$payment) {
          if(Params::getParam('custom')!='') {
            $custom = Params::getParam('custom');
          } else if(Params::getParam('cm')!='') {
            $custom = Params::getParam('cm');
          } else if(Params::getParam('extra')!='') {
            $custom = Params::getParam('extra');
          }

          $data = osp_get_custom($custom);
          $product_type = explode('x', Params::getParam('item_number'));
          $amount = (Params::getParam('mc_gross')!='' ? Params::getParam('mc_gross') : Params::getParam('payment_gross'));

          // SAVE TRANSACTION LOG
          $payment_id = ModelOSP::newInstance()->saveLog(
            Params::getParam('item_name'), //concept
            $tx, // payment id
            $amount, //amount
            Params::getParam('mc_currency'), //currency
            Params::getParam('payer_email') <> '' ? Params::getParam('payer_email') : '', // payer's email
            $data['user'], //user
            osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
            $product_type[0], //product type
            'PAYPAL' //source
          ); 


          // Pay it!
          $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
          $pay_item = osp_pay_fee($payment_details);

          return OSP_STATUS_COMPLETED;
        }
        return OSP_STATUS_ALREADY_PAID;
      }
      return OSP_STATUS_PENDING;
    }



    public static function processDGPayment($doresponse, $response) {

      if(Params::getParam('custom')!='') {
        $custom = Params::getParam('custom');
      } else if(Params::getParam('cm')!='') {
        $custom = Params::getParam('cm');
      } else if(Params::getParam('extra')!='') {
        $custom = Params::getParam('extra');
      }

      $data = osp_get_custom($custom);

      if ($doresponse['ACK'] == 'Success' || $doresponse['ACK'] == 'SuccessWithWarning') {
        $product_type = explode('x', urldecode($response['L_PAYMENTREQUEST_0_NUMBER0']));
        $amount = (Params::getParam('mc_gross') <> '' ? Params::getParam('mc_gross') : Params::getParam('payment_gross'));

        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          urldecode($response['L_PAYMENTREQUEST_0_NAME0']), //concept
          urldecode($doresponse['PAYMENTINFO_0_TRANSACTIONID']),  // transaction code
          urldecode($doresponse['PAYMENTINFO_0_AMT']), //amount
          urldecode($doresponse['PAYMENTINFO_0_CURRENCYCODE']), //currency
          isset($response['EMAIL']) ? urldecode($response['EMAIL']) : '', // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
          $product_type[0], //product type
          'PAYPAL' //source
        ); 


        // Pay it!
        $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);

        return OSP_STATUS_COMPLETED;
      } else if($doresponse['ACK'] == "Failure" || $doresponse['ACK'] == "FailureWithWarning") {
        return OSP_STATUS_FAILED;
      }
      return OSP_STATUS_PENDING;
    }



    //Makes an API call using an NVP String and an Endpoint
    public static function httpPost($my_endpoint, $my_api_str) {

      // setting the curl parameters.
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $my_endpoint);
      curl_setopt($ch, CURLOPT_VERBOSE, 1);

      // turning off the server and peer verification(TrustManager Concept).
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POST, 1);

      // setting the NVP $my_api_str as POST FIELD to curl
      curl_setopt($ch, CURLOPT_POSTFIELDS, $my_api_str);

      // getting response from server
      $httpResponse = curl_exec($ch);

      if (!$httpResponse) {
        $response = "$API_method failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')';
        return $response;
      }

      $httpResponseAr = explode("&", $httpResponse);
      $httpParsedResponseAr = array();
      foreach ($httpResponseAr as $i => $value) {
        $tmpAr = explode("=", $value);
        if (sizeof($tmpAr) > 1) {
          $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
        }
      }

      if ((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
        $response = "Invalid HTTP Response for POST request($my_api_str) to $API_Endpoint.";
        return $response;
      }

      return $httpParsedResponseAr;
    }
  }
?>