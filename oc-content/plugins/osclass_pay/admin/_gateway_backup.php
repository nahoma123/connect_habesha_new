<?php
  // Create menu 
  $title = __('Payment Gateways', 'osclass_pay');
  osp_menu($title);


  // GET & UPDATE PARAMETERS
  // $variable = osp_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value or value_crypt


  $bt_enabled = osp_param_update( 'bt_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $bt_iban = osp_param_update( 'bt_iban', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $bt_min = osp_param_update( 'bt_min', 'plugin_action', 'value', 'plugin-osclass_pay' );

  $paypal_enabled = osp_param_update( 'paypal_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $paypal_api_username = osp_param_update( 'paypal_api_username', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $paypal_api_password = osp_param_update( 'paypal_api_password', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $paypal_api_signature = osp_param_update( 'paypal_api_signature', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $paypal_email = osp_param_update( 'paypal_email', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $paypal_standard = osp_param_update( 'paypal_standard', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $paypal_sandbox = osp_param_update( 'paypal_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );

  $stripe_enabled = osp_param_update( 'stripe_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $stripe_public_key = osp_param_update( 'stripe_public_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $stripe_secret_key = osp_param_update( 'stripe_secret_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $stripe_sandbox = osp_param_update( 'stripe_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $stripe_public_key_test = osp_param_update( 'stripe_public_key_test', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $stripe_secret_key_test = osp_param_update( 'stripe_secret_key_test', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );

  $skrill_enabled = osp_param_update( 'skrill_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $skrill_merchant_id = osp_param_update( 'skrill_merchant_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $skrill_secret_word = osp_param_update( 'skrill_secret_word', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $skrill_email = osp_param_update( 'skrill_email', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $skrill_notify = osp_param_update( 'skrill_notify', 'plugin_action', 'check', 'plugin-osclass_pay' );

  $securionpay_enabled = osp_param_update( 'securionpay_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $securionpay_public_key = osp_param_update( 'securionpay_public_key', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $securionpay_secret_key = osp_param_update( 'securionpay_secret_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  
  $payza_enabled = osp_param_update( 'payza_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $payza_email = osp_param_update( 'payza_email', 'plugin_action', 'value', 'plugin-osclass_pay' );

  $przelewy24_enabled = osp_param_update( 'przelewy24_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $przelewy24_merchant_id = osp_param_update( 'przelewy24_merchant_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $przelewy24_shop_id = osp_param_update( 'przelewy24_shop_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $przelewy24_crc_key = osp_param_update( 'przelewy24_crc_key', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $przelewy24_sandbox = osp_param_update( 'przelewy24_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $przelewy24_language = osp_param_update( 'przelewy24_language', 'plugin_action', 'value', 'plugin-osclass_pay' );

  $payherelk_enabled = osp_param_update( 'payherelk_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $payherelk_merchant_id = osp_param_update( 'payherelk_merchant_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $payherelk_secret = osp_param_update( 'payherelk_secret', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $payherelk_sandbox = osp_param_update( 'payherelk_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );

  $blockchain_enabled = osp_param_update( 'blockchain_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $blockchain_address = osp_param_update( 'blockchain_address', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $blockchain_key = osp_param_update( 'blockchain_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $blockchain_xpub = osp_param_update( 'blockchain_xpub', 'plugin_action', 'value', 'plugin-osclass_pay' );

  $braintree_enabled = osp_param_update( 'braintree_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $braintree_merchant_id = osp_param_update( 'braintree_merchant_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $braintree_public_key = osp_param_update( 'braintree_public_key', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $braintree_private_key = osp_param_update( 'braintree_private_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $braintree_encryption_key = osp_param_update( 'braintree_encryption_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $braintree_sandbox = osp_param_update( 'braintree_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );

  $twocheckout_enabled = osp_param_update( 'twocheckout_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $twocheckout_seller_id = osp_param_update( 'twocheckout_seller_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $twocheckout_secret_word = osp_param_update( 'twocheckout_secret_word', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $twocheckout_type = osp_param_update( 'twocheckout_type', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $twocheckout_publishable_key = osp_param_update( 'twocheckout_publishable_key', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $twocheckout_private_key = osp_param_update( 'twocheckout_private_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $twocheckout_sandbox = osp_param_update( 'twocheckout_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );

  $authorizenet_enabled = osp_param_update( 'authorizenet_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $authorizenet_merchant_login_id = osp_param_update( 'authorizenet_merchant_login_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $authorizenet_merchant_transaction_key = osp_param_update( 'authorizenet_merchant_transaction_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $authorizenet_sandbox = osp_param_update( 'authorizenet_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );

  $pagseguro_enabled = osp_param_update( 'pagseguro_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $pagseguro_email = osp_param_update( 'pagseguro_email', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $pagseguro_token = osp_param_update( 'pagseguro_token', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $pagseguro_application_id = osp_param_update( 'pagseguro_application_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $pagseguro_application_key = osp_param_update( 'pagseguro_application_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $pagseguro_sb_token = osp_param_update( 'pagseguro_sb_token', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $pagseguro_sb_application_id = osp_param_update( 'pagseguro_sb_application_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $pagseguro_sb_application_key = osp_param_update( 'pagseguro_sb_application_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $pagseguro_sandbox = osp_param_update( 'pagseguro_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $pagseguro_lightbox = osp_param_update( 'pagseguro_lightbox', 'plugin_action', 'check', 'plugin-osclass_pay' );

  $payumoney_enabled = osp_param_update( 'payumoney_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $payumoney_merchant_key = osp_param_update( 'payumoney_merchant_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $payumoney_salt = osp_param_update( 'payumoney_salt', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $payumoney_sandbox = osp_param_update( 'payumoney_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );

  $instamojo_enabled = osp_param_update( 'instamojo_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $instamojo_api_key = osp_param_update( 'instamojo_api_key', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $instamojo_auth_token = osp_param_update( 'instamojo_auth_token', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $instamojo_salt = osp_param_update( 'instamojo_salt', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $instamojo_sandbox = osp_param_update( 'instamojo_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );

  $payulatam_enabled = osp_param_update( 'payulatam_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $payulatam_merchant_id = osp_param_update( 'payulatam_merchant_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $payulatam_account_id = osp_param_update( 'payulatam_account_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $payulatam_api_key = osp_param_update( 'payulatam_api_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $payulatam_sandbox = osp_param_update( 'payulatam_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );

  $ccavenue_enabled = osp_param_update( 'ccavenue_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $ccavenue_merchant_id = osp_param_update( 'ccavenue_merchant_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $ccavenue_working_key = osp_param_update( 'ccavenue_working_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $ccavenue_access_code = osp_param_update( 'ccavenue_access_code', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $ccavenue_language = osp_param_update( 'ccavenue_language', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $ccavenue_sandbox = osp_param_update( 'ccavenue_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );

  $payscz_enabled = osp_param_update( 'payscz_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $payscz_merchant_id = osp_param_update( 'payscz_merchant_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $payscz_shop_id = osp_param_update( 'payscz_shop_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $payscz_api_pass = osp_param_update( 'payscz_api_pass', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );


  $paystack_enabled = osp_param_update( 'paystack_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $paystack_email = osp_param_update( 'paystack_email', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $paystack_public_key = osp_param_update( 'paystack_public_key', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $paystack_test_public_key = osp_param_update( 'paystack_test_public_key', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $paystack_secret_key = osp_param_update( 'paystack_secret_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $paystack_test_secret_key = osp_param_update( 'paystack_test_secret_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $paystack_sandbox = osp_param_update( 'paystack_sandbox', 'plugin_action', 'check', 'plugin-osclass_pay' );

  $weaccept_enabled = osp_param_update( 'weaccept_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $weaccept_integration_id = osp_param_update( 'weaccept_integration_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $weaccept_iframe_id = osp_param_update( 'weaccept_iframe_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $weaccept_api_key = osp_param_update( 'weaccept_api_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );

  $euplatesc_enabled = osp_param_update( 'euplatesc_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $euplatesc_mid = osp_param_update( 'euplatesc_mid', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $euplatesc_key = osp_param_update( 'euplatesc_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );

  $komfortkasse_enabled = osp_param_update( 'komfortkasse_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $komfortkasse_api_key = osp_param_update( 'komfortkasse_api_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );

  $yandex_enabled = osp_param_update( 'yandex_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $yandex_shop_id = osp_param_update( 'yandex_shop_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $yandex_api_secret = osp_param_update( 'yandex_api_secret', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );

  $cardinity_enabled = osp_param_update( 'cardinity_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $cardinity_project_id = osp_param_update( 'cardinity_project_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $cardinity_project_secret = osp_param_update( 'cardinity_project_secret', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );

  $begateway_enabled = osp_param_update( 'begateway_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $begateway_shop_id = osp_param_update( 'begateway_shop_id', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $begateway_public_key = osp_param_update( 'begateway_public_key', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $begateway_domain_checkout = osp_param_update( 'begateway_domain_checkout', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $begateway_secret_key = osp_param_update( 'begateway_secret_key', 'plugin_action', 'value_crypt', 'plugin-osclass_pay' );
  $begateway_timeout = osp_param_update( 'begateway_timeout', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $begateway_test_mode = osp_param_update( 'begateway_test_mode', 'plugin_action', 'check', 'plugin-osclass_pay' );



  if(Params::getParam('plugin_action') == 'done') {
    message_ok( __('Settings were successfully saved', 'osclass_pay') );
  }

  
  // MANAGE BANK TRANSFERS
  if(Params::getParam('btId') <> '' && Params::getParam('btId') > 0 && Params::getParam('status') <> '') {
    $id = Params::getParam('btId');
    $status = Params::getParam('status');

    if($status == 1) {
      // Pay it
      $bt = ModelOSP::newInstance()->getBankTransferById($id);
      $tdata = osp_get_custom($bt['s_extra']);
      $user = User::newInstance()->findByPrimaryKey($bt['i_user_id']);

      if(@$user['pk_i_id'] <= 0 || trim(@$user['s_email']) == '') {
        $user = array(
          'pk_i_id' => 0,
          's_name' => @$tdata['name'],
          's_email' => @$tdata['email']
        );
      }
       

      ModelOSP::newInstance()->updateBankTransfer($id, $status);
      osc_add_flash_ok_message(__('Bank transfer accepted successfully', 'osclass_pay'), 'admin');

      // pay cart content
      $cart = $bt['s_cart'];

      if($cart <> '' && $bt['dt_date_paid'] == '') {  // run promotion just in case it was not already accepted
        $products = explode('|', $cart);
  
        if(count($products) > 0) {

          // SAVE TRANSACTION LOG
          $payment_id = ModelOSP::newInstance()->saveLog(
            $bt['s_description'], //concept
            $bt['s_transaction'], // transaction code
            $bt['f_price'], //amount
            strtoupper(osp_currency()), //currency
            isset($user['s_email']) ? $user['s_email'] : '', // payer's email
            $bt['i_user_id'], //user
            $bt['s_cart'], // cart string
            OSP_TYPE_MULTIPLE, //product type
            'TRANSFER' //source
          );


          foreach($products as $p) {
            $c = array_merge(array($p), explode('x', $p));

            $type = $c[1];
            $item = array('type' => $c[1], 'quantity' => $c[2], 'item_id' => $c[3], 'payment_id' => $payment_id);

            if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE || $type == OSP_TYPE_PREMIUM || $type == OSP_TYPE_HIGHLIGHT || $type == OSP_TYPE_REPUBLISH || $type == OSP_TYPE_TOP) {
              $item = array_merge($item, array('hours' => @$c[4], 'repeat' => @$c[5]));

            } else if($type == OSP_TYPE_PACK) {
              $item = array_merge($item, array('pack_id' => $c[3], 'pack_value' => $c[4], 'pack_user_id' => $bt['i_user_id']));

            } else if($type == OSP_TYPE_MEMBERSHIP) {
              $item = array_merge($item, array('group_id' => $c[3], 'group_days' => $c[4], 'group_user_id' => $bt['i_user_id']));

            } else if($type == OSP_TYPE_BANNER) {
              $item = array_merge($item, array('banner_id' => $c[3], 'banner_budget' => $c[4]));

            } else if($type == OSP_TYPE_PRODUCT) {
              if(osp_param('stock_management') == 1) {
                $item = Item::newInstance()->findByPrimaryKey($c[3]);
                $item_data = ModelOSP::newInstance()->getItemData($c[3]);
                $avl_quantity = isset($item_data['i_quantity']) ? $item_data['i_quantity'] : 0;

                if($c[2] > $avl_quantity) {
                  osc_add_flash_warning_message(sprintf(__('Insufficient quantity on stock for product %s! We have %s items on stock, you have requested %s. Our team may contact you.', 'osclass_pay'), '<strong>' . @$item['s_title'] . '</strong>', $avl_quantity, $c[2]));
                }
              }

              $update_qty = (osp_param('stock_management') == 0 ? 0 : $c[2]);

              ModelOSP::newInstance()->updateItemQuantity($c[3], -$update_qty); 
            }

            $order_id = ModelOSP::newInstance()->createOrder($payment_id);

            if($order_id !== false) {
              osp_email_order($order_id, 1);
            }

            osp_pay_fee($item);
          }
        }
      }

    } else if($status == 2) {
      // Cancel it
      ModelOSP::newInstance()->updateBankTransfer($id, $status);
      osc_add_flash_ok_message(__('Bank transfer cancelled successfully', 'osclass_pay'), 'admin');
    } else if($status == 9) {
      // Remove it
      ModelOSP::newInstance()->deleteBankTransfer($id);
      osc_add_flash_ok_message(__('Bank transfer removed successfully', 'osclass_pay'), 'admin');
    }

    osp_redirect(osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/gateway.php&goto=1'));
  }



  // SCROLL TO DIV
  if(Params::getParam('position') == '2') {
    osp_js_scroll('.mb-methods');
  } else if (Params::getParam('btId') <> '' || Params::getParam('goto') == 1) {
    osp_js_scroll('.mb-transfer');
  }
?>



<div class="mb-body">

  <!-- PAYMENT METHODS SECTION -->
  <div class="mb-box mb-methods">
    <div class="mb-head"><i class="fa fa-id-card"></i> <?php _e('Payment Methods', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>gateway.php" />
        <input type="hidden" name="plugin_action" value="done" />
        <input type="hidden" name="position" value="2" />


        <!-- BANK TRANSFER -->
        <div class="mb-method mb-bt <?php if($bt_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($bt_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Bank Transfer Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/banktransfer.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('Note that each payment must be accepted/approved by admin. After approval, promotions are executed.', 'osclass_pay'); ?></div>
              <div class="mb-line"><?php _e('Approve transaction just in case you see it on your bank account, this action cannot be undone.', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-line">
              <label for="bt_enabled"><span><?php _e('Enable Bank Transfer Payments', 'osclass_pay'); ?></span></label>
              <input name="bt_enabled" id="bt_enabled" class="element-slide" type="checkbox" <?php echo ($bt_enabled == 1 ? 'checked' : ''); ?> />

              <div class="mb-explain"><?php _e('Using bank transfer is possible to pay just for credit packs.', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-line">
              <label for="bt_iban"><span><?php _e('Bank Transfer IBAN', 'osclass_pay'); ?></span></label>
              <input name="bt_iban" id="bt_iban" type="text" value="<?php echo $bt_iban; ?>" style="width:240px;"/>
            </div>

            <div class="mb-line">
              <label for="bt_min"><span><?php _e('Minimum Value for Transfer', 'osclass_pay'); ?></span></label>
              <input name="bt_min" id="bt_min" type="text" value="<?php echo $bt_min; ?>" style="width:80px;text-align:right;" />
              <div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div>

              <div class="mb-explain"><?php _e('Payments with amount less than minimum will not be possible to pay using Bank Transfer.', 'osclass_pay'); ?></div>
            </div>

            <?php if($bt_enabled == 1) { ?>
              <div class="mb-line"><a href="#" id="mb-move-to-bt"><i class="fa fa-exchange"></i>&nbsp;&nbsp;<?php _e('Show existing bank transfers', 'osclass_pay'); ?></a></div>
            <?php } ?>
          </div>
        </div>


        <!-- PAYPAL -->
        <div class="mb-method mb-paypal <?php if($paypal_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($paypal_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('PayPal Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/paypl.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('It is preferred to use Digital Goods checkout if available, it is more user friendly.', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-row mb-method-links">
              <a href="https://www.paypal.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Paypal Production', 'osclass_pay'); ?></span></a>
              <a href="https://developer.paypal.com/developer/accounts/" target="_blank" class="mb-sand osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-cubes"></i> <span><?php _e('Open Paypal Sandbox', 'osclass_pay'); ?></span></a>
              <a href="https://developer.paypal.com/docs/" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Paypal Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="paypal_enabled"><span><?php _e('Enable PayPal Payments', 'osclass_pay'); ?></span></label>
              <input name="paypal_enabled" id="paypal_enabled" class="element-slide" type="checkbox" <?php echo ($paypal_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="paypal_api_username"><span><?php _e('PayPal API Username', 'osclass_pay'); ?></span></label>
              <input name="paypal_api_username" id="paypal_api_username" type="text" value="<?php echo $paypal_api_username; ?>" />
            </div>

            <div class="mb-line">
              <label for="paypal_api_signature"><span><?php _e('PayPal API Signature', 'osclass_pay'); ?></span></label>
              <input name="paypal_api_signature" id="paypal_api_signature" type="text" value="<?php echo $paypal_api_signature; ?>" />
            </div>

            <div class="mb-line">
              <label for="paypal_api_password"><span><?php _e('PayPal API Password', 'osclass_pay'); ?></span></label>
              <input name="paypal_api_password" id="paypal_api_password" type="password" value="<?php echo $paypal_api_password; ?>" />
            </div>

            <div class="mb-line">
              <label for="paypal_email"><span><?php _e('PayPal Email', 'osclass_pay'); ?></span></label>
              <input name="paypal_email" id="paypal_email" type="text" value="<?php echo $paypal_email; ?>" />
            </div>

            <div class="mb-line">
              <label for="paypal_standard"><span><?php _e('PayPal Standard Payments', 'osclass_pay'); ?></span></label>
              <input name="paypal_standard" id="paypal_standard" class="element-slide" type="checkbox" <?php echo ($paypal_standard == 1 ? 'checked' : ''); ?> />
              <div class="mb-explain"><?php _e('Use "Standard Payments" if "Digital Goods" is not available in your country', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-line">
              <label for="paypal_sandbox"><span><?php _e('PayPal Sandbox Enabled', 'osclass_pay'); ?></span></label>
              <input name="paypal_sandbox" id="paypal_sandbox" class="element-slide" type="checkbox" <?php echo ($paypal_sandbox == 1 ? 'checked' : ''); ?> />
            </div>
          </div>
        </div>



        <!-- PRZELEWY24 -->
        <div class="mb-method mb-przelewy24 <?php if($przelewy24_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($przelewy24_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Przelewy24 Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/przelewy24.png"/>
          </div>

          <div class="mb-method-body">
            <?php if(!in_array(osp_currency(), array('PLN', 'EUR', 'CZK'))) { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('Przelewy24 not available, currency must be set to PLN, EUR or CZK to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>

            <div class="mb-row mb-method-links">
              <a href="https://panel.przelewy24.pl/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Przelewy24 Production', 'osclass_pay'); ?></span></a>
              <a href="https://sandbox.przelewy24.pl/panel" target="_blank" class="mb-sand osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-cubes"></i> <span><?php _e('Open Przelewy24 Sandbox', 'osclass_pay'); ?></span></a>
              <a href="https://www.przelewy24.pl/eng/download/page#installation" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Przelewy24 Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="przelewy24_enabled"><span><?php _e('Enable Przelewy24 Payments', 'osclass_pay'); ?></span></label>
              <input name="przelewy24_enabled" id="przelewy24_enabled" class="element-slide" type="checkbox" <?php echo ($przelewy24_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="przelewy24_merchant_id"><span><?php _e('Przelewy24 Merchant Id', 'osclass_pay'); ?></span></label>
              <input name="przelewy24_merchant_id" id="przelewy24_merchant_id" type="text" value="<?php echo $przelewy24_merchant_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="przelewy24_shop_id"><span><?php _e('Przelewy24 Shop/POS Id', 'osclass_pay'); ?></span></label>
              <input name="przelewy24_shop_id" id="przelewy24_shop_id" type="text" value="<?php echo $przelewy24_shop_id; ?>" />

              <div class="mb-explain"><?php _e('Leave blank or enter Merchant ID if not applicable for you.', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-line">
              <label for="przelewy24_crc_key"><span><?php _e('Przelewy24 CRC Key', 'osclass_pay'); ?></span></label>
              <input name="przelewy24_crc_key" id="przelewy24_crc_key" type="text" value="<?php echo $przelewy24_crc_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="przelewy24_language"><span><?php _e('Przelewy24 UI Language', 'osclass_pay'); ?></span></label>
              <select name="przelewy24_language" id="przelewy24_language">
                <option value="pl" <?php if($przelewy24_language == 'pl') { ?>selected="selected"<?php } ?>><?php _e('Poland', 'osclass_pay'); ?></option>
                <option value="en" <?php if($przelewy24_language == 'en') { ?>selected="selected"<?php } ?>><?php _e('English', 'osclass_pay'); ?></option>
                <option value="de" <?php if($przelewy24_language == 'de') { ?>selected="selected"<?php } ?>><?php _e('German', 'osclass_pay'); ?></option>
                <option value="es" <?php if($przelewy24_language == 'es') { ?>selected="selected"<?php } ?>><?php _e('Spanish', 'osclass_pay'); ?></option>
                <option value="it" <?php if($przelewy24_language == 'it') { ?>selected="selected"<?php } ?>><?php _e('Italian', 'osclass_pay'); ?></option>
              </select>
            </div>


            <div class="mb-line">
              <label for="przelewy24_sandbox"><span><?php _e('Enable Sandbox', 'osclass_pay'); ?></span></label>
              <input name="przelewy24_sandbox" id="przelewy24_sandbox" class="element-slide" type="checkbox" <?php echo ($przelewy24_sandbox == 1 ? 'checked' : ''); ?> />
            </div>
          </div>
        </div>


        <!-- PAYHERELK -->
        <div class="mb-method mb-payherelk <?php if($payherelk_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($payherelk_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Payhere.lk Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/payherelk.png"/>
          </div>

          <div class="mb-method-body">
            <?php if(!in_array(osp_currency(), array('USD', 'LKR'))) { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('payherelk not available, currency must be set to USD or LKR to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>

            <div class="mb-row mb-method-links">
              <a href="https://payhere.lk/account/dashboard" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Payhere.lk Production', 'osclass_pay'); ?></span></a>
              <a href="https://sandbox.payhere.lk/account/dashboard" target="_blank" class="mb-sand osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-cubes"></i> <span><?php _e('Open Payhere.lk Sandbox', 'osclass_pay'); ?></span></a>
              <a href="https://support.payhere.lk/api-&-mobile-sdk/payhere-checkout" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Payhere.lk Documentation', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="payherelk_enabled"><span><?php _e('Enable Payhere.lk Payments', 'osclass_pay'); ?></span></label>
              <input name="payherelk_enabled" id="payherelk_enabled" class="element-slide" type="checkbox" <?php echo ($payherelk_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="payherelk_merchant_id"><span><?php _e('Payhere.lk Merchant Id', 'osclass_pay'); ?></span></label>
              <input name="payherelk_merchant_id" id="payherelk_merchant_id" type="text" value="<?php echo $payherelk_merchant_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="payherelk_secret"><span><?php _e('Payhere.lk Secret Key', 'osclass_pay'); ?></span></label>
              <input name="payherelk_secret" id="payherelk_secret" type="password" value="<?php echo $payherelk_secret; ?>" />
            </div>

            <div class="mb-line">
              <label for="payherelk_sandbox"><span><?php _e('Enable Sandbox', 'osclass_pay'); ?></span></label>
              <input name="payherelk_sandbox" id="payherelk_sandbox" class="element-slide" type="checkbox" <?php echo ($payherelk_sandbox == 1 ? 'checked' : ''); ?> />
            </div>
          </div>
        </div>


        <!-- STRIPE -->
        <div class="mb-method mb-stripe <?php if($stripe_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($stripe_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Stripe Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/stripe.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://stripe.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Stripe Production', 'osclass_pay'); ?></span></a>
              <a href="https://stripe.com/docs/testing" target="_blank" class="mb-sand osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-cubes"></i> <span><?php _e('Open Stripe Sandbox', 'osclass_pay'); ?></span></a>
              <a href="https://stripe.com/docs" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Stripe Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="stripe_enabled"><span><?php _e('Enable Stripe Payments', 'osclass_pay'); ?></span></label>
              <input name="stripe_enabled" id="stripe_enabled" class="element-slide" type="checkbox" <?php echo ($stripe_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="stripe_public_key"><span><?php _e('Stripe Publishable Key', 'osclass_pay'); ?></span></label>
              <input name="stripe_public_key" id="stripe_public_key" type="text" value="<?php echo $stripe_public_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="stripe_secret_key"><span><?php _e('Stripe Secret Key', 'osclass_pay'); ?></span></label>
              <input name="stripe_secret_key" id="stripe_secret_key" type="password" value="<?php echo $stripe_secret_key ; ?>" />
            </div>


            <div class="mb-line">
              <label for="stripe_sandbox"><span><?php _e('Enable Sandbox', 'osclass_pay'); ?></span></label>
              <input name="stripe_sandbox" id="stripe_sandbox" class="element-slide" type="checkbox" <?php echo ($stripe_sandbox == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="stripe_public_key_test"><span><?php _e('Stripe Test Publishable Key', 'osclass_pay'); ?></span></label>
              <input name="stripe_public_key_test" id="stripe_public_key_test" type="text" value="<?php echo $stripe_public_key_test; ?>" />
            </div>

            <div class="mb-line">
              <label for="stripe_secret_key_test"><span><?php _e('Stripe Test Secret Key', 'osclass_pay'); ?></span></label>
              <input name="stripe_secret_key_test" id="stripe_secret_key_test" type="password" value="<?php echo $stripe_secret_key_test; ?>" />
            </div>
          </div>
        </div>


        <!-- SKRILL -->
        <div class="mb-method mb-skrill <?php if($skrill_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($skrill_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Skrill Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/skrill.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('Skrill does not have sandbox, in regard to test this gateway, contact their support to convert your account to testing one or look for testing credentials on google or in their docs.', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-row mb-method-links">
              <a href="https://www.skrill.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Skrill Production', 'osclass_pay'); ?></span></a>
              <a href="https://www.skrill.com/en/support" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Stripe Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="skrill_enabled"><span><?php _e('Enable Skrill Payments', 'osclass_pay'); ?></span></label>
              <input name="skrill_enabled" id="skrill_enabled" class="element-slide" type="checkbox" <?php echo ($skrill_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="skrill_merchant_id"><span><?php _e('Skrill Merchant ID', 'osclass_pay'); ?></span></label>
              <input name="skrill_merchant_id" id="skrill_merchant_id" type="text" value="<?php echo $skrill_merchant_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="skrill_email"><span><?php _e('Skrill Email', 'osclass_pay'); ?></span></label>
              <input name="skrill_email" id="skrill_email" type="text" value="<?php echo $skrill_email; ?>" />
            </div>

            <div class="mb-line">
              <label for="skrill_secret_word"><span><?php _e('Skrill Secret Word', 'osclass_pay'); ?></span></label>
              <input name="skrill_secret_word" id="skrill_secret_word" type="password" value="<?php echo $skrill_secret_word; ?>" />
            </div>

            <div class="mb-line">
              <label for="skrill_notify"><span><?php _e('Send Payment Notifications', 'osclass_pay'); ?></span></label>
              <input name="skrill_notify" id="skrill_notify" class="element-slide" type="checkbox" <?php echo ($skrill_notify == 1 ? 'checked' : ''); ?> />
              <div class="mb-explain"><?php _e('Admin will receive notifications regarding payments.', 'osclass_pay'); ?></div>
            </div>
          </div>
        </div>


        <!-- AUTHORIZENET -->
        <div class="mb-method mb-authorizenet <?php if($authorizenet_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($authorizenet_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Authorize.Net Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/authorize.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('Authorize.net operates in currency USD only, plugin must be set to this currency, otherwise payment will not be available.', 'osclass_pay'); ?></div>
              <div class="mb-line"><?php _e('Make sure to perform your registration on correct site, otherwise you will have troubles to make gateway work.', 'osclass_pay'); ?></div>
            </div>

            <?php if(osp_currency() <> 'USD') { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('Authorize.net not available, currency must be set to USD to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>

            <div class="mb-row mb-method-links">
              <a href="https://www.authorize.net/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Authorize.net Production', 'osclass_pay'); ?></span></a>
              <a href="https://sandbox.authorize.net/" target="_blank" class="mb-sand osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-cubes"></i> <span><?php _e('Open Authorize.net Sandbox', 'osclass_pay'); ?></span></a>
              <a href="http://developer.authorize.net/api/reference/index.html" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Authorize.net Help', 'osclass_pay'); ?></span></a>
            </div>


            <div class="mb-line">
              <label for="authorizenet_enabled"><span><?php _e('Enable Authorize.Net Payments', 'osclass_pay'); ?></span></label>
              <input name="authorizenet_enabled" id="authorizenet_enabled" class="element-slide" type="checkbox" <?php echo ($authorizenet_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="authorizenet_merchant_login_id"><span><?php _e('Authorize.Net Merchant Login ID', 'osclass_pay'); ?></span></label>
              <input name="authorizenet_merchant_login_id" id="authorizenet_merchant_login_id" type="text" value="<?php echo $authorizenet_merchant_login_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="authorizenet_merchant_transaction_key"><span><?php _e('Authorize.Net Merchant Transaction Key', 'osclass_pay'); ?></span></label>
              <input name="authorizenet_merchant_transaction_key" id="authorizenet_merchant_transaction_key" type="password" value="<?php echo $authorizenet_merchant_transaction_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="authorizenet_sandbox"><span><?php _e('Enable Sandbox', 'osclass_pay'); ?></span></label>
              <input name="authorizenet_sandbox" id="authorizenet_sandbox" class="element-slide" type="checkbox" <?php echo ($authorizenet_sandbox == 1 ? 'checked' : ''); ?> />
            </div>
          </div>
        </div>


        <!-- PAYZA -->
        <div class="mb-method mb-payza <?php if($payza_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($payza_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Payza Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/payza.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('In order to make Payza fully functional, you need to enable IPN v2 in your Payza account. Otherwise payments will not be received.', 'osclass_pay'); ?></div>
              <div class="mb-line"><?php _e('Payza sandbox/test mode must be enabled/disabled in Payza account (IPN section).', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-row mb-method-links">
              <a href="https://www.payza.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Payza Production', 'osclass_pay'); ?></span></a>
              <a href="https://sandbox.payza.com/center/" target="_blank" class="mb-sand osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-cubes"></i> <span><?php _e('Open Payza Sandbox', 'osclass_pay'); ?></span></a>
              <a href="https://docs.payza.com/" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Payza Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="payza_enabled"><span><?php _e('Enable Payza Payments', 'osclass_pay'); ?></span></label>
              <input name="payza_enabled" id="payza_enabled" class="element-slide" type="checkbox" <?php echo ($payza_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="payza_email"><span><?php _e('Payza Email', 'osclass_pay'); ?></span></label>
              <input name="payza_email" id="payza_email" type="text" value="<?php echo $payza_email; ?>" />
            </div>
          </div>
        </div>


        <!-- BLOCKCHAIN -->
        <div class="mb-method mb-blockchain <?php if($blockchain_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($blockchain_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Blockchain Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/blockchain.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('To get your address, click on "Request" button in Blockchain account.', 'osclass_pay'); ?></div>
              <div class="mb-line"><?php _e('Blockchain does not have sandbox/test mode, in order to test your payments, you need to use live account and money.', 'osclass_pay'); ?></div>
              <div class="mb-line"><?php _e('Request API Key on following link, you will receive it to your mail: https://api.blockchain.info/v2/apikey/request/.', 'osclass_pay'); ?></div>
              <div class="mb-line"><?php _e('Get xPub Key in your account: Settings > Addresses > Manage > More Options > Show xPub', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-row mb-method-links">
              <a href="https://www.blockchain.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Blockchain Production', 'osclass_pay'); ?></span></a>
              <a href="https://blockchain.info/api/" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Blockchain Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="blockchain_enabled"><span><?php _e('Enable Blockchain Payments', 'osclass_pay'); ?></span></label>
              <input name="blockchain_enabled" id="blockchain_enabled" class="element-slide" type="checkbox" <?php echo ($blockchain_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="blockchain_address"><span><?php _e('Blockchain Address', 'osclass_pay'); ?></span></label>
              <input name="blockchain_address" id="blockchain_address" type="text" value="<?php echo $blockchain_address; ?>" />
            </div>

            <div class="mb-line">
              <label for="blockchain_key"><span><?php _e('Blockchain API Key', 'osclass_pay'); ?></span></label>
              <input name="blockchain_key" id="blockchain_key" type="password" value="<?php echo $blockchain_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="blockchain_xpub"><span><?php _e('Blockchain xPub Key', 'osclass_pay'); ?></span></label>
              <input name="blockchain_xpub" id="blockchain_xpub" type="text" value="<?php echo $blockchain_xpub; ?>" />
            </div>
          </div>
        </div>


        <!-- BRAINTREE -->
        <div class="mb-method mb-braintree <?php if($braintree_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($braintree_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Braintree Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/braintree.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://www.braintreepayments.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Braintree Production', 'osclass_pay'); ?></span></a>
              <a href="https://www.braintreepayments.com/sandbox" target="_blank" class="mb-sand osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-cubes"></i> <span><?php _e('Open Braintree Sandbox', 'osclass_pay'); ?></span></a>
              <a href="https://developers.braintreepayments.com/" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Braintree Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="braintree_enabled"><span><?php _e('Enable Braintree Payments', 'osclass_pay'); ?></span></label>
              <input name="braintree_enabled" id="braintree_enabled" class="element-slide" type="checkbox" <?php echo ($braintree_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="braintree_merchant_id"><span><?php _e('Braintree Merchant ID', 'osclass_pay'); ?></span></label>
              <input name="braintree_merchant_id" id="braintree_merchant_id" type="text" value="<?php echo $braintree_merchant_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="braintree_public_key"><span><?php _e('Braintree Public Key', 'osclass_pay'); ?></span></label>
              <input name="braintree_public_key" id="braintree_public_key" type="text" value="<?php echo $braintree_public_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="braintree_private_key"><span><?php _e('Braintree Private Key', 'osclass_pay'); ?></span></label>
              <input name="braintree_private_key" id="braintree_private_key" type="password" value="<?php echo $braintree_private_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="braintree_encryption_key"><span><?php _e('Braintree Encryption Key', 'osclass_pay'); ?></span></label>
              <input name="braintree_encryption_key" id="braintree_encryption_key" type="password" value="<?php echo $braintree_encryption_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="braintree_sandbox"><span><?php _e('Enable Sandbox', 'osclass_pay'); ?></span></label>
              <input name="braintree_sandbox" id="braintree_sandbox" class="element-slide" type="checkbox" <?php echo ($braintree_sandbox == 1 ? 'checked' : ''); ?> />
            </div>
          </div>
        </div>


        <!-- 2CHECKOUT -->
        <div class="mb-method mb-twocheckout <?php if($twocheckout_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($twocheckout_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('2Checkout Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/2checkout.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://www.2checkout.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open 2Checkout Production', 'osclass_pay'); ?></span></a>
              <a href="https://sandbox.2checkout.com/sandbox" target="_blank" class="mb-sand osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-cubes"></i> <span><?php _e('Open 2Checkout Sandbox', 'osclass_pay'); ?></span></a>
              <a href="https://www.2checkout.com/documentation/" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open 2Checkout Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('Once you select type INLINE, go to Account > Site Management, in Direct Return option select "Header Redirect (Your URL) and into approved URL put following:', 'osclass_pay'); ?><?php echo osc_base_url() . 'oc-content/plugins/osclass_pay/payments/2checkout/return.php'; ?></div>
            </div>

            <div class="mb-line">
              <label for="twocheckout_enabled"><span><?php _e('Enable 2Checkout Payments', 'osclass_pay'); ?></span></label>
              <input name="twocheckout_enabled" id="twocheckout_enabled" class="element-slide" type="checkbox" <?php echo ($twocheckout_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="twocheckout_type"><span><?php _e('2Checkout Type', 'osclass_pay'); ?></span></label>
              <select name="twocheckout_type">
                <option value="onsite" <?php if($twocheckout_type == '' || $twocheckout_type == 'onsite') { ?>selected="selected"<?php } ?>><?php _e('OnSite Checkout', 'osclass_pay'); ?></option>
                <option value="standard" <?php if($twocheckout_type == 'standard') { ?>selected="selected"<?php } ?>><?php _e('Standard Checkout (redirect)', 'osclass_pay'); ?></option>
              </select>

              <div class="mb-explain"><?php _e('OnSite checkout works without redirect, but allows payment with credit card. Standard will redirect user to 2Checkout.', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-line">
              <label for="twocheckout_seller_id"><span><?php _e('2Checkout Seller ID', 'osclass_pay'); ?></span></label>
              <input name="twocheckout_seller_id" id="twocheckout_seller_id" type="text" value="<?php echo $twocheckout_seller_id; ?>" />

              <div class="mb-explain"><?php _e('You can get it in my account section: "Account Number".', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-line">
              <label for="twocheckout_secret_word"><span><?php _e('2Checkout Secret Word', 'osclass_pay'); ?></span></label>
              <input name="twocheckout_secret_word" id="twocheckout_secret_word" type="text" value="<?php echo $twocheckout_secret_word; ?>" />

              <div class="mb-explain"><?php _e('Secret word you have setup in your account, set in Site Management', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-line">
              <label for="twocheckout_publishable_key"><span><?php _e('2Checkout Publishable Key', 'osclass_pay'); ?></span></label>
              <input name="twocheckout_publishable_key" id="twocheckout_publishable_key" type="text" value="<?php echo $twocheckout_publishable_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="twocheckout_private_key"><span><?php _e('2Checkout Private Key', 'osclass_pay'); ?></span></label>
              <input name="twocheckout_private_key" id="twocheckout_private_key" type="password" value="<?php echo $twocheckout_private_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="twocheckout_sandbox"><span><?php _e('Enable Sandbox', 'osclass_pay'); ?></span></label>
              <input name="twocheckout_sandbox" id="twocheckout_sandbox" class="element-slide" type="checkbox" <?php echo ($twocheckout_sandbox == 1 ? 'checked' : ''); ?> />
            </div>
          </div>
        </div>


        <!-- PAGSEGURO -->
        <div class="mb-method mb-pagseguro <?php if($pagseguro_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($pagseguro_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('PagSeguro Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/pagseguro.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('PagSeguro operates in Brazil only, osclass pay plugin must be set to currency BRL, otherwise payment will not be available.', 'osclass_pay'); ?></div>
              <div class="mb-line"><?php _e('Use lightbox if possible, it provides better user experience.', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-row mb-notes" style="margin-bottom:35px;">
              <div class="mb-line"><?php _e('PagSeguro require settings of your application on their site in Integration Profiles > Application. If you open bellow listed URLs and got error 404, contact your hosting provider to make these files readable.', 'osclass_pay'); ?></div>
              <div class="mb-line">
                <?php _e('Transaction notification URL:', 'osclass_pay'); ?> <?php echo osc_base_url(); ?>oc-content/plugins/osclass_pay/payments/pagseguro/notify.php<br/>
                <?php _e('Redirect page URL:', 'osclass_pay'); ?> <?php echo osc_base_url(); ?>oc-content/plugins/osclass_pay/payments/pagseguro/return.php<br/>
                <?php _e('Redirect with transaction code, name of parameter:', 'osclass_pay'); ?> transaction_id
              </div>
            </div>


            <?php if(osp_currency() <> 'BRL') { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('PagSeguro is not available, currency must be set to BRL to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>

            <div class="mb-row mb-method-links">
              <a href="https://pagseguro.uol.com.br/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open PagSeguro Production', 'osclass_pay'); ?></span></a>
              <a href="https://sandbox.pagseguro.uol.com.br/" target="_blank" class="mb-sand osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-cubes"></i> <span><?php _e('Open PagSeguro Sandbox', 'osclass_pay'); ?></span></a>
              <a href="https://dev.pagseguro.uol.com.br/documentacao/pagamento-online/pagamentos" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open PagSeguro Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="pagseguro_enabled"><span><?php _e('Enable PagSeguro Payments', 'osclass_pay'); ?></span></label>
              <input name="pagseguro_enabled" id="pagseguro_enabled" class="element-slide" type="checkbox" <?php echo ($pagseguro_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="pagseguro_email"><span><?php _e('PagSeguro Email', 'osclass_pay'); ?></span></label>
              <input name="pagseguro_email" id="pagseguro_email" type="text" value="<?php echo $pagseguro_email; ?>" />
            </div>

            <div class="mb-line">
              <label for="pagseguro_token"><span><?php _e('PagSeguro Token', 'osclass_pay'); ?></span></label>
              <input name="pagseguro_token" id="pagseguro_token" type="text" value="<?php echo $pagseguro_token; ?>" />
            </div>

            <div class="mb-line">
              <label for="pagseguro_application_id"><span><?php _e('PagSeguro Application ID', 'osclass_pay'); ?></span></label>
              <input name="pagseguro_application_id" id="pagseguro_application_id" type="text" value="<?php echo $pagseguro_application_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="pagseguro_sb_token"><span><?php _e('Sandbox Token', 'osclass_pay'); ?></span></label>
              <input name="pagseguro_sb_token" id="pagseguro_sb_token" type="text" value="<?php echo $pagseguro_sb_token; ?>" />
            </div>

            <div class="mb-line">
              <label for="pagseguro_sb_application_id"><span><?php _e('Sandbox Application ID', 'osclass_pay'); ?></span></label>
              <input name="pagseguro_sb_application_id" id="pagseguro_sb_application_id" type="text" value="<?php echo $pagseguro_sb_application_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="pagseguro_sb_application_key"><span><?php _e('Sandbox Application Key', 'osclass_pay'); ?></span></label>
              <input name="pagseguro_sb_application_key" id="pagseguro_sb_application_key" type="password" value="<?php echo $pagseguro_sb_application_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="pagseguro_sandbox"><span><?php _e('Enable Sandbox', 'osclass_pay'); ?></span></label>
              <input name="pagseguro_sandbox" id="pagseguro_sandbox" class="element-slide" type="checkbox" <?php echo ($pagseguro_sandbox == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="pagseguro_lightbox"><span><?php _e('Enable Lightbox', 'osclass_pay'); ?></span></label>
              <input name="pagseguro_lightbox" id="pagseguro_lightbox" class="element-slide" type="checkbox" <?php echo ($pagseguro_lightbox == 1 ? 'checked' : ''); ?> />

              <div class="mb-explain"><?php _e('Lightbox can be slowly loading in sandbox mode.', 'osclass_pay'); ?></div>
            </div>
          </div>
        </div>


        <!-- PAYUMONEY -->
        <div class="mb-method mb-payumoney <?php if($payumoney_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($payumoney_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('PayUMoney Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/payumoney.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('PayUMoney operates in currency INR only, plugin must be set to this currency, otherwise payment will not be available.', 'osclass_pay'); ?></div>
              <div class="mb-line"><?php _e('PayUMoney is India company, do not get into confusion it is PayU or PayULatam. Also PayUBiz is different API of PayUMoney.', 'osclass_pay'); ?></div>
              <div class="mb-line"><?php _e('PayUMoney does not have sandbox. Use Testing credentials that can be found on "Open PayUMoney Testing" link.', 'osclass_pay'); ?></div>
            </div>

            <?php if(osp_currency() <> 'INR') { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('PayUMoney is not available, currency must be set to INR to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>

            <div class="mb-row mb-method-links">
              <a href="https://www.payumoney.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open PayUMoney Production', 'osclass_pay'); ?></span></a>
              <a href="https://www.payumoney.com/dev-guide/development/general.html" target="_blank" class="mb-sand osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-cubes"></i> <span><?php _e('Open PayUMoney Testing', 'osclass_pay'); ?></span></a>
              <a href="https://www.payumoney.com/dev-guide/webcheckout/redirect.html" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open PayUMoney Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="payumoney_enabled"><span><?php _e('Enable PayUMoney Payments', 'osclass_pay'); ?></span></label>
              <input name="payumoney_enabled" id="payumoney_enabled" class="element-slide" type="checkbox" <?php echo ($payumoney_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="payumoney_merchant_key"><span><?php _e('PayUMoney Merchant Key', 'osclass_pay'); ?></span></label>
              <input name="payumoney_merchant_key" id="payumoney_merchant_key" type="text" value="<?php echo $payumoney_merchant_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="payumoney_salt"><span><?php _e('PayUMoney Salt', 'osclass_pay'); ?></span></label>
              <input name="payumoney_salt" id="payumoney_salt" type="password" value="<?php echo $payumoney_salt; ?>" />
            </div>

            <div class="mb-line">
              <label for="payumoney_sandbox"><span><?php _e('Enable Sandbox', 'osclass_pay'); ?></span></label>
              <input name="payumoney_sandbox" id="payumoney_sandbox" class="element-slide" type="checkbox" <?php echo ($payumoney_sandbox == 1 ? 'checked' : ''); ?> />
            </div>
          </div>
        </div>


        <!-- INSTAMOJO -->
        <div class="mb-method mb-instamojo <?php if($instamojo_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($instamojo_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Instamojo Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/instamojo.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('Instamojo operates in currency INR only, plugin must be set to this currency, otherwise payment will not be available.', 'osclass_pay'); ?></div>
            </div>

            <?php if(osp_currency() <> 'INR') { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('Instamojo is not available, currency must be set to INR to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>

            <div class="mb-row mb-method-links">
              <a href="https://www.instamojo.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Instamojo Production', 'osclass_pay'); ?></span></a>
              <a href="https://test.instamojo.com/" target="_blank" class="mb-sand osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-cubes"></i> <span><?php _e('Open Instamojo Sandbox', 'osclass_pay'); ?></span></a>
              <a href="https://docs.instamojo.com/v2/docs" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Instamojo Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="instamojo_enabled"><span><?php _e('Enable Instamojo Payments', 'osclass_pay'); ?></span></label>
              <input name="instamojo_enabled" id="instamojo_enabled" class="element-slide" type="checkbox" <?php echo ($instamojo_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="instamojo_api_key"><span><?php _e('Instamojo API Key', 'osclass_pay'); ?></span></label>
              <input name="instamojo_api_key" id="instamojo_api_key" type="text" value="<?php echo $instamojo_api_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="instamojo_auth_token"><span><?php _e('Instamojo Auth Token', 'osclass_pay'); ?></span></label>
              <input name="instamojo_auth_token" id="instamojo_auth_token" type="password" value="<?php echo $instamojo_auth_token; ?>" />
            </div>

            <div class="mb-line">
              <label for="instamojo_salt"><span><?php _e('Instamojo Salt', 'osclass_pay'); ?></span></label>
              <input name="instamojo_salt" id="instamojo_salt" type="password" value="<?php echo $instamojo_salt; ?>" />
            </div>

            <div class="mb-line">
              <label for="instamojo_sandbox"><span><?php _e('Enable Sandbox', 'osclass_pay'); ?></span></label>
              <input name="instamojo_sandbox" id="instamojo_sandbox" class="element-slide" type="checkbox" <?php echo ($instamojo_sandbox == 1 ? 'checked' : ''); ?> />
            </div>
          </div>
        </div>


        <!-- PAYULATAM -->
        <div class="mb-method mb-payulatam <?php if($payulatam_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($payulatam_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('PayULatam Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/payulatam.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('PayULatam operates in currencies ARS, BRL, CLP, COP, MXN, PEN, USD only, plugin must be set to one of listed currency, otherwise payment will not be available.', 'osclass_pay'); ?></div>
            </div>

            <?php if(!in_array(osp_currency(), array('ARS', 'BRL', 'CLP', 'COP', 'MXN', 'PEN', 'USD'))) { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('PayULatam is not available, currency must be set to ARS, BRL, CLP, COP, MXN, PEN or USD to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>

            <div class="mb-row mb-method-links">
              <a href="https://www.payulatam.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open PayULatam Production', 'osclass_pay'); ?></span></a>
              <a href="http://developers.payulatam.com/en/web_checkout/sandbox.html" target="_blank" class="mb-sand osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-cubes"></i> <span><?php _e('Open PayULatam Sandbox', 'osclass_pay'); ?></span></a>
              <a href="http://developers.payulatam.com/en/web_checkout/integration.html" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open PayULatam Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="payulatam_enabled"><span><?php _e('Enable PayULatam Payments', 'osclass_pay'); ?></span></label>
              <input name="payulatam_enabled" id="payulatam_enabled" class="element-slide" type="checkbox" <?php echo ($payulatam_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="payulatam_merchant_id"><span><?php _e('PayULatam Merchant ID', 'osclass_pay'); ?></span></label>
              <input name="payulatam_merchant_id" id="payulatam_merchant_id" type="text" value="<?php echo $payulatam_merchant_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="payulatam_account_id"><span><?php _e('PayULatam Account ID', 'osclass_pay'); ?></span></label>
              <input name="payulatam_account_id" id="payulatam_account_id" type="text" value="<?php echo $payulatam_account_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="payulatam_api_key"><span><?php _e('PayULatam API Key', 'osclass_pay'); ?></span></label>
              <input name="payulatam_api_key" id="payulatam_api_key" type="password" value="<?php echo $payulatam_api_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="payulatam_sandbox"><span><?php _e('Enable Sandbox', 'osclass_pay'); ?></span></label>
              <input name="payulatam_sandbox" id="payulatam_sandbox" class="element-slide" type="checkbox" <?php echo ($payulatam_sandbox == 1 ? 'checked' : ''); ?> />
            </div>
          </div>
        </div>


        <!-- CCAVENUE -->
        <div class="mb-method mb-ccavenue <?php if($ccavenue_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($ccavenue_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('CCAvenue Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/ccavenue.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('CCAvenue is very strict. If you get credentials, you can use it just on URL/domain it was registered for. Otherwise it will result in Authentication failed error.', 'osclass_pay'); ?></div>
              <div class="mb-line"><?php _e('CCAvenue operates in currencies INR, USD, SGD, GBP, EUR only, plugin must be set to one of listed currency, otherwise payment will not be available. Note that other than INR currency must be approved by CCAvenue on your account, otherwise checkout will result in error code 115.', 'osclass_pay'); ?></div>
            </div>

            <?php if(!in_array(osp_currency(), array('INR', 'USD', 'SGD', 'GBP', 'EUR'))) { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('CCAvenue is not available, currency must be set to INR, USD, SGD, GBP or EUR to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>

            <div class="mb-row mb-method-links">
              <a href="https://www.ccavenue.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open CCAvenue Production', 'osclass_pay'); ?></span></a>
              <a href="http://www.bookhungama.com/pdfs/1442054378_CCAvenueIntegration-Ver2.4.pdf" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open CCAvenue Help', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="ccavenue_enabled"><span><?php _e('Enable CCAvenue Payments', 'osclass_pay'); ?></span></label>
              <input name="ccavenue_enabled" id="ccavenue_enabled" class="element-slide" type="checkbox" <?php echo ($ccavenue_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="ccavenue_language"><span><?php _e('Language on CCAvenue Checkout', 'osclass_pay'); ?></span></label>
              <select id="ccavenue_language" name="ccavenue_language">
                <option value="EN" <?php if($ccavenue_language == 'EN' || $ccavenue_language == '') { ?>selected="selected"<?php } ?>><?php echo __('EN - English', 'osclass_pay'); ?></option>
                <option value="HI" <?php if($ccavenue_language == 'HI') { ?>selected="selected"<?php } ?>><?php echo __('HI - Hindi', 'osclass_pay'); ?></option>
                <option value="GU" <?php if($ccavenue_language == 'GU') { ?>selected="selected"<?php } ?>><?php echo __('GU - Gujarati', 'osclass_pay'); ?></option>
                <option value="MR" <?php if($ccavenue_language == 'MR') { ?>selected="selected"<?php } ?>><?php echo __('MR - Marathi', 'osclass_pay'); ?></option>
                <option value="BN" <?php if($ccavenue_language == 'BN') { ?>selected="selected"<?php } ?>><?php echo __('BN - Bengali', 'osclass_pay'); ?></option>
              </select>
            </div>

            <div class="mb-line">
              <label for="ccavenue_merchant_id"><span><?php _e('CCAvenue Merchant ID', 'osclass_pay'); ?></span></label>
              <input name="ccavenue_merchant_id" id="ccavenue_merchant_id" type="text" value="<?php echo $ccavenue_merchant_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="ccavenue_access_code"><span><?php _e('CCAvenue Access Code', 'osclass_pay'); ?></span></label>
              <input name="ccavenue_access_code" id="ccavenue_access_code" type="password" value="<?php echo $ccavenue_access_code; ?>" />
            </div>

            <div class="mb-line">
              <label for="ccavenue_working_key"><span><?php _e('CCAvenue Working Key', 'osclass_pay'); ?></span></label>
              <input name="ccavenue_working_key" id="ccavenue_working_key" type="password" value="<?php echo $ccavenue_working_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="ccavenue_sandbox"><span><?php _e('Enable Sandbox', 'osclass_pay'); ?></span></label>
              <input name="ccavenue_sandbox" id="ccavenue_sandbox" class="element-slide" type="checkbox" <?php echo ($ccavenue_sandbox == 1 ? 'checked' : ''); ?> />
            </div>
          </div>
        </div>



        <!-- PAYSTACK -->
        <div class="mb-method mb-paystack <?php if($paystack_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($paystack_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Paystack Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/paystack.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('Paystack operates in USD, GHS & NGN currency only, plugin must be set to one of listed currency, otherwise payment will not be available.', 'osclass_pay'); ?></div>
            </div>

            <?php if(!in_array(osp_currency(), array('NGN', 'GHS', 'USD'))) { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('Paystack is not available, currency must be set to NGN, GHS or USD to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>

            <div class="mb-row mb-method-links">
              <a href="https://paystack.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Paystack Production', 'osclass_pay'); ?></span></a>
              <a href="https://developers.paystack.co/v1.0/reference" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Paystack API', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="paystack_enabled"><span><?php _e('Enable Paystack Payments', 'osclass_pay'); ?></span></label>
              <input name="paystack_enabled" id="paystack_enabled" class="element-slide" type="checkbox" <?php echo ($paystack_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="paystack_email"><span><?php _e('Paystack Email', 'osclass_pay'); ?></span></label>
              <input name="paystack_email" id="paystack_email" type="text" value="<?php echo $paystack_email; ?>" />
            </div>

            <div class="mb-line">
              <label for="paystack_public_key"><span><?php _e('Paystack Public Key', 'osclass_pay'); ?></span></label>
              <input name="paystack_public_key" id="paystack_public_key" type="text" value="<?php echo $paystack_public_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="paystack_secret_key"><span><?php _e('Paystack Secret Key', 'osclass_pay'); ?></span></label>
              <input name="paystack_secret_key" id="paystack_secret_key" type="password" value="<?php echo $paystack_secret_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="paystack_test_public_key"><span><?php _e('Paystack Test Public Key', 'osclass_pay'); ?></span></label>
              <input name="paystack_test_public_key" id="paystack_test_public_key" type="text" value="<?php echo $paystack_test_public_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="paystack_test_secret_key"><span><?php _e('Paystack Test Secret Key', 'osclass_pay'); ?></span></label>
              <input name="paystack_test_secret_key" id="paystack_test_secret_key" type="password" value="<?php echo $paystack_test_secret_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="paystack_sandbox"><span><?php _e('Enable Sandbox', 'osclass_pay'); ?></span></label>
              <input name="paystack_sandbox" id="paystack_sandbox" class="element-slide" type="checkbox" <?php echo ($paystack_sandbox == 1 ? 'checked' : ''); ?> />
            </div>
          </div>
        </div>




        <!-- WEACCEPT -->
        <div class="mb-method mb-weaccept <?php if($weaccept_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($weaccept_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('WeAccept Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/weaccept.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('Currency is selected on gateway side, ensure it is same as you have in Osclass Pay.', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-row mb-notes">
              <div class="mb-line">
                <?php _e('This gateway require to setup correct return and notification url. Please go to Payment Integrations and in selected integration set Transaction Processed Callback and Transaction Response Callback to following:', 'osclass_pay'); ?><br/>
                <?php echo osc_base_url(); ?>oc-content/plugins/osclass_pay/payments/weaccept/return.php
              </div>
            </div>

            <div class="mb-row mb-method-links">
              <a href="https://weaccept.co/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open WeAccept Production', 'osclass_pay'); ?></span></a>
              <a href="https://accept.paymobsolutions.com/docs/guide/online-guide/#online-payment-integration-guide" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open WeAccept API', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="weaccept_enabled"><span><?php _e('Enable WeAccept Payments', 'osclass_pay'); ?></span></label>
              <input name="weaccept_enabled" id="weaccept_enabled" class="element-slide" type="checkbox" <?php echo ($weaccept_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="weaccept_integration_id"><span><?php _e('WeAccept Integration ID', 'osclass_pay'); ?></span></label>
              <input name="weaccept_integration_id" id="weaccept_integration_id" type="text" value="<?php echo $weaccept_integration_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="weaccept_iframe_id"><span><?php _e('WeAccept Iframe ID', 'osclass_pay'); ?></span></label>
              <input name="weaccept_iframe_id" id="weaccept_iframe_id" type="text" value="<?php echo $weaccept_iframe_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="weaccept_api_key"><span><?php _e('WeAccept Api Key', 'osclass_pay'); ?></span></label>
              <input name="weaccept_api_key" id="weaccept_api_key" type="password" value="<?php echo $weaccept_api_key; ?>" />
            </div>

          </div>
        </div>



        <!-- EUPLATESC.RO -->
        <div class="mb-method mb-euplatesc <?php if($euplatesc_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($euplatesc_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('EuPlatesc.ro Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/euplatesc.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('EuPlatesc.ro operates in currencies RON, EUR and USD only, plugin must be set to one of listed currency, otherwise payment will not be available.', 'osclass_pay'); ?></div>
            </div>

            <?php if(!in_array(osp_currency(), array('EUR', 'RON', 'USD'))) { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('EuPlatesc.ro is not available, currency must be set to RON, EUR or USD to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>


            <div class="mb-row mb-notes">
              <div class="mb-line">
                <div class="mb-line"><?php _e('This gateway require to setup correct return and notification (silent) url. Please go to Admin > Gestionare Cont. At bottom click on Modifica cont and update Return URL and Silent URL with following:', 'osclass_pay'); ?></div>
                <div class="mb-line"><?php _e('Return URL', 'osclass_pay'); ?>: <?php echo osc_base_url(); ?>oc-content/plugins/osclass_pay/payments/euplatesc/return.php</div>
                <div class="mb-line"><?php _e('Silent URL', 'osclass_pay'); ?>: <?php echo osc_base_url(); ?>oc-content/plugins/osclass_pay/payments/euplatesc/response.php</div>
              </div>
            </div>

            <div class="mb-row mb-method-links">
              <a href="https://www.euplatesc.ro/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open EuPlatesc.ro', 'osclass_pay'); ?></span></a>
              <!--<a href="<?php echo osc_base_url(); ?>oc-content/plugins/osclass_pay/payments/euplatesc/docs/integration.pdf" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Integration Guide', 'osclass_pay'); ?></span></a>-->
            </div>

            <div class="mb-line">
              <label for="euplatesc_enabled"><span><?php _e('Enable EuPlatesc Payments', 'osclass_pay'); ?></span></label>
              <input name="euplatesc_enabled" id="euplatesc_enabled" class="element-slide" type="checkbox" <?php echo ($euplatesc_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="euplatesc_mid"><span><?php _e('EuPlatesc MID', 'osclass_pay'); ?></span></label>
              <input name="euplatesc_mid" id="euplatesc_mid" type="text" value="<?php echo $euplatesc_mid; ?>" />
            </div>

            <div class="mb-line">
              <label for="euplatesc_key"><span><?php _e('EuPlatesc Key', 'osclass_pay'); ?></span></label>
              <input name="euplatesc_key" id="euplatesc_key" type="password" value="<?php echo $euplatesc_key; ?>" />
            </div>

          </div>
        </div>


        <!-- KOMFORTKASSE -->
        <div class="mb-method mb-komfortkasse <?php if($komfortkasse_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($komfortkasse_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Komfortkasse.eu Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/komfortkasse.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://komfortkasse.eu/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Komfortkasse.eu', 'osclass_pay'); ?></span></a>
              <a href="https://komfortkasse.docs.apiary.io/" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Integration Guide', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="komfortkasse_enabled"><span><?php _e('Enable Komfortkasse Payments', 'osclass_pay'); ?></span></label>
              <input name="komfortkasse_enabled" id="komfortkasse_enabled" class="element-slide" type="checkbox" <?php echo ($komfortkasse_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="komfortkasse_api_key"><span><?php _e('Komfortkasse Api Key', 'osclass_pay'); ?></span></label>
              <input name="komfortkasse_api_key" id="komfortkasse_api_key" type="password" value="<?php echo $komfortkasse_api_key; ?>" />
            </div>

          </div>
        </div>


        <!-- PAYS.CZ -->
        <div class="mb-method mb-payscz <?php if($payscz_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($payscz_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Pays.cz Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/payscz.png"/>
          </div>

          <div class="mb-method-body">
            <?php if(!in_array(osp_currency(), array('USD', 'EUR', 'CZK'))) { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('Pays.cz is not available, currency must be set to USD, EUR or CZK to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>

            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('Pays.cz require to send following data to podpora@pays.cz:', 'osclass_pay'); ?></div>
              <div class="mb-line"><?php _e('Successful online/offline payment url:', 'osclass_pay'); ?> <?php echo osc_base_url(); ?>oc-content/plugins/osclass_pay/payments/payscz/success.php</div>
              <div class="mb-line"><?php _e('Payment error url:', 'osclass_pay'); ?> <?php echo osc_base_url(); ?>oc-content/plugins/osclass_pay/payments/payscz/cancel.php</div>
              <div class="mb-line"><?php _e('Payment confirm url:', 'osclass_pay'); ?> <?php echo osc_base_url(); ?>oc-content/plugins/osclass_pay/payments/payscz/confirm.php</div>
            </div>

            <div class="mb-row mb-method-links">
              <a href="https://www.pays.cz/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Pays.cz', 'osclass_pay'); ?></span></a>
              <a href="https://www.pays.cz/developers.asp" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Integration Guide', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="payscz_enabled"><span><?php _e('Enable Pays.cz Payments', 'osclass_pay'); ?></span></label>
              <input name="payscz_enabled" id="payscz_enabled" class="element-slide" type="checkbox" <?php echo ($payscz_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="payscz_merchant_id"><span><?php _e('Pays.cz Merchant Id', 'osclass_pay'); ?></span></label>
              <input name="payscz_merchant_id" id="payscz_merchant_id" type="text" value="<?php echo $payscz_merchant_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="payscz_shop_id"><span><?php _e('Pays.cz Shop Id', 'osclass_pay'); ?></span></label>
              <input name="payscz_shop_id" id="payscz_shop_id" type="text" value="<?php echo $payscz_shop_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="payscz_api_pass"><span><?php _e('Pays.cz API Password', 'osclass_pay'); ?></span></label>
              <input name="payscz_api_pass" id="payscz_api_pass" type="password" value="<?php echo $payscz_api_pass; ?>" />
            </div>

          </div>
        </div>



        <!-- YANDEX MONEY -->
        <div class="mb-method mb-yandex <?php if($yandex_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($yandex_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Yandex Money Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/yandex.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://money.yandex.ru/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Yandex Money page', 'osclass_pay'); ?></span></a>
              <a href="https://kassa.yandex.ru/developers/payments/quick-start" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Integration Guide', 'osclass_pay'); ?></span></a>
            </div>

            <?php if(osp_currency() <> 'RUB') { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('Yandex Money is not available, currency must be set to RUB to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>

            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('In your Yandex account setup notification URL into following:', 'osclass_pay'); ?></div>
              <div class="mb-line"><?php echo osc_base_url() . 'oc-content/plugins/osclass_pay/payments/yandex/notification.php'; ?></div>
            </div>

            <div class="mb-line">
              <label for="yandex_enabled"><span><?php _e('Enable Yandex Money Payments', 'osclass_pay'); ?></span></label>
              <input name="yandex_enabled" id="yandex_enabled" class="element-slide" type="checkbox" <?php echo ($yandex_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="yandex_shop_id"><span><?php _e('Yandex Shop ID (Account/Merchant)', 'osclass_pay'); ?></span></label>
              <input name="yandex_shop_id" id="yandex_shop_id" type="text" value="<?php echo $yandex_shop_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="yandex_api_secret"><span><?php _e('Yandex Api Secret Key', 'osclass_pay'); ?></span></label>
              <input name="yandex_api_secret" id="yandex_api_secret" type="password" value="<?php echo $yandex_api_secret; ?>" />
            </div>

          </div>
        </div>
        
  

        <!-- CARDINITY.com -->
        <div class="mb-method mb-cardinity <?php if($cardinity_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($cardinity_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Cardinity Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/cardinity.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://cardinity.com/manage" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open Cardinity Dashboard', 'osclass_pay'); ?></span></a>
              <a href="https://developers.cardinity.com/api/v1/#payments" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open API Details', 'osclass_pay'); ?></span></a>
              <a href="https://github.com/cardinity/cardinity-sdk-php" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open PHP SDK Github', 'osclass_pay'); ?></span></a>
            </div>

            <?php if(!in_array(osp_currency(), array('EUR', 'GBP', 'USD'))) { ?>
              <div class="mb-row mb-errors">
                <div class="mb-line"><?php _e('Cardinity.com is not available, currency must be set to EUR, GBP or USD to be able to use this gateway.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>

            <div class="mb-line">
              <label for="cardinity_enabled"><span><?php _e('Enable Cardinity Payments', 'osclass_pay'); ?></span></label>
              <input name="cardinity_enabled" id="cardinity_enabled" class="element-slide" type="checkbox" <?php echo ($cardinity_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="cardinity_project_id"><span><?php _e('Cardinity Project ID', 'osclass_pay'); ?></span></label>
              <input name="cardinity_project_id" id="cardinity_project_id" type="text" value="<?php echo $cardinity_project_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="cardinity_project_secret"><span><?php _e('Cardinity Project Secret', 'osclass_pay'); ?></span></label>
              <input name="cardinity_project_secret" id="cardinity_project_secret" type="password" value="<?php echo $cardinity_project_secret; ?>" />
            </div>

          </div>
        </div>


        <!-- Securion.com -->
        <div class="mb-method mb-securionpay <?php if($securionpay_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($securionpay_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Securionpay Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/securionpay.svg"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://securionpay.com/dashboard" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open securionpay Dashboard', 'osclass_pay'); ?></span></a>
              <a href="https://securionpay.com/docs" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open Docs', 'osclass_pay'); ?></span></a>
              <a href="https://securionpay.com/docs/api#introduction" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open API Details', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="securionpay_enabled"><span><?php _e('Enable Securionpay Payments', 'osclass_pay'); ?></span></label>
              <input name="securionpay_enabled" id="securionpay_enabled" class="element-slide" type="checkbox" <?php echo ($securionpay_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="securionpay_public_key"><span><?php _e('Securionpay Public Key', 'osclass_pay'); ?></span></label>
              <input name="securionpay_public_key" id="securionpay_public_key" type="text" value="<?php echo $securionpay_public_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="securionpay_secret_key"><span><?php _e('Securionpay Secret Keyt', 'osclass_pay'); ?></span></label>
              <input name="securionpay_secret_key" id="securionpay_secret_key" type="password" value="<?php echo $securionpay_secret_key; ?>" />
            </div>

          </div>
        </div>


        <!-- BeGateway -->
        <div class="mb-method mb-begateway <?php if($begateway_enabled == 1) { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if($begateway_enabled == 1) { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('BeGateway Payments', 'osclass_pay'); ?></span>
            <img src="<?php echo osp_url(); ?>img/payments/begateway.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://begateway.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Open BeGateway Home', 'osclass_pay'); ?></span></a>
              <a href="https://github.com/begateway/begateway-api-php" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'osclass_pay')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Open PHP SDK Github', 'osclass_pay'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="begateway_enabled"><span><?php _e('Enable BeGateway Payments', 'osclass_pay'); ?></span></label>
              <input name="begateway_enabled" id="begateway_enabled" class="element-slide" type="checkbox" <?php echo ($begateway_enabled == 1 ? 'checked' : ''); ?> />
            </div>

            <div class="mb-line">
              <label for="begateway_domain_checkout"><span><?php _e('BeGateway Checkout Domain', 'osclass_pay'); ?></span></label>
              <input name="begateway_domain_checkout" id="begateway_domain_checkout" type="text" value="<?php echo $begateway_domain_checkout; ?>" />

              <div class="mb-explain"><?php _e('Enter your payment provider checkout domain e.g. checkout.example.com', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-line">
              <label for="begateway_shop_id"><span><?php _e('BeGateway Shop Id', 'osclass_pay'); ?></span></label>
              <input name="begateway_shop_id" id="begateway_shop_id" type="text" value="<?php echo $begateway_shop_id; ?>" />

              <div class="mb-explain"><?php _e('Enter your shop id issued by your payment provider', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-line">
              <label for="begateway_secret_key"><span><?php _e('BeGateway Shop Secret Key', 'osclass_pay'); ?></span></label>
              <input name="begateway_secret_key" id="begateway_secret_key" type="text" value="<?php echo $begateway_secret_key; ?>" />

              <div class="mb-explain"><?php _e('Enter your shop secret key issued by your payment provider', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-line">
              <label for="begateway_public_key"><span><?php _e('BeGateway Shop Public Key', 'osclass_pay'); ?></span></label>
              <input name="begateway_public_key" id="begateway_public_key" type="text" value="<?php echo $begateway_public_key; ?>" />

              <div class="mb-explain"><?php _e('Enter your shop public issued by your payment provider', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-line">
              <label for="begateway_timeout"><span><?php _e('BeGateway Payment Timeout', 'osclass_pay'); ?></span></label>
              <input name="begateway_timeout" id="begateway_timeout" type="text" value="<?php echo $begateway_timeout; ?>" />

              <div class="mb-explain"><?php _e('Enter number of minutes to complete payment by customer', 'osclass_pay'); ?></div>
            </div>

            <div class="mb-line">
              <label for="begateway_test_mode"><span><?php _e('Enable Test Mode', 'osclass_pay'); ?></span></label>
              <input name="begateway_test_mode" id="begateway_test_mode" class="element-slide" type="checkbox" <?php echo ($begateway_test_mode == 1 ? 'checked' : ''); ?> />
            </div>
          </div>
        </div>

        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Save', 'osclass_pay');?></button>
        </div>
      </form>
    </div>
  </div>



  <!-- BANK TRANSFERS -->
  <?php if($bt_enabled == 1) { ?>
    <div class="mb-box mb-transfer">
      <div class="mb-head"><i class="fa fa-id-card"></i> <?php _e('Payment Methods', 'osclass_pay'); ?></div>

      <div class="mb-inside">
        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('Bellow are shown all bank transfers ordered by status and date.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('If you have received funds to your bank account, accept payment.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('Approve transaction just in case you see it on your bank account, this action cannot be undone.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-table mb-table-transfer">
          <div class="mb-table-head">
            <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
            <div class="mb-col-3"><?php _e('Transaction', 'osclass_pay'); ?></div>
            <div class="mb-col-2"><?php _e('User', 'osclass_pay'); ?></div>
            <div class="mb-col-3"><?php _e('Variable Symbol', 'osclass_pay'); ?></div>
            <div class="mb-col-3"><?php _e('Amount', 'osclass_pay'); ?></div>
            <div class="mb-col-1">&nbsp;</div>
            <div class="mb-col-2"><?php _e('Status', 'osclass_pay'); ?></div>
            <div class="mb-col-3"><?php _e('Date', 'osclass_pay'); ?></div>
            <div class="mb-col-3"><?php _e('Accept Date', 'osclass_pay'); ?></div>
            <div class="mb-col-3">&nbsp;</div>
          </div>

          <?php $transfers = ModelOSP::newInstance()->getBankTransfers(); ?>

          <?php if(count($transfers) <= 0) { ?>
            <div class="mb-table-row mb-row-empty">
              <i class="fa fa-warning"></i><span><?php _e('No bank transfers has been found', 'osclass_pay'); ?></span>
            </div>
          <?php } else { ?>
            <?php foreach($transfers as $t) { ?>
              <?php 
                $tdata = osp_get_custom($t['s_extra']);
                $user = User::newInstance()->findByPrimaryKey($t['i_user_id']);
              ?>

              <div class="mb-table-row">
                <div class="mb-col-1"><?php echo $t['pk_i_id']; ?></div>
                <div class="mb-col-3"><?php echo $t['s_transaction']; ?></div>
                <div class="mb-col-2">
                  <?php if(@$user['pk_i_id'] > 0 && trim($user['s_name']) <> '') { ?>
                    <?php echo '<a href="' . osc_admin_base_url(true) . '?page=users&action=edit&id=' . $user['pk_i_id'] . '" target="_blank">' . $user['s_name'] . '</a>'; ?>
                  <?php } else if(trim(@$tdata['email']) <> '') { ?>
                    <span title="<?php echo osc_esc_html(@$tdata['email']); ?>" class="mb-has-tooltip"><?php echo (@$tdata['name'] <> '' ? @$tdata['name'] : @$tdata['email']); ?></span>
                  <?php } else { ?>
                    <?php echo __('Unknown', 'osclass_pay'); ?>
                  <?php } ?>
                </div>
                <div class="mb-col-3"><span class="mb-has-tooltip" title="<?php echo osc_esc_html(__('This code should be used as variable symbol in transaction you have received', 'osclass_pay')); ?>"><?php echo $t['s_variable']; ?></span></div>
                <div class="mb-col-3"><?php echo osp_format_price($t['f_price']); ?></div>
                <div class="mb-col-1">
                  <?php if(osp_cart_string_to_title($t['s_cart']) <> '') { ?>
                    <i class="fa fa-search mb-has-tooltip mb-log-details" title="<?php echo osc_esc_html(str_replace('<br/>', PHP_EOL, osp_cart_string_to_title($t['s_cart']))); ?>"></i>
                  <?php } ?>
                </div>
                <div class="mb-col-2 mb-bt-status">
                  <span class="st<?php echo $t['i_paid']; ?>">
                    <?php
                      if($t['i_paid'] == 0) {
                        echo '<i class="fa fa-hourglass-half"></i> ' . __('Pending', 'osclass_pay');
                      } else if($t['i_paid'] == 1) {
                        echo '<i class="fa fa-check"></i> ' . __('Paid', 'osclass_pay');
                      } else {
                        echo '<i class="fa fa-times"></i> ' . __('Cancelled', 'osclass_pay');
                      }
                    ?>
                  </span>
                </div>
                <div class="mb-col-3"><?php echo $t['dt_date']; ?></div>
                <div class="mb-col-3"><?php echo ($t['dt_date_paid'] <> '' ? $t['dt_date_paid'] : '-'); ?></div>
                <div class="mb-col-3 mb-bt-buttons mb-align-right">
                  <?php if($t['i_paid'] <> 1) { ?>
                    <a href="<?php echo osc_route_admin_url('osp-admin-transfer', array('btId' => $t['pk_i_id'], 'status' => 1)); ?>" class="mb-bt-accept mb-button-green mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Accept payment', 'osclass_pay')); ?>"><i class="fa fa-check"></i></a>
                  <?php } ?>

                  <?php if($t['i_paid'] == 0) { ?>
                    <a href="<?php echo osc_route_admin_url('osp-admin-transfer', array('btId' => $t['pk_i_id'], 'status' => 2)); ?>" class="mb-bt-cancel mb-button-white mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Cancel payment', 'osclass_pay')); ?>"><i class="fa fa-times"></i></a>
                  <?php } ?>

                  <?php if($t['i_paid'] == 2) { ?>
                    <a href="<?php echo osc_route_admin_url('osp-admin-transfer', array('btId' => $t['pk_i_id'], 'status' => 9)); ?>" class="mb-bt-remove mb-button-red mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Remove payment', 'osclass_pay')); ?>" onclick="return confirm('<?php echo osc_esc_js(__('Are you sure you want to remove this transfer? Action cannot be undone.', 'osclass_pay')); ?>')"><i class="fa fa-trash"></i></a>
                  <?php } ?>
                </div>
              </div>
            <?php } ?>
          <?php } ?>
        </div>
      </div>
    </div>
  <?php } ?>
</div>

<?php echo osp_footer(); ?>