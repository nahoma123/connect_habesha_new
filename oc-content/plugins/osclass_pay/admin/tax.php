<?php
  // Create menu
  $title = __('Tax Settings', 'osclass_pay');
  osp_menu($title);

  // GET & UPDATE PARAMETERS
  // $variable = osp_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value or value_crypt


  //$premium_allow = osp_param_update( 'premium_allow', 'plugin_action', 'check', 'plugin-osclass_pay' );





  // UPDATE CATEGORY PRICES
  if(Params::getParam('plugin_action') == 'tax') {
    $params = Params::getParamsAsArray();

    foreach(array_keys($params) as $p) {
      $detail = explode('_', $p);
     
      if($detail[0] == 'tax') {
        // detail[1] - region id
        // detail[2] - type (id1,2,3; name1,2,3; val1,2,3)
        // $params[$p] - value

        ModelOSP::newInstance()->updateTax($detail[1], $detail[2], $params[$p]);
      }
    }

    message_ok( __('Tax for regions were successfully saved', 'osclass_pay') );
  }



  if(Params::getParam('plugin_action') == 'done') {
    message_ok( __('Settings were successfully saved', 'osclass_pay') );
  }



  $taxes = ModelOSP::newInstance()->getTaxes();
  $tax_default = ModelOSP::newInstance()->getTax(-1);
  //$tax_default['fk_i_region_id'] = -1;

  array_unshift($taxes, $tax_default);

?>



<div class="mb-body">


  <!-- CATEGORY SECTION -->
  <div class="mb-box mb-tax">
    <div class="mb-head"><i class="fa fa-list"></i> <?php _e('Tax Setup', 'osclass_pay'); ?></div>

    <div class="mb-inside">

      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('Set tax value between 0 and 100.', 'osclass_pay'); ?></div>
      </div>


      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>tax.php" />
        <input type="hidden" name="plugin_action" value="tax" />
        <input type="hidden" name="position" value="1" />


        <div class="mb-table mb-table-tax">
          <div class="mb-table-head">
            <div class="mb-col-1 mb-align-left"><?php _e('Country', 'osclass_pay');?></div>
            <div class="mb-col-2 mb-align-left"><?php _e('Region', 'osclass_pay');?></div>

            <div class="mb-col-2"><?php _e('Tax ID #1', 'osclass_pay');?></div>
            <div class="mb-col-2"><?php _e('Tax ID #2', 'osclass_pay');?></div>
            <div class="mb-col-2"><?php _e('Tax ID #3', 'osclass_pay');?></div>

            <div class="mb-col-2"><?php _e('Tax Name #1', 'osclass_pay');?></div>
            <div class="mb-col-2"><?php _e('Tax Name #2', 'osclass_pay');?></div>
            <div class="mb-col-2"><?php _e('Tax Name #3', 'osclass_pay');?></div>

            <div class="mb-col-3"><?php _e('Tax Value #1', 'osclass_pay');?></div>
            <div class="mb-col-3"><?php _e('Tax Value #2', 'osclass_pay');?></div>
            <div class="mb-col-3"><?php _e('Tax Value #3', 'osclass_pay');?></div>

          </div>


          <?php foreach($taxes as $t) { ?>
            <div class="mb-table-row">
              <div class="mb-col-1 mb-align-left"><?php echo strtoupper($t['s_country_code'] <> '' ? $t['s_country_code'] : '-'); ?></div>
              <div class="mb-col-2 mb-align-left"><?php echo ($t['s_region'] <> '' ? $t['s_region'] : __('Default', 'osclass_pay')); ?></div>

              <div class="mb-col-2 mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                <div class="cvr"></div>
                <input type="text" disabled="disabled" name="tax_<?php echo $t['fk_i_region_id']; ?>_id1" value="<?php echo $t['s_tax_id1']; ?>" />
              </div>

              <div class="mb-col-2 mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                <div class="cvr"></div>

                <input type="text" disabled="disabled" name="tax_<?php echo $t['fk_i_region_id']; ?>_id2" value="<?php echo $t['s_tax_id2']; ?>" />
              </div>

              <div class="mb-col-2 mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                <div class="cvr"></div>

                <input type="text" disabled="disabled" name="tax_<?php echo $t['fk_i_region_id']; ?>_id3" value="<?php echo $t['s_tax_id3']; ?>" />
              </div>


              <div class="mb-col-2 mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                <div class="cvr"></div>

                <input type="text" disabled="disabled" name="tax_<?php echo $t['fk_i_region_id']; ?>_name1" value="<?php echo $t['s_tax_name1']; ?>" />
              </div>

              <div class="mb-col-2 mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                <div class="cvr"></div>

                <input type="text" disabled="disabled" name="tax_<?php echo $t['fk_i_region_id']; ?>_name2" value="<?php echo $t['s_tax_name2']; ?>" />
              </div>

              <div class="mb-col-2 mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                <div class="cvr"></div>

                <input type="text" disabled="disabled" name="tax_<?php echo $t['fk_i_region_id']; ?>_name3" value="<?php echo $t['s_tax_name3']; ?>" />
              </div>


              <div class="mb-col-3 mb-t1 mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                <div class="cvr"></div>

                <input type="number" min="0" max="100" disabled="disabled" step="0.0000001" name="tax_<?php echo $t['fk_i_region_id']; ?>_val1" value="<?php echo number_format((float)$t['s_tax_val1'], 7, '.', ''); ?>" /><div class="mb-input-desc">%</div>
              </div>

              <div class="mb-col-3 mb-t1 mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                <div class="cvr"></div>

                <input type="number" min="0" max="100" step="0.0000001" disabled="disabled" name="tax_<?php echo $t['fk_i_region_id']; ?>_val2" value="<?php echo number_format((float)$t['s_tax_val2'], 7, '.', ''); ?>" /><div class="mb-input-desc">%</div>
              </div>

              <div class="mb-col-3 mb-t1 mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to unlock field', 'osclass_pay')); ?>">
                <div class="cvr"></div>

                <input type="number" min="0" max="100" step="0.0000001" disabled="disabled" name="tax_<?php echo $t['fk_i_region_id']; ?>_val3" value="<?php echo number_format((float)$t['s_tax_val3'], 7, '.', ''); ?>" /><div class="mb-input-desc">%</div>
              </div>
            </div>
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