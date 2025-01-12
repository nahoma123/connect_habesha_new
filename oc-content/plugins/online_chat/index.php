<?php
/*
  Plugin Name: Online Chat Plugin
  Plugin URI: https://osclasspoint.com/osclass-plugins/messaging-and-communication/online-chat-plugin-i58
  Description: Allow your customers to communicate with buyers/sellers via chat
  Version: 2.3.4
  Author: MB Themes
  Author URI: https://osclasspoint.com
  Author Email: info@osclasspoint.com
  Short Name: online_chat
  Plugin update URI: online-chat-plugin
  Support URI: https://forums.osclasspoint.com/online-chat-plugin/
  Product Key: GlNHp4EtSnbdG9CAQ2z6
*/

require_once 'model/ModelOC.php';
require_once 'functions.php';
require_once 'email.php';


osc_enqueue_style('font-awesome47', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
osc_enqueue_style('oc-user-style', osc_base_url() . 'oc-content/plugins/online_chat/css/user.css?v=' . date('Ymdhis'));

osc_register_script('oc-user', osc_base_url() . 'oc-content/plugins/online_chat/js/user.js?v=' . date('Ymdhis'), 'jquery');
osc_enqueue_script('oc-user');



// INSTALL FUNCTION - DEFINE VARIABLES
function oc_call_after_install() {
  ModelOC::newInstance()->import('online_chat/model/struct.sql');
  
  osc_set_preference('hook_button', 1, 'plugin-online_chat', 'INTEGER');
  osc_set_preference('refresh_message', 10, 'plugin-online_chat', 'INTEGER');
  osc_set_preference('refresh_user', 120, 'plugin-online_chat', 'INTEGER');
  osc_set_preference('refresh_closed', 60, 'plugin-online_chat', 'INTEGER');
  osc_set_preference('delete_days', 720, 'plugin-online_chat', 'INTEGER');


  // UPLOAD EMAIL TEMPLATES
  $oc_transcript = array();
  $locales = OSCLocale::newInstance()->listAllEnabled();
  
  foreach($locales as $loc) {
    $email_text  = '<p>Hi {CONTACT_NAME},</p>';
    $email_text .= '<p>There is transcript of your chat:</p>';
    $email_text .= '<p>{CHAT}</p>';
    $email_text .= '<p><br/></p>';
    $email_text .= '<p>Thank you, <br />{WEB_TITLE}</p>';

    $oc_transcript[$loc['pk_c_code']]['s_title'] = '[{WEB_TITLE}] Your chat transcript';
    $oc_transcript[$loc['pk_c_code']]['s_text'] = $email_text;
  }

  Page::newInstance()->insert( array('s_internal_name' => 'onc_email_transcript', 'b_indelible' => '1'), $oc_transcript);
}



// UNINSTALL PLUGIN
function oc_call_after_uninstall() {
  ModelOC::newInstance()->uninstall();

  osc_delete_preference('hook_button', 'plugin-online_chat');
  osc_delete_preference('refresh_message', 'plugin-online_chat');
  osc_delete_preference('refresh_user', 'plugin-online_chat');
  osc_delete_preference('refresh_closed', 'plugin-online_chat');
  osc_delete_preference('delete_days', 'plugin-online_chat');


  $pages = ModelOC::newInstance()->getPages();  
  foreach($pages as $page) {
    Page::newInstance()->deleteByPrimaryKey($page['pk_i_id']);
  }
}



// ADD AJAX URL TO VARIABLES
function oc_js() {
  $r_message = osc_get_preference('refresh_message', 'plugin-online_chat');  // in seconds
  $r_user = osc_get_preference('refresh_user', 'plugin-online_chat');        // in seconds
  $r_closed = osc_get_preference('refresh_closed', 'plugin-online_chat');    // in seconds

  $r_message = intval($r_message > 0 ? $r_message : 10)*1000;
  $r_user = intval($r_user > 0 ? $r_user : 120)*1000;
  $r_closed = intval($r_closed > 0 ? $r_closed : 60)*1000;

  $js  = '<script type="text/javascript">';
  $js .= 'var ocRefreshMessage=' . $r_message . ';';
  $js .= 'var ocRefreshUser=' . $r_user . ';';
  $js .= 'var ocRefreshClosed=' . $r_closed . ';';
  $js .= 'var ocBaseUrl="' . osc_base_url() . '";';
  $js .= 'var ocAjaxUrl="' . oc_ajax_url() . '";';
  $js .= 'var ocUserId=' . osc_logged_user_id() . ';';
  $js .= 'var ocRemoveMessage="' . osc_esc_js(__('Are you sure you want to remove this chat? Action cannot be undone.', 'online_chat')) . '";';
  $js .= 'var ocRemoveBlock="' . osc_esc_js(__('Are you sure you want unblock this user? User will be able to contact you via chat.', 'online_chat')) . '";';
  $js .= 'var ocRemoveBlockAll="' . osc_esc_js(__('Are you sure you want unblock all users? Anyone will be able to contact you via chat.', 'online_chat')) . '";';
  $js .= 'var ocOptBlock="' . osc_esc_js(__('User blocked', 'online_chat')) . '";';
  $js .= 'var ocOptEmail="' . osc_esc_js(__('Chat transcript sent', 'online_chat')) . '";';
  $js .= 'var ocBlockAll="' . osc_esc_js(__('Block all', 'online_chat')) . '";';
  $js .= 'var ocBlockAllActive="' . osc_esc_js(__('Cancel full block', 'online_chat')) . '";';
  $js .= 'var ocNewMessage="' . osc_esc_js(__('You have a new message!', 'online_chat')) . '";';
  $js .= 'var ocAllString="' . osc_esc_js(__('All users blocked', 'online_chat')) . '";';
  $js .= 'var ocDefImg="' . oc_get_picture(0) . '";';
  $js .= '</script>';

  echo $js;
}

osc_add_hook('header', 'oc_js');
osc_add_hook('admin_header', 'oc_js');



// AUTO HOOK BUTTON
function oc_hook_button() {
  $hook = osc_get_preference('hook_button', 'plugin-online_chat');
  $hook = ($hook <> '' ? $hook : 1);

  if($hook == 1) {
    echo oc_chat_button();
  }
}

osc_add_hook('item_detail', 'oc_hook_button', 8);



// UPDATE USER LAST ACTIVE DATETIME
osc_add_hook('footer', 'oc_update_last_active');


// REMOVE OLD CHATS DAILY
osc_add_hook('cron_daily', 'oc_remove_old');


// ADD CHAT FORM TO FRONT
function oc_chat_form() {
  require_once 'user/chat.php';
}

osc_add_hook('footer', 'oc_chat_form');


function oc_chat_manage() {
  // CHECK AVAILABILITY OF CHAT USERS
  if(Params::getParam('ocChatUsersAvailability') == 1 && Params::getParam('userId') <> '') {
    $active_limit = osc_get_preference('refresh_user', 'plugin-online_chat');
    $active_limit = ($active_limit > 0 ? $active_limit : 120);
    $active_limit = $active_limit + 10;

    $array = array();
    $list = ModelOC::newInstance()->getChatUserAvailability(Params::getParam('userId'));
    $limit_datetime = date('Y-m-d H:i:s', strtotime(' -' . $active_limit . ' seconds', time()));

    foreach($list as $l) {
      if(date('Y-m-d H:i:s', strtotime($l['dt_access_date'])) >= $limit_datetime) {
        $active = 1;
      } else {
        $active = 0;
      }

      $array[] = array(
        'pk_i_chat_id' => $l['pk_i_chat_id'],
        'i_active' => $active
      );
    }

    echo json_encode($array);
    exit;
  }



  // CHECK AVAILABILITY OF USERS ON INITIATE CHAT BUTTONS
  if(Params::getParam('ajaxChat') == 1 && Params::getParam('ocUserButton') == 1 && Params::getParam('userId') <> '') {
    $active_limit = osc_get_preference('refresh_user', 'plugin-online_chat');
    $active_limit = ($active_limit > 0 ? $active_limit : 120);
    $active_limit = $active_limit + 10;

    $array = array();
    $list = ModelOC::newInstance()->getUserButtonsAvailability(Params::getParam('userId'));
    $limit_datetime = date('Y-m-d H:i:s', strtotime(' -' . $active_limit . ' seconds', time()));

    foreach($list as $l) {
      if(date('Y-m-d H:i:s', strtotime($l['dt_access_date'])) >= $limit_datetime) {
        $active = 1;
      } else {
        $active = 0;
      }

      $array[] = array(
        'i_user_id' => $l['pk_i_id'],
        'i_active' => $active
      );
    }

    echo json_encode($array);
    exit;
  }



  // UPDATE USER LAST ACTIVE VIA AJAX
  if(Params::getParam('ajaxChat') == 1 && Params::getParam('ocLastActive') == 1) {
    oc_update_last_active();
    exit;
  }



  // ADD BAN - BLOCK ALL
  if(Params::getParam('ajaxChat') == 1 && Params::getParam('ocAddBanAll') == 1) {
    ModelOC::newInstance()->insertUserBanAll();
    exit;
  }



  // REMOVE BAN - BLOCK ALL
  if(Params::getParam('ajaxChat') == 1 && Params::getParam('ocRemoveBanAll') == 1) {
    ModelOC::newInstance()->removeUserBan(0);
    exit;
  }



  // SEND CHAT VIA EMAIL
  if(Params::getParam('ajaxChat') == 1 && Params::getParam('ocMailChat') == 1 && Params::getParam('chatId') <> '' && Params::getParam('chatId') > 0) {
    oc_email_transcript(Params::getParam('chatId'));
    exit;
  }



  // ADD USER BAN
  if(Params::getParam('ajaxChat') == 1 && Params::getParam('ocAddBan') == 1 && Params::getParam('chatId') <> '' && Params::getParam('chatId') > 0) {
    ModelOC::newInstance()->insertUserBan(Params::getParam('chatId'), Params::getParam('blockedId'));
    exit;
  }



  // REMOVE USER BAN
  if(Params::getParam('ajaxChat') == 1 && Params::getParam('ocRemoveBan') == 1 && Params::getParam('blockUserId') <> '') {
    ModelOC::newInstance()->removeUserBan(Params::getParam('blockUserId'));
    exit;
  }



  // GET CLOSED CHATS
  if(Params::getParam('ajaxChat') == 1 && Params::getParam('ocClosedChat') == 1 && Params::getParam('userId') <> '' && Params::getParam('userId') <> '') {
    $chat_id = ModelOC::newInstance()->getClosedChats(Params::getParam('userId'));
    echo json_encode($chat_id);
    exit;
  }



  // UPDATE CHAT AS CLOSED
  if(Params::getParam('ajaxChat') == 1 && Params::getParam('ocChatClose') == 1 && Params::getParam('chatId') <> '' && Params::getParam('chatId') > 0) {
    ModelOC::newInstance()->closeChat(Params::getParam('chatId'), osc_logged_user_id());
    exit;
  }



  // UPDATE MESSAGE AS READ
  if(Params::getParam('ajaxChat') == 1 && Params::getParam('ocChatUpdateRead') == 1 && Params::getParam('chatId') <> '' && Params::getParam('chatId') > 0) {
    ModelOC::newInstance()->updateChatRead(Params::getParam('chatId'), osc_logged_user_id());
    exit;
  }



  // PROCESS CHAT INITIATION
  if(Params::getParam('ocStartChat') == 1 && Params::getParam('toUserId') <> '' && Params::getParam('userId') <> '') {
    $output = ModelOC::newInstance()->insertChatWithoutId(Params::getParam('userId'), Params::getParam('userName'), Params::getParam('toUserId'), Params::getParam('toUserName'), nl2br(htmlspecialchars(Params::getParam('text', false, false))));

    echo json_encode(array(
      'timestamp' => date('Y-m-d H:i:s'),
      'chatId' => $output['chat_id'],
      'messageId' => $output['message_id']
    ));
    
    exit;
  }



  // PROCESS NEW MESSAGE
  if(Params::getParam('ocChat') == 1 && Params::getParam('chatId') <> '' && Params::getParam('chatId') > 0) {
    $message_id = ModelOC::newInstance()->insertChat(Params::getParam('chatId'), Params::getParam('fromId'), Params::getParam('fromName'), Params::getParam('toId'), Params::getParam('toName'), nl2br(htmlspecialchars(Params::getParam('text', false, false))));

    echo json_encode(array(
      'timestamp' => date('Y-m-d H:i:s'),
      'messageId' => $message_id
    ));
    exit;
  }



  // GET LATEST MESSAGES
  if(Params::getParam('ajaxChat') == 1 && Params::getParam('ocGetLatest') == 1 && Params::getParam('fromId') > 0) {
    $chats = ModelOC::newInstance()->getLatestChats(Params::getParam('fromId'), Params::getParam('ocLastCheck'));

    echo json_encode(array(
      'timestamp' => date('Y-m-d H:i:s'),
      'chats' => $chats
    ));
    
    exit;
  }
}

osc_add_hook('init', 'oc_chat_manage', 1);


// READ PLUGIN PARAMETERS
function oc_param($name) {
  return osc_get_preference($name, 'plugin-online_chat');
}



// ADMIN MENU
function oc_menu($title = NULL) {
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/online_chat/css/admin.css" rel="stylesheet" type="text/css" />';
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/online_chat/css/bootstrap-switch.css" rel="stylesheet" type="text/css" />';
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/online_chat/css/tipped.css" rel="stylesheet" type="text/css" />';
  echo '<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/online_chat/js/admin.js"></script>';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/online_chat/js/tipped.js"></script>';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/online_chat/js/bootstrap-switch.js"></script>';



  if( $title == '') { $title = __('Dashboard', 'online_chat'); }

  $text  = '<div class="mb-head">';
  $text .= '<div class="mb-head-left">';
  $text .= '<h1>' . $title . '</h1>';
  $text .= '<h2>Online Chat Plugin</h2>';
  $text .= '</div>';
  $text .= '<div class="mb-head-right">';
  $text .= '<ul class="mb-menu">';
  $text .= '<li><a href="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=online_chat/admin/configure.php"><i class="fa fa-wrench"></i><span>' . __('Configure', 'online_chat') . '</span></a></li>';
  $text .= '</ul>';
  $text .= '</div>';
  $text .= '</div>';

  echo $text;
}



// ADMIN FOOTER
function oc_footer() {
  $pluginInfo = osc_plugin_get_info('online_chat/index.php');
  $text  = '<div class="mb-footer">';
  $text .= '<a target="_blank" class="mb-developer" href="http://osclasspoint.com"><img src="http://osclasspoint.com/favicon.ico" alt="MB Themes" /> osclasspoint.com</a>';
  $text .= '<a target="_blank" href="' . $pluginInfo['support_uri'] . '"><i class="fa fa-bug"></i> ' . __('Report Bug', 'online_chat') . '</a>';
  $text .= '<a target="_blank" href="http://forums.osclasspoint.com/"><i class="fa fa-comments"></i> ' . __('Support Forums', 'online_chat') . '</a>';
  $text .= '<a target="_blank" class="mb-last" href="mailto:info@osclasspoint.com"><i class="fa fa-envelope"></i> ' . __('Contact Us', 'online_chat') . '</a>';
  $text .= '<span class="mb-version">v' . $pluginInfo['version'] . '</span>';
  $text .= '</div>';

  return $text;
}



// ADD MENU LINK TO PLUGIN LIST
function oc_admin_menu() {
echo '<h3><a href="#">Online Chat Plugin</a></h3>
<ul> 
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/configure.php') . '">&raquo; ' . __('Configure', 'online_chat') . '</a></li>
</ul>';
}


// ADD MENU TO PLUGINS MENU LIST
osc_add_hook('admin_menu','oc_admin_menu', 1);



// DISPLAY CONFIGURE LINK IN LIST OF PLUGINS
function oc_conf() {
  osc_admin_render_plugin( osc_plugin_path( dirname(__FILE__) ) . '/admin/configure.php' );
}

osc_add_hook( osc_plugin_path( __FILE__ ) . '_configure', 'oc_conf' );	


// CALL WHEN PLUGIN IS ACTIVATED - INSTALLED
osc_register_plugin(osc_plugin_path(__FILE__), 'oc_call_after_install');

// SHOW UNINSTALL LINK
osc_add_hook(osc_plugin_path(__FILE__) . '_uninstall', 'oc_call_after_uninstall');

?>