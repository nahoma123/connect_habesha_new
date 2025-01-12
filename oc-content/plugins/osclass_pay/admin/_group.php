<?php
  // Create menu
  //$title = __('User Settings', 'osclass_pay');
  //osp_menu($title);


  // GET & UPDATE PARAMETERS
  // $variable = osp_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value or value_crypt


  $groups_enabled = osp_param_update( 'groups_enabled', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $groups_category = osp_param_update( 'groups_category', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $groups_registration = osp_param_update( 'groups_registration', 'plugin_action', 'value', 'plugin-osclass_pay' );

  $groups_limit_items = osp_param_update( 'groups_limit_items', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $groups_max_items = osp_param_update( 'groups_max_items', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $groups_max_items_days = osp_param_update( 'groups_max_items_days', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $groups_max_items_type = osp_param_update( 'groups_max_items_type', 'plugin_action', 'value', 'plugin-osclass_pay' );


  if(Params::getParam('plugin_action') == 'done') {
    message_ok( __('Settings were successfully saved', 'osclass_pay') );
  }


  // UPDATE GROUPS
  if(Params::getParam('plugin_action') == 'group_update' && !osp_is_demo()) {
    $ids = array();
    $params = Params::getParamsAsArray();

    // FIRST GET IDS OF GROUPS THAT SHOULD BE UPDATED
    foreach(array_keys($params) as $p) {
      // detail[1] - group id
      // detail[2] - value name

      $detail = explode('_', $p);

      if($detail[0] == 'group' && $detail[2] = 'name') {
        if($params['group_' . $detail[1] . '_name'] <> '') {
          $ids[] = $detail[1];
        }
      }
    }

    $ids = array_unique($ids);

    if(count($ids) > 0) {
      foreach($ids as $i) {     
        $id = @$params['group_' . $i . '_id'];
        $name = @$params['group_' . $i . '_name'];
        $desc = @$params['group_' . $i . '_description'];
        $price = @$params['group_' . $i . '_price'];
        $discount = @$params['group_' . $i . '_discount'];
        $days = @$params['group_' . $i . '_days'];
        $color = @$params['group_' . $i . '_color'];
        $category = @$params['group_' . $i . '_category'];
        $pbonus = @$params['group_' . $i . '_pbonus'];
        $custom = @$params['group_' . $i . '_custom'];
        $rank = @$params['group_' . $i . '_rank'];
        $attr = @$params['group_' . $i . '_attr'];
        $max_items = @$params['group_' . $i . '_maxitems'];
        $max_items_days = @$params['group_' . $i . '_maxitemsdays'];
        $free_items_101 = @$params['group_' . $i . '_free_items_101'];
        // $free_items_201 = @$params['group_' . $i . '_free_items_201'];
        // $free_items_401 = @$params['group_' . $i . '_free_items_401'];

        if($pbonus <> '' && $pbonus < 0) {
          $pbonus = '';
          message_error( __('Value for Periodical bonus was not entered correctly. It must be integer larger or equal to 0.', 'osclass_pay') );
        }

        $response = ModelOSP::newInstance()->updateGroup($id, $name, $desc, $price, $discount, $days, $color, $category, $pbonus, $custom, $rank, $attr, $max_items, $max_items_days, $free_items_101);

        if(!$response) {
          message_error( sprintf(__('Group with name %s already exist and was not created.', 'osclass_pay'), $name) );
        }
      }
    }

    message_ok( __('Groups successfully updated', 'osclass_pay') );
  }



  // UPDATE USER IN GROUP
  if(Params::getParam('plugin_action') == 'add_user_to_group') {
    $email = trim(strtolower(Params::getParam('email')));
    $group_id = Params::getParam('group_update');
    $expire = Params::getParam('expire');
    $user = User::newInstance()->findByEmail($email);
    $group = ModelOSP::newInstance()->getGroup($group_id);

    if($expire == '') {
       if($group['i_days'] <> '' && $group['i_days'] > 0) {
         $expire = date('Y-m-d H:i:s', strtotime(' + ' . $group['i_days'] . ' day', time()));
       } else {
         $expire = date('Y-m-d H:i:s', strtotime(' + 30 day', time()));
       }
    }

    if(isset($user['pk_i_id']) && $user['pk_i_id'] <> '' && $user['pk_i_id'] > 0) {
      if($group_id <> '' && $group_id > 0) {
        ModelOSP::newInstance()->updateUserGroup($user['pk_i_id'], $group_id, $expire);
        message_ok(sprintf(__('%s (%s) successfully assigned to group %s.', 'osclass_pay'), $user['s_name'], $user['s_email'], $group['s_name']));
      } else {
        ModelOSP::newInstance()->deleteUserGroup($user['pk_i_id']);
        message_ok( __('User successfully removed from group', 'osclass_pay') );
      }
    } else {
      message_error( __('User not found', 'osclass_pay') );
    }
  }




  // REMOVE GROUP
  if(Params::getParam('what') == 'group_remove' && Params::getParam('group_id') > 0 && !osp_is_demo()) {
    ModelOSP::newInstance()->deleteGroup(Params::getParam('group_id'));
    message_ok( __('Groups successfully removed', 'osclass_pay') );
  }


  // REMOVE GROUP
  if(Params::getParam('what') == 'user_remove' && Params::getParam('user_id') > 0 && !osp_is_demo()) {
    ModelOSP::newInstance()->deleteUserGroup(Params::getParam('user_id'));
    message_ok( __('User successfully removed from group', 'osclass_pay') );
  }


  // SCROLL TO DIV
  if(Params::getParam('plugin_action') == 'list_users' || Params::getParam('what') == 'user_remove') {
    osp_js_scroll('.mb-user-in-group');
  } else if(Params::getParam('plugin_action') == 'add_user_to_group') {
    osp_js_scroll('.mb-add-users');
  } else if(Params::getParam('plugin_action') == 'group_update' || Params::getParam('what') == 'group_remove') {
    osp_js_scroll('.mb-group-update');
  } else if(Params::getParam('plugin_action') == 'done' || Params::getParam('what') == 'group_remove') {
    osp_js_scroll('.mb-group-manage');
  } else if (Params::getParam('scrollTo') <> '') {
    osp_js_scroll('.' . Params::getParam('scrollTo'));
  }
?>



<div class="mb-body">

  <!-- GROUP CONFIGURATION SECTION -->
  <div class="mb-box mb-group-manage">
    <div class="mb-head">
      <i class="fa fa-star"></i> <?php _e('User Groups', 'osclass_pay'); ?>

      <?php $runs = osp_get_cron_runs(); ?>
      <span class="mb-runs mb-has-tooltip" title="<?php echo osc_esc_html(@$runs[1]); ?>"><?php echo @$runs[0]; ?></span>
    </div>


    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>user.php" />
        <input type="hidden" name="go_to_file" value="_group.php" />
        <input type="hidden" name="plugin_action" value="done" />

        <div class="mb-row">
          <label for="groups_enabled" class="h1"><span><?php _e('Enable User Groups', 'osclass_pay'); ?></span></label> 
          <input name="groups_enabled" id="groups_enabled" class="element-slide" type="checkbox" <?php echo ($groups_enabled == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('Enable user groups (memberships) and promote your regular users with extra benefits.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="groups_category" class="h2"><span><?php _e('Restrict Categories for User Groups', 'osclass_pay'); ?></span></label> 
          <input name="groups_category" id="groups_category" class="element-slide" type="checkbox" <?php echo ($groups_category == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain <?php echo ($groups_category == 1 ? 'mb-explain-red' : ''); ?>"><?php _e('Restrict selected categories to be available just for group members. Note that selected categories and it\'s listings will be available only and only for group members specified bellow!', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="groups_registration" class="h3"><span><?php _e('Add new user to group', 'osclass_pay'); ?></span></label> 
          <select id="groups_registration" name="groups_registration">
            <option value=""><?php _e('No group', 'osclass_pay'); ?></option>

            <?php foreach(ModelOSP::newInstance()->getGroups() as $g) { ?>
              <option value="<?php echo $g['pk_i_id']; ?>" <?php echo ($g['pk_i_id'] == $groups_registration ? 'selected="selected"' : ''); ?>><?php echo $g['s_name']; ?></option>
            <?php } ?>
          </select>
          
          <div class="mb-explain"><?php _e('When new user register, add this user automatically to group.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="groups_limit_items" class="h4"><span><?php _e('Limit User Items', 'osclass_pay'); ?></span></label> 
          <input name="groups_limit_items" id="groups_limit_items" class="element-slide" type="checkbox" <?php echo ($groups_limit_items == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, maximum number of listings those can user create will be limited. In order to increase this count, it is required to be member of group.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="groups_max_items" class="h5"><span><?php _e('Default Max Items', 'osclass_pay'); ?></span></label> 
          <span style="float:left;position:relative;">
            <input size="10" name="groups_max_items" id="groups_max_items" class="mb-short" type="text" style="text-align:right;" value="<?php echo $groups_max_items; ?>" />
            <div class="mb-input-desc"><?php _e('items', 'osclass_pay'); ?></div>
          </span>

          <span style="float:left;position:relative;margin:0 12px;line-height:31px;"><?php _e('in', 'osclass_pay'); ?></span>

          <span style="float:left;position:relative;">
            <input size="10" name="groups_max_items_days" id="groups_max_items_days" class="mb-short" type="text" style="text-align:right;" value="<?php echo $groups_max_items_days; ?>" />
            <div class="mb-input-desc"><?php _e('days', 'osclass_pay'); ?></div>
          </span>
        
          <div class="mb-explain"><?php _e('Define default maximum items count that can be published in selected period. I.e. 10 listings in 30 days for "free". This value will be used for users those are not logged in or for those that are not member of any group.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="groups_max_items_type" class="h6"><span><?php _e('Max Items Count Method', 'osclass_pay'); ?></span></label> 
          <select id="groups_max_items_type" name="groups_max_items_type">
            <option value="0" <?php echo ($groups_max_items_type == 0 ? 'selected="selected"' : ''); ?>><?php _e('Count all items', 'osclass_pay'); ?></option>
            <option value="1" <?php echo ($groups_max_items_type == 1 ? 'selected="selected"' : ''); ?>><?php _e('Count active items', 'osclass_pay'); ?></option>
            <option value="2" <?php echo ($groups_max_items_type == 2 ? 'selected="selected"' : ''); ?>><?php _e('Count all items except premiums', 'osclass_pay'); ?></option>
            <option value="3" <?php echo ($groups_max_items_type == 3 ? 'selected="selected"' : ''); ?>><?php _e('Count active items except premiums', 'osclass_pay'); ?></option>
          </select>
          
          <div class="mb-explain"><?php _e('Select how would you like to count user items.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">&nbsp;</div>

        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
        </div>
      </form>
    </div>
  </div>



  <!-- USER GROUPS SECTION -->
  <div class="mb-box mb-group-update">
    <div class="mb-head">
      <i class="fa fa-star"></i> <?php _e('User Groups', 'osclass_pay'); ?>
      <?php echo osp_locale_box('user.php', '_group.php', 'mb-group-update'); ?>
    </div>


    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>user.php" />
        <input type="hidden" name="go_to_file" value="_group.php" />
        <input type="hidden" name="plugin_action" value="group_update" />
        <input type="hidden" name="ospLocale" value="<?php echo osp_get_locale(); ?>" />

        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('Enable user groups to provide better experience and extra content to your regular customers.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('You can define price for membership, membership discount and also that content of some categories is available just to members of specified group.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('Set periodical bonus if you want to send more credits to members of group using functionality "Add credits to users once per period". Value must be integer, i.e. setting 50 will cause to send 50% more credits to user.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('Attr can be used for integration with different plugins or functionalities. Plugin itself does not use them and you do not need to define them.', 'osclass_pay'); ?></div>
        </div>


        <?php $groups = ModelOSP::newInstance()->getGroups(true); ?>

        <div class="mb-table-group-scroll">
          <div class="mb-table mb-table-group">
            <div class="mb-table-head">
              <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
              <div class="mb-col-5 mb-input-box mb-align-left"><?php _e('Group Name', 'osclass_pay'); ?></div>
              <div class="mb-col-7 mb-input-box mb-align-left"><?php _e('Description', 'osclass_pay'); ?></div>
              <div class="mb-col-2 mb-group-price"><?php _e('Fee', 'osclass_pay'); ?></div>
              <div class="mb-col-2 mb-group-price"><?php _e('Discount', 'osclass_pay'); ?></div>
              <div class="mb-col-2 mb-group-price mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Expiration days - for how many days is price set. User can choose different expiration days and price will be lineary recalculated.', 'osclass_pay')); ?>"><?php _e('Exp. Days', 'osclass_pay'); ?></div>
              <div class="mb-col-1 mb-group-price mb-align-left"><?php _e('Color', 'osclass_pay'); ?></div>
              <div class="mb-col-3">&nbsp;</div>
              <div class="mb-col-1">&nbsp;</div>
            </div>

            <?php foreach($groups as $g) { ?>
              <?php $id = $g['pk_i_id']; ?>

              <div class="mb-table-row">
                <div class="mb-col-1 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_id" value="<?php echo $g['pk_i_id']; ?>" readonly="readonly"/></div>
                <div class="mb-col-5 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_name" value="<?php echo $g['s_name']; ?>" required placeholder="<?php echo osc_esc_html(__('Enter group name', 'osclass_pay')); ?>"/></div>
                <div class="mb-col-7 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_description" value="<?php echo $g['s_description']; ?>"/></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_price" value="<?php echo $g['f_price']; ?>"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_discount" value="<?php echo $g['i_discount']; ?>"/><div class="mb-input-desc">%</div></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_days" value="<?php echo $g['i_days']; ?>"/><div class="mb-input-desc"><?php _e('d', 'osclass_pay'); ?></div></div>
                <div class="mb-col-1 mb-group-price"><input type="color" name="group_<?php echo $id; ?>_color" value="<?php echo ($g['s_color'] <> '' ? $g['s_color'] : '#2eacce'); ?>"/></div>
                <div class="mb-col-3"><a href="#" class="mb-more-details"><i class="fa fa-caret-down"></i> <span><?php _e('More details', 'osclass_pay'); ?></span></a></div>
                <div class="mb-col-1"><a href="<?php echo osp_admin_plugin_url('user.php'); ?>&go_to_file=_group.php&what=group_remove&group_id=<?php echo $g['pk_i_id']; ?>" onclick="return confirm('<?php echo osc_esc_html(__('Are you sure you want to delete this group?', 'osclass_pay')); ?>')" class="mb-group-remove" title="<?php echo osc_esc_html(__('Remove group', 'osclass_pay')); ?>"><i class="fa fa-trash-o"></i></a></div>

                <div class="mb-table-row-hidden">
                  <div class="mb-top-line"><?php _e('Detail group settings', 'osclass_pay'); ?></div>
                  
                  <div class="mb-line">
                    <label><?php _e('Max. Items', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_maxitems" value="<?php echo $g['i_max_items']; ?>"/>
                    <div class="mb-input-desc"><?php _e('items', 'osclass_pay'); ?></div>
                    <div class="mb-explain"><?php echo osc_esc_html(__('How many items can member of this group publish in certain period (days).', 'osclass_pay')); ?></div>
                  </div>
                  
                  <div class="mb-line">
                    <label><?php _e('Item Period', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_maxitemsdays" value="<?php echo $g['i_max_items_days']; ?>"/>
                    <div class="mb-input-desc"><?php _e('days', 'osclass_pay'); ?></div>
                    <div class="mb-explain"><?php echo osc_esc_html(__('Max Items Days Expiration - enter period for calculation max items. I.e 10 items in 30 days. When user want to publish more, it is required to upgrade membership', 'osclass_pay')); ?></div>
                  </div>
                  
                  <div class="mb-line">
                    <label><?php _e('Categories', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_category" value="<?php echo $g['s_category']; ?>"/>
                    <div class="mb-explain"><?php echo osc_esc_html(__('Enter category IDs delimited by comma that should be restricted just to selected user group. Only members of this group will be allowed to see content of those categories. Example: 1,3,7', 'osclass_pay')); ?></div>
                  </div>
                  
                  <div class="mb-line">
                    <label><?php _e('Periodical Bonus', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_pbonus" value="<?php echo $g['i_pbonus']; ?>"/><div class="mb-input-desc">%</div>
                    <div class="mb-explain"><?php echo osc_esc_html(__('Bonus (wallet credits) sent periodically to users as motivation.', 'osclass_pay')); ?></div>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Custom Text', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_custom" value="<?php echo $g['s_custom']; ?>"/>
                    <div class="mb-explain"><?php _e('Text shown on group card.', 'osclass_pay'); ?></div>
                  </div>
                  
                  <div class="mb-line">
                    <label><?php _e('Rank', 'osclass_pay'); ?></label>
                    <?php echo osp_admin_group_ranks($id, $g['i_rank']); ?>
                    <div class="mb-explain"><?php _e('Rank is used to give "weight" to groups and how they rank across each other.', 'osclass_pay'); ?></div>
                  </div>
                  
                  <div class="mb-line">
                    <label><?php _e('Attribute', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_attr" value="<?php echo $g['i_attr']; ?>"/>
                    <div class="mb-explain"><?php _e('Can be used by plugin (ie. Business Profile plugin).', 'osclass_pay'); ?></div>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Pay Per Publish Free Limit', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_free_items_101" value="<?php echo $g['i_free_items_101']; ?>"/>
                    <div class="mb-explain"><?php _e('Number of items user may have active for free (without paying Publish Fee). When this limit is reached, user has to pay Publish fee to have more active listings.', 'osclass_pay'); ?></div>
                  </div>

                </div>
              </div>
            <?php } ?>


            <?php for($i=1;$i<=3-count($groups);$i++) { ?>
              <?php $id = -($i + count($groups)); ?>

              <div class="mb-table-row">
                <div class="mb-col-1">xx</div>
                <div class="mb-col-5 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_name" placeholder="<?php echo osc_esc_html(__('Create new group', 'osclass_pay')); ?>"/></div>
                <div class="mb-col-7 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_description"/></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_price"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_discount"/><div class="mb-input-desc">%</div></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_days"/><div class="mb-input-desc"><?php _e('d', 'osclass_pay'); ?></div></div>
                <div class="mb-col-1 mb-group-price"><input type="color" name="group_<?php echo $id; ?>_color" value="#2eacce"/></div>
                <div class="mb-col-3"><a href="#" class="mb-more-details mb-disabled"><i class="fa fa-caret-down"></i> <span><?php _e('More details', 'osclass_pay'); ?></span></a></div>
                <div class="mb-col-1 mb-del-col"><a href="#" class="mb-group-remove mb-group-new-line" title="<?php echo osc_esc_html(__('Remove group', 'osclass_pay')); ?>"><i class="fa fa-trash-o"></i></a></div>
              </div>
            <?php } ?>

            <div class="mb-group-placeholder">
              <?php $id = -999; ?>

              <div class="mb-table-row" style="display:none;">
                <div class="mb-col-1">xx</div>
                <div class="mb-col-5 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_name" placeholder="<?php echo osc_esc_html(__('Create new group', 'osclass_pay')); ?>"/></div>
                <div class="mb-col-7 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_description"/></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_price"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_discount"/><div class="mb-input-desc">%</div></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_days"/><div class="mb-input-desc"><?php _e('d', 'osclass_pay'); ?></div></div>
                <div class="mb-col-1 mb-group-price"><input type="color" name="group_<?php echo $id; ?>_color" value="#2eacce"/></div>
                <div class="mb-col-3"><a href="#" class="mb-more-details mb-disabled"><i class="fa fa-caret-down"></i> <span><?php _e('More details', 'osclass_pay'); ?></span></a></div>
                <div class="mb-col-1 mb-del-col"><a href="#" class="mb-group-remove mb-group-new-line" title="<?php echo osc_esc_html(__('Remove group', 'osclass_pay')); ?>"><i class="fa fa-trash-o"></i></a></div>
              </div>
            </div>
          </div>
        </div>

        <a href="#" class="mb-button-green mb-add-group"><?php _e('Add new line for group', 'osclass_pay'); ?></a>


        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
        </div>
      </form>
    </div>
  </div>



  <!-- ADD USER TO GROUP -->
  <div class="mb-box mb-add-users">
    <div class="mb-head"><i class="fa fa-plus-circle"></i> <?php _e('Add User to Group', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>user.php" />
        <input type="hidden" name="go_to_file" value="_group.php" />
        <input type="hidden" name="plugin_action" value="add_user_to_group" />

        <?php
          if(Params::getParam('plugin_action') == 'add_user_to_group') {
            $user = User::newInstance()->findByEmail($email);

            if(isset($user['pk_i_id']) && $user['pk_i_id'] <> '' && $user['pk_i_id'] > 0) {
              $user_id = $user['pk_i_id'];
              $user_name = $user['s_name'];
              $user_email = $user['s_email'];
              $user_group = ModelOSP::newInstance()->getUserGroupRecord($user['pk_i_id']);
            }
          }
        ?>


        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('You can check to what group user belongs.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('You can also add or remove user from group.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row mb-group-lookup">
          <div class="mb-line mb-error-block"></div>

          <div class="mb-line">
            <label for="id"><span><?php _e('User ID', 'osclass_pay'); ?></span></label>
            <input type="text" id="id" name="id" readonly="readonly" value="<?php echo (isset($user['pk_i_id']) ? $user['pk_i_id'] : ''); ?>"/>
          </div>

          <div class="mb-line">
            <label for="name"><span><?php _e('Name', 'osclass_pay'); ?></span></label>
            <input type="text" id="name" name="name" placeholder="<?php echo osc_esc_html(__('Type user name or email...', 'osclass_pay')); ?>" value="<?php echo osc_esc_html(isset($user['s_name']) ? $user['s_name'] : ''); ?>" autocomplete="off"/>
            <div class="mb-explain"><?php _e('Start typing user name or email and select user you want to check from list.', 'osclass_pay'); ?></div>
          </div>

          <div class="mb-line">
            <label for="email"><span><?php _e('Email', 'osclass_pay'); ?></span></label>
            <input type="text" id="email" name="email" readonly="readonly" value="<?php echo (isset($user['s_email']) ? $user['s_email'] : ''); ?>"/>
          </div>

          <div class="mb-row"><div class="mb-line">&nbsp;</div><div class="mb-line" style="border-top:1px solid rgba(0,0,0,0.1);">&nbsp;</div></div>

          <div class="mb-line">
            <label for="group_update"><span><?php _e('Update user group', 'osclass_pay'); ?></span></label>
            <select id="group_update" name="group_update">
              <option value=""><?php _e('No Group', 'osclass_pay'); ?></option>

              <?php foreach(ModelOSP::newInstance()->getGroups() as $g) { ?>
                <option value="<?php echo $g['pk_i_id']; ?>" <?php echo ($g['pk_i_id'] == @$user_group['pk_i_id'] ? 'selected="selected"' : ''); ?>><?php echo $g['s_name']; ?></option>
              <?php } ?>
            </select>

            <button type="submit" class="mb-button-green"><i class="fa fa-check"></i> <?php _e('Update', 'osclass_pay');?></button>
          </div>

          <div class="mb-line">
            <label for="expire"><span><?php _e('Membership Expire on', 'osclass_pay'); ?></span></label>
            <input type="text" id="expire" name="expire" value="<?php echo (isset($user_group['dt_expire']) ? $user_group['dt_expire']: ''); ?>" placeholder="yyyy-mm-dd HH:mm:ss"/>
            <div class="mb-explain"><?php _e('If no date is entered, it is calculated by default number of days for group.', 'osclass_pay'); ?></div>
          </div>
        </div>
      </form>
    </div>
  </div>



  <!-- SHOW USERS IN GROUP -->
  <div class="mb-box mb-user-in-group">
    <div class="mb-head"><i class="fa fa-list"></i> <?php _e('List users in group', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>user.php" />
        <input type="hidden" name="go_to_file" value="_group.php" />
        <input type="hidden" name="plugin_action" value="list_users" />

        <div class="mb-row">
          <label for="list_group"><span><?php _e('Select Group', 'osclass_pay'); ?></span></label> 
          <select id="list_group" name="list_group">
            <option value=""><?php _e('Select Group', 'osclass_pay'); ?></option>

            <?php foreach(ModelOSP::newInstance()->getGroups() as $g) { ?>
              <option value="<?php echo $g['pk_i_id']; ?>" <?php echo ($g['pk_i_id'] == Params::getParam('list_group') ? 'selected="selected"' : ''); ?>><?php echo $g['s_name']; ?></option>
            <?php } ?>
          </select>
          <button type="submit" class="mb-button-green"><i class="fa fa-check"></i> <?php _e('List Users', 'osclass_pay');?></button>

          <div class="mb-explain"><?php _e('Select group to list users.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">&nbsp;</div>


        <?php if(Params::getParam('list_group') <> '' && Params::getParam('list_group') > 0) { ?>
          <?php $list_users = ModelOSP::newInstance()->getUsersByGroup(Params::getParam('list_group')); ?>
          <div class="mb-table mb-table-group-list">
            <div class="mb-table-head">
              <div class="mb-col-1"><?php _e('ID', 'osclass_pay'); ?></div>
              <div class="mb-col-5 mb-align-left"><?php _e('User Name', 'osclass_pay'); ?></div>
              <div class="mb-col-8 mb-align-left"><?php _e('Email', 'osclass_pay'); ?></div>
              <div class="mb-col-4 mb-align-left"><?php _e('Group', 'osclass_pay'); ?></div>
              <div class="mb-col-5"><?php _e('Expire on', 'osclass_pay'); ?></div>
              <div class="mb-col-1">&nbsp;</div>
            </div>

            <?php if(count($list_users) <= 0) { ?>
              <div class="mb-table-row mb-row-empty">
                <i class="fa fa-warning"></i><span><?php _e('No users has been found in selected group', 'osclass_pay'); ?></span>
              </div>
            <?php } else { ?>
              <?php foreach($list_users as $u) { ?>
                <div class="mb-table-row">
                  <div class="mb-col-1"><?php echo $u['user_id']; ?></div>
                  <div class="mb-col-5 mb-align-left"><?php echo $u['user_name']; ?></div>
                  <div class="mb-col-8 mb-align-left"><?php echo $u['user_email']; ?></div>
                  <div class="mb-col-4 mb-align-left">
                    <div class="mb-group-label-short" style="background:<?php echo $u['group_color']; ?>;color:<?php echo osp_text_color($u['group_color']); ?>"><?php echo $u['group_name']; ?></div>
                  </div>
                  <div class="mb-col-5"><?php echo $u['expire']; ?></div>
                  <div class="mb-col-1 mb-del-col"><a href="<?php echo osp_admin_plugin_url('user.php'); ?>&go_to_file=_group.php&what=user_remove&user_id=<?php echo $u['user_id']; ?>" onclick="return confirm('<?php echo osc_esc_html(__('Are you sure you want to delete this user from group?', 'osclass_pay')); ?>')" class="mb-group-remove" title="<?php echo osc_esc_html(__('Remove user', 'osclass_pay')); ?>"><i class="fa fa-trash-o"></i></a></div>
                </div>
              <?php } ?>
            <?php } ?>
          </div>
        <?php } ?>
      </form>
    </div>
  </div>



  <!-- HELP TOPICS -->
  <div class="mb-box" id="mb-help">
    <div class="mb-head"><i class="fa fa-question-circle"></i> <?php _e('Help', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-help"><span class="sup">(1)</span> <div class="h1"><?php _e('When enabled, users can purchase membership in different groups. You can set privileges to groups or integrate them with different plugins.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(2)</span> <div class="h2"><?php _e('When enabled, categories selected in definition of groups will be visible only and only to members of these groups. Care! For everyone else will be content of category hidden. Even member without access to category can publish listing inside it.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(3)</span> <div class="h3"><?php _e('Select group where newly registered user is automatically added.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(4)</span> <div class="h4"><?php _e('When enabled, users will be able to publish just XX items in YY days. Note that if you have enabled publishing items for unregistered, these people can avoid this limit by using multiple email addresses.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(5)</span> <div class="h5"><?php _e('Enter what is maximum count of listings published in selected period. Period is used as (Today) <-> (Today - YY days).', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(6)</span> <div class="h6"><?php _e('Select methodology to calulate user items. It is recommended to count all active items.', 'osclass_pay'); ?></div></div>
    </div>
  </div>
</div>


<script type="text/javascript">
  var group_lookup_error = "<?php echo osc_esc_js(__('Error getting data, user not found', 'osclass_pay')); ?>";
  var group_lookup_url = "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=runhook&hook=osp_group_data&id=";
  var group_lookup_base = "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=userajax";
</script>

<?php echo osp_footer(); ?>