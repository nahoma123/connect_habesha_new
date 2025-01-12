<?php
  // Create menu
  //$title = __('User Settings', 'osclass_pay');
  //osp_menu($title);


  // GET & UPDATE PARAMETERS
  // $variable = osp_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value or value_crypt


  $wallet_enabled = osp_param_update( 'wallet_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $wallet_registration = osp_param_update( 'wallet_registration', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $wallet_referral = osp_param_update( 'wallet_referral', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $wallet_periodically = osp_param_update( 'wallet_periodically', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $wallet_period = osp_param_update( 'wallet_period', 'plugin_action', 'value', 'plugin-osclass_pay' );

  $pack_style = osp_param_update( 'pack_style', 'plugin_action', 'value', 'plugin-osclass_pay' );


  if(Params::getParam('plugin_action') == 'done') {
    message_ok( __('Settings were successfully saved', 'osclass_pay') );
  }


  // UPDATE PACKAGE
  if(Params::getParam('plugin_action') == 'pack_update' && !osp_is_demo()) {
    $ids = array();
    $params = Params::getParamsAsArray();

    // FIRST GET IDS OF PACKAGES THAT SHOULD BE UPDATED
    foreach(array_keys($params) as $p) {
      // detail[1] - pack id
      // detail[2] - value name

      $detail = explode('_', $p);

      if($detail[0] == 'pack' && $detail[2] = 'name') {
        if($params['pack_' . $detail[1] . '_name'] <> '') {
          $ids[] = $detail[1];
        }
      }
    }

    $ids = array_unique($ids);

    if(count($ids) > 0) {
      foreach($ids as $i) {     
        $id = @$params['pack_' . $i . '_id'];
        $name = @$params['pack_' . $i . '_name'];
        $desc = @$params['pack_' . $i . '_description'];
        $price = @$params['pack_' . $i . '_price'];
        $bonus = @$params['pack_' . $i . '_extra'];
        $group = @$params['pack_' . $i . '_group'];
        $color = @$params['pack_' . $i . '_color'];

        $response = ModelOSP::newInstance()->updatePack($id, $name, $desc, $price, $bonus, $group, $color);

        if(!$response) {
          message_error( sprintf(__('Pack with name %s already exist and was not created.', 'osclass_pay'), $name) );
        }
      }
    }

    message_ok( __('Packages successfully updated', 'osclass_pay') );
  }


  // REMOVE PACKAGE
  if(Params::getParam('what') == 'pack_remove' && !osp_is_demo()) {
    ModelOSP::newInstance()->deletePack(Params::getParam('pack_id'));
    message_ok( __('Package successfully removed', 'osclass_pay') );
  }


  // ADD FUNDS TO USER ACCOUNT
  if(Params::getParam('plugin_action') == 'fund') {
    $email = trim(strtolower(Params::getParam('email')));
    $amount = floatval(Params::getParam('amount'));
    $user = User::newInstance()->findByEmail($email);

    if(isset($user['pk_i_id']) && $user['pk_i_id'] <> '' && $user['pk_i_id'] > 0) {
      if($amount <> 0 && $amount <> '') {
        osp_wallet_update($user['pk_i_id'], $amount);
        ModelOSP::newInstance()->saveLog(sprintf(__('Credit for user %s (%s) at %s by Admin', 'osclass_pay'), $user['s_name'], ($amount . osp_currency_symbol()), osc_page_title()), 'wallet_' . date('YmdHis'), $amount, osp_currency(), $user['s_email'], $user['pk_i_id'], '', OSP_TYPE_PACK, 'ADMIN');
      }

      $new_amount = osp_get_wallet_amount($user['pk_i_id']);

      message_ok(sprintf(__('%s successfully assigned to %s (%s). This user has now %s in wallet.', 'osclass_pay'),$amount . osp_currency_symbol(), $user['s_name'], $user['s_email'], $new_amount . osp_currency_symbol()));

    } else {
      message_error( __('User not found', 'osclass_pay') );
    }
  }


  // SCROLL TO DIV
  if(Params::getParam('plugin_action') == 'pack_update' || Params::getParam('what') == 'pack_remove') {
    osp_js_scroll('.mb-packs');
  } else if(Params::getParam('plugin_action') == 'done') {
    osp_js_scroll('.mb-wallet');
  } else if(Params::getParam('plugin_action') == 'fund') {
    osp_js_scroll('.mb-credits');
  } else if (Params::getParam('scrollTo') <> '') {
    osp_js_scroll('.' . Params::getParam('scrollTo'));
  }

?>



<div class="mb-body">

  <!-- USER WALLET SECTION -->
  <div class="mb-box mb-wallet">
    <div class="mb-head">
      <i class="fa fa-folder-open"></i> <?php _e('User Wallet', 'osclass_pay'); ?>

      <?php if(osp_param('wallet_periodically') > 0 && osp_param('wallet_periodically') <> '') { ?>
        <span class="mb-runs mb-has-tooltip" title="<?php echo osc_esc_html(osp_get_cron_runs_user()[1]); ?>"><?php echo osp_get_cron_runs_user()[0]; ?></span>
      <?php } ?>
    </div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>user.php" />
        <input type="hidden" name="go_to_file" value="_wallet.php" />
        <input type="hidden" name="plugin_action" value="done" />

        <div class="mb-row">
          <label for="wallet_enabled" class="h1"><span><?php _e('Enable User Wallet', 'osclass_pay'); ?></span></label> 
          <input name="wallet_enabled" id="wallet_enabled" class="element-slide" type="checkbox" <?php echo ($wallet_enabled == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('User can purchase credit packs and pay using credits.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="wallet_registration" class="h3"><span><?php _e('Add credits to new user', 'osclass_pay'); ?></span></label> 
          <input name="wallet_registration" style="text-align:right;width:80px;" id="wallet_registration" type="text" value="<?php echo ($wallet_registration > 0 ? $wallet_registration : ''); ?>" /><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div>
          
          <div class="mb-explain"><?php _e('Add credits to each registered user. Set to 0 or leave blank to disable.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="wallet_referral" class="h4"><span><?php _e('Add credits to referral users', 'osclass_pay'); ?></span></label> 
          <input name="wallet_referral" style="text-align:right;width:80px;" id="wallet_referral" type="text" value="<?php echo ($wallet_referral > 0 ? $wallet_referral : ''); ?>" /><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div>
          
          <div class="mb-explain"><?php _e('When newly registered user use referral code provided by other users, both of them will get bonus credits. Set to 0 or leave blank to disable.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="wallet_periodically" class="h5"><span><?php _e('Add credits to users once per period', 'osclass_pay'); ?></span></label> 
          <input name="wallet_periodically" style="text-align:right;width:80px;" id="wallet_periodically" type="text" value="<?php echo ($wallet_periodically > 0 ? $wallet_periodically : ''); ?>" /><div class="mb-input-desc" style="float:left;margin-right:10px"><?php echo osp_currency_symbol(); ?></div>
          <div style="display:inline-block;padding:6px 4px;line-height:15px;font-size:12px;color:#888;margin-right:10px;float:left;"><?php _e('once per', 'osclass_pay'); ?></div>

          <select name="wallet_period" id="wallet_period">
            <option value="w" <?php if($wallet_period == 'w') { ?>selected="selected"<?php } ?>><?php _e('Week', 'osclass_pay'); ?></option>
            <option value="m" <?php if($wallet_period == 'm') { ?>selected="selected"<?php } ?>><?php _e('Month', 'osclass_pay'); ?></option>
            <option value="q" <?php if($wallet_period == 'q') { ?>selected="selected"<?php } ?>><?php _e('Quarter', 'osclass_pay'); ?></option>
          </select>

          <div class="mb-explain"><?php _e('As bonus to registered users, provide them extra credits once per selected period. Credits are sent in first day of period. Set to 0 or leave blank to disable.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="pack_style" class="h2"><span><?php _e('Design packs as', 'osclass_pay'); ?></span></label> 
          <select name="pack_style" id="pack_style">
            <option value="1" <?php if($pack_style == 1) { ?>selected="selected"<?php } ?>><?php _e('Gallery - boxes', 'osclass_pay'); ?></option>
            <option value="2" <?php if($pack_style == 2) { ?>selected="selected"<?php } ?>><?php _e('List - stripes', 'osclass_pay'); ?></option>
          </select>
          
          <div class="mb-explain"><?php _e('In front office, select what kind of design you prefer for packs.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
        </div>
      </form>
    </div>
  </div>



  <!-- PACKS SECTION -->
  <div class="mb-box mb-packs">
    <div class="mb-head">
      <i class="fa fa-folder-copy"></i> <?php _e('Credit Packs', 'osclass_pay'); ?>
      <?php echo osp_locale_box('user.php', '_wallet.php', 'mb-packs'); ?>
    </div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>user.php" />
        <input type="hidden" name="go_to_file" value="_wallet.php" />
        <input type="hidden" name="plugin_action" value="pack_update" />
        <input type="hidden" name="ospLocale" value="<?php echo osp_get_locale(); ?>" />


        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('You can specify credit packs users can purchase and replace numerous number of small transations with less larger transactions.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('Using packages is more suitable for users as well as they do not need to checkout each time they promote their listing.', 'osclass_pay'); ?></div>
        </div>


        <?php $packs = ModelOSP::newInstance()->getPacks(-1, 0, true); ?>
        <div class="mb-table mb-table-pack">
          <div class="mb-table-head">
            <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
            <div class="mb-col-4 mb-input-box mb-align-left"><?php _e('Pack Name', 'osclass_pay'); ?></div>
            <div class="mb-col-7 mb-input-box mb-align-left"><?php _e('Description', 'osclass_pay'); ?></div>
            <div class="mb-col-3 mb-pack-price"><?php _e('Amount', 'osclass_pay'); ?></div>
            <div class="mb-col-3 mb-pack-price"><?php _e('Bonus', 'osclass_pay'); ?></div>
            <div class="mb-col-3 mb-pack-price"><?php _e('User Group', 'osclass_pay'); ?></div>
            <div class="mb-col-2 mb-pack-price mb-align-left"><?php _e('Color', 'osclass_pay'); ?></div>
            <div class="mb-col-1">&nbsp;</div>
          </div>

          <?php foreach($packs as $p) { ?>
            <?php $id = $p['pk_i_id']; ?>

            <div class="mb-table-row">
              <div class="mb-col-1 mb-input-box"><input type="text" name="pack_<?php echo $id; ?>_id" value="<?php echo $p['pk_i_id']; ?>" readonly="readonly"/></div>
              <div class="mb-col-4 mb-input-box"><input type="text" name="pack_<?php echo $id; ?>_name" value="<?php echo $p['s_name']; ?>" required placeholder="<?php echo osc_esc_html(__('Enter package name', 'osclass_pay')); ?>"/></div>
              <div class="mb-col-7 mb-input-box"><input type="text" name="pack_<?php echo $id; ?>_description" value="<?php echo $p['s_description']; ?>"/></div>
              <div class="mb-col-3 mb-pack-price"><input type="text" name="pack_<?php echo $id; ?>_price" value="<?php echo $p['f_price']; ?>"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div></div>
              <div class="mb-col-3 mb-pack-price"><input type="text" name="pack_<?php echo $id; ?>_extra" value="<?php echo $p['f_extra']; ?>"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div></div>

              <div class="mb-col-3 mb-pack-price">
                <select id="group" name="pack_<?php echo $id; ?>_group">
                  <option value=""><?php _e('All groups', 'osclass_pay'); ?></option>

                  <?php foreach(ModelOSP::newInstance()->getGroups() as $g) { ?>
                    <option value="<?php echo $g['pk_i_id']; ?>" <?php echo ($g['pk_i_id'] == $p['i_group'] ? 'selected="selected"' : ''); ?>><?php echo $g['s_name']; ?></option>
                  <?php } ?>
                </select>
              </div>

              <div class="mb-col-2 mb-pack-price"><input type="color" name="pack_<?php echo $id; ?>_color" value="<?php echo ($p['s_color'] <> '' ? $p['s_color'] : '#2eacce'); ?>"/></div>
              <div class="mb-col-1 mb-del-col"><a href="<?php echo osp_admin_plugin_url('user.php'); ?>&go_to_file=_wallet.php&what=pack_remove&pack_id=<?php echo $p['pk_i_id']; ?>" onclick="return confirm('<?php echo osc_esc_html(__('Are you sure you want to delete this pack?', 'osclass_pay')); ?>')" class="mb-pack-remove" title="<?php echo osc_esc_html(__('Remove pack', 'osclass_pay')); ?>"><i class="fa fa-trash-o"></i></a></div>
            </div>
          <?php } ?>


          <?php for($i=1;$i<=3-count($packs);$i++) { ?>
            <?php $id = -($i + count($packs)); ?>

            <div class="mb-table-row">
              <div class="mb-col-1">xx</div>
              <div class="mb-col-4 mb-input-box"><input type="text" name="pack_<?php echo $id; ?>_name" placeholder="<?php echo osc_esc_html(__('Create new package', 'osclass_pay')); ?>"/></div>
              <div class="mb-col-7 mb-input-box"><input type="text" name="pack_<?php echo $id; ?>_description"/></div>
              <div class="mb-col-3 mb-pack-price"><input type="text" name="pack_<?php echo $id; ?>_price"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div></div>
              <div class="mb-col-3 mb-pack-price"><input type="text" name="pack_<?php echo $id; ?>_extra"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div></div>

              <div class="mb-col-3 mb-pack-price">
                <select id="group" name="pack_<?php echo $id; ?>_group">
                  <option value=""><?php _e('All groups', 'osclass_pay'); ?></option>

                  <?php foreach(ModelOSP::newInstance()->getGroups() as $g) { ?>
                    <option value="<?php echo $g['pk_i_id']; ?>"><?php echo $g['s_name']; ?></option>
                  <?php } ?>
                </select>
              </div>

              <div class="mb-col-2 mb-pack-price"><input type="color" name="pack_<?php echo $id; ?>_color" value="#2eacce"/></div>
              <div class="mb-col-1 mb-del-col"><a href="#" class="mb-pack-remove mb-pack-new-line" title="<?php echo osc_esc_html(__('Remove pack', 'osclass_pay')); ?>"><i class="fa fa-trash-o"></i></a></div>
            </div>
          <?php } ?>

          <div class="mb-pack-placeholder">
            <?php $id = -999; ?>

            <div class="mb-table-row" style="display:none;">
              <div class="mb-col-1 mb-id">xx</div>
              <div class="mb-col-4 mb-input-box"><input type="text" name="pack_<?php echo $id; ?>_name" placeholder="<?php echo osc_esc_html(__('Create new package', 'osclass_pay')); ?>"/></div>
              <div class="mb-col-7 mb-input-box"><input type="text" name="pack_<?php echo $id; ?>_description"/></div>
              <div class="mb-col-3 mb-pack-price"><input type="text" name="pack_<?php echo $id; ?>_price"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div></div>
              <div class="mb-col-3 mb-pack-price"><input type="text" name="pack_<?php echo $id; ?>_extra"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div></div>

              <div class="mb-col-3 mb-pack-price">
                <select id="group" name="pack_<?php echo $id; ?>_group">
                  <option value=""><?php _e('All groups', 'osclass_pay'); ?></option>

                  <?php foreach(ModelOSP::newInstance()->getGroups() as $g) { ?>
                    <option value="<?php echo $g['pk_i_id']; ?>"><?php echo $g['s_name']; ?></option>
                  <?php } ?>
                </select>
              </div>

              <div class="mb-col-2 mb-pack-price"><input type="color" name="pack_<?php echo $id; ?>_color" value="#2eacce"/></div>
              <div class="mb-col-1 mb-del-col"><a href="#" class="mb-pack-remove mb-pack-new-line" title="<?php echo osc_esc_html(__('Remove pack', 'osclass_pay')); ?>"><i class="fa fa-trash-o"></i></a></div>
            </div>
          </div>
        </div>

        <a href="#" class="mb-button-green mb-add-pack"><?php _e('Add new line for pack', 'osclass_pay'); ?></a>

        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
        </div>
      </form>
    </div>
  </div>



  <!-- ADD FUNDS TO USER -->
  <div class="mb-box mb-credits">
    <div class="mb-head"><i class="fa fa-stack-overflow"></i> <?php _e('Credits on User Account', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>user.php" />
        <input type="hidden" name="go_to_file" value="_wallet.php" />
        <input type="hidden" name="plugin_action" value="fund" />

        <?php
          if(Params::getParam('plugin_action') == 'fund') {
            $email = trim(strtolower(Params::getParam('email')));
            $user = User::newInstance()->findByEmail($email);

            if(isset($user['pk_i_id']) && $user['pk_i_id'] <> '' && $user['pk_i_id'] > 0) {
              $user_id = $user['pk_i_id'];
              $user_name = $user['s_name'];
              $user_email = $user['s_email'];
              $account_amount = osp_get_wallet_amount($user['pk_i_id']);
            }
          }
        ?>


        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('You can check how much funds has each user in wallet, just start typing user name into name field.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('You can also add or withdraw funds to user account.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row mb-user-lookup">
          <div class="mb-line mb-error-block"></div>

          <div class="mb-line">
            <label for="id"><span><?php _e('User ID', 'osclass_pay'); ?></span></label>
            <input type="text" id="id" name="id" readonly="readonly" value="<?php echo (isset($user['pk_i_id']) ? $user['pk_i_id'] : ''); ?>"/>
          </div>

          <div class="mb-line">
            <label for="name"><span><?php _e('Name', 'osclass_pay'); ?></span></label>
            <input type="text" id="name" name="name" placeholder="<?php echo osc_esc_html(__('Type user name or email...', 'osclass_pay')); ?>" value="<?php echo osc_esc_html(isset($user['s_name']) ? $user['s_name'] : ''); ?>"/>
            <div class="mb-explain"><?php _e('Start typing user name or email and select user you want to check from list.', 'osclass_pay'); ?></div>
          </div>

          <div class="mb-line">
            <label for="email"><span><?php _e('Email', 'osclass_pay'); ?></span></label>
            <input type="text" id="email" name="email" readonly="readonly" value="<?php echo (isset($user['s_email']) ? $user['s_email'] : ''); ?>"/>
          </div>

          <div class="mb-line">
            <label for="account_amount"><span><?php _e('Wallet Amount', 'osclass_pay'); ?></span></label>
            <input type="text" id="account_amount" name="account_amount" readonly="readonly" value="<?php echo (isset($account_amount) ? $account_amount : ''); ?>"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div>
          </div>

          <div class="mb-row"><div class="mb-line">&nbsp;</div><div class="mb-line" style="border-top:1px solid rgba(0,0,0,0.1);">&nbsp;</div></div>

          <div class="mb-line">
            <label for="amount"><span><?php _e('Add/withdraw credits', 'osclass_pay'); ?></span></label>
            <input type="text" id="amount" name="amount" /><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div>
            <button type="submit" class="mb-button-green"><i class="fa fa-check"></i> <?php _e('Update', 'osclass_pay');?></button>
            <div class="mb-explain"><?php _e('You can place positive (add) or negative (withdraw) amount.', 'osclass_pay'); ?></div>
          </div>
        </div>
      </form>
    </div>
  </div>


  <!-- HELP TOPICS -->
  <div class="mb-box" id="mb-help">
    <div class="mb-head"><i class="fa fa-question-circle"></i> <?php _e('Help', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-help"><span class="sup">(1)</span> <div class="h1"><?php _e('When enabled, users can buy credit packs and get bonus for registration or referral of friend. Wallet should help you to reduce amount of small payments.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(2)</span> <div class="h2"><?php _e('Select preferred design for packs - show as gallery (boxes) or show as list (stripes).', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(3)</span> <div class="h3"><?php _e('Provide credits for registration. User will get credits automatically after registration.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(4)</span> <div class="h4"><?php _e('Provide credits for friend referral. When newly registered user used referral code of existing user, both users will get credits to their wallet. Credits are added to wallet after newly registered user activate it\'s account, if required.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(5)</span> <div class="h5"><?php _e('Provide credits to registered users periodically. This helps to keep users active and attract their attention.', 'osclass_pay'); ?></div></div>
    </div>
  </div>
</div>


<script type="text/javascript">
  var user_lookup_error = "<?php echo osc_esc_js(__('Error getting data, user not found', 'osclass_pay')); ?>";
  var user_lookup_url = "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=runhook&hook=osp_wallet_data&id=";
  var user_lookup_base = "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=userajax";
</script>


<?php echo osp_footer(); ?>