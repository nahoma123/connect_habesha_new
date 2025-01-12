<?php

// WHEN NEW USER REGISTER, CHECK IF PHONE DOES NOT EXIST
function phl_check_register() {
  $phone_original = trim(Params::getParam('s_phone_mobile'));
  $is_backoffice = false;
  
  if(defined('OC_ADMIN') && OC_ADMIN == true) {
    $is_backoffice = true;
  }

  if($is_backoffice) {
    $current_user_id = Params::getParam('id');
  } else {
    $current_user_id = osc_logged_user_id();
  }
  
  if(PHL_PHONE_CHECK_ADVANCED == false) {
    $phone = $phone_original;
  } else {
    $phone = phl_prepare_number($phone_original, true);
  }
  
  if($phone <> '') {
    $user = ModelPHL::newInstance()->findUserByPhone($phone, PHL_PHONE_CHECK_ADVANCED);

    if($user && isset($user['pk_i_id']) && $user['pk_i_id'] != $current_user_id) {
      osc_add_flash_error_message(sprintf(__('Phone number %s is already registered to another account, please use different number', 'phone_login'), $phone_original), $is_backoffice ? 'admin' : 'pubMessages');

      if($is_backoffice) {
        header('Location:' . osc_admin_base_url(true) . '?page=users&action=edit&id=' . $user['pk_i_id']);
      } else {
        if(osc_is_web_user_logged_in()) {
          header('Location:' . osc_user_profile_url());
        } else {
          header('Location:' . osc_register_account_url());
        }
      }
      
      exit;
    }
  }
  
  $phone_land_original = trim(Params::getParam('s_phone_land'));

  if(PHL_PHONE_CHECK_ADVANCED == false) {
    $phone_land = $phone_land_original;
  } else {
    $phone_land = phl_prepare_number($phone_land_original, true);
  }
  
  if($phone_land <> '') {
    $user = ModelPHL::newInstance()->findUserByPhone($phone_land, PHL_PHONE_CHECK_ADVANCED);

    if($user && isset($user['pk_i_id']) && $user['pk_i_id'] != $current_user_id) {
      osc_add_flash_error_message(sprintf(__('Phone number %s is already registered to another account, please use different number', 'phone_login'), $phone_land_original), $is_backoffice ? 'admin' : 'pubMessages');

      if($is_backoffice) {
        header('Location:' . osc_admin_base_url(true) . '?page=users&action=edit&id=' . $user['pk_i_id']);
      } else {
        if(osc_is_web_user_logged_in()) {
          header('Location:' . osc_user_profile_url());
        } else {
          header('Location:' . osc_register_account_url());
        }
      }
      
      exit;
    }
  }
}

//osc_add_hook('before_user_register', 'phl_check_register');
osc_add_hook('pre_user_post', 'phl_check_register', 2);



// CREATE VALID PHONE NUMBER
function phl_prepare_number($phone_number, $unique_check = false) {
  //$phone_number = preg_replace('/\s+/', '', $phone_number);
  $phone_number = ltrim($phone_number, '0');
  $phone_number = str_replace('/', '', $phone_number);
  $phone_number = str_replace('(', '', $phone_number);
  $phone_number = str_replace(')', '', $phone_number);
  $phone_number = str_replace('-', '', $phone_number);
  $phone_number = str_replace(' ', '', $phone_number);

  $phone_number = filter_var($phone_number, FILTER_SANITIZE_NUMBER_INT);
  
  if($unique_check) {
    $phone_number = str_replace('+', '', $phone_number);
    $phone_number = ltrim($phone_number, '0');
    return $phone_number;
  }

  if((strlen($phone_number) < 9 || strlen($phone_number) > 16)) {
    return false;
  } else if(substr($phone_number, 0, 1) <> '+') {
    return '+' . $phone_number;
  } else {
    return $phone_number;
  }
}  


// ADD PHONE NUMBER TO REGISTER FORM
function phl_form_phone() {
  if(phl_param('hook_phone') == 1) { 
    $html  = '<div class="control-group phl-mobile">';
    $html .= '<label class="control-label" for="s_phone_mobile">' . __('Mobile phone', 'phone_login') . '</label>';
    $html .= '<div class="controls">';
    $html .= '<input id="s_phone_mobile" type="text" name="s_phone_mobile" value="' . Params::getParam('s_phone_mobile') . '" autocomplete="off">';
    //$html .= '<p id="phl_form_error" style="display:none;"></p>';
    $html .= '</div>';
    $html .= '</div>';

    echo $html;
  }
}

osc_add_hook('user_register_form', 'phl_form_phone');



// LOGIN USER
function phl_login_user() {
  if(phl_param('enable') == 1 && Params::getParam('page') == 'login' && Params::getParam('action') == 'login_post' && Params::getParam('fbLogin') <> 1) {
    $user = array();
    $email_phone = trim(Params::getParam('email'));
    $password = Params::getParam('password', false, false);
    $wrongCredentials = false;
    $url_redirect = '';


    if ($email_phone == '') {
      osc_add_flash_error_message(__('Please provide an email address', 'phone_login'));
      $wrongCredentials = true;
    }

    if ($password == '') {
      osc_add_flash_error_message(__('Empty passwords are not allowed. Please provide a password', 'phone_login') );
      $wrongCredentials = true;
    }

    if ($wrongCredentials) {
      header('Location:' . osc_user_login_url());
      exit;
    }


    if(osc_validate_email($email_phone)) {
      $user = User::newInstance()->findByEmail($email_phone);
    }

    if (empty($user)) {
      $user = User::newInstance()->findByUsername($email_phone);
    }


    if (empty($user)) {
      $user = ModelPHL::newInstance()->findUser($email_phone);
    }

    if (empty($user)) {
      osc_add_flash_error_message(__('The user doesn\'t exist', 'phone_login'));
      header('Location:' . osc_user_login_url());
      exit;
    }


    if (!osc_verify_password($password, (isset($user['s_password']) ? $user['s_password'] : ''))) {
      osc_add_flash_error_message(__('The password is incorrect', 'phone_login'));
      header('Location:' . osc_user_login_url());
      exit;
    }


    $banned = osc_is_banned($email_phone); // int 0: not banned or unknown, 1: email is banned, 2: IP is banned, 3: both email & IP are banned

    if($banned == 1) {
      osc_add_flash_error_message(__('Your current email is not allowed', 'phone_login'));
    } 

    if($banned == 2) {
      osc_add_flash_error_message(__('Your current IP is not allowed', 'phone_login'));
    } 

    if($banned == 3) {
      osc_add_flash_error_message(__('Your current IP and email is not allowed', 'phone_login'));
    } 


    if($banned !== 0) {
      header('Location:' . osc_user_login_url());
      exit;
    }

    osc_run_hook('before_login');

    require_once LIB_PATH . 'osclass/UserActions.php';
    $uActions = new UserActions(false);
    $logged = $uActions->bootstrap_login($user['pk_i_id']);


    if($logged == 0) {
      osc_add_flash_error_message(__('The user doesn\'t exist', 'phone_login'));

    } else if($logged == 1) {
      if((time() - strtotime($user['dt_access_date'])) > 1200) { // EACH 20 MINUTES
        osc_add_flash_error_message(sprintf(__('The user has not been validated yet. Would you like to re-send your <a href="%s">activation?</a>', 'phone_login'), osc_user_resend_activation_link($user['pk_i_id'], $user['s_email'])));
      } else {
        osc_add_flash_error_message(__('The user has not been validated yet', 'phone_login'));
      }

    } else if($logged==2) {
      osc_add_flash_error_message(__('The user has been suspended', 'phone_login'));

    } else if($logged==3) {
      if (Params::getParam('remember') == 1) {
        //this include contains de osc_genRandomPassword function
        require_once osc_lib_path() . 'osclass/helpers/hSecurity.php';
        $secret = osc_genRandomPassword();
        User::newInstance()->update(
          array('s_secret' => $secret),
          array('pk_i_id' => $user['pk_i_id'])
        );

        Cookie::newInstance()->set_expires(osc_time_cookie());
        Cookie::newInstance()->push('oc_userId', $user['pk_i_id']);
        Cookie::newInstance()->push('oc_userSecret', $secret);
        Cookie::newInstance()->set();
      }

      if($url_redirect == '') {
        $url_redirect = osc_user_dashboard_url();
      }

      osc_run_hook('after_login', $user, $url_redirect);

      header('Location:' . osc_apply_filter('correct_login_url_redirect', $url_redirect));
      exit;

    } else {
      osc_add_flash_error_message(__('Something went wrong', 'phone_login'));
    }

    header('Location:' . osc_base_url());
    exit;
  }
}


osc_add_hook('before_validating_login', 'phl_login_user', 10);


// ADD JS VARIABLES TO FOOTER
function phl_footer_js() {
  if(phl_is_auth() == 1) {
    $html = '<script>';
    $html .= 'var phlEnable=' . (phl_param('enable') == 1 ? 1 : 0) . ';';
    $html .= 'var phlIsLogin=' . (phl_is_auth() == 1 ? 1 : 0) . ';';
    $html .= 'var phlEmailLabel="' . osc_esc_js(__('Email/Phone', 'phone_login')) . '";';
    $html .= '</script>';

    echo $html;
  }
}

osc_add_hook('footer', 'phl_footer_js');


function phl_is_auth() {
  if(osc_is_login_page()) {
    return 1;
  } else if (osc_is_register_page()) {
    return 1;
  } else {
    return 0;
  }
}


if(!function_exists('osc_is_login_page')) {
  function osc_is_login_page() {
    return osc_is_current_page('login', '');
  }
}

if(!function_exists('osc_is_register_page')) {
  function osc_is_register_page() {
    return osc_is_current_page("register", "register");
  }
}




// CORE FUNCTIONS
function phl_param($name) {
  return osc_get_preference($name, 'plugin-phone_login');
}


if(!function_exists('mb_param_update')) {
  function mb_param_update( $param_name, $update_param_name, $type = NULL, $plugin_var_name = NULL ) {
  
    $val = '';
    if( $type == 'check') {

      // Checkbox input
      if( Params::getParam( $param_name ) == 'on' ) {
        $val = 1;
      } else {
        if( Params::getParam( $update_param_name ) == 'done' ) {
          $val = 0;
        } else {
          $val = ( osc_get_preference( $param_name, $plugin_var_name ) != '' ) ? osc_get_preference( $param_name, $plugin_var_name ) : '';
        }
      }
    } else {

      // Other inputs (text, password, ...)
      if( Params::getParam( $update_param_name ) == 'done' && Params::existParam($param_name)) {
        $val = Params::getParam( $param_name );
      } else {
        $val = ( osc_get_preference( $param_name, $plugin_var_name) != '' ) ? osc_get_preference( $param_name, $plugin_var_name ) : '';
      }
    }


    // If save button was pressed, update param
    if( Params::getParam( $update_param_name ) == 'done' ) {

      if(osc_get_preference( $param_name, $plugin_var_name ) == '') {
        osc_set_preference( $param_name, $val, $plugin_var_name, 'STRING');  
      } else {
        $dao_preference = new Preference();
        $dao_preference->update( array( "s_value" => $val ), array( "s_section" => $plugin_var_name, "s_name" => $param_name ));
        osc_reset_preferences();
        unset($dao_preference);
      }
    }

    return $val;
  }
}


// CHECK IF RUNNING ON DEMO
function phl_is_demo() {
  if(osc_logged_admin_username() == 'admin') {
    return false;
  } else if(isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'],'mb-themes') !== false || strpos($_SERVER['HTTP_HOST'],'abprofitrade') !== false)) {
    return true;
  } else {
    return false;
  }
}


if(!function_exists('message_ok')) {
  function message_ok( $text ) {
    $final  = '<div class="flashmessage flashmessage-ok flashmessage-inline">';
    $final .= $text;
    $final .= '</div>';
    echo $final;
  }
}


if(!function_exists('message_error')) {
  function message_error( $text ) {
    $final  = '<div class="flashmessage flashmessage-error flashmessage-inline">';
    $final .= $text;
    $final .= '</div>';
    echo $final;
  }
}


if( !function_exists('osc_is_contact_page') ) {
  function osc_is_contact_page() {
    $location = Rewrite::newInstance()->get_location();
    $section = Rewrite::newInstance()->get_section();
    if( $location == 'contact' ) {
      return true ;
    }

    return false ;
  }
}


// COOKIES WORK
if(!function_exists('mb_set_cookie')) {
  function mb_set_cookie($name, $val) {
    Cookie::newInstance()->set_expires( 86400 * 30 );
    Cookie::newInstance()->push($name, $val);
    Cookie::newInstance()->set();
  }
}


if(!function_exists('mb_get_cookie')) {
  function mb_get_cookie($name) {
    return Cookie::newInstance()->get_value($name);
  }
}

if(!function_exists('mb_drop_cookie')) {
  function mb_drop_cookie($name) {
    Cookie::newInstance()->pop($name);
  }
}


if(!function_exists('mb_generate_rand_string')) {
  function mb_generate_rand_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
  }
}


?>