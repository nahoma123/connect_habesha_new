<?php
  // Create menu
  //$title = __('Pay per Premium', 'osclass_pay');
  //osp_menu($title);

  // GET & UPDATE PARAMETERS
  // $variable = osp_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value or value_crypt


  $premium_allow = osp_param_update( 'premium_allow', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $premium_fee = osp_param_update( 'premium_fee', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $premium_duration = osp_param_update( 'premium_duration', 'plugin_action', 'value', 'plugin-osclass_pay' );


  // Make sure at least 1 duration exists
  if($premium_duration == '') {
    Params::setParam('premium_duration', 24);
    $premium_duration = 24;
    osc_set_preference('premium_duration', '24', 'plugin-osclass_pay', 'STRING');
    message_info( __('You have not selected any duration, this is not possible. Default duration per 24 hours has been used.', 'osclass_pay') );
  }


  $avl_duration = explode(',', osp_available_duration());
  $duration_array = explode(',', $premium_duration);




  // UPDATE CATEGORY PRICES
  if(Params::getParam('plugin_action') == 'category') {
    $params = Params::getParamsAsArray();

    foreach(array_keys($params) as $p) {
      $detail = explode('_', $p);
     
      if($detail[0] == 'fee') {
        // detail[1] - type of payment
        // detail[2] - category
        // detail[3] - duration

        ModelOSP::newInstance()->updateCategoryFee($detail[1], $detail[2], $params[$p], $detail[3]);
      }
    }

    message_ok( __('Fees for categories were successfully saved', 'osclass_pay') );
  }



  // UPDATE LOCATION UPLIFTS
  if(Params::getParam('plugin_action') == 'location') {
    $problem = false;
    $params = Params::getParamsAsArray();

    foreach(array_keys($params) as $p) {
      $detail = explode('_', $p);
     
      if($detail[0] == 'uplift') {
        // detail[1] - type of payment
        // detail[2] - country
        // detail[3] - region

        if($params[$p] <= 300 && $params[$p] >= -90) {
          ModelOSP::newInstance()->updateLocationFee($detail[1], $detail[2], $detail[3], $params[$p]);
        } else {
          $problem = true;
        }
      }
    }

    message_ok( __('Fees for locations were successfully saved', 'osclass_pay') );

    if($problem) {
      message_error( __('Some uplifts were not updated due to their format or range. Note that only integer values between -90 and 300 are accepted.', 'osclass_pay') );
    }
  }



  if(Params::getParam('plugin_action') == 'done') {
    message_ok( __('Settings were successfully saved', 'osclass_pay') );
  }


  $categories = osp_category_list();
  $locations = osp_location_list();


  // SCROLL TO DIV
  if(Params::getParam('position') == '1') {
    osp_js_scroll('.mb-setting');
  } else if(Params::getParam('position') == '2') {
    osp_js_scroll('.mb-catprice');
  } else if(Params::getParam('position') == '3') {
    osp_js_scroll('.mb-locprice');
  }
?>



<div class="mb-body">

  <!-- DEFAULT PARAMETERS -->
  <div class="mb-box mb-setting">
    <div class="mb-head">
      <i class="fa fa-cog"></i> <?php _e('Settings', 'osclass_pay'); ?>

      <?php $runs = osp_get_cron_runs(); ?>
      <span class="mb-runs mb-has-tooltip" title="<?php echo osc_esc_html($runs[1]); ?>"><?php echo $runs[0]; ?></span>
    </div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>item.php" />
        <input type="hidden" name="go_to_file" value="_premium.php" />
        <input type="hidden" name="plugin_action" value="done" />
        <input type="hidden" name="position" value="1" />


        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('Make Premium functionality allows to mark listings as premium.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('Based on theme, premium listing may be shown in special block where only premium listings are shown, has special more attractive background color or label that identifies listing as premium.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="premium_allow" class="h1"><span><?php _e('Enable Pay per Premium', 'osclass_pay'); ?></span></label> 
          <input name="premium_allow" id="premium_allow" class="element-slide" type="checkbox" <?php echo ($premium_allow == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, users can pay to mark their listings as premium.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="premium_fee" class="h2"><span><?php _e('Default fee per 24 hours', 'osclass_pay'); ?></span></label> 
          <input size="10" name="premium_fee" id="premium_fee" class="mb-short" type="text" style="text-align:right;" value="<?php echo number_format((float)$premium_fee, 1, '.', ''); ?>" />
          <div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div>

          <div class="mb-explain"><?php _e('Define default fee per 24 hours of duration. Price for other durations is recalculated via formula.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row mb-row-select-multiple">
          <label for="duration_multiple" class="h3"><span><?php _e('Duration of premiums', 'osclass_pay'); ?></span></label> 

          <input type="hidden" name="premium_duration" id="premium_duration" value="<?php echo $premium_duration; ?>"/>
          <select id="duration_multiple" name="duration_multiple" multiple>
            <?php foreach($avl_duration as $a) { ?>
              <option value="<?php echo $a; ?>" <?php if(in_array($a, $duration_array)) { ?>selected="selected"<?php } ?>><?php echo osp_duration_name($a); ?></option>
            <?php } ?>
          </select>

          <div class="mb-explain"><?php _e('Select at least 1 duration to make Pay per Premium functional (Press CTRL to select multiple).', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
        </div>
      </form>
    </div>
  </div>



  <!-- CATEGORY SECTION -->
  <div class="mb-box mb-catprice">
    <div class="mb-head"><i class="fa fa-list"></i> <?php _e('Category Prices', 'osclass_pay'); ?></div>

    <div class="mb-inside">

      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('If you do not want to set different prices per category, skip this section. Prices will be then calculated based on default price.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('If price on parent category is changed, this change is applied to all subcategories of this category.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('If same price as default price is set, price is cleared (default is used). If price field is left blank, default price is used.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('Do not modify too many categories at once.', 'osclass_pay'); ?></div>
      </div>

      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('Set price to 0 (zero) to disable selection. Set price to blank (empty) to use default price.', 'osclass_pay'); ?></div>
      </div>


      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>item.php" />
        <input type="hidden" name="go_to_file" value="_premium.php" />
        <input type="hidden" name="plugin_action" value="category" />
        <input type="hidden" name="position" value="2" />

        <a href="#" class="mb-button-green mb-category-difference"><?php _e('Show only prices different to default', 'osclass_pay'); ?></a>
        <a href="#" class="mb-button-white mb-category-all"><?php _e('Show all prices', 'osclass_pay'); ?></a>
        <a href="#" class="mb-button-white mb-category-clear"><?php _e('Clear prices', 'osclass_pay'); ?></a>

        <?php
          $cols = count($duration_array);
          if($cols < 1) { 
            $cols = 1;
          }

          if(18/$cols >= 3) {
            $cols = 3;
          } else {
            $cols = 2;
          }
        ?>

        <div class="mb-table mb-table-category">
          <div class="mb-table-head">
            <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
            <div class="mb-col-5 mb-align-left"><?php _e('Category', 'osclass_pay'); ?></div>

            <?php foreach($duration_array as $hours) { ?>
              <div class="mb-col-<?php echo $cols; ?> mb-align-center"><?php echo osp_duration_name($hours); ?></div>
            <?php } ?>
          </div>


          <?php if(count($categories) <= 0) { ?>
            <div class="mb-table-row mb-row-empty">
              <i class="fa fa-warning"></i><span><?php _e('No categories has been added yet', 'osclass_pay'); ?></span>
            </div>
          <?php } else { ?>
            <?php foreach($categories as $c) { ?>
              <div class="mb-table-row" data-level="<?php echo $c['level']; ?>" data-category-id="<?php echo $c['pk_i_id']; ?>" data-parent-id="<?php echo $c['fk_i_parent_id']; ?>">
                <div class="mb-col-1"><?php echo $c['pk_i_id']; ?></div>
                <div class="mb-col-5 mb-align-left mb-category-name">
                  <?php if( $c['level'] == 1 ) { ?>
                    <strong title="<?php _e('Main category', 'osclass_pay');?>"><?php echo $c['s_name']; ?></strong>
                  <?php } else { ?>
                    <?php echo osp_category_tabs($c['level']); ?><?php echo $c['s_name']; ?>
                  <?php } ?>
                </div>

                <?php foreach($duration_array as $hours) { ?>
                  <?php 
                    $fee = ModelOSP::newInstance()->getCategoryFee(OSP_TYPE_PREMIUM, $c['pk_i_id'], $hours );

                    if($fee == 0 && $fee <> '') {
                      $class2 = 'mb-input-dsbl';
                      $title2 = osc_esc_html(__('This option is disabled', 'osclass_pay')) . '.<br/>';
                    } else {
                      $class2 = '';
                      $title2 = '';
                    }

                    if($fee <> osp_hours_uplift($premium_fee, $hours) && !($fee == 0 && $fee <> '')) { 
                      $class = 'mb-input-bold'; 
                    } else {
                      if(!($fee == 0 && $fee <> '')) {
                        $title2 = osc_esc_html(__('Default price is used', 'osclass_pay')) . '.<br/>';
                      }
                      $class = ''; 
                    } 
                  ?>

                  <div class="mb-col-<?php echo $cols; ?> mb-category-price mb-has-tooltip-light <?php echo $class2; ?>" data-hours="<?php echo $hours; ?>" title="<?php echo $title2 . osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                    <input class="<?php echo $class; ?>" type="text" disabled="disabled" id="category-fee" name="fee_<?php echo OSP_TYPE_PREMIUM; ?>_<?php echo $c['pk_i_id']; ?>_<?php echo $hours; ?>" value="<?php echo number_format((float)$fee, 1, '.', ''); ?>" /><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div>
                  </div>
                <?php } ?>
              </div>
            <?php } ?>
          <?php } ?>
        </div>

        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
        </div>
      </form>
    </div>
  </div>



  <!-- LOCATION SECTION -->
  <div class="mb-box mb-locprice">
    <div class="mb-head"><i class="fa fa-map-signs"></i> <?php _e('Location Prices', 'osclass_pay'); ?></div>

    <div class="mb-inside">

      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('You can set there uplift of price per country or region in range -90% to +300%. If you set +100%, fee in selected location will be doubled.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('Note that to get final fee amount, there are 3 variables: category, location, user membership.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('Leave blank or set to 0 to disable.', 'osclass_pay'); ?></div>
       </div>

      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>item.php" />
        <input type="hidden" name="go_to_file" value="_premium.php" />
        <input type="hidden" name="plugin_action" value="location" />
        <input type="hidden" name="position" value="3" />

        <a href="#" class="mb-button-green mb-location-difference"><?php _e('Show only prices different to default', 'osclass_pay'); ?></a>
        <a href="#" class="mb-button-white mb-location-all"><?php _e('Show all prices', 'osclass_pay'); ?></a>
        <a href="#" class="mb-button-white mb-location-clear"><?php _e('Clear prices', 'osclass_pay'); ?></a>

        <div class="mb-table mb-table-location">
          <div class="mb-table-head">
            <div class="mb-col-2"><?php _e('ID', 'osclass_pay');?></div>
            <div class="mb-col-5 mb-align-left"><?php _e('Location', 'osclass_pay'); ?></div>
            <div class="mb-col-5 mb-align-center"><?php _e('Uplift percentage', 'osclass_pay'); ?></div>
            <div class="mb-col-12 mb-align-left">&nbsp;</div>
          </div>

          <?php if(count($locations) <= 0) { ?>
            <div class="mb-table-row mb-row-empty">
              <i class="fa fa-warning"></i><span><?php _e('No locations has been added yet', 'osclass_pay'); ?></span>
            </div>
          <?php } else { ?>
            <?php foreach($locations as $l) { ?>
              <div class="mb-table-row" data-level="<?php echo $l['level']; ?>" data-country-code="<?php echo $l['country_code']; ?>" data-region-id="<?php echo $l['region_id']; ?>">
                <div class="mb-col-2">
                  <?php if( $l['level'] == 1 ) { ?>
                    <?php echo $l['country_code']; ?>
                  <?php } else { ?>
                    <?php echo $l['region_id']; ?>
                  <?php } ?>
                </div>

                <div class="mb-col-5 mb-align-left mb-location-name">
                  <?php if( $l['level'] == 1 ) { ?>
                    <strong><?php echo $l['country_name']; ?></strong>
                  <?php } else { ?>
                    <i class="fa fa-angle-right"></i> <?php echo $l['region_name']; ?> (<?php echo $l['country_code']; ?>)
                  <?php } ?>
                </div>


                <?php 
                  $uplift = ModelOSP::newInstance()->getLocationFee(OSP_TYPE_PREMIUM, $l['country_code'], $l['region_id'] );

                  if($uplift <> '' && $uplift <> 0) { 
                    $class = 'mb-input-bold'; 
                  } else { 
                    $class = ''; 
                  } 
                ?>

                <div class="mb-col-5 mb-location-uplift mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                  <input class="<?php echo $class; ?>" type="text" disabled="disabled" id="location-uplift" name="uplift_<?php echo OSP_TYPE_PREMIUM; ?>_<?php echo $l['country_code']; ?>_<?php echo $l['region_id']; ?>" value="<?php echo $uplift; ?>" /><div class="mb-input-desc">%</div>
                </div>

                <div class="mb-col-12 mb-location-info mb-align-left"><?php _e('Enter integer values only! It can be negative or possitive in range from -90 to 300.', 'osclass_pay'); ?></div>
              </div>
            <?php } ?>
          <?php } ?>
        </div>

        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
        </div>
      </form>
    </div>
  </div>




  <!-- HELP TOPICS -->
  <div class="mb-box" id="mb-help">
    <div class="mb-head"><i class="fa fa-question-circle"></i> <?php _e('Help', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-help"><span class="sup">(1)</span> <div class="h1"><?php echo sprintf(__('When enabled, users can %s their listings.', 'osclass_pay'), __('make premium', 'osclass_pay')); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(2)</span> <div class="h2"><?php _e('Set default fee per 24 hours. This fee will be used to calculate fee for other durations.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(3)</span> <div class="h3"><?php echo sprintf(__('Select available durations of %s item. You can define different prices for different durations, i.e. price per 24 hours set to $1.0, but price for 48 hours set to $1.5. Use CTRL to select more than one duration.', 'osclass_pay'),  __('premium', 'osclass_pay')); ?></div></div>
    </div>
  </div>
</div>

<?php echo osp_footer(); ?>