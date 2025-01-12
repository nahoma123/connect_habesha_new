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
          <div class="mb-col-6 mb-align-left"><span><?php _e('Phone Number', 'sms'); ?></span></div>
          <div class="mb-col-6 mb-align-left"><span><?php _e('User Email', 'sms'); ?></span></div>
          <div class="mb-col-3"><span><?php _e('Verification Code', 'sms'); ?></span></div>
          <div class="mb-col-3"><span><?php _e('Provider', 'sms'); ?></span></div>
          <div class="mb-col-3"><span><?php _e('Status', 'sms'); ?></span></div>
          <div class="mb-col-3"><span><?php _e('Date', 'sms'); ?></span></div>
        </div>

        <?php if(count($logs) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No verification logs has been found', 'sms'); ?></span>
          </div>
        <?php } else { ?>
          <?php foreach($logs as $l) { ?>
            <div class="mb-table-row">
              <div class="mb-col-6 mb-align-left"><?php echo $l['s_phone_number']; ?></div>
              <div class="mb-col-6 mb-align-left"><?php echo ($l['s_email'] <> '' ? $l['s_email'] : '-'); ?></div>
              <div class="mb-col-3"><?php echo $l['s_token']; ?></div>
              <div class="mb-col-3"><?php echo $l['s_provider']; ?></div>
              <div class="mb-col-3"><?php echo $l['s_status']; ?></div>
              <div class="mb-col-3"><?php echo $l['dt_date']; ?></div>
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