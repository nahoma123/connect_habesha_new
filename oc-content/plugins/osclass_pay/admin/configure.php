<?php
  // Create menu 
  $title = __('Configure', 'osclass_pay');
  osp_menu($title);

  // GET & UPDATE PARAMETERS
  // $variable = osp_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value or value_crypt


  $currency = osp_param_update( 'currency', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $price_decimals = osp_param_update( 'price_decimals', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $price_position = osp_param_update( 'price_position', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $price_space = osp_param_update( 'price_space', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $price_decimal_symbol = osp_param_update( 'price_decimal_symbol', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $price_thousand_symbol = osp_param_update( 'price_thousand_symbol', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $links_sidebar = osp_param_update( 'links_sidebar', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $horizontal_menu = osp_param_update( 'horizontal_menu', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $def_price_calc = osp_param_update( 'def_price_calc', 'plugin_action', 'value', 'plugin-osclass_pay' );


  if(Params::getParam('plugin_action') == 'done') {
    osp_get_currency_rates(); // update currency rates
    message_ok( __('Settings were successfully saved', 'osclass_pay') );
  }

  $currencies = osp_available_currencies(true);
  $symbols = osp_available_currencies();


  // SCROLL TO DIV
  if(Params::getParam('position') == '1') {
    osp_js_scroll('.mb-conf');
  }

?>



<div class="mb-body">

  <!-- CONFIGURE SECTION -->
  <div class="mb-box mb-conf">
    <div class="mb-head"><i class="fa fa-cog"></i> <?php _e('Configure', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>configure.php" />
        <input type="hidden" name="plugin_action" value="done" />
        <input type="hidden" name="position" value="1" />

        <?php 
          $check_file1 = osc_plugins_path() . 'osclass_pay/payments/paypl/notify_url.php'; 
          $check_file2 = osc_plugins_path() . 'osclass_pay/payments/instamojo/return.php'; 
          $check_file3 = osc_plugins_path() . 'osclass_pay/payments/skrill/status.php'; 
        ?>
        <?php if(!is_readable($check_file1) || !is_readable($check_file2) || !is_readable($check_file3) || osp_get_chmod($check_file1) < 555 || osp_get_chmod($check_file2) < 555 || osp_get_chmod($check_file3) < 555) { ?>
          <div class="mb-row mb-errors">
            <div class="mb-line"><?php echo sprintf(__('Some files in folder %s are not readable!', 'osclass_pay'), 'oc-content/plugins/osclass_pay/payments'); ?></div>
            <div class="mb-line"><?php _e('In order to make plugin fully functional, all the plugin files must be readable. Please contact your hosting provider for help.', 'osclass_pay'); ?></div>
            <div class="mb-line"><?php _e('Make sure correct CHMOD settings are set on all plugin files.', 'osclass_pay'); ?></div>
          </div>
        <?php } ?>

        <div class="mb-row mb-row-select">
          <label for="currency" class="h1"><span><?php _e('Currency', 'osclass_pay'); ?></span></label> 

          <select id="currency" name="currency">
            <?php foreach($currencies as $c) { ?>

              <option value="<?php echo $c; ?>" <?php if($c == osp_currency()) { ?>selected="selected"<?php } ?>><?php echo $c . ' (' . $symbols[$c] . ')'; ?></option>
            <?php } ?>
          </select>

          <div class="mb-explain"><?php _e('Select default currency for payments.', 'osclass_pay'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="price_decimals" class="h2"><span><?php _e('Price Decimal Numbers', 'osclass_pay'); ?></span></label> 
          <input name="price_decimals" id="price_decimals" type="text" value="<?php echo $price_decimals; ?>" />
          
          <div class="mb-explain"><?php _e('How many decimal numbers show in price. Default is 2.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="price_position" class="h3"><span><?php _e('Position of Currency Symbol', 'osclass_pay'); ?></span></label> 
          <select id="price_position" name="price_position">
            <option value="0" <?php if($price_position == 0) { ?>selected="selected"<?php } ?>><?php _e('After price (1.0$)', 'osclass_pay'); ?></option>
            <option value="1" <?php if($price_position == 1) { ?>selected="selected"<?php } ?>><?php _e('Before price ($1.0)', 'osclass_pay'); ?></option>
          </select>
        </div>

        <div class="mb-row">
          <label for="price_space" class="h4"><span><?php _e('Space Between Price and Symbol', 'osclass_pay'); ?></span></label> 
          <input name="price_space" id="price_space" type="text" value="<?php echo $price_space; ?>" />
          
          <div class="mb-explain"><?php _e('By default there is no space. Usually white space can be added.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="price_decimal_symbol" class="h5"><span><?php _e('Decimal Sybmol', 'osclass_pay'); ?></span></label> 
          <input name="price_decimal_symbol" id="price_decimal_symbol" type="text" value="<?php echo $price_decimal_symbol; ?>" />
          
          <div class="mb-explain"><?php _e('By default it is dot (.)', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="price_thousand_symbol" class="h6"><span><?php _e('Thousands Separator', 'osclass_pay'); ?></span></label> 
          <input name="price_thousand_symbol" id="price_thousand_symbol" type="text" value="<?php echo $price_thousand_symbol; ?>" />
          
          <div class="mb-explain"><?php _e('By default there is white space ( )', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label><?php _e('Sample price', 'osclass_pay'); ?></label>
          <input name="" id="mb-sample-price" type="text" value="<?php echo osp_format_price(rand(10000, 10000000)/100); ?>" readonly/>

          <div class="mb-explain"><?php _e('This is price format defined by you that plugin will use', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="links_sidebar" class=""><span><?php _e('Links in User sidebar', 'osclass_pay'); ?></span></label> 
          <select id="links_sidebar" name="links_sidebar">
            <option value="0" <?php if($links_sidebar == 0) { ?>selected="selected"<?php } ?>><?php _e('Only promotion link', 'osclass_pay'); ?></option>
            <option value="1" <?php if($links_sidebar == 1) { ?>selected="selected"<?php } ?>><?php _e('All links', 'osclass_pay'); ?></option>
          </select>
          
          <div class="mb-explain"><?php _e('Select what links are added into user sidebar', 'osclass_pay'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="horizontal_menu" class=""><span><?php _e('Horizonal Navigation Menu', 'osclass_pay'); ?></span></label> 
          <select id="horizontal_menu" name="horizontal_menu">
            <option value="0" <?php if($horizontal_menu == 0) { ?>selected="selected"<?php } ?>><?php _e('Shown', 'osclass_pay'); ?></option>
            <option value="1" <?php if($horizontal_menu == 1) { ?>selected="selected"<?php } ?>><?php _e('Hidden', 'osclass_pay'); ?></option>
          </select>
          
          <div class="mb-explain"><?php _e('Select if horizontal menu of plugin in user section will be visible or not.', 'osclass_pay'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="def_price_calc" class=""><span><?php _e('Default Price Calculation', 'osclass_pay'); ?></span></label> 
          <select id="def_price_calc" name="def_price_calc">
            <option value="" <?php if($def_price_calc == '') { ?>selected="selected"<?php } ?>><?php _e('Progressive', 'osclass_pay'); ?></option>
            <option value="LINEAR" <?php if($def_price_calc == 'LINEAR') { ?>selected="selected"<?php } ?>><?php _e('Linear', 'osclass_pay'); ?></option>
          </select>
          
          <div class="mb-explain"><?php _e('Select how to calculate default price (item features). Progressive has more complex formula, linear is simply [price] = [fee]*[hours]/24.', 'osclass_pay'); ?></div>
        </div>
        
        
        <div class="mb-row">&nbsp;</div>

        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Save', 'osclass_pay');?></button>
        </div>
      </form>
    </div>
  </div>



  <!-- PLUGIN INTEGRATION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-wrench"></i> <?php _e('Plugin Setup', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <?php if(osc_current_web_theme() == 'osclasswizards') { ?>
        <div class="mb-row mb-errors">
          <div class="mb-line"><?php _e('Osclass Wizards theme require modication in it\'s file.', 'osclass_pay'); ?></div>
          <div class="mb-line"><?php _e('Go to file', 'osclass_pay'); ?>: oc-content/themes/osclasswizards/js/main.js</div>
          <div class="mb-line"><?php _e('Find code', 'osclass_pay'); ?>: $("input").on("ifCreated ifClicked</div>
          <div class="mb-line"><?php _e('Replace it with', 'osclass_pay'); ?>: $("input:not(.osp-input)").on("ifCreated ifClicked</div>
        </div>
      <?php } ?>

      <div class="mb-row">
        <div class="mb-line"><?php _e('There are no changes required in your theme files in order to make plugin fully functional.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php echo sprintf(__('For "Highlight" and "Show image" features please make sure your theme contains hook %s. More info about integration can be found in Items > Highlight > Plugin Setup section.', 'osclass_pay'), '<strong>highlight_class</strong>'); ?></div>
      </div>
    </div>
  </div>



  <!-- HELP TOPICS -->
  <div class="mb-box" id="mb-help">
    <div class="mb-head"><i class="fa fa-question-circle"></i> <?php _e('Help', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-help"><span class="sup">(1)</span> <div class="h1"><?php _e('There can be just one currency for all payments and customers. Note that some payment gateway may not be available in selected currency, i.e. Instamojo or PayUMoney are available just for India Rupee (INR) currency.', 'osclass_pay'); ?></div></div>
    </div>
  </div>
</div>

<?php echo osp_footer(); ?>