<?php
  // Create menu
  $title = __('provider', 'sms');
  sms_menu($title);


  // GET & UPDATE PARAMETERS
  // $variable = sms_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value

  $ringcaptcha_app_key = sms_param_update('ringcaptcha_app_key', 'plugin_action', 'value', 'plugin-sms');
  $ringcaptcha_api_key = sms_param_update('ringcaptcha_api_key', 'plugin_action', 'value', 'plugin-sms');
  
  $way2sms_username = sms_param_update('way2sms_username', 'plugin_action', 'value', 'plugin-sms');
  $way2sms_password = sms_param_update('way2sms_password', 'plugin_action', 'value', 'plugin-sms');
  
  $textlocalin_api_key = sms_param_update('textlocalin_api_key', 'plugin_action', 'value', 'plugin-sms');
  $textlocalin_sender_id = sms_param_update('textlocalin_sender_id', 'plugin_action', 'value', 'plugin-sms');
  $textlocalin_password = sms_param_update('textlocalin_password', 'plugin_action', 'value', 'plugin-sms');
  
  $twilio_account_sid = sms_param_update('twilio_account_sid', 'plugin_action', 'value', 'plugin-sms');
  $twilio_auth_token = sms_param_update('twilio_auth_token', 'plugin_action', 'value', 'plugin-sms');
  $twilio_number = sms_param_update('twilio_number', 'plugin_action', 'value', 'plugin-sms');
  
  $textlocalcom_api_key = sms_param_update('textlocalcom_api_key', 'plugin_action', 'value', 'plugin-sms');
  $textlocalcom_sender = sms_param_update('textlocalcom_sender', 'plugin_action', 'value', 'plugin-sms');

  $msg91_api_key = sms_param_update('msg91_api_key', 'plugin_action', 'value', 'plugin-sms');
  $msg91_sender_id = sms_param_update('msg91_sender_id', 'plugin_action', 'value', 'plugin-sms');
  $msg91_template_id = sms_param_update('msg91_template_id', 'plugin_action', 'value', 'plugin-sms');
  $msg91_short_url = sms_param_update('msg91_short_url', 'plugin_action', 'check', 'plugin-sms');
  
  $gatewayapi_api_token = sms_param_update('gatewayapi_api_token', 'plugin_action', 'value', 'plugin-sms');
  $gatewayapi_sender = sms_param_update('gatewayapi_sender', 'plugin_action', 'value', 'plugin-sms');
  

  $budgetsms_user_id = sms_param_update('budgetsms_user_id', 'plugin_action', 'value', 'plugin-sms');
  $budgetsms_username = sms_param_update('budgetsms_username', 'plugin_action', 'value', 'plugin-sms');
  $budgetsms_handle = sms_param_update('budgetsms_handle', 'plugin_action', 'value', 'plugin-sms');
  $budgetsms_from = sms_param_update('budgetsms_from', 'plugin_action', 'value', 'plugin-sms');
  
  $plivo_smsh_id = sms_param_update('plivo_smsh_id', 'plugin_action', 'value', 'plugin-sms');
  $plivo_smsh_token = sms_param_update('plivo_smsh_token', 'plugin_action', 'value', 'plugin-sms');
  $plivo_sender_id = sms_param_update('plivo_sender_id', 'plugin_action', 'value', 'plugin-sms');

  $notify_user_id = sms_param_update('notify_user_id', 'plugin_action', 'value', 'plugin-sms');
  $notify_api_key = sms_param_update('notify_api_key', 'plugin_action', 'value', 'plugin-sms');
  $notify_sender_id = sms_param_update('notify_sender_id', 'plugin_action', 'value', 'plugin-sms');

  $assistent_api_username = sms_param_update('assistent_api_username', 'plugin_action', 'value', 'plugin-sms');
  $assistent_api_pass = sms_param_update('assistent_api_pass', 'plugin_action', 'value', 'plugin-sms');
  $assistent_api_sender = sms_param_update('assistent_api_sender', 'plugin_action', 'value', 'plugin-sms');

  $routee_app_id = sms_param_update('routee_app_id', 'plugin_action', 'value', 'plugin-sms');
  $routee_app_secret = sms_param_update('routee_app_secret', 'plugin_action', 'value', 'plugin-sms');
  $routee_from = sms_param_update('routee_from', 'plugin_action', 'value', 'plugin-sms');
  $routee_country_code = sms_param_update('routee_country_code', 'plugin_action', 'value', 'plugin-sms');


  osc_reset_preferences();    


  $provider_array = explode(',', sms_providers());

  if(Params::getParam('plugin_action') == 'done') {
    message_ok( __('Settings were successfully saved', 'sms') );
  }



  // TEST SMS
  if(Params::getParam('plugin_action') == 'test') {
    $output = sms_send(Params::getParam('phone_number'), Params::getParam('message'), 'ADMIN_TEST', Params::getParam('provider'));

    if($output['status'] == 'OK') {
      message_ok(sprintf(__('SMS has been sent. Provider response: %s', 'sms'), ($output['message'] <> '' ? $output['message'] : '-')));
    } else {
      message_error(sprintf(__('There was error while sending SMS. Provider response: %s', 'sms'), ($output['message'] <> '' ? $output['message'] : '-')));
    }
  }


  // CHECK BALANCE
  if(Params::getParam('plugin_action') == 'balance') {
    $balance = sms_get_balance(Params::getParam('provider'));

    if ($balance['status'] == 'ERROR') {
      message_error(sprintf(__('There was problem to retrieve balance: %s', 'sms'), $balance['response']));
    } else {
      message_ok(sprintf(__('Balance successfully retrieved. Your balance is: %s', 'sms'), $balance['response']));
    }
  }




  // SCROLL TO DIV
  if(Params::getParam('plugin_action') == 'done') {
    sms_js_scroll('.mb-configure');
  } else if(Params::getParam('plugin_action') == 'test') {
    sms_js_scroll('.mb-test-sms');
  } else if(Params::getParam('plugin_action') == 'balance') {
    sms_js_scroll('.mb-balance');
  }


?>


<div class="mb-body">

  <!-- PROVIDERS SECTION -->
  <div class="mb-box mb-configure">
    <div class="mb-head"><i class="fa fa-id-card"></i> <?php _e('SMS Providers', 'sms'); ?></div>

    <div class="mb-inside mb-minify">
      <form name="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <?php if(!sms_is_demo()) { ?>
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>provider.php" />
        <input type="hidden" name="plugin_action" value="done" />
        <?php } ?>



        <!-- TWILIO.COM -->
        <div class="mb-method mb-twilio <?php if(sms_param('twilio_account_sid') <> '') { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if(sms_param('twilio_account_sid') <> '') { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Twilio.com', 'sms'); ?></span>
            <img src="<?php echo sms_plugin_url(); ?>img/providers/twilio.png"/>
          </div>
          
          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://www.twilio.com/console" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Twilio Dashboard', 'sms'); ?></span></a>
              <a href="https://www.twilio.com/docs/sms/quickstart/php" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Twilio Docs', 'sms'); ?></span></a>
            </div>


            <div class="mb-line">
              <label for="twilio_account_sid"><span><?php _e('Twilio Account SID', 'sms'); ?></span></label>
              <input name="twilio_account_sid" id="twilio_account_sid" type="text" size="80" value="<?php echo $twilio_account_sid; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="twilio_auth_token"><span><?php _e('Twilio Auth Token', 'sms'); ?></span></label>
              <input name="twilio_auth_token" id="twilio_auth_token" type="text" size="80" value="<?php echo $twilio_auth_token; ?>" />
            </div>

            <div class="mb-line">
              <label for="twilio_number"><span><?php _e('Twilio Phone Number', 'sms'); ?></span></label>
              <input name="twilio_number" id="twilio_number" type="text" size="30" value="<?php echo $twilio_number; ?>" />
            </div>

          </div>
        </div>

        
        <!-- GATEWAYAPI.COM -->
        <div class="mb-method mb-gatewayapi <?php if(sms_param('gatewayapi_api_token') <> '') { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if(sms_param('gatewayapi_api_token') <> '') { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('GatewayAPI.com', 'sms'); ?></span>
            <img src="<?php echo sms_plugin_url(); ?>img/providers/gatewayapi.png"/>
          </div>
          
          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://gatewayapi.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('GatewayAPI Home', 'sms'); ?></span></a>
              <a href="https://gatewayapi.com/docs/rest.html#check-account-balance" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('GatewayAPI Docs', 'sms'); ?></span></a>
            </div>


            <div class="mb-line">
              <label for="gatewayapi_api_token"><span><?php _e('GatewayAPI API Token', 'sms'); ?></span></label>
              <input name="gatewayapi_api_token" id="gatewayapi_api_token" type="text" size="80" value="<?php echo $gatewayapi_api_token; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="gatewayapi_sender"><span><?php _e('GatewayAPI Sender', 'sms'); ?></span></label>
              <input name="gatewayapi_sender" id="gatewayapi_sender" type="text" maxlength="11" value="<?php echo $gatewayapi_sender; ?>" />
            </div>

          </div>
        </div>


        <!-- RINGCAPTCHA -->
        <div class="mb-method mb-ringcaptcha <?php if(sms_param('ringcaptcha_app_key') <> '') { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if(sms_param('ringcaptcha_app_key') <> '') { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('RingCaptcha', 'sms'); ?></span>
            <img src="<?php echo sms_plugin_url(); ?>img/providers/ringcaptcha.png"/>
          </div>

          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://my.ringcaptcha.com/apps" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('RingCaptcha Dashboard', 'sms'); ?></span></a>
              <a href="https://my.ringcaptcha.com/docs/api#!#sms-gateway" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('RingCaptcha Docs', 'sms'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="ringcaptcha_app_key"><span><?php _e('RingCaptcha App Key', 'sms'); ?></span></label>
              <input name="ringcaptcha_app_key" id="ringcaptcha_app_key" type="text" size="40" value="<?php echo $ringcaptcha_app_key; ?>" />
            </div>

            <div class="mb-line">
              <label for="ringcaptcha_api_key"><span><?php _e('RingCaptcha Api Key', 'sms'); ?></span></label>
              <input name="ringcaptcha_api_key" id="ringcaptcha_api_key" type="password" autocomplete="new-password" size="60" value="<?php echo $ringcaptcha_api_key; ?>" />
            </div>
           
          </div>
        </div>
        
        

        <!-- TEXTLOCAL.COM -->
        <div class="mb-method mb-textlocalcom <?php if(sms_param('textlocalcom_api_key') <> '') { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if(sms_param('textlocalcom_api_key') <> '') { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('TextLocal.com', 'sms'); ?></span>
            <img src="<?php echo sms_plugin_url(); ?>img/providers/textlocal.png"/>
          </div>
          
          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://www.textlocal.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('TextLocal.com Home', 'sms'); ?></span></a>
              <a href="https://www.textlocal.com/integrations/api/#choose-language" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('TextLocal.com Docs', 'sms'); ?></span></a>
            </div>


            <div class="mb-line">
              <label for="textlocalcom_api_key"><span><?php _e('TextLocal.com Api Key', 'sms'); ?></span></label>
              <input name="textlocalcom_api_key" id="textlocalcom_api_key" type="text" value="<?php echo $textlocalcom_api_key; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="textlocalcom_sender"><span><?php _e('TextLocal.com Sender', 'sms'); ?></span></label>
              <input name="textlocalcom_sender" id="textlocalcom_sender" type="text" maxlength="11" value="<?php echo $textlocalcom_sender; ?>" />
            </div>

          </div>
        </div>
        
        
        
        <!-- TEXTLOCAL.IN -->
        <div class="mb-method mb-textlocalin <?php if(sms_param('textlocalin_api_key') <> '') { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if(sms_param('textlocalin_api_key') <> '') { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('TextLocal.in', 'sms'); ?></span>
            <img src="<?php echo sms_plugin_url(); ?>img/providers/textlocal.png"/>
          </div>
          
          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://www.textlocal.in/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('TextLocal.in Home', 'sms'); ?></span></a>
              <a href="https://api.textlocal.in/docs/sendsms" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('TextLocal.in Docs', 'sms'); ?></span></a>
            </div>


            <div class="mb-line">
              <label for="textlocalin_api_key"><span><?php _e('TextLocal.in Api Key', 'sms'); ?></span></label>
              <input name="textlocalin_api_key" id="textlocalin_api_key" type="text" value="<?php echo $textlocalin_api_key; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="textlocalin_sender_id"><span><?php _e('TextLocal.in Sender Id', 'sms'); ?></span></label>
              <input name="textlocalin_sender_id" id="textlocalin_sender_id" type="text" value="<?php echo $textlocalin_sender_id; ?>" />
            </div>
            
            <?php if(1==2) { ?>
            <div class="mb-line">
              <label for="textlocalin_password"><span><?php _e('TextLocal.in Password', 'sms'); ?></span></label>
              <input name="textlocalin_password" id="textlocalin_password" type="password" autocomplete="new-password" value="<?php echo $textlocalin_password; ?>" />
            </div>
            <?php } ?>

          </div>
        </div>
        
        

        <!-- WAY2SMS -->
        <div class="mb-method mb-way2sms <?php if(sms_param('way2sms_username') <> '') { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if(sms_param('way2sms_username') <> '') { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Way2SMS', 'sms'); ?></span>
            <img src="<?php echo sms_plugin_url(); ?>img/providers/way2sms.png"/>
          </div>
          
          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://www.way2sms.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Way2SMS Home', 'sms'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="way2sms_username"><span><?php _e('Way2SMS Username', 'sms'); ?></span></label>
              <input name="way2sms_username" id="way2sms_username" type="text" value="<?php echo $way2sms_username; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="way2sms_password"><span><?php _e('Way2SMS Password', 'sms'); ?></span></label>
              <input name="way2sms_password" id="way2sms_password" type="password" autocomplete="new-password" value="<?php echo $way2sms_password; ?>" />
            </div>

          </div>
        </div>


        
        <!-- MSG91 -->
        <div class="mb-method mb-msg91 <?php if(sms_param('msg91_api_key') <> '') { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if(sms_param('msg91_api_key') <> '') { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Msg91', 'sms'); ?></span>
            <img src="<?php echo sms_plugin_url(); ?>img/providers/msg91.png"/>
          </div>
          
          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://msg91.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Msg91 Home', 'sms'); ?></span></a>
              <a href="https://docs.msg91.com/collection/msg91-api-integration/5/send-sms-v2/TZ2IXQHS" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Msg91 Docs', 'sms'); ?></span></a>
            </div>

            <div class="mb-row mb-notes">
              <div class="mb-line"><?php _e('Following variables are used: OTP - One time verification code, MESSAGE - SMS text, ACTION - action code.', 'sms'); ?></div>
            </div>


            <div class="mb-line">
              <label for="msg91_api_key"><span><?php _e('Msg91 Api Key (auth key)', 'sms'); ?></span></label>
              <input name="msg91_api_key" id="msg91_api_key" type="text" value="<?php echo $msg91_api_key; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="msg91_sender_id"><span><?php _e('Msg91 Sender Id', 'sms'); ?></span></label>
              <input name="msg91_sender_id" id="msg91_sender_id" type="text" value="<?php echo $msg91_sender_id; ?>" />
            </div>

            <div class="mb-line">
              <label for="msg91_template_id"><span><?php _e('Msg91 Template Id', 'sms'); ?></span></label>
              <input name="msg91_template_id" id="msg91_template_id" type="text" value="<?php echo $msg91_template_id; ?>" />
            </div>

            <div class="mb-row">
              <label for="msg91_short_url"><span><?php _e('Msg91 Short URL', 'sms'); ?></span></label> 
              <input type="checkbox" name="msg91_short_url" id="msg91_short_url" class="element-slide" <?php echo ($msg91_short_url == 1 ? 'checked' : ''); ?>/>
            </div>
          </div>
        </div>
        
        
        
        <!-- BUDGETSMS -->
        <div class="mb-method mb-budgetsms <?php if(sms_param('budgetsms_user_id') <> '') { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if(sms_param('budgetsms_user_id') <> '') { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Budget SMS', 'sms'); ?></span>
            <img src="<?php echo sms_plugin_url(); ?>img/providers/budgetsms.png"/>
          </div>
          
          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://www.budgetsms.net/controlpanel/dashboard/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Budget SMS Dashboard', 'sms'); ?></span></a>
              <a href="https://www.budgetsms.net/sms-http-api/send-sms/" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Budget SMS Docs', 'sms'); ?></span></a>
            </div>


            <div class="mb-line">
              <label for="budgetsms_user_id"><span><?php _e('Budget SMS User Id', 'sms'); ?></span></label>
              <input name="budgetsms_user_id" id="budgetsms_user_id" type="text" value="<?php echo $budgetsms_user_id; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="budgetsms_username"><span><?php _e('Budget SMS Username', 'sms'); ?></span></label>
              <input name="budgetsms_username" id="budgetsms_username" type="text" value="<?php echo $budgetsms_username; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="budgetsms_from"><span><?php _e('Budget SMS From', 'sms'); ?></span></label>
              <input name="budgetsms_from" id="budgetsms_from" type="text" value="<?php echo $budgetsms_from; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="budgetsms_handle"><span><?php _e('Budget SMS Handle', 'sms'); ?></span></label>
              <input name="budgetsms_handle" id="budgetsms_handle" type="password" autocomplete="new-password" value="<?php echo $budgetsms_handle; ?>" />
            </div>

          </div>
        </div>
        
        
        
        <!-- PLIVO -->
        <div class="mb-method mb-plivo <?php if(sms_param('plivo_smsh_id') <> '') { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if(sms_param('plivo_smsh_id') <> '') { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Plivo', 'sms'); ?></span>
            <img src="<?php echo sms_plugin_url(); ?>img/providers/plivo.png"/>
          </div>
          
          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://console.plivo.com/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Plivo Dashboard', 'sms'); ?></span></a>
              <a href="https://www.plivo.com/docs/sms/" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Plivo Docs', 'sms'); ?></span></a>
            </div>


            <div class="mb-line">
              <label for="plivo_smsh_id"><span><?php _e('Plivo Smsh Id', 'sms'); ?></span></label>
              <input name="plivo_smsh_id" id="plivo_smsh_id" type="text" value="<?php echo $plivo_smsh_id; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="plivo_sender_id"><span><?php _e('Plivo Sender Id', 'sms'); ?></span></label>
              <input name="plivo_sender_id" id="plivo_sender_id" type="text" value="<?php echo $plivo_sender_id; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="plivo_smsh_token"><span><?php _e('Plivo Smsh Token', 'sms'); ?></span></label>
              <input name="plivo_smsh_token" id="plivo_smsh_token" type="password" autocomplete="new-password" value="<?php echo $plivo_smsh_token; ?>" />
            </div>

          </div>
        </div>
        

        <!-- NOTIFY.LK-->
        <div class="mb-method mb-notifylk <?php if(sms_param('notify_user_id') <> '') { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if(sms_param('notify_user_id') <> '') { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Notify.lk', 'sms'); ?></span>
            <img src="<?php echo sms_plugin_url(); ?>img/providers/notifylk.png"/>
          </div>
          
          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://app.notify.lk/dashboard" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Notify.lk Dashboard', 'sms'); ?></span></a>
              <a href="https://developer.notify.lk/api-endpoints/" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Notify.lk Api Endpoints', 'sms'); ?></span></a>
            </div>


            <div class="mb-line">
              <label for="notify_user_id"><span><?php _e('Notify.lk User ID', 'sms'); ?></span></label>
              <input name="notify_user_id" id="notify_user_id" type="text" value="<?php echo $notify_user_id; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="notify_api_key"><span><?php _e('Notify.lk Api Key', 'sms'); ?></span></label>
              <input name="notify_api_key" id="notify_api_key" type="password" autocomplete="new-password" value="<?php echo $notify_api_key; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="notify_sender_id"><span><?php _e('Notify.lk Sender ID', 'sms'); ?></span></label>
              <input name="notify_sender_id" id="notify_sender_id" type="text" value="<?php echo $notify_sender_id; ?>" />
              <div class="mb-explain"><?php _e('Default value, if you have not requested any, is NotifyDEMO', 'sms'); ?></div>
            </div>

          </div>
        </div>

        <!-- SMS-ASSISTENT.BY-->
        <div class="mb-method mb-smsassistentby <?php if(sms_param('assistent_api_username') <> '') { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if(sms_param('assistent_api_username') <> '') { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('SMS-Assistent.by', 'sms'); ?></span>
            <img src="<?php echo sms_plugin_url(); ?>img/providers/smsassistent.png"/>
          </div>
          
          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://sms-assistent.by/" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('SMS-Assistent.by Home', 'sms'); ?></span></a>
              <a href="https://sms-assistent.by/uslugi/sms/" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('SMS-Assistent.by Integration Docs', 'sms'); ?></span></a>
            </div>


            <div class="mb-line">
              <label for="assistent_api_username"><span><?php _e('SMS-Assistent.by Api Username', 'sms'); ?></span></label>
              <input name="assistent_api_username" id="assistent_api_username" type="text" value="<?php echo $assistent_api_username; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="assistent_api_pass"><span><?php _e('SMS-Assistent.by Api Password', 'sms'); ?></span></label>
              <input name="assistent_api_pass" id="assistent_api_pass" type="password" autocomplete="new-password" value="<?php echo $assistent_api_pass; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="assistent_api_sender"><span><?php _e('SMS-Assistent.by Api Sender', 'sms'); ?></span></label>
              <input name="assistent_api_sender" id="assistent_api_sender" type="text" value="<?php echo $assistent_api_sender; ?>" />
            </div>

          </div>
        </div>
        

        <!-- ROUTEE.NET-->
        <div class="mb-method mb-routee <?php if(sms_param('routee_app_id') <> '') { ?>enabled<?php } ?>">
          <div class="mb-method-name">
            <i class="mb-method-status fa fa-<?php if(sms_param('routee_app_id') <> '') { ?>check<?php } else { ?>times<?php } ?>"></i>
            <span><?php _e('Routee.net', 'sms'); ?></span>
            <img src="<?php echo sms_plugin_url(); ?>img/providers/routee.svg"/>
          </div>
          
          <div class="mb-method-body">
            <div class="mb-row mb-method-links">
              <a href="https://go.routee.net/#/management/applications" target="_blank" class="mb-prod osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-rocket"></i> <span><?php _e('Routee.net Account', 'sms'); ?></span></a>
              <a href="https://docs.routee.net/docs/send-a-simple-sms" target="_blank" class="mb-docu osp-has-tooltip" title="<?php echo osc_esc_html(__('Link will open in new window', 'sms')); ?>"><i class="fa fa-graduation-cap"></i> <span><?php _e('Routee.net Integration Docs', 'sms'); ?></span></a>
            </div>

            <div class="mb-line">
              <label for="routee_app_id"><span><?php _e('Routee Application Id', 'sms'); ?></span></label>
              <input name="routee_app_id" id="routee_app_id" type="text" value="<?php echo $routee_app_id; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="routee_app_secret"><span><?php _e('Routee Application Secret', 'sms'); ?></span></label>
              <input name="routee_app_secret" id="routee_app_secret" type="password" autocomplete="new-password" value="<?php echo $routee_app_secret; ?>" />
            </div>
            
            <div class="mb-line">
              <label for="routee_from"><span><?php _e('Routee From Name/Phone', 'sms'); ?></span></label>
              <input name="routee_from" id="routee_from" type="text" value="<?php echo $routee_from; ?>" />
            </div>
            
            
            <div class="mb-line">
              <label for="routee_country_code"><span><?php _e('Routee Default Country Code', 'sms'); ?></span></label>
              <input name="routee_country_code" id="routee_country_code" type="text" value="<?php echo $routee_country_code; ?>"/>
              <div class="mb-explain"><?php _e('Example: +90,+234,...', 'sms'); ?></div>
            </div>
          </div>
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


  <!-- TEST SMS -->
  <div class="mb-box mb-test-sms">
    <div class="mb-head"><i class="fa fa-check-square"></i> <?php _e('Test SMS sending', 'sms'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>provider.php" />
        <input type="hidden" name="plugin_action" value="test" />


        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('You can test functionality of each SMS provider here.', 'sms'); ?></div>
        </div>


        <div class="mb-row">
          <div class="mb-line">
            <label for="id"><span><?php _e('Provider', 'sms'); ?></span></label>

            <select id="provider" name="provider">
              <?php foreach($provider_array as $p) { ?>
                <option value="<?php echo $p; ?>" <?php echo ($p == Params::getParam('provider') ? 'selected="selected"' : ''); ?>><?php echo ucwords($p); ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="mb-line">
            <label for="phone_number"><span><?php _e('Phone Number (with +)', 'sms'); ?></span></label>
            <input type="tel" id="phone_number" size="30" name="phone_number" placeholder="<?php echo osc_esc_html(__('Type phone number...', 'sms')); ?>" value="<?php echo Params::getParam('phone_number'); ?>"/>
          </div>

          <div class="mb-line">
            <label for="message"><span><?php _e('Message', 'sms'); ?></span></label>
            <textarea id="message" name="message" maxlength="160" placeholder="<?php echo osc_esc_html(__('Enter message (max 160 chars)', 'sms')); ?>"><?php echo Params::getParam('message'); ?></textarea>
          </div>

          <div class="mb-line">
            <label for="">&nbsp;</label>
            <button type="submit" class="mb-button-green"><i class="fa fa-send"></i> <?php _e('Send SMS', 'sms');?></button>
          </div>
        </div>

        <div class="mb-row">&nbsp;</div>

      </form>
    </div>
  </div>


  <!-- CHECK BALANCE -->
  <div class="mb-box mb-balance">
    <div class="mb-head"><i class="fa fa-check-square"></i> <?php _e('Check balance', 'sms'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>provider.php" />
        <input type="hidden" name="plugin_action" value="balance" />


        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('Check what balance is on your account. Note that not all gateways support this feature.', 'sms'); ?></div>
        </div>


        <div class="mb-row">
          <div class="mb-line">
            <label for="id"><span><?php _e('Provider', 'sms'); ?></span></label>

            <select id="provider" name="provider">
              <?php foreach($provider_array as $p) { ?>
                <option value="<?php echo $p; ?>" <?php echo ($p == Params::getParam('provider') ? 'selected="selected"' : ''); ?>><?php echo ucwords($p); ?></option>
              <?php } ?>
            </select>
          </div>


          <div class="mb-line">
            <label for="">&nbsp;</label>
            <button type="submit" class="mb-button-green"><i class="fa fa-check"></i> <?php _e('Check balance', 'sms');?></button>
          </div>
        </div>

        <div class="mb-row">&nbsp;</div>

      </form>
    </div>
  </div>

</div>


<?php echo sms_footer(); ?>