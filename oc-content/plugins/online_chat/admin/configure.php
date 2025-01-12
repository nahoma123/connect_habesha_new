<?php
  // Create menu
  $title = __('Configure', 'online_chat');
  oc_menu($title);


  // GET & UPDATE PARAMETERS
  // $variable = mb_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value

  $hook_button = mb_param_update( 'hook_button', 'plugin_action', 'check', 'plugin-online_chat' );
  $refresh_message = mb_param_update( 'refresh_message', 'plugin_action', 'value', 'plugin-online_chat' );
  $refresh_user = mb_param_update( 'refresh_user', 'plugin_action', 'value', 'plugin-online_chat' );
  $refresh_closed = mb_param_update( 'refresh_closed', 'plugin_action', 'value', 'plugin-online_chat' );
  $delete_days = mb_param_update( 'delete_days', 'plugin_action', 'value', 'plugin-online_chat' );


  if (!(version_compare(PHP_VERSION, '5.5.0') >= 0)) {
    message_error( __('Your PHP version is:', 'online_chat') . ' ' . PHP_VERSION . '. ' . __('This plugin require at least PHP 5.5.0 or later.', 'online_chat') );
  }

  if ( OSC_DEBUG || OSC_DEBUG_DB ) {
    message_error( __('Debug mode is enabled, online chat will not be functional! Disable debug mode in config.php', 'online_chat') );
  }

  if(Params::getParam('plugin_action') == 'done') {
    message_ok( __('Settings were successfully saved', 'online_chat') );
  }
?>



<div class="mb-body">
  <!-- CONFIGURE SECTION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-cog"></i> <?php _e('Configure', 'online_chat'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <?php if(!oc_is_demo()) { ?>
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>configure.php" />
        <input type="hidden" name="plugin_action" value="done" />
        <?php } ?>

        <div class="mb-row">
          <label for="hook_button" class="h1"><span><?php _e('Hook Initiate Chat Button', 'online_chat'); ?></span></label> 
          <input name="hook_button" id="hook_button" class="element-slide" type="checkbox" <?php echo ($hook_button == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('Button will be automatically hooked to item page (using item_detail hook) and no theme modifications are required.', 'online_chat'); ?></div>
        </div>

        <div class="mb-row">
          <label for="refresh_message" class="h2"><span><?php _e('Message Check Time', 'online_chat'); ?></span></label> 
          <input size="6" name="refresh_message" id="refresh_message" class="mb-short" type="text" value="<?php echo $refresh_message; ?>" />
          <div class="mb-input-desc"><?php _e('seconds', 'online_chat'); ?></div>

          <div class="mb-explain"><?php _e('Specify time in seconds how often server check for new messages and show them to user. (default: 10)', 'online_chat'); ?></div>
        </div>

        <div class="mb-row">
          <label for="refresh_user" class="h2"><span><?php _e('User Online Check Time', 'online_chat'); ?></span></label> 
          <input size="6" name="refresh_user" id="refresh_user" class="mb-short" type="text" value="<?php echo $refresh_user; ?>" />
          <div class="mb-input-desc"><?php _e('seconds', 'online_chat'); ?></div>

          <div class="mb-explain"><?php _e('Specify time in seconds how often server check if user is online or not. (default: 120)', 'online_chat'); ?></div>
        </div>

        <div class="mb-row">
          <label for="refresh_closed" class="h2"><span><?php _e('Chat Closed Check Time', 'online_chat'); ?></span></label> 
          <input size="6" name="refresh_closed" id="refresh_closed" class="mb-short" type="text" value="<?php echo $refresh_closed; ?>" />
          <div class="mb-input-desc"><?php _e('seconds', 'online_chat'); ?></div>

          <div class="mb-explain"><?php _e('Specify time in seconds how often server check if chat has been closed. (default: 60)', 'online_chat'); ?></div>
        </div>

        <div class="mb-row">
          <label for="delete_days" class="h3"><span><?php _e('Inactive Chat Removed After', 'online_chat'); ?></span></label> 
          <input size="6" name="delete_days" id="delete_days" class="mb-short" type="text" value="<?php echo $delete_days; ?>" />
          <div class="mb-input-desc"><?php _e('days', 'online_chat'); ?></div>

          <div class="mb-explain"><?php _e('Specify after how many days is inactive chat removed. (default: 720)', 'online_chat'); ?></div>
        </div>

        <div class="mb-foot">
          <?php if(!oc_is_demo()) { ?>
            <button type="submit" class="mb-button"><?php _e('Save', 'online_chat');?></button>
          <?php } else { ?>
            <a href="#" onclick="return false" class="mb-button"><?php _e('Save (demo - disabled)', 'online_chat');?></button>
          <?php } ?>
        </div>
      </form>
    </div>
  </div>



  <!-- PLUGIN INTEGRATION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-wrench"></i> <?php _e('Plugin Setup', 'online_chat'); ?></div>

    <div class="mb-inside">
      <div class="mb-row"><?php _e('If you want to place initiate chat button on specific place, you can use built-in function. This function has optional parameter $user_id that does not need to be used.', 'online_chat'); ?></div>
      <div class="mb-code">&lt;?php if(function_exists('oc_chat_button')) { echo oc_chat_button(); } ?&gt;</div>

      <div class="mb-row" style="margin-top:30px;"><?php _e('If you want to show circle with user status (online - green, offline - gray), you can use built-in function. This function has optional parameter $user_id that does not need to be used.', 'online_chat'); ?></div>
      <div class="mb-code">&lt;?php if(function_exists('oc_user_status')) { echo oc_user_status(); } ?&gt;</div>
    </div>
  </div>



  <!-- HELP TOPICS -->
  <div class="mb-box" id="mb-help">
    <div class="mb-head"><i class="fa fa-question-circle"></i> <?php _e('Help', 'online_chat'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-help"><span class="sup">(1)</span> <div class="h1"><?php _e('Enable to add button to start chat to item page into hook item_detail. Then no theme modifications are required.', 'online_chat'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(2)</span> <div class="h2"><?php _e('Specify time in seconds. Do not put less than 5 seconds. Note that more often refresh means more load to server and your database, therefore make sure you are not overloading your machine and your site is running smoothly. Minimum time for message refresh is 3 seconds (less is risky). Minimum time for user online check is 30 seconds (less is risky). Minimum time for checking of chat closed is 30 seconds (less is risky). Put only integers!', 'online_chat'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(3)</span> <div class="h3"><?php _e('Specify after how many days are inactive chats removed. Plugin will check last activity on chat and remove it. Not that more days you add, more server resources are required as plugin tables are larger. Do not put more than 30 days. If you have hundreds of users online at same time, put it to 2 days to reduce table size as much as possible. Larger table is, more time takes to check for new messages.', 'online_chat'); ?></div></div>
   </div>
  </div>
</div>

<?php echo oc_footer(); ?>