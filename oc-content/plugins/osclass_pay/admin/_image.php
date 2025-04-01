<?php
  // Create menu
  //$title = __('Pay per Image', 'osclass_pay');
  //osp_menu($title);

  // GET & UPDATE PARAMETERS
  // $variable = osp_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value or value_crypt


  $image_allow = osp_param_update( 'image_allow', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $image_fee = osp_param_update( 'image_fee', 'plugin_action', 'value', 'plugin-osclass_pay' );



  // UPDATE CATEGORY PRICES
  if(Params::getParam('plugin_action') == 'category') {
    $params = Params::getParamsAsArray();

    foreach(array_keys($params) as $p) {
      $detail = explode('_', $p);
     
      if($detail[0] == 'fee') {
        // detail[1] - type of payment
        // detail[2] - category

        ModelOSP::newInstance()->updateCategoryFee($detail[1], $detail[2], $params[$p]);
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
    <div class="mb-head"><i class="fa fa-cog"></i> <?php _e('Settings', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>item.php" />
        <input type="hidden" name="go_to_file" value="_image.php" />
        <input type="hidden" name="plugin_action" value="done" />
        <input type="hidden" name="position" value="1" />


        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('Pay to Show Images functionality enables to charge your users for showing pictures on listings.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('When enabled, users are able to upload pictures to listings, however in order to show pictures it is required to pay fee.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('When fee is not paid, in front listing behave like without pictures.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('Enabling functionality does not affect existing listings, however it is possible from oc-admin > items to require to pay image fee additionally.', 'osclass_pay'); ?></div>

        </div>


        <div class="mb-row">
          <label for="image_allow" class="h1"><span><?php _e('Enable Pay per Show Image', 'osclass_pay'); ?></span></label> 
          <input name="image_allow" id="image_allow" class="element-slide" type="checkbox" <?php echo ($image_allow == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, users must pay to show images on their listings.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="image_fee" class="h2"><span><?php _e('Default fee', 'osclass_pay'); ?></span></label> 
          <input size="10" name="image_fee" id="image_fee" class="mb-short" type="text" style="text-align:right;" value="<?php echo number_format((float)$image_fee, 1, '.', ''); ?>" />
          <div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div>

          <div class="mb-explain"><?php _e('Define default show image fee.', 'osclass_pay'); ?></div>
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
        <input type="hidden" name="go_to_file" value="_image.php" />
        <input type="hidden" name="plugin_action" value="category" />
        <input type="hidden" name="position" value="2" />

        <a href="#" class="mb-button-green mb-category-difference"><?php _e('Show only prices different to default', 'osclass_pay'); ?></a>
        <a href="#" class="mb-button-white mb-category-all"><?php _e('Show all prices', 'osclass_pay'); ?></a>
        <a href="#" class="mb-button-white mb-category-clear"><?php _e('Clear prices', 'osclass_pay'); ?></a>

        <div class="mb-table mb-table-category">
          <div class="mb-table-head">
            <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
            <div class="mb-col-5 mb-align-left"><?php _e('Category', 'osclass_pay'); ?></div>
            <div class="mb-col-3 mb-align-center"><?php _e('Fee', 'osclass_pay'); ?></div>
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

                <?php 
                  $fee = ModelOSP::newInstance()->getCategoryFee(OSP_TYPE_IMAGE, $c['pk_i_id']);

                  if($fee == 0 && $fee <> '') {
                    $class2 = 'mb-input-dsbl';
                    $title2 = osc_esc_html(__('This option is disabled', 'osclass_pay')) . '.<br/>';
                  } else {
                    $class2 = '';
                    $title2 = '';
                  }


                  if($fee <> $image_fee && !($fee == 0 && $fee <> '')) { 
                    $class = 'mb-input-bold'; 
                  } else { 
                    if(!($fee == 0 && $fee <> '')) {
                      $title2 = osc_esc_html(__('Default price is used', 'osclass_pay')) . '.<br/>';
                    }
                    $class = ''; 
                  } 
                ?>

                <div class="mb-col-3 mb-category-price mb-has-tooltip-light <?php echo $class2; ?>" data-hours="0" title="<?php echo $title2 . osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                  <input class="<?php echo $class; ?>" type="text" disabled="disabled" id="category-fee" name="fee_<?php echo OSP_TYPE_IMAGE; ?>_<?php echo $c['pk_i_id']; ?>" value="<?php echo number_format((float)$fee, 1, '.', ''); ?>" /><div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div>
                </div>

                <div class="mb-col-15 mb-category-info mb-align-left"><?php _e('Setting price to 0 (zero) will remove unpaid image fee payment requests (pictures will be visible) in selected categories.', 'osclass_pay'); ?></div>
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
        <input type="hidden" name="go_to_file" value="_image.php" />
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
                  $uplift = ModelOSP::newInstance()->getLocationFee(OSP_TYPE_IMAGE, $l['country_code'], $l['region_id'] );

                  if($uplift <> '' && $uplift <> 0) { 
                    $class = 'mb-input-bold'; 
                  } else { 
                    $class = ''; 
                  } 
                ?>

                <div class="mb-col-5 mb-location-uplift mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                  <input class="<?php echo $class; ?>" type="text" disabled="disabled" id="location-uplift" name="uplift_<?php echo OSP_TYPE_IMAGE; ?>_<?php echo $l['country_code']; ?>_<?php echo $l['region_id']; ?>" value="<?php echo $uplift; ?>" /><div class="mb-input-desc">%</div>
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


  <!-- PLUGIN INTEGRATION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-wrench"></i> <?php _e('Plugin Setup', 'osclass_pay'); ?></div>

    <div class="mb-inside">

      <div class="mb-row">
        <div class="mb-line"><?php echo sprintf(__('For "Highlight" and "Show image" features please make sure your theme contains hook %s. Some theme like bender already contains it, but some of them may not.', 'osclass_pay'), '<strong>highlight_class</strong>'); ?></div>
        <div class="mb-line"><?php _e('If Highligh / Show image does not work for you, please go to your theme folder.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('If your theme has files loop-single.php and loop-single-premium.php, continue with section 1, otherwise go to section 2.', 'osclass_pay'); ?></div>

        <div class="mb-line" style="margin-top:20px;"><strong><?php _e('Section 1', 'osclass_pay'); ?></strong></div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php echo sprintf(__('Open files loop-single.php and loop-single-premium.php  and check if contains string %s', 'osclass_pay'), '&lt;?php osc_run_hook("highlight_class"); ?&gt'); ?></div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php _e('If you can see this code here, everything is setup correctly and no action is required. If not, continue with next step.', 'osclass_pay'); ?></div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php _e('Locate first html element in this file. It could be', 'osclass_pay'); ?> &lt;div, &lt;p, &lt;li, &lt;span, &lt;tr</div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php _e('If it contains class element, before second apostrophe insert following code:', 'osclass_pay'); ?> &lt;?php osc_run_hook("highlight_class"); ?&gt</div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php _e('After that class element will looks like:', 'osclass_pay'); ?> class="xxxx xxxx &lt;?php osc_run_hook("highlight_class"); ?&gt"</div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php _e('If it does not contain class element and let\'s say it is div element, before first > sign insert following code:', 'osclass_pay'); ?> class="&lt;?php osc_run_hook("highlight_class"); ?&gt"</div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php _e('So element will look like (div, p, li, ...) following:', 'osclass_pay'); ?> &lt;div class="&lt;?php osc_run_hook("highlight_class"); ?&gt"&gt;</div>

        <div class="mb-line" style="margin-top:20px;"><strong><?php _e('Section 2', 'osclass_pay'); ?></strong></div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php echo sprintf(__('Open files search_list.php and search_gallery.php and check if contains string %s', 'osclass_pay'), '&lt;?php osc_run_hook("highlight_class"); ?&gt'); ?></div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php _e('If you can see this code here, everything is setup correctly and no action is required. If not, continue with next step.', 'osclass_pay'); ?></div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php echo sprintf(__('Locate first html element in this file right after line that contains %s. It could be', 'osclass_pay'), 'while(osc_has_items()) {'); ?> &lt;div, &lt;p, &lt;li, &lt;span, &lt;tr</div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php _e('If it contains class element, before second apostrophe insert following code:', 'osclass_pay'); ?> &lt;?php osc_run_hook("highlight_class"); ?&gt</div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php _e('After that class element will looks like:', 'osclass_pay'); ?> class="xxxx xxxx &lt;?php osc_run_hook("highlight_class"); ?&gt"</div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php _e('If it does not contain class element and let\'s say it is div element, before first > sign insert following code:', 'osclass_pay'); ?> class="&lt;?php osc_run_hook("highlight_class"); ?&gt"</div>
        <div class="mb-line"><i class="fa fa-caret-right mb-dl"></i> <?php _e('So element will look like (div, p, li, ...) following:', 'osclass_pay'); ?> &lt;div class="&lt;?php osc_run_hook("highlight_class"); ?&gt"&gt;</div>

      </div>
    </div>
  </div>


  <!-- HELP TOPICS -->
  <div class="mb-box" id="mb-help">
    <div class="mb-head"><i class="fa fa-question-circle"></i> <?php _e('Help', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-help"><span class="sup">(1)</span> <div class="h1"><?php _e('When enabled, users must pay image fee in order to show images on their listings.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(2)</span> <div class="h2"><?php _e('Select default fee for showing images on listings.', 'osclass_pay'); ?></div></div>
    </div>
  </div>
</div>

<?php echo osp_footer(); ?>