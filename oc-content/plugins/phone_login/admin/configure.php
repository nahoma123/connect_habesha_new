<?php
  // Create menu
  $title = __('Configure', 'phone_login');
  phl_menu($title);


  // GET & UPDATE PARAMETERS
  // $variable = mb_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value

  $enable = mb_param_update('enable', 'plugin_action', 'check', 'plugin-phone_login');
  $hook_phone = mb_param_update('hook_phone', 'plugin_action', 'check', 'plugin-phone_login');


  if(Params::getParam('plugin_action') == 'done') {
    message_ok( __('Settings were successfully saved', 'phone_login') );
  }
?>


<div class="mb-body">

  <!-- CONFIGURE SECTION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-wrench"></i> <?php _e('Configure', 'phone_login'); ?></div>

    <div class="mb-inside mb-minify">
      <form name="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <?php if(!phl_is_demo()) { ?>
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>configure.php" />
        <input type="hidden" name="plugin_action" value="done" />
        <?php } ?>

        <div class="mb-row">
          <label for="enable" class="h1"><span><?php _e('Enable Phone Login', 'phone_login'); ?></span></label> 
          <input name="enable" type="checkbox" class="element-slide" <?php echo ($enable == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('When enabled, visitors can login also using mobile phone.', 'phone_login'); ?></div>
        </div>

        <div class="mb-row">
          <label for="hook_phone" class="h2"><span><?php _e('Add Phone to Register Page', 'phone_login'); ?></span></label> 
          <input name="hook_phone" type="checkbox" class="element-slide" <?php echo ($hook_phone == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('When enabled, phone number input will be added to registration form.', 'phone_login'); ?></div>
        </div>


        <div class="mb-row">&nbsp;</div>

        <div class="mb-foot">
          <?php if(phl_is_demo()) { ?>
            <a class="mb-button mb-has-tooltip disabled" onclick="return false;" style="cursor:not-allowed;opacity:0.5;" title="<?php echo osc_esc_html(__('This is demo site', 'phone_login')); ?>"><?php _e('Save', 'phone_login');?></a>
          <?php } else { ?>
            <button type="submit" class="mb-button"><?php _e('Save', 'phone_login');?></button>
          <?php } ?>
        </div>
      </form>
    </div>
  </div>


  <!-- PLUGIN INTEGRATION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-wrench"></i> <?php _e('Plugin Setup', 'phone_login'); ?></div>

    <div class="mb-inside">

      <div class="mb-row">
        <div class="mb-line"><?php _e('Plugin does not require any modifications in theme files.', 'phone_login'); ?></div>

      </div>
    </div>
  </div>
</div>


<?php echo phl_footer(); ?>