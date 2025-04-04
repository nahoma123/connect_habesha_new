<?php
  // Create menu
  $title = __('Phone Number Verification Logs', 'sms');
  sms_menu($title);


  // GET & UPDATE PARAMETERS
  // $variable = sms_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value or value_crypt

  $params = Params::getParamsAsArray();
  $logs = ModelSMS::newInstance()->getVerificationLogs($params);
  $count_all = ModelSMS::newInstance()->getVerificationLogs($params, true);
  
  
  // VERIFY PHONE BY ADMIN - PHONE DIRECTLY
  if(Params::getParam('verifyAdminPhoneNumber') != '') {
    $phone_number = osc_esc_html((Params::getParam('verifyAdminPhoneNumber')));
    $email = osc_esc_html((Params::getParam('email')));
    
    $phone_number = sms_prepare_number($phone_number);
    
    $data = array(
      's_phone_number' => $phone_number, 
      's_email' => $email,
      's_status' => 'VERIFIED'
    );

    ModelSMS::newInstance()->cancelPreviousVerification($phone_number, $email);       // Previous phone number verifications
    ModelSMS::newInstance()->updateVerification($data, false);                        // Mark verification as verified
    
    osc_add_flash_ok_message(sprintf(__('Phone number %s has been successfully verified and paired with email %s. In case this phone was already verified before by different user, this verification has been canceled', 'sms'), $phone_number, $email), 'admin');
    header('Location:' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=sms/admin/log_verification.php');
    exit;
  }


  // CANCEL PHONE BY ADMIN - PHONE DIRECTLY
  if(Params::getParam('cancelAdminPhoneNumber') != '') {
    $phone_number = osc_esc_html((Params::getParam('cancelAdminPhoneNumber')));
    $email = osc_esc_html((Params::getParam('email')));
    
    $phone_number = sms_prepare_number($phone_number);
    
    $data = array(
      's_phone_number' => $phone_number, 
      's_email' => $email,
      's_status' => 'CANCELED'
    );

    ModelSMS::newInstance()->cancelPreviousVerification($phone_number, $email);       // Previous phone number verifications
    ModelSMS::newInstance()->updateVerification($data, false);                        // Mark verification as verified
    
    osc_add_flash_ok_message(sprintf(__('Phone number %s has been successfully verified and paired with email %s. In case this phone was already verified before by different user, this verification has been canceled', 'sms'), $phone_number, $email), 'admin');
    header('Location:' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=sms/admin/log_verification.php');
    exit;
  }
  
  // VERIFY PHONE BY ADMIN - ITEM
  if(Params::getParam('verifyItemPhoneNumber') > 0) {
    $item_id = (int)osc_esc_html(Params::getParam('verifyItemPhoneNumber'));
    $item = Item::newInstance()->findByPrimaryKey($item_id);
    
    $phone_number = sms_prepare_number($item['s_contact_phone']);
    $email = $item['s_contact_email'];
    
    $data = array(
      's_phone_number' => $phone_number, 
      's_email' => $email,
      's_provider' => 'MANUAL-ADMIN',
      's_token' => '0000',
      's_status' => 'VERIFIED'
    );

    $item_active_flag = 1;
    
    // Check if item validation plugin is installed
    if(function_exists('itv_call_after_install') || function_exists('iv_call_after_install')) {
      if(osc_get_preference('enable', 'plugin-item_validation') == 1) {
        $item_active_flag = 0;
      }
    }

    ModelSMS::newInstance()->updateItem(array('b_active' => $item_active_flag, 'pk_i_id' => $item_id));
    ModelSMS::newInstance()->cancelPreviousVerification($phone_number, $email);       // Previous phone number verifications
    ModelSMS::newInstance()->updateVerification($data, false, true);                  // Mark verification as verified
    
    osc_add_flash_ok_message(sprintf(__('Phone number %s for item #%s has been successfully verified and paired with email %s. In case this phone was already verified before by different user, this verification has been canceled', 'sms'), $phone_number, $item_id, $email), 'admin');
    header('Location:' . osc_admin_base_url(true) . '?page=items');
    exit;
  }
  
  
  // VERIFY PHONE BY ADMIN - USER
  if(Params::getParam('verifyUserPhoneNumber') > 0) {
    $user_id = (int)osc_esc_html(Params::getParam('verifyUserPhoneNumber'));
    $user = User::newInstance()->findByPrimaryKey($user_id);
    
    $phone_number = sms_prepare_number($user['s_phone_mobile']);
    $email = $user['s_email'];
    
    $data = array(
      's_phone_number' => $phone_number, 
      's_email' => $email,
      's_provider' => 'MANUAL-ADMIN',
      's_token' => '0000',
      's_status' => 'VERIFIED'
    );

    ModelSMS::newInstance()->cancelPreviousUserVerification($phone_number, $email);
    ModelSMS::newInstance()->cancelPreviousVerification($phone_number, $email);       // Previous phone number verifications
    ModelSMS::newInstance()->updateVerification($data, false, true);                  // Mark verification as verified
    
    osc_add_flash_ok_message(sprintf(__('Phone number %s has been successfully verified and paired with email %s. In case this phone was already verified before by different user, this verification has been canceled', 'sms'), $phone_number, $email), 'admin');
    header('Location:' . osc_admin_base_url(true) . '?page=users');
    exit;
  }

?>


<div class="mb-body">
  <!-- CONFIGURE SECTION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-address-book"></i> <?php _e('Phone Number Verification Logs', 'sms'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('Verification logs cannot be removed.', 'sms'); ?></div>
      </div>
      
      <form name="promo_form" action="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=sms/admin/log_verification.php" method="POST" enctype="multipart/form-data" >
        <div id="mb-search-table">
          <div class="mb-col-5">
            <label for="phone"><?php _e('Phone', 'sms'); ?></label>
            <input type="text" name="phone" value="<?php echo Params::getParam('phone'); ?>" placeholder="123456789"/>
          </div>
          
          <div class="mb-col-8">
            <label for="email"><?php _e('Email', 'sms'); ?></label>
            <input type="text" name="email" value="<?php echo Params::getParam('email'); ?>" placeholder="you@email.com"/>
          </div>
          
          <div class="mb-col-3">
            <label for="">&nbsp;</label>
            <button type="submit" class="mb-button mb-button-black"><i class="fa fa-search"></i> <?php _e('Search', 'sms'); ?></button>
          </div>
        </div>
      </form>


      <div class="mb-table mb-table-log">
        <div class="mb-table-head">
          <div class="mb-col-4 mb-align-left"><span><?php _e('Phone Number', 'sms'); ?></span></div>
          <div class="mb-col-5 mb-align-left"><span><?php _e('User Email', 'sms'); ?></span></div>
          <div class="mb-col-2"><span><?php _e('Verification Code', 'sms'); ?></span></div>
          <div class="mb-col-3"><span><?php _e('Provider', 'sms'); ?></span></div>
          <div class="mb-col-3"><span><?php _e('Status', 'sms'); ?></span></div>
          <div class="mb-col-3"><span><?php _e('Date', 'sms'); ?></span></div>
          <div class="mb-col-4"><span>&nbsp;</span></div>
        </div>

        <?php if(count($logs) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No verification logs has been found', 'sms'); ?></span>
          </div>
        <?php } else { ?>
          <?php foreach($logs as $l) { ?>
            <div class="mb-table-row">
              <div class="mb-col-4 mb-align-left"><?php echo $l['s_phone_number']; ?></div>
              <div class="mb-col-5 mb-align-left"><?php echo ($l['s_email'] <> '' ? $l['s_email'] : '-'); ?></div>
              <div class="mb-col-2"><?php echo $l['s_token']; ?></div>
              <div class="mb-col-3"><?php echo $l['s_provider']; ?></div>
              <div class="mb-col-3"><?php echo $l['s_status']; ?></div>
              <div class="mb-col-3"><?php echo $l['dt_date']; ?></div>
              <div class="mb-col-4 mb-align-right">
                <?php if($l['s_status'] == 'PENDING' || $l['s_status'] == 'CANCELED') { ?>
                  <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=sms/admin/log_verification.php&verifyAdminPhoneNumber=<?php echo urlencode($l['s_phone_number']); ?>&email=<?php echo urlencode($l['s_email']); ?>" class="mb-btn mb-button-blue"><i class="fa fa-check"></i> <?php _e('Verify', 'user_custom_fields_pro'); ?></a>
                <?php } ?>

                <?php if($l['s_status'] == 'PENDING' || $l['s_status'] == 'VERIFIED') { ?>
                  <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=sms/admin/log_verification.php&cancelAdminPhoneNumber=<?php echo urlencode($l['s_phone_number']); ?>&email=<?php echo urlencode($l['s_email']); ?>" class="mb-btn mb-button-white"><i class="fa fa-times"></i> <?php _e('Cancel', 'user_custom_fields_pro'); ?></a>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
          
          <?php 
            $param_string = '&email=' . Params::getParam('email') . '&phone=' . Params::getParam('phone');
            echo sms_admin_paginate('sms/admin/log_verification.php', Params::getParam('pageId'), 20, $count_all, '', $param_string); 
          ?>
        <?php } ?>
      </div>
    </div>
  </div>

</div>

<?php echo sms_footer(); ?>