<?php
/*
  Plugin Name: SMS Verification and Notification Plugin
  Plugin URI: https://osclasspoint.com/osclass-plugins/messaging-and-communication/sms-notification-and-verification-plugin-i104
  Description: Send SMS notifications to customers & admins, add phone number verification functionality.
  Version: 1.9.0
  Author: MB Themes
  Author URI: https://osclasspoint.com
  Author Email: info@osclasspoint.com
  Short Name: sms
  Plugin update URI: sms
  Support URI: https://forums.osclasspoint.com/sms-notification-and-verification-plugin/
  Product Key: 5GbeI4IbxU5ZLeSXlS5F
*/

define('PHONE_CHECK_ADVANCED', true);

require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'model/ModelSMS.php';
require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'functions.php';


osc_enqueue_style('sms-user', osc_base_url() . 'oc-content/plugins/sms/css/user.css?v=' . date('YmdHis'));
osc_enqueue_style('sms-tel-style', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/13.0.4/css/intlTelInput.css');

osc_register_script('sms-tel-script', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/13.0.4/js/intlTelInput.min.js', array('jquery'));
osc_register_script('sms-user', osc_base_url() . 'oc-content/plugins/sms/js/user.js?v=' . date('YmdHis'), array('jquery', 'sms-tel-script'));

osc_enqueue_script('sms-user');
osc_enqueue_script('sms-tel-script');

osc_add_route('sms-user-verify', 'sms/user-verify/(.+)', 'sms/user-verify/{userId}', osc_plugin_folder(__FILE__).'/form/verify.php', false, 'sms', 'user-verify');
osc_add_route('sms-item-verify', 'sms/item-verify/(.+)', 'sms/item-verify/{itemId}', osc_plugin_folder(__FILE__).'/form/verify.php', false, 'sms', 'item-verify');



// INSTALL FUNCTION - DEFINE VARIABLES
function sms_call_after_install() {
  osc_set_preference('admin_phone_number', '', 'plugin-sms', 'STRING');
  osc_set_preference('provider', '', 'plugin-sms', 'STRING');
  osc_set_preference('site_name', '', 'plugin-sms', 'STRING');
  osc_set_preference('verification_account', 0, 'plugin-sms', 'INTEGER');
  osc_set_preference('verification_listing', 0, 'plugin-sms', 'INTEGER');
  osc_set_preference('verification_identifier', 'input[name="s_phone_mobile"], input[name="sPhone"]', 'plugin-sms', 'STRING');
  osc_set_preference('verification_countries', 'us,gb,de', 'plugin-sms', 'STRING');
  osc_set_preference('initial_country', '', 'plugin-sms', 'STRING');
  osc_set_preference('custom_field_phone', '', 'plugin-sms', 'STRING');
  osc_set_preference('premium_groups', '', 'plugin-sms', 'STRING');
  osc_set_preference('verify_phone', 0, 'plugin-sms', 'INTEGER');
  osc_set_preference('ignore_first_zero', 1, 'plugin-sms', 'INTEGER');
  osc_set_preference('ignore_first_zero_prefixes', '', 'plugin-sms', 'STRING');

  osc_set_preference('notify_user_account_validate', 0, 'plugin-sms', 'INTEGER');
  osc_set_preference('notify_user_item_validate', 0, 'plugin-sms', 'INTEGER');
  osc_set_preference('notify_user_item_activated', 0, 'plugin-sms', 'INTEGER');
  osc_set_preference('notify_user_item_post', 0, 'plugin-sms', 'INTEGER');
  osc_set_preference('notify_user_item_contact', 0, 'plugin-sms', 'INTEGER');
  osc_set_preference('notify_user_reset_password', 0, 'plugin-sms', 'INTEGER');

  osc_set_preference('notify_admin_contact_form', 0, 'plugin-sms', 'INTEGER');
  osc_set_preference('notify_admin_item_post', 0, 'plugin-sms', 'INTEGER');
  osc_set_preference('notify_admin_register', 0, 'plugin-sms', 'INTEGER');

  osc_set_preference('ringcaptcha_app_key', '', 'plugin-sms', 'STRING');
  osc_set_preference('ringcaptcha_api_key', '', 'plugin-sms', 'STRING');

  osc_set_preference('twilio_account_sid', '', 'plugin-sms', 'STRING');
  osc_set_preference('twilio_auth_token', '', 'plugin-sms', 'STRING');
  osc_set_preference('twilio_number', '', 'plugin-sms', 'STRING');

  osc_set_preference('way2sms_username', '', 'plugin-sms', 'STRING');
  osc_set_preference('way2sms_password', '', 'plugin-sms', 'STRING');
  
  osc_set_preference('textlocalin_api_key', '', 'plugin-sms', 'STRING');
  osc_set_preference('textlocalin_password', '', 'plugin-sms', 'STRING');
  osc_set_preference('textlocalin_sender_id', '', 'plugin-sms', 'STRING');
  
  osc_set_preference('msg91_api_key', '', 'plugin-sms', 'STRING');
  osc_set_preference('msg91_sender_id', '', 'plugin-sms', 'STRING');
  
  osc_set_preference('budgetsms_user_id', '', 'plugin-sms', 'STRING');
  osc_set_preference('budgetsms_username', '', 'plugin-sms', 'STRING');
  osc_set_preference('budgetsms_handle', '', 'plugin-sms', 'STRING');
  osc_set_preference('budgetsms_from', '', 'plugin-sms', 'STRING');
  
  osc_set_preference('plivo_smsh_id', '', 'plugin-sms', 'STRING');
  osc_set_preference('plivo_smsh_token', '', 'plugin-sms', 'STRING');
  osc_set_preference('plivo_sender_id', '', 'plugin-sms', 'STRING');
  
  osc_set_preference('notify_user_id', '', 'plugin-sms', 'STRING');
  osc_set_preference('notify_api_key', '', 'plugin-sms', 'STRING');
  osc_set_preference('notify_sender_id', '', 'plugin-sms', 'STRING');

  // sms-assistent.by
  osc_set_preference('assistent_api_username', '', 'plugin-sms', 'STRING');
  osc_set_preference('assistent_api_pass', '', 'plugin-sms', 'STRING');
  osc_set_preference('assistent_api_sender', '', 'plugin-sms', 'STRING');
  
  osc_set_preference('routee_app_id', '', 'plugin-sms', 'STRING');
  osc_set_preference('routee_app_secret', '', 'plugin-sms', 'STRING');
  osc_set_preference('routee_from', '', 'plugin-sms', 'STRING');
  osc_set_preference('routee_country_code', '+234', 'plugin-sms', 'STRING');

  
  ModelSMS::newInstance()->install();
}


function sms_call_after_uninstall() {
  ModelSMS::newInstance()->uninstall();
}



// VERIFICATION AJAX
function sms_init() {
  if(Params::getParam('smsAjax') == 1) {
    http_response_code(200);
    header("Content-Type: application/json", true);

    $type = Params::getParam('type'); // USER or ITEM

    // STEP 1: PREPARE VERIFICATION CODE AND SEND IT
    if(Params::getParam('step') == 1) {
      $provider = sms_param('provider');
      $provider_alt = sms_param('provider_alt');
      $is_backup = false;
      $primary_issue = '';

      $phone_number = sms_prepare_number(Params::getParam('phoneNumber'));
      $item_id = Params::getParam('itemId');
      $item = Item::newInstance()->findByPrimaryKey($item_id);

      if($phone_number !== false) {
        $user = array();

        if($type == 'USER') {
          $user_id = (osc_logged_user_id() > 0 ? osc_logged_user_id() : Params::getParam('userId'));
          $user = User::newInstance()->findByPrimaryKey($user_id);

          // Check if phone that entered user, if not matching to already saved phone, does not already belong to different user 
          if(sms_param('verify_phone') == 1 && isset($user['pk_i_id'])) {
            if(PHONE_CHECK_ADVANCED) {
              $phone = sms_prepare_number(trim(Params::getParam('phoneNumber')), '', true);
              $current_phone = sms_prepare_number(trim($user['s_phone_mobile']), '', true);
            } else {
              $phone = trim(Params::getParam('phoneNumber'));
              $current_phone = trim($user['s_phone_mobile']);
            }
            
            if($phone <> '' && ($current_phone == '' || $current_phone != $phone)) {
              $other_user = ModelSMS::newInstance()->findUserByPhone($phone, PHONE_CHECK_ADVANCED);

              if($other_user !== false && isset($other_user['pk_i_id']) && $other_user['pk_i_id'] != $user_id) {
                echo json_encode(array('status' => 'ERROR', 'code' => '', 'message' => osc_esc_js(sprintf(__('Phone number %s already belongs to other user. Please use different phone number!', 'sms'), trim(Params::getParam('phoneNumber'))))));
                exit;
              }
            }
          }

          // Update user phone number to recent one
          ModelSMS::newInstance()->updateUser(
            array(
              'pk_i_id' => $user_id, 
              's_phone_mobile' => $phone_number
            )
          );

          $user_name = isset($user['s_name']) ? $user['s_name'] : '';

        } else { 
          ModelSMS::newInstance()->updateItemPhone(
            array(
              'fk_i_item_id' => $item_id, 
              's_phone' => $phone_number
            )
          );

          $user_name = $item['s_contact_name'];
        }

        $code = mb_generate_rand_int(4);

        $data = array(
          's_phone_number' => $phone_number,
          's_email' => @$user['s_email'],
          's_provider' => strtoupper($provider),
          's_token' => $code,
          's_status' => 'PENDING'
        );

        $message = sprintf(__('Hi %s, your %s verification code is: %s', 'sms'), $user_name, sms_param('site_name'), $code);
        $output = sms_send($phone_number, $message, 'VALIDATE_' . $type, '', $code);

        // backup gateway if there was an issue
        if($output['status'] == 'ERROR' && $provider_alt <> '') {
          $primary_issue = osc_esc_js(sprintf(__('%s: Problem to send SMS: %s', 'sms'), strtoupper(!$is_backup ? $provider : $provider_alt), $output['message'])) . '<br/>';

          $output = sms_send($phone_number, $message, 'VALIDATE_' . $type, $provider_alt, $code);
          $data['s_provider'] = $provider_alt;
          $is_backup = true;
        }

        ModelSMS::newInstance()->createVerification($data);

        if($output['status'] == 'ERROR') {
          echo json_encode(array('status' => 'ERROR', 'code' => '', 'message' => $primary_issue . osc_esc_js(sprintf(__('%s: Problem to send SMS: %s', 'sms'), strtoupper(!$is_backup ? $provider : $provider_alt), $output['message']))));
        } else {
          echo json_encode(array('status' => 'OK', 'code' => (osc_is_admin_user_logged_in() ? $code : ($provider == 'demo' ? $code : '')), 'message' => ($provider == 'demo' ? sprintf(__('SMS Verification is in demo mode, please enter following code to proceed verification: %s', 'sms'), $code) : '')));
        }

      } else {
        echo json_encode(array('status' => 'ERROR', 'code' => '', 'message' => osc_esc_js(__('Phone number is not valid', 'sms'))));
      }

    }


    // STEP 2: VERIFY ENTERED CODE
    if(Params::getParam('step') == 2) {
      $phone_number = sms_prepare_number(Params::getParam('phoneNumber'));
      $item_id = Params::getParam('itemId');
      $item = Item::newInstance()->findByPrimaryKey($item_id);
      $user_id = (osc_logged_user_id() > 0 ? osc_logged_user_id() : Params::getParam('userId'));
      $user = User::newInstance()->findByPrimaryKey($user_id);
      
      $email = (isset($user['s_email']) ? $user['s_email'] : (isset($item['s_contact_email']) ? $item['s_contact_email'] : osc_logged_user_email()));

      if($type == 'USER') {
        ModelSMS::newInstance()->updateUser(
          array(
            'pk_i_id' => $user_id, 
            's_phone_mobile' => $phone_number
          )
        );
      } else { 
        ModelSMS::newInstance()->updateItemPhone(
          array(
            'fk_i_item_id' => $item_id, 
            's_phone' => $phone_number
          )
        );
      }

      $code = (Params::getParam('code1') . Params::getParam('code2') . Params::getParam('code3') . Params::getParam('code4'));

      if($phone_number !== false && $code <> '' && strlen($code) == 4) {
        $status = ModelSMS::newInstance()->verifyNumber($phone_number, $code);
        
        $data = array(
          's_phone_number' => $phone_number, 
          's_status' => 'VERIFIED'
        );
        
        if($email != '') {
          $data['s_email'] = $email; 
        }

        if($status) {
          ModelSMS::newInstance()->cancelPreviousVerification($phone_number, $email);
          ModelSMS::newInstance()->cancelPreviousUserVerification($phone_number, $email);
          ModelSMS::newInstance()->updateVerification($data, true);
          ModelSMS::newInstance()->updateItem(array('b_active' => 1, 'pk_i_id' => $item_id));

          $url = ($type == 'USER' ? osc_user_dashboard_url() : osc_item_url_from_item($item));

          echo json_encode(array('status' => 'OK', 'code' => '', 'message' => osc_esc_js(__('Phone number verified. Thank you!', 'sms')), 'url' => $url));

        } else {
          echo json_encode(array('status' => 'ERROR', 'code' => '', 'message' => osc_esc_js(__('Verification has failed, verification code does not match!', 'sms'))));
        }
      } else {
        echo json_encode(array('status' => 'ERROR', 'code' => '', 'message' => osc_esc_js(__('Phone number or verification code is not valid!', 'sms'))));
      }
    }
   
    exit;
  }
}

osc_add_hook('init', 'sms_init');


// WHEN NEW LISTING IS POSTED, CHECK PHONE
function sms_posted_item_check($item) {
  
  // No redirect for backoffice edits
  if(defined('OC_ADMIN') && OC_ADMIN === true) {
    return false;
  }
  
  $phone_number = sms_prepare_number(sms_item_phone_number($item['pk_i_id']));

  if(sms_phone_verify($phone_number) === false && sms_param('verification_listing') == 1) {
    ModelSMS::newInstance()->updateItem(array('b_active' => 0, 'pk_i_id' => $item['pk_i_id']));

    osc_add_flash_info_message(__('In order to show listing, you must verify your phone number', 'sms'));
    header('Location:' . osc_route_url('sms-item-verify', array('itemId' => $item['pk_i_id'])));
    exit;

  } else if(sms_phone_verify($phone_number) !== false && sms_param('verification_listing') == 1 && $item['b_active'] == 0) {
    ModelSMS::newInstance()->updateItem(array('b_active' => 1, 'pk_i_id' => $item['pk_i_id']));
  }

}

osc_add_hook('posted_item', 'sms_posted_item_check', 10);
osc_add_hook('edited_item', 'sms_posted_item_check', 10);


// WHEN LISTING IS BEING ACTIVATED, CHECK PHONE
function sms_activate_item_check($item_id) {
  // No redirect for backoffice edits
  if(defined('OC_ADMIN') && OC_ADMIN === true) {
    return false;
  }
  
  $phone_number = sms_prepare_number(sms_item_phone_number($item_id));

  if(sms_phone_verify($phone_number) === false && sms_param('verification_listing') == 1) {
    ModelSMS::newInstance()->updateItem(array('b_active' => 0, 'pk_i_id' => $item_id));

    osc_add_flash_info_message(__('You cannot activate listing, you must verify your phone number first', 'sms'));
    header('Location:' . osc_route_url('sms-item-verify', array('itemId' => $item_id)));
    exit;
  }
}

osc_add_hook('activate_item', 'sms_activate_item_check', 10);



// WHEN USER LOGIN INTO IT'S PROFILE AND PHONE NUMBER IS NOT VERIFIED, REQUIRE VERIFICATION IN USER ACCOUNT PAGES
function sms_user_verify() {
  
  // No redirect for backoffice edits
  if(defined('OC_ADMIN') && OC_ADMIN === true) {
    return false;
  }
  
  $location = Rewrite::newInstance()->get_location();
  $section = Rewrite::newInstance()->get_section();
  $route = Params::getParam('route');

  if(osc_is_web_user_logged_in() && (osc_is_publish_page() || in_array($location, array('user', 'im')) || in_array($route, array('osp-item'))) && sms_param('verification_account') == 1) { 
    $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());

    if(sms_phone_verify($user['s_phone_mobile']) === false || $user['s_phone_mobile'] == '') {
      if($location <> 'sms' || $section <> 'user-verify') {
        osc_add_flash_info_message(__('In order to improve our services, you must verify your phone number', 'sms'));
        header('Location:' . osc_route_url('sms-user-verify', array('userId' => osc_logged_user_id())));
        exit;
      }
    }
  }
}

osc_add_hook('init', 'sms_user_verify', 10);


// WHEN USER REGISTER OR EDIT IT'S PROFILE, REQUIRE PHONE VERIFICATION
function sms_user_verify_after_reg($user_id) {
  
  // No redirect for backoffice edits
  if(defined('OC_ADMIN') && OC_ADMIN === true) {
    return false;
  }
  
  $user = User::newInstance()->findByPrimaryKey($user_id);

  if(osc_is_web_user_logged_in() && (!sms_phone_verify($user['s_phone_mobile']) || $user['s_phone_mobile'] == '') && sms_param('verification_account') == 1) {
    osc_add_flash_info_message(__('In order to improve our services, you must verify your phone number', 'sms'));
    header('Location:' . osc_route_url('sms-user-verify', array('userId' => $user_id)));
    exit;
  }
}

osc_add_hook('user_register_completed', 'sms_user_verify_after_reg', 10);
osc_add_hook('user_edit_completed', 'sms_user_verify_after_reg', 10);


// ADD JQUERY FUNCTIONALITY FOR PHONE NUMBER BOX
function sms_js() {
  $location = Rewrite::newInstance()->get_location();
  $section  = Rewrite::newInstance()->get_section();


  //if(sms_param('verification_identifier') <> '') {
    if(((osc_is_web_user_logged_in() && $location == 'user' || osc_is_register_page() || osc_is_login_page()) && sms_param('verification_account') == 1) || ((osc_is_publish_page() || osc_is_edit_page()) && sms_param('verification_listing') == 1) || ($location == 'sms' && $section == 'item-verify' && sms_param('verification_listing') == 1) || $location == 'login' && $section == 'recover' || ($location == 'sms' && $section == 'user-verify' && sms_param('verification_account') == 1)) { 
    ?>
    <script>
      $(document).ready(function() {
        $('<?php echo sms_js_selector(); ?>').intlTelInput({
          <?php if(sms_param('verification_countries') <> '') { ?>preferredCountries: [<?php echo '"' . implode('","', array_filter(array_map('trim', explode(',', sms_param('verification_countries'))))) . '"'; ?>], <?php } ?>
          utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/13.0.4/js/utils.js",
          initialCountry: '<?php echo (sms_param('initial_country') <> '' ? sms_param('initial_country') : 'auto'); ?>',
          <?php if(sms_param('only_country') <> '') { ?>onlyCountries: ["<?php echo implode('", "', array_filter(explode(',', sms_param('only_country')))); ?>"],<?php } ?>
          <?php if(@count(array_filter(explode(',', sms_param('only_country')))) == 1) { ?>allowDropdown: false,<?php } ?>
          autoFormat: true,
          nationalMode: false,
          <?php if(sms_param('geo_ip_lookup') == 1) { ?>
          geoIpLookup: function(success, failure) {
            $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
              var countryCode = (resp && resp.country) ? resp.country : "";
              success(countryCode);
            });
          }
          <?php } ?>
        });


        var errorMap = [ "Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];


        $('body').on('change keyup load', $('<?php echo sms_js_selector(); ?>'), function() {
          $('<?php echo sms_js_selector(); ?>').removeClass("error"); 
          $('.sms-validation').hide(0).text('');

          $('.intl-tel-input').each(function() {
            if(!$(this).find('.sms-validation').length) {
              $(this).append('<em class="sms-validation"></em>');
            } 
          });


          if ($('<?php echo sms_js_selector(); ?>').val()) {
            if ($('<?php echo sms_js_selector(); ?>').intlTelInput('isValidNumber')) {
              $('<?php echo sms_js_selector(); ?>').removeClass("error").addClass('valid');
              $('.sms-validation').hide(0).text('');

            } else {
              $('<?php echo sms_js_selector(); ?>').addClass('error').removeClass('valid');
              var errorCode = $('<?php echo sms_js_selector(); ?>').intlTelInput('getValidationError');

              if(errorCode != -99) {
                $('.sms-validation').show(0).text(errorMap[errorCode]);
              }
            }
          }
        });

        $('<?php echo sms_js_selector(); ?>').prop('required', true);
      });
    </script>

    <?php
    }
  //}
}

osc_add_hook('footer', 'sms_js');

function sms_js_selector() {
  $output = 'input[name="phoneNumber"]:not([type="hidden"]), input[name="s_phone_mobile"]';
  
  if(sms_param('custom_field_phone') <> '') {
    $output .= ',input[id="meta_' . sms_param('custom_field_phone') . '"]';
  }

  if(sms_param('verification_identifier') <> '') {
    $output .= ',' . sms_param('verification_identifier');
  }

  return $output;
}


// ADMIN MENU
function sms_menu($title = NULL) {
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/sms/css/admin.css?v=' . date('YmdHis') . '" rel="stylesheet" type="text/css" />';
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/sms/css/bootstrap-switch.css" rel="stylesheet" type="text/css" />';
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/sms/css/tipped.css" rel="stylesheet" type="text/css" />';
  echo '<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/sms/js/admin.js?v=' . date('YmdHis') . '"></script>';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/sms/js/tipped.js"></script>';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/sms/js/bootstrap-switch.js"></script>';



  if( $title == '') { $title = __('Configure', 'sms'); }

  $text  = '<div class="mb-head">';
  $text .= '<div class="mb-head-left">';
  $text .= '<h1>' . $title . '</h1>';
  $text .= '<h2>SMS Verification and Notification Plugin</h2>';
  $text .= '</div>';
  $text .= '<div class="mb-head-right">';
  $text .= '<ul class="mb-menu">';
  $text .= '<li><a href="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=sms/admin/configure.php"><i class="fa fa-wrench"></i><span>' . __('Configure', 'sms') . '</span></a></li>';
  $text .= '<li><a href="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=sms/admin/provider.php"><i class="fa fa-send"></i><span>' . __('SMS Providers', 'sms') . '</span></a></li>';
  $text .= '<li><a href="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=sms/admin/log_sms.php"><i class="fa fa-database"></i><span>' . __('SMS Logs', 'sms') . '</span></a></li>';
  $text .= '<li><a href="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=sms/admin/log_verification.php"><i class="fa fa-address-book"></i><span>' . __('Verification Logs', 'sms') . '</span></a></li>';
  $text .= '</ul>';
  $text .= '</div>';
  $text .= '</div>';

  echo $text;
}



// ADMIN FOOTER
function sms_footer() {
  $pluginInfo = osc_plugin_get_info('sms/index.php');
  $text  = '<div class="mb-footer">';
  $text .= '<a target="_blank" class="mb-developer" href="https://osclasspoint.com"><img src="https://osclasspoint.com/favicon.ico" alt="OsclassPoint Market" /> OsclassPoint Market</a>';
  $text .= '<a target="_blank" href="' . $pluginInfo['support_uri'] . '"><i class="fa fa-bug"></i> ' . __('Report Bug', 'sms') . '</a>';
  $text .= '<a target="_blank" href="https://forums.osclasspoint.com/"><i class="fa fa-handshake-o"></i> ' . __('Support Forums', 'sms') . '</a>';
  $text .= '<a target="_blank" class="mb-last" href="mailto:info@osclasspoint.com"><i class="fa fa-envelope"></i> ' . __('Contact Us', 'sms') . '</a>';
  $text .= '<span class="mb-version">v' . $pluginInfo['version'] . '</span>';
  $text .= '</div>';

  return $text;
}



// ADD MENU LINK TO PLUGIN LIST
function sms_admin_menu() {
echo '<h3><a href="#">SMS Verification and Notification Plugin</a></h3>
<ul> 
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/configure.php') . '">&raquo; ' . __('Configure', 'sms') . '</a></li>
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/provider.php') . '">&raquo; ' . __('SMS Providers', 'sms') . '</a></li>
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/log_sms.php') . '">&raquo; ' . __('SMS Logs', 'sms') . '</a></li>
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/log_verification.php') . '">&raquo; ' . __('Verification Logs', 'sms') . '</a></li>
</ul>';
}


// ADD MENU TO PLUGINS MENU LIST
osc_add_hook('admin_menu','sms_admin_menu', 1);



// DISPLAY CONFIGURE LINK IN LIST OF PLUGINS
function sms_conf() {
  osc_admin_render_plugin( osc_plugin_path( dirname(__FILE__) ) . '/admin/configure.php' );
}

osc_add_hook( osc_plugin_path( __FILE__ ) . '_configure', 'sms_conf' );	


// CALL WHEN PLUGIN IS ACTIVATED - INSTALLED
osc_register_plugin(osc_plugin_path(__FILE__), 'sms_call_after_install');

// SHOW UNINSTALL LINK
osc_add_hook(osc_plugin_path(__FILE__) . '_uninstall', 'sms_call_after_uninstall');

?>