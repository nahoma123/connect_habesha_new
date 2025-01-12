<?php
  // Create menu
  // $title = __('Product selling', 'osclass_pay');
  // osp_menu($title);

  $seller_users = osp_param('seller_users');
  $seller_all = osp_param('seller_all');



  // ADD USER TO SELLER LIST
  $make_seller = '';
  if(Params::getParam('plugin_action') == 'add_user_to_seller') {
    $email = trim(strtolower(Params::getParam('email')));
    $make_seller = Params::getParam('seller_update');
    $seller_array = array_filter(explode(',', trim($seller_users)));
    $user = User::newInstance()->findByEmail($email);

    if(isset($user['pk_i_id']) && $user['pk_i_id'] <> '' && $user['pk_i_id'] > 0) {
      if($make_seller == 1) { 
        if(!in_array($user['pk_i_id'], $seller_array)) {
          $seller_array[] = $user['pk_i_id'];
          message_ok(sprintf(__('User %s successfully added to seller\'s list. This user is now eligible to sell items.', 'osclass_pay'), '<strong>' . $user['s_name'] . '</strong>'));
        }
      } else {
        if(($key = array_search($user['pk_i_id'], $seller_array)) !== false) {
          unset($seller_array[$key]);
          message_ok(sprintf(__('User %s successfully removed from seller\'s list. This user cannot sell items anymore.', 'osclass_pay'), '<strong>' . $user['s_name'] . '</strong>'));
        }
      }

      $seller_string = implode(',', $seller_array);

      osc_set_preference( 'seller_users', $seller_string, 'plugin-osclass_pay', 'STRING'); 
      osc_reset_preferences();

    } else {
      message_error( __('User not found', 'osclass_pay') );
    }
  }


  // REMOVE SELLER FROM LIST
  if(Params::getParam('what') == 'removeSeller' && Params::getParam('id') <> '' && Params::getParam('id') > 0) {
    $seller_array = array_filter(explode(',', trim(osp_param('seller_users'))));
    $user = User::newInstance()->findByPrimaryKey(Params::getParam('id'));

    if(($key = array_search($user['pk_i_id'], $seller_array)) !== false) {
      unset($seller_array[$key]);
      message_ok(sprintf(__('User %s successfully removed from seller\'s list. This user cannot sell items anymore.', 'osclass_pay'), '<strong>' . $user['s_name'] . '</strong>'));

      $seller_string = implode(',', $seller_array);

      osc_set_preference( 'seller_users', $seller_string, 'plugin-osclass_pay', 'STRING'); 
      osc_reset_preferences();
    }
  }

?>



<div class="mb-body">

  <!-- ADD USER TO SELLERS LIST -->
  <div class="mb-box mb-seller">
    <div class="mb-head"><i class="fa fa-plus-circle"></i> <?php _e('Make users sellers', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>ecommerce.php" />
        <input type="hidden" name="go_to_file" value="_ecommerce_users.php" />
        <input type="hidden" name="plugin_action" value="add_user_to_seller" />
        <input type="hidden" name="position" value="4" />

        <?php
          if(Params::getParam('plugin_action') == 'add_user_to_seller') {
            $user = User::newInstance()->findByEmail($email);

            if(isset($user['pk_i_id']) && $user['pk_i_id'] <> '' && $user['pk_i_id'] > 0) {
              $user_id = $user['pk_i_id'];
              $user_name = $user['s_name'];
              $user_email = $user['s_email'];
            }
          }
        ?>


        <?php if($seller_all == 1) { ?>
          <div class="mb-row mb-errors">
            <div class="mb-line"><?php _e('All users can sell their products! Selection bellow will not be used. You can disable this in section above, option "Enable All Registered to Sell Products".', 'osclass_pay'); ?></div>
          </div>
        <?php } ?>

        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('Select users that are allowed to sell their products. Money from payments will go to your account!', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row mb-seller-lookup">
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

          <div class="mb-row"><div class="mb-line">&nbsp;</div><div class="mb-line" style="border-top:1px solid rgba(0,0,0,0.1);">&nbsp;</div></div>

          <div class="mb-line mb-seller-update-line">
            <label for="seller_update"><span><?php _e('Set User as Seller', 'osclass_pay'); ?></span></label>
            <select id="seller_update" name="seller_update">
              <option value="0" <?php if($make_seller == 0) { ?>selected="selected"<?php } ?>><?php _e('No', 'osclass_pay'); ?></option>
              <option value="1" <?php if($make_seller == 1) { ?>selected="selected"<?php } ?>><?php _e('Yes', 'osclass_pay'); ?></option>
            </select>

            <button type="submit" class="mb-button-green"><i class="fa fa-check"></i> <?php _e('Update', 'osclass_pay');?></button>
          </div>
        </div>
      </form>


      <strong style="float:left;clear:both;margin:40px 0 10px 0;"><?php _e('List of active sellers', 'osclass_pay'); ?></strong>
      <div class="mb-table mb-table-sellers">
        <div class="mb-table-head">
          <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-left"><?php _e('Seller', 'osclass_pay');?></div>
          <div class="mb-col-1"><?php _e('Items', 'osclass_pay');?></div>
          <div class="mb-col-5 mb-align-left"><?php _e('Email', 'osclass_pay');?></div>
          <div class="mb-col-2"><?php _e('Type', 'osclass_pay');?></div>
          <div class="mb-col-6 mb-align-left"><?php _e('Address', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-left"><?php _e('Phone', 'osclass_pay');?></div>
          <div class="mb-col-4 mb-align-left"><?php _e('Web', 'osclass_pay');?></div>
          <div class="mb-col-1">&nbsp;</div>
        </div>

        <?php $sellers = array_filter(explode(',', trim(osp_param('seller_users')))); ?>
          
        <?php if($seller_all == 1) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('You have set that all users can sell, this section will not be used.', 'osclass_pay'); ?></span>
          </div>
        <?php } else if(count($sellers) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No listings has been found', 'osclass_pay'); ?></span>
          </div>
        <?php } else { ?>
          <?php foreach($sellers as $s) { ?>
            <div class="mb-table-row">
              <?php $user = User::newInstance()->findByPrimaryKey($s); ?>

              <div class="mb-col-1"><?php echo $user['pk_i_id']; ?></div>
              <div class="mb-col-2 mb-align-left"><a target="_blank" href="<?php echo osc_admin_base_url(); ?>?page=users&action=edit&id=<?php echo $user['pk_i_id']; ?>"><?php echo $user['s_name']; ?></a></div>
              <div class="mb-col-1"><?php echo $user['i_items']; ?>x</div>
              <div class="mb-col-5 mb-align-left nw"><a href="mailto:<?php echo $user['s_email']; ?>"><?php echo $user['s_email']; ?></a></div>
              <div class="mb-col-2 mb-type t<?php echo $user['b_company']; ?>">
                <span>
                  <?php 
                    if($user['b_company'] == 1) {
                      echo '<i class="fa fa-suitcase"></i> ' . __('Company', 'osclass_pay');
                    } else {
                      echo '<i class="fa fa-user"></i> ' . __('Personal', 'osclass_pay');
                    }
                  ?>
                </span>
              </div>
              <div class="mb-col-6 mb-delivery mb-align-left">
                <?php
                  $addr = trim(implode(', ', array_filter(array($user['fk_c_country_code'], $user['s_region'], $user['s_city'], $user['s_zip'], $user['s_address']))));
                  if($addr <> '') {
                    echo $addr;
                  } else {
                    echo '<em>' . __('Address not set!', 'osclass_pay') . '</em>';
                  }
                ?>
              </div>
              <div class="mb-col-2 mb-align-left">
                <?php
                  $phone = trim(($user['s_phone_mobile'] <> '' ? $user['s_phone_mobile'] : $user['s_phone_land']));
                  if($phone <> '') {
                    echo $phone;
                  } else {
                    echo '<em>' . __('Phone not set!', 'osclass_pay') . '</em>';
                  }
                ?>
              </div>
              <div class="mb-col-4 mb-align-left">
                <?php 
                  if(trim($user['s_website']) <> '') {
                    echo '<a target="_blank" href="' . trim($user['s_website']) . '">' . trim($user['s_website']) . '</a>';
                  }
                ?>
              </div>
              <div class="mb-col-1 mb-align-right"><a class="mb-button-white mb-remove" href="<?php echo osc_admin_base_url(); ?>?page=plugins&action=renderplugin&file=osclass_pay/admin/ecommerce.php&go_to_file=_ecommerce_users.php&what=removeSeller&id=<?php echo $user['pk_i_id']; ?>"><i class="fa fa-trash-o"></i></a></div>
            </div>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
  </div>

</div>

<?php
  // PREPARE JAVASCRIPT SELLER ARRAY
  $cn = 0;
  $js_seller_array = '';
  foreach(explode(',', osp_param('seller_users')) as $s) {
    if($cn > 0) {
      $js_seller_array .= ',';
    }

    $js_seller_array .= '"' . $s . '"'; 
    $cn++;
  }
?>


<script type="text/javascript">
  var seller_lookup_error = "<?php echo osc_esc_js(__('Error getting data, user not found', 'osclass_pay')); ?>";
  var seller_lookup_url = "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=runhook&hook=osp_wallet_data&id=";
  var seller_lookup_base = "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=userajax";
  var seller_array = [<?php echo $js_seller_array; ?>];
</script>

<?php echo osp_footer(); ?>