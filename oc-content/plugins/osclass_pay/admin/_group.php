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

      // Make sure it's a group parameter and specifically check for the 'name' field to identify a valid group row
      if($detail[0] == 'group' && isset($detail[1]) && isset($detail[2]) && $detail[2] == 'name') {
         // Only consider rows where a name is actually provided
        if(isset($params['group_' . $detail[1] . '_name']) && trim($params['group_' . $detail[1] . '_name']) !== '') {
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
        // $free_items_201 = @$params['group_' . $i . '_free_items_201']; // Example commented out fields
        // $free_items_401 = @$params['group_' . $i . '_free_items_401']; // Example commented out fields

        // ***** START NEW FIELD (s_type) *****
        // Get the type, default to 'standard' if empty or not set
        $type = isset($params['group_' . $i . '_type']) ? trim($params['group_' . $i . '_type']) : 'standard';
        if ($type === '') {
            $type = 'standard'; // Ensure empty string becomes 'standard'
        }
        // ***** END NEW FIELD (s_type) *****


        if($pbonus <> '' && $pbonus < 0) {
          $pbonus = '';
          message_error( __('Value for Periodical bonus was not entered correctly. It must be integer larger or equal to 0.', 'osclass_pay') );
        }

        // ***** UPDATE MODEL CALL *****
        // NOTE: You MUST adjust the ModelOSP::updateGroup function definition to accept $type as a parameter (likely near the end).
        $response = ModelOSP::newInstance()->updateGroup(
            $id, $name, $desc, $price, $discount, $days, $color, $category, $pbonus, $custom, $rank, $attr,
            $max_items, $max_items_days, $free_items_101, /* $free_items_201, $free_items_401, */ // Add other free items if they exist
            $type // Add the new type parameter here
        );
        // ***** END UPDATE MODEL CALL *****


        if(!$response) {
          // Use %d for group ID if $id is available and numeric, otherwise fallback to name
          $error_identifier = (is_numeric($id) && $id > 0) ? $id : $name;
          message_error( sprintf(__('Error updating group %s. Maybe a group with the same name already exists?', 'osclass_pay'), $error_identifier) );
        }
      }
    }

    if(Params::getParam('plugin_action') == 'group_update' && !osp_is_demo()) {
      $ids = array();
      $params = Params::getParamsAsArray();
      $errors_occurred = false; // <-- ADD FLAG: Initialize error flag
  
      // FIRST GET IDS OF GROUPS THAT SHOULD BE UPDATED
      // ... (keep the existing logic to populate $ids) ...
      foreach(array_keys($params) as $p) {
        $detail = explode('_', $p);
        if($detail[0] == 'group' && isset($detail[1]) && isset($detail[2]) && $detail[2] == 'name') {
          if(isset($params['group_' . $detail[1] . '_name']) && trim($params['group_' . $detail[1] . '_name']) !== '') {
            $ids[] = $detail[1];
          }
        }
      }
      $ids = array_unique($ids);
  
  
      if(count($ids) > 0) {
        foreach($ids as $i) {
          // ... (keep the existing logic to get $id, $name, $desc, $type, etc.) ...
          $id = @$params['group_' . $i . '_id'];
          $name = @$params['group_' . $i . '_name'];
          $desc = @$params['group_' . $i . '_description'];
          $type = isset($params['group_' . $i . '_type']) ? trim($params['group_' . $i . '_type']) : 'standard';
          if ($type === '') { $type = 'standard'; }
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
          // ... other free items ...
  
  
          if($pbonus <> '' && $pbonus < 0) {
            $pbonus = '';
            message_error( __('Value for Periodical bonus was not entered correctly. It must be integer larger or equal to 0.', 'osclass_pay') );
            $errors_occurred = true; // <-- SET FLAG: Error occurred
          }
  
          // ***** UPDATE MODEL CALL *****
          $response = ModelOSP::newInstance()->updateGroup(
              $id, $name, $desc, $type, $price, $discount, $days, $color, $category, $pbonus, $custom, $rank, $attr,
              $max_items, $max_items_days, $free_items_101 /*, $free_items_201, $free_items_401 */
          );
          // ***** END UPDATE MODEL CALL *****
  
  
          if(!$response) {
            // Use %d for group ID if $id is available and numeric, otherwise fallback to name
            $error_identifier = (is_numeric($id) && $id > 0) ? $id : $name;
            message_error( sprintf(__('Error updating group %s. Maybe a group with the same name already exists?', 'osclass_pay'), $error_identifier) );
            $errors_occurred = true; // <-- SET FLAG: Error occurred
          }
        } // End foreach loop
      } // End if(count($ids) > 0)
  
      // Check the flag instead of the Osclass function
      if (!$errors_occurred) { // <-- CHECK FLAG: Show success only if no errors happened
          message_ok( __('Groups successfully updated', 'osclass_pay') );
      }
  
    } // End if(Params::getParam('plugin_action') == 'group_update')  
  }



  // UPDATE USER IN GROUP
  if(Params::getParam('plugin_action') == 'add_user_to_group') {
    $email = trim(strtolower(Params::getParam('email')));
    $group_id = Params::getParam('group_update');
    $expire = Params::getParam('expire');
    $user = User::newInstance()->findByEmail($email);
    $group = ModelOSP::newInstance()->getGroup($group_id); // Assumes getGroup fetches s_type

    if($expire == '') {
       if(isset($group['i_days']) && $group['i_days'] <> '' && $group['i_days'] > 0) {
         $expire = date('Y-m-d H:i:s', strtotime(' + ' . $group['i_days'] . ' day', time()));
       } else {
         $expire = date('Y-m-d H:i:s', strtotime(' + 30 day', time()));
       }
    }

    if(isset($user['pk_i_id']) && $user['pk_i_id'] <> '' && $user['pk_i_id'] > 0) {
      if($group_id <> '' && $group_id > 0) {
        ModelOSP::newInstance()->updateUserGroup($user['pk_i_id'], $group_id, $expire);
        // Include group type in message if available
        $group_name_display = isset($group['s_name']) ? $group['s_name'] : __('Unknown Group', 'osclass_pay');
        if (isset($group['s_type']) && $group['s_type'] != 'standard') {
            $group_name_display .= ' (' . ucfirst(osc_esc_html($group['s_type'])) . ')';
        }
        message_ok(sprintf(__('%s (%s) successfully assigned to group %s.', 'osclass_pay'), $user['s_name'], $user['s_email'], $group_name_display));
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
    message_ok( __('Group successfully removed', 'osclass_pay') ); // Changed message slightly
  }


  // REMOVE USER FROM GROUP
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
      <i class="fa fa-cog"></i> <?php _e('User Groups Settings', 'osclass_pay'); ?> <?php // Changed title slightly ?>

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

            <?php foreach(ModelOSP::newInstance()->getGroups() as $g) { // Assumes getGroups fetches s_type ?>
               <?php
                 $group_name_display = osc_esc_html($g['s_name']);
                 if (isset($g['s_type']) && $g['s_type'] != 'standard') {
                     $group_name_display .= ' (' . ucfirst(osc_esc_html($g['s_type'])) . ')';
                 }
               ?>
              <option value="<?php echo $g['pk_i_id']; ?>" <?php echo ($g['pk_i_id'] == $groups_registration ? 'selected="selected"' : ''); ?>><?php echo $group_name_display; ?></option>
            <?php } ?>
          </select>

          <div class="mb-explain"><?php _e('When new user register, add this user automatically to selected group.', 'osclass_pay'); ?></div> <?php // Slightly rephrased ?>
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


        <div class="mb-row"> </div>

        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Update Settings', 'osclass_pay');?></button> <?php // Changed button text slightly ?>
        </div>
      </form>
    </div>
  </div>



  <!-- USER GROUPS SECTION -->
  <div class="mb-box mb-group-update">
    <div class="mb-head">
      <i class="fa fa-users"></i> <?php _e('Manage User Groups', 'osclass_pay'); ?> <?php // Changed icon and title slightly ?>
      <?php echo osp_locale_box('user.php', '_group.php', 'mb-group-update'); ?>
    </div>


    <div class="mb-inside">
      <form name="group_form" id="group_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" > <?php // Changed form name/id ?>
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>user.php" />
        <input type="hidden" name="go_to_file" value="_group.php" />
        <input type="hidden" name="plugin_action" value="group_update" />
        <input type="hidden" name="ospLocale" value="<?php echo osp_get_locale(); ?>" />

        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('Define different user groups (memberships). Use the "More details" section to configure specific settings for each group.', 'osclass_pay'); ?></div> <?php // Updated notes ?>
          <div class="mb-line"><?php _e('You can set membership fees, discounts, category access, item limits, and assign a type (e.g., Standard, VIP).', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('Set periodical bonus if you want to send more credits to members of group using functionality "Add credits to users once per period". Value must be integer, i.e. setting 50 will cause to send 50% more credits to user.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('Attr can be used for integration with different plugins or functionalities. Plugin itself does not use them and you do not need to define them.', 'osclass_pay'); ?></div>
        </div>


        <?php $groups = ModelOSP::newInstance()->getGroups(true); // Ensure getGroups fetches the new 's_type' field ?>

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
              <div class="mb-col-3"> </div>
              <div class="mb-col-1"> </div>
            </div>

            <?php foreach($groups as $g) { ?>
              <?php $id = $g['pk_i_id']; ?>
              <?php $group_type = isset($g['s_type']) ? $g['s_type'] : 'standard'; // Default to 'standard' if not set ?>

              <div class="mb-table-row">
                <div class="mb-col-1 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_id" value="<?php echo $g['pk_i_id']; ?>" readonly="readonly"/></div>
                <div class="mb-col-5 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_name" value="<?php echo osc_esc_html($g['s_name']); ?>" required placeholder="<?php echo osc_esc_html(__('Enter group name', 'osclass_pay')); ?>"/></div>
                <div class="mb-col-7 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_description" value="<?php echo osc_esc_html($g['s_description']); ?>"/></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_price" value="<?php echo $g['f_price']; ?>"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_discount" value="<?php echo $g['i_discount']; ?>"/><div class="mb-input-desc">%</div></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_days" value="<?php echo $g['i_days']; ?>"/><div class="mb-input-desc"><?php _e('d', 'osclass_pay'); ?></div></div>
                <div class="mb-col-1 mb-group-price"><input type="color" name="group_<?php echo $id; ?>_color" value="<?php echo ($g['s_color'] <> '' ? $g['s_color'] : '#2eacce'); ?>"/></div>
                <div class="mb-col-3"><a href="#" class="mb-more-details"><i class="fa fa-caret-down"></i> <span><?php _e('More details', 'osclass_pay'); ?></span></a></div>
                <div class="mb-col-1"><a href="<?php echo osp_admin_plugin_url('user.php'); ?>&go_to_file=_group.php&what=group_remove&group_id=<?php echo $g['pk_i_id']; ?>" onclick="return confirm('<?php echo osc_esc_js(__('Are you sure you want to delete this group? This action cannot be undone.', 'osclass_pay')); ?>')" class="mb-group-remove" title="<?php echo osc_esc_html(__('Remove group', 'osclass_pay')); ?>"><i class="fa fa-trash-o"></i></a></div> <?php // Added JS escaping and stronger warning ?>

                <div class="mb-table-row-hidden">
                  <div class="mb-top-line"><?php _e('Detail group settings', 'osclass_pay'); ?></div>

                  <div class="mb-line">
                    <label><?php _e('Max. Items', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_maxitems" value="<?php echo $g['i_max_items']; ?>"/>
                    <div class="mb-input-desc"><?php _e('items', 'osclass_pay'); ?></div>
                    <div class="mb-explain"><?php echo osc_esc_html(__('How many items can member of this group publish in certain period (days). Leave empty for unlimited.', 'osclass_pay')); ?></div> <?php // Added note ?>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Item Period', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_maxitemsdays" value="<?php echo $g['i_max_items_days']; ?>"/>
                    <div class="mb-input-desc"><?php _e('days', 'osclass_pay'); ?></div>
                    <div class="mb-explain"><?php echo osc_esc_html(__('Period (in days) for the Max Items limit calculation (e.g., 10 items per 30 days).', 'osclass_pay')); ?></div> <?php // Rephrased ?>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Restricted Categories', 'osclass_pay'); ?></label> <?php // Changed label ?>
                    <input type="text" name="group_<?php echo $id; ?>_category" value="<?php echo $g['s_category']; ?>"/>
                    <div class="mb-explain"><?php echo osc_esc_html(__('Enter category IDs (comma-separated) visible only to members of this group. Requires "Restrict Categories" setting to be enabled. Example: 1,3,7', 'osclass_pay')); ?></div> <?php // Updated explanation ?>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Periodical Bonus', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_pbonus" value="<?php echo $g['i_pbonus']; ?>"/><div class="mb-input-desc">%</div>
                    <div class="mb-explain"><?php echo osc_esc_html(__('Bonus (wallet credits) sent periodically to users as motivation (percentage of what they would normally receive).', 'osclass_pay')); ?></div> <?php // Clarified percentage ?>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Custom Text', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_custom" value="<?php echo osc_esc_html($g['s_custom']); ?>"/> <?php // Escaped value ?>
                    <div class="mb-explain"><?php _e('Optional text shown on the group card (e.g., on membership selection page).', 'osclass_pay'); ?></div> <?php // Clarified usage ?>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Rank', 'osclass_pay'); ?></label>
                    <?php echo osp_admin_group_ranks($id, $g['i_rank']); ?>
                    <div class="mb-explain"><?php _e('Rank determines group hierarchy and potential permission overrides (higher rank usually wins).', 'osclass_pay'); ?></div> <?php // Clarified usage ?>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Attribute', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_attr" value="<?php echo $g['i_attr']; ?>"/>
                    <div class="mb-explain"><?php _e('Internal attribute, can be used by other plugins for integration (e.g., Business Profile plugin).', 'osclass_pay'); ?></div> <?php // Clarified usage ?>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Pay Per Publish Free Limit', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_free_items_101" value="<?php echo $g['i_free_items_101']; ?>"/>
                    <div class="mb-explain"><?php _e('Number of items user may have active for free (without paying Publish Fee). When this limit is reached, user has to pay Publish fee to have more active listings.', 'osclass_pay'); ?></div>
                  </div>

                  <?php /* Add other free item limits here if needed, e.g.:
                  <div class="mb-line">
                    <label><?php _e('Free Item Limit 201', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_free_items_201" value="<?php echo @$g['i_free_items_201']; ?>"/>
                    <div class="mb-explain"><?php _e('Explain this limit.', 'osclass_pay'); ?></div>
                  </div>
                  */ ?>

                  <!-- ***** START NEW FIELD UI (s_type) ***** -->
                  <div class="mb-line">
                    <label><?php _e('Group Type', 'osclass_pay'); ?></label>
                    <select name="group_<?php echo $id; ?>_type">
                        <option value="standard" <?php echo ($group_type == 'standard' ? 'selected="selected"' : ''); ?>><?php _e('Standard', 'osclass_pay'); ?></option>
                        <option value="vip" <?php echo ($group_type == 'vip' ? 'selected="selected"' : ''); ?>><?php _e('VIP', 'osclass_pay'); ?></option>
                        <?php // Add more predefined types here if needed: ?>
                        <?php // <option value="premium" <?php echo ($group_type == 'premium' ? 'selected="selected"' : ''); ?>>Premium</option> ?>
                    </select>
                    <div class="mb-explain"><?php _e('Select the type for this group (e.g., Standard, VIP). Affects display and potentially permissions based on other settings or plugins.', 'osclass_pay'); ?></div>
                  </div>
                  <!-- ***** END NEW FIELD UI (s_type) ***** -->

                </div>
              </div>
            <?php } ?>


            <?php for($i=1;$i<=3-count($groups);$i++) { ?>
              <?php $id = -($i + count($groups)); // Use negative IDs for new rows ?>

              <div class="mb-table-row mb-table-row-new"> <?php // Added class for potential JS targeting ?>
                <div class="mb-col-1">xx</div>
                <div class="mb-col-5 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_name" placeholder="<?php echo osc_esc_html(__('Enter name for new group', 'osclass_pay')); ?>"/></div> <?php // Updated placeholder ?>
                <div class="mb-col-7 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_description"/></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_price"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_discount"/><div class="mb-input-desc">%</div></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_days"/><div class="mb-input-desc"><?php _e('d', 'osclass_pay'); ?></div></div>
                <div class="mb-col-1 mb-group-price"><input type="color" name="group_<?php echo $id; ?>_color" value="#2eacce"/></div>
                <div class="mb-col-3"><a href="#" class="mb-more-details"><i class="fa fa-caret-down"></i> <span><?php _e('More details', 'osclass_pay'); ?></span></a></div> <?php // Enable details for new rows too ?>
                <div class="mb-col-1 mb-del-col"><a href="#" class="mb-group-remove mb-group-new-line" title="<?php echo osc_esc_html(__('Remove this new line', 'osclass_pay')); ?>"><i class="fa fa-trash-o"></i></a></div> <?php // Changed title ?>

                <div class="mb-table-row-hidden"> <?php // Add hidden details section for new rows ?>
                  <div class="mb-top-line"><?php _e('Detail group settings', 'osclass_pay'); ?></div>

                  <div class="mb-line">
                    <label><?php _e('Max. Items', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_maxitems" value=""/>
                    <div class="mb-input-desc"><?php _e('items', 'osclass_pay'); ?></div>
                    <div class="mb-explain"><?php echo osc_esc_html(__('How many items can member of this group publish in certain period (days). Leave empty for unlimited.', 'osclass_pay')); ?></div>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Item Period', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_maxitemsdays" value=""/>
                    <div class="mb-input-desc"><?php _e('days', 'osclass_pay'); ?></div>
                    <div class="mb-explain"><?php echo osc_esc_html(__('Period (in days) for the Max Items limit calculation (e.g., 10 items per 30 days).', 'osclass_pay')); ?></div>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Restricted Categories', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_category" value=""/>
                    <div class="mb-explain"><?php echo osc_esc_html(__('Enter category IDs (comma-separated) visible only to members of this group. Requires "Restrict Categories" setting to be enabled. Example: 1,3,7', 'osclass_pay')); ?></div>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Periodical Bonus', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_pbonus" value=""/><div class="mb-input-desc">%</div>
                    <div class="mb-explain"><?php echo osc_esc_html(__('Bonus (wallet credits) sent periodically to users as motivation (percentage of what they would normally receive).', 'osclass_pay')); ?></div>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Custom Text', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_custom" value=""/>
                    <div class="mb-explain"><?php _e('Optional text shown on the group card (e.g., on membership selection page).', 'osclass_pay'); ?></div>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Rank', 'osclass_pay'); ?></label>
                    <?php echo osp_admin_group_ranks($id, ''); // No default rank for new ?>
                    <div class="mb-explain"><?php _e('Rank determines group hierarchy and potential permission overrides (higher rank usually wins).', 'osclass_pay'); ?></div>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Attribute', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_attr" value=""/>
                    <div class="mb-explain"><?php _e('Internal attribute, can be used by other plugins for integration (e.g., Business Profile plugin).', 'osclass_pay'); ?></div>
                  </div>

                  <div class="mb-line">
                    <label><?php _e('Pay Per Publish Free Limit', 'osclass_pay'); ?></label>
                    <input type="text" name="group_<?php echo $id; ?>_free_items_101" value=""/>
                    <div class="mb-explain"><?php _e('Number of items user may have active for free (without paying Publish Fee). When this limit is reached, user has to pay Publish fee to have more active listings.', 'osclass_pay'); ?></div>
                  </div>

                  <?php /* Add other free item limits here if needed */ ?>

                  <!-- ***** START NEW FIELD UI (s_type - New Row) ***** -->
                  <div class="mb-line">
                    <label><?php _e('Group Type', 'osclass_pay'); ?></label>
                    <select name="group_<?php echo $id; ?>_type">
                        <option value="standard" selected="selected"><?php _e('Standard', 'osclass_pay'); ?></option> <?php // Default to Standard ?>
                        <option value="vip"><?php _e('VIP', 'osclass_pay'); ?></option>
                         <?php // Add more predefined types here if needed: ?>
                         <?php // <option value="premium">Premium</option> ?>
                    </select>
                    <div class="mb-explain"><?php _e('Select the type for this group (e.g., Standard, VIP). Affects display and potentially permissions based on other settings or plugins.', 'osclass_pay'); ?></div>
                  </div>
                  <!-- ***** END NEW FIELD UI (s_type - New Row) ***** -->
                </div>
              </div>
            <?php } ?>

            <div class="mb-group-placeholder">
              <?php $id = -999; // Placeholder ID ?>

              <div class="mb-table-row" style="display:none;"> <?php // This whole row is cloned by JS ?>
                <div class="mb-col-1">xx</div>
                <div class="mb-col-5 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_name" placeholder="<?php echo osc_esc_html(__('Enter name for new group', 'osclass_pay')); ?>"/></div>
                <div class="mb-col-7 mb-input-box"><input type="text" name="group_<?php echo $id; ?>_description"/></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_price"/><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_discount"/><div class="mb-input-desc">%</div></div>
                <div class="mb-col-2 mb-group-price"><input type="text" name="group_<?php echo $id; ?>_days"/><div class="mb-input-desc"><?php _e('d', 'osclass_pay'); ?></div></div>
                <div class="mb-col-1 mb-group-price"><input type="color" name="group_<?php echo $id; ?>_color" value="#2eacce"/></div>
                <div class="mb-col-3"><a href="#" class="mb-more-details"><i class="fa fa-caret-down"></i> <span><?php _e('More details', 'osclass_pay'); ?></span></a></div>
                <div class="mb-col-1 mb-del-col"><a href="#" class="mb-group-remove mb-group-new-line" title="<?php echo osc_esc_html(__('Remove this new line', 'osclass_pay')); ?>"><i class="fa fa-trash-o"></i></a></div>

                <div class="mb-table-row-hidden"> <?php // Add hidden details section for placeholder ?>
                    <div class="mb-top-line"><?php _e('Detail group settings', 'osclass_pay'); ?></div>

                    <div class="mb-line">
                      <label><?php _e('Max. Items', 'osclass_pay'); ?></label>
                      <input type="text" name="group_<?php echo $id; ?>_maxitems" value=""/>
                      <div class="mb-input-desc"><?php _e('items', 'osclass_pay'); ?></div>
                      <div class="mb-explain"><?php echo osc_esc_html(__('How many items can member of this group publish in certain period (days). Leave empty for unlimited.', 'osclass_pay')); ?></div>
                    </div>

                    <div class="mb-line">
                      <label><?php _e('Item Period', 'osclass_pay'); ?></label>
                      <input type="text" name="group_<?php echo $id; ?>_maxitemsdays" value=""/>
                      <div class="mb-input-desc"><?php _e('days', 'osclass_pay'); ?></div>
                      <div class="mb-explain"><?php echo osc_esc_html(__('Period (in days) for the Max Items limit calculation (e.g., 10 items per 30 days).', 'osclass_pay')); ?></div>
                    </div>

                    <div class="mb-line">
                      <label><?php _e('Restricted Categories', 'osclass_pay'); ?></label>
                      <input type="text" name="group_<?php echo $id; ?>_category" value=""/>
                      <div class="mb-explain"><?php echo osc_esc_html(__('Enter category IDs (comma-separated) visible only to members of this group. Requires "Restrict Categories" setting to be enabled. Example: 1,3,7', 'osclass_pay')); ?></div>
                    </div>

                    <div class="mb-line">
                      <label><?php _e('Periodical Bonus', 'osclass_pay'); ?></label>
                      <input type="text" name="group_<?php echo $id; ?>_pbonus" value=""/><div class="mb-input-desc">%</div>
                      <div class="mb-explain"><?php echo osc_esc_html(__('Bonus (wallet credits) sent periodically to users as motivation (percentage of what they would normally receive).', 'osclass_pay')); ?></div>
                    </div>

                    <div class="mb-line">
                      <label><?php _e('Custom Text', 'osclass_pay'); ?></label>
                      <input type="text" name="group_<?php echo $id; ?>_custom" value=""/>
                      <div class="mb-explain"><?php _e('Optional text shown on the group card (e.g., on membership selection page).', 'osclass_pay'); ?></div>
                    </div>

                    <div class="mb-line">
                      <label><?php _e('Rank', 'osclass_pay'); ?></label>
                      <?php echo osp_admin_group_ranks($id, ''); ?>
                      <div class="mb-explain"><?php _e('Rank determines group hierarchy and potential permission overrides (higher rank usually wins).', 'osclass_pay'); ?></div>
                    </div>

                    <div class="mb-line">
                      <label><?php _e('Attribute', 'osclass_pay'); ?></label>
                      <input type="text" name="group_<?php echo $id; ?>_attr" value=""/>
                      <div class="mb-explain"><?php _e('Internal attribute, can be used by other plugins for integration (e.g., Business Profile plugin).', 'osclass_pay'); ?></div>
                    </div>

                    <div class="mb-line">
                      <label><?php _e('Pay Per Publish Free Limit', 'osclass_pay'); ?></label>
                      <input type="text" name="group_<?php echo $id; ?>_free_items_101" value=""/>
                      <div class="mb-explain"><?php _e('Number of items user may have active for free (without paying Publish Fee). When this limit is reached, user has to pay Publish fee to have more active listings.', 'osclass_pay'); ?></div>
                    </div>

                    <?php /* Add other free item limits here if needed */ ?>

                    <!-- ***** START NEW FIELD UI (s_type - Placeholder) ***** -->
                    <div class="mb-line">
                      <label><?php _e('Group Type', 'osclass_pay'); ?></label>
                      <select name="group_<?php echo $id; ?>_type">
                          <option value="standard" selected="selected"><?php _e('Standard', 'osclass_pay'); ?></option>
                          <option value="vip"><?php _e('VIP', 'osclass_pay'); ?></option>
                           <?php // Add more predefined types here if needed: ?>
                           <?php // <option value="premium">Premium</option> ?>
                      </select>
                      <div class="mb-explain"><?php _e('Select the type for this group (e.g., Standard, VIP). Affects display and potentially permissions based on other settings or plugins.', 'osclass_pay'); ?></div>
                    </div>
                    <!-- ***** END NEW FIELD UI (s_type - Placeholder) ***** -->
                </div>
              </div>
            </div>
          </div>
        </div>

        <a href="#" class="mb-button-green mb-add-group"><i class="fa fa-plus"></i> <?php _e('Add New Group Line', 'osclass_pay'); ?></a> <?php // Added icon and changed text ?>


        <div class="mb-foot">
          <button type="submit" class="mb-button"><i class="fa fa-check"></i> <?php _e('Update Groups', 'osclass_pay');?></button> <?php // Changed button text ?>
        </div>
      </form>
    </div>
  </div>



  <!-- ADD USER TO GROUP -->
  <div class="mb-box mb-add-users">
    <div class="mb-head"><i class="fa fa-user-plus"></i> <?php _e('Manage User Membership', 'osclass_pay'); ?></div> <?php // Changed icon and title ?>

    <div class="mb-inside">
      <form name="user_group_form" id="user_group_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" > <?php // Changed form name/id ?>
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>user.php" />
        <input type="hidden" name="go_to_file" value="_group.php" />
        <input type="hidden" name="plugin_action" value="add_user_to_group" />

        <?php
          $user_id = ''; $user_name = ''; $user_email = ''; $user_group = null; // Initialize variables
          if(Params::getParam('plugin_action') == 'add_user_to_group' && Params::getParam('email') != '') { // Check if email was actually submitted
            $email = trim(strtolower(Params::getParam('email')));
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
          <div class="mb-line"><?php _e('Look up a user by name or email to view or change their group membership and expiration date.', 'osclass_pay'); ?></div> <?php // Updated note ?>
        </div>


        <div class="mb-row mb-group-lookup">
          <div class="mb-line mb-error-block"></div>

          <div class="mb-line">
            <label for="id"><span><?php _e('User ID', 'osclass_pay'); ?></span></label>
            <input type="text" id="id" name="id" readonly="readonly" value="<?php echo $user_id; ?>"/>
          </div>

          <div class="mb-line">
            <label for="name"><span><?php _e('User Lookup', 'osclass_pay'); ?></span></label> <?php // Changed label ?>
            <input type="text" id="name" name="name" placeholder="<?php echo osc_esc_html(__('Start typing user name or email...', 'osclass_pay')); ?>" value="<?php echo osc_esc_html($user_name); ?>" autocomplete="off"/> <?php // Use current name if found ?>
            <div class="mb-explain"><?php _e('Select the user you want to manage from the suggestions.', 'osclass_pay'); ?></div> <?php // Updated explanation ?>
          </div>

          <div class="mb-line">
            <label for="email"><span><?php _e('Email', 'osclass_pay'); ?></span></label>
            <input type="text" id="email" name="email" readonly="readonly" value="<?php echo $user_email; ?>"/>
          </div>

          <div class="mb-row"><div class="mb-line"> </div><div class="mb-line" style="border-top:1px solid rgba(0,0,0,0.1);"> </div></div>

          <div class="mb-line">
            <label for="group_update"><span><?php _e('Assign Group', 'osclass_pay'); ?></span></label> <?php // Changed label ?>
            <select id="group_update" name="group_update">
              <option value=""><?php _e('No Group', 'osclass_pay'); ?></option>

              <?php foreach(ModelOSP::newInstance()->getGroups() as $g) { // Assumes getGroups fetches s_type ?>
                <?php
                   $group_name_display = osc_esc_html($g['s_name']);
                   if (isset($g['s_type']) && $g['s_type'] != 'standard') {
                       $group_name_display .= ' (' . ucfirst(osc_esc_html($g['s_type'])) . ')';
                   }
                 ?>
                <option value="<?php echo $g['pk_i_id']; ?>" <?php echo (isset($user_group['fk_i_group_id']) && $g['pk_i_id'] == $user_group['fk_i_group_id'] ? 'selected="selected"' : ''); ?>><?php echo $group_name_display; ?></option> <?php // Check fk_i_group_id ?>
              <?php } ?>
            </select>

            <button type="submit" class="mb-button-green" style="margin-left:10px;"><i class="fa fa-check"></i> <?php _e('Update Membership', 'osclass_pay');?></button> <?php // Changed button text ?>
          </div>

          <div class="mb-line">
            <label for="expire"><span><?php _e('Membership Expires On', 'osclass_pay'); ?></span></label> <?php // Changed label ?>
            <input type="text" id="expire" name="expire" value="<?php echo (isset($user_group['dt_expire']) ? $user_group['dt_expire']: ''); ?>" placeholder="yyyy-mm-dd HH:mm:ss"/>
            <div class="mb-explain"><?php _e('Leave blank to automatically calculate based on the selected group\'s default duration (or 30 days if none). Format: YYYY-MM-DD HH:MM:SS', 'osclass_pay'); ?></div> <?php // Updated explanation ?>
          </div>
        </div>
      </form>
    </div>
  </div>



  <!-- SHOW USERS IN GROUP -->
  <div class="mb-box mb-user-in-group">
    <div class="mb-head"><i class="fa fa-list-ul"></i> <?php _e('View Group Members', 'osclass_pay'); ?></div> <?php // Changed icon and title ?>

    <div class="mb-inside">
      <form name="list_user_form" id="list_user_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" > <?php // Changed form name/id ?>
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>user.php" />
        <input type="hidden" name="go_to_file" value="_group.php" />
        <input type="hidden" name="plugin_action" value="list_users" />

        <div class="mb-row">
          <label for="list_group"><span><?php _e('Select Group', 'osclass_pay'); ?></span></label>
          <select id="list_group" name="list_group">
            <option value=""><?php _e('-- Select a Group --', 'osclass_pay'); ?></option> <?php // Changed default text ?>

            <?php foreach(ModelOSP::newInstance()->getGroups() as $g) { // Assumes getGroups fetches s_type ?>
               <?php
                  $group_name_display = osc_esc_html($g['s_name']);
                  if (isset($g['s_type']) && $g['s_type'] != 'standard') {
                      $group_name_display .= ' (' . ucfirst(osc_esc_html($g['s_type'])) . ')';
                  }
                ?>
              <option value="<?php echo $g['pk_i_id']; ?>" <?php echo ($g['pk_i_id'] == Params::getParam('list_group') ? 'selected="selected"' : ''); ?>><?php echo $group_name_display; ?></option>
            <?php } ?>
          </select>
          <button type="submit" class="mb-button-green" style="margin-left:10px;"><i class="fa fa-search"></i> <?php _e('List Users', 'osclass_pay');?></button> <?php // Changed icon ?>

          <div class="mb-explain"><?php _e('Select a group to see a list of its current members.', 'osclass_pay'); ?></div> <?php // Updated explanation ?>
        </div>

        <div class="mb-row"> </div>


        <?php if(Params::getParam('list_group') <> '' && Params::getParam('list_group') > 0) { ?>
          <?php $list_users = ModelOSP::newInstance()->getUsersByGroup(Params::getParam('list_group')); // Assumes this fetches user_id, user_name, user_email, group_name, group_color, expire, and potentially group_type ?>
          <div class="mb-table mb-table-group-list">
            <div class="mb-table-head">
              <div class="mb-col-1"><?php _e('ID', 'osclass_pay'); ?></div>
              <div class="mb-col-5 mb-align-left"><?php _e('User Name', 'osclass_pay'); ?></div>
              <div class="mb-col-8 mb-align-left"><?php _e('Email', 'osclass_pay'); ?></div>
              <div class="mb-col-4 mb-align-left"><?php _e('Group', 'osclass_pay'); ?></div>
              <div class="mb-col-5"><?php _e('Expires On', 'osclass_pay'); ?></div> <?php // Changed label ?>
              <div class="mb-col-1"> </div>
            </div>

            <?php if(count($list_users) <= 0) { ?>
              <div class="mb-table-row mb-row-empty">
                <i class="fa fa-info-circle"></i> <span><?php _e('No users found in the selected group.', 'osclass_pay'); ?></span> <?php // Changed icon and text ?>
              </div>
            <?php } else { ?>
              <?php foreach($list_users as $u) { ?>
                <div class="mb-table-row">
                  <div class="mb-col-1"><?php echo $u['user_id']; ?></div>
                  <div class="mb-col-5 mb-align-left"><?php echo osc_esc_html($u['user_name']); ?></div>
                  <div class="mb-col-8 mb-align-left"><?php echo osc_esc_html($u['user_email']); ?></div>
                  <div class="mb-col-4 mb-align-left">
                     <?php
                       $group_name_display = osc_esc_html($u['group_name']);
                       // Add type indicator if available and not 'standard'
                       if (isset($u['group_type']) && $u['group_type'] != 'standard') {
                           $group_name_display .= ' <span style="font-weight:normal; font-size:0.9em;">(' . ucfirst(osc_esc_html($u['group_type'])) . ')</span>';
                       }
                     ?>
                    <div class="mb-group-label-short" style="background:<?php echo osc_esc_html($u['group_color']); ?>;color:<?php echo osp_text_color($u['group_color']); ?>"><?php echo $group_name_display; ?></div>
                  </div>
                  <div class="mb-col-5"><?php echo ($u['expire'] ? $u['expire'] : __('Never', 'osclass_pay')); ?></div> <?php // Handle potentially null/empty expire dates ?>
                  <div class="mb-col-1 mb-del-col"><a href="<?php echo osp_admin_plugin_url('user.php'); ?>&go_to_file=_group.php&what=user_remove&user_id=<?php echo $u['user_id']; ?>" onclick="return confirm('<?php echo osc_esc_js(sprintf(__('Are you sure you want to remove %s from this group?', 'osclass_pay'), osc_esc_html($u['user_name']))); ?>')" class="mb-group-remove" title="<?php echo osc_esc_html(__('Remove user from group', 'osclass_pay')); ?>"><i class="fa fa-times-circle"></i></a></div> <?php // Changed icon and added user name to confirm message ?>
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
      <div class="mb-row mb-help"><span class="sup">(1)</span> <div class="h1"><?php _e('Enable user groups to offer memberships with different privileges.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(2)</span> <div class="h2"><?php _e('When enabled, categories listed in a group\'s "Restricted Categories" setting (under "More details") will only be visible to members of that specific group. Non-members (including admins previewing) will not see the category or its listings. Listings can still be posted to these categories by allowed members.', 'osclass_pay'); ?></div></div> <?php // Updated help ?>
      <div class="mb-row mb-help"><span class="sup">(3)</span> <div class="h3"><?php _e('Automatically assign newly registered users to the selected group.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(4)</span> <div class="h4"><?php _e('Enable limits on the number of listings users can publish within a specific period (defined below). Group memberships can override the default limit.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(5)</span> <div class="h5"><?php _e('Define the default maximum number of listings and the time period (days) for users not belonging to any group or for logged-out users (if guest posting is allowed).', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(6)</span> <div class="h6"><?php _e('Choose how listings are counted towards the limit (all vs. active, including/excluding premium). "Count active items" is generally recommended.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><div><?php _e('**Group Type:** Use the "More details" section under "Manage User Groups" to set a type (e.g., Standard, VIP) for each group.', 'osclass_pay'); ?></div></div> <?php // Updated help text ?>
    </div>
  </div>
</div>


<script type="text/javascript">
  // Ensure these variables are still needed by the original plugin JS
  var group_lookup_error = "<?php echo osc_esc_js(__('Error getting data, user not found', 'osclass_pay')); ?>";
  var group_lookup_url = "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=runhook&hook=osp_group_data&id=";
  var group_lookup_base = "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=userajax";

  // No additional JS needed here if the plugin's original JS handles cloning and events correctly.
  // Verify the original plugin JS correctly clones the placeholder including the new <select name="group_-999_type">
  // and updates its name attribute when adding a new line.
</script>

<?php echo osp_footer(); ?>