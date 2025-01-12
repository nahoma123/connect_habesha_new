<?php
  // This page will handle the GetECDetails, and DoECPayment API Calls
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $status = OSP_STATUS_FAILED;

  if(Params::getParam('custom')!='') {
    $custom = Params::getParam('custom');
  } else if(Params::getParam('cm')!='') {
    $custom = Params::getParam('cm');
  } else if(Params::getParam('extra')!='') {
    $custom = Params::getParam('extra');
  }

  $data = osp_get_custom($custom);


  if(osp_param('paypal_standard') == 1) {
    $product_type = explode('x', Params::getParam('item_number'));
    $tx = Params::getParam('txn_id') <> '' ? Params::getParam('txn_id') : Params::getParam('tx');
    $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'PAYPAL');
    
    if (isset($payment['pk_i_id'])) {
      osc_add_flash_ok_message(sprintf(__('Success! Please write down this transaction ID in case you have any problem: %s', 'osclass_pay'), $tx));
    } else {
      osc_add_flash_info_message(sprintf(__('We are processing your payment, if it did not finish in a few seconds, please contact us', 'osclass_pay'), $tx));
    }
    
    $status = PaypalPayment::processPayment();

    if(OSP_DEBUG && isset($data)) {
      $emailtext = "status => " . $status . "\r\n";
      $emailtext .= osp_array_to_string(Params::getParamsAsArray());
      mail(osc_contact_email() , 'OSCLASS PAY - PAYPAL DEBUG RESPONSE (RETURN - STANDARD)', $emailtext);
    }
    
    osp_js_redirect_to(osp_pay_url_redirect($product_type));

  } else {

    //set GET var's to local vars:
    $token = $_GET['token'];
    $payerid = $_GET['PayerID'];

    //set API Creds, Version, and endpoint:
    //**************************************************//
    // This is where you would set your API Credentials //
    // Please note this is not considered "SECURE" this //
    // is an example only. It is NOT Recommended to use //
    // this method in production........................//
    //**************************************************//
    $APIUSERNAME = osp_decrypt(osp_param('paypal_api_username'));
    $APIPASSWORD = osp_decrypt(osp_param('paypal_api_password'));
    $APISIGNATURE = osp_decrypt(osp_param('paypal_api_signature'));
    $ENDPOINT = 'https://api-3t.paypal.com/nvp';

    if(osp_param('paypal_sandbox') == 1) {
      $ENDPOINT = 'https://api-3t.sandbox.paypal.com/nvp';
    }

    $VERSION = '65.1'; //must be >= 65.1

    //Build the Credential String:
    $cred_str = 'USER=' . $APIUSERNAME . '&PWD=' . $APIPASSWORD . '&SIGNATURE=' . $APISIGNATURE . '&VERSION=' . $VERSION;

    //Build NVP String for GetExpressCheckoutDetails
    $nvp_str = '&METHOD=GetExpressCheckoutDetails&TOKEN='. urldecode($token);

    //combine the two strings and make the API Call
    $req_str = $cred_str . $nvp_str;
    $response = PaypalPayment::httpPost($ENDPOINT, $req_str);

    //based on the API Response from GetExpressCheckoutDetails
    $doec_str = $cred_str 
      . '&METHOD=DoExpressCheckoutPayment'
      . '&TOKEN=' . $token
      . '&PAYERID=' . $payerid
      . '&PAYMENTREQUEST_0_CURRENCYCODE=' . urldecode($response['PAYMENTREQUEST_0_CURRENCYCODE'])
      . '&PAYMENTREQUEST_0_AMT=' . urldecode($response['PAYMENTREQUEST_0_AMT'])
      . '&PAYMENTREQUEST_0_ITEMAMT=' . urldecode($response['PAYMENTREQUEST_0_ITEMAMT'])
      . '&PAYMENTREQUEST_0_TAXAMT=' . urldecode($response['PAYMENTREQUEST_0_TAXAMT'])
      . '&PAYMENTREQUEST_0_DESC=' . urldecode($response['PAYMENTREQUEST_0_DESC'])
      . '&PAYMENTREQUEST_0_PAYMENTACTION=Sale'
      . '&L_PAYMENTREQUEST_0_ITEMCATEGORY0=' . urldecode($response['L_PAYMENTREQUEST_0_ITEMCATEGORY0'])
      . '&L_PAYMENTREQUEST_0_NAME0=' . urldecode($response['L_PAYMENTREQUEST_0_NAME0'])
      . '&L_PAYMENTREQUEST_0_NUMBER0=' . urldecode($response['L_PAYMENTREQUEST_0_NUMBER0'])
      . '&L_PAYMENTREQUEST_0_QTY0=' . urldecode($response['L_PAYMENTREQUEST_0_QTY0'])
      . '&L_PAYMENTREQUEST_0_TAXAMT0=' . urldecode($response['L_PAYMENTREQUEST_0_TAXAMT0'])
      . '&L_PAYMENTREQUEST_0_AMT0=' . urldecode($response['L_PAYMENTREQUEST_0_AMT0'])
      . '&L_PAYMENTREQUEST_0_DESC0=' . urldecode($response['L_PAYMENTREQUEST_0_DESC0'])
      . '&NOTIFYURL=';

    //make the DoEC Call:
    $doresponse = PaypalPayment::httpPost($ENDPOINT, $doec_str);
    $status = PaypalPayment::processDGPayment($doresponse, $response);

    $product_type = explode('x', urldecode($response['L_PAYMENTREQUEST_0_NUMBER0']));
    $url = osp_pay_url_redirect($product_type);

    if($status == OSP_STATUS_COMPLETED || $status == OSP_STATUS_ALREADY_PAID) {
      osc_add_flash_ok_message(__('Payment processed correctly', 'osclass_pay'));
      $html = '<p>' . __('Payment processed correctly', 'osclass_pay') . ' <a href=\\"' . $url . '\\">' . __('Click here to continue', 'osclass_pay') . '</a></p>';
    } else if($status == OSP_STATUS_PENDING) {
      osc_add_flash_info_message(__('We are processing your payment, if we did not finish in a few seconds, please contact us', 'osclass_pay'));
      $html = '<p>' . __('We are processing your payment, if it did not finish in a few seconds, please contact us', 'osclass_pay') . ' <a href=\\"' . $url . '\\">' . __('Click here to continue', 'osclass_pay') . '</a></p>';
    } else {
      osc_add_flash_error_message(__('There was a problem processing your payment. Please contact the administrators', 'osclass_pay'));
      $html = '<p>' . __('There was a problem processing your payment. Please contact the administrators and', 'osclass_pay') . ' <a href=\\"' . $url . '\\">' . __('Click here to continue', 'osclass_pay') . '</a></p>';
    }
    
    osp_js_redirect_to($url);

    if(OSP_DEBUG && isset($data)) {
      $emailtext = "status => " . $status . "\r\n";
      $emailtext .= osp_array_to_string($response);
      $emailtext .= osp_array_to_string($doresponse);
      mail(osc_contact_email() , 'OSCLASS PAY - PAYPAL DEBUG RESPONSE (RETURN - DIGITAL)', $emailtext);
    }
  ?>
  
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
      <meta http-equiv="Content-Type" content="text/html" charset=iso-8859-1" />
      <script type="text/javascript" src="https://www.paypalobjects.com/js/external/dg.js"></script>
      <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.js"></script>

      <title><?php echo osc_page_title(); ?></title>
    </head>
    <body>
      <script type="text/javascript">
        top.rd.innerHTML = "<?php echo $html; ?>" ;
        top.location.href = "<?php echo $url; ?>" ;
        top.dg_<?php echo $data['random'] ; ?>.closeFlow() ;
      </script>
    </body>
  </html>
<?php } ?>