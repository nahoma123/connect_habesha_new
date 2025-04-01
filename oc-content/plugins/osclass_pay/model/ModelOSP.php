<?php
class ModelOSP extends DAO {
private static $instance;

public static function newInstance() {
  if( !self::$instance instanceof self ) {
    self::$instance = new self;
  }
  return self::$instance;
}

function __construct() {
  parent::__construct();
}


public function getTable_log() {
  return DB_TABLE_PREFIX.'t_osp_log';
}

public function getTable_pending() {
  return DB_TABLE_PREFIX.'t_osp_pending';
}

public function getTable_transfer() {
  return DB_TABLE_PREFIX.'t_osp_bank_transfer';
}

public function getTable_wallet() {
  return DB_TABLE_PREFIX.'t_osp_wallet';
}

public function getTable_item_payment() {
  return DB_TABLE_PREFIX.'t_osp_item';
}

public function getTable_user() {
  return DB_TABLE_PREFIX.'t_user';
}

public function getTable_booking() {
  return DB_TABLE_PREFIX.'t_bkg_reservation';
}

public function getTable_voucher() {
  return DB_TABLE_PREFIX.'t_vcr_voucher';
}

public function getTable_voucher_stats() {
  return DB_TABLE_PREFIX.'t_vcr_voucher_stats';
}

public function getTable_category() {
  return DB_TABLE_PREFIX.'t_category_description';
}

public function getTable_price_category() {
  return DB_TABLE_PREFIX.'t_osp_price_category';
}

public function getTable_price_location() {
  return DB_TABLE_PREFIX.'t_osp_price_location';
}

public function getTable_user_to_group() {
  return DB_TABLE_PREFIX.'t_osp_user_to_group';
}

public function getTable_user_group() {
  return DB_TABLE_PREFIX.'t_osp_user_group';
}

public function getTable_user_group_locale() {
  return DB_TABLE_PREFIX.'t_osp_user_group_locale';
}

public function getTable_pack() {
  return DB_TABLE_PREFIX.'t_osp_pack';
}

public function getTable_pack_locale() {
  return DB_TABLE_PREFIX.'t_osp_pack_locale';
}

public function getTable_user_cart() {
  return DB_TABLE_PREFIX.'t_osp_user_cart';
}

public function getTable_banner() {
  return DB_TABLE_PREFIX.'t_osp_banner';
}

public function getTable_rate() {
  return DB_TABLE_PREFIX.'t_osp_currency_rate';
}

public function getTable_item() {
  return DB_TABLE_PREFIX.'t_item';
}

public function getTable_item_description() {
  return DB_TABLE_PREFIX.'t_item_description';
}

public function getTable_item_location() {
  return DB_TABLE_PREFIX.'t_item_location';
}

public function getTable_item_data() {
  return DB_TABLE_PREFIX.'t_osp_item_data';
}

public function getTable_order() {
  return DB_TABLE_PREFIX.'t_osp_order';
}

public function getTable_order_item() {
  return DB_TABLE_PREFIX.'t_osp_order_item';
}

public function getTable_shipping() {
  return DB_TABLE_PREFIX.'t_osp_shipping';
}

public function getTable_auction() {
  return DB_TABLE_PREFIX.'t_act_auction';
}


public function import($file) {
  $path = osc_plugin_resource($file);
  $sql = file_get_contents($path);

  if(!$this->dao->importSQL($sql) ){
    throw new Exception("Error importSQL::ModelOSP<br>" . $file . "<br>" . $this->dao->getErrorLevel() . " - " . $this->dao->getErrorDesc() );
  }
}


public function install($version = '') {
  if($version == '') {
    $this->import('osclass_pay/model/struct.sql');

    osc_set_preference('version', 114, 'plugin-osclass_pay', 'INTEGER');
    
    if(osc_get_preference('crypt_key', 'plugin-osclass_pay') == '') {
      osc_set_preference('crypt_key', mb_generate_rand_string(32), 'plugin-osclass_pay', 'STRING');
    }
    
    osc_set_preference('cron_runs', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('cron_runs_user', '', 'plugin-osclass_pay', 'STRING');

    osc_set_preference('price_decimals', 2, 'plugin-osclass_pay', 'INTEGER');
    osc_set_preference('price_position', 0, 'plugin-osclass_pay', 'INTEGER');
    osc_set_preference('price_space', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('price_decimal_symbol', '.', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('price_thousand_symbol', ' ', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('def_price_calc', '', 'plugin-osclass_pay', 'STRING');

    osc_set_preference('publish_fee', '1.0', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('publish_allow', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('publish_item_disable', 1, 'plugin-osclass_pay', 'INTEGER');

    osc_set_preference('movetotop_fee', '1.0', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('movetotop_allow', '0', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('premium_fee', '1.0', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('premium_allow', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('premium_duration', '24,72,168,720', 'plugin-osclass_pay', 'STRING');

    osc_set_preference('highlight_fee', '1.0', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('highlight_allow', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('highlight_duration', '24,72,168,720', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('highlight_color', '#F3FFBD', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('highlight_css', '', 'plugin-osclass_pay', 'STRING');

    osc_set_preference('image_fee', '1.0', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('image_allow', '0', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('republish_fee', '1.0', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('republish_repeat', '1,2,3,4,5,10', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('republish_repeat_discount', '5', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('republish_duration', '12,24,72,168', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('republish_allow', '0', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('currency', 'USD', 'plugin-osclass_pay', 'STRING');
    
    osc_set_preference('wallet_enabled', 1, 'plugin-osclass_pay', 'INTEGER');
    osc_set_preference('wallet_registration', 10, 'plugin-osclass_pay', 'INTEGER');
    osc_set_preference('wallet_referral', 10, 'plugin-osclass_pay', 'INTEGER');
    osc_set_preference('wallet_periodically', 50, 'plugin-osclass_pay', 'INTEGER');
    osc_set_preference('wallet_period', 'm', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('pack_style', 2, 'plugin-osclass_pay', 'INTEGER');

    osc_set_preference('groups_enabled', 1, 'plugin-osclass_pay', 'INTEGER');
    osc_set_preference('groups_category', 0, 'plugin-osclass_pay', 'INTEGER');


    osc_set_preference('bt_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('bt_iban', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('bt_min', 10, 'plugin-osclass_pay', 'INTEGER');


    osc_set_preference('paypal_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('paypal_api_username', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('paypal_api_password', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('paypal_api_signature', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('paypal_email', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('paypal_standard', '1', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('paypal_sandbox', '1', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('payza_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('payza_email', '', 'plugin-osclass_pay', 'STRING');

    osc_set_preference('blockchain_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('blockchain_address', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('blockchain_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('blockchain_xpub', '', 'plugin-osclass_pay', 'STRING');

    osc_set_preference('skrill_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('skrill_merchant_id', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('skrill_secret_word', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('skrill_email', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('skrill_notify', '0', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('braintree_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('braintree_merchant_id', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('braintree_public_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('braintree_private_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('braintree_encryption_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('braintree_sandbox', '1', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('stripe_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('stripe_secret_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('stripe_webhook_secret_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('stripe_public_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('stripe_secret_key_test', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('stripe_webhook_secret_key_test', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('stripe_public_key_test', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('stripe_sandbox', '1', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('twocheckout_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('twocheckout_seller_id', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('twocheckout_publishable_key', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('twocheckout_private_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('twocheckout_sandbox', '1', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('authorizenet_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('authorizenet_merchant_login_id', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('authorizenet_merchant_transaction_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('authorizenet_sandbox', '1', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('pagseguro_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('pagseguro_email', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('pagseguro_token', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('pagseguro_application_id', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('pagseguro_application_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('pagseguro_sb_token', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('pagseguro_sb_application_id', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('pagseguro_sb_application_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('pagseguro_sandbox', '1', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('pagseguro_lightbox', '0', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('payumoney_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('payumoney_merchant_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('payumoney_salt', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('payumoney_sandbox', '1', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('payulatam_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('payulatam_merchant_id', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('payulatam_account_id', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('payulatam_api_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('payulatam_sandbox', '1', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('ccavenue_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('ccavenue_language', 'EN', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('ccavenue_merchant_id', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('ccavenue_working_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('ccavenue_access_code', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('ccavenue_sandbox', '1', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('paystack_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('paystack_email', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('paystack_public_key', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('paystack_secret_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('paystack_test_public_key', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('paystack_test_secret_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('ccavenue_sandbox', '1', 'plugin-osclass_pay', 'BOOLEAN');

    osc_set_preference('euplatesc_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('euplatesc_mid', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('euplatesc_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    
    osc_set_preference('begateway_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('begateway_shop_id', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('begateway_domain_checkout', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('begateway_secret_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    osc_set_preference('begateway_public_key', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('begateway_timeout', '60', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('begateway_test_mode', '1', 'plugin-osclass_pay', 'BOOLEAN');
  }


  if($version == '' || $version < 104) {
    osc_set_preference('banner_allow', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('banner_hook', '1', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('banner_fee_view', '0.02', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('banner_fee_click', '0.08', 'plugin-osclass_pay', 'STRING');
  }

  if($version == '' || $version < 105) {
    osc_set_preference('selling_allow', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('stock_management', '1', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('quantity_show', '1', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('seller_users', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('seller_all', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('selling_apply_membership', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('cart_button_hook', '1', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('status_disable', '0', 'plugin-osclass_pay', 'BOOLEAN');
  }

  
  if($version == '' || $version < 106) {
    osc_set_preference('groups_limit_items', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('groups_max_items', 10, 'plugin-osclass_pay', 'INTEGER');
    osc_set_preference('groups_max_items_days', 30, 'plugin-osclass_pay', 'INTEGER');
    osc_set_preference('groups_max_items_type', 0, 'plugin-osclass_pay', 'INTEGER');
    
    osc_set_preference('weaccept_enabled', '0', 'plugin-osclass_pay', 'BOOLEAN');
    osc_set_preference('weaccept_integration_id', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('weaccept_iframe_id', '', 'plugin-osclass_pay', 'STRING');
    osc_set_preference('weaccept_api_key', osp_crypt(''), 'plugin-osclass_pay', 'STRING');
    
    if($version > 100) {
      $this->import('osclass_pay/model/struct_update_106.sql');
    }
  }

  if($version == '' || $version < 108) {
    if($version > 100) {
      $this->import('osclass_pay/model/struct_update_108.sql');
    }
  }


  if($version == '' || $version < 108) {
    $locales = OSCLocale::newInstance()->listAllEnabled();

    // Promote listing
    $osp_email_bt_new = array();
    foreach($locales as $l) {
      $email_text  = '<p>Hi {CONTACT_NAME}!</p>';
      $email_text .= '<p>You have just submitted new bank transfer payment with following details:</p>';
      $email_text .= '<p><br/></p>';
      $email_text .= '<p>Transaction ID: {TRANSACTION_ID}</p>';
      $email_text .= '<p>Variable symbol: {VARIABLE_SYMBOL}</p>';
      $email_text .= '<p>Amount: {PRICE}</p>';
      $email_text .= '<p>Account to pay: {ACCOUNT}</p>';

      $email_text .= '<p><br/></p>';

      $email_text .= '<p>Please transfer funds to our account {ACCOUNT} as soon as possible to speed up process. Once funds are on our account, we complete your payment. Note that bank transfer can take up to 3 business days.</p>';


      $email_text .= '<p><br/></p>';
      $email_text .= '<p>Thank you, <br />{WEB_TITLE}</p>';


      $osp_email_bt_new[$l['pk_c_code']]['s_title'] = '{WEB_TITLE} - New bank transfer initiated';
      $osp_email_bt_new[$l['pk_c_code']]['s_text'] = $email_text;
    }

    Page::newInstance()->insert( array('s_internal_name' => 'osp_email_bt_new', 'b_indelible' => '1'), $osp_email_bt_new);
  }
  
  if($version == '' || $version < 110) {
    osc_set_preference('filter_button_hook', '0', 'plugin-osclass_pay', 'BOOLEAN');
  }
  
  
  if($version == '' || $version < 111) {
    if($version > 100) {
      $this->import('osclass_pay/model/struct_update_111.sql');
    }
  }



  // UPLOAD EMAIL TEMPLATES
  if($version == '') {
    $locales = OSCLocale::newInstance()->listAllEnabled();

    // Promote listing
    $osp_email_promote = array();
    foreach($locales as $l) {
      $email_text  = '<p>Hi {CONTACT_NAME}!</p>';
      $email_text .= '<p>We just published your item {ITEM_URL} on {WEB_TITLE}, you may want to promote it and make it more attractive!</p>';
      $email_text .= '<p><br/></p>';

      $email_text .= '<p>{START_PUBLISH}</p>';
      $email_text .= '<p><strong>Publish fee</strong> - In order to make your ad available to anyone on {WEB_TITLE}, you should complete the process and pay the publish fee.</p>';
      $email_text .= '<p>{END_PUBLISH}</p>';

      $email_text .= '<p>{START_IMAGE}</p>';
      $email_text .= '<p><strong>Image fee</strong> - In order to show images on your listing, you need to pay the image fee.</p>';
      $email_text .= '<p>{END_IMAGE}</p>';

      $email_text .= '<p>{START_PREMIUM}</p>';
      $email_text .= '<p><strong>Make premium</strong> - Make your ad premium and make it to appear on top result of the searches made on {WEB_TITLE}.</p>';
      $email_text .= '<p>{END_PREMIUM}</p>';

      $email_text .= '<p>{START_HIGHLIGHT}</p>';
      $email_text .= '<p><strong>Highlight</strong> - Highlight your ad and make it more attractive.</p>';
      $email_text .= '<p>{END_HIGHLIGHT}</p>';

      $email_text .= '<p>{START_MOVETOTOP}</p>';
      $email_text .= '<p><strong>Move to top</strong> - Move your listing to top and make it to show in top of search results.</p>';
      $email_text .= '<p>{END_MOVETOTOP}</p>';

      $email_text .= '<p>{START_REPUBLISH}</p>';
      $email_text .= '<p><strong>Republish</strong> - Automatically republish/renew your listing in selected periods multiple times.</p>';
      $email_text .= '<p>{END_REPUBLISH}</p>';

      $email_text .= '<p><br/></p>';
      $email_text .= '<p>You can promote your listing in {ACCOUNT_LINK} or on following link: <br />{PROMOTE_LINK}</p>';

      $email_text .= '<p><br/></p>';
      $email_text .= '<p>This is an automatic email, if you already did that, please ignore this email.</p>';
      $email_text .= '<p>Thank you, <br />{WEB_TITLE}</p>';


      $osp_email_promote[$l['pk_c_code']]['s_title'] = '{WEB_TITLE} - Promote your ad: {ITEM_TITLE}';
      $osp_email_promote[$l['pk_c_code']]['s_text'] = $email_text;
    }


    // Promotion has expired
    $osp_email_expired = array();
    foreach($locales as $l) {

      $email_text  = '<p>Hi {CONTACT_NAME}!</p>';
      $email_text .= '<p>Some promotions on {ITEM_URL} has expired!</p>';
      $email_text .= '<p><br/></p>';

      $email_text .= '<p>{START_PREMIUM}</p>';
      $email_text .= '<p><strong>Premium</strong> - listing is no more premium as paid period has expired.</p>';
      $email_text .= '<p>{END_PREMIUM}</p>';

      $email_text .= '<p>{START_HIGHLIGHT}</p>';
      $email_text .= '<p><strong>Highlight</strong> - listing is no more highlighted as paid period has expired.</p>';
      $email_text .= '<p>{END_HIGHLIGHT}</p>';

      $email_text .= '<p>{START_REPUBLISH}</p>';
      $email_text .= '<p><strong>Republish</strong> - listing has been republished for last time and will not be republished anymore, as paid repeats has been used.</p>';
      $email_text .= '<p>{END_REPUBLISH}</p>';

      $email_text .= '<p><br/></p>';
      $email_text .= '<p>You can promote your listing in {ACCOUNT_LINK} or on following link: <br />{PROMOTE_LINK}</p>';

      $email_text .= '<p><br/></p>';
      $email_text .= '<p>This is an automatic email, if you already did that, please ignore this email.</p>';
      $email_text .= '<p>Thank you, <br />{WEB_TITLE}</p>';


      $osp_email_expired[$l['pk_c_code']]['s_title'] = '{WEB_TITLE} - Some promotions has expired on your ad: {ITEM_TITLE}';
      $osp_email_expired[$l['pk_c_code']]['s_text'] = $email_text;
    }


    // Membership has expired
    $osp_email_expired_membership = array();
    foreach($locales as $l) {

      $email_text  = '<p>Hi {CONTACT_NAME}!</p>';
      $email_text .= '<p>Let us inform you, that your membership on {WEB_TITLE} in group {GROUP} has expired!</p>';
      $email_text .= '<p>You will have no more access to premium content and special discount provided by membership.</p>';
      $email_text .= '<p>If you would like to continue in membership, you can extend it in {ACCOUNT_LINK} on our site.</p>';

      $email_text .= '<p><br/></p>';
      $email_text .= '<p>This is an automatic email, if you already did that, please ignore this email.</p>';
      $email_text .= '<p>Thank you, <br />{WEB_TITLE}</p>';


      $osp_email_expired_membership[$l['pk_c_code']]['s_title'] = '{WEB_TITLE} - Some promotions has expired on your ad: {ITEM_TITLE}';
      $osp_email_expired_membership[$l['pk_c_code']]['s_text'] = $email_text;
    }


    // Bonus credits to user periodically
    $osp_email_bonus_credit = array();
    foreach($locales as $l) {

      $email_text  = '<p>Hi {CONTACT_NAME}!</p>';
      $email_text .= '<p>Let us inform you, we have just sent you {CREDIT} as a bonus!</p>';

      $email_text .= '{GROUP_BONUS}';

      $email_text .= '<p>You can use credits to promote your listings or to extend membership.</p>';
      $email_text .= '<p>To use credits, login to {ACCOUNT_LINK}.</p>';

      $email_text .= '<p><br/></p>';
      $email_text .= '<p>This is an automatic email, if you already did that, please ignore this email.</p>';
      $email_text .= '<p>Thank you, <br />{WEB_TITLE}</p>';


      $osp_email_bonus_credit[$l['pk_c_code']]['s_title'] = '{WEB_TITLE} - You got bonus credits';
      $osp_email_bonus_credit[$l['pk_c_code']]['s_text'] = $email_text;
    }


    Page::newInstance()->insert( array('s_internal_name' => 'osp_email_promote', 'b_indelible' => '1'), $osp_email_promote);
    Page::newInstance()->insert( array('s_internal_name' => 'osp_email_expired', 'b_indelible' => '1'), $osp_email_expired);
    Page::newInstance()->insert( array('s_internal_name' => 'osp_email_expired_membership', 'b_indelible' => '1'), $osp_email_expired_membership);
    Page::newInstance()->insert( array('s_internal_name' => 'osp_email_bonus_credit', 'b_indelible' => '1'), $osp_email_bonus_credit);
  }


  if($version == '' || $version < 104) {
    $locales = OSCLocale::newInstance()->listAllEnabled();
    $osp_email_banner = array();

    // Banner management
    foreach($locales as $l) {
      $email_text  = '<p>Hi!</p>';
      $email_text .= '<p>We just reviewed your banner {BANNER_NAME} and it was {STATUS}.</p>';
      $email_text .= '<p>Comment: {COMMENT}</p>';
      $email_text .= '<p><br/></p>';
      
      $email_text .= '<p>{START_APPROVED}</p>';
      $email_text .= '<p>In order to show banner on our site it is required to pay it\'s budget. Please click on following link to initiate payment: <br/>{PAYMENT_LINK}</p>';
      $email_text .= '<p>{END_APPROVED}</p>';

      $email_text .= '<p>{START_REJECTED}</p>';
      $email_text .= '<p>If you are still interested in advertising on our site, please follow the reviewer comment, check our terms and conditions and submit banner again.</p>';
      $email_text .= '<p>{END_REJECTED}</p>';

      $email_text .= '<p><br/></p>';

      $email_text .= '<p>Thank you, <br />{WEB_TITLE}</p>';

      $osp_email_banner[$l['pk_c_code']]['s_title'] = '{WEB_TITLE} - Banner review';
      $osp_email_banner[$l['pk_c_code']]['s_text'] = $email_text;
    }

    Page::newInstance()->insert( array('s_internal_name' => 'osp_email_banner', 'b_indelible' => '1'), $osp_email_banner);
  }


  if($version == '' || $version < 105) {
    $locales = OSCLocale::newInstance()->listAllEnabled();

    // Orders management
    $osp_email_order = array();
    foreach($locales as $l) {
      $email_text  = '<p>Hi {CONTACT_NAME}!</p>';
      
      $email_text .= '<p>{START_NEW}</p>';
      $email_text .= '<p>We have successfully received your order. Our team will do the best to deliver it as soon as possible.</p>';
      $email_text .= '<p><br/></p>';
      $email_text .= '<p>{END_NEW}</p>';

      $email_text .= '<p>{START_PROCESSING}</p>';
      $email_text .= '<p>Status of your order is <strong>PROCESSING</strong>. We are preparing your order to be shipped.</p>';
      $email_text .= '<p>{END_PROCESSING}</p>';

      $email_text .= '<p>{START_SHIPPED}</p>';
      $email_text .= '<p>Status of your order is <strong>SHIPPED</strong>. Currier will contact you shortly to deliver order to your adress.</p>';
      $email_text .= '<p>{END_SHIPPED}</p>';

      $email_text .= '<p>{START_COMPLETED}</p>';
      $email_text .= '<p>Status of your order is <strong>COMPLETED</strong>. Order has been delivered to you and is now closed.</p>';
      $email_text .= '<p>{END_COMPLETED}</p>';

      $email_text .= '<p>{START_CANCELLED}</p>';
      $email_text .= '<p>Your order has been <strong>CANCELLED</strong>. All funds associated with this order will be send back to your account. We are looking forward for your next order.</p>';
      $email_text .= '<p>{END_CANCELLED}</p>';

      $email_text .= '<p>Comment: {COMMENT}</p>';
      $email_text .= '<p><br/></p>';

      $email_text .= '<p>Order content:</p>';
      $email_text .= '<p>{ORDER_CONTENT}</p>';

      $email_text .= '<p><br/></p>';

      $email_text .= '<p>If you would like to see your order details, you can check it in {ORDER_LINK} on our site.</p>';

      $email_text .= '<p><br/></p>';
      $email_text .= '<p>Thank you, <br />{WEB_TITLE}</p>';


      $osp_email_order[$l['pk_c_code']]['s_title'] = '{WEB_TITLE} - Order #{ORDER_ID}';
      $osp_email_order[$l['pk_c_code']]['s_text'] = $email_text;
    }

    Page::newInstance()->insert( array('s_internal_name' => 'osp_email_order', 'b_indelible' => '1'), $osp_email_order);
  }
}


public function uninstall() {
  // DELETE ALL TABLES
  $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_wallet()));
  $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_log()));
  $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_item_payment()));
  $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_price_category()));
  $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_price_location()));
  $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_user_to_group()));
  $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_user_group()));
  $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_user_cart()));
  $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_transfer()));
  $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_banner()));
  $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_rate()));
  $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_item_data()));


  // DELETE ALL EMAIL TEMPLATES
  $page_promote = Page::newInstance()->findByInternalName('osp_email_promote');
  $page_expired = Page::newInstance()->findByInternalName('osp_email_expired');
  $page_expired_membership = Page::newInstance()->findByInternalName('osp_email_expired_membership');
  $page_bonus_credit = Page::newInstance()->findByInternalName('osp_email_bonus_credit');
  $page_banner = Page::newInstance()->findByInternalName('osp_email_banner');
  $page_order = Page::newInstance()->findByInternalName('osp_email_order');
  $page_transfer = Page::newInstance()->findByInternalName('osp_email_bt_new');

  Page::newInstance()->deleteByPrimaryKey($page_promote['pk_i_id']);
  Page::newInstance()->deleteByPrimaryKey($page_expired['pk_i_id']);
  Page::newInstance()->deleteByPrimaryKey($page_expired_membership['pk_i_id']);
  Page::newInstance()->deleteByPrimaryKey($page_bonus_credit['pk_i_id']);
  Page::newInstance()->deleteByPrimaryKey($page_banner['pk_i_id']);
  Page::newInstance()->deleteByPrimaryKey($page_order['pk_i_id']);
  Page::newInstance()->deleteByPrimaryKey($page_transfer['pk_i_id']);


  // DELETE ALL PREFERENCES
  $db_prefix = DB_TABLE_PREFIX;
  $query = "DELETE FROM {$db_prefix}t_preference WHERE s_section = 'plugin-osclass_pay'";
  $this->dao->query($query);
}


// DO QUERIES ON VERSION UPDATE
public function versionUpdate() {
  $version = (osp_param('version') <> '' ? osp_param('version') : 100);    // v100 is initial

  if( $version < 101 ) { 
    $this->dao->query(sprintf("ALTER TABLE %st_osp_user_group ADD COLUMN s_custom VARCHAR(100);", DB_TABLE_PREFIX));
    $this->dao->query(sprintf("ALTER TABLE %st_osp_user_group ADD COLUMN i_rank INT;", DB_TABLE_PREFIX));
    osc_set_preference('version', 101, 'plugin-osclass_pay', 'INTEGER');
  }

  if( $version < 102 ) { 
    $this->dao->query(sprintf("ALTER TABLE %st_osp_log ADD COLUMN s_cart VARCHAR(1000);", DB_TABLE_PREFIX));
    osc_set_preference('version', 102, 'plugin-osclass_pay', 'INTEGER');
  }

  if( $version < 103 ) { 
    $this->dao->query(sprintf("CREATE TABLE %st_osp_banner ( pk_i_id INT UNSIGNED NOT NULL AUTO_INCREMENT, fk_s_banner_id VARCHAR(500), i_type INT(1), fk_i_user_id INT, s_name VARCHAR(100), s_key VARCHAR(100), s_url VARCHAR(500), s_code VARCHAR(5000), d_price_click DECIMAL(10, 3) DEFAULT 0, d_price_view DECIMAL(10, 3) DEFAULT 0, d_budget DECIMAL(10, 3) DEFAULT 0, dt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, s_category VARCHAR(100), s_size_width VARCHAR(10), s_size_height VARCHAR(10), i_status INT(1) DEFAULT 0, s_comment VARCHAR(1000), i_ba_advert_id INT, PRIMARY KEY (pk_i_id) ) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';", DB_TABLE_PREFIX));
    osc_set_preference('version', 103, 'plugin-osclass_pay', 'INTEGER');
  }

  if( $version < 104 ) { 
    $this->install($version);
    $this->dao->query(sprintf("ALTER TABLE %st_osp_banner ADD COLUMN fk_i_user_id INT;", DB_TABLE_PREFIX));
    osc_set_preference('version', 104, 'plugin-osclass_pay', 'INTEGER');
  }

  if( $version < 105 ) { 
    $this->dao->query(sprintf("CREATE TABLE %st_osp_currency_rate (s_from VARCHAR(3) NOT NULL, s_to VARCHAR(3) NOT NULL, f_rate FLOAT NULL DEFAULT 1.0, dt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (s_from, s_to) ) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';", DB_TABLE_PREFIX));
    $this->dao->query(sprintf("CREATE TABLE %st_osp_order (pk_i_id INT UNSIGNED NOT NULL AUTO_INCREMENT, fk_i_user_id INT, s_cart VARCHAR(5000), s_item_id VARCHAR(1000), f_amount FLOAT, f_amount_regular FLOAT, s_amount_comment VARCHAR(200), i_discount INT, s_currency_code VARCHAR(3), i_status INT, s_comment VARCHAR(500), dt_date DATETIME, fk_i_payment_id INT NOT NULL, PRIMARY KEY (pk_i_id) ) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';", DB_TABLE_PREFIX));
    $this->dao->query(sprintf("CREATE TABLE %st_osp_item_data (fk_i_item_id INT, i_sell INT DEFAULT 0, i_quantity INT DEFAULT 1, PRIMARY KEY (fk_i_item_id) ) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';", DB_TABLE_PREFIX));
    osp_get_currency_rates();
    osc_set_preference('version', 105, 'plugin-osclass_pay', 'INTEGER');
  }

  if( $version < 106 ) { 
    $this->install($version);
    osc_set_preference('version', 106, 'plugin-osclass_pay', 'INTEGER');
  }

  if( $version < 107 ) { 
    $this->install($version);
    osc_set_preference('version', 107, 'plugin-osclass_pay', 'INTEGER');
  }

  if($version < 108) { 
    $this->install($version);
    osc_set_preference('version', 108, 'plugin-osclass_pay', 'INTEGER');
  }
  
  if($version < 109) { 
    $this->dao->query(sprintf("ALTER TABLE %st_osp_bank_transfer ADD COLUMN s_extra VARCHAR(5000) NULL;", DB_TABLE_PREFIX));
    osc_set_preference('version', 109, 'plugin-osclass_pay', 'INTEGER');
  }
  
  if($version < 110) { 
    $this->install($version);
    osc_set_preference('version', 110, 'plugin-osclass_pay', 'INTEGER');
  }
  
  if($version < 111) { 
    $this->install($version);
    $this->generateOrderItems();
    osc_set_preference('version', 111, 'plugin-osclass_pay', 'INTEGER');
  }

  if($version < 112) { 
    $this->dao->query(sprintf("ALTER TABLE %st_osp_item MODIFY i_hours FLOAT;", DB_TABLE_PREFIX));
    osc_set_preference('version', 112, 'plugin-osclass_pay', 'INTEGER');
  }

  if($version < 113) { 
    $this->dao->query(sprintf("ALTER TABLE %st_osp_price_category MODIFY i_hours VARCHAR(10) NULL;", DB_TABLE_PREFIX));
    $this->dao->query(sprintf("ALTER TABLE %st_osp_item MODIFY i_hours VARCHAR(10) NULL;", DB_TABLE_PREFIX));
    osc_set_preference('version', 113, 'plugin-osclass_pay', 'INTEGER');
  }

  if($version < 114) { 
    $this->dao->query(sprintf("ALTER TABLE %st_osp_user_group ADD COLUMN i_free_items_101 INT DEFAULT 0;", DB_TABLE_PREFIX));
    $this->dao->query(sprintf("ALTER TABLE %st_osp_user_group ADD COLUMN i_free_items_201 INT DEFAULT 0;", DB_TABLE_PREFIX));
    $this->dao->query(sprintf("ALTER TABLE %st_osp_user_group ADD COLUMN i_free_items_401 INT DEFAULT 0;", DB_TABLE_PREFIX));
    osc_set_preference('version', 114, 'plugin-osclass_pay', 'INTEGER');
  }
  
  osc_reset_preferences();
}


// GET AUCTION
public function getAuction($id) {
  if($id <= 0) { return false; }
  
  $this->dao->select();
  $this->dao->from($this->getTable_auction());
  $this->dao->where('pk_i_id', (int)$id);

  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }

  return false;
}


// UPDATE AUCTION
public function updateAuction($id, $data) {
  $data['dt_update_date'] = date('Y-m-d H:i:s');
  return $this->dao->update($this->getTable_auction(), $data, array('pk_i_id' => (int)$id));
}


// DELETE PENDING
public function deletePendingOld($days = 180) {
  //$limit_date = date('Y-m-d H:i:s', strtotime("- 14 day"));
  $query = "DELETE FROM {$this->getTable_pending()} WHERE dt_date < (NOW() - INTERVAL {$days} DAY)";
  $this->dao->query($query);
}


// GET PAYMENT BY ID
public function getPayment($payment_id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_log());
  $this->dao->where('pk_i_id', $payment_id);
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return false;
}

public function getLog($payment_id) {
  return getPayment($payment_id);
}

// GET USER ACTIVE ITEMS
public function findByUserID($userId, $start = 0, $end = null){
  $this->dao->select('l.*, i.*');
  $this->dao->from(DB_TABLE_PREFIX.'t_item i, '.DB_TABLE_PREFIX.'t_item_location l');
  $this->dao->where('l.fk_i_item_id = i.pk_i_id');
  $this->dao->where('i.dt_expiration >= \'' . date('Y-m-d H:i:s') .'\'');

  $array_where = array(
    'i.fk_i_user_id' => $userId,
    'i.b_enabled' => 1,
    'i.b_spam' => 0
  );
  
  $this->dao->where($array_where);
  $this->dao->orderBy('i.pk_i_id', 'DESC');
  if($end!=null) {
    $this->dao->limit($start, $end);
  } else {
    if ($start > 0 ) {
      $this->dao->limit($start);
    }
  }

  $result = $this->dao->get();
  
  if($result == false) {
    return array();
  }

  $items  = $result->result();

  //return Item::newInstance()->extendData($items);
  return $items;
}


// COUNT USER ITEMS
public function countUserItems($id = '', $email = '', $days = 0) {
  $this->dao->select('count(*) as i_count');
  $this->dao->from($this->getTable_item() . ' as i');

  if($id > 0) {
    $this->dao->where('i.fk_i_user_id', $id);
  } else if ($email <> '') {
    $this->dao->where('i.s_contact_email', $email);
  } else {
    return 0;
  }


  $method = osp_param('groups_max_items_type');

  if($method == 1 || $method == 3) {
    $this->dao->where('i.b_enabled', 1);
    $this->dao->where('i.b_active', 1);
    $this->dao->where('i.b_spam', 0);
  }

  if($method == 2 || $method == 3) {
    $this->dao->where('i.b_premium', 0);
  }

  if($days > 0) {
    $this->dao->where('date(i.dt_pub_date) between date_sub(now(), INTERVAL ' . $days . ' DAY) AND now()');
  }

  $result = $this->dao->get();
  
  if($result) {
    return $result->row()['i_count'];
  }

  return 0;
}

// GET USER ITEMS
public function getUserItems($id = '', $email = '', $days = 0) {
  $this->dao->select('i.*');
  $this->dao->from($this->getTable_item() . ' as i');

  if($id > 0) {
    $this->dao->where('i.fk_i_user_id', $id);
  } else if ($email <> '') {
    $this->dao->where('i.s_contact_email', $email);
  } else {
    return 0;
  }


  $method = osp_param('groups_max_items_type');

  if($method == 1 || $method == 3) {
    $this->dao->where('i.b_enabled', 1);
    $this->dao->where('i.b_active', 1);
    $this->dao->where('i.b_spam', 0);
  }

  if($method == 2 || $method == 3) {
    $this->dao->where('i.b_premium', 0);
  }

  if($days > 0) {
    $this->dao->where('date(i.dt_pub_date) between date_sub(now(), INTERVAL ' . $days . ' DAY) AND now()');
  }
  
  $this->dao->orderBy('i.dt_pub_date', 'DESC');


  $result = $this->dao->get();
  
  if($result) {
    return $result->result();
  }

  return array();
}

// GET PAYMENT BY CODE AND SOURCE
public function getPaymentByCode($code, $source) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_log());
  $this->dao->where('s_code', $code);
  $this->dao->where('s_source', $source);
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  return false;
}


// GET PAYMENTS BY UESR
public function getPaymentsByUser($user_id, $history = '') {
  $this->dao->select('*');
  $this->dao->from($this->getTable_log());
  $this->dao->where('fk_i_user_id', $user_id);

  if($history == 1) {
    $this->dao->where('(year(dt_date)*100 + month(dt_date) = year(NOW())*100 + month(NOW()))');
  } else if ($history == 2) {
    $this->dao->where('(year(dt_date) = year(NOW()))');
  }

  $this->dao->orderby('pk_i_id DESC');

  $result = $this->dao->get();

  if($result) {
    return $result->result();
  }

  return array();
}


// GET RECORD FROM OSP_ITEM TABLE WITH RECORDS FOR TYPE OF TRANSACTION
public function getItem($type, $item_id, $paid = -1) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_item_payment());
  $this->dao->where('i_item_id', $item_id);
  $this->dao->where('s_type', $type);

  if($paid <> -1) {
    $this->dao->where('i_paid', $paid);
  }
  
  $result = $this->dao->get();

  if($result) {
    return $result->row();
  }

  return false;
}


// CREATE CART STRING FROM ITEMS TO BE PAID (for bank transfer)
public function itemsToCartString($item_id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_item_payment());
  $this->dao->where('i_item_id', $item_id);
  $this->dao->where('i_paid', 0);
  
  $result = $this->dao->get();

  $cart = array();

  if($result) {
    $items = $result->result();

    if(count($items) > 0) {
      foreach($items as $i) {
        $s = array();
        $s[0] = $i['s_type'];
        $s[1] = 1;
        $s[2] = $i['i_item_id'];

        if($i['s_type'] == OSP_TYPE_PREMIUM || $i['s_type'] == OSP_TYPE_HIGHLIGHT || $i['s_type'] == OSP_TYPE_REPUBLISH) {
          $s[3] = $i['i_hours'];
        }

        if($i['s_type'] == OSP_TYPE_REPUBLISH) {
          $s[4] = $i['i_repeat'];
        }

        $s = array_filter($s);
        $string = implode('x', $s);

        $cart[] = $string;
      }
    }
  }

  return implode('|', $cart);
}


// CREATE RECORD FOR ITEM IN OSP_ITEM TABLE IF DOES NOT EXISTS, OTHERWISE UPDATE RECORD
public function createItem($type, $item_id, $paid = -1, $date = NULL, $payment = NULL, $expire = NULL, $hours = NULL, $repeat = NULL) {
  $item = $this->getItem($type, $item_id);
  $was_paid = isset($item['i_paid']) ? $item['i_paid'] : 0;
  $minutes = round($hours*60);

  if($date == '') { 
    $date = date("Y-m-d H:i:s"); 
  }
  
  $value = array(
    's_type' => $type, 
    'i_item_id' => $item_id, 
    'i_hours' => $hours, 
    'i_repeat' => $repeat, 
    'dt_date' => $date, 
    'i_paid' => $paid
  );
  
  if($payment <> '') {
    $value['fk_i_payment_id'] = $payment;
  }
  
  $curr_date = date('Y-m-d H:i:s');


  if($expire == '' && $minutes > 0) {
    $expire = date('Y-m-d H:i:s', strtotime(" + " . $minutes . " minutes", strtotime($curr_date)));
  }

  if(empty($item) || $was_paid == 0 || @$item['pk_i_id'] <= 0) {
    $value['dt_expire'] = $expire;

  } else {
    $orig_date = date('Y-m-d H:i:s', strtotime(@$item['dt_expire']));
    $new_date = date('Y-m-d H:i:s', strtotime($expire));

    if($curr_date > $orig_date) {          // promotion exists, but has expired already - for some reason record is in database (check cron)
      $value['dt_expire'] = $expire;

    } else {                               // promotion exists, but has not expired yet, so to it's end date add promotion hours
      //$diff = abs($curr_date - $orig_date);
      //$hours_diff = $diff / ( 60 * 60 );
      //$date = date('Y-m-d H:i:s', strtotime(" + " . $hours_diff . " hours", strtotime($new_date)));
      
      if($type <> OSP_TYPE_REPUBLISH) {
        $value['dt_expire'] = date('Y-m-d H:i:s', strtotime(" + " . $minutes . " minutes", strtotime($orig_date)));

      } else {   // Set next republish datetime
        $value['dt_expire'] = date('Y-m-d H:i:s', strtotime(" + " . $minutes . " minutes", strtotime($curr_date)));
      }
    }      
  }


  if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_PACK || $type == OSP_TYPE_IMAGE || $type == OSP_TYPE_TOP) {
    $expire = null; //= '2099-01-01 00:00:00';
  }

  
  if($type == OSP_TYPE_REPUBLISH) {
    if($repeat == '' || $repeat <= 0) {
      $repeat = 1;
    }

    if(isset($item['i_repeat']) && $item['i_repeat'] > 0 && $was_paid == 1) {  // is republish
      $value['i_repeat'] = $item['i_repeat'] + $repeat;
    } else {
      $value['i_repeat'] = $repeat;
    }
  }


  if($type == OSP_TYPE_PREMIUM || $type == OSP_TYPE_HIGHLIGHT || $type == OSP_TYPE_REPUBLISH) {
    if($hours == '' || $hours <= 0) {
      $hours = 24;
    }

    if(isset($item['i_hours']) && $item['i_hours'] > 0 && $was_paid == 1 && $type <> OSP_TYPE_REPUBLISH) {
      $value['i_hours'] = $item['i_hours'] + $hours;
    } else {
      $value['i_hours'] = $hours;
    }
  }


  $where = array(
    'i_item_id' => $item_id,
    's_type' => $type
  );


  if(empty($item) || @$item['pk_i_id'] <= 0) {
    $this->dao->insert($this->getTable_item_payment(), $value);
  } else {
    $this->dao->update($this->getTable_item_payment(), $value, $where);
  }
}


// REMOVE ITEM RECORD
public function deleteItem($type, $item_id) {
  if($type == -1) {
    return $this->dao->delete($this->getTable_item_payment(), array('i_item_id' => $item_id));
  } else {
    return $this->dao->delete($this->getTable_item_payment(), array('s_type' => $type, 'i_item_id' => $item_id));
  }
}


// REMOVE UNPAID ITEMS RECORD
public function deleteUnpaidItems($type) {
  return $this->dao->delete($this->getTable_item_payment(), array('s_type' => $type, 'i_paid' => 0));
}


// CHECK IF FEE IS PAID BY TYPE
public function feeIsPaid($type, $item_id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_item_payment());
  $this->dao->where('i_item_id', $item_id);
  $this->dao->where('s_type', $type);
  
  $result = $this->dao->get();

  if($result) {
    $row = $result->row();
    if(isset($row['i_paid']) && $row['i_paid'] == 1) {
      return true;
//    } else if ((!isset($row['i_paid']) || $row['i_paid'] == '') && ($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE)) {
    } else if ((!isset($row['i_paid']) || $row['i_paid'] == '') && ($type == OSP_TYPE_PUBLISH)) {
      return true;   // for publish fee, if not found, considered as paid (for existing listings)
    } else {
      return false;
    }
//  } else if ($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE) {
  } else if ($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE) {
    return true;   // for publish fee, if not found, considered as paid (for existing listings)
  }

  return false;
}


// CHECK IF FEE RECORD EXISTS
public function feeExists($type, $item_id, $paid = -1) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_item_payment());
  $this->dao->where('i_item_id', $item_id);
  $this->dao->where('s_type', $type);
  
  if($paid <> -1) {
    $this->dao->where('i_paid', $paid);
  }

  $result = $this->dao->get();

  if($result) {
    $row = $result->row();
    if(isset($row['i_paid']) && $row['i_paid'] <> '') {
      return true;
    }
  }

  return false;
}


// PAY FEE BY TYPE
public function payFee($type, $item_id, $payment_id, $expire = NULL, $hours = NULL, $repeat = NULL) {
  $this->createItem($type, $item_id, 1, date("Y-m-d H:i:s"), $payment_id, $expire, $hours, $repeat);
}


// GET USER WALLET DATA
public function getWallet($user_id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_wallet());
  $this->dao->where('fk_i_user_id', $user_id);
  $result = $this->dao->get();
  
  if($result) {
    $row = $result->row();
    $row['formatted_amount'] = (isset($row['i_amount']) ? $row['i_amount'] : 0)/1000000000000;
    return $row;
  }
  
  return false;
}


// ADD MONEY TO USER WALLET
public function addWallet($user, $amount) {
  $amount = (int)($amount*1000000000000);
  $wallet = $this->getWallet($user);
  
  if(isset($wallet['i_amount'])) {
    return $this->dao->update($this->getTable_wallet(), array('i_amount' => $amount+$wallet['i_amount']), array('fk_i_user_id' => $user));
  } else {
    return $this->dao->insert($this->getTable_wallet(), array('fk_i_user_id' => $user, 'i_amount' => $amount));
  }
}


// GET FEE TO BE PAID
public function getFee($type, $category, $country = NULL, $region = NULL, $hours = NULL) {
  $fee = $this->getCategoryFee($type, $category, $hours);

  if($country <> '' || $region <> '') {
    $uplift = $this->getLocationFee($type, $country, $region);
    
    $uplift = $uplift/100;

    if($uplift <> '') {
      $uplift = 1 + $uplift;
    } else {
      $uplift = 1;
    }
  } else {
    $uplift = 1;
  }
  
  return $fee * $uplift;
}  
  

// GET FEE FOR CATEGORY
public function getCategoryFee($type, $category_id, $hours = NULL) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_price_category());
  $this->dao->where('fk_i_category_id', $category_id);
  $this->dao->where('s_type', $type);
  
  // if($type == OSP_TYPE_REPUBLISH) {
  if($hours <> '' && $hours > 0) {
    $this->dao->where('i_hours', $hours);
  }
  
  $result = $this->dao->get();
  
  if($result) {
    $fee = $result->row();
    if(isset($fee['f_fee'])) {
      return $fee['f_fee'];
    }
  }

  return osp_category_default_fee($type, $hours);
}


// GET FEE FOR LOCATION
public function getLocationFee($type, $country = NULL, $region = NULL) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_price_location());
  $this->dao->where('s_type', $type);

  if($country <> '') {  
    $this->dao->where('fk_c_country_code', $country);
  }

  if($region <> '' && $region <> 0 ) {
    $this->dao->where('fk_i_region_id', $region);
  } else {
    $this->dao->where('coalesce(fk_i_region_id,0) = 0');
  }
  
  $result = $this->dao->get();
  
  if($result) {
    $fee = $result->row();
    if(isset($fee['f_fee'])) {
      return $fee['f_fee'];
    }
  }
  
  return 0;
}


// GET FEE FOR ALL LOCATIONS
public function getLocationFees() {
  $this->dao->select('*');
  $this->dao->from($this->getTable_price_location());
  $this->dao->orderby('s_type ASC, fk_c_country_code ASC, fk_i_region_id ASC');

  $result = $this->dao->get();
  
  if($result) {
    return $result->result();
  }
  
  return array();
}


// INSERT CATEGORY PRICE, IF EXIST UPDATE
public function updateCategoryFee($type, $category_id, $fee, $hours = NULL) {
  $default_fee = osp_category_default_fee($type, $hours); 

  if($fee < 0 && $fee !== '') { $fee = 0; }
  //$fee = ($fee > 0 ? $fee : 0);


  //if(($fee == 0 && $fee <> '') || ($fee <> '' && $fee <> $default_fee)) {
  //if(($fee === 0 && $fee <> '') || ($fee > 0 && $fee <> $default_fee || $type == OSP_TYPE_REPUBLISH)) {
  if($fee >= 0 && ($fee !== '' && $fee <> $default_fee) || $type == OSP_TYPE_REPUBLISH) {
    $this->dao->select();
    $this->dao->from($this->getTable_price_category());
    $this->dao->where('fk_i_category_id', (int)$category_id);
    $this->dao->where('s_type', $type);

    if($hours > 0) {
      $this->dao->where('i_hours', $hours);
    }
  
    if($hours > 0) {
      $value = array('fk_i_category_id' => $category_id, 's_type' => $type, 'i_hours' => $hours, 'f_fee' => $fee);
      $where = array('fk_i_category_id' => $category_id, 's_type' => $type, 'i_hours' => $hours);
    } else {
      $value = array('fk_i_category_id' => $category_id, 's_type' => $type, 'f_fee' => $fee);
      $where = array('fk_i_category_id' => $category_id, 's_type' => $type);
    }

    $result = $this->dao->get();

    if($result) {
      if($result->row()) {
        $this->dao->update($this->getTable_price_category(), $value, $where);
      } else {
        $this->dao->insert($this->getTable_price_category(), $value);
      }
    } else {
      $this->dao->insert($this->getTable_price_category(), $value);
    }

    // Remove existing payment request as fee was set to 0 (zero)
    //if($fee === 0 && $fee <> '') {
    if($fee == 0) {
      if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE) {
        $this->deleteUnpaidItems($type); 
      }
    }
  } else {
    $this->deleteCategoryFee($type, $category_id, $hours);
  }
}


// DELETE CATEGORY FEE
public function deleteCategoryFee($type, $category, $hours = NULL) {
  if($hours <> '' && $hours > 0) {
    return $this->dao->delete($this->getTable_price_category(), array('fk_i_category_id' => $category, 's_type' => $type, 'i_hours' => $hours));
  } else {
    return $this->dao->delete($this->getTable_price_category(), array('fk_i_category_id' => $category, 's_type' => $type));
  }
}


// INSERT LOCATION UPLIFT, IF EXIST UPDATE
public function updateLocationFee($type, $country, $region = NULL, $uplift = 0) {
  if($uplift <> '' && $uplift <> 0) {
    // get uplift record
    $this->dao->select('*');
    $this->dao->from($this->getTable_price_location());
    $this->dao->where('s_type', $type);

    $this->dao->where('fk_c_country_code', $country);
  
    if($region <> '' && $region <> 0) {
      $this->dao->where('fk_i_region_id', $region);
    } else {
      $this->dao->where('coalesce(fk_i_region_id, 0) = 0');
    }
  
    $result = $this->dao->get();

    $value = array('fk_c_country_code' => $country, 'fk_i_region_id' => $region, 's_type' => $type, 'f_fee' => $uplift);
    $where = array('fk_c_country_code' => $country, 'fk_i_region_id' => $region, 's_type' => $type);

    if($result) {
      if($result->row()) {
        $this->dao->update($this->getTable_price_location(), $value, $where);
      } else {
        $this->dao->insert($this->getTable_price_location(), $value);
      }
    } else {
      $this->dao->insert($this->getTable_price_location(), $value);
    }
  } else {
    $this->deleteLocationFee($type, $country, $region);
  }
}


// DELETE LOCATION FEE
public function deleteLocationFee($type, $country, $region = NULL) {
  if($region <> '' && $region <> 0) {
    return $this->dao->delete($this->getTable_price_location(), array('fk_c_country_code' => $country, 'fk_i_region_id' => $region, 's_type' => $type));
  } else {
    return $this->dao->delete($this->getTable_price_location(), array('fk_c_country_code' => $country, 'fk_i_region_id' => 0, 's_type' => $type));
    return $this->dao->delete($this->getTable_price_location(), array('fk_c_country_code' => $country, 'fk_i_region_id' => '', 's_type' => $type));
    return $this->dao->delete($this->getTable_price_location(), array('fk_c_country_code' => $country, 'fk_i_region_id' => NULL, 's_type' => $type));
  }
}


// TAKE CARE OF EXPIRED ELEMENTS
public function purgeExpired() {
  $return = array();
  $i = 0;

  // Take care of expired items
  $this->dao->select('*');
  $this->dao->from($this->getTable_item_payment());
  //$this->dao->where('dt_expire <= "' . date('Y-m-d H:i:s') . '"');
  $this->dao->where(sprintf("TIMESTAMPDIFF(MINUTE, dt_expire, '%s') >= 0", date('Y-m-d H:i:s')));
  $this->dao->where('i_paid', 1);

  
  $result = $this->dao->get();
  
  if($result) {
    $items = $result->result();
    $mItem = new ItemActions(false);
    
    if(count($items) > 0) {
      $return[$i] = ' --- ' . __('Listings section', 'osclass_pay') . ' --- '; $i++;

      foreach($items as $item) {
        $item_id = $item['i_item_id'];
        $item_original = Item::newInstance()->findByPrimaryKey($item['i_item_id']);
        $type = $item['s_type'];
        $notify = array($type);

        $return[$i] = array('item_id' => $item_id, 'type' => $type . ' - ' . osp_product_type_name($type), 'title' => osc_highlight($item_original['s_title'], 30));

        if($type == OSP_TYPE_PREMIUM) {
          $mItem->premium($item['i_item_id'], false);           // Unmark premium
          $this->deleteItem($type, $item_id);                   // Remove pay row
          $return[$i]['action'] = __('Remove premium mark', 'osclass_pay');
        }

        if($type == OSP_TYPE_HIGHLIGHT) {
          $this->deleteItem($type, $item_id);
          $return[$i]['action'] = __('Remove highlight mark', 'osclass_pay');
        }

        if($type == OSP_TYPE_REPUBLISH) {
          if($item['i_repeat'] <= 0) {
            $this->deleteItem($type, $item_id);     // No repeats left
            $return[$i]['action'] = __('Remove repblish options (all were used)', 'osclass_pay');

          } else {
            $curr_date = date('Y-m-d H:i:s');
            if($item['i_hours'] > 0) {
              $minutes = round($item['i_hours']*60);
              
              $expire = date('Y-m-d H:i:s', strtotime(" + " . $minutes . " minutes", strtotime($curr_date)));
              $repeat = $item['i_repeat'] - 1;

              $value = array('i_repeat' => $repeat, 'dt_expire' => $expire);
              $where = array('i_item_id' => $item_id, 's_type' => $type);

              Item::newInstance()->dao->query(sprintf('UPDATE %st_item SET dt_pub_date = NOW() WHERE pk_i_id = %d', DB_TABLE_PREFIX, $item_id));

              $this->dao->update($this->getTable_item_payment(), $value, $where);

              $return[$i]['action'] = sprintf(__('Republish item, %d repeats left, next republish on %s.', 'osclass_pay'), $repeat, $expire);
            }
          }
        }

        $i++;

        // send notification to user
        if(($type == OSP_TYPE_REPUBLISH && $item['i_repeat'] <= 0) || $type <> OSP_TYPE_REPUBLISH) {
          osp_email_expired($item_original, $notify);
        }
      }

      $return[$i] = ' --- --- '; $i++;
      $return[$i] = ' '; $i++;
    }
  }


  // Take care of expired memberships
  $this->dao->select('*');
  $this->dao->from($this->getTable_user_to_group());
  $this->dao->where(sprintf("TIMESTAMPDIFF(MINUTE, dt_expire, '%s') >= 0", date('Y-m-d H:i:s')));
  
  $result = $this->dao->get();
  
  if($result) {
    $users = $result->result();

    if(count($users) > 0) {
      $return[$i] = ' --- ' . __('Users section', 'osclass_pay') . ' --- '; $i++;

      foreach($users as $user) {
        $user_original = User::newInstance()->findByPrimaryKey($user['fk_i_user_id']);
        $group = $this->getGroup($user['fk_i_group_id']);

        osp_email_expired_membership($user_original, $group);

        $this->deleteUserGroup($user['fk_i_user_id']);
        $this->deactivateUserItemsAboveLimit($user['fk_i_user_id'], (isset($user_original['s_email']) ? $user_original['s_email'] : ''));

        $return[$i] = array('user_id' => $user['fk_i_user_id'], 'type' => '701 - ' . __('Membership', 'osclass_pay'), 'name' => osc_highlight($user_original['s_name'], 30), 'action' => sprintf(__('Membership in %s expired', 'osclass_pay'), $group['s_name']));

        $i++;
      }

      $return[$i] = ' --- --- '; $i++;
      $return[$i] = ' '; $i++;
    }
  }


  $runs = osp_param('cron_runs');
  $runs = explode(',', $runs);

  $runs = array_filter($runs);
  $runs = array_merge(array(date('Y-m-d H:i:s')), $runs);
  if(isset($runs[10])) { 
    unset($runs[10]);  // keep just last 10 runs in descending order (0) is latest
  }

  $runs = implode(',', $runs);

  osc_set_preference('cron_runs', $runs, 'plugin-osclass_pay', 'STRING');

  return $return;
}


// DEACTIVATE USER ITEMS ABOVE ITEM LIMITS
// Used when moving user out of group or into new group
public function deactivateUserItemsAboveLimit($user_id, $email = '', $new_group_id = NULL) {
  if(osp_param('groups_limit_items') == 1) {
    if((int)$new_group_id > 0) {
      $group = $this->getGroup($new_group_id);
      
      if(isset($group['pk_i_id']) && $group['pk_i_id'] > 0) {
        $max_items = (int)$group['i_max_items'];
        $max_items_days = (int)$group['i_max_items_days'];

      } else {
        $max_items = osp_param('groups_max_items');
        $max_items_days = osp_param('groups_max_items_days');
      }
      
    } else {
      $max_items = osp_param('groups_max_items');
      $max_items_days = osp_param('groups_max_items_days');
    }


    $count = $this->countUserItems($user_id, $email, $max_items_days);
    
    if($count > $max_items) {
      $items = $this->getUserItems($user_id, $email, $max_items_days);
     
      $i = 0;
      if(is_array($items) && count($items) > 0) {
        foreach($items as $item) {
          if($i >= $max_items) {
            if($item['b_active'] == 1) {
              $this->updateItemActive($item['pk_i_id'], 0);
            }
          }
          
          $i++;
        }
      }
    }
  }
}



// GET ALL USER PACKS
public function getPacks($group_id = -1, $only = 0, $is_admin = false) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_pack());

  if($only == 0) {
    if($group_id >= 0) {
      $this->dao->where('coalesce(i_group, 0) IN (' . $group_id . ',0)');
    }
  } else {
    if($group_id >= 0) {
      $this->dao->where('i_group', $group_id);
    }
  }
  
  $result = $this->dao->get();

  if($result) {
    $output = array();
    $data = $result->result();

    if(count($data) > 0) {
      foreach($data as $d) {
        $row = $d;

        $row['locale'] = $this->getPackLocale($d['pk_i_id'], Params::getParam('ospLocale'));
        $row['s_name'] = osp_locale($row, 's_name', $is_admin);
        $row['s_description'] = osp_locale($row, 's_description', $is_admin);
        $output[] = $row;
      }
    }

    return $output;
  }
  
  return array();
}


// UPDATE PACK LOCALE
public function updatePackLocale($pack_id, $locale, $name, $description) {
  $value = array(
    'fk_i_pack_id' => $pack_id,
    'fk_c_locale_code' => $locale,
    's_name' => $name,
    's_description' => $description
  );

  $this->dao->replace($this->getTable_pack_locale(), $value);    
}


// UPDATE USER GROUP LOCALE
public function updateUserGroupLocale($group_id, $locale, $name, $description, $custom) {
  $value = array(
    'fk_i_group_id' => $group_id,
    'fk_c_locale_code' => $locale,
    's_name' => $name,
    's_description' => $description,
    's_custom' => $custom
  );

  $this->dao->replace($this->getTable_user_group_locale(), $value);    
}


// GET PACK LOCALE
public function getPackLocale($pack_id, $locale = '') {
  if($locale == '') {
    $locale = osc_current_user_locale();
  }

  $this->dao->select();
  $this->dao->from($this->getTable_pack_locale());
  $this->dao->where('fk_i_pack_id', $pack_id);
  $this->dao->where('fk_c_locale_code', $locale);

  $result = $this->dao->get();
  
  if($result) {
    $row = $result->row();
    return $row;
  }
  
  return array();
}


// GET USER GROUP LOCALE
public function getUserGroupLocale($group_id, $locale = '') {
  if($locale == '') {
    $locale = osc_current_user_locale();
  }

  $this->dao->select();
  $this->dao->from($this->getTable_user_group_locale());
  $this->dao->where('fk_i_group_id', $group_id);
  $this->dao->where('fk_c_locale_code', $locale);

  $result = $this->dao->get();
  
  if($result) {
    $row = $result->row();
    return $row;
  }
  
  return array();
}


// CHECK IF THERE EXISTS CUSTOM TEXT FOR ANY GROUP
public function checkGroupCustom() {
  $locale = osc_current_user_locale();

  $this->dao->select('fk_i_group_id');
  $this->dao->from($this->getTable_user_group_locale());
  $this->dao->where('s_custom <> ""');
  $this->dao->where('fk_c_locale_code', $locale);

  $result = $this->dao->get();
  
  if($result) {
    $row = $result->row();
    if(isset($row['fk_i_group_id']) && $row['fk_i_group_id'] <> '' && $row['fk_i_group_id'] > 0) {
      return true;
    }
  }
  
  return false;
}


// CHECK IF THERE EXISTS PACK SPECIAL FOR ANY GROUP
public function checkGroupPacks() {
  $this->dao->select('pk_i_id');
  $this->dao->from($this->getTable_pack());
  $this->dao->where('i_group <> 0');

  $result = $this->dao->get();
  
  if($result) {
    $row = $result->row();
    if(isset($row['pk_i_id']) && $row['pk_i_id'] <> '' && $row['pk_i_id'] > 0) {
      return true;
    }
  }
  
  return false;
}


// CHECK IF THERE EXISTS FLAT DISCOUNT FOR ANY GROUP
public function checkGroupDiscount() {
  $this->dao->select('pk_i_id');
  $this->dao->from($this->getTable_user_group());
  $this->dao->where('i_discount <> 0');

  $result = $this->dao->get();
  
  if($result) {
    $row = $result->row();
    if(isset($row['pk_i_id']) && $row['pk_i_id'] <> '' && $row['pk_i_id'] > 0) {
      return true;
    }
  }
  
  return false;
}



// CHECK IF THERE EXISTS BONUS FOR PERIODICAL CREDITS FOR ANY GROUP
public function checkGroupBonus() {
  $this->dao->select('pk_i_id');
  $this->dao->from($this->getTable_user_group());
  $this->dao->where('i_pbonus <> 0');

  $result = $this->dao->get();
  
  if($result) {
    $row = $result->row();
    if(isset($row['pk_i_id']) && $row['pk_i_id'] <> '' && $row['pk_i_id'] > 0) {
      return true;
    }
  }
  
  return false;
}


// CHECK IF THERE EXISTS EXCLUSIVE CATEGORY ACCESS FOR ANY GROUP
public function checkGroupCategory() {
  $this->dao->select('pk_i_id');
  $this->dao->from($this->getTable_user_group());
  $this->dao->where('s_category <> ""');

  $result = $this->dao->get();
  
  if($result) {
    $row = $result->row();
    if(isset($row['pk_i_id']) && $row['pk_i_id'] <> '' && $row['pk_i_id'] > 0) {
      return true;
    }
  }
  
  return false;
}


// GET PACK
public function getPack($id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_pack());
  $this->dao->where('pk_i_id', $id);
 
  $result = $this->dao->get();
  
  if($result) {
    $row = $result->row();
    $row['s_name'] = osp_locale($row, 's_name');
    $row['s_description'] = osp_locale($row, 's_description');

    return $row;
  }
  
  return false;
}


// GET PACK BY NAME
public function getPackByName($name) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_pack());
  $this->dao->where('lower(s_name) like "%' . $name . '%"');
 
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return false;
}


// INSERT PACK IF DOES NOT EXIST, OTHERWISE UPDATE
public function updatePack($id, $name, $desc, $price, $bonus, $group, $color) {
  $value = array('s_name' => $name, 's_description' => $desc, 'f_price' => $price, 'f_extra' => $bonus, 'i_group' => $group, 's_color' => $color);
  $where = array('pk_i_id' => $id);
  //$check = $this->getPackByName($name);

  if($id > 0 && $this->getPack($id)) {
    $this->dao->update($this->getTable_pack(), $value, $where);
  } else {
    //if(isset($check['pk_i_id']) && $check['pk_i_id'] > 0) { return false; }

    $this->dao->insert($this->getTable_pack(), $value);  
    $id = $this->dao->insertedId();
  }


  // Update locale
  $locale = Params::getParam('ospLocale');
  $this->updatePackLocale($id, $locale, $name, $desc);

  return true;
}


// REMOVE PACK
public function deletePack($pack_id) {
  return $this->dao->delete($this->getTable_pack(), array('pk_i_id' => $pack_id));
}



// GET ALL USER GROUPS
public function getGroups($is_admin = false) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_user_group());
  
  $result = $this->dao->get();

  if($result) {
    $output = array();
    $data = $result->result();

    if(count($data) > 0) {
      foreach($data as $d) {
        $row = $d;

        $row['locale'] = $this->getUserGroupLocale($d['pk_i_id'], Params::getParam('ospLocale'));

        $row['s_name'] = osp_locale($row, 's_name', $is_admin);
        $row['s_description'] = osp_locale($row, 's_description', $is_admin);
        $row['s_custom'] = osp_locale($row, 's_custom', $is_admin);
        $output[] = $row;
      }
    }

    return $output;
  }
  
  return array();
}


// GET GROUP
public function getGroup($id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_user_group());
  $this->dao->where('pk_i_id', $id);
 
  $result = $this->dao->get();
  
  if($result) {
    $row = $result->row();
    $row['locale'] = $this->getUserGroupLocale($id, osc_current_user_locale());
    $row['s_name'] = osp_locale($row, 's_name');
    $row['s_description'] = osp_locale($row, 's_description');
    $row['s_custom'] = osp_locale($row, 's_custom');

    return $row;
  }
  
  return false;
}


// GET GROUP BY NAME
public function getGroupByName($name) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_user_group());
  $this->dao->where('lower(s_name) like "%' . $name . '%"');
 
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return false;
}



// INSERT GROUP IF DOES NOT EXIST, OTHERWISE UPDATE
public function updateGroup($id, $name, $desc, $price, $discount, $days, $color, $category, $pbonus, $custom, $rank, $attr, $max_items, $max_items_days, $free_items_101 = 0, $free_items_201 = 0, $free_items_401 = 0) {
  $value = array(
    's_name' => $name, 
    's_description' => $desc, 
    'f_price' => $price, 
    'i_discount' => $discount, 
    'i_days' => $days, 
    's_color' => $color, 
    's_category' => $category, 
    'i_pbonus' => $pbonus, 
    's_custom' => $custom, 
    'i_rank' => $rank, 
    'i_attr' => $attr, 
    'i_max_items' => $max_items, 
    'i_max_items_days' => $max_items_days,
    'i_free_items_101' => $free_items_101,
    'i_free_items_201' => $free_items_201,
    'i_free_items_401' => $free_items_401
  );
  
  $where = array('pk_i_id' => $id);
  //$check = $this->getGroupByName($name);

  if($id > 0 && $this->getGroup($id)) {
    $this->dao->update($this->getTable_user_group(), $value, $where);
  } else {
    //if(isset($check['pk_i_id']) && $check['pk_i_id'] > 0) { return false; }

    $this->dao->insert($this->getTable_user_group(), $value); 
    $id = $this->dao->insertedId();
  }


  // Update locale
  $locale = Params::getParam('ospLocale');
  $this->updateUserGroupLocale($id, $locale, $name, $desc, $custom);

  return true;
}


// REMOVE GROUP
public function deleteGroup($group_id) {
  return $this->dao->delete($this->getTable_user_group(), array('pk_i_id' => $group_id));
}


// GET USER GROUP
public function getUserGroup($user_id = '') {
  if($user_id == '' || $user_id <= 0) {
    $user_id = osc_logged_user_id();
  }
  
  if($user_id == '' || $user_id <= 0) {
    return 0;
  }

  $this->dao->select('g.fk_i_group_id');
  $this->dao->from($this->getTable_user_to_group() . ' as g');
  $this->dao->where('g.fk_i_user_id', $user_id);
  
  $result = $this->dao->get();
  
  if($result) {
    $group = $result->row();

    if(isset($group['fk_i_group_id']) && $group['fk_i_group_id'] > 0) {
      return $group['fk_i_group_id'];
    } else {
      return 0;
    }
  } else {
    return 0;
  }
}


// GET USER GROUP RECORD
public function getUserGroupRecord($user_id = '') {
  if($user_id == '' || $user_id <= 0) {
    $user_id = osc_logged_user_id();
  }

  $this->dao->select('ug.*, g.*');
  $this->dao->from($this->getTable_user_to_group() . ' ug,' . $this->getTable_user_group() . ' g');
  $this->dao->where('ug.fk_i_user_id', $user_id);
  $this->dao->where('ug.fk_i_group_id = g.pk_i_id');
  
  $result = $this->dao->get();
  
  if($result) {
    $row = $result->row();
    $row['s_name'] = osp_locale($row, 's_name');
    $row['s_description'] = osp_locale($row, 's_description');
    $row['s_custom'] = osp_locale($row, 's_custom');

    return $row;
  }

  return array();
}


// INSERT USER TO GROUP IF DOES NOT EXIST, OTHERWISE UPDATE
public function updateUserGroup($user_id, $group_id, $expire = NULL) {
  if($expire == '') {
     $group = $this->getGroup($group_id);
     if($group['i_days'] <> '' && $group['i_days'] > 0) {
       $expire = date('Y-m-d H:i:s', strtotime(' + ' . $group['i_days'] . ' days', time()));
     } else {
       $expire = date('Y-m-d H:i:s', strtotime(' + 30 days', time()));
     }
  }

  $value = array('fk_i_user_id' => $user_id, 'fk_i_group_id' => $group_id, 'dt_expire' => $expire);
  $where = array('fk_i_user_id' => $user_id);
  $check = $this->getUserGroupRecord($user_id);
  
  
  // Run before real update of user group record happen, so we can reconcile old limit vs new limit
  $this->deactivateUserItemsAboveLimit($user_id, '', $group_id);     // should make sense just for update

  if($user_id > 0 && isset($check['fk_i_group_id'])) {
    $this->dao->update($this->getTable_user_to_group(), $value, $where);
  } else {
    $this->dao->insert($this->getTable_user_to_group(), $value);  
  }
}



// REMOVE GROUP
public function deleteUserGroup($user_id) {
  return $this->dao->delete($this->getTable_user_to_group(), array('fk_i_user_id' => $user_id));
}


// LIST USERS IN GROUP
public function getUsersByGroup($group_id) {
  $this->dao->select('u.pk_i_id as user_id, u.s_name as user_name, u.s_email as user_email, ug.dt_expire as expire, g.pk_i_id as group_id, g.s_name as group_name, g.s_color as group_color');
  $this->dao->from($this->getTable_user_group() . ' g, ' . $this->getTable_user_to_group() . ' ug, ' . $this->getTable_user() . ' u');
  $this->dao->where('g.pk_i_id', $group_id);
  $this->dao->where('g.pk_i_id = ug.fk_i_group_id');
  $this->dao->where('ug.fk_i_user_id = u.pk_i_id');
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->result();
  }

  return array();
}


// GET USER CART
public function getCart($user_id = '') {
  if($user_id == '' || $user_id == 0) {
    $user_id = osc_logged_user_id();
  }

  $this->dao->select('s_content');
  $this->dao->from($this->getTable_user_cart());
  $this->dao->where('fk_i_user_id', $user_id);
  
  $result = $this->dao->get();
  
  if($result) {
    $cart = $result->row();
    return isset($cart['s_content']) ? $cart['s_content'] : '';
  }
  
  return '';
}



// GET ORDER ITEM
public function getOrderItem($id) {
  $this->dao->select();
  $this->dao->from($this->getTable_order_item());
  $this->dao->where('pk_i_id', $id);
  
  $result = $this->dao->get();
  
  if($result) {
    $data = $result->row();
    
    if(isset($data['pk_i_id'])) {
      return $data;
    }
  }
  
  return false;
}


// GET ORDER ITEMS
public function getOrderItems($order_id, $status = NULL, $seller = NULL) {
  $this->dao->select();
  $this->dao->from($this->getTable_order_item());
  $this->dao->where('fk_i_order_id', $order_id);
  
  if($status !== NULL) {
    $this->dao->where('i_status', $status);
  }
  
  if($seller !== NULL) {
    $this->dao->where('fk_i_user_id', $seller);
  }
  
  
  $result = $this->dao->get();
  
  if($result) {
    $data = $result->result();
    return $data;
  }
  
  return array();
}


// INSERT PENDING
public function insertPending($data) {
  $this->deletePendingByUserId($data['fk_i_user_id'], $data['s_source']);   // remove historical for this user and gateway
  
  $this->dao->insert($this->getTable_pending(), $data);
  return $this->dao->insertedId();
}


// INSERT ORDER ITEM
public function insertOrderItem($data) {
  $this->dao->insert($this->getTable_order_item(), $data);
  return $this->dao->insertedId();
}


// UPDATE ORDER ITEM
public function updateOrderItem($id, $data) {
  $this->dao->update($this->getTable_order_item(), $data, array('pk_i_id' => $id));
}


// DELETE PENDING
public function deletePending($id) {
  return $this->dao->delete($this->getTable_pending(), array('pk_i_id' => $id));
}


// DELETE PENDING BY USER ID
public function deletePendingByUserId($user_id, $source) {
  if($user_id > 0) {
    $query = "DELETE FROM {$this->getTable_pending()} WHERE fk_i_user_id = {$user_id} AND s_source = '{$source}' and dt_date < (NOW() - INTERVAL 1 DAY)";
    $this->dao->query($query);

    //return $this->dao->delete($this->getTable_pending(), array('fk_i_user_id' => $user_id, 's_source' => $source));
  }
}


// UPDATE PENDING TRANSACTION ID
public function updatePendingTransaction($id, $transaction_id) {
  return $this->dao->update($this->getTable_pending(), array('s_transaction_id' => $transaction_id), array('pk_i_id' => $id));
}


// GET PENDING BY PRIMARY ID
public function getPendingById($id) {
  $this->dao->select();
  $this->dao->from($this->getTable_pending());
  $this->dao->where('pk_i_id', (int)$id);
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return false;
}


// GET PENDING BY PRIMARY ID
public function getPendingByExtra($extra, $user_id, $email, $source) {
  $this->dao->select();
  $this->dao->from($this->getTable_pending());
  $this->dao->where('s_extra', $extra);
  $this->dao->where('fk_i_user_id', $user_id);
  $this->dao->where('s_email', $email);
  $this->dao->where('s_source', $source);
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return false;
}




// GET PENDING BY TRANSACTION ID
public function getPendingByTransactionId($transaction_id, $source = '') {
  $this->dao->select();
  $this->dao->from($this->getTable_pending());
  $this->dao->where('s_transaction_id', $transaction_id);

  if($source <> '') {
    $this->dao->where('s_source', $source);
  }

  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return false;
}




// INSERT USER CART, IF EXIST UPDATE
public function updateCart($user_id = '', $content = '') {
  if($user_id == '' || $user_id == 0) {
    $user_id = osc_logged_user_id();
  }

  return $this->dao->replace($this->getTable_user_cart(), array('fk_i_user_id' => $user_id, 's_content' => $content));
}


// DELETE USER CART
public function deleteCart($user_id) {
  return $this->dao->delete($this->getTable_user_cart(), array('fk_i_user_id' => $user_id));
}


// SAVE PAYMENT LOG
public function saveLog($concept, $code, $amount, $currency, $email, $user, $cart, $product_type, $source) {
  $this->dao->insert($this->getTable_log(), array(
    's_concept' => $concept,
    'dt_date' => date("Y-m-d H:i:s"),
    's_code' => $code,
    'i_amount' => $amount*1000000000000,
    's_currency_code' => $currency,
    's_email' => $email,
    'fk_i_user_id' => $user,
    's_cart' => $cart,
    'i_product_type' => $product_type,
    's_source' => $source
  ));

  $payment_id = $this->dao->insertedId();

  osc_run_hook('osp_log_saved', $payment_id);
  
  return $payment_id;
}


// GET PAYMENT LOGS
public function getLogs($type = -1, $params = array(), $only_count = false) {
  $selector = 'DISTINCT *';
  
  if($only_count === true) {
    $selector = 'count(DISTINCT pk_i_id) as i_count';
  }
  
  $this->dao->select($selector);
  $this->dao->from($this->getTable_log());

  if($type <> -1) {
    $this->dao->where('i_product_type', $type);
  }
  
  
  if(isset($params['id']) && $params['id'] !== '') {
    $this->dao->like('pk_i_id', $params['id']);
  }
  
  if(isset($params['concept']) && $params['concept'] !== '') {
    $this->dao->like('s_concept', $params['concept']);
  }
  
  if(isset($params['date']) && $params['date'] !== '') {
    $this->dao->like('dt_date', $params['date']);
  }
  
  if(isset($params['code']) && $params['code'] !== '') {
    $this->dao->like('s_code', $params['code']);
  }
  
  if(isset($params['user']) && $params['user'] !== '') {
    $this->dao->like('concat(fk_i_user_id, s_email)', $params['user']);
  }
  
  if(isset($params['source']) && $params['source'] !== '') {
    $this->dao->like('s_source', $params['source']);
  }
  
  if(isset($params['type']) && $params['type'] !== '') {
    $this->dao->like('i_product_type', $params['type']);
  }
  
  
  
  if($only_count !== true) {
    // $limit[0] == page; $limit[1] == limit
    $page = intval(isset($params['pageId']) ? $params['pageId'] : 0);
    $per_page = intval(isset($params['per_page']) ? ($params['per_page'] > 0 ? $params['per_page'] : 25) : 25);
    
    if($page > 0) {
      $this->dao->limit(($page-1)*$per_page, $per_page);
    } else if($per_page > 0) {
      $this->dao->limit($per_page);
    }
    
    if(!isset($params['sort']) || $params['sort'] == '' || $params['sort'] == 'DESC') {
      $this->dao->orderby('pk_i_id', 'DESC');
    } else if ($params['sort'] == 'ASC') {
      $this->dao->orderby('pk_i_id', 'ASC');
    }
  }


  $result = $this->dao->get();
  
  if($result) {
    if($only_count === true) {
      $data = $result->row();
      return isset($data['i_count']) ? $data['i_count'] : 0;
    } else {
      return $result->result();
    }
  }

  return ($only_count ? 0 : array());
}



// GET ACTIVE USERS
public function getUsers() {
  $this->dao->select('*');
  $this->dao->from($this->getTable_user());
  $this->dao->where('b_active', 1);
  $this->dao->where('b_enabled', 1);

  $result = $this->dao->get();
  
  if($result) {
    return $result->result();
  }
  
  return array();
}



// CREATE BANK TRANSFER
public function createBankTransfer($variable, $cart, $description, $price, $user_id, $extra = '') {
  $transaction = 'bt_' . mb_generate_rand_string(8);

  $this->dao->insert($this->getTable_transfer(), array(
    'i_user_id' => $user_id,
    's_transaction' => $transaction,
    's_variable' => $variable,
    's_cart' => $cart,
    's_description' => $description,
    's_extra' => $extra,
    'i_paid' => 0,
    'f_price' => $price,
    'dt_date' => date("Y-m-d H:i:s")
  ));
  
  return $transaction;
}


// GET ALL TRANSFERS
public function getBankTransfers($paid = -1) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_transfer());

  if($paid <> -1) {
    $this->dao->where('i_paid', $paid);
  }

  $this->dao->orderby('i_paid ASC, dt_date DESC');
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->result();
  }
  
  return array();
}


// GET BANK TRANSFERS
public function getBankTransferById($id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_transfer());

  $this->dao->where('pk_i_id', (int)$id);
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return array();
}


// GET VOUCHER BY ID
public function getVoucher($id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_voucher());

  $this->dao->where('pk_i_id', $id);
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return array();
}


// GET BOOKING RESERVATION BY ID
public function getBooking($id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_booking());

  $this->dao->where('pk_i_id', $id);
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return false;
}


// UPDATE BOOKING/RESERVATION AS PAID
public function updateBookingPaid($id) {
  $this->dao->update($this->getTable_booking(), array('b_paid' => 1), array('pk_i_id' => $id));
}



// GET VOUCHER BY CODE
public function getVoucherByCode($code) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_voucher());

  $this->dao->where('s_code', $code);
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return array();
}


// UPDATE VOUCHER USAGE
public function updateVoucherUsage($id, $value) {
  $this->dao->query('UPDATE '.$this->getTable_voucher().' SET i_quantity_used = i_quantity_used + ' . $value . ' WHERE pk_i_id=' . $id);
  $this->dao->insert($this->getTable_voucher_stats(), array('fk_i_voucher_id' => $id, 'fk_i_user_id' => osc_logged_user_id(), 'dt_datetime' => date('Y-m-d H:i:s')));
}



// GET VOUCHER STATS
public function getVoucherStats($voucher_id, $user_id) {
  $this->dao->select('count(*) as i_count');
  $this->dao->from($this->getTable_voucher_stats());

  $this->dao->where('fk_i_voucher_id', $voucher_id);
  $this->dao->where('fk_i_user_id', $user_id);
  
  $result = $this->dao->get();
  
  if($result) {
    return @$result->row()['i_count'];
  }
  
  return 0;
}




// GET BANK TRANSFERS
public function getBankTransferByTransactionId($transaction_id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_transfer());

  $this->dao->where('s_transaction', $transaction_id);
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return array();
}


// GET USER PENDING BANK TRANSFERS
public function getBankTransferByUserId($user_id, $status = 0) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_transfer());

  $this->dao->where('i_paid', $status);
  $this->dao->where('i_user_id', $user_id);
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->result();
  }
  
  return array();
}


// UPDATE TRANSFER STATUS
public function updateBankTransfer($id, $status) {
  $where = array('pk_i_id' => $id);

  if($status == 1) {
    $value = array('i_paid' => $status, 'dt_date_paid' => date("Y-m-d H:i:s"));
  } else {
    $value = array('i_paid' => $status);
  }

  return $this->dao->update($this->getTable_transfer(), $value, $where);
}


// DELETE BANK TRANSFER
public function deleteBankTransfer($id) {
  return $this->dao->delete($this->getTable_transfer(), array('pk_i_id' => $id));
}


// INSERT BANNER
public function insertBanner($user_id, $group_id, $type, $name, $key, $url, $code, $price_view, $price_click, $budget, $category, $width, $height) {
  $value = array(
    'fk_i_user_id' => $user_id,
    'fk_s_banner_id' => $group_id,
    'i_type' => $type,
    's_name' => $name,
    's_key' => $key,
    's_url' => $url,
    's_code' => $code,
    'd_price_view' => $price_view,
    'd_price_click' => $price_click,
    'd_budget' => $budget,
    's_category' => $category,
    's_size_width' => $width,
    's_size_height' => $height
  );

  return $this->dao->insert($this->getTable_banner(), $value);
}


// UPDATE BANNER
public function updateBanner($id, $user_id, $group_id, $type, $name, $key, $url, $code, $price_view, $price_click, $budget, $category, $width, $height, $comment, $advert_id, $status) {
  $value = array(
    'fk_i_user_id' => $user_id,
    'fk_s_banner_id' => $banner_id,
    'i_type' => $type,
    's_name' => $name,
    's_key' => $key,
    's_url' => $url,
    's_code' => $code,
    'd_price_view' => $price_view,
    'd_price_click' => $price_click,
    'd_budget' => $budget,
    's_category' => $category,
    's_size_width' => $width,
    's_size_height' => $height,
    's_comment' => $comment,
    'i_ba_advert_id ' => $advert_id,
    'i_status' => $status
  ); 

  $where = array('pk_i_id' => $id);

  return $this->dao->update($this->getTable_banner(), $value, $where);
}


// DELETE BANNER
public function deleteBanner($id) {
  $remove_banner = ModelOSP::newInstance()->getBanner($id);

  if($remove_banner['i_ba_advert_id'] <> '' && osp_plugin_ready('banner_ads')) {
    ModelBA::newInstance()->removeAdvert($remove_banner['i_ba_advert_id']);    // remove from Banner Ads Plugin
  }

  return $this->dao->delete($this->getTable_banner(), array('pk_i_id' => $id));
}


// GET BANNER
public function getBanner($id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_banner());
  $this->dao->where('pk_i_id', $id);
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return array();
}


// GET BANNERS
public function getBanners($status = -1, $user_id = -1) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_banner());

  if($status <> -1) {
    $this->dao->where('i_status', $status);
  }

  if($user_id <> -1) {
    $this->dao->where('fk_i_user_id', $user_id);
  }

  $this->dao->orderby('i_status ASC, pk_i_id DESC');
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->result();
  }
  
  return array();
}


// UPDATE BANNER STATUS
public function updateBannerStatus($id, $status, $comment = '') {
  $where = array('pk_i_id' => $id);

  if($comment == '') {
    $value = array('i_status' => $status);
  } else {
    $value = array('i_status' => $status, 's_comment' => $comment);
  }

  return $this->dao->update($this->getTable_banner(), $value, $where);
}


// UPDATE ID OF ADVERT IN BANNER ADS PLUGIN
public function updateBannerAdvertId($id, $advert_id) {
  $where = array('pk_i_id' => $id);
  $value = array('i_ba_advert_id' => $advert_id);

  return $this->dao->update($this->getTable_banner(), $value, $where);
}




// CURRENCY RATES
public function getCurrencies() {
  $this->dao->select('pk_c_code');
  $this->dao->from(sprintf('%st_currency', DB_TABLE_PREFIX));
  $result = $this->dao->get();

  if($result) {
    return $result->result();
  }

  return array();
}


// GET CURRENCY RATE ROW
public function getRate($currency) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_rate());
  $this->dao->where('s_from', $currency);
  $this->dao->where('s_to', osp_currency());
  $result = $this->dao->get();
  
  if($result) {
    $row = $result->row();
    return isset($row['f_rate']) ? $row['f_rate'] : 1.0;
  }
  
  return 1.0;
}


// UPDATE CURRENCY RATES
public function replaceCurrency($from, $to, $rate) {
  return $this->dao->replace(
    $this->getTable_rate(),
    array(
      's_from' => $from,
      's_to' => $to,
      'f_rate' => $rate,
      'dt_date' => date('Y-m-d H:i:s')
    )
  );
}


// GET ITEM DATA
public function getItemData($item_id) {
  if($item_id <= 0) {
    return array();
  }
  
  $this->dao->select('*');
  $this->dao->from($this->getTable_item_data());
  $this->dao->where('fk_i_item_id', $item_id);
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return array();
}


// GET SHIPPING
public function getShipping($id, $user_id = NULL) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_shipping());
  $this->dao->where('pk_i_id', $id);
  
  if($user_id > 0) {
    $this->dao->where('fk_i_user_id', $user_id);
  }
  
  $result = $this->dao->get();
  
  if($result) {
    $data = $result->row();
    
    if(isset($data['pk_i_id'])) {
      return $data;
    }
  }
  
  return false;
}


// GET USER SHIPPINGS
public function getUserShippings($user_id, $country = NULL, $enabled = NULL) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_shipping());
  $this->dao->where('fk_i_user_id', $user_id);
  $this->dao->where('f_fee > 0');

  if($country != '') {
    $this->dao->where('(fk_c_country_code = "' . $country . '" or coalesce(fk_c_country_code, "") = "")');
  }
  
  if($enabled == 1) {
    $this->dao->where('i_status', 1);
  }
  
  $this->dao->orderby('i_speed', 'ASC');
  
  $result = $this->dao->get();
  
  if($result) {
    $data = $result->result();
    return $data;
  }
  
  return array();
}


// GET ALL SHIPPINGS
public function getShippings($params = array(), $only_count = false) {
  $selector = 'DISTINCT *';
  
  if($only_count === true) {
    $selector = 'count(DISTINCT pk_i_id) as i_count';
  }
  
  $this->dao->select($selector);
  $this->dao->from($this->getTable_shipping());

  if(isset($params['id']) && $params['id'] !== '') {
    $this->dao->like('pk_i_id', $params['id']);
  }
  
  if(isset($params['name']) && $params['name'] !== '') {
    $this->dao->like('concat(s_name, s_description, s_delivery)', $params['name']);
  }
  
  
  if($only_count !== true) {
    // $limit[0] == page; $limit[1] == limit
    $page = intval(isset($params['pageId']) ? $params['pageId'] : 0);
    $per_page = intval(isset($params['per_page']) ? ($params['per_page'] > 0 ? $params['per_page'] : 25) : 25);
    
    if($page > 0) {
      $this->dao->limit(($page-1)*$per_page, $per_page);
    } else if($per_page > 0) {
      $this->dao->limit($per_page);
    }
    
    if(!isset($params['sort']) || $params['sort'] == '' || $params['sort'] == 'DESC') {
      $this->dao->orderby('pk_i_id', 'DESC');
    } else if ($params['sort'] == 'ASC') {
      $this->dao->orderby('pk_i_id', 'ASC');
    }
  }


  $result = $this->dao->get();
  
  if($result) {
    if($only_count === true) {
      $data = $result->row();
      return isset($data['i_count']) ? $data['i_count'] : 0;
    } else {
      return $result->result();
    }
  }

  return ($only_count ? 0 : array());
}


// INSERT SHIPPING
public function insertShipping($data) {
  $this->dao->insert($this->getTable_shipping(), $data);
}


// UPDATE SHIPPING
public function updateShipping($id, $data) {
  $this->dao->update($this->getTable_shipping(), $data, array('pk_i_id' => $id));
}


// REMOVE SHIPPING
public function deleteShipping($id) {
  return $this->dao->delete($this->getTable_shipping(), array('pk_i_id' => $id));
}



// UPDATE ITEM QUANTITY
public function updateItemQuantity($item_id, $quantity) {
  $this->dao->query('INSERT INTO ' . $this->getTable_item_data() . ' (fk_i_item_id, i_sell, i_quantity) VALUES (' . $item_id . ', 0, 0) ON DUPLICATE KEY UPDATE i_quantity = i_quantity + ' . $quantity);
  $this->updateVeronikaSold($item_id);
}

// UPDATE ITEM SELL FIELD
public function updateItemData2($data, $is_hook = 0) {
  $this->dao->replace($this->getTable_item_data(), $data);

  if($is_hook <> 1) {
    $this->updateVeronikaSold($data['fk_i_item_id']);
  }
}


// UPDATE ITEM SELL FIELD
public function updateItemData($item_id, $sell, $quantity, $is_hook = 0) {
  $value = array('fk_i_item_id' => $item_id, 'i_sell' => $sell, 'i_quantity' => $quantity);
  $this->dao->replace($this->getTable_item_data(), $value);

  if($is_hook <> 1) {
    $this->updateVeronikaSold($item_id);
  }
}


// UPDATE VERONIKA SOLD MARK
public function updateVeronikaSold($item_id) {
  if(function_exists('veronika_check_sold') && osp_param('stock_management') == 1) {
    $item = $this->getItemData($item_id);

    if(@$item['i_quantity'] <= 0) {
      $this->dao->update(DB_TABLE_PREFIX.'t_item_veronika', array('i_sold' => 1), array('fk_i_item_id' => $item_id));
    } else {
      $this->dao->update(DB_TABLE_PREFIX.'t_item_veronika', array('i_sold' => 0), array('fk_i_item_id' => $item_id));
    }
  }
}


// REMOVE ITEM DATA
public function deleteItemData($item_id) {
  return $this->dao->delete($this->getTable_item_data(), array('fk_i_item_id' => $item_id));
}


// GET ORDER BY PAYMENT ID
public function getOrderByPayment($payment_id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_order());
  $this->dao->where('fk_i_payment_id', $payment_id);
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return array();
}


// GET ORDER BY ID
public function getOrder($id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_order());
  $this->dao->where('pk_i_id', $id);
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->row();
  }
  
  return array();
}


// GET ALL ORDERS
public function getOrders($status = -1, $user_id = -1) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_order());
  
  if($status <> -1) {
    $this->dao->where('i_status', $status);
  }

  if($user_id <> -1) {
    $this->dao->where('fk_i_user_id', $user_id);
  }

  $this->dao->orderby('pk_i_id DESC');

  $result = $this->dao->get();
  
  if($result) {
    return $result->result();
  }
  
  return array();
}



// GET ORDERS - V2
public function getOrders2($params, $only_count = false) {
  $selector = 'DISTINCT o.*';
  
  if($only_count === true) {
    $selector = 'count(DISTINCT o.pk_i_id) as i_count';
  }
  
  $this->dao->select($selector);
  $this->dao->from($this->getTable_order() . ' as o');
  $this->dao->join($this->getTable_user() . ' as u', 'o.fk_i_user_id = u.pk_i_id', 'LEFT OUTER');
  $this->dao->join($this->getTable_order_item() . ' as oi', 'o.pk_i_id = oi.fk_i_order_id', 'LEFT OUTER');

  
  if(isset($params['status']) && $params['status'] !== '') {
    $this->dao->where('o.i_status', $params['status']);
  }
  
  if(isset($params['currency']) && $params['currency'] !== '') {
    $this->dao->where('o.s_currency_code', $params['currency']);
  }
  
  if(isset($params['payment']) && $params['payment'] !== '') {
    $this->dao->join( $this->getTable_log() . ' as p', 'o.fk_i_payment_id = p.pk_i_id', 'LEFT OUTER');
    $this->dao->like('concat(convert(coalesce(o.fk_i_payment_id, 0), char), p.s_concept, p.s_code, p.s_cart, p.s_source, p.i_product_type)', $params['payment']);
  }
  
  if(isset($params['id']) && $params['id'] !== '') {
    $this->dao->like('o.pk_i_id', $params['id']);
  }
  
  if(isset($params['user']) && $params['user'] !== '') {
    $this->dao->join( $this->getTable_user() . ' as ou', 'oi.fk_i_user_id = ou.pk_i_id', 'LEFT OUTER');
    $this->dao->join( $this->getTable_item() . ' as i', 'oi.fk_i_item_id = i.pk_i_id', 'LEFT OUTER');
    $this->dao->like('concat(convert(coalesce(o.fk_i_user_id, 0), char), coalesce(ou.s_name, ""), coalesce(ou.s_email, ""), coalesce(u.s_name, ""), coalesce(u.s_email, ""),  coalesce(i.s_contact_name, ""), coalesce(i.s_contact_email, ""))', $params['user']);
  }
  
  if(isset($params['address']) && $params['address'] !== '') {
    $this->dao->like('concat(coalesce(u.fk_c_country_code, ""), coalesce(u.s_country, ""), coalesce(u.s_region, ""), coalesce(u.s_city, ""), coalesce(u.s_zip, ""), coalesce(u.s_address, ""))', $params['address']);
  }
  
  if(isset($params['date']) && $params['date'] !== '') {
    $this->dao->like('o.dt_date', $params['date']);
  }
  
  if(isset($params['comment']) && $params['comment'] !== '') {
    $this->dao->like('concat(o.s_comment, o.s_amount_comment)', $params['comment']);
  }
  
  if(isset($params['amount_min']) && $params['amount_min'] !== '') {
    $this->dao->where('o.f_amount >= ' . floatval($params['amount_min']));
  }

  if(isset($params['amount_max']) && $params['amount_max'] !== '') {
    $this->dao->where('o.f_amount <= ' . floatval($params['amount_max']));
  }
  
  if(isset($params['item']) && $params['item'] !== '') {
    $this->dao->join( $this->getTable_item_description() . ' as d', 'oi.fk_i_item_id = d.fk_i_item_id', 'LEFT OUTER');
    $this->dao->like('concat(o.s_item_id, d.s_title, d.s_description)', $params['item']);
  }
  
  
  
  if($only_count !== true) {
    $this->dao->groupby('o.pk_i_id');

    // $limit[0] == page; $limit[1] == limit
    $page = intval(isset($params['pageId']) ? $params['pageId'] : 0);
    $per_page = intval(isset($params['per_page']) ? ($params['per_page'] > 0 ? $params['per_page'] : 25) : 25);
    
    if($page > 0) {
      $this->dao->limit(($page-1)*$per_page, $per_page);
    } else if($per_page > 0) {
      $this->dao->limit($per_page);
    }
    
    if(!isset($params['sort']) || $params['sort'] == '' || $params['sort'] == 'DESC') {
      $this->dao->orderby('o.pk_i_id', 'DESC');
    } else if ($params['sort'] == 'ASC') {
      $this->dao->orderby('o.pk_i_id', 'ASC');
    }
  }


  $result = $this->dao->get();
  
  if($result) {
    if($only_count === true) {
      $data = $result->row();
      return isset($data['i_count']) ? $data['i_count'] : 0;
    } else {
      $data = $result->result();
      $output = array();
      
      if(is_array($data) && count($data) > 0) {
        foreach($data as $d) {
          $output[$d['pk_i_id']] = $d;
          $output[$d['pk_i_id']]['order_items'] = $this->getOrderItems($d['pk_i_id']);
        }
      }
      
      return $output;
    }
  }

  return ($only_count ? 0 : array());
}



// GET ORDERS - V2
public function getItemDataList2($params, $only_count = false) {
  $selector = 'i.pk_i_id as fk_i_item_id, i.i_price, coalesce(d.i_shipping, 0) as i_shipping, coalesce(d.i_sell, 0) as i_sell, coalesce(d.i_quantity, 0) as i_quantity';

  if($only_count === true) {
    $selector = 'count(DISTINCT i.pk_i_id) as i_count';
  }
  
  $this->dao->select($selector);
  $this->dao->from($this->getTable_item() . ' as i');
  $this->dao->join($this->getTable_item_data() . ' as d', 'i.pk_i_id = d.fk_i_item_id', 'LEFT OUTER');

  
  if(isset($params['item']) && $params['item'] !== '') {
    $this->dao->join($this->getTable_item_description() . ' as e', 'i.pk_i_id = e.fk_i_item_id', 'INNER');
    $this->dao->like('concat(e.s_title, e.s_description)', $params['item']);
  }
  
  if(isset($params['id']) && $params['id'] !== '') {
    $this->dao->like('i.pk_i_id', $params['id']);
  }
  
  if(isset($params['user']) && $params['user'] !== '') {
    $this->dao->join($this->getTable_user() . ' as u', 'i.fk_i_user_id = u.pk_i_id', 'LEFT OUTER');
    $this->dao->like('concat(convert(coalesce(i.fk_i_user_id, 0), char), coalesce(u.s_name, ""), coalesce(u.s_email, ""),  coalesce(i.s_contact_name, ""), coalesce(i.s_contact_email, ""))', $params['user']);
  }
  
  if(isset($params['address']) && $params['address'] !== '') {
    $this->dao->join($this->getTable_item_location() . ' as l', 'i.pk_i_id = l.fk_i_item_id', 'INNER');
    $this->dao->like('concat(coalesce(l.fk_c_country_code, ""), coalesce(l.s_country, ""), coalesce(l.s_region, ""), coalesce(l.s_city, ""), coalesce(l.s_zip, ""), coalesce(l.s_address, ""))', $params['address']);
  }
  
  if(isset($params['date']) && $params['date'] !== '') {
    $this->dao->like('i.dt_pub_date', $params['date']);
  }
  
  
  if($only_count !== true) {
    $this->dao->groupby('i.pk_i_id');

    // $limit[0] == page; $limit[1] == limit
    $page = intval(isset($params['pageId']) ? $params['pageId'] : 0);
    $per_page = intval(isset($params['per_page']) ? ($params['per_page'] > 0 ? $params['per_page'] : 25) : 25);
    
    if($page > 0) {
      $this->dao->limit(($page-1)*$per_page, $per_page);
    } else if($per_page > 0) {
      $this->dao->limit($per_page);
    }
    
    if(!isset($params['sort']) || $params['sort'] == '' || $params['sort'] == 'DESC') {
      $this->dao->orderby('i.pk_i_id', 'DESC');
    } else if ($params['sort'] == 'ASC') {
      $this->dao->orderby('i.pk_i_id', 'ASC');
    }
  }


  $result = $this->dao->get();
  
  if($result) {
    if($only_count === true) {
      $data = $result->row();
      return isset($data['i_count']) ? $data['i_count'] : 0;
    } else {
      $data = $result->result();
      return $data;
    }
  }

  return ($only_count ? 0 : array());
}




// CHECK IF ORDER EXISTS
public function checkOrder($item_id, $user_id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_order());
  $this->dao->where('i_status', 2);
  $this->dao->where('fk_i_user_id', $user_id);
  
  $result = $this->dao->get();
  
  if($result) {
    $orders = $result->result();

    if(count($orders) > 0) {
      foreach($orders as $o) {
        $items = array_filter(explode(',', $o['s_item_id']));
        if(in_array($item_id, $items)) {
          return $o;
        }
      }
    }
  }
  
  return false;
}


// CHECK IF ITEM HAS BEEN PURCHASED BY USER... checkItemOrder
public function checkItemPurchased($item_id, $user_id, $acceptable_status = array(0,1,2)) {
  $this->dao->select('o.*');
  $this->dao->from($this->getTable_order_item() . ' as i, ' . $this->getTable_order() . ' as o');
  $this->dao->where('o.pk_i_id = i.fk_i_order_id');
  $this->dao->where('i.fk_i_item_id', $item_id);
  $this->dao->where('o.fk_i_user_id', $user_id);      // order user id is buyer   ...order item user id is seller
  
  if(is_array($acceptable_status) && count($acceptable_status) > 0) {
    $this->dao->where('o.i_status in (' . implode(',', $acceptable_status) . ')');
  }
  
  $result = $this->dao->get();
  
  if($result) {
    $data = $result->row();
    
    if(isset($data['pk_i_id']) && $data['pk_i_id'] > 0) {
      return $data;
    }
  }
  
  return false;
}


// CREATE ORDER ITEMS
public function generateOrderItems() {
  $this->dao->select();
  $this->dao->from($this->getTable_order());

  $result = $this->dao->get();
  
  if($result) {
    $orders = $result->result();

    if(count($orders) > 0) {
      foreach($orders as $o) {
        if(empty($this->getOrderItems($o['pk_i_id']))) {
          $items = array_filter(explode(',', trim($o['s_item_id'])));
          $cart = array_filter(explode('|', trim($o['s_cart'])));

          if(count($items) > 0) {
            $c = 0;
            
            foreach($items as $i) {
              $item_data = Item::newInstance()->findByPrimaryKey($i);
              $cart_row = explode('x', $cart[$c]);
              $qty = $cart_row[1];
              $item_price_full = $cart_row[3] * $qty;
              $item_price = $cart_row[3] * (1 - $o['i_discount']/100) * $qty;
              $item_discount = $cart_row[3] * ($o['i_discount']/100) * $qty;
              $customer = User::newInstance()->findByPrimaryKey($o['fk_i_user_id']);
            
              $this->insertOrderItem(array(
                'fk_i_order_id' => $o['pk_i_id'],
                'fk_i_user_id' => $item_data['fk_i_user_id'],
                'fk_i_item_id' => $i,
                'i_quantity' => $qty,
                'f_amount' => $item_price,
                'f_amount_regular' => $item_price_full,
                'f_discount' => $item_discount,
                's_amount_comment' => $o['s_amount_comment'],
                's_location' => implode(', ', array_filter(array($customer['s_country'], $customer['s_region'], $customer['s_city'], $customer['s_city_area'], $customer['s_zip'], $customer['s_address']))),
                's_title' => $item_data['s_title'],
                's_currency_code' => $o['s_currency_code'],
                'i_status' => $o['i_status'],
                'dt_last_update' => date('Y-m-d H:i:s'),
                'dt_date' => $o['dt_date']
              ));
              
              $c++;
            }
          }
        }
      }
    }
  }
}


// GET ALL ORDERS
public function getSales($user_id = -1) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_order());
  
  $this->dao->orderby('pk_i_id DESC');

  $result = $this->dao->get();
  
  $sales = array();

  if($result) {
    $orders = $result->result();

    if(count($orders) > 0) {
      foreach($orders as $o) {
        $items = array_filter(explode(',', trim($o['s_item_id'])));
        $cart = array_filter(explode('|', trim($o['s_cart'])));

        if(count($items) > 0) {
          $c = 0;
          foreach($items as $i) {
            if($user_id <> -1 && $user_id > 0) {
              $item_data = Item::newInstance()->findByPrimaryKey($i);

              if($item_data['fk_i_user_id'] == $user_id) {
                $qty = explode('x', $cart[$c])[1];
                $item_price = explode('x', $cart[$c])[3] * (1 - $o['i_discount']/100) * $qty;

                $to_sales = $o;
                $to_sales['s_cart'] = $cart[$c];
                $to_sales['s_item_id'] = $i;

                $sales[$i][] = array_merge($to_sales, array('i_quantity' => $qty), array('f_item_price' => $item_price));
              }
            }

            $c++;
          }
        }
      }
    }
  }
  
  return $sales;
}



// GET USER SALES
public function getUserSales($params, $only_count = false) {
  $selector = 'DISTINCT o.*';
  
  if($only_count === true) {
    $selector = 'count(DISTINCT o.pk_i_id) as i_count';
  }
  
  $this->dao->select($selector);
  $this->dao->from($this->getTable_order() . ' as o');
  $this->dao->join( $this->getTable_order_item() . ' as i', 'o.pk_i_id = i.fk_i_order_id', 'LEFT OUTER');


  // Search params
  if(isset($params['ospKeyword']) && $params['ospKeyword'] !== '') {
    $this->dao->join( $this->getTable_user() . ' as u', 'o.fk_i_user_id = u.pk_i_id', 'LEFT OUTER');
    $this->dao->like('concat(coalesce(u.s_name, ""), coalesce(u.s_email, ""), coalesce(u.s_country, ""), coalesce(u.s_region, ""), coalesce(u.s_city, ""), coalesce(u.s_city_area, ""), coalesce(u.s_zip, ""), coalesce(u.s_address, ""), i.s_title, convert(i.dt_date, char), i.s_currency_code, convert(i.f_amount, char))', $params['ospKeyword']);
  }
  
  if(isset($params['ospStatus']) && $params['ospStatus'] !== '') {
    $this->dao->where('o.i_status', $params['ospStatus']);
  }
  
  
  if(isset($params['user']) && $params['user'] !== '') {
    $this->dao->where('i.fk_i_user_id', $params['user']);
  }
  
  if(isset($params['customer']) && $params['customer'] !== '') {
    $this->dao->where('o.fk_i_user_id', $params['customer']);
  }
  
  if(isset($params['status']) && $params['status'] !== '' && $params['status'] !== 'ALL') {
    $this->dao->where('o.i_status', $params['status']);
  }
  
  
  if($only_count !== true) {
    // $limit[0] == page; $limit[1] == limit
    $page = intval(isset($params['pageId']) ? $params['pageId'] : 0);
    $per_page = intval(isset($params['per_page']) ? ($params['per_page'] > 0 ? $params['per_page'] : 20) : 20);
    
    if($page > 0) {
      $this->dao->limit(($page-1)*$per_page, $per_page);
    } else if($per_page > 0) {
      $this->dao->limit($per_page);
    }
    
    if(!isset($params['sort']) || $params['sort'] == '' || $params['sort'] == 'DESC') {
      $this->dao->orderby('i.fk_i_order_id DESC, i.pk_i_id', 'DESC');
    } else if ($params['sort'] == 'ASC') {
      $this->dao->orderby('i.fk_i_order_id ASC, i.pk_i_id', 'ASC');
    }
  }

  $result = $this->dao->get();
  
  if($result) {
    if($only_count === true) {
      $data = $result->row();
      return isset($data['i_count']) ? $data['i_count'] : 0;
    } else {
      $data = $result->result();
      $output = array();
      
      if(is_array($data) && count($data) > 0) {
        foreach($data as $d) {
          $output[$d['pk_i_id']] = $d;
          $output[$d['pk_i_id']]['order_items'] = $this->getOrderItems($d['pk_i_id'], (@$params['ospStatus'] <> '' ? $params['ospStatus'] : NULL), (@$params['user'] <> '' ? $params['user'] : NULL));
        }
      }
      
      return $output;
    }
  }

  return ($only_count ? 0 : array());
}



// GET ORDERS WITH ORDER ITEMS
public function getOrdersWithItems($params, $only_count = false) {
  $selector = 'DISTINCT o.*, sum(coalesce(d.i_shipping, 0)) as i_shipping_count';
  
  if($only_count === true) {
    $selector = 'count(DISTINCT o.pk_i_id) as i_count';
  }
  
  $this->dao->select($selector);
  $this->dao->from($this->getTable_order() . ' as o');
  $this->dao->join($this->getTable_order_item() . ' as i', 'o.pk_i_id = i.fk_i_order_id', 'LEFT OUTER');
  $this->dao->join($this->getTable_item_data() . ' as d', 'i.fk_i_item_id = d.fk_i_item_id', 'LEFT OUTER');


  // Search params
  if(isset($params['ospKeyword']) && $params['ospKeyword'] !== '') {
    $this->dao->join( $this->getTable_user() . ' as u', 'o.fk_i_user_id = u.pk_i_id', 'LEFT OUTER');
    $this->dao->join( $this->getTable_item_description() . ' as e', 'i.fk_i_item_id = e.fk_i_item_id', 'LEFT OUTER');
    $this->dao->join( $this->getTable_shipping() . ' as h', 'i.fk_i_shipping_id = h.pk_i_id', 'LEFT OUTER');
    $this->dao->like('concat(coalesce(concat("Shipping: ", coalesce(h.s_name, ""), " (", coalesce(h.s_delivery, ""), ")"), ""), coalesce(e.s_title, ""), coalesce(e.s_description, ""), coalesce(u.s_name, ""), coalesce(u.s_email, ""), coalesce(u.s_country, ""), coalesce(u.s_region, ""), coalesce(u.s_city, ""), coalesce(u.s_city_area, ""), coalesce(u.s_zip, ""), coalesce(u.s_address, ""), i.s_title, convert(i.dt_date, char), i.s_currency_code, convert(i.f_amount, char))', $params['ospKeyword']);

    if($only_count !== true) {
      $this->dao->groupby('o.pk_i_id');
    }
  }
  
  if(isset($params['ospStatus']) && $params['ospStatus'] !== '') {
    $this->dao->where('i.i_status', $params['ospStatus']);
  }
  
  
  if(isset($params['user']) && $params['user'] !== '') {
    $this->dao->where('i.fk_i_user_id', $params['user']);
  }
  
  if(isset($params['customer']) && $params['customer'] !== '') {
    $this->dao->where('o.fk_i_user_id', $params['customer']);
  }
  
  if(isset($params['status']) && $params['status'] !== '' && $params['status'] !== 'ALL') {
    $this->dao->where('i.i_status', $params['status']);
  }
  
  
  if($only_count !== true) {
    // $limit[0] == page; $limit[1] == limit
    $page = intval(isset($params['pageId']) ? $params['pageId'] : 0);
    $per_page = intval(isset($params['per_page']) ? ($params['per_page'] > 0 ? $params['per_page'] : 20) : 20);
    
    if($page > 0) {
      $this->dao->limit(($page-1)*$per_page, $per_page);
    } else if($per_page > 0) {
      $this->dao->limit($per_page);
    }
    
    $this->dao->groupby('o.pk_i_id');

    if(!isset($params['sort']) || $params['sort'] == '' || $params['sort'] == 'DESC') {
      $this->dao->orderby('i.fk_i_order_id DESC, i.pk_i_id', 'DESC');
    } else if ($params['sort'] == 'ASC') {
      $this->dao->orderby('i.fk_i_order_id ASC, i.pk_i_id', 'ASC');
    }
  }

  $result = $this->dao->get();
  
  if($result) {
    if($only_count === true) {
      $data = $result->row();
      return isset($data['i_count']) ? $data['i_count'] : 0;
    } else {
      $data = $result->result();
      $output = array();
      
      if(is_array($data) && count($data) > 0) {
        foreach($data as $d) {
          $output[$d['pk_i_id']] = $d;
          $output[$d['pk_i_id']]['order_items'] = $this->getOrderItems($d['pk_i_id'], (@$params['ospStatus'] <> '' ? $params['ospStatus'] : NULL), (@$params['user'] <> '' ? $params['user'] : NULL));
        }
      }
      
      return $output;
    }
  }

  return ($only_count ? 0 : array());
}





// COUNT SALES BY ITEM ID
public function countOrder($item_id) {
  $this->dao->select('*');
  $this->dao->from($this->getTable_order());
  $this->dao->where('i_status', 2);
  $this->dao->where('(s_item_id like "' . $item_id . ',%" OR s_item_id like "%,' . $item_id . '" OR s_item_id like "%,' . $item_id . ',%" OR s_item_id = '. $item_id . ')');
  
  $result = $this->dao->get();
  
  if($result) {
    return count($result->result());
  }
  
  return 0;
}


// CREATE ORDER
public function createOrder($payment_id) {
  if($payment_id == '' || $payment_id < 0) {
    return false;
  }

  $has_product = false;

  $order_check = $this->getOrderByPayment($payment_id);
  if(isset($order_check['fk_i_payment_id']) && $order_check['fk_i_payment_id'] == $payment_id) {
    return false;
  }

  $payment = $this->getPayment($payment_id);

  $order_cart = array();
  $order_items = array();
  $amount = 0;
  $amount_regular = 0;

  $amount_comment = '';
  $user_group = $this->getUserGroupRecord($payment['fk_i_user_id']);

  $discount = 0;
  $discount_perc = 0;
  if(osp_param('selling_apply_membership') == 1 && osp_user_group_discount($payment['fk_i_user_id']) > 0) {
    $amount_comment = sprintf(__('Membership discount %s%% of user group %s has been applied.', 'osclass_pay'), osp_user_group_discount($payment['fk_i_user_id'])*100, $user_group['s_name']);
    $discount = round(osp_user_group_discount($payment['fk_i_user_id'])*100);
    $discount_perc = osp_user_group_discount($payment['fk_i_user_id']);
  }
  
  $cart = explode('|', $payment['s_cart']);

  if(count($cart) > 0) {
    foreach($cart as $product) {
      $p = explode('x', $product);

      if(@$p[0] == OSP_TYPE_PRODUCT || @$p[0] == OSP_TYPE_SHIPPING || @$p[0] == OSP_TYPE_OFFER) {
        // For offers, we have offer ID under $p[2], we need to identify item id
        if($p[0] == OSP_TYPE_OFFER) {
          $offer = ModelMO::newInstance()->getOfferById($p[2]);
          
          if(isset($offer['fk_i_item_id']) && $offer['fk_i_item_id'] > 0) {
            $item_id = $offer['fk_i_item_id'];
            
          } else {
            continue;  // offer is not valid anymore
          }
        } else {
          $item_id = @$p[2];
        }
        

        $base_qty = isset($p[1]) ? (int)$p[1] : 1;

        // NOTE: Here replaced with real-time price calculation
        // $base_price = isset($p[3]) ? (int)$p[3] : 0;
        $base_price = osp_get_fee($p[0], isset($p[1]) ? $p[1] : '', isset($p[2]) ? $p[2] : '', isset($p[3]) ? $p[3] : '', isset($p[4]) ? $p[4] : ''); 
        $amount += $base_price * (1-$discount_perc);
        $amount_regular += $base_price;
        
        $has_product = true;
        $order_cart[] = $product;
        $order_items[] = $item_id;
      }
    }
  }


  $order_string = implode('|', array_filter($order_cart));
  $item_string = implode(',', array_filter($order_items));


  $value = array(
    'fk_i_user_id' => $payment['fk_i_user_id'],
    's_cart' => $order_string,
    's_item_id' => $item_string,
    'f_amount' => round($amount, 2),
    'f_amount_regular' => round($amount_regular, 2),
    's_amount_comment' => $amount_comment,
    'i_discount' => $discount,
    's_currency_code' => $payment['s_currency_code'],
    'i_status' => (osp_param('status_disable') <> 1 ? 0 : 2),
    'dt_date' => $payment['dt_date'],
    'fk_i_payment_id' => $payment_id
  );


  if($has_product) {
    $this->dao->insert($this->getTable_order(), $value);
    $order_id = (int)$this->dao->insertedId();
    
    // CREATE ORDER ITEMS
    $items = array_filter(explode(',', trim($item_string)));
    $cart = array_filter(explode('|', trim($order_string)));

    if(count($items) > 0 && $order_id > 0) {
      $c = 0;
      
      foreach($items as $i) {
        $cart_row = explode('x', $cart[$c]);
        $ship_type = 'STANDARD';
        
        if($cart_row[0] == OSP_TYPE_PRODUCT || $cart_row[0] == OSP_TYPE_OFFER) {
          $item_data = Item::newInstance()->findByPrimaryKey($i);
          
        } else if($cart_row[0] == OSP_TYPE_SHIPPING) {
          $ship_elems = explode('-', $i);
          
          if($ship_elems[0] == 'stn') {
            $ship_type = 'DEFAULT';
            $ship_user_id = $ship_elems[1];
            $ship_user = User::newInstance()->findByPrimaryKey($ship_user_id);
            
            $item_data = array(
              'pk_i_id' => -$ship_user_id,
              'fk_i_user_id' => $ship_user_id,
              's_name' => sprintf(__('Standard shipping from %s', 'osclass_pay'), (@$ship_user['s_name'] <> '' ? $ship_user['s_name'] : sprintf(__('Unknown (#%s)', 'osclass_pay'), $ship_user_id))),
              's_delivery' => ''              
            );
            
          } else {         
            $item_data = ModelOSP::newInstance()->getShipping($i);
          }
        }

        
        $qty = $cart_row[1];

        // $item_price_base = $cart_row[3];
        // $item_price_full = $item_price_base * $qty;

        $item_price_full = osp_get_fee($cart_row[0], isset($cart_row[1]) ? $cart_row[1] : '', isset($cart_row[2]) ? $cart_row[2] : '', isset($cart_row[3]) ? $cart_row[3] : '', isset($cart_row[4]) ? $cart_row[4] : ''); 
        $item_price = $item_price_full * (1 - $discount/100);
        $item_discount = $item_price_full * ($discount/100);
        
        $customer = User::newInstance()->findByPrimaryKey($payment['fk_i_user_id']);
        $customer_location = '';
        
        if($customer !== false && isset($customer['pk_i_id'])) {
          $customer_location = implode(', ', array_filter(array($customer['s_country'], $customer['s_region'], $customer['s_city'], $customer['s_city_area'], $customer['s_zip'], $customer['s_address'])));
        }
        
        if($cart_row[0] == OSP_TYPE_PRODUCT || $cart_row[0] == OSP_TYPE_OFFER) {
          $item_title = $item_data['s_title'];
          
        } else {
          $item_title = $item_data['s_name'];
          $item_title .= ($item_data['s_delivery'] <> '' ? ' (' . $item_data['s_delivery'] . ')' : '');
        }
      
        // Insert into database
        $this->insertOrderItem(array(
          'fk_i_order_id' => $order_id,
          'fk_i_user_id' => $item_data['fk_i_user_id'],
          'fk_i_item_id' => ($cart_row[0] == OSP_TYPE_PRODUCT || $cart_row[0] == OSP_TYPE_OFFER) ? $i : NULL,
          'fk_i_shipping_id' => $cart_row[0] == OSP_TYPE_SHIPPING ? (@$item_data['pk_i_id'] > 0 ? $item_data['pk_i_id'] : $i) : NULL,
          'i_quantity' => $qty,
          'f_amount' => $item_price,
          'f_amount_regular' => $item_price_full,
          'f_discount' => $item_discount,
          's_amount_comment' => $amount_comment,
          's_location' => $customer_location,
          's_title' => $item_title,
          's_currency_code' => $payment['s_currency_code'],
          'i_status' => (osp_param('status_disable') <> 1 ? 0 : 2),
          'dt_last_update' => date('Y-m-d H:i:s'),
          'dt_date' => $payment['dt_date']
        ));
        
        $c++;
      }
    }
    
    return $order_id;
  }
  
  
  // Order not created
  return false;
}


// GET ITEM DATA LIST
public function getItemDataList($type = 1, $page = 0, $per_page = 50, $count = 0) {
  if($count == 1) {
    $this->dao->select('count(*) as i_count');
  } else {
    $this->dao->select('i.pk_i_id as fk_i_item_id, i.i_price, coalesce(d.i_shipping, 0) as i_shipping, coalesce(d.i_sell, 0) as i_sell, coalesce(d.i_quantity, 0) as i_quantity');
  }

  if($type == 1) {
    $join = 'INNER';
  } else {
    $join = 'LEFT OUTER';
  }

  $this->dao->from( $this->getTable_item() . ' as i' );
  $this->dao->join( $this->getTable_item_data() . ' as d', 'i.pk_i_id = d.fk_i_item_id', $join );

  if($count <> 1) {
    if($page > 0) {
      $this->dao->limit($page, $per_page);
    } else {
      $this->dao->limit($per_page);
    }
  }

  $this->dao->orderby('i.pk_i_id DESC');
  
  $result = $this->dao->get();
  
  if($result) {
    return $result->result();
  }
  
  return array();
}



// GET ITEM DATA LIST
public function getUserProducts($params, $only_count = false) {
  $selector = 'DISTINCT i.*, d.*';
  
  if($only_count === true) {
    $selector = 'count(DISTINCT i.pk_i_id) as i_count';
  }
  
  $this->dao->select($selector);
  $this->dao->from($this->getTable_item() . ' as i');
  $this->dao->join($this->getTable_item_data() . ' as d', 'i.pk_i_id = d.fk_i_item_id', 'LEFT OUTER');

  if(osc_logged_user_id() > 0) {
    $this->dao->where('i.fk_i_user_id', osc_logged_user_id());
  } else {
    return array(); 
  }
  
  // Search params
  if(isset($params['ospKeyword']) && $params['ospKeyword'] !== '') {
    $this->dao->join( $this->getTable_item_description() . ' as f', 'i.pk_i_id = f.fk_i_item_id', 'INNER');
    $this->dao->join( $this->getTable_category() . ' as g', 'i.fk_i_category_id = g.fk_i_category_id', 'INNER');
    $this->dao->like('concat(convert(i.pk_i_id, char), coalesce(f.s_title, ""), coalesce(f.s_description, ""), coalesce(g.s_name, ""), coalesce(g.s_description, ""),  convert(i.fk_i_category_id, char), convert(coalesce(i.i_price, 0), char))', $params['ospKeyword']);
  }
  
  if(isset($params['ospAvailability']) && $params['ospAvailability'] !== '' && $params['ospAvailability'] !== '9') {
    $this->dao->where('coalesce(d.i_sell, 0)=' . $params['ospAvailability']);
  }

  if(isset($params['ospShipping']) && $params['ospShipping'] !== '' && $params['ospShipping'] !== '9') {
    $this->dao->where('coalesce(d.i_shipping, 0)=' . $params['ospShipping']);
  }

  if(isset($params['ospLowStock']) && $params['ospLowStock'] !== '') {
    $this->dao->where('coalesce(d.i_quantity, 0) <= 3');
  }
  
  if($only_count !== true) {
    // $limit[0] == page; $limit[1] == limit
    $page = intval(isset($params['pageId']) ? $params['pageId'] : 0);
    $per_page = intval(isset($params['per_page']) ? ($params['per_page'] > 0 ? $params['per_page'] : 20) : 20);
    
    if($page > 0) {
      $this->dao->limit(($page-1)*$per_page, $per_page);
    } else if($per_page > 0) {
      $this->dao->limit($per_page);
    }
    
    if(!isset($params['sort']) || $params['sort'] == '' || $params['sort'] == 'DESC') {
      $this->dao->orderby('i.pk_i_id', 'DESC');
    } else if ($params['sort'] == 'ASC') {
      $this->dao->orderby('i.pk_i_id', 'ASC');
    }
  }


  $result = $this->dao->get();
  
  if($result) {
    if($only_count === true) {
      $data = $result->row();
      return isset($data['i_count']) ? $data['i_count'] : 0;
    } else {
      return $result->result();
    }
  }

  return ($only_count ? 0 : array());
}


// UPDATE ORDER STATUS
public function updateOrderStatus($order_id, $status) {
  $value = array('i_status' => $status);
  $where = array('pk_i_id' => $order_id);

  $this->dao->update($this->getTable_order(), $value, $where);
}


// UPDATE ORDER COMMENT
public function updateOrderComment($order_id, $comment) {
  $value = array('s_comment' => $comment);
  $where = array('pk_i_id' => $order_id);

  $this->dao->update($this->getTable_order(), $value, $where);
}


// DEACTIVATE ITEM
public function updateItemActive($item_id, $active = 0) {
  $value = array('b_active' => $active);
  $where = array('pk_i_id' => $item_id);

  $this->dao->update($this->getTable_item(), $value, $where);
}


// RESTOCK ORDER PRODUCTS
public function restockOrder($order_id, $type = '+') {
  $order = $this->getOrder($order_id);
  $cart = explode('|', $order['s_cart']);

  if(count($cart) > 0) {
    foreach($cart as $c) {
      $item = explode('x', $c);

      $item_id = $item[2];
      $qty = $item[1];

      if($type == '-') {
        $qty = $qty*(-1);
      }

      ModelOSP::newInstance()->updateItemQuantity($item_id, $qty);
    }
  }
}



}
?>