<?php
class EuPlatescPayment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    $r = rand(0,1000);
    $extra .= 'r,'.$r;

    $email = osc_logged_user_email(); 
    $mid = osp_param('euplatesc_mid');
    $key = osp_decrypt(osp_param('euplatesc_key'));
    $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
 
    $data = array(
      'amount'      => $amount,                                                     // suma de plata
      'curr'        => osp_currency(),                                              // moneda de plata
      'invoice_id'  => mb_generate_rand_int(12),                                    // numarul comenzii este generat aleator. inlocuiti cuu seria dumneavoastra
      'order_desc'  => osc_highlight($description, 45),                             // descrierea comenzii
      'merch_id'    => $mid,                                                        // nu modificati
      'timestamp'   => gmdate("YmdHis"),                                            // nu modificati
      'nonce'       => md5(microtime() . mt_rand())                                 // nu modificati
    ); 

    $data['fp_hash'] = strtoupper(euplatesc_mac($data, $key));

    ?>

    <li>
      <form ACTION="https://secure.euplatesc.ro/tdsprocess/tranzactd.php" METHOD="POST" class="form-euplatesc" name="gateway" target="_self">
        <input name="fname" type="hidden" value="<?php echo $user['s_name'];?>" />
        <input name="lname" type="hidden" value="<?php echo $user['s_name'];?>" />
        <input name="country" type="hidden" value="<?php echo ($user['s_country'] <> '' ? $user['s_country'] : '-'); ?>" />
        <input name="city" type="hidden" value="<?php echo ($user['s_city'] <> '' ? $user['s_city'] : '-'); ?>" />
        <input name="add" type="hidden" value="<?php echo ($user['s_address'] <> '' ? $user['s_address'] : '-'); ?>" />
        <input name="email" type="hidden" value="<?php echo osc_logged_user_email(); ?>" />
        <input name="phone" type="hidden" value="<?php echo ($user['s_phone_land'] <> '' ? $user['s_phone_land'] : $user['s_phone_mobile']); ?>" />

        <input type="hidden" NAME="amount" VALUE="<?php echo $data['amount']; ?>" SIZE="12" MAXLENGTH="12" />
        <input TYPE="hidden" NAME="curr" VALUE="<?php echo $data['curr']; ?>" SIZE="5" MAXLENGTH="3" />
        <input type="hidden" NAME="invoice_id" VALUE="<?php echo $data['invoice_id']; ?>" SIZE="32" MAXLENGTH="32" />
        <input type="hidden" NAME="order_desc" VALUE="<?php echo $data['order_desc']; ?>" SIZE="32" MAXLENGTH="50" />
        <input TYPE="hidden" NAME="merch_id" SIZE="15" VALUE="<?php echo $data['merch_id']; ?>" />
        <input TYPE="hidden" NAME="timestamp" SIZE="15" VALUE="<?php echo $data['timestamp']; ?>" />
        <input TYPE="hidden" NAME="nonce" SIZE="35" VALUE="<?php echo $data['nonce']; ?>" />
        <input TYPE="hidden" NAME="fp_hash" SIZE="40" VALUE="<?php echo $data['fp_hash']; ?>" />

        <input TYPE="hidden" NAME="ExtraData" SIZE="40" VALUE="<?php echo $extra; ?>" />
      </form>

      <a id="osp-button-confirm" class="button osp-has-tooltip" title="<?php echo osc_esc_html(__('You will be redirected to EuPlatesc.ro', 'osclass_pay')); ?>" onclick="javascript:gateway.submit();">
        <span><img src="<?php echo osp_url(); ?>img/payments/euplatesc.png"/></span>
        <strong><?php _e('Pay with EuPlatesc.ro', 'osclass_pay'); ?></strong>
      </a>
    </li>
    <?php
  }


  public static function processPayment($type = 'RETURN') {
    $payment_status = Params::getParam('action');
    $payment_message = Params::getParam('message');

    if($payment_status == 0) {

      // Have we processed the payment already?
      $tx = Params::getParam('ep_id');
      $payment = ModelOSP::newInstance()->getPaymentByCode($tx, 'EUPLATESC');


      // If ExtraData is null it's just return URL and payment cannot be processed, ExtraData are sent in silent reply
      if($type == 'RETURN') {
        if($payment) {
          return array(OSP_STATUS_COMPLETED, '');
        } else if(Params::getParam('ExtraData') == '') {
          return array(OSP_STATUS_PENDING, __('We are processing your payment!', 'osclass_pay'));
        }
      }


      if (!$payment) {
        $data = osp_get_custom(Params::getParam('ExtraData'));
        $product_type = explode('x', $data['product']);
        $amount = Params::getParam('amount');

        if($amount <= 0) { 
          return array(OSP_STATUS_AMOUNT_ZERO, ''); 
        }

        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $data['concept'],  //concept
          $tx, // payment id
          $amount, //amount
          Params::getParam('curr'), //currency
          $data['email'], // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $data['itemid']), // cart string
          $product_type[0], //product type
          'EUPLATESC' //source
        ); 


        // Pay it!
        $payment_details = osp_prepare_payment_data($amount, $payment_id, $data['user'], $product_type);   //amount, payment_id, user_id, product_type
        $pay_item = osp_pay_fee($payment_details);

        return array(OSP_STATUS_COMPLETED, $payment_message);
      }

      return array(OSP_STATUS_ALREADY_PAID, ''); 
    }

    return array(OSP_STATUS_FAILED, $payment_message);
  }
}




// -- GATEWAY FUNCTIONS --
function euplatesc_hmacsha1($key,$data) {
   $blocksize = 64;
   $hashfunc  = 'md5';
   
   if(strlen($key) > $blocksize)
     $key = pack('H*', $hashfunc($key));
   
   $key  = str_pad($key, $blocksize, chr(0x00));
   $ipad = str_repeat(chr(0x36), $blocksize);
   $opad = str_repeat(chr(0x5c), $blocksize);
   
   $hmac = pack('H*', $hashfunc(($key ^ $opad) . pack('H*', $hashfunc(($key ^ $ipad) . $data))));
   return bin2hex($hmac);

}

function euplatesc_mac($data, $key)
{
  $str = NULL;

  foreach($data as $d)
  {
    if($d === NULL || strlen($d) == 0)
      $str .= '-'; // valorile nule sunt inlocuite cu -
    else
      $str .= strlen($d) . $d;
  }
     
  // ================================================================
  $key = pack('H*', $key); // convertim codul secret intr-un string binar
  // ================================================================

// echo " $str " ;

  return euplatesc_hmacsha1($key, $str);
}

?>