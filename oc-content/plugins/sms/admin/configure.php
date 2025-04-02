<?php
  // Create menu
  $title = __('Configure', 'sms');
  sms_menu($title);


  // GET & UPDATE PARAMETERS
  // $variable = sms_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value

  $admin_phone_number = sms_param_update('admin_phone_number', 'plugin_action', 'value', 'plugin-sms');
  $provider = sms_param_update('provider', 'plugin_action', 'value', 'plugin-sms');
  $provider_alt = sms_param_update('provider_alt', 'plugin_action', 'value', 'plugin-sms');
  $site_name = sms_param_update('site_name', 'plugin_action', 'value', 'plugin-sms');
  $geo_ip_lookup = sms_param_update('geo_ip_lookup', 'plugin_action', 'check', 'plugin-sms');
  $verification_account = sms_param_update('verification_account', 'plugin_action', 'check', 'plugin-sms');
  $verification_listing = sms_param_update('verification_listing', 'plugin_action', 'check', 'plugin-sms');
  $verification_identifier = sms_param_update('verification_identifier', 'plugin_action', 'code', 'plugin-sms');
  $verification_countries = sms_param_update('verification_countries', 'plugin_action', 'value', 'plugin-sms');
  $initial_country = sms_param_update('initial_country', 'plugin_action', 'value', 'plugin-sms');
  $only_country = sms_param_update('only_country', 'plugin_action', 'value', 'plugin-sms');
  $custom_field_phone = sms_param_update('custom_field_phone', 'plugin_action', 'value', 'plugin-sms');
  $premium_groups = sms_param_update('premium_groups', 'plugin_action', 'value', 'plugin-sms');
  $verify_phone = sms_param_update('verify_phone', 'plugin_action', 'check', 'plugin-sms');
  $ignore_first_zero = sms_param_update('ignore_first_zero', 'plugin_action', 'check', 'plugin-sms');
  $ignore_first_zero_prefixes = sms_param_update('ignore_first_zero_prefixes', 'plugin_action', 'value', 'plugin-sms');

  $notify_user_account_validate = sms_param_update('notify_user_account_validate', 'plugin_action', 'check', 'plugin-sms');
  $notify_user_item_validate = sms_param_update('notify_user_item_validate', 'plugin_action', 'check', 'plugin-sms');
  $notify_user_item_activated = sms_param_update('notify_user_item_activated', 'plugin_action', 'check', 'plugin-sms');
  $notify_user_item_post = sms_param_update('notify_user_item_post', 'plugin_action', 'check', 'plugin-sms');
  $notify_user_item_contact = sms_param_update('notify_user_item_contact', 'plugin_action', 'check', 'plugin-sms');
  $notify_user_reset_password = sms_param_update('notify_user_reset_password', 'plugin_action', 'check', 'plugin-sms');

  $notify_admin_contact_form = sms_param_update('notify_admin_contact_form', 'plugin_action', 'check', 'plugin-sms');
  $notify_admin_item_post = sms_param_update('notify_admin_item_post', 'plugin_action', 'check', 'plugin-sms');
  $notify_admin_register = sms_param_update('notify_admin_register', 'plugin_action', 'check', 'plugin-sms');


  $available_providers = sms_providers();
  $available_providers_array = explode(',', $available_providers);


  $premium_groups_array = explode(',', $premium_groups);
  $premium_content = false;
  
  if(function_exists('osp_param')) {
    if(osp_param('groups_enabled') == 1) {
      $osp_groups = ModelOSP::newInstance()->getGroups();
      $premium_content = true;
    }
  }
  
  if(Params::getParam('plugin_action') == 'done') {
    message_ok( __('Settings were successfully saved', 'sms') );
  }


?>


<div class="mb-body">

  <div class="mb-notes">
    <div class="mb-line"><?php _e('Plugin will automatically identify phone number field for listings on OsclassPoint themes, Telephone plugin or Custom field that is set as phone number', 'sms'); ?></div>
  </div>


  <!-- CONFIGURE SECTION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-wrench"></i> <?php _e('Configure', 'sms'); ?></div>

    <div class="mb-inside mb-minify">
      <form name="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <?php if(!sms_is_demo()) { ?>
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>configure.php" />
        <input type="hidden" name="plugin_action" value="done" />
        <?php } ?>

        <div class="mb-row">
          <label for="admin_phone_number"><span><?php _e('Admin Phone Number', 'sms'); ?></span></label> 
          <input name="admin_phone_number" size="50" type="tel" value="<?php echo $admin_phone_number; ?>" />

          <div class="mb-explain"><?php _e('To receive SMS notifications. Format with +, no white spaces.', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="site_name"><span><?php _e('Site Name - Shortcut', 'sms'); ?></span></label> 
          <input name="site_name" size="40" type="text" maxlength="20" value="<?php echo $site_name; ?>" />

          <div class="mb-explain"><?php _e('Your site name used in SMS. Should be shortest possible, max 20 chars. Good example: OsclassPoint Bad example: Osclass themes and plugins - OsclassPoint.com.', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="provider"><span><?php _e('SMS Provider (gateway)', 'sms'); ?></span></label> 

          <select id="provider" name="provider">
            <?php foreach($available_providers_array as $p) { ?>
              <option value="<?php echo $p; ?>" <?php if($p == $provider) { ?>selected="selected"<?php } ?>><?php echo ucfirst($p); ?></option>
            <?php } ?>
          </select>

          <div class="mb-explain"><?php _e('Select primary SMS gateway / provider used to send messages. Note that selected provider must be configured in "SMS Providers" section first. When "Demo" is selected, plugin will show user what code should be put into confirmation field, however no SMS is being sent.', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="provider_alt"><span><?php _e('SMS Provider (gateway) - Alternative', 'sms'); ?></span></label> 

          <select id="provider_alt" name="provider_alt">
            <?php foreach($available_providers_array as $p) { ?>
              <option value="<?php echo $p; ?>" <?php if($p == $provider_alt) { ?>selected="selected"<?php } ?>><?php echo ucfirst($p); ?></option>
            <?php } ?>
          </select>

          <div class="mb-explain"><?php _e('Select alternative (backup) SMS gateway / provider used to send messages in case primary one has failed.', 'sms'); ?></div>
        </div>


        <div class="mb-row">
          <label for="verify_phone"><span><?php _e('Phone Number Uniqueness Check', 'sms'); ?></span></label> 
          <input type="checkbox" name="verify_phone" id="verify_phone" class="element-slide" <?php echo ($verify_phone == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, phone number user enters in user profile (both mobile & land) is checked for uniqueness. If some other user already use entered mobile or land phone, warning is shown to user.', 'sms'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="ignore_first_zero"><span><?php _e('Ignore First Zero in Number', 'sms'); ?></span></label> 
          <input type="checkbox" name="ignore_first_zero" id="ignore_first_zero" class="element-slide" <?php echo ($ignore_first_zero == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, first zero after country prefix is ignored. Means, if country prefix is +98, then phone numner +980xxxxxx and +98xxxxxx are considered as identical.', 'sms'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="ignore_first_zero_prefixes"><span><?php _e('Ignore First Zero Prefixes', 'sms'); ?></span></label> 
          <input name="ignore_first_zero_prefixes" size="80" type="text" value="<?php echo osc_esc_html($ignore_first_zero_prefixes); ?>" />

          <div class="mb-explain"><?php _e('For setting "Ignore First Zero in Number" define list of country phone prefixes where first zero is ignored. Delimit by comma. Example: +90,+420,+233', 'sms'); ?></div>
        </div>
        
        
        
        <div class="mb-row">
          <label for="verification_account"><span><?php _e('User Account Phone Verification', 'sms'); ?></span></label> 
          <input type="checkbox" name="verification_account" id="verification_account" class="element-slide" <?php echo ($verification_account == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, phone number used on user register (user account) must be verified. These fields will be required as well.', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="verification_listing"><span><?php _e('Listing Phone Verification', 'sms'); ?></span></label> 
          <input type="checkbox" name="verification_listing" id="verification_listing" class="element-slide" <?php echo ($verification_listing == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, phone number used on item post (listing) must be verified. These fields will be required as well.', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="verification_countries"><span><?php _e('Preferred Countries', 'sms'); ?></span></label> 
          <input name="verification_countries" size="80" type="text" value="<?php echo osc_esc_html($verification_countries); ?>" />

          <div class="mb-explain"><?php _e('Countries those will be at top of list with country code prefixes. Example: gb,us,de', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="initial_country"><span><?php _e('Initial Country', 'sms'); ?></span></label> 
          <input name="initial_country" size="20" type="text" maxlength="2" value="<?php echo osc_esc_html($initial_country); ?>" />

          <div class="mb-explain"><?php _e('Default country selected for every user. If left blank, plugin will try to auto-locate user and fill country code based on its IP address. Example: us', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="only_country"><span><?php _e('Only Countries', 'sms'); ?></span></label> 
          <input name="only_country" size="40" type="text" value="<?php echo osc_esc_html($only_country); ?>" />

          <div class="mb-explain"><?php _e('Restrict list of countries shown in prefix select box. Delimit with comma. Example: gb,us,de', 'sms'); ?></div>
        </div>


        <div class="mb-row">
          <label for="custom_field_phone"><span><?php _e('Custom Field Phone Identifier', 'sms'); ?></span></label> 
          <input name="custom_field_phone" size="50" type="text" value="<?php echo osc_esc_html($custom_field_phone); ?>" />

          <div class="mb-explain"><?php _e('Identifier of custom field used for phone number input on items (Listings > Custom Fields > Advanced > Identifier Name). Input should be text type. Field is optional, if your theme already has phone number field or telephone plugin is used.', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="verification_identifier"><span><?php _e('Phone Number CSS Selector', 'sms'); ?></span></label> 
          <input name="verification_identifier" size="50" type="text" value="<?php echo osc_esc_html($verification_identifier); ?>" />

          <div class="mb-explain"><?php _e('CSS selector for phone number field/input to add country select box in front of number', 'sms'); ?></div>
        </div>


        <div class="mb-row">
          <label for="geo_ip_lookup"><span><?php _e('Geo IP Lookup', 'sms'); ?></span></label> 
          <input type="checkbox" name="geo_ip_lookup" id="geo_ip_lookup" class="element-slide" <?php echo ($geo_ip_lookup == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, plugin will try to detect user phone prefix automatically. May cause slower page load.', 'sms'); ?></div>
        </div>


        <div class="mb-subtitle"><?php _e('User SMS notification settings', 'sms'); ?></div>



        <div class="mb-row">
          <label for="notify_user_account_validate"><span><?php _e('Account Validation', 'sms'); ?></span></label> 
          <input type="checkbox" name="notify_user_account_validate" id="notify_user_account_validate" class="element-slide" <?php echo ($notify_user_account_validate == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, user will receive SMS with account validation link.', 'sms'); ?></div>
        </div>


        <div class="mb-row">
          <label for="notify_user_reset_password"><span><?php _e('Password Reset', 'sms'); ?></span></label> 
          <input type="checkbox" name="notify_user_reset_password" id="notify_user_reset_password" class="element-slide" <?php echo ($notify_user_reset_password == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, user will receive SMS with link to reset its password.', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="notify_user_item_validate"><span><?php _e('Item Validation', 'sms'); ?></span></label> 
          <input type="checkbox" name="notify_user_item_validate" id="notify_user_item_validate" class="element-slide" <?php echo ($notify_user_item_validate == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, user will receive SMS with link to validate newly published listing.', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="notify_user_item_activated"><span><?php _e('Item Activated', 'sms'); ?></span></label> 
          <input type="checkbox" name="notify_user_item_activated" id="notify_user_item_activated" class="element-slide" <?php echo ($notify_user_item_activated == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, user will receive confirmation SMS that item has been activated.', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="notify_user_item_post"><span><?php _e('Item Publish', 'sms'); ?></span></label> 
          <input type="checkbox" name="notify_user_item_post" id="notify_user_item_post" class="element-slide" <?php echo ($notify_user_item_post == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, user will receive confirmation SMS that item has been published (if validation is not required).', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="notify_user_item_contact"><span><?php _e('Item Contact Form', 'sms'); ?></span></label> 
          <input type="checkbox" name="notify_user_item_contact" id="notify_user_item_contact" class="element-slide" <?php echo ($notify_user_item_contact == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, user will receive SMS with notification that someone has used contact form on its listing (with brief message details).', 'sms'); ?></div>
        </div>
        
        <div class="mb-row mb-row-select-multiple">
          <label for="premium_groups_multiple"><span><?php _e('Premium Groups', 'sms'); ?></span></label> 

          <input type="hidden" name="premium_groups" id="premium_groups" value="<?php echo $premium_groups; ?>"/>
          <select id="premium_groups_multiple" name="premium_groups_multiple" multiple>
            <?php if(!$premium_content || count($osp_groups) <= 0) { ?>
              <option value="" selected="selected"><?php _e('No groups in Osclass Pay Plugin', 'sms'); ?></option>
            <?php } else { ?>
              <?php foreach($osp_groups as $g) { ?>
                <option value="<?php echo $g['pk_i_id']; ?>" <?php if(in_array($g['pk_i_id'], $premium_groups_array)) { ?>selected="selected"<?php } ?>><?php echo $g['s_name']; ?></option>
              <?php } ?>
            <?php } ?>
          </select>

          <div class="mb-explain">
            <div class="mb-line"><?php _e('Select user groups from Osclass Pay Plugin those members will receive notification messages (SMS).', 'sms'); ?></div>
            <div class="mb-line"><?php _e('If no group is selected, all users will receive SMS notifications.', 'sms'); ?></div>
          </div>
        </div>


        <div class="mb-subtitle"><?php _e('Admin SMS notification settings', 'sms'); ?></div>


        <div class="mb-row">
          <label for="notify_admin_contact_form"><span><?php _e('Web Contact Form', 'sms'); ?></span></label> 
          <input type="checkbox" name="notify_admin_contact_form" id="notify_admin_contact_form" class="element-slide" <?php echo ($notify_admin_contact_form == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, admin will receive SMS notification that web contact form has been used (with brief message details).', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for="notify_admin_item_post"><span><?php _e('New Listing Published', 'sms'); ?></span></label> 
          <input type="checkbox" name="notify_admin_item_post" id="notify_admin_item_post" class="element-slide" <?php echo ($notify_admin_item_post == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, admin will receive SMS notification that new listing has been added on site.', 'sms'); ?></div>
        </div>

        <div class="mb-row">
          <label for=""><span><?php _e('New User Registered', 'sms'); ?></span></label> 
          <input type="checkbox" name="notify_admin_register" id="notify_admin_register" class="element-slide" <?php echo ($notify_admin_register == 1 ? 'checked' : ''); ?>/>
          
          <div class="mb-explain"><?php _e('When enabled, admin will receive SMS notification when new user has registered on site.', 'sms'); ?></div>
        </div>


        <div class="mb-row">&nbsp;</div>

        <div class="mb-foot">
          <?php if(sms_is_demo()) { ?>
            <a class="mb-button mb-has-tooltip disabled" onclick="return false;" style="cursor:not-allowed;opacity:0.5;" title="<?php echo osc_esc_html(__('This is demo site', 'sms')); ?>"><?php _e('Save', 'sms');?></a>
          <?php } else { ?>
            <button type="submit" class="mb-button"><?php _e('Save', 'sms');?></button>
          <?php } ?>
        </div>
      </form>
    </div>
  </div>

</div>


<?php echo sms_footer(); ?>