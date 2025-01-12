<?php
$location = Rewrite::newInstance()->get_location();
$section  = Rewrite::newInstance()->get_section();

$user_id = Params::getParam('userId');
$item_id = Params::getParam('itemId');


// USER PHONE VERIFICATION
if($section == 'user-verify' || Params::getParam('route') == 'sms-user-verify') {
  $type = 'USER';
  $user = User::newInstance()->findByPrimaryKey($user_id);

  if(!osc_is_web_user_logged_in()) {
    osc_add_flash_error_message(__('You must be logged in to verify phone number in user profile', 'sms'));
    header('Location:' . osc_base_url());
    exit;
  }
  
  if(osc_logged_user_id() != $user_id) {
    osc_add_flash_error_message(__('Phone belongs to different user, you cannot verify phone number on behalf of different user', 'sms'));
    header('Location:' . osc_base_url());
    exit;
  }

  if($user_id <= 0 || $user === false || !isset($user['pk_i_id'])) {
    osc_add_flash_error_message(__('User does not exists', 'sms'));
    header('Location:' . osc_base_url());
    exit;
  }
  
  $phone_number = sms_prepare_number(isset($user['s_phone_mobile']) ? $user['s_phone_mobile'] : '');

  if(sms_phone_verify($phone_number)) {
    osc_add_flash_ok_message(__('Phone number has already been verified', 'sms'));
    header('Location:' . osc_user_dashboard_url());
    exit;
  }


// ITEM PHONE VERIFICATION
} else if($section == 'item-verify' || Params::getParam('route') == 'sms-item-verify') {
  $type = 'ITEM';
  $item = Item::newInstance()->findByPrimaryKey($item_id);

  if($item_id <= 0 || $item === false || !isset($item['pk_i_id'])) {
    osc_add_flash_ok_message(__('Item does not exists.', 'sms'));
    header('Location:' . osc_base_url());
    exit;
  }

  $phone_number = sms_prepare_number(sms_item_phone_number($item_id));

}


if(sms_phone_verify($phone_number)) {
  osc_add_flash_ok_message(__('Phone number has already been verified', 'sms'));
  header('Location:' . osc_item_url($item));
  exit;
}

?>

<div class="sms-body sms-verify sms-user-verify">
  <div class="sms-box">
    <div class="sms-wrap">
      <h2><?php _e('Verify your phone number', 'sms'); ?></h2>

      <div class="sms-inside">
        <div class="sms-row sms-error" style="display:none"></div>
        <div class="sms-row sms-success" <?php if(sms_param('provider') <> 'demo') { ?>style="display:none"<?php } ?>><?php if(sms_param('provider') == 'demo') { _e('Plugin is in demo mode, you can enter any phone number', 'sms'); } ?></div>

        <form action="<?php echo osc_base_url(true); ?>?ajaxRequest=1" method="post" class="sms-step1 nocsrf">
          <input type="hidden" name="smsAjax" value="1" />
          <input type="hidden" name="ajaxRequest" value="1" />
          <input type="hidden" name="nolog" value="1" />
          <input type="hidden" name="type" value="<?php echo $type; ?>" />
          <input type="hidden" name="step" value="1" />
          <input type="hidden" name="itemId" value="<?php echo $item_id; ?>" />
          <input type="hidden" name="userId" value="<?php echo $user_id; ?>" />

          <div class="sms-row">
            <?php if($type == 'USER') { ?>
              <label for="name"><?php _e('User', 'sms'); ?></label> 
              <div class="sms-input-box sms-only-text"><?php echo (isset($user['s_name']) ? $user['s_name'] : '-'); ?></div>
            <?php } else { ?>
              <label for="name"><?php _e('Item', 'sms'); ?></label> 
              <div class="sms-input-box sms-only-text"><?php echo $item['s_title'] . ' (' . $item['pk_i_id'] . ')'; ?></div>
            <?php } ?>
          </div>

          <div class="sms-row">
            <label for="phoneNumber"><?php _e('Mobile Phone', 'sms'); ?></label>
            <input id="phoneNumber" type="text" name="phoneNumber" class="sms-input-field" value="<?php echo $phone_number; ?>" required/>
          </div>

          <button type="submit" class="alpBg mbBg sms-button sms-button-primary sms-send-code <?php echo ($phone_number == '' ? 'disabled' : ''); ?>"><?php _e('Send verification code', 'sms'); ?></button>
        </form>


        <form action="<?php echo osc_base_url(true); ?>?ajaxRequest=1" method="post" class="sms-step2 nocsrf" style="display:none;">
          <input type="hidden" name="smsAjax" value="1" />
          <input type="hidden" name="ajaxRequest" value="1" />
          <input type="hidden" name="nolog" value="1" />
          <input type="hidden" name="type" value="<?php echo $type; ?>" />
          <input type="hidden" name="step" value="2" />
          <input type="hidden" name="phoneNumber" value="<?php echo $phone_number; ?>" />
          <input type="hidden" name="itemId" value="<?php echo $item_id; ?>" />
          <input type="hidden" name="userId" value="<?php echo $user_id; ?>" />

          <div class="sms-row sms-four-inputs">
            <label for="code1"><?php _e('Please enter verification code we sent to', 'sms'); ?> <<span class="sms-phone-code-sent"></span>></label> 
            <input id="code1" type="text" name="code1" class="sms-code" maxlength="1"/>
            <input id="code2" type="text" name="code2" class="sms-code" maxlength="1"/>
            <input id="code3" type="text" name="code3" class="sms-code" maxlength="1"/>
            <input id="code4" type="text" name="code4" class="sms-code" maxlength="1"/>
          </div>

          <button type="submit" class="alpBg mbBg sms-button sms-button-primary sms-verify disabled" disabled><?php _e('Verify', 'sms'); ?></button>

          <div class="sms-row sms-rsnd"><?php _e('SMS may take up to 40 seconds. Wait up, please!', 'sms'); ?></div>
          <a href="#" class="sms-button-resend disabled" disabled><span class="txt"><?php _e('Resend in', 'sms'); ?></span> <span class="counter">60</span></a>


        </form>
      </div>
    </div>
  </div>
</div>

<script>
  var smsResendReady = '<?php echo osc_esc_js(__('Resend SMS', 'sms')); ?>';
  var smsResendStart = '<?php echo osc_esc_js(__('Resend in', 'sms')); ?>';
  var smsResendSent = '<?php echo osc_esc_js(__('SMS Sent!', 'sms')); ?>';
</script>
