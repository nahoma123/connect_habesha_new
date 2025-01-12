<?php

class Przelewy24Payment {
  public function __construct() { }

  // BUTTON GENERATED VIA FUNCTION OSP_BUTTONS TO PROCESS PAYMENT
  public static function button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'src/class_przelewy24.php';

    $extra = osp_prepare_custom($extra_array) . '|';
    $extra .= 'concept,'.$description.'|';
    $extra .= 'product,'.$itemnumber.'|';
    $r = rand(0,1000);
    $extra .= 'r,'.$r;


    $pos_id = (osp_param('przelewy24_shop_id') == '' ? osp_param('przelewy24_merchant_id') : osp_param('przelewy24_shop_id'));


    $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
    
    $product = explode('x', $itemnumber);
    $item = array();
    
    if(isset($product[1]) && $product[1] == 2 && isset($product[2]) && $product[2] > 0) {
      $item = Item::newInstance()->findByPrimaryKey($product[2]);
    }
    
    $email = isset($item['s_contact_email']) ? $item['s_contact_email'] : '';
    $email = isset($user['s_email']) ? $user['s_email'] : $email;

    $name = isset($item['s_contact_name']) ? $item['s_contact_name'] : '';
    $name = isset($user['s_name']) ? $user['s_name'] : $name;
    
    $user_id = isset($item['fk_i_user_id']) ? $item['fk_i_user_id'] : '';
    $user_id = isset($user['pk_i_id']) ? $user['pk_i_id'] : $user_id;


    if(osp_param('przelewy24_sandbox') == 1) {
      $URL = 'https://sandbox.przelewy24.pl/';
    } else {
      $URL = 'https://secure.przelewy24.pl/';
    }

    $session_id = md5(session_id().date("YmdHis"));

    $pending_data = array(
      'fk_i_user_id' => $user_id,
      's_transaction_id' => $session_id,
      's_email' => $email,
      's_extra' => $extra,
      's_source' => 'PRZELEWY24',
      'dt_date' => date('Y-m-d h:i:s')
    );


    $order_id = ModelOSP::newInstance()->insertPending($pending_data);

    $salt = md5($session_id . "|" . osp_param('przelewy24_merchant_id') . "|" . ($amount*100) . "|" . osp_currency() . "|" . osp_param('przelewy24_crc_key'));

    //$RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php?p24_session_id=' . $session_id . '&amount=' . ($amount*100);
    $RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php';
    $STATUSURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'status.php';

    ?>


    <li class="payment przelewy24-btn">
      <a class="osp-has-tooltip" title="<?php echo osc_esc_html(__('Form to submit your details will pop-up', 'osclass_pay')); ?>" href="#" >
        <span><img src="<?php echo osp_url(); ?>img/payments/przelewy24.png"/></span>
        <strong><?php _e('Pay with Przelewy24', 'osclass_pay'); ?></strong>
      </a>
    </li>


    <div id="przelewy24-dialog" title="<?php _e('Przelewy24', 'osclass_pay'); ?>" style="display:none;">
      <div id="przelewy24-response"></div>

      <div id="przelewy24-info">
        <div id="przelewy24-data">
          <p id="przelewy24-desc"><?php echo $description; ?></p>
          <label><?php _e('Payment amount', 'osclass_pay'); ?></label>
          <p id="przelewy24-price"><?php echo osp_format_price($amount); ?></p>
        </div>

        <form action="<?php echo $URL; ?>" method="POST" class="nocsrf" id="przelewy24-form">

          <input type="hidden" name="p24_merchant_id" value="<?php echo osp_param('przelewy24_merchant_id'); ?>" />
          <input type="hidden" name="p24_pos_id" value="<?php echo $pos_id; ?>" />
          <input type="hidden" name="p24_sign" value="<?php echo $salt; ?>" />
          <input type="hidden" name="p24_session_id" value="<?php echo $session_id; ?>" />
          <input type="hidden" name="p24_amount" value="<?php echo round($amount*100); ?>" />
          <input type="hidden" name="p24_currency" value="<?php echo osp_currency(); ?>" />
          <input type="hidden" name="p24_description" value="<?php echo $description; ?>" />
          <input type="hidden" name="p24_email" value="<?php echo $email; ?>" />
          <input type="hidden" name="p24_client" value="<?php echo $name; ?>" />
          <input type="hidden" name="p24_url_return" value="<?php echo $RETURNURL; ?>"/>
          <input type="hidden" name="p24_url_status" value="<?php echo $STATUSURL; ?>"/>
          <input type="hidden" name="p24_name_1" value="<?php echo $description; ?>" />
          <input type="hidden" name="p24_quantity_1" value="1" />
          <input type="hidden" name="p24_price_1" value="<?php echo round($amount*100); ?>" />
          <input type="hidden" name="p24_api_version" value="<?php echo P24_VERSION; ?>" />
          <input type="hidden" name="p24_language" value="<?php echo osp_param('przelewy24_language'); ?>" />
          <input type="hidden" name="p24_time_limit" value="29" />
          <input type="hidden" name="p24_wait_for_result" value="1" />
          <input type="hidden" name="p24_transfer_label" value="<?php echo $itemnumber; ?>" />
          <input type="hidden" name="p24_ecod" value="" />



          <p class="rw">
            <label><?php _e('Country Code', 'osclass_pay'); ?></label>
            <span class="osp-input-box">
              <?php $countries = Country::newInstance()->listAll(); ?>

              <?php if(count($countries) > 0) { ?>
                <select name="p24_country" required>
                  <?php foreach($countries as $c) { ?>
                    <option value="<?php echo $c['pk_c_code']; ?>" <?php if($c['pk_c_code'] == $user['fk_c_country_code']) { ?>selected="selected"<?php } ?>><?php echo $c['s_name']; ?></option>
                  <?php } ?>
                </select>
              <?php } else { ?>
                <input type="text" name="p24_country" max-length="2" value="<?php echo $user['fk_c_country_code']; ?>" />
              <?php } ?>
             </span>
          </p>

          <p class="rw rw-cty">
            <label><?php _e('City', 'osclass_pay'); ?></label>
            <span class="osp-input-box">
              <input type="text" name="p24_city" value="<?php echo $user['s_city']; ?>" required/>
            </span>
          </p>

          <p class="rw rw-zip">
            <label><?php _e('ZIP', 'osclass_pay'); ?></label>
            <span class="osp-input-box">
              <input type="text" name="p24_zip" value="<?php echo $user['s_zip']; ?>" required/>
            </span>
          </p>

          <p class="rw">
            <label><?php _e('Address', 'osclass_pay'); ?></label>
            <span class="osp-input-box">
              <input type="text" name="p24_address" value="<?php echo $user['s_address']; ?>" required/>
            </span>
          </p>


          <button type="submit" id="submit"><?php echo osc_esc_html(__('Pay', 'osclass_pay')); ?> <span></span></button>
        </form>
      </div>
    </div>

    <script type="text/javascript">
      $(document).ready(function(){
        $("#przelewy24-dialog").dialog({
          autoOpen: false,
          dialogClass: "osp-dialog przelewy24-dialog",
          modal: true,
          show: { effect: 'fade', duration: 200 },
          hide: { effect: 'fade', duration: 200 }
        });

        $('body').on('click', '.payment.przelewy24-btn', function(e) {
          e.preventDefault();
          $("#przelewy24-dialog").dialog('open');
        });

        $('body').on('submit', '#przelewy24-form', function(e) {
          e.preventDefault();

          $.ajax({
            type: "POST",
            url: '<?php echo osc_base_url(true); ?>',
            data: $("#przelewy24-form").serialize(),
            dataType: 'json',
            success: function(data) {
              if(data.error !== 0) {
                $('#przelewy24-response').fadeIn(200).text(data.errorMessage);
              } else {
                window.location.replace(data.url);
              }
            }
          });
        });
      });
    </script>

    <?php
  }



  // AJAX FUNCTION TO PROCESS PAYMENT, HOOKED TO AJAX_TWOCHECKOUT
  public static function preparePayment() {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'src/class_przelewy24.php';

    if(Params::getParam('p24_merchant_id') <> '' && Params::getParam('p24_order_id') == '') {

      $pos_id = (osp_param('przelewy24_shop_id') == '' ? osp_param('przelewy24_merchant_id') : osp_param('przelewy24_shop_id'));

      $test = (osp_param('przelewy24_sandbox') == 1 ? "1" : "0");
      $salt = osp_param('przelewy24_crc_key');
      
      $P24 = new Przelewy24(osp_param('przelewy24_merchant_id'), $pos_id, $salt, $test);
     
      foreach($_POST as $k=>$v) {
        $P24->addValue($k,$v);                            
      }

      $res = $P24->trnRegister(false);
      $url = $P24->trnRequest(@$res['token'], false);

      $error_message = (@$res['error'] !== 0 ? __('There was error processing payment:', 'osclass_pay') . ' ' . @$res['errorMessage'] : '');
      
      echo json_encode(array('error' => @$res['error'], 'errorMessage' => $error_message, 'token' => @$res['token'], 'url' => $url));
      exit;
    }
  }     

  

  // PROCESS PAYMENT ON PLUGIN SIDE
  public static function processPayment() {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'src/class_przelewy24.php';

    $pending = ModelOSP::newInstance()->getPendingByTransactionId(Params::getParam('p24_session_id'), 'PRZELEWY24');

    if(!$pending || !isset($pending['s_extra']) || $pending['s_extra'] == '' || $pending['pk_i_id'] <= 0) {
      return array(OSP_STATUS_FAILED, __('Failed - unable to find data related to this order (Pending item missing)', 'osclass_pay')); 
    }

    $extra = $pending['s_extra'];     // get pending row
    $data = osp_get_custom($extra);
    $product_type = explode('x', $data['product']);

    Params::setParam('extra', $extra);

    $amount = Params::getParam('p24_amount')/100; 

    if($amount <= 0) { 
      return array(OSP_STATUS_AMOUNT_ZERO, ''); 
    }


    $tid = Params::getParam('p24_session_id');
    Params::setParam('przelewy24_transaction_id', $tid);
    $payment = ModelOSP::newInstance()->getPaymentByCode($tid, 'PRZELEWY24');


    $pos_id = (osp_param('przelewy24_shop_id') == '' ? osp_param('przelewy24_merchant_id') : osp_param('przelewy24_shop_id'));

    $test = (osp_param('przelewy24_sandbox') == 1 ? "1" : "0");
    $salt = osp_param('przelewy24_crc_key');
     
    $P24 = new Przelewy24(osp_param('przelewy24_merchant_id'), $pos_id, $salt, $test);
     
    foreach($_POST as $k=>$v) { 
      $P24->addValue($k,$v);                            
    }

    $P24->addValue('p24_currency', osp_currency());
    $P24->addValue('p24_amount', round($data['amount']*100));

    $res = $P24->trnVerify();


    if(isset($res['error']) and $res['error'] === '0') {
      if(!$payment) { 
        // SAVE TRANSACTION LOG
        $payment_id = ModelOSP::newInstance()->saveLog(
          $data['concept'], //concept
          $tid, // transaction code
          $amount, //amount
          strtoupper(osp_currency()), //currency
          $data['email'], // payer's email
          $data['user'], //user
          osp_create_cart_string($product_type[1], $data['user'], $product_type[2]), // $data['itemid']), // cart string
          $product_type[0], //product type
          'PRZELEWY24' //source
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

    return array(OSP_STATUS_FAILED, __('Payment verification has failed:', 'osclass_pay') . ' ' . $res['errorMessage']);



  }
}
?>