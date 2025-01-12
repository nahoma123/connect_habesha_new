<?php
  // Create menu
  $title = __('Payment Logs', 'osclass_pay');
  osp_menu($title);


  // GET & UPDATE PARAMETERS
  // $variable = osp_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value or value_crypt

  $per_page = (Params::getParam('per_page') > 0 ? Params::getParam('per_page') : 25);
  $params = Params::getParamsAsArray();
  $logs = ModelOSP::newInstance()->getLogs(-1, $params);
  $count_all = ModelOSP::newInstance()->getLogs(-1, $params, true);
?>


<div class="mb-body">
  <!-- CONFIGURE SECTION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-database"></i> <?php _e('Payment Logs', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" action="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=osclass_pay/admin/log.php" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="logSearch" value="1"/>
        
        <div id="mb-search-table">
       
          <div class="mb-col-2">
            <label for="id"><?php _e('ID', 'osclass_pay'); ?></label>
            <input type="text" name="id" value="<?php echo Params::getParam('id'); ?>" />
          </div>

          <div class="mb-col-4">
            <label for="concept"><?php _e('Concept', 'osclass_pay'); ?></label>
            <input type="text" name="concept" value="<?php echo Params::getParam('concept'); ?>" />
          </div>
          
          <div class="mb-col-2">
            <label for="date"><?php _e('Date', 'osclass_pay'); ?></label>
            <input type="text" name="date" value="<?php echo Params::getParam('date'); ?>" placeholder="YYYY-MM-DD"/>
          </div>
          
          <div class="mb-col-2">
            <label for="code"><?php _e('Code', 'osclass_pay'); ?></label>
            <input type="text" name="code" value="<?php echo Params::getParam('code'); ?>" />
          </div>

          <div class="mb-col-3">
            <label for="user"><?php _e('User', 'osclass_pay'); ?></label>
            <input type="text" name="user" value="<?php echo Params::getParam('user'); ?>" />
          </div>
          
          <div class="mb-col-2">
            <label for="source"><?php _e('Source', 'osclass_pay'); ?></label>
            <input type="text" name="source" value="<?php echo Params::getParam('source'); ?>" />
          </div>
          
          <div class="mb-col-2">
            <label for="type"><?php _e('Type', 'osclass_pay'); ?></label>
            <input type="text" name="type" value="<?php echo Params::getParam('type'); ?>" />
          </div>
          
          <div class="mb-col-2">
            <label for="sort"><?php _e('Sorting', 'osclass_pay'); ?></label>
            <select name="sort">
              <option value="DESC" <?php if(Params::getParam('sort') == '' || Params::getParam('sort') == 'DESC') { ?>selected="selected"<?php } ?>><?php _e('By ID Descending', 'osclass_pay'); ?></option>
              <option value="ASC" <?php if(Params::getParam('sort') == 'ASC') { ?>selected="selected"<?php } ?>><?php _e('By ID Ascending', 'osclass_pay'); ?></option>
            </select>
          </div>
          
          <div class="mb-col-2">
            <label for="per_page"><?php _e('Per Page', 'osclass_pay'); ?></label>
            <select name="per_page">
              <option value="25" <?php if(Params::getParam('per_page') == '' || Params::getParam('per_page') == '25') { ?>selected="selected"<?php } ?>>25</option>
              <option value="50" <?php if(Params::getParam('per_page') == '50') { ?>selected="selected"<?php } ?>>50</option>
              <option value="100" <?php if(Params::getParam('per_page') == '100') { ?>selected="selected"<?php } ?>>100</option>
              <option value="200" <?php if(Params::getParam('per_page') == '200') { ?>selected="selected"<?php } ?>>200</option>
              <option value="500" <?php if(Params::getParam('per_page') == '500') { ?>selected="selected"<?php } ?>>500</option>
              <option value="1000" <?php if(Params::getParam('per_page') == '1000') { ?>selected="selected"<?php } ?>>1000</option>
            </select>
          </div>
         
          <div class="mb-col-2">
            <label for="">&nbsp;</label>
            <button type="submit" class="mb-button mb-button-black"><i class="fa fa-search"></i> <?php _e('Search', 'osclass_pay'); ?></button>
          </div>
        </div>
      </form>
      

      <div class="mb-table mb-table-log">
        <div class="mb-table-head">
          <div class="mb-col-1 h1"><span><?php _e('ID', 'osclass_pay');?></span></div>
          <div class="mb-col-5 h2"><span><?php _e('Concept', 'osclass_pay'); ?></span></div>
          <div class="mb-col-3 h3"><span><?php _e('Date', 'osclass_pay'); ?></span></div>
          <div class="mb-col-2 h4"><span><?php _e('Code', 'osclass_pay'); ?></span></div>
          <div class="mb-col-2 h5"><span><?php _e('Amount', 'osclass_pay'); ?></span></div>
          <div class="mb-col-1 h6"><span><?php _e('Cur', 'osclass_pay'); ?></span></div>
          <div class="mb-col-3 h7"><span><?php _e('Email', 'osclass_pay'); ?></span></div>
          <div class="mb-col-2 h8"><span><?php _e('User ID', 'osclass_pay'); ?></span></div>
          <div class="mb-col-2 h9"><span><?php _e('Source', 'osclass_pay'); ?></span></div>
          <div class="mb-col-2 h10"><span><?php _e('Type', 'osclass_pay'); ?></span></div>
          <div class="mb-col-1 h11"><span>&nbsp;</span></div>
        </div>

        <?php if(count($logs) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No payment logs has been found', 'osclass_pay'); ?></span>
          </div>
        <?php } else { ?>
          <?php foreach($logs as $l) { ?>
            <div class="mb-table-row">
              <div class="mb-col-1"><?php echo $l['pk_i_id']; ?></div>
              <div class="mb-col-5"><?php echo $l['s_concept']; ?></div>
              <div class="mb-col-3"><?php echo $l['dt_date']; ?></div>
              <div class="mb-col-2"><span class="mb-has-tooltip mb-log-code" title="<?php echo $l['s_code']; ?>"><?php echo $l['s_code']; ?></span></div>
              <div class="mb-col-2"><?php echo number_format((float)($l['f_amount'] <> 0 ? $l['f_amount'] : $l['i_amount']/1000000000000), 2, '.', ''); ?></div>
              <div class="mb-col-1"><?php echo $l['s_currency_code']; ?></div>
              <div class="mb-col-3 mb-log-email"><?php echo $l['s_email']; ?></div>
              <div class="mb-col-2">
                <?php 
                  if($l['fk_i_user_id'] <> '' && $l['fk_i_user_id'] > 0) {
                    $user = User::newInstance()->findByPrimaryKey($l['fk_i_user_id']);
                    $utitle = osc_esc_html(@$user['s_name'] . PHP_EOL . @$user['s_email'] . PHP_EOL . __('Reg. date', 'osclass_pay') . ': ' . @$user['dt_reg_date']);

                    $group_id = ModelOSP::newInstance()->getUserGroup($l['fk_i_user_id']);

                    if($group_id > 0) {
                      $group_full = ModelOSP::newInstance()->getGroup($group_id);
                      $utitle .= PHP_EOL . __('Active membership in', 'osclass_pay') . ': ' . $group_full['s_name'];
                    } else {
                      $utitle .= PHP_EOL . __('Not member of any group', 'osclass_pay');
                    }

                    $in_wallet = osp_get_wallet_amount($l['fk_i_user_id']);
                    $utitle .= PHP_EOL . __('Wallet', 'osclass_pay') . ': ' . osp_format_price($in_wallet);
                  } else {
                    $user = array();
                    $utitle = '';
                  }
                ?>

                <span class="<?php echo ($utitle <> '' ? 'mb-has-tooltip' : ''); ?>" title="<?php echo $utitle; ?>"><?php echo (isset($user['s_name']) ? $user['s_name'] : __('Unregistered', 'osclass_pay')); ?></span>
              </div>
              <div class="mb-col-2"><span class="source <?php echo strtolower($l['s_source']); ?>"><?php echo $l['s_source']; ?></span></div>
              <div class="mb-col-2"><?php echo osp_product_type_name($l['i_product_type']); ?></div>
              <div class="mb-col-1">
                <?php if(osp_cart_string_to_title($l['s_cart']) <> '') { ?>
                  <i class="fa fa-search mb-has-tooltip mb-log-details" title="<?php echo osc_esc_html(str_replace('<br/>', PHP_EOL, osp_cart_string_to_title($l['s_cart']))); ?>"></i>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
        <?php } ?>
        
        <?php 
          $param_string = '&id=' . Params::getParam('id') . '&concept=' . Params::getParam('concept') . '&date=' . Params::getParam('date') . '&code=' . Params::getParam('code') . '&user=' . Params::getParam('user') . '&source=' . Params::getParam('source') . '&type=' . Params::getParam('type') . '&per_page=' . Params::getParam('per_page') . '&sort=' . Params::getParam('sort');
          echo osp_admin_paginate('osclass_pay/admin/log.php', Params::getParam('pageId'), $per_page, $count_all, '', $param_string); 
        ?>
      </div>
    </div>
  </div>



  <!-- HELP TOPICS -->
  <div class="mb-box" id="mb-help">
    <div class="mb-head"><i class="fa fa-question-circle"></i> <?php _e('Help', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-help"><span class="sup">(1)</span> <div class="h1"><?php _e('Unique database order number of log.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(2)</span> <div class="h2"><?php _e('Concept is short description what was paid, like "Pay 2 cart items".', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(3)</span> <div class="h3"><?php _e('Date of payment.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(4)</span> <div class="h4"><?php _e('Code is transaction ID provided by payment gateway or generated by plugin (for wallet operations).', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(5)</span> <div class="h5"><?php _e('Total amount of order - how much was paid to your account.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(6)</span> <div class="h6"><?php _e('Currency used for transaction (USD, EUR, INR, ...).', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(7)</span> <div class="h7"><?php _e('Payer email.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(8)</span> <div class="h8"><?php _e('User details - available just in case order was done by logged in customer.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(9)</span> <div class="h9"><?php _e('Source identify what gateway was used for payment, it can be external or internal (like Referral, Wallet, ...).', 'osclass_pay'); ?></div></div>
    </div>
  </div>
</div>

<?php echo osp_footer(); ?>