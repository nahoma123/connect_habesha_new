<?php
class CcavenuePayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'src/crypto.php';

    $extra = osp_prepare_custom($extra_array) . '|';
    $r = rand(0,1000);
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    $extra .= 'random,'.$r;
    $merchant_params = self::extra_to_mp($extra);

    if(osp_param('ccavenue_sandbox') == 1) {
      $ENDPOINT = 'https://test.ccavenue.com';
    } else {
      $ENDPOINT = 'https://secure.ccavenue.com';
    }

    $RESPONSEURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'response.php';

    $data = 'tid=' . time();
    $data .= '&merchant_id=' . osp_param('ccavenue_merchant_id');
    $data .= '&order_id=' . mb_generate_rand_string(10);
    $data .= '&amount=' . round($amount, 2);
    $data .= '&currency=' . osp_currency();
    $data .= '&redirect_url=' . urlencode($RESPONSEURL);
    $data .= '&cancel_url=' . urlencode($RESPONSEURL);
    $data .= '&language=' . (osp_param('ccavenue_language') <> '' ? osp_param('ccavenue_language') : 'EN');
    $data .= '&merchant_param1=' . $merchant_params[1];
    $data .= '&merchant_param2=' . $merchant_params[2];
    $data .= '&merchant_param3=' . $merchant_params[3];
    $data .= '&merchant_param4=' . $merchant_params[4];
    $data .= '&merchant_param5=' . $merchant_params[5];

    $data_encrypt = encrypt($data, osp_decrypt(osp_param('ccavenue_working_key')));

  ?>

    <li class="payment ccavenue-btn">
      <form name="redirect" action="<?php echo $ENDPOINT; ?>/transaction/transaction.do?command=initiateTransaction" method="POST" class="nocsrf">
        <input type="hidden" name="encRequest" value="<?php echo $data_encrypt; ?>"/>
        <input type="hidden" name="access_code" value="<?php echo osp_decrypt(osp_param('ccavenue_access_code')); ?>"/>

        <a class="osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to CCAvenue', 'osclass_pay')); ?>" href="#" onclick="$(this).closest('form').submit();return false;" >
          <span><img src="<?php echo osp_url(); ?>img/payments/ccavenue.png"/></span>
          <strong><?php _e('Pay with CCAvenue', 'osclass_pay'); ?></strong>
        </a>
      </form>
    </li>
  <?php
  }
 
  
  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment() {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'src/crypto.php';

    $working_key = osp_decrypt(osp_param('ccavenue_working_key'));

    if(osp_param('ccavenue_sandbox') == 1) {
      $ENDPOINT = 'https://test.ccavenue.com';
    } else {
      $ENDPOINT = 'https://secure.ccavenue.com';
    }


    $encResponse = $_POST['encResp'];
    $rcvdString = decrypt($encResponse, $working_key);
    $order_status = '';
    $decryptValues = explode('&', $rcvdString);
    $dataSize = sizeof($decryptValues);

    $response = array('tracking_id' => '', 'currency' => '', 'amount' => '', 'order_status' => '', 'billing_email' => '', 'failure_message' => '', 'response_code' => '');

    for($i = 0; $i < $dataSize; $i++) {
      $information = explode('=', $decryptValues[$i]);
      $response[$information[0]] = isset($information[1]) ? $information[1] : '';

      if($i==3)	{ 
        $order_status = $information[1];
      }
    }

    
    Params::setParam('ccavenue_transaction_id', $response['tracking_id']);

    $extra = self::mp_to_extra($response);
    $data = osp_get_custom($extra);
    $product_type = explode('x', $data['product']);
    $amount = round($response['amount'], 2); 

    if($amount<=0) { 
      return array(OSP_STATUS_AMOUNT_ZERO, ''); 
    }

    $payment = ModelOSP::newInstance()->getPaymentByCode($response['tracking_id'], 'CCAVENUE');

    if(!$payment) { 
      if(strtolower($response['order_status']) == 'success') {

        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $data['concept'], //concept
          $response['tracking_id'], // transaction code
          $amount, //amount
          strtoupper($response['currency']), //currency
          $response['billing_email'], // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $product_type[2]), // cart string
          $product_type[0], //product type
          'CCAVENUE' //source
        );

          
        // Pay it!
        $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);

        return array(OSP_STATUS_COMPLETED, $response);

      } else if(strtolower($response['order_status']) == 'invalid') {
        return array(OSP_STATUS_INVALID, $response); 

      } else {
        return array(OSP_STATUS_FAILED, $response); 

      }
    } 

    return array(OSP_STATUS_ALREADY_PAID, ''); 
  }



  // CCAVENUE SET MAXIMUM OF 100 CHARS IN EACH merchant_dataX VARIABLE
  // $extra WILL BE SPLIT INTO THESE 5 VARIABLES
  public static function extra_to_mp($extra) {
    $mp = array();
    $min = min(5, ceil(strlen(urlencode($extra))));
    for($k=0;$k<$min;$k++) {
      $mp[$k+1] = substr(urlencode($extra), 100*$k, 100);
    }

    return $mp;
  }


  // GENERATE EXTRA-CUSTOM BASED ON APCs FROM PAYZA
  public static function mp_to_extra($response) {
    $extra = '';
    for($i=1;$i<=5;$i++) {
      if(isset($response['merchant_param' . $i]) && $response['merchant_param' . $i] <> '') {
        $extra .= $response['merchant_param' . $i];
      }
    }

    return urldecode($extra);
  }
}
?>