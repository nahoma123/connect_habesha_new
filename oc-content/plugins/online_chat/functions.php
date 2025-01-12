<?php

// INCLUDE MAILER SCRIPT
function oc_include_mailer() {
  if(file_exists(osc_lib_path() . 'phpmailer/class.phpmailer.php')) {
    require_once osc_lib_path() . 'phpmailer/class.phpmailer.php';
  } else if(file_exists(osc_lib_path() . 'vendor/phpmailer/phpmailer/class.phpmailer.php')) {
    require_once osc_lib_path() . 'vendor/phpmailer/phpmailer/class.phpmailer.php';
  }
}


function oc_remove_old() {
  ModelOC::newInstance()->removeOldChats();
}


function oc_get_picture( $user_id ) {
  $img = osc_base_url() . 'oc-content/plugins/online_chat/img/no-user.png';

  if(function_exists('profile_picture_show')) {
    $picture = ModelOC::newInstance()->getPictureByUserId($user_id);
    $picture['pic_ext'] = isset($picture['pic_ext']) ? $picture['pic_ext'] : '.jpg';

    if(file_exists(osc_content_path() . 'plugins/profile_picture/images/profile' . $user_id . $picture['pic_ext'])) { 
      $img = osc_base_url() . 'oc-content/plugins/profile_picture/images/profile' . $user_id . $picture['pic_ext'];
    } 
  }

  return $img; 
}


function oc_check_availability( $user_id ) {
  if($user_id <> '' && $user_id > 0) {
    $registered = 1;
    $last_active = ModelOC::newInstance()->getUserLastActive($user_id);

    $active_limit = osc_get_preference('refresh_user', 'plugin-online_chat');
    $active_limit = ($active_limit > 0 ? $active_limit : 120);
    $active_limit = $active_limit + 10;

    $limit_datetime = date('Y-m-d H:i:s', strtotime(' -' . $active_limit . ' seconds', time()));
  } else {
    $registered = 0;
  }

  if($registered == 1 && $last_active >= $limit_datetime && $user_id <> osc_logged_user_id()) {
    $avl = 1;
  } else {
    $avl = 0;
  }

  return $avl;
}


// CHECK IF RUNNING ON DEMO
function oc_is_demo() {
  if(osc_logged_admin_username() == 'admin') {
    return false;
  } else if(isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'],'mb-themes') !== false || strpos($_SERVER['HTTP_HOST'],'abprofitrade') !== false)) {
    return true;
  } else {
    return false;
  }
}


function oc_chat_button( $user_id = '' ) {
  $html = '';
  $user_name = '';

  if((osc_is_ad_page() || osc_is_search_page()) && $user_id == '') {
    $user_id = osc_item_user_id();
    $user_name = osc_item_contact_name();
  }

  if($user_id <> '' && $user_id > 0) {
    $registered = 1;
    $last_active = ModelOC::newInstance()->getUserLastActive($user_id);

    $active_limit = osc_get_preference('refresh_user', 'plugin-online_chat');
    $active_limit = ($active_limit > 0 ? $active_limit : 120);
    $active_limit = $active_limit + 10;

    $limit_datetime = date('Y-m-d H:i:s', strtotime(' -' . $active_limit . ' seconds', time()));
  } else {
    $registered = 0;
  }

  if($registered == 1 && $user_id <> osc_logged_user_id() && !oc_check_bans($user_id)) {
    $class = ' oc-active';
  } else {
    $class = ' oc-disabled';
  }

  if(isset($limit_datetime) && $limit_datetime <> '' && $last_active >= $limit_datetime) {
    $class .= ' oc-online';
  } else {
    $class .= ' oc-offline';
  }

  $html .= '<a href="#" class="oc-chat-button' . $class . '" data-to-user-id="' . $user_id . '" data-to-user-name="' . osc_esc_html($user_name) . '" data-to-user-image="' . oc_get_picture( $user_id ) . '">';
  $html .= '<div class="oc-user-left"><i class="fa fa-comments-o"></i></div>';

  $html .= '<div class="oc-user-right">';

  if($registered == 0) {
    $html .= '<div class="oc-user-top">' . __('Chat unavailable', 'online_chat') . '</div>';
    $html .= '<div class="oc-user-bot">' . __('User is not registered', 'online_chat') . '</div>';
  } else {
    if($user_id == osc_logged_user_id()) {
      $html .= '<div class="oc-user-top">' . __('Chat unavailable', 'online_chat') . '</div>';
      $html .= '<div class="oc-user-bot">' . __('It is your listing', 'online_chat') . '</div>';
    } else if (oc_check_bans($user_id)) {
      $html .= '<div class="oc-user-top">' . __('Chat unavailable', 'online_chat') . '</div>';
      $html .= '<div class="oc-user-bot">' . __('User has blocked you', 'online_chat') . '</div>';
    } else {
      $html .= '<div class="oc-user-top oc-status-offline">' . __('Start chatting', 'online_chat') . '</div>';
      $html .= '<div class="oc-user-bot oc-status-offline">' . __('User is offline', 'online_chat') . '</div>';

      $html .= '<div class="oc-user-top oc-status-online">' . __('Start chatting', 'online_chat') . '</div>';
      $html .= '<div class="oc-user-bot oc-status-online">' . __('User is online', 'online_chat') . '</div>';
    }
  }

  $html .= '</div>';
  $html .= '</a>';

  return $html;
}



function oc_user_status( $user_id = '' ) {
  $html = '';
  $user_name = '';

  if((osc_is_ad_page() || osc_is_search_page()) && $user_id == '') {
    $user_id = osc_item_user_id();
    $user_name = osc_item_contact_name();
  }

  if($user_id <> '' && $user_id > 0) {
    $registered = 1;
    $last_active = ModelOC::newInstance()->getUserLastActive($user_id);

    $active_limit = osc_get_preference('refresh_user', 'plugin-online_chat');
    $active_limit = ($active_limit > 0 ? $active_limit : 120);
    $active_limit = $active_limit + 10;

    $limit_datetime = date('Y-m-d H:i:s', strtotime(' -' . $active_limit . ' seconds', time()));
  } else {
    $registered = 0;
  }

  //if($registered == 1 && $user_id <> osc_logged_user_id() && !oc_check_bans($user_id)) {
  if($registered == 1 && !oc_check_bans($user_id)) {
    $class = ' oc-active';
  } else {
    $class = ' oc-disabled';
  }

  if(isset($limit_datetime) && $limit_datetime <> '' && $last_active >= $limit_datetime) {
    $class .= ' oc-online';
  } else {
    $class .= ' oc-offline';
  }

  if($registered == 0) {
    $title = osc_esc_html(__('Chat unavailable', 'online_chat') . ': ' . __('User is not registered', 'online_chat'));
  } else {
    if($user_id == osc_logged_user_id()) {
      $title = osc_esc_html(__('Chat unavailable', 'online_chat') . ': ' . __('It is your listing', 'online_chat'));
    } else if (oc_check_bans($user_id)) {
      $title = osc_esc_html(__('Chat unavailable', 'online_chat') . ': ' . __('User has blocked you', 'online_chat'));
    } else {
      $title = '';
    }
  }

  $html .= '<span class="oc-user-status' . $class . '" data-user-id="' . $user_id . '" title="' . $title . '"><i class="fa fa-circle"></i></span>';

  return $html;
}



function oc_check_bans($check_user_id) {
  $bans = ModelOC::newInstance()->getUserBans($check_user_id);

  $banned_ids = array_column($bans, 'i_block_user_id');
  $banned_ids = array_unique($banned_ids);

  if(in_array(0, $banned_ids) || in_array(osc_logged_user_id(), $banned_ids)) {
    return true;    // user is blocked
  } else {
    return false;
  }
}


function oc_check_bans_all() {
  $bans = ModelOC::newInstance()->getUserBans();

  $banned_ids = array_column($bans, 'i_block_user_id');
  $banned_ids = array_unique($banned_ids);

  if(in_array(0, $banned_ids)) {
    return true;    // user is blocked
  } else {
    return false;
  }
}


function oc_update_last_active() {
  if(osc_is_web_user_logged_in()) {
    ModelOC::newInstance()->updateUserLastActive(osc_logged_user_id());
  }
}


function oc_ajax_url() {
  $url = osc_base_url(true) . '?ajaxRequest=1&ajaxChat=1';
  return $url;
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



?>