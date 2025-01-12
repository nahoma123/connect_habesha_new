<?php
// Load Twilio
require_once 'src/twilio-php-master/Twilio/autoload.php';
use Twilio\Rest\Client;

require_once 'src/sms-assistent-by/errors.php';


// CHECK IF USER IS VERIFIED
function sms_check_user_verified($user_id = NULL) {
  if($user_id === NULL) {
    $user_id = osc_logged_user_id();
  }

  if($user_id > 0) { 
    $user = User::newInstance()->findByPrimaryKey($user_id);

    if(isset($user['s_phone_mobile']) && trim((string)$user['s_phone_mobile']) != '' && sms_phone_verify($user['s_phone_mobile'])) {
      return true;
    }
  }
  
  return false;
}


// CREATE LIST OF PROVIDERS
function sms_providers() {
  return 'demo,gatewayapi.com,twilio,textlocal.in,textlocal.com,budgetsms,way2sms,plivo,ringcaptcha,msg91,notify.lk,smsassistent.by,routee.net';
}


// WHEN NEW USER REGISTER, CHECK IF PHONE DOES NOT EXIST
function sms_check_register() {
  $is_backoffice = false;
  if(defined('OC_ADMIN') && OC_ADMIN == true) {
    $is_backoffice = true;
  }

  if($is_backoffice) {
    $current_user_id = Params::getParam('id');
  } else {
    $current_user_id = osc_logged_user_id();
  }

  if(sms_param('verify_phone') == 1) {
    if(PHONE_CHECK_ADVANCED) {
      $phone = sms_prepare_number(trim(Params::getParam('s_phone_mobile')), '', true);
    } else {
      $phone = trim(Params::getParam('s_phone_mobile'));
    }
    
    if($phone <> '') {
      $user = ModelSMS::newInstance()->findUserByPhone($phone, PHONE_CHECK_ADVANCED);

      if($user && isset($user['pk_i_id']) && $user['pk_i_id'] != $current_user_id) {
        osc_add_flash_error_message(sprintf(__('Phone number %s is already registered to another account, please use different number', 'sms'), $phone), $is_backoffice ? 'admin' : 'pubMessages');

        if(defined('OC_ADMIN') && OC_ADMIN == true) {
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

    if(PHONE_CHECK_ADVANCED) {
      $phone_land = sms_prepare_number(trim(Params::getParam('s_phone_land')), '', true);
    } else {
      $phone_land = trim(Params::getParam('s_phone_land'));
    }

    if($phone_land <> '') {
      $user = ModelSMS::newInstance()->findUserByPhone($phone_land, PHONE_CHECK_ADVANCED);

      if($user && isset($user['pk_i_id']) && $user['pk_i_id'] != $current_user_id) {
        osc_add_flash_error_message(sprintf(__('Phone number %s is already registered to another account, please use different number', 'sms'), $phone_land), $is_backoffice ? 'admin' : 'pubMessages');

        if(defined('OC_ADMIN') && OC_ADMIN == true) {
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
}

//osc_add_hook('before_user_register', 'sms_check_register');
osc_add_hook('pre_user_post', 'sms_check_register');


// GET PREMIUM GROUPS
function sms_get_premium_groups() {
  if(function_exists('osp_param')) {
    if(osp_param('groups_enabled') == 1) {
      if(sms_param('premium_groups') <> '') {
        $groups = array_filter(array_map('trim', explode(',', sms_param('premium_groups'))));
        
        if(is_array($groups) && count($groups) > 0) {
          return $groups;
        }
      }
    }
  }

  return false;
}


// CHECK IF USER IS IN PREMIUM GROUP
function sms_check_user_group($user_id) {
  if(function_exists('osp_get_user_group')) {
    $groups = sms_get_premium_groups();

    if($groups !== false && is_array($groups) && count($groups) > 0) {
      if($user_id > 0) {
        $user_group = osp_get_user_group($user_id);

        if(!in_array($user_group, $groups) || $user_group <= 0) {
          return false;
        }
      } else {
        return false; 
      }
    }
  }
  
  return true;
}


// ADD INFORMATION TO OC-AMDIN USER TABLE
function sms_backoffice_phone_verified($row) {
  $phone = isset($row['phone']) ? $row['phone'] : '';
  
  if(isset($row['phone'])) {
    if($phone == '') {
      $row['phone'] .= '<div class="sms-bo-user-ver"><span class="sms-missing">' . __('Phone missing', 'sms') . '</span></div>';
    } else {
      $check = sms_phone_verify($phone);
      
      if($check === true) {
        $row['phone'] .= '<div class="sms-bo-user-ver"><span class="sms-ok">' . __('Verified', 'sms') . '</span></div>';
      } else {
        $row['phone'] .= '<div class="sms-bo-user-ver"><span class="sms-error">' . __('Not verified', 'sms') . '</span></div>';
      }
    }
  }
  
  return $row;
}

osc_add_filter('users_processing_row', 'sms_backoffice_phone_verified');


// SEND SMS FUNCTIONALITY
function sms_send($phone_number, $message, $action, $provider = '', $otp_code = '') {
  $error = false;
  $error_num = 0;
  $extra = array();
  
  $provider = ($provider <> '' ? $provider : sms_param('provider'));
  $phone_number = sms_prepare_number($phone_number, $provider);
  $response = '';
  
  if($provider == '') {
    return false;  
  } else if (!$phone_number) {
    return false;  
  }

  // DEMO PROVIDER
  if($provider == 'demo') {
    // nothing to do


  // SMS-ASSISTENT.BY
  } else if($provider == 'smsassistent.by') {
    $data = array(
      'user' => sms_param('assistent_api_username'), 
      'password' => sms_param('assistent_api_pass'), 
      'sender' => sms_param('assistent_api_sender'), 
      'message' => $message, 
      'recipient' => $phone_number, 
      'validity_period' => 48
    );

    $api_url = 'https://userarea.sms-assistent.by/api/v1.2/send_sms/plain';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);    


    $response = curl_exec($ch);
    $error_num = curl_errno($ch);
    $error = (curl_errno($ch) ? curl_error($ch) : false);
    curl_close($ch);


  } else if($provider == 'routee.net') {
    $access_token = sms_routee_get_access_token();
    
    $data = array(
      'from' => sms_param('routee_from'), 
      'body' => $message, 
      'to' => $phone_number
    );
    
    $ch = curl_init();

    curl_setopt_array($ch, array(
      CURLOPT_URL => "https://connect.routee.net/sms",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER => array(
        "authorization: Bearer {$access_token}",
        "content-type: application/json"
      ),
    ));
    
    $extra = $data;
    $response = curl_exec($ch);
    $error_num = curl_errno($ch);
    $error = (curl_errno($ch) ? curl_error($ch) : false);
    curl_close($ch);

  // NOTIFY.LK
  } else if($provider == 'notify.lk') {
    $data = array('user_id' => sms_param('notify_user_id'), 'api_key' => sms_param('notify_api_key'), 'sender_id' => (sms_param('notify_sender_id') <> '' ? sms_param('notify_sender_id') : 'NotifyDEMO'), 'message' => $message, 'to' => $phone_number);

    $ch = curl_init('https://app.notify.lk/api/v1/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $error_num = curl_errno($ch);
    $error = (curl_errno($ch) ? curl_error($ch) : false);
    curl_close($ch);

  
  // TWILIO
  } else if($provider == 'twilio') {
    $account_sid = sms_param('twilio_account_sid');        // Your Account SID and Auth Token from twilio.com/console
    $auth_token =  sms_param('twilio_auth_token');

    $twilio_number = sms_param('twilio_number');           // A Twilio number you own with SMS capabilities
    $twilio = new Client($account_sid, $auth_token);


    try {
      $response = $twilio->messages->create(
        $phone_number,
        array(
          'from' => $twilio_number,
          'body' => $message
        )
      );
      
    } catch(Exception $e){
      $response = $e;
      $error = $e->getMessage();
      $error_num = $e->getCode();
    }


  // TEXTLOCAL.IN
  } else if($provider == 'textlocal.in') {
    $data = array('apikey' => sms_param('textlocalin_api_key'), 'numbers' => $phone_number, 'sender' => sms_param('textlocalin_sender_id'), 'message' => $message);
    $ch = curl_init('https://api.textlocal.in/send/');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $error_num = curl_errno($ch);
    $error = (curl_errno($ch) ? curl_error($ch) : false);
    curl_close($ch);


  
  // TEXTLOCAL.COM
  } else if($provider == 'textlocal.com') {
    $api_key = urlencode(sms_param('textlocalcom_api_key'));
    $sender = urlencode(sms_param('textlocalcom_sender'));
    $message = rawurlencode($message);

    $data = array('apikey' => $api_key, 'numbers' => $phone_number, "sender" => $sender, "message" => $message);

    $ch = curl_init('https://api.txtlocal.com/send/');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $error_num = curl_errno($ch);
    $error = (curl_errno($ch) ? curl_error($ch) : false);
    curl_close($ch);


  // GATEWAYAPI.COM
  } else if($provider == 'gatewayapi.com') {
    $recipients = [$phone_number];
    $url = "https://gatewayapi.com/rest/mtsms";
    $api_token = sms_param('gatewayapi_api_token');
    $json = array(
       'sender' => sms_param('gatewayapi_sender'),
       'message' => $message,
       'recipients' => [],
    );

    foreach ($recipients as $msisdn) {
      $json['recipients'][] = ['msisdn' => $msisdn];
    }

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($ch,CURLOPT_USERPWD, $api_token.":");
    curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($json));
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $error_num = curl_errno($ch);
    $error = (curl_errno($ch) ? curl_error($ch) : false);
    curl_close($ch);
  
    
  // BUDGETSMS.NET  
  } else if ($provider == 'budgetsms') {
    $data = array('username' => sms_param('budgetsms_username'), 'userid' => sms_param('budgetsms_user_id'), 'handle' => sms_param('budgetsms_handle'), 'msg' => $message, 'from' => sms_param('budgetsms_from'), 'to'=> $phone_number);
    $ch = curl_init('https://api.budgetsms.net/sendsms/');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $error_num = curl_errno($ch);
    $error = (curl_errno($ch) ? curl_error($ch) : false);
    curl_close($ch);
    
    
  // WAY2SMS
  } else if ($provider == 'way2sms') {
    require_once 'src/way2sms-api.php';
    $response = sendWay2SMS(sms_param('way2sms_username'), sms_param('way2sms_password'), $phone_number, $message);
    
    
  // PLIVO
  } else if ($provider == 'plivo') {
    $url = 'https://api.plivo.com/v1/Account/' . sms_param('plivo_sms_hid') . '/Message/';
    $data = array('src' => sms_param('plivo_sender_id'), 'dst' => $phone_number, 'text' => $message);
    $data_string = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_USERPWD, sms_param('plivo_sms_hid') . ':' . sms_param('plivo_smsh_token'));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = curl_exec($ch);
    $error_num = curl_errno($ch);
    $error = (curl_errno($ch) ? curl_error($ch) : false);
    curl_close($ch);
    
    
  // RING CAPTCHA
  } else if ($provider == 'ringcaptcha') {
    $data = array('api_key' => sms_param('ringcaptcha_api_key'), 'phone' => $phone_number, 'message' => $message);
    $string = http_build_query($data, '', '&');

    $url = 'https://api.ringcaptcha.com/' . sms_param('ringcaptcha_app_key') . '/sms';
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('application/x-www-url-encoded; charset=utf-8'));
    $response = curl_exec($ch);
    $error_num = curl_errno($ch);
    $error = (curl_errno($ch) ? curl_error($ch) : false);

    curl_close($ch);
  
    
  // MSG91.COM OLD INTEGRATION
  // Remove after 2023-12-31
  } else if ($provider == 'msg91-OLD') {
    $data = array(
      'authkey' => sms_param('msg91_api_key'), 
      'mobiles' => $phone_number, 
      'message' => urlencode($message), 
      'sender' => sms_param('msg91_sender_id'), 
      'route' => 'default',
      'response' => 'json'
    );
    
    $url = "https://api.msg91.com/api/sendhttp.php";
    
    $ch = curl_init();
    curl_setopt_array($ch, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data
      //,CURLOPT_FOLLOWLOCATION => true
    ));

    //Ignore SSL certificate verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
    $response = curl_exec($ch);
    $error_num = curl_errno($ch);
    $error = (curl_errno($ch) ? curl_error($ch) : false);

    curl_close($ch); 
    
  // MSG91.COM
  // https://docs.msg91.com/reference/send-sms
  } else if ($provider == 'msg91') {
    $data = array(
      'template_id' => sms_param('msg91_template_id'),
      'short_url' => (sms_param('msg91_short_url') == 1 ? 1 : 0),
      'recipients' => array(
        array(
          'mobiles' => $phone_number, 
          'VAR1' => $otp_code, 
          'VAR2' => $message, 
          'OTP' => $otp_code,
          'MESSAGE' => $message,
          'ACTION' => $action
        )
      )
    );
    
    $url = "https://control.msg91.com/api/v5/flow/";
    
    $ch = curl_init();
    curl_setopt_array($ch, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => json_encode($data)
    ));

    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Accept: application/json',
      'Content-Type: application/json',
      'authkey: ' . sms_param('msg91_api_key')
    ));
    
    //Ignore SSL certificate verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
    $response = curl_exec($ch);
    $error_num = curl_errno($ch);
    $error = (curl_errno($ch) ? curl_error($ch) : false);

    curl_close($ch);    
  }


  // Idenitfy if sms was sent successfully
  $output = sms_provider_status($response, $provider, $extra);

  if($error_num > 0) {
    $output['status'] = 'ERROR';
    $output['message'] = $error;
  }
  
  // add log
  $log = array(
    'phone_number' => $phone_number,
    'message' => $message,
    'provider' => $provider,
    'user_id' => osc_logged_user_id(),
    'action' => $action,
    'response' => (@$output['message'] <> '' ? $output['message'] : $response),
    'error' => $error,
    'status' => @$output['status']
  );

  sms_add_log($log);

  $output['log'] = $log;

  return $output;
}


// GET ACCOUNT BALANCE
function sms_get_balance($provider = '') {
  $provider = ($provider <> '' ? $provider : sms_param('provider'));
  

  // DEMO
  if($provider == 'demo') {
    return array('status' => 'OK', 'response' => 1000, 'data' => '');

  // ROUTEE.NET
  } else if($provider == 'routee.net') {
    $access_token = sms_routee_get_access_token();
    
    $ch = curl_init();

    curl_setopt_array($ch, array(
      CURLOPT_URL => "https://connect.routee.net/accounts/me/balance",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer {$access_token}"
      ),
    ));

    $response = curl_exec($ch);

    if(curl_errno($ch)) {
      return array('status' => 'ERROR', 'response' => curl_error($ch));
    }

    curl_close($ch);
    $data = json_decode($response);

    if(isset($data->error) && $data->error != '') {
      return array('status' => 'ERROR', 'response' => '[' . $data->error . '] ' . $data->error_description, 'data' => $data);
    } else {
      return array('status' => 'OK', 'response' => $data->balance . $data->currency->sign, 'data' => $data);
    }
    
    
  // SMS-ASSISTENT.BY
  } else if($provider == 'smsassistent.by') {

    $api_url = 'https://userarea.sms-assistent.by/api/v1.2/credits/plain';

    $data = array(
      'user' => sms_param('assistent_api_username'),
      'password' => sms_param('assistent_api_pass')
    );

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);    

    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
      return array('status' => 'ERROR', 'response' => curl_error($ch));
    }

    curl_close($ch);
    if((int)$response < 0) {
      return array('status' => 'ERROR', 'response' => sms_smsassistant_get_error($response) . ' (' . $response . ')', 'data' => $response);
    } else {
      return array('status' => 'OK', 'response' => $response, 'data' => $response);
    }

  // NOTIFY.LK
  } else if($provider == 'notify.lk') {

    // https://app.notify.lk/api/v1/status?user_id=[USER_ID]&api_key=[API_KEY]
    $balance_url = 'https://app.notify.lk/api/v1/status?user_id=' . sms_param('notify_user_id') . '&api_key=' . sms_param('notify_api_key');

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $balance_url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);    

    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
      return array('status' => 'ERROR', 'response' => curl_error($ch));
    }

    curl_close($ch);

    $sms = json_decode($response);
   
    if($sms->status == 'success') {
      return array('status' => 'OK', 'response' => 'Rs.' . $sms->data->acc_balance, 'data' => $response);
    } else {
      return array('status' => 'ERROR', 'response' => $sms->data, 'data' => $response);
    }

  // TWILIO
  } else if($provider == 'twilio') {
    $account_sid = sms_param('twilio_account_sid');        // Your Account SID and Auth Token from twilio.com/console
    $auth_token =  sms_param('twilio_auth_token');
  
    // https://{AccountSid}:{AuthToken}@api.twilio.com/2010-04-01/Accounts
    $balance_url = 'https://' . $account_sid . ':' . $auth_token . '@api.twilio.com/2010-04-01/Accounts/' . $account_sid . '/Balance.json';

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $balance_url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);    

    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
      return array('status' => 'ERROR', 'response' => curl_error($ch));
    }

    curl_close($ch);

    $sms = json_decode($response);
   
    return array('status' => 'OK', 'response' => $sms->balance . ' ' . $sms->currency, 'data' => $response);

  
  // TEXTLOCAL.COM
  } else if($provider == 'textlocal.com') {
    $api_key = urlencode(sms_param('textlocalcom_api_key'));

    $data = array('apiKey' => $api_key);
    $ch = curl_init('https://api.txtlocal.com/balance/');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
      return array('status' => 'ERROR', 'response' => curl_error($ch));
    }

    curl_close($ch);

    $sms = json_decode($response);
   
    return array('status' => 'OK', 'response' => $sms->balance->sms, 'data' => $response);


  // GATEWAYAPI.COM
  } else if($provider == 'gatewayapi.com') {
    $url = "https://gatewayapi.com/rest/me";
    $api_token = sms_param('gatewayapi_api_token');

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($ch,CURLOPT_USERPWD, $api_token);
    //curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($json));
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if(curl_errno($ch)) {
      return array('status' => 'ERROR', 'response' => curl_error($ch));
    }

    curl_close($ch);

    $json = json_decode($response); 

    return array('status' => 'OK', 'response' => $json->credit . ' ' . $json->currency, 'data' => $response);


  // MSG91.COM
  } else if($provider == 'msg91') {
    $url = "https://control.msg91.com/api/balance.php";
    //$data = array('smshkey' => sms_param('msg91_api_key'), 'type' => 4);
    $data = array('authkey' => sms_param('msg91_api_key'), 'type' => 4, 'response' => 'json');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
      return array('status' => 'ERROR', 'response' => curl_error($ch), 'data' => $response);
    }

    curl_close($ch);
   
    return array('status' => 'OK', 'response' => $response, 'data' => '');  
  }

  
  return array('status' => 'ERROR', 'response' => __('Balance retrieval for this provider is not supported', 'sms'), 'data' => '');  
}


// READ PROVIDER STATUS
function sms_provider_status($data, $provider = '', $extra = array()) {
  $provider = ($provider <> '' ? $provider : sms_param('provider'));


  // DEMO
  if($provider == 'demo') {
    return array('status' => 'OK', 'message' => '');

  } else if($provider == 'routee.net') {
    $data = json_decode($data, true);

    if(isset($data['code']) && $data['code'] != '') {
      return array('status' => 'ERROR', 'message' => '[' . $data['code'] . '] ' . @$data['developerMessage'] . ' (' . json_encode($extra) . ')');
    } else {
      return array('status' => 'OK', 'message' => '');
    }
    
  } else if($provider == 'smsassistent.by') {

    if((int)$data < 0) {
      return array('status' => 'ERROR', 'message' => sms_smsassistant_get_error($data) . ' (' . $data . ')');
    } else {
      return array('status' => 'OK', 'message' => '');
    }


  } else if($provider == 'notify.lk') {
    $response = json_decode($data, true);

    if(@$response['status'] == 'success') {
      return array('status' => 'OK', 'message' => '');
    } else {
      return array('status' => 'ERROR', 'message' => @$response['errors'][0]);
    }


  // TWILIO
  } else if($provider == 'twilio') {
    $object = $data;

    if(is_object($object) && get_class($object) == 'Twilio\Exceptions\RestException') {
      return array('status' => 'ERROR', 'message' => $object->getCode() . ' - ' . $object->getMessage());

    } else if(isset($object->errorCode) && $object->errorCode <> '' || (string)$object->errorMessage <> '') {
      return array('status' => 'ERROR', 'message' => $object->errorCode . ' - ' . $object->errorMessage);
      
    } else {
      $message = implode(' / ', array_filter(array($object->status, $object->sid, $object->from, $object->to, $object->direction)));
      return array('status' => 'OK', 'message' => $message);
      
    }

  
  // RINGCAPTCHA
  } else if ($provider == 'ringcaptcha') {
    $object = json_decode($data);

    if($object->status == 'ERROR') {
      return array('status' => 'ERROR', 'message' => $object->message);
    } else {
      return array('status' => 'OK', 'message' => '');
    }
  }

  // TEXTLOCAL.COM
  if ($provider == 'textlocal.com') {
    $object = json_decode($data);

    if($object->status != 'success') {
      return array('status' => 'ERROR', 'message' => $object->message);
    } else {
      return array('status' => 'OK', 'message' => '');
    }
  }

  // GATEWAYAPI.COM
  if ($provider == 'gatewayapi.com') {
    $array = json_decode($data, true);

    if(isset($array['code'])) {
      return array('status' => 'ERROR', 'message' => @$array['message'] . ' (' . @$array['code'] . ')');
    } else {
      return array('status' => 'OK', 'message' => '');
    }
  }

  // PLIVO
  if ($provider == 'plivo') {
    $array = json_decode($data, true);

    if($data != true && $data <> '' && (int)$array['error_code'] <= 0) {
      return array('status' => 'OK', 'message' => '');
    } else {
      return array('status' => 'ERROR', 'message' => __('Provider returned error with code:', 'sms') . ' (' . @$array['code'] . ')');
    }
  }

  // MSG91
  if ($provider == 'msg91') {
    $array = @json_decode($data, true);

    if(@$array['type'] == 'success' || @$array['msgType'] == 'success' || @$array['msg'] == '200') {
      return array('status' => 'OK', 'message' => @$array['message']);
    } else if($data != '') {
      return array('status' => 'OK', 'message' => $data);
    } else {
      return array('status' => 'ERROR', 'message' => __('Provider returned error:', 'sms') . ' ' . @$array['message'] . ' (' . @$array['code'] . ')');
    }
  }

  // TEXTLOCAL.IN
  if ($provider == 'textlocal.in') {
    $array = json_decode($data, true);

    if($array['status'] == 'success') {
      return array('status' => 'OK', 'message' => '');
    } else {
      return array('status' => 'ERROR', 'message' => __('Provider returned error:', 'sms') . ' ' . @$array['errors'][0]['message'] . ' (' . @$array['errors'][0]['code'] . ')');
    }
  }

  // BUDGETSMS
  if ($provider == 'budgetsms') {
    $string = $data;

    if(@substr($string, 0, 3) == 'ERR') {
      return array('status' => 'ERROR', 'message' => __('Provider returned error with code:', 'sms') . ' (' . @$string . ')');
    } else {
      return array('status' => 'OK', 'message' => '');
    }
  }

  // WAY2SMS
  if ($provider == 'way2sms') {
    $array = json_decode($data, true);

    if(@$array['status'] == 'success') {
      return array('status' => 'OK', 'message' => '');
    } else {
      return array('status' => 'ERROR', 'message' => __('Provider returned error:', 'sms') . ' ' . @$array['msg']);
    }
  }



  return array('status' => 'OK', 'message' => '');

}



// GET ACCESS TOKEN FOR ROUTEE.NET
function sms_routee_get_access_token() {
  $base64Token = base64_encode(sms_param('routee_app_id') . ':' . sms_param('routee_app_secret'));
  
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://auth.routee.net/oauth/token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => "grant_type=client_credentials",
    CURLOPT_HTTPHEADER => array(
      "authorization: Basic {$base64Token}",
      "content-type: application/x-www-form-urlencoded"
    ),
  ));

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    return false;
  } else {
    $data = json_decode($response);

    if(isset($data->access_token) && $data->access_token != '') {
      return $data->access_token;
    }
    
    return false;
  }
}


// ITEM ACTIVATED
function sms_notif_admin_contact_form($item_id) {
  if(sms_param('notify_admin_contact_form') == 1 && sms_param('admin_phone_number') <> '') {
    $message = sprintf(__('Hi admin, new message from %s on topic %s: %s', 'sms'), Params::getParam('yourName'), osc_highlight(Params::getParam('subject'), 20), osc_highlight(Params::getParam('message'), 200));
    sms_send(sms_param('admin_phone_number'), $message, 'ADMIN_CONTACT_FORM');
  }
}

osc_add_hook('pre_contact_post', 'sms_notif_admin_contact_form');


// ITEM ACTIVATED
function sms_notif_user_item_activated($item_id) {
  if(sms_param('notify_user_item_activated') == 1) {
    $item = Item::newInstance()->findByPrimaryKey($item_id);
    
    if(sms_check_user_group($item['fk_i_user_id'])) {
      $user = User::newInstance()->findByPrimaryKey($item['fk_i_user_id']);
      $user_name = (@$user['s_name'] <> '' ? $user['s_name'] : $item['s_contact_name']);

      $phone_number = @$user['s_phone_mobile'];
      $phone_number_item = sms_item_phone_number($item_id);

      $phone_number = ($phone_number <> '' ? $phone_number : $phone_number_item);

      $message = sprintf(__('Hi %s, your ad on %s has been activated %s', 'sms'), $user_name, sms_param('site_name'), osc_item_url_from_item($item));
      sms_send($phone_number, $message, 'USER_ITEM_ACTIVATE');
    }
  }
}

osc_add_hook('activate_item', 'sms_notif_user_item_activated');


// ITEM PUBLISHED
function sms_notif_user_item_published($item) {
  if(sms_param('notify_user_item_validate') == 1 || sms_param('notify_user_item_post') == 1 || sms_param('notify_admin_item_post') == 1) {
    $item_id = $item['pk_i_id'];
    //$item = Item::newInstance()->findByPrimaryKey($item_id));
    $user = User::newInstance()->findByPrimaryKey($item['fk_i_user_id']);

    $user_name = (@$user['s_name'] <> '' ? $user['s_name'] : $item['s_contact_name']);

    $phone_number = @$user['s_phone_mobile'];
    $phone_number_item = sms_item_phone_number($item_id);

    $phone_number = ($phone_number <> '' ? $phone_number : $phone_number_item);

    $title = osc_highlight($item['s_title'], 30);

    if($item['b_active'] == 0 && sms_param('notify_user_item_validate') == 1 && sms_check_user_group($item['fk_i_user_id'])) {
      $activate_url = osc_item_activate_url($item['s_secret'], $item['pk_i_id']);
      $message = sprintf(__('Hi %s, validate your %s ad %s', 'sms'), $user_name, sms_param('site_name'), $activate_url);
      sms_send($phone_number, $message, 'USER_ITEM_VALIDATE');

    } else if(sms_param('notify_user_item_post') == 1 && sms_check_user_group($item['fk_i_user_id'])) {
      $message = sprintf(__('Hi %s, your %s ad has been published: %s %s', 'sms'), $user_name, sms_param('site_name'), $title, osc_item_url_from_item($item));
      sms_send($phone_number, $message, 'USER_ITEM_PUBLISHED');
    }

    if(sms_param('notify_admin_item_post') == 1 && sms_param('admin_phone_number') <> '') {
      $message = sprintf(__('Hi admin, %s has published new ad: %s %s', 'sms'), $user_name, $title, osc_item_url_from_item($item));
      sms_send(sms_param('admin_phone_number'), $message, 'ADMIN_ITEM_PUBLISHED');
    }
  }
}

osc_add_hook('posted_item', 'sms_notif_user_item_published');




// ITEM CONTACT FORM USED
function sms_notif_user_contact_form() {
  if(Params::getParam('action') == 'contact_post' && Params::getParam('page') == 'item') {
    if(sms_param('notify_user_item_contact') == 1) {
      $item = Item::newInstance()->findByPrimaryKey(Params::getParam('id'));
      
      if(sms_check_user_group($item['fk_i_user_id'])) {
        $user = User::newInstance()->findByPrimaryKey($item['fk_i_user_id']);

        $text = osc_highlight(Params::getParam('message'), 240);
        $sender = Params::getParam('yourName') . (Params::getParam('phoneNumber') <> '' ? ' (' . Params::getParam('phoneNumber') . ')' : '');
        $user_name = (@$user['s_name'] <> '' ? $user['s_name'] : $item['s_contact_name']);

        $phone_number = @$user['s_phone_mobile'];
        $phone_number_item = sms_item_phone_number(Params::getParam('id'));

        $phone_number = ($phone_number <> '' ? $phone_number : $phone_number_item);

        $title = osc_highlight($item['s_title'], 15);

        $message = sprintf(__('Hi %s, %s has question on %s: %s', 'sms'), $user_name, $sender, $title, $text);

        sms_send($phone_number, $message, 'USER_CONTACT_FORM');
      }
    }
  }
}

osc_add_hook('init', 'sms_notif_user_contact_form');


// RESET PASSWORD
function sms_notif_user_reset_password($user, $url) {
  if(sms_param('notify_user_reset_password') == 1 && $user['s_phone_mobile'] <> '') {
    if(sms_check_user_group($user['pk_i_id'])) {
      $phone_number = $user['s_phone_mobile'];
      $user_name = $user['s_name'];

      $message = sprintf(__('Hi %s, reset your %s password %s', 'sms'), $user_name, sms_param('site_name'), $url);

      sms_send($phone_number, $message, 'USER_RESET_PASSWORD');
      osc_add_flash_ok_message(__('A SMS has been sent to your mobile number instructions to reset password', 'sms'));
    }
  }
}

osc_add_hook('hook_email_user_forgot_password', 'sms_notif_user_reset_password');



// VALIDATE USER ACCOUNT
function sms_notif_user_validate_account($user, $input) {
  if(sms_param('notify_user_account_validate') == 1 && $user['s_phone_mobile'] <> '') {
    if(sms_check_user_group($user['pk_i_id'])) {
      $phone_number = $user['s_phone_mobile'];
      $user_name = $user['s_name'];

      $url = osc_user_activate_url($user['pk_i_id'], $input['s_secret']);
      $message = sprintf(__('Hi %s, validate your %s account %s', 'sms'), $user_name, sms_param('site_name'), $url);

      sms_send($phone_number, $message, 'USER_VALIDATE_ACCOUNT');
      osc_add_flash_ok_message(sprintf(__('%s activation SMS has been sent to your mobile number', 'sms'), sms_param('site_name')));
    }
  }
}


osc_add_hook('hook_email_user_validation', 'sms_notif_user_account_validate');


// NEW USER HAS REGISTERED
function sms_notif_admin_user_registered($user_id) {
  if(sms_param('notify_admin_register') == 1 && sms_param('admin_phone_number') <> '') {
    $user = User::newInstance()->findByPrimaryKey($user_id);
    $message = sprintf(__('Hi admin, new user has registered: %s (%s)', 'sms'), $user['s_name'], $user['s_email']);
    sms_send(sms_param('admin_phone_number'), $message, 'ADMIN_USER_REGISTERED');
  }
}


osc_add_hook('user_register_completed', 'sms_notif_admin_user_registered');


// REMOVE VERIFICATION IF USER IS REMOVED
function sms_user_removed($id) {
  $user = User::newInstance()->findByPrimaryKey($id);
  
  ModelSMS::newInstance()->deleteVerificationByEmail($user['s_email']);
  ModelSMS::newInstance()->deleteVerificationByPhone($user['s_phone_land']);
  ModelSMS::newInstance()->deleteVerificationByPhone($user['s_phone_mobile']);
}

osc_add_hook('delete_user', 'sms_user_removed');


// GET ITEM PHONE NUMBER
function sms_item_phone_number($item_id) {
  $theme = osc_current_web_theme();

  $item = Item::newInstance()->findByPrimaryKey($item_id);  //Params::getParam('id')
  $user = User::newInstance()->findByPrimaryKey($item['fk_i_user_id']);

  $check_table = ModelSMS::newInstance()->checkTable($theme);


  // OSCLASSPOINT THEMES
  if($check_table) {
    $phone1 = ModelSMS::newInstance()->getItemThemeNumber($item_id, $theme);
    
    if($phone1 <> '') {
      return $phone1;
    }
  }


  // ZARA THEME 
  if($theme == 'zara' && @$item['s_city_area'] <> '') {
    return $item['s_city_area'];
  }


  // TELEPHONE PLUGIN
  if(function_exists('osc_telephone_number')){ 
    if($item_id) {
      $data = Modelphone::newInstance()->t_check_value($item_id);
 
      if(@$data['s_telephone'] <> '') {
        return $data['s_telephone'];
      }
    }
  }


  // CUSTOM FIELD
  if(sms_param('custom_field_phone') <> '') {
    $data =Item::newInstance()->metaFields($item_id);

    if(count($data) > 0) {
      foreach($data as $d) {
        if($d['s_slug'] == sms_param('custom_field_phone')) {
          if($d['s_value'] <> '') {
            return $d['s_value'];
          }

          break;
        }
      }
    }
  }


  if(@$user['s_phone_mobile'] <> '') {
    return $user['s_phone_mobile'];
  }

  return '';
}


// VERIFY PHONE NUMBER
function sms_phone_verify($phone_number) {
  $phone_number = sms_prepare_number($phone_number);

  if($phone_number !== false) {
    $check = ModelSMS::newInstance()->getVerification($phone_number);

    if(@$check['s_status'] == 'VERIFIED') {
      return true;
    }
  }
 
  return false;
}


// CREATE VALID PHONE NUMBER
function sms_prepare_number($phone_number, $provider = '', $unique_check = false) {
  //$phone_number = preg_replace('/\s+/', '', $phone_number);
  $phone_number = ltrim($phone_number, '0');
  $phone_number = str_replace('/', '', $phone_number);
  $phone_number = str_replace('(', '', $phone_number);
  $phone_number = str_replace(')', '', $phone_number);
  $phone_number = str_replace('-', '', $phone_number);
  $phone_number = str_replace(' ', '', $phone_number);

  $phone_number = filter_var($phone_number, FILTER_SANITIZE_NUMBER_INT);


  // Ignore first zero
  if(sms_param('ignore_first_zero') == 1 && substr($phone_number, 0, 1) == '+') {
    $ignore_first_zero_prefixes = sms_param('ignore_first_zero');
    $ignore_first_zero_prefixes_arr = array_filter(array_unique(array_map('trim', explode(',', $ignore_first_zero_prefixes))));
  
    if(count($ignore_first_zero_prefixes_arr) > 0) {
      foreach($ignore_first_zero_prefixes_arr as $prefix) {
        if(substr($prefix, 0, 1) == '+') {
          $phone_number = str_replace($prefix . '0', $prefix, $phone_number);
        }
      }
    }  
  }
  
  
  if($unique_check) {
    $phone_number = str_replace('+', '', $phone_number);
    $phone_number = ltrim($phone_number, '0');
    return $phone_number;
  }

  
  if(sms_param('provider') == 'demo') {
    return $phone_number;
  }


  // Routee.net - phone number must start with country code
  if(sms_param('provider') == 'routee.net') {
    if(substr($phone_number, 0, 1) <> '+') {
      $country_code = sms_param('routee_country_code') <> '' ? sms_param('routee_country_code') : '+234';  // Nigeria
      $phone_number = ltrim($phone_number, '0');
      $phone_number = $country_code . $phone_number;         // Nigeria
    }
  }

  if((strlen($phone_number) < 9 || strlen($phone_number) > 16)) {
    return false;
  } else if(substr($phone_number, 0, 1) <> '+' && $provider <> 'notify.lk') {
    return '+' . $phone_number;
  } else if(substr($phone_number, 0, 1) == '+' && $provider == 'notify.lk') {
    return substr($phone_number, 1, strlen($phone_number));
  } else {
    return $phone_number;
  }
}  


// CREATE LOG ENTRY
function sms_add_log($data = array()) {
  $user_id = osc_logged_user_id();
  
  $data = array(
    's_status' => $data['status'],
    'fk_i_user_id' => $data['user_id'],
    's_phone_number' => $data['phone_number'],
    's_provider' => strtoupper($data['provider']),
    's_message' => $data['message'],
    's_action' => $data['action'],
    's_error' => $data['error'],
    's_response' => json_encode($data['response']),
    'dt_date' => date('Y-m-d H:i:s')
  );
  
  ModelSMS::newInstance()->insertLog($data);
}

  

// URL TO PLUGIN FOLDER
function sms_plugin_url() {
  return osc_base_url() . 'oc-content/plugins/sms/';
}


// CORE FUNCTIONS
function sms_param($name) {
  return osc_get_preference($name, 'plugin-sms');
}


if(!function_exists('sms_param_update')) {
  function sms_param_update( $param_name, $update_param_name, $type = NULL, $plugin_var_name = NULL ) {
  
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
    } elseif ($type == 'code') {

      // Other inputs (text, password, ...)
      if( Params::getParam( $update_param_name ) == 'done' && Params::existParam($param_name)) {
        $val = Params::getParam( $param_name, false, false );
      } else {
        $val = ( osc_get_preference( $param_name, $plugin_var_name) != '' ) ? osc_get_preference( $param_name, $plugin_var_name ) : '';
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




// GENERATE PAGINATION
function sms_admin_paginate($file, $page_id, $per_page, $count_all, $class = '', $params = '') {
  $html = '';
  $page_id = (int)$page_id;
  $page_id = ($page_id <= 0 ? 1 : $page_id);
  $base_link = osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=' . $file . $params;

  if($per_page < $count_all) {
    $html .= '<div id="mb-pagination" class="' . $class . '">';
    $html .= '<div class="mb-pagination-wrap">';
    $html .= '<div>' . __('Page:', 'sms') . '</div>';

    $pages = ceil($count_all/$per_page); 
    $page_actual = ($page_id == '' ? 1 : $page_id);

    if($pages > 6) {

      // Too many pages to list them all
      if($page_id == 1) { 
        $ids = array(1,2,3, $pages);

      } else if ($page_id > 1 && $page_id < $pages) {
        $ids = array(1,$page_id-1, $page_id, $page_id+1, $pages);

      } else {
        $ids = array(1, $page_id-2, $page_id-1, $page_id);
      }

      $old = -1;
      $ids = array_unique(array_filter($ids));

      foreach($ids as $i) {
        $url = $base_link . '&pageId=' . $i;
        
        if($old <> -1 && $old <> $i - 1) {
          $html .= '<span>&middot;&middot;&middot;</span>';
        }

        $html .= '<a href="' . $url . '" ' . ($page_actual == $i ? 'class="mb-active"' : '') . '>' . $i . '</a>';
        $old = $i;
      }

    } else {

      // List all pages
      for ($i = 1; $i <= $pages; $i++) {
        $url = $base_link . '&pageId=' . $i;
        $html .= '<a href="' . $url . '" ' . ($page_actual == $i ? 'class="mb-active"' : '') . '>' . $i . '</a>';
      }
    }

    $html .= '</div>';
    $html .= '</div>';
  }

  return $html;
}



// CHECK IF RUNNING ON DEMO
function sms_is_demo() {
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

if(!function_exists('mb_generate_rand_int')) {
  function mb_generate_rand_int($length = 4) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
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


// JAVASCRIPT SCROLL TO DIV
function sms_js_scroll($block) { 
  ?>

  <script>
    $(document).ready(function() {
      if($('<?php echo $block; ?>').length) { 
        var flash = $('.mb-head').nextAll('.flashmessage');
        flash = flash.add('#content-render > .flashmessage:not(.jsMessage)');
        flash.each(function(){
          $(this).removeAttr('style');
          $(this).removeAttr('style');
          $(this).find('a.btn').remove();
          $(this).html($(this).text().trim());

          if($(this).text() != '') {
            $('<?php echo $block; ?>').before($(this).wrap('<div/>').parent().html());
            $(this).hide(0);
          }
        });

        var flashCount = 0;

        if(flash.length > 0) {
          flashCount = flash.length;
        }

        $('html,body').animate({scrollTop: $('<?php echo $block; ?>').offset().top - 70 - parseInt(flashCount*64)}, 0);

      }
    });
  </script>

  <?php
}



?>