<?php
  // Create menu
  // $title = __('Currency Rates', 'osclass_pay');
  // osp_menu($title);


  // REFRESH CURRENCY RATES
  if(Params::getParam('what') == 'refreshRates') {
    $result = osp_get_currency_rates();
    
    if($result === true) {
      message_ok(__('Currency rates successfully updated.', 'osclass_pay'));
    } else {
      message_error('ExchangeRatesApi.io: ' . $result);
    }
  }
?>

<div class="mb-body">
  <?php if(osp_param('exchangeratesapikey') == '') { ?>
    <div class="mb-errors">
      <div class="mb-line"><?php _e('You are missing exchangeratesapi.io API key, currency rates refresh will not be functional! You can enter API key in Settings section.', 'osclass_pay'); ?></div> 
    </div>
  <?php } ?>
  
  <div class="mb-box mb-currency">
    <div class="mb-head"><i class="fa fa-exchange"></i> <?php _e('Currency Rates', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <?php if(osp_param('use_exchange_rates_plugin') == 1) { ?>
        <div class="mb-row mb-notes">
          <?php if(function_exists('exr_convert')) { ?>
            <div class="mb-line"><strong><?php _e('Exchange Rates Plugin has been setup as replacement for built-in currency conversions. Rates below are not used by plugin now!', 'osclass_pay'); ?></strong></div>
          <?php } else { ?>
            <div class="mb-line"><strong><?php _e('Exchange Rates Plugin has been setup as replacement for built-in currency conversions, however plugin function <u>exr_convert</u> was not found! You may be using old or incompatible version of plugin.', 'osclass_pay'); ?></strong></div>
          <?php } ?>
          
          <div class="mb-line"><?php _e('You may change this setting in eCommerce > Settings page', 'osclass_pay'); ?></div>
        </div>
      <?php } ?>
      
    
      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('In order to make eCommerce functional, it is required to convert any price of item, to currency of plugin, if it is different.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('Plugin uses ExchangeRatesApi.io to get currency rate with a daily frequency. Make sure your cron is functional.', 'osclass_pay'); ?></div>
      </div>

      <div class="mb-row mb-notes">
        <div class="mb-line"><?php echo sprintf(__('Osclass Pay Plugin has default currency %s and all other currencies are converted to this one', 'osclass_pay'), '<strong>' . osp_currency() . '(' . osp_currency_symbol() . ')</strong>'); ?></div>
      </div>

      <div class="mb-row">
        <div class="mb-line"><?php _e('Your classifieds has configured following currencies as available.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php echo sprintf(__('You can modify this list in %s', 'osclass_pay'), '<a target="_blank" href="' . osc_admin_base_url(true) . '?page=currencies">' . __('Settings > Currencies', 'osclass_pay') . '</a>'); ?>.</div>
      </div>

      <div class="mb-row">&nbsp;</div>

      <div class="mb-table mb-table-currencies">
        <div class="mb-table-head">
          <div class="mb-col-1"><?php _e('Code', 'osclass_pay');?></div>
          <div class="mb-col-5 mb-align-left"><?php _e('Name', 'osclass_pay');?></div>
          <div class="mb-col-4"><?php _e('Symbol', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-left"><?php _e('Rate', 'osclass_pay');?></div>
          <div class="mb-col-12 mb-align-left"><?php _e('Detail', 'osclass_pay');?></div>
        </div>

        <?php $currencies = ModelOSP::newInstance()->getCurrencies(); ?>

        <?php foreach($currencies as $code) { ?>
          <?php
            $c = Currency::newInstance()->findByPrimaryKey($code['pk_c_code']);
            $rate = ModelOSP::newInstance()->getRate($c['pk_c_code']);

            $problem = false;
            $def = false;
            if($c['pk_c_code'] == osp_currency()) {
              $rate_text = '-';
              $rate_detail = __('No conversion, osclass pay default currency', 'osclass_pay');
              $def = true;
            } else if($rate == 1.0 && $c['pk_c_code'] <> osp_currency()) {
              $rate_text = '-';
              $rate_detail = __('Not set, run refresh!', 'osclass_pay');
              $problem = true;
            } else {
              $rate_text = number_format($rate, 4);
              $rate_detail  = '<span>1' . osp_currency_symbol() . ' = ' . number_format(1/$rate, 4) . $c['s_description'] . '</span>'; 
              $rate_detail .= '<span>1' . $c['s_description'] . ' = ' . number_format($rate, 4) . osp_currency_symbol() . '</span>'; 

            }
          ?>

          <div class="mb-table-row">
            <div class="mb-col-1"><?php echo $c['pk_c_code']; ?></div>
            <div class="mb-col-5 mb-align-left"><?php echo $c['s_name']; ?></div>
            <div class="mb-col-4"><?php echo $c['s_description']; ?></div>
            <div class="mb-col-2 mb-align-left mb-rate <?php if($problem) { ?>mb-rate-dash<?php } ?>"><?php echo $rate_text; ?></div>
            <div class="mb-col-12 mb-align-left mb-cur-desc <?php if($problem) { ?>mb-rate-null<?php } ?> <?php if($def) { ?>mb-rate-def<?php } ?>"><?php echo $rate_detail; ?></div>
          </div>
        <?php } ?>
      </div>

      <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=osclass_pay/admin/ecommerce.php&go_to_file=_ecommerce_currencies.php&what=refreshRates" class="mb-button-green mb-get-rates"><?php _e('Refresh Rates', 'osclass_pay'); ?></a>

    </div>
  </div>



</div>

<?php echo osp_footer(); ?>