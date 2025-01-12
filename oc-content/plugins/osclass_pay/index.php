<?php
/*
  Plugin Name: Osclass Pay Plugin
  Plugin URI: https://osclasspoint.com/osclass-plugins/payments-and-shopping/osclass-pay-payment-plugin-i46
  Description: Ultimate payment and monetization solution for osclass classifieds
  Version: 3.9.2
  Author: MB Themes
  Author URI: https://osclasspoint.com
  Author Email: info@osclasspoint.com
  Short Name: osclass_pay
  Plugin update URI: osclass-pay-plugin
  Support URI: https://forums.osclasspoint.com/osclass-pay-plugin/
  Product Key: 0yNxpeRQtrJ1P40QfCn1
*/

// DEBUG MODE
define('OSP_DEBUG', false);

// PAYMENT STATUS
define('OSP_STATUS_FAILED', 0);
define('OSP_STATUS_COMPLETED', 1);
define('OSP_STATUS_PENDING', 2);
define('OSP_STATUS_ALREADY_PAID', 3);
define('OSP_STATUS_AMOUNT_ZERO', 4);
define('OSP_STATUS_AMOUNT_SMALL', 5);
define('OSP_STATUS_INVALID', 6);

// PAYMENT TYPE
define('OSP_TYPE_PUBLISH', '101');
define('OSP_TYPE_PREMIUM', '201');
define('OSP_TYPE_HIGHLIGHT', '401');
define('OSP_TYPE_IMAGE', '501');
define('OSP_TYPE_TOP', '801');
define('OSP_TYPE_REPUBLISH', '601');
define('OSP_TYPE_PACK', '301');
define('OSP_TYPE_MEMBERSHIP', '701');
define('OSP_TYPE_BANNER', '1001');
define('OSP_TYPE_PRODUCT', '1101');
define('OSP_TYPE_MULTIPLE', '901');
define('OSP_TYPE_SHIPPING', '7001');
define('OSP_TYPE_BOOKING', '8001');
define('OSP_TYPE_VOUCHER', '9001');
define('OSP_TYPE_AUCTION_PAY', '3001');
define('OSP_TYPE_AUCTION_BUYOUT', '3002');
define('OSP_TYPE_AUCTION_BID_FEE', '3008');
define('OSP_TYPE_AUCTION_FEE', '3009');
define('OSP_TYPE_OFFER', '4001');


// ORDER STATUS
define('OSP_ORDER_PROCESSING', 0);
define('OSP_ORDER_SHIPPED', 1);
define('OSP_ORDER_COMPLETED', 2);
define('OSP_ORDER_REFUNDED', 8);
define('OSP_ORDER_CANCELLED', 9);


define('OSP_URL_DIR', 'payments');                    // default is 'osclasspay', used in URLs
define('OSP_GROUP_DAYS_REPEATS', '1,3,6,12,24');      // delimit by comma, repeats of "days", by default 30 days
define('OSP_GROUP_DAYS_FORMAT', 'M');                 // user group duration format, D - days, W - weeks, M - months, Y - years, YM - year/month

// SVG
define('OSP_SVG_ADD_TO_CART', '<svg height="24" viewBox="0 0 24 24" width="24"><circle cx="10.5" cy="22.5" r="1.5"/><circle cx="18.5" cy="22.5" r="1.5"/><path d="m24 6.5c0 3.584-2.916 6.5-6.5 6.5s-6.5-2.916-6.5-6.5 2.916-6.5 6.5-6.5 6.5 2.916 6.5 6.5zm-3 0c0-.552-.448-1-1-1h-1.5v-1.5c0-.552-.448-1-1-1s-1 .448-1 1v1.5h-1.5c-.552 0-1 .448-1 1s.448 1 1 1h1.5v1.5c0 .552.448 1 1 1s1-.448 1-1v-1.5h1.5c.552 0 1-.448 1-1z"/><path d="m9 6.5c0-.169.015-.334.025-.5h-2.666l-.38-1.806c-.266-1.26-1.392-2.178-2.679-2.183l-2.547-.011c-.001 0-.002 0-.003 0-.413 0-.748.333-.75.747s.333.751.747.753l2.546.011c.585.002 1.097.42 1.218.992l.505 2.401 1.81 8.596h-.576c-1.241 0-2.25 1.009-2.25 2.25s1.009 2.25 2.25 2.25h15c.414 0 .75-.336.75-.75s-.336-.75-.75-.75h-15c-.414 0-.75-.336-.75-.75s.336-.75.75-.75h1.499.001 13.5c.354 0 .661-.249.734-.596l.665-3.157c-1.431 1.095-3.213 1.753-5.149 1.753-4.687 0-8.5-3.813-8.5-8.5z"/></svg>');



// REQUIRED FUNCTION FILES
require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'model/ModelOSP.php';
require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'functions.php';
require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'email.php';
require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'admin/pagination.php';



osc_enqueue_style('font-awesome47', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
// osc_enqueue_style('font-open-sans', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap');
osc_enqueue_style('osp-user-style', osc_base_url() . 'oc-content/plugins/osclass_pay/css/user.css?v=' . date('YmdHis'));
osc_enqueue_style('tipped', osc_base_url() . 'oc-content/plugins/osclass_pay/css/tipped.css');
osc_enqueue_style('osp-admin-line', osp_url() . 'css/admin_items.css');

osc_register_script('osp-user', osc_base_url() . 'oc-content/plugins/osclass_pay/js/user.js?v=' . date('YmdHis'), 'jquery');
osc_register_script('tipped', osc_base_url() . 'oc-content/plugins/osclass_pay/js/tipped.js', 'jquery');

osc_enqueue_script('osp-user');
osc_enqueue_script('tipped');



// LOAD PAYMENTS
if(osp_param('paypal_enabled') == 1) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/paypl/Paypl.php';
}

if(osp_param('przelewy24_enabled') == 1 && in_array(osp_currency(), array('CZK', 'EUR', 'PLN'))) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/przelewy24/Przelewy24Payment.php';
  Przelewy24Payment::preparePayment();
}

if(osp_param('payherelk_enabled') == 1 && in_array(osp_currency(), array('USD', 'LKR'))) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/payherelk/PayherelkPayment.php';
}

if(osp_param('blockchain_enabled') == 1) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/blockchain/Blockchain.php';
  osc_add_hook('ajax_blockchain', array('BlockchainPayment', 'ajaxPayment'));
}

if(osp_param('braintree_enabled') == 1) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/braintree/BraintreePayment.php';
  osc_add_hook('ajax_braintree', array('BraintreePayment', 'ajaxPayment'));
}

if(osp_param('stripe_enabled') == 1) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/stripe/StripePayment.php';
//  osc_add_hook('ajax_stripe', array('StripePayment', 'ajaxPayment'));
}

if(osp_param('payscz_enabled') == 1 && in_array(osp_currency(), array('CZK', 'EUR', 'USD'))) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/payscz/PaysczPayment.php';
}


if(osp_param('komfortkasse_enabled') == 1) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/komfortkasse/KomfortkassePayment.php';
}

if(osp_param('paystack_enabled') == 1 && in_array(osp_currency(), array('GHS', 'NGN', 'USD', 'ZAR', 'KES'))) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/paystack/PaystackPayment.php';
  osc_add_hook('ajax_paystack', array('PaystackPayment', 'processPayment'));
}

if(osp_param('przelewy24_enabled') == 1 && in_array(osp_currency(), array('PLN', 'CZK', 'EUR'))) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/przelewy24/Przelewy24Payment.php';
}

if(osp_param('payherelk_enabled') == 1 && in_array(osp_currency(), array('USD', 'LKR'))) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/payherelk/PayherelkPayment.php';
}

if(osp_param('instamojo_enabled') == 1 && osp_currency() == 'INR') {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/instamojo/InstamojoPayment.php';
  osc_add_hook('ajax_instamojo', array('InstamojoPayment', 'ajaxPayment'));
}


if(osp_param('pagseguro_enabled') == 1 && osp_currency() == 'BRL') {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/pagseguro/PagseguroPayment.php';
}

if(osp_param('authorizenet_enabled') == 1 && osp_currency() == 'USD') {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/authorizenet/AuthorizenetPayment.php';
  osc_add_hook('ajax_authorizenet', array('AuthorizenetPaymentOSP', 'ajaxPayment'));
}

if(osp_param('skrill_enabled') == 1) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/skrill/SkrillPayment.php';
}

if(osp_param('ccavenue_enabled') == 1 && in_array(osp_currency(), array('INR', 'USD', 'SGD', 'GBP', 'EUR'))) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/ccavenue/CcavenuePayment.php';
}

if(osp_param('payza_enabled') == 1) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/payza/PayzaPayment.php';
}

if(osp_param('payumoney_enabled') == 1 && osp_currency() == 'INR') {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/payumoney/PayumoneyPayment.php';
}

if(osp_param('payulatam_enabled') == 1 && in_array(osp_currency(), array('ARS', 'BRL', 'CLP', 'COP', 'MXN', 'PEN', 'USD'))) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/payulatam/PayulatamPayment.php';
}

if(osp_param('worldpay_enabled') == 1) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/worldpay/WorldpayPayment.php';
}

if(osp_param('weaccept_enabled') == 1) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/weaccept/WeacceptPayment.php';
}

if(osp_param('twocheckout_enabled') == 1) {
  if(osp_param('twocheckout_type') == '' || osp_param('twocheckout_type') == 'onsite') {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/2checkout/TwoCheckoutPayment.php';
    osc_add_hook('ajax_twocheckout', array('TwoCheckoutPayment', 'ajaxPayment'));
  } else {
    require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/2checkout/TwoCheckoutInlinePayment.php';
  }
}

if(osp_param('euplatesc_enabled') == 1 && in_array(osp_currency(), array('EUR', 'USD', 'RON'))) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/euplatesc/EuPlatescPayment.php';
}

if(osp_param('yookassa_enabled') == 1 && osp_currency() == 'RUB') {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/yookassa/YookassaPayment.php';
}

if(osp_param('cardinity_enabled') == 1 && in_array(osp_currency(), array('EUR', 'GBP', 'USD'))) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/cardinity/CardinityPayment.php';
}

if(osp_param('securionpay_enabled') == 1) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/securionpay/SecurionpayPayment.php';
}

if(osp_param('begateway_enabled') == 1) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/begateway/BeGatewayPayment.php';
}

if(osp_param('makecommerce_enabled') == 1) {
  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'payments/makecommerce/MakeCommercePayment.php';
}


// BANNER MANAGEMENT
if(Params::getParam('banner') == 'create') {
  if(osp_param('banner_allow') == 1 && osc_is_web_user_logged_in()) { 
    $type = 1;  // HTML advert
    $group = Params::getParam('group');
    $key = mb_generate_rand_string(6);
    $url = Params::getParam('url');
    $banner_name = Params::getParam('name');
    $code = stripslashes(osp_closetags(Params::getParam('code', false, false)));
    $category = Params::getParam('category');
    $budget = Params::getParam('budget');

    ModelOSP::newInstance()->insertBanner(osc_logged_user_id(), $group, $type, $banner_name, $key, $url, $code, osp_param('banner_fee_view'), osp_param('banner_fee_click'), $budget, $category, '100%', 'auto');
  }
}



// ADD JAVASCRIPT VARIABLES TO FOOTER
function osp_footer_js() { ?>
<script type="text/javascript">
  var ospLocationSection = "<?php echo osc_get_osclass_location(); ?>_<?php echo osc_get_osclass_section(); ?>";
  var ospIsDebug = "<?php echo (OSP_DEBUG ? 1 : 0); ?>";
  var ospButtonInCart = "<?php echo osc_esc_js(__('Success! Go to cart', 'osclass_pay')); ?>";
  var ospButtonNotInCart = "<?php echo osc_esc_js(__('Select promotions', 'osclass_pay')); ?>";
  var ospButtonCartURL = "<?php echo osc_route_url('osp-cart'); ?>";
  var ospAddCartURL = "<?php echo osp_cart_add(1, 2, 3, 4, 5); ?>";
  var ospCurrency = "<?php echo osc_esc_js(osp_currency()); ?>";
  var ospCurrencySymbol = "<?php echo osc_esc_js(osp_currency_symbol()); ?>";
  var ospTheme= "<?php echo osc_current_web_theme(); ?>";



  <?php if(osc_get_osclass_location() == 'item' && osc_get_osclass_section() == 'item_add' && 1==2) { ?>
    // DISABLED FOR NOW
    $(document).ready(function(){
      if($('[name="regionId"]').val() != '') {
        ospPromoteUpdate($('[name="regionId"]').val(), '20');
      } else if($('input[name="region"]').val() != '') {
        ospPromoteUpdate($('input[name="region"]').val(), '21');
      } else if($('[name="countryId"]').val() != '') {
        ospPromoteUpdate($('[name="countryId"]').val(), '22');
      } else if($('input[name="country"]').val() != '') {
        ospPromoteUpdate($('input[name="country"]').val(), '23');
      }
    });
  <?php } ?>

  <?php $locs = ModelOSP::newInstance()->getLocationFees(); ?>
  var ospLoc = [];

  <?php foreach($locs as $l) { ?>
    <?php if(!($l['fk_i_region_id'] <> '' && $l['fk_i_region_id'] > 0)) { ?>
       <?php echo 'ospLoc["C_' . strtoupper($l['fk_c_country_code']) . '_' . $l['s_type'] . '"] = ' . $l['f_fee'] . ';'; ?>
    <?php } else { ?>
       <?php echo 'ospLoc["R_' . $l['fk_i_region_id'] . '_' . $l['s_type'] . '"] = ' . $l['f_fee'] . ';'; ?>
    <?php } ?>
  <?php } ?>
</script>
<?php 
}


// CREATE ITEM LIMIT RELATED TO USER MEMBERSHIP GROUP
// User can only publish as many items as enabled in it's group
function osp_limit_items($action = 'post') {
  if(osp_param('groups_limit_items') == 1) {
    if(Params::getParam('contactEmail') <> '') {
      $user_id = osc_logged_user_id();
      $email = Params::getParam('contactEmail');
    } else {
      $user_id = osc_logged_user_id();
      $email = osc_logged_user_email();
    }

    $group = array();
    if($user_id > 0) {
      $group = ModelOSP::newInstance()->getUserGroupRecord($user_id);
    }

    if(isset($group['pk_i_id']) && $group['i_max_items'] >= 0 && $group['i_max_items_days'] >= 0) {
      $max_items = $group['i_max_items'];
      $max_items_days = $group['i_max_items_days'];
    } else {
      $max_items = osp_param('groups_max_items');
      $max_items_days = osp_param('groups_max_items_days');
    }

    $count = ModelOSP::newInstance()->countUserItems($user_id, $email, $max_items_days);


    // Activating new listing
    if($count > $max_items && $action == 'activate') {
      osc_add_flash_error_message(sprintf(__('You have reached maximum number of listings you can publish (%s items in %s days). Please upgrade your membership in order to increase your limits.', 'osclass_pay'), $max_items, $max_items_days));

      $item_id = Params::getParam('id');
      ModelOSP::newInstance()->updateItemActive($item_id, 0);
      
      if(osc_is_web_user_logged_in()) {
        osp_redirect(osc_route_url('osp-membership'));
      } else {
        osp_redirect(osc_base_url());
      }
      exit;

    // Entering edit page - limit reached and listing is inactive
    } else if($count > $max_items && $action == 'edit' || $count >= $max_items && $action == 'pre-edit') {
      $item_id = Params::getParam('id');
      $item = Item::newInstance()->findByPrimaryKey($item_id);

      // Block for inactive listings only
      if(isset($item['b_active']) && $item['b_active'] != 1) {
        osc_add_flash_error_message(sprintf(__('You have reached maximum number of listings you can publish (%s items in %s days). Please upgrade your membership in order to increase your limits.', 'osclass_pay'), $max_items, $max_items_days));

        if(osc_is_web_user_logged_in()) {
          osp_redirect(osc_route_url('osp-membership'));
        } else {
          osp_redirect(osc_base_url());
        }
        exit;
      }
      
    // Entering publish page
    } else if($count > $max_items && $action == 'post' || $count >= $max_items && $action == 'pre-post') {
      osc_add_flash_error_message(sprintf(__('You have reached maximum number of listings you can publish (%s items in %s days). Please upgrade your membership in order to increase your limits.', 'osclass_pay'), $max_items, $max_items_days));

      if(osc_is_web_user_logged_in()) {
        osp_redirect(osc_route_url('osp-membership'));
      } else {
        osp_redirect(osc_base_url());
      }
      exit;
      
    } else if($action == 'data') {
      return array(
        'user' => $user_id,
        'email' => $email,
        'group_id' => (isset($group['pk_i_id']) ? $group['pk_i_id'] : 0),
        'group_name' => (isset($group['s_name']) ? $group['s_name'] : ''),
        'max_items' => $max_items,
        'max_items_days' => $max_items_days,
        'def_max_items' => osp_param('groups_max_items'),
        'def_max_items_days' => osp_param('groups_max_items_days'),
        'count' => $count
      );
    }
  }
}


// LIMIT WHEN PUBLISHING ITEM, ONLY IN FRONT
// function osc_limit_items_pre_publish() {
  // if(!(Params::getParam('page') == 'items' && Params::getParam('action') == 'item_edit_post')) {
    // osp_limit_items();
  // }
// }

//osc_add_hook('pre_item_post', 'osc_limit_items_pre_publish');

// LIMIT WHEN POSTING A NEW LISTING
function osc_limit_items_posted() {
  if(!defined('OC_ADMIN') || (defined('OC_ADMIN') && OC_ADMIN == false)) {
    osp_limit_items('post');
  }
}

osc_add_hook('pre_item_add', 'osc_limit_items_posted', 1);


// LIMIT WHEN EDITING LISTING
function osc_limit_items_edited() {
  if(!defined('OC_ADMIN') || (defined('OC_ADMIN') && OC_ADMIN == false)) {
    osp_limit_items('edit');
  }
}

osc_add_hook('pre_item_edit', 'osc_limit_items_edited', 1);


// LIMIT ENTERING TO PUBLISH PAGE
function osc_limit_items_publish_page() {
  if(osc_is_publish_page()) {
    osp_limit_items('pre-post');
  }
}

osc_add_hook('init', 'osc_limit_items_publish_page');


// LIMIT ENTERING TO EDIT PAGE
function osc_limit_items_edit_page() {
  if(osc_is_edit_page()) {
    osp_limit_items('pre-edit');
  }
}

osc_add_hook('init', 'osc_limit_items_edit_page');


// LIMIT ACTIVATE
function osc_limit_items_activate($item_id) {
  osp_limit_items('activate');
}

osc_add_hook('activate_item', 'osc_limit_items_activate');


// DO NOT SHOW LISTINGS WHERE PUBLISH FEE IS NOT PAID
function osp_item_filter() {
  if(osp_param('publish_allow') == 1) {
    Search::newInstance()->addJoinTable(DB_TABLE_PREFIX.'t_osp_item.pk_i_id', DB_TABLE_PREFIX.'t_osp_item', '(' . DB_TABLE_PREFIX.'t_item.pk_i_id = '.DB_TABLE_PREFIX.'t_osp_item.i_item_id AND '.DB_TABLE_PREFIX.'t_osp_item.s_type = "101")', 'LEFT OUTER');

    if(osp_param('publish_item_disable') == 1) {
      Search::newInstance()->addConditions(sprintf("coalesce(%st_osp_item.i_paid, 0) = 1", DB_TABLE_PREFIX));
    } else {
      Search::newInstance()->addConditions(sprintf("coalesce(%st_osp_item.i_paid, 1) = 1", DB_TABLE_PREFIX));
    }
  }
}

osc_add_hook('search_conditions', 'osp_item_filter');


// ITEMPAY - ADD FLASH MESSAGES TO HEAD
function osp_itempay_flash() {
  $item_id = Params::getParam('itemId');

  if($item_id <> '' && $item_id > 0) {
    if(osp_param('publish_allow') == 1 && !osp_fee_is_paid(OSP_TYPE_PUBLISH, $item_id) && osp_fee_exists(OSP_TYPE_PUBLISH, $item_id)) {
      osc_add_flash_error_message(__('Publish fee for listing has not been paid. Please pay publish fee in order to enable it and make visible to other users.', 'osclass_pay'));
    }

    if(osp_param('image_allow') == 1 && !osp_fee_is_paid(OSP_TYPE_IMAGE, $item_id) && osp_fee_exists(OSP_TYPE_IMAGE, $item_id)) {
      osc_add_flash_warning_message(__('Show image fee for listing has not been paid. Please pay this fee in order to show images on your listings.', 'osclass_pay'));
    }
  }
}

osc_add_hook('header', 'osp_itempay_flash');



// ADD CREDITS TO USERS PERIODICALLY
function osp_period_bonus() {
  $timestamp = time();
  $period = osp_param('wallet_period');

  if(($period == 'w' && date('D', $timestamp) === 'Mon')
    || ($period == 'm' && date('j', $timestamp) === '1')
    || ($period == 'q' && date('j', $timestamp) === '1' && (date('n', $timestamp) === '1' || date('n', $timestamp) === '4' || date('n', $timestamp) === '7' || date('n', $timestamp) === '10'))
 ) {

    if(osp_param('wallet_enabled') == 1 && osp_param('wallet_periodically') <> '' && osp_param('wallet_periodically') > 0) {
      $users = ModelOSP::newInstance()->getUsers();

      if(count($users) > 0) {
        foreach($users as $u) {
          $group = ModelOSP::newInstance()->getUserGroupRecord($u['pk_i_id']);
          if(isset($group['i_pbonus']) && $group['i_pbonus'] <> '' && $group['i_pbonus'] > 0) {
            $credit = round((1 + $group['i_pbonus']/100) * osp_param('wallet_periodically'), 2);
          } else {
            $credit = round(osp_param('wallet_periodically'), 2);
          }

          ModelOSP::newInstance()->saveLog(sprintf(__('Periodical credits to user %s (%s) at %s', 'osclass_pay'), $u['s_name'], ($credit . osp_currency_symbol()), osc_page_title()), 'wallet_' . date('YmdHis'), $credit, osp_currency(), $u['s_email'], $u['pk_i_id'], NULL, OSP_TYPE_PACK, 'PERIODICAL');
          osp_wallet_update($u['pk_i_id'], $credit);
          osp_email_bonus_credit($u, osp_format_price($credit), $group);
        }
      }
    }

    osc_set_preference('cron_runs_user', date('Y-m-d H:i:s'), 'plugin-osclass_pay', 'STRING');  
  }
}

osc_add_hook('cron_daily', 'osp_period_bonus');


// UPDATE META TITLE ON REGISTRATION PAGE, IF REFERRAL CODE IS PROVIDED
function osp_reg_title_filter() {
  return sprintf(__('Create a new account and get bonus %s!', 'osclass_pay'), osp_format_price(osp_param('wallet_referral')));
}

function osp_reg_title_filter_apply() {
  if(Rewrite::newInstance()->get_location() == 'register' && Params::getParam('ospref') <> '') {
    osc_add_filter('meta_title_filter', 'osp_reg_title_filter');
  }
}

osc_add_hook('init', 'osp_reg_title_filter_apply');


// ADMIN MENU
function osp_menu($title = NULL) {
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/osclass_pay/css/admin.css?v=' . date('YmdHis') . '" rel="stylesheet" type="text/css" />';
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/osclass_pay/css/bootstrap-switch.css" rel="stylesheet" type="text/css" />';
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/osclass_pay/css/tipped.css" rel="stylesheet" type="text/css" />';
  echo '<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/osclass_pay/js/admin.js"></script>';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/osclass_pay/js/tipped.js"></script>';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/osclass_pay/js/bootstrap-switch.js"></script>';


  $current = basename(Params::getParam('file'));

  $links = array();
  $links[] = array('file' => 'configure.php', 'icon' => 'fa-wrench', 'title' => __('Configure', 'osclass_pay'));
  $links[] = array('file' => 'gateway.php', 'icon' => 'fa-cc-mastercard', 'title' => __('Gateways', 'osclass_pay'));
  $links[] = array('file' => 'item.php', 'icon' => 'fa-star', 'title' => __('Item', 'osclass_pay'));
  $links[] = array('file' => 'user.php', 'icon' => 'fa-user', 'title' => __('User', 'osclass_pay'));
  $links[] = array('file' => 'banner.php', 'icon' => 'fa-newspaper-o', 'title' => __('Banner', 'osclass_pay'));
  $links[] = array('file' => 'ecommerce.php', 'icon' => 'fa-shopping-basket', 'title' => __('eCommerce', 'osclass_pay'));
  $links[] = array('file' => 'log.php', 'icon' => 'fa-database', 'title' => __('Logs', 'osclass_pay'));

  if($title == '') { $title = __('Configure', 'osclass_pay'); }

  $text  = '<div class="mb-head">';
  $text .= '<div class="mb-head-left">';
  $text .= '<h1>' . $title . '</h1>';
  $text .= '<h2>Osclass Pay Plugin</h2>';
  $text .= '</div>';
  $text .= '<div class="mb-head-right">';
  $text .= '<ul class="mb-menu">';

  foreach($links as $l) {
    $text .= '<li><a href="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=osclass_pay/admin/' . $l['file'] . '" class="' . ($l['file'] == $current ? 'active' : '') . '"><i class="fa ' . $l['icon'] . '"></i><span>' . $l['title'] . '</span></a></li>';
  }
 
  $text .= '</ul>';
  $text .= '</div>';
  $text .= '</div>';

  echo $text;
}


// ADMIN SUBMENU
function osp_submenu($core, $links, $current = NULL) {
  $base_url = osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=osclass_pay/admin/' . $core . '&go_to_file=';

  if($current == '') {
    $current = $links[0]['file'];
  }

  //basename(__FILE__);

  $text  = '<div class="mb-subhead">';
  $text .= '<ul class="mb-submenu">';

  foreach($links as $l) {
    $text .= '<li class="' . ($l['file'] == $current ? 'active' : '') . '"><a href="' . $base_url . $l['file'] . '" class="' . ($l['file'] == $current ? 'active' : '') . '"><i class="fa '. $l['icon'] . '"></i><span>' . $l['title'] . '</span></a></li>';
  }

  $text .= '</ul>';
  $text .= '</div>';

  echo $text;

  if(osp_is_demo()) {
    message_info(__('This is demo site, you may not be able to change settings and update / remove elements.', 'osclass_pay'));
  }
  
  return $current;
}


// ADMIN FOOTER
function osp_footer() {
  $pluginInfo = osc_plugin_get_info('osclass_pay/index.php');
  $text  = '<div class="mb-footer">';
  $text .= '<a target="_blank" class="mb-developer" href="https://osclasspoint.com"><img src="https://osclasspoint.com/favicon.ico" alt="MB Themes" /> osclasspoint.com</a>';
  $text .= '<a target="_blank" href="' . $pluginInfo['support_uri'] . '"><i class="fa fa-bug"></i> ' . __('Report Bug', 'osclass_pay') . '</a>';
  $text .= '<a target="_blank" href="https://forums.osclasspoint.com/"><i class="fa fa-handshake-o"></i> ' . __('Support Forums', 'osclass_pay') . '</a>';
  $text .= '<a target="_blank" class="mb-last" href="mailto:info@osclasspoint.com"><i class="fa fa-envelope"></i> ' . __('Contact Us', 'osclass_pay') . '</a>';
  $text .= '<span class="mb-version">v' . $pluginInfo['version'] . '</span>';
  $text .= '</div>';

  return $text;
}


// ADD MENU LINK TO PLUGIN LIST
function osp_admin_menu() {
echo '<h3><a href="#">Osclass Pay Plugin</a></h3>
<ul> 
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/configure.php') . '">&raquo; ' . __('Configure', 'osclass_pay') . '</a></li>
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/gateway.php') . '">&raquo; ' . __('Gateways', 'osclass_pay') . '</a></li>
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/item.php') . '">&raquo; ' . __('Item', 'osclass_pay') . '</a></li>
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/user.php') . '">&raquo; ' . __('User', 'osclass_pay') . '</a></li>
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/banner.php') . '">&raquo; ' . __('Banner', 'osclass_pay') . '</a></li>
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/ecommerce.php') . '">&raquo; ' . __('eCommerce', 'osclass_pay') . '</a></li>
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/log.php') . '">&raquo; ' . __('Log', 'osclass_pay') . '</a></li>
</ul>';
}
if((strpos(osc_plugin_get_info('osclass_pay/index.php')['plugin_update_uri'],'ay-pl') == false || strpos(osc_plugin_get_info('osclass_pay/index.php')['plugin_update_uri'],'ss-pa') == false) && !osc_is_admin_user_logged_in()) {header('Location:'.osc_base_url());}


// INSTALL PLUGIN
function osp_install() {
  ModelOSP::newInstance()->install();
  osp_get_currency_rates();  // update currency rates
}


// UNINSTALL PLUGIN
function osp_uninstall() {
  ModelOSP::newInstance()->uninstall();
}


// LOAD PAYMENT JAVASCRIPTS
function osp_load_js() {
  if(Params::getParam('page') == 'custom') {
    if(osp_param('paypal_enabled') == 1) {
      osc_register_script('paypal', 'https://www.paypalobjects.com/js/external/dg.js', array('jquery'));
      osc_enqueue_script('paypal');
    }
    
    if(osp_param('blockchain_enabled') == 1) {
      osc_register_script('blockchain-js', osc_base_url() . 'oc-content/plugins/osclass_pay/payments/blockchain/source/pay-now-button-v2.js', array('jquery'));
      osc_enqueue_script('blockchain-js');
    }
    
    if(osp_param('stripe_enabled') == 1) {
      //osc_register_script('stripe', 'https://checkout.stripe.com/v3/checkout.js', array('jquery'));  // old version
      osc_register_script('stripe', 'https://js.stripe.com/v3/', array('jquery'));  // sca version

      osc_enqueue_script('stripe');
    }

    if(osp_param('pagseguro_enabled') == 1 && osp_currency() == 'BRL') {
      if(osp_param('pagseguro_sandbox') == 1) {
        osc_register_script('pagseguro', 'https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js', array('jquery'));
      } else {
        osc_register_script('pagseguro', 'https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js', array('jquery'));
      }

      osc_enqueue_script('pagseguro');
    }

    if(osp_param('payza_enabled') == 1) {
      osc_register_script('payza', 'https://secure.payza.com/JS/PayzaCheckout.js', array('jquery'));
      osc_enqueue_script('payza');
    }

    if(osp_param('twocheckout_enabled') == 1) {
      if(osp_param('twocheckout_type') == '' || osp_param('twocheckout_type') == 'onsite') {
        osc_register_script('twocheckout', 'https://www.2checkout.com/static/checkout/javascript/direct.min.js', array('jquery'));
        osc_register_script('twocheckout-token', 'https://www.2checkout.com/checkout/api/2co.min.js', array('jquery'));
        osc_enqueue_script('twocheckout');
        osc_enqueue_script('twocheckout-token');
      }
    }
    
    if(osp_param('begateway_enabled') == 1) {
      $url = explode('.', osp_param('begateway_domain_checkout'));
      $url[0] = 'js';
      $url = 'https://' . implode('.', $url) . '/widget/be_gateway.js';
      osc_register_script('begateway', $url, array('jquery'));
      osc_enqueue_script('begateway');
    }
  }
}


// REDIRECT TO PAYMENT PAGE AFTER PUBLISHING LISTING
function osp_item_publish($item, $publish = 1) {
  $redirect = false;
  $post = Params::getParamsAsArray('post');
  $types_required = array(OSP_TYPE_PUBLISH);
  $types_optional = array(OSP_TYPE_PREMIUM, OSP_TYPE_IMAGE, OSP_TYPE_TOP, OSP_TYPE_HIGHLIGHT, OSP_TYPE_REPUBLISH);
  
  // Check free limits
  $free_publish_limit = 0;
  
  if(osc_is_web_user_logged_in()) {
    $free_publish_limit = osp_get_user_group_remaining_limit(osc_logged_user_id(), OSP_TYPE_PUBLISH);
  }

  // REQUIRED PAYMENTS, CREATE PAYMENT REQUIREMENT
  foreach($types_required as $type) {
    
    //if(osp_fee_is_allowed($type) && !osp_fee_is_paid($type, $item['pk_i_id'])) {
    if(osp_fee_is_allowed($type) && (!osp_fee_exists($type, $item['pk_i_id']) || !osp_fee_is_paid($type, $item['pk_i_id']))) {
      $fee = osp_get_fee($type, 1, $item['pk_i_id']);

      if($fee > 0) {
        if(@$post[$type] == 1 || $publish == 1) {
          if($type == OSP_TYPE_PUBLISH && $free_publish_limit > 0) {
            ModelOSP::newInstance()->createItem($type, $item['pk_i_id'], 1, date("Y-m-d H:i:s"), -1);
            ModelOSP::newInstance()->saveLog(sprintf(__('Free group publish limit usage of %s on item #%s (%s) at %s', 'osclass_pay'), $item['s_contact_name'], $item['pk_i_id'], ($fee . osp_currency_symbol()), osc_page_title()), 'freepub_' . date('YmdHis'), $fee, osp_currency(), $item['s_contact_email'], $item['fk_i_user_id'], NULL, OSP_TYPE_PUBLISH, 'FREE_LIMIT');

          } else {
            $redirect = true;
            osc_resend_flash_messages();
            ModelOSP::newInstance()->createItem($type, $item['pk_i_id'], 0, date("Y-m-d H:i:s"), -1);
          }

          if($type == OSP_TYPE_PUBLISH && osp_param('publish_item_disable') == 1 && $publish == 1 && $free_publish_limit <= 0) {
            osp_item_active($item['pk_i_id'], 0); // deactivate item
          }
        }
      }
    }
  }


  // OPTIONAL PAYMENTS, CHECK IF USER SELECTED
  foreach($types_optional as $type) {
    if(osp_fee_is_allowed($type) && (!osp_fee_is_paid($type, $item['pk_i_id']) || $type == OSP_TYPE_TOP) && @$post[$type] == 1) {

      if($type == OSP_TYPE_IMAGE ||  $type == OSP_TYPE_TOP) {
        $hours = null;
        $repeat = null;
      }
  
      if($type == OSP_TYPE_PREMIUM || $type == OSP_TYPE_HIGHLIGHT) {
        $hours = @$post[$type . '_duration'];
        $repeat = null;
      }

      if($type == OSP_TYPE_REPUBLISH) {
        $hours = @$post[$type . '_duration'];
        $repeat = @$post[$type . '_repeat'];
      }


      $fee = osp_get_fee($type, 1, $item['pk_i_id'], $hours, $repeat);

      if($fee > 0) {
        $redirect = true;
        osc_resend_flash_messages();

        $curr_date = date('Y-m-d H:i:s');
        $expire = date('Y-m-d H:i:s', strtotime(" + " . $hours . " hours", strtotime($curr_date)));

        ModelOSP::newInstance()->createItem($type, $item['pk_i_id'], 0, date("Y-m-d H:i:s"), -1, $expire, $hours, $repeat);
      }
    }
  }

  // Send email notification with promotions
  if($publish <> 0 && $publish <> '') {
    osp_email_promote($item);
  }

  if($redirect) {
    osp_redirect(osc_route_url('osp-item-pay-publish', array('itemId' => $item['pk_i_id'], 'isPublish' => $publish)));
    exit;
  }
}

osc_add_hook('posted_item', 'osp_item_publish', 10);


// MANAGE PROMOTE OPTIONS FROM ITEM PAGE
function osp_item_promote_manage() {
  $item_id = Params::getParam('itemId');
  $item = Item::newInstance()->findByPrimaryKey($item_id);
  osp_item_publish($item, 0);
}



// CREATE LINK IN USER MENU
function osp_user_sidebar() {
  if(osc_current_web_theme() == 'veronika' || osc_current_web_theme() == 'stela' || osc_current_web_theme() == 'starter' || (defined('USER_MENU_ICONS') && USER_MENU_ICONS == 1)) {
    if(osp_param('links_sidebar') == 0) {
      echo '<li class="opt_osp_payment"><a href="' . osc_route_url('osp-item') . '" ><i class="fa fa-star-o"></i> ' . __('Promotions', 'osclass_pay') . '</a></li>';

    } else {
      echo '<li class="opt_osp_item"><a href="' . osc_route_url('osp-item') . '" ><i class="fa fa-list"></i> ' . __('Items', 'osclass_pay') . '</a></li>';

      if(osp_param('wallet_enabled') == 1) {
        echo '<li class="opt_osp_pack"><a href="' . osc_route_url('osp-pack') . '" ><i class="fa fa-tags"></i> ' . __('Wallet & Packs', 'osclass_pay') . '</a></li>';
      }
      
      if(osp_param('groups_enabled') == 1) {
        echo '<li class="opt_osp_membership"><a href="' . osc_route_url('osp-membership') . '" ><i class="fa fa-star"></i> ' . __('Membership', 'osclass_pay') . '</a></li>';
      }

      if(osp_param('banner_allow') == 1) {
        echo '<li class="opt_osp_banner"><a href="' . osc_route_url('osp-banner') . '" ><i class="fa fa-newspaper-o"></i> ' . __('Banners', 'osclass_pay') . '</a></li>';
      }

      if(osp_param('selling_allow') == 1) {
        echo '<li class="opt_osp_order"><a href="' . osc_route_url('osp-order') . '" ><i class="fa fa-handshake-o"></i> ' . __('Orders & Sales', 'osclass_pay') . '</a></li>';
      }

      echo '<li class="opt_osp_cart"><a href="' . osc_route_url('osp-cart') . '" ><i class="fa fa-shopping-cart"></i> ' . __('Cart', 'osclass_pay') . '</a></li>';
      echo '<li class="opt_osp_payments"><a href="' . osc_route_url('osp-payments', array('history' => 1)) . '" ><i class="fa fa-cc-mastercard"></i> ' . __('Payments History', 'osclass_pay') . '</a></li>';
    }
    
  } else {
    if(osp_param('links_sidebar') == 0) {
      echo '<li class="opt_osp_payment"><a href="' . osc_route_url('osp-item') . '" >' . __('Promotions', 'osclass_pay') . '</a></li>';

    } else {
      echo '<li class="opt_osp_item"><a href="' . osc_route_url('osp-item') . '" >' . __('Items', 'osclass_pay') . '</a></li>';

      if(osp_param('wallet_enabled') == 1) {
        echo '<li class="opt_osp_pack"><a href="' . osc_route_url('osp-pack') . '" >' . __('Wallet & Packs', 'osclass_pay') . '</a></li>';
      }
      
      if(osp_param('groups_enabled') == 1) {
        echo '<li class="opt_osp_membership"><a href="' . osc_route_url('osp-membership') . '" >' . __('Membership', 'osclass_pay') . '</a></li>';
      }

      if(osp_param('banner_allow') == 1) {
        echo '<li class="opt_osp_banner"><a href="' . osc_route_url('osp-banner') . '" >' . __('Banners', 'osclass_pay') . '</a></li>';
      }

      if(osp_param('selling_allow') == 1) {
        echo '<li class="opt_osp_order"><a href="' . osc_route_url('osp-order') . '" >' . __('Orders & Sales', 'osclass_pay') . '</a></li>';
      }
    
      echo '<li class="opt_osp_cart"><a href="' . osc_route_url('osp-cart') . '" >' . __('Shopping Cart', 'osclass_pay') . '</a></li>';
      echo '<li class="opt_osp_payments"><a href="' . osc_route_url('osp-payments', array('history' => 1)) . '" >' . __('Payments History', 'osclass_pay') . '</a></li>';
    }
  }
}


// EXECUTE HOURLY CRON
function osp_hourly_cron() {
  $report = ModelOSP::newInstance()->purgeExpired();
  //print_r($report);
}


// WHEN ITEM IS MANUALLY SET TO NO-PREMIUM, CLEAN IT UP ON THE PLUGIN TABLE
function osp_premium_off($id) {
  osc_add_flash_ok_message(__('Listing has been unmarked as premium in Osclass Pay plugin as well.', 'osclass_pay'), 'admin');
  ModelOSP::newInstance()->deleteItem(OSP_TYPE_PREMIUM, $id);
}


// WHEN ITEM IS MANUALLY SET TO PREMIUM, ADD IT TO PLUGIN TABLE
function osp_premium_on($id) {
  osc_add_flash_ok_message(__('Listing has been marked as premium in Osclass Pay plugin as well as non-expiring', 'osclass_pay'), 'admin');
  ModelOSP::newInstance()->createItem(OSP_TYPE_PREMIUM, $id, 1, date("Y-m-d H:i:s"), -1, '2099-01-01 00:00:00');
}


// AVOID CATEGORY CHANGES ONCE THE ITEM IS PAID
function osp_prevent_category($item_id = '') {
  if(is_array($item_id) && isset($item_id['pk_i_id'])) {
    $item_id = $item_id['pk_i_id'];
  }
  
  if(osc_get_osclass_location() == 'item' && osc_get_osclass_section() == 'item_edit') {
    if($item_id == '' || $item_id <= 0) {
      $item_id = osc_item_id();
    }

    if($item_id <> '' && $item_id > 0) {
      $item = Item::newInstance()->findByPrimaryKey($item_id);

      $publish_allow = osp_param('publish_allow');
      $premium_allow = osp_param('premium_allow');

      $publish_fee_paid = osp_fee_is_paid(OSP_TYPE_PUBLISH, $item_id);
      $premium_fee_paid = osp_fee_is_paid(OSP_TYPE_PREMIUM, $item_id);

      if(($publish_allow == 1 && $publish_fee_paid) || ($premium_allow == 1 && $premium_fee_paid)) {
        if(isset($item['fk_i_category_id']) && $item['fk_i_category_id'] > 0) {
          $cat[0] = Category::newInstance()->findByPrimaryKey($item['fk_i_category_id']);
          View::newInstance()->_exportVariableToView('categories', $cat);
          View::newInstance()->_exportVariableToView('categories_restricted', true);
        }
      }
    }
  }
}


// SHOW ITEM HOOK
function osp_show_item($item) {

  // FLASH MESSAGES ARE CREATED IN FUNCTION osp_itempay_flash

  $redirect = false;
  if(osp_param('publish_allow') == 1 && !osp_fee_is_paid(OSP_TYPE_PUBLISH, $item['pk_i_id']) && osp_fee_exists(OSP_TYPE_PUBLISH, $item['pk_i_id'])) {
    $redirect = true;
  }

  if(osp_param('image_allow') == 1 && !osp_fee_is_paid(OSP_TYPE_IMAGE, $item['pk_i_id']) && osp_fee_exists(OSP_TYPE_IMAGE, $item['pk_i_id'])) {
    if($item['fk_i_user_id'] == osc_logged_user_id() && osc_is_web_user_logged_in()) {
      $redirect = true;
    }
  }

  if($redirect) {
    osp_redirect(osc_route_url('osp-item-pay', array('itemId' => $item['pk_i_id'])));
  }
}

osc_add_hook('show_item', 'osp_show_item');



// SHOW PROMOTION OPTIONS ON ITEM DETAIL PAGE
function osp_show_item_promote($item) {
  if(osc_is_web_user_logged_in() && osc_logged_user_id() == $item['fk_i_user_id'] || osc_is_admin_user_logged_in()) {
    include_once 'user/item_post.php';
  }
}


// SHOW PROMOTION OPTIONS ON ITEM PAY PAGE
function osp_show_itempay_promote($item) {
  $is_itempay = 1;

  include_once 'user/item_post.php';
}



// ADD NOTIFICATION TO ADMIN TOOLBAR MENU - PENDING BANK TRANSFERS
function osp_admin_toolbar_transfer(){
  if(!osc_is_moderator()) {
    $total = ModelOSP::newInstance()->getBankTransfers(0);
    $total = count($total);

    if($total > 0) {
      $title = '<i class="circle circle-red">' . $total . '</i>' . ($total == 1 ? __('Pending bank transfer', 'osclass_pay') : __('Pending bank transfers', 'osclass_pay'));
      AdminToolbar::newInstance()->add_menu(
        array(
          'id' => 'osclass_pay_transfer',
          'title' => $title,
          'href'  => osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=osclass_pay/admin/gateway.php&goto=1',
          'meta'  => array('class' => 'action-btn action-btn-black')
       )
      );
    }
  }
}

osc_add_hook('add_admin_toolbar_menus', 'osp_admin_toolbar_transfer', 1);




// ADD NOTIFICATION TO ADMIN TOOLBAR MENU - PENDING BANNERS
function osp_admin_toolbar_banner(){
  if(!osc_is_moderator()) {
    $total = ModelOSP::newInstance()->getBanners(0);
    $total = count($total);

    if($total > 0) {
      $title = '<i class="circle circle-red">' . $total . '</i>' . ($total == 1 ? __('Pending banner', 'osclass_pay') : __('Pending banners', 'osclass_pay'));
      AdminToolbar::newInstance()->add_menu(
        array(
          'id' => 'osclass_pay_banner',
          'title' => $title,
          'href'  => osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=osclass_pay/admin/banner.php&position=2',
          'meta'  => array('class' => 'action-btn action-btn-black')
       )
      );
    }
  }
}

osc_add_hook('add_admin_toolbar_menus', 'osp_admin_toolbar_banner', 1);


// ADD NOTIFICATION TO ADMIN TOOLBAR MENU - PENDING BANNERS
function osp_admin_toolbar_order(){
  if(!osc_is_moderator() && osp_param('selling_allow')) {
    $total = ModelOSP::newInstance()->getOrders2(array('status' => 0), true);

    if($total > 0) {
      $title = '<i class="circle circle-red">' . $total . '</i>' . ($total == 1 ? __('New order', 'osclass_pay') : __('New orders', 'osclass_pay'));
      AdminToolbar::newInstance()->add_menu(
        array(
          'id' => 'osclass_pay_order',
          'title' => $title,
          'href'  => osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=osclass_pay/admin/ecommerce.php&position=3',
          'meta'  => array('class' => 'action-btn action-btn-black')
       )
      );
    }
  }
}

osc_add_hook('add_admin_toolbar_menus', 'osp_admin_toolbar_order', 1);


// DELETE ITEM HOOK
function osp_delete_item($itemId) {
  ModelOSP::newInstance()->deleteItem(-1, $itemId);
  ModelOSP::newInstance()->deleteItemData($itemId);
}


// CONFIGURE LINK IN PLUGIN LIST
function osp_configure_link() {
  osc_admin_render_plugin(osc_plugin_path(dirname(__FILE__)) . '/admin/configure.php') ;
}


// PLUGIN UPDATE
function osp_update_version() {
  ModelOSP::newInstance()->versionUpdate();
}

// REMOVE PENDING PAYMENT ENTRIES OLDER THAN 180 DAYS
function osp_remove_pending_old() {
  ModelOSP::newInstance()->deletePendingOld(180);
}

osc_add_hook('cron_daily', 'osp_remove_pending_old');


// ROUTES
osc_add_route('osp-item-pay', OSP_URL_DIR . '/itempay/([0-9]+)', OSP_URL_DIR . '/itempay/{itemId}', osc_plugin_folder(__FILE__).'user/item_pay.php', false, 'custom', 'osp-itempay', __('Listings promotion', 'osclass_pay'));
osc_add_route('osp-item-pay-publish', OSP_URL_DIR . '/itempaypub/([0-9]+)/([0-9]+)', OSP_URL_DIR . '/itempaypub/{itemId}/{isPublish}', osc_plugin_folder(__FILE__).'user/item_pay.php', false, 'custom', 'osp-itempay', __('Listings promotion', 'osclass_pay'));
osc_add_route('osp-item-pay-remove', OSP_URL_DIR . '/itempaydel/(.+)/([0-9]+)', OSP_URL_DIR . '/itempaydel/{removeType}/{itemId}', osc_plugin_folder(__FILE__).'user/item_pay.php');
//osc_add_route('osp-item-page', OSP_URL_DIR . '/pageitem/([0-9]+)', OSP_URL_DIR . '/pageitem/{pageId}', osc_plugin_folder(__FILE__).'user/item.php', true, 'custom', 'osp-item', __('Listings promotion', 'osclass_pay'));
osc_add_route('osp-item-page', OSP_URL_DIR . '/item/([0-9]+)', OSP_URL_DIR . '/item/{pageId}', osc_plugin_folder(__FILE__).'user/item.php', true, 'custom', 'osp-item', __('Listings promotion', 'osclass_pay'));
osc_add_route('osp-item', OSP_URL_DIR . '/item', OSP_URL_DIR . '/item', osc_plugin_folder(__FILE__).'user/item.php', true, 'custom', 'osp-item', __('Listings promotion', 'osclass_pay'));
osc_add_route('osp-cart', OSP_URL_DIR . '/cart',  OSP_URL_DIR . '/cart', osc_plugin_folder(__FILE__).'user/cart.php', true, 'custom', 'osp-cart', __('Shopping cart', 'osclass_pay'));
osc_add_route('osp-cart-update', OSP_URL_DIR . '/updatecart/(.+)', OSP_URL_DIR . '/updatecart/{product}', osc_plugin_folder(__FILE__).'user/cart.php', true, 'custom', 'osp-cart', __('Shopping cart', 'osclass_pay'));
osc_add_route('osp-cart-remove', OSP_URL_DIR . '/removecart/(.+)', OSP_URL_DIR . '/removecart/{remove}', osc_plugin_folder(__FILE__).'user/cart.php', true, 'custom', 'osp-cart', __('Shopping cart', 'osclass_pay'));
osc_add_route('osp-pack', OSP_URL_DIR . '/pack', OSP_URL_DIR . '/pack', osc_plugin_folder(__FILE__).'user/pack.php', true, 'custom', 'osp-pack', __('Wallet & Packs', 'osclass_pay'));
osc_add_route('osp-membership', OSP_URL_DIR . '/membership', OSP_URL_DIR . '/membership', osc_plugin_folder(__FILE__).'user/group.php', true, 'custom', 'osp-membership', __('Membership', 'osclass_pay'));
osc_add_route('osp-banner', OSP_URL_DIR . '/banner', OSP_URL_DIR . '/banner', osc_plugin_folder(__FILE__).'user/banner.php', true, 'custom', 'osp-banner', __('Advertisement', 'osclass_pay'));
osc_add_route('osp-banner-remove', OSP_URL_DIR . '/removebanner/(.+)', OSP_URL_DIR . '/removebanner/{removeId}', osc_plugin_folder(__FILE__).'user/banner.php', true, 'custom', 'osp-banner', __('Advertisement', 'osclass_pay'));
osc_add_route('osp-shipping-edit', OSP_URL_DIR . '/shipping/(.+)', OSP_URL_DIR . '/shipping/{editId}', osc_plugin_folder(__FILE__).'user/shipping.php', true, 'custom', 'osp-shipping', __('Shipping', 'osclass_pay'));
osc_add_route('osp-shipping-remove', OSP_URL_DIR . '/shipping/([0-9]+)', OSP_URL_DIR . '/shipping/{removeId}', osc_plugin_folder(__FILE__).'user/shipping.php', true, 'custom', 'osp-shipping', __('Shipping', 'osclass_pay'));
osc_add_route('osp-shipping', OSP_URL_DIR . '/shipping', OSP_URL_DIR . '/shipping', osc_plugin_folder(__FILE__).'user/shipping.php', true, 'custom', 'osp-shipping', __('Shipping', 'osclass_pay'));
osc_add_route('osp-manager-paginate', OSP_URL_DIR . '/manager/([0-9]+)', OSP_URL_DIR . '/manager/{pageId}', osc_plugin_folder(__FILE__).'user/manager.php', true, 'custom', 'osp-manager', __('Orders management', 'osclass_pay'));
osc_add_route('osp-manager', OSP_URL_DIR . '/orders-management', OSP_URL_DIR . '/orders-management', osc_plugin_folder(__FILE__).'user/manager.php', true, 'custom', 'osp-manager', __('Orders management', 'osclass_pay'));
osc_add_route('osp-products-paginate', OSP_URL_DIR . '/products/([0-9]+)', OSP_URL_DIR . '/products/{pageId}', osc_plugin_folder(__FILE__).'user/products.php', true, 'custom', 'osp-products', __('Products management', 'osclass_pay'));
osc_add_route('osp-products', OSP_URL_DIR . '/products', OSP_URL_DIR . '/products', osc_plugin_folder(__FILE__).'user/products.php', true, 'custom', 'osp-products', __('Products management', 'osclass_pay'));
osc_add_route('osp-sales-paginate', OSP_URL_DIR . '/sales/([0-9]+)', OSP_URL_DIR . '/sales/{pageId}', osc_plugin_folder(__FILE__).'user/sales.php', true, 'custom', 'osp-sales', __('Sales', 'osclass_pay'));
osc_add_route('osp-sales', OSP_URL_DIR . '/sales', OSP_URL_DIR . '/sales', osc_plugin_folder(__FILE__).'user/sales.php', true, 'custom', 'osp-sales', __('Sales', 'osclass_pay'));
osc_add_route('osp-order-paginate', OSP_URL_DIR . '/order/([0-9]+)', OSP_URL_DIR . '/order/{pageId}', osc_plugin_folder(__FILE__).'user/order.php', true, 'custom', 'osp-order', __('Orders & Sales', 'osclass_pay'));
osc_add_route('osp-order', OSP_URL_DIR . '/order', OSP_URL_DIR . '/order', osc_plugin_folder(__FILE__).'user/order.php', true, 'custom', 'osp-order', __('Orders & Sales', 'osclass_pay'));
osc_add_route('osp-payments', OSP_URL_DIR . '/payments/([0-9]+)', OSP_URL_DIR . '/payments/{history}', osc_plugin_folder(__FILE__).'user/payments.php', true, 'custom', 'osp-payments', __('Payments history', 'osclass_pay'));
osc_add_route('osp-restrict', OSP_URL_DIR . '/restrict/([^\/]+)', OSP_URL_DIR . '/restrict/{category}', osc_plugin_folder(__FILE__).'user/restrict.php', false, 'custom', 'osp-restrict', __('Restricted access', 'osclass_pay'));
osc_add_route('osp-admin-mark', OSP_URL_DIR . '/admin/mark/(.+)/([0-9]+)/([0-9]+)/([0-9]+)/([0-9]+)', OSP_URL_DIR . '/admin/mark/{type}/{itemId}/{what}/{iPage}/{iDisplayLength}', osc_plugin_folder(__FILE__).'admin/mark.php');
osc_add_route('osp-wallet', OSP_URL_DIR . '/wallet/([^\/]+)/([^\/]+)/(.+)', OSP_URL_DIR . '/wallet/{a}/{extra}/{desc}', osc_plugin_folder(__FILE__).'/user/wallet_pay.php', true);
osc_add_route('osp-transfer', OSP_URL_DIR . '/transfer/([^\/]+)/([^\/]+)/(.+)', OSP_URL_DIR . '/transfer/{a}/{extra}/{desc}', osc_plugin_folder(__FILE__).'/user/transfer_pay.php');
osc_add_route('osp-admin-pay', OSP_URL_DIR . '/adminpay/([^\/]+)/([^\/]+)/(.+)', OSP_URL_DIR . '/adminpay/{a}/{extra}/{desc}', osc_plugin_folder(__FILE__).'/user/admin_pay.php');
osc_add_route('osp-admin-transfer', OSP_URL_DIR . '/admin/transfer/([0-9]+)/([0-9]+)', OSP_URL_DIR . '/admin/transfer/{btId}/{status}', osc_plugin_folder(__FILE__).'admin/_gateway_transfer.php');


// HOOKS
osc_register_plugin(osc_plugin_path(__FILE__), 'osp_install');
osc_add_hook(osc_plugin_path(__FILE__) . '_configure', 'osp_configure_link');
osc_add_hook(osc_plugin_path(__FILE__) . '_uninstall', 'osp_uninstall');
osc_add_hook(osc_plugin_path(__FILE__) . '_enable', 'osp_update_version');

osc_add_hook('admin_menu','osp_admin_menu', 1);

osc_add_hook('init', 'osp_load_js');
osc_add_hook('footer', 'osp_footer_js');
osc_add_hook('user_menu', 'osp_user_sidebar');
osc_add_hook('cron_hourly', 'osp_hourly_cron');
osc_add_hook('item_premium_off', 'osp_premium_off');
osc_add_hook('item_premium_on', 'osp_premium_on');
osc_add_hook('before-content', 'osp_prevent_category');
osc_add_hook('before_item_edit', 'osp_prevent_category');
osc_add_hook('item_detail', 'osp_show_item_promote', 10);
osc_add_hook('delete_item', 'osp_delete_item');

?>