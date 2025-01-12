<?php
/*
  Plugin Name: Phone Login Plugin
  Plugin URI: https://osclasspoint.com/osclass-plugins/extra-fields-and-other/phone-number-login-plugin-i97
  Description: Phone Login enable users to login with phone number
  Version: 1.1.7
  Author: MB Themes
  Author URI: https://osclasspoint.com
  Author Email: info@osclasspoint.com
  Short Name: phone_login
  Plugin update URI: phone_login
  Support URI: https://forums.osclasspoint.com/
  Product Key: FJGisjE1yqVWa3KVoCzY
*/

define('PHL_PHONE_CHECK_ADVANCED', true);

require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'model/ModelPHL.php';
require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'functions.php';

osc_register_script('phl-user', osc_base_url() . 'oc-content/plugins/phone_login/js/user.js?v=' . date('YmdHis'), array('jquery'));
osc_enqueue_script('phl-user');


// INSTALL FUNCTION - DEFINE VARIABLES
function phl_call_after_install() {
  osc_set_preference('enable', 1, 'plugin-phone_login', 'INTEGER');
  osc_set_preference('hook_phone', 0, 'plugin-phone_login', 'INTEGER');

  ModelPHL::newInstance()->install();
}


function phl_call_after_uninstall() {
  ModelPHL::newInstance()->uninstall();
}



// ADMIN MENU
function phl_menu($title = NULL) {
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/phone_login/css/admin.css?v=' . date('YmdHis') . '" rel="stylesheet" type="text/css" />';
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/phone_login/css/bootstrap-switch.css" rel="stylesheet" type="text/css" />';
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/phone_login/css/tipped.css" rel="stylesheet" type="text/css" />';
  echo '<link href="//fonts.googleapis.com/css?family=Open+Sans:300,600&amp;subset=latin,latin-ext" rel="stylesheet" type="text/css" />';
  echo '<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/phone_login/js/admin.js?v=' . date('YmdHis') . '"></script>';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/phone_login/js/tipped.js"></script>';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/phone_login/js/bootstrap-switch.js"></script>';



  if( $title == '') { $title = __('Configure', 'phone_login'); }

  $text  = '<div class="mb-head">';
  $text .= '<div class="mb-head-left">';
  $text .= '<h1>' . $title . '</h1>';
  $text .= '<h2>Phone Login Plugin</h2>';
  $text .= '</div>';
  $text .= '<div class="mb-head-right">';
  $text .= '<ul class="mb-menu">';
  $text .= '<li><a href="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=phone_login/admin/configure.php"><i class="fa fa-wrench"></i><span>' . __('Configure', 'phone_login') . '</span></a></li>';
  $text .= '</ul>';
  $text .= '</div>';
  $text .= '</div>';

  echo $text;
}



// ADMIN FOOTER
function phl_footer() {
  $pluginInfo = osc_plugin_get_info('phone_login/index.php');
  $text  = '<div class="mb-footer">';
  $text .= '<a target="_blank" class="mb-developer" href="https://osclasspoint.com"><img src="https://osclasspoint.com/favicon.ico" alt="OsclassPoint Market" /> OsclassPoint Market</a>';
  $text .= '<a target="_blank" href="' . $pluginInfo['support_uri'] . '"><i class="fa fa-bug"></i> ' . __('Report Bug', 'phone_login') . '</a>';
  $text .= '<a target="_blank" href="https://forums.osclasspoint.com/"><i class="fa fa-handshake-o"></i> ' . __('Support Forums', 'phone_login') . '</a>';
  $text .= '<a target="_blank" class="mb-last" href="mailto:info@osclasspoint.com"><i class="fa fa-envelope"></i> ' . __('Contact Us', 'phone_login') . '</a>';
  $text .= '<span class="mb-version">v' . $pluginInfo['version'] . '</span>';
  $text .= '</div>';

  return $text;
}



// ADD MENU LINK TO PLUGIN LIST
function phl_admin_menu() {
echo '<h3><a href="#">Phone Login Plugin</a></h3>
<ul> 
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/configure.php') . '">&raquo; ' . __('Configure', 'phone_login') . '</a></li>
</ul>';
}


// ADD MENU TO PLUGINS MENU LIST
osc_add_hook('admin_menu','phl_admin_menu', 1);



// DISPLAY CONFIGURE LINK IN LIST OF PLUGINS
function phl_conf() {
  osc_admin_render_plugin( osc_plugin_path( dirname(__FILE__) ) . '/admin/configure.php' );
}

osc_add_hook( osc_plugin_path( __FILE__ ) . '_configure', 'phl_conf' );	


// CALL WHEN PLUGIN IS ACTIVATED - INSTALLED
osc_register_plugin(osc_plugin_path(__FILE__), 'phl_call_after_install');

// SHOW UNINSTALL LINK
osc_add_hook(osc_plugin_path(__FILE__) . '_uninstall', 'phl_call_after_uninstall');

?>