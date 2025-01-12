<?php
//$category_id from form
$types = array(OSP_TYPE_PUBLISH, OSP_TYPE_IMAGE, OSP_TYPE_TOP, OSP_TYPE_PREMIUM, OSP_TYPE_HIGHLIGHT, OSP_TYPE_REPUBLISH);

if(isset($item)) {
  $item_id = $item['pk_i_id'];
  $category_id = $item['fk_i_category_id'];
} else {
  $item_id = '';
}

if(!isset($is_itempay)) {
  $is_itempay = 0;
}

$no_promotions = true;
foreach($types as $type) { 
  if(osp_fee_is_allowed($type)) {
    $no_promotions = false;
    break;
  }
}

?>

<?php if($no_promotions === false) { ?>
  <div class="osp-promote-form <?php if(osc_get_osclass_location() == 'ajax' && osc_get_osclass_section() == 'runhook') { ?>osp-is-publish<?php } ?>" data-theme="<?php echo osc_current_web_theme(); ?>">
    <input type="hidden" name="ospDecimals" value="<?php echo osp_param('price_decimals'); ?>"/>
    <input type="hidden" name="ospDecimalSymbol" value="<?php echo osp_param('price_decimal_symbol'); ?>"/>
    <input type="hidden" name="ospThousandSymbol" value="<?php echo osp_param('price_thousand_symbol'); ?>"/>

    <?php if(isset($item)) { ?>
      <input type="hidden" name="countryId" value="<?php echo $item['fk_c_country_code']; ?>"/>
      <input type="hidden" name="regionId" value="<?php echo $item['fk_i_region_id']; ?>"/>
    <?php } ?>

    <?php if(isset($item)) { ?>
      <?php if($is_itempay == 0) { ?>
        <div class="osp-h1"><?php _e('Promote your listing to make it more attractive', 'osclass_pay'); ?></div>
        <div class="osp-h2"><?php _e('This section is visible only to owner of this listing and admins', 'osclass_pay'); ?></div>
      <?php } else { ?>
        <div class="osp-h1"><?php _e('Click to show promotion options for your listing', 'osclass_pay'); ?></div>
      <?php } ?>
    <?php } ?>

    <?php if(isset($item)) { ?>
      <form class="nocsrf" action="<?php echo osc_route_url('osp-item-pay', array('itemId' => $item_id)); ?>" method="POST" name="manage_promote_form">
        <input type="hidden" name="itemId" value="<?php echo $item_id; ?>"/>
        <input type="hidden" name="manage_promote" value="1"/>
        <div class="osp-wrap">
    <?php } ?>

    <?php foreach($types as $type) { ?>
      <?php if(osp_fee_is_allowed($type) && (!osp_fee_is_paid_special($type, $item_id) || !osp_fee_is_paid($type, $item_id))) { ?>
        <?php
          $paid_title = '';
          
          if(osp_fee_is_paid($type, $item_id)) {
            $paid_title = osc_esc_html(__('This feature is already paid, if you would like to extend it, login to your account and prolong promotion from user account section', 'osclass_pay'));
          }
        ?>

        <?php if($type == OSP_TYPE_PUBLISH) { ?>

          <?php 
            if(isset($item)) {
              $fee = osp_get_fee($type, 1, $item['pk_i_id']);
            } else {
              $fee = ModelOSP::newInstance()->getCategoryFee($type, $category_id)*(1-osp_user_group_discount());
            }
            
            // Check free limits
            $free_publish_limit = 0;

            if(osc_is_web_user_logged_in()) {
              $free_publish_limit = osp_get_user_group_remaining_limit(osc_logged_user_id(), OSP_TYPE_PUBLISH);
            }
          ?>

          <?php if($fee > 0) { ?>
            <?php if(isset($item)) { ?>
              <div class="osp-pb-line osp-has-tooltip-left pt<?php echo $type; ?> <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>" data-type="<?php echo $type; ?>" title="<?php echo $paid_title; ?>">
            <?php } else { ?>
              <div class="osp-pb-line osp-has-tooltip-left pt<?php echo $type; ?>" data-type="<?php echo $type; ?>">
            <?php } ?>

              <label class="osp-chk readonly"><input class="osp-input" type="checkbox" name="<?php echo $type; ?>" value="1" checked="checked" readonly/><div></div></label>
              <div class="osp-text">
                <div class="osp-pb-name">
                  <?php _e('Publish Fee', 'osclass_pay'); ?> 
                  <strong class="finprice_<?php echo $type; ?> <?php if($free_publish_limit > 0) { ?>strike<?php } ?>" data-price="<?php echo osp_format_price($fee, 0); ?>" data-price-current="<?php echo osp_format_price($fee, 0); ?>"><?php echo osp_format_price($fee); ?></strong>
                  
                  <?php if($free_publish_limit > 0) { ?>
                    <span class="freelimit"><?php _e('Free of charge!', 'osclass_pay'); ?></span>
                  <?php } ?>
                </div>
                
                <div class="osp-pb-desc">
                  <span class="<?php if($free_publish_limit > 0) { ?>strike<?php } ?>"><?php _e('In order to show your listing it is required to pay publish fee', 'osclass_pay'); ?>.</span>

                  <?php if($free_publish_limit > 0) { ?>
                    <span class="freerow"><?php echo sprintf(__('You have %s remaining items published for free (group bonus).', 'osclass_pay'), '<b>' . $free_publish_limit . '</b>'); ?></span>
                  <?php } ?>
                </div>
              </div>
            </div>
          <?php } ?>

        <?php } else if($type == OSP_TYPE_IMAGE) { ?>

          <?php 
            if(isset($item)) {
              $fee = osp_get_fee($type, 1, $item['pk_i_id']);
            } else {
              $fee = ModelOSP::newInstance()->getCategoryFee($type, $category_id)*(1-osp_user_group_discount());
            }
          ?>

          <?php if($fee > 0) { ?>
            <?php if(isset($item)) { ?>
              <div class="osp-pb-line osp-has-tooltip-left pt<?php echo $type; ?> <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>" data-type="<?php echo $type; ?>" title="<?php echo $paid_title; ?>">
            <?php } else { ?>
              <div class="osp-pb-line osp-has-tooltip-left pt<?php echo $type; ?>" data-type="<?php echo $type; ?>">
            <?php } ?>

              <label class="osp-chk"><input class="osp-input" type="checkbox" name="<?php echo $type; ?>" value="1" checked="checked" <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>/><div></div></label>
              <div class="osp-text">
                <div class="osp-pb-name"><?php _e('Show Images Fee', 'osclass_pay'); ?> <strong class="finprice_<?php echo $type; ?>" data-price="<?php echo osp_format_price($fee, 0); ?>" data-price-current="<?php echo osp_format_price($fee, 0); ?>"><?php echo osp_format_price($fee); ?></strong></div>
                <div class="osp-pb-desc"><?php _e('In order to show images on your listing it is required to pay fee', 'osclass_pay'); ?></div>
              </div>
            </div>
          <?php } ?>

        <?php } else if($type == OSP_TYPE_TOP && isset($item)) { ?>

          <?php 
            if(isset($item)) {
              $fee = osp_get_fee($type, 1, $item['pk_i_id']);
            } else {
              $fee = ModelOSP::newInstance()->getCategoryFee($type, $category_id)*(1-osp_user_group_discount());
            }
          ?>

          <?php if($fee > 0) { ?>
            <?php if(isset($item)) { ?>
              <div class="osp-pb-line pt<?php echo $type; ?>" data-type="<?php echo $type; ?>">
            <?php } else { ?>
              <div class="osp-pb-line osp-has-tooltip-left pt<?php echo $type; ?>" data-type="<?php echo $type; ?>">
            <?php } ?>

              <label class="osp-chk"><input class="osp-input" type="checkbox" name="<?php echo $type; ?>" value="1" <?php if(isset($item) && osp_fee_exists($type, $item['pk_i_id'], 0)) {?>checked="checked"<?php } ?>/><div></div></label>
              <div class="osp-text">
                <div class="osp-pb-name"><?php _e('Move to Top', 'osclass_pay'); ?> <strong class="finprice_<?php echo $type; ?>" data-price="<?php echo osp_format_price($fee, 0); ?>" data-price-current="<?php echo osp_format_price($fee, 0); ?>"><?php echo osp_format_price($fee); ?></strong></div>
                <div class="osp-pb-desc"><?php _e('Your listings will be moved to top position in search results', 'osclass_pay'); ?></div>
              </div>
            </div>
          <?php } ?>

        <?php } else if($type == OSP_TYPE_PREMIUM) { ?>

          <?php 
            $duration = (osp_param('premium_duration') <> '' ? osp_param('premium_duration') : 24); 
            $duration_array = explode(',', $duration);
            $record = (isset($item) ? osp_get_fee_record($type, $item['pk_i_id'], 0) : false);
            $rec_hours = (isset($record['i_hours']) ? $record['i_hours'] : '');

            $is_notnull = false;
            foreach($duration_array as $d) { 
              if(ModelOSP::newInstance()->getCategoryFee($type, $category_id, $d)*(1-osp_user_group_discount()) > 0) {
                $is_notnull = true;
              }
            }
          ?>

          <?php if($is_notnull) { ?>
            <?php if(isset($item)) { ?>
              <div class="osp-pb-line osp-has-tooltip-left pt<?php echo $type; ?> <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>" data-type="<?php echo $type; ?>" title="<?php echo $paid_title; ?>">
            <?php } else { ?>
              <div class="osp-pb-line osp-has-tooltip-left pt<?php echo $type; ?>" data-type="<?php echo $type; ?>">
            <?php } ?>

              <?php
                $base_fee = (isset($item) ? osp_get_fee($type, 1, $item['pk_i_id'], ($rec_hours <> '' ? $rec_hours : $duration_array[0])) : ModelOSP::newInstance()->getCategoryFee($type, $category_id, $duration_array[0])*(1-osp_user_group_discount()));
              ?>

              <label class="osp-chk"><input class="osp-input" type="checkbox" name="<?php echo $type; ?>" value="1" <?php if(isset($item) && (osp_fee_exists($type, $item['pk_i_id'], 0) || osp_fee_is_paid($type, $item_id))) {?>checked="checked"<?php } ?> <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>/><div></div></label>
              <div class="osp-text">
                <div class="osp-pb-name"><?php _e('Mark as Premium', 'osclass_pay'); ?> <strong class="finprice_<?php echo $type; ?>" data-price="<?php echo osp_format_price($base_fee, 0); ?>" data-price-current="<?php echo osp_format_price($base_fee, 0); ?>"><?php echo osp_format_price($base_fee); ?></strong></div>
                <div class="osp-pb-desc"><?php _e('Make your listing unique on home and search page!', 'osclass_pay'); ?></div>
              </div>

              <div class="osp-select">
                <select id="<?php echo $type; ?>_duration" name="<?php echo $type; ?>_duration" <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>>
                  <?php foreach($duration_array as $d) { ?>
                    <?php 
                      if(isset($item)) {
                        $fee = osp_get_fee($type, 1, $item['pk_i_id'], $d);
                      } else {
                        $fee = ModelOSP::newInstance()->getCategoryFee($type, $category_id, $d)*(1-osp_user_group_discount());
                      }
                    ?>

                    <?php if($fee > 0) { ?>
                      <option value="<?php echo $d; ?>" data-price-orig="<?php echo osp_format_price($fee, 0); ?>" data-price-current="<?php echo osp_format_price($fee, 0); ?>" <?php if($rec_hours == $d) { ?>selected="selected"<?php } ?> ><?php echo osp_duration_name($d) . ' ' . __('for', 'osclass_pay') . ' ' . osp_format_price($fee); ?></option>
                    <?php } ?>
                  <?php } ?>
                </select>
              </div>
            </div>
          <?php } ?>

        <?php } else if($type == OSP_TYPE_HIGHLIGHT) { ?>
          <?php 
            $duration = (osp_param('highlight_duration') <> '' ? osp_param('highlight_duration') : 24); 
            $duration_array = explode(',', $duration);
            $record = (isset($item) ? osp_get_fee_record($type, $item['pk_i_id'], 0) : false);
            $rec_hours = (isset($record['i_hours']) ? $record['i_hours'] : '');

            $is_notnull = false;
            foreach($duration_array as $d) { 
              if(ModelOSP::newInstance()->getCategoryFee($type, $category_id, $d)*(1-osp_user_group_discount()) > 0) {
                $is_notnull = true;
              }
            }
          ?>

          <?php if($is_notnull) { ?>
            <?php if(isset($item)) { ?>
              <div class="osp-pb-line osp-has-tooltip-left pt<?php echo $type; ?> <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>" data-type="<?php echo $type; ?>" title="<?php echo $paid_title; ?>">
            <?php } else { ?>
              <div class="osp-pb-line osp-has-tooltip-left pt<?php echo $type; ?>" data-type="<?php echo $type; ?>">
            <?php } ?>

              <?php 
                $base_fee = (isset($item) ? osp_get_fee($type, 1, $item['pk_i_id'], ($rec_hours <> '' ? $rec_hours : $duration_array[0])) : ModelOSP::newInstance()->getCategoryFee($type, $category_id, $duration_array[0])*(1-osp_user_group_discount()));
              ?>

              <label class="osp-chk"><input class="osp-input" type="checkbox" name="<?php echo $type; ?>" value="1" <?php if(isset($item) && (osp_fee_exists($type, $item['pk_i_id'], 0) || osp_fee_is_paid($type, $item_id))) {?>checked="checked"<?php } ?> <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>/><div></div></label>
              <div class="osp-text">
                <div class="osp-pb-name"><?php _e('Highlight Item', 'osclass_pay'); ?> <strong class="finprice_<?php echo $type; ?>" data-price="<?php echo osp_format_price($base_fee, 0); ?>" data-price-current="<?php echo osp_format_price($base_fee, 0); ?>"><?php echo osp_format_price($base_fee); ?></strong></div>
                <div class="osp-pb-desc"><?php _e('Make listing more visible and attract more people!', 'osclass_pay'); ?></div>
              </div>

              <div class="osp-select">
                <select id="<?php echo $type; ?>_duration" name="<?php echo $type; ?>_duration" <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>>
                  <?php foreach($duration_array as $d) { ?>
                    <?php 
                      if(isset($item)) {
                        $fee = osp_get_fee($type, 1, $item['pk_i_id'], $d);
                      } else {
                        $fee = ModelOSP::newInstance()->getCategoryFee($type, $category_id, $d)*(1-osp_user_group_discount());
                      }
                    ?>

                    <?php if($fee > 0) { ?>
                      <option value="<?php echo $d; ?>" data-price-orig="<?php echo osp_format_price($fee, 0); ?>" data-price-current="<?php echo osp_format_price($fee, 0); ?>" <?php if($rec_hours == $d) { ?>selected="selected"<?php } ?> ><?php echo osp_duration_name($d) . ' ' . __('for', 'osclass_pay') . ' ' . osp_format_price($fee); ?></option>
                    <?php } ?>
                  <?php } ?>
                </select>
              </div>
            </div>
          <?php } ?>

        <?php } else if($type == OSP_TYPE_REPUBLISH) { ?>

          <?php 
            $duration = (osp_param('republish_duration') <> '' ? osp_param('republish_duration') : 24); 
            $repeat = (osp_param('republish_repeat') <> '' ? osp_param('republish_repeat') : 1); 
            $duration_array = explode(',', $duration);
            $repeat_array = explode(',', $repeat);
            $record = (isset($item) ? osp_get_fee_record($type, $item['pk_i_id'], 0) : false);
            $rec_hours = (isset($record['i_hours']) ? $record['i_hours'] : '');
            $rec_repeat = (isset($record['i_repeat']) ? $record['i_repeat'] : '');

            if($repeat == '' || $repeat <= 0) {
              $repeat = 1;
            }


            $repeat_discount = osp_param('republish_repeat_discount')/100;

            if($repeat_discount == '' || $repeat_discount > 1 || $repeat_discount < 0) {
              $repeat_discount = 1;
            } else {
              $repeat_discount = 1 - $repeat_discount;
            }


            $is_notnull = false;
            foreach($duration_array as $d) { 
              if(ModelOSP::newInstance()->getCategoryFee($type, $category_id, $d)*(1-osp_user_group_discount()) > 0) {
                $is_notnull = true;
              }
            }
          ?>


          <?php if($is_notnull) { ?>
            <?php if(isset($item)) { ?>
              <div class="osp-pb-line osp-has-tooltip-left pt<?php echo $type; ?> <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>" data-type="<?php echo $type; ?>" title="<?php echo $paid_title; ?>">
            <?php } else { ?>
              <div class="osp-pb-line osp-has-tooltip-left pt<?php echo $type; ?>" data-type="<?php echo $type; ?>">
            <?php } ?>

              <?php 
                $base_fee = (isset($item) ? osp_get_fee($type, 1, $item['pk_i_id'], ($rec_hours <> '' ? $rec_hours : $duration_array[0]), ($rec_repeat <> '' ? $rec_repeat : $repeat_array[0])) : ModelOSP::newInstance()->getCategoryFee($type, $category_id, $duration_array[0])*(1-osp_user_group_discount()));

                if(!isset($item)) {
                  $base_fee = $base_fee * $repeat_array[0] * (pow($repeat_discount, ($repeat_array[0] - 1)));
                }
              ?>

              <label class="osp-chk"><input class="osp-input" type="checkbox" name="<?php echo $type; ?>" value="1" <?php if(isset($item) && (osp_fee_exists($type, $item['pk_i_id'], 0) || osp_fee_is_paid($type, $item_id))) {?>checked="checked"<?php } ?> <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>/><div></div></label>
              <div class="osp-text">
                <div class="osp-pb-name"><?php _e('Auto-Republish', 'osclass_pay'); ?> <strong class="finprice_<?php echo $type; ?>" data-price="<?php echo osp_format_price($base_fee, 0); ?>" data-price-current="<?php echo osp_format_price($base_fee, 0); ?>"><?php echo osp_format_price($base_fee); ?></strong></div>
                <div class="osp-pb-desc"><?php _e('Listing will be renewed multiple times in selected intervals', 'osclass_pay'); ?></div>
              </div>

              <div class="osp-select1">
                <select id="<?php echo $type; ?>_duration" name="<?php echo $type; ?>_duration" <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>>
                  <?php foreach($duration_array as $d) { ?>
                    <?php 
                      if(isset($item)) {
                        $fee = osp_get_fee($type, 1, $item['pk_i_id'], $d);
                      } else {
                        $fee = ModelOSP::newInstance()->getCategoryFee($type, $category_id, $d)*(1-osp_user_group_discount());
                      }
                    ?>

                    <?php if($fee > 0) { ?>
                      <option value="<?php echo $d; ?>" data-price-orig="<?php echo osp_format_price($fee, 0); ?>" data-price-current="<?php echo osp_format_price($fee, 0); ?>" <?php if($rec_hours == $d) { ?>selected="selected"<?php } ?> ><?php echo osp_duration_name($d) . ' ' . __('for', 'osclass_pay') . ' ' . osp_format_price($fee); ?></option>
                    <?php } ?>
                  <?php } ?>
                </select>
              </div>


              <?php
                $repeat_title = sprintf(__('Get stacked discount for repeating republish %s%% for each extra repeat!', 'osclass_pay'), round((1-$repeat_discount)*100, 1)) . '<br/>';
                foreach($repeat_array as $r) {
                  if($r > 1) {
                    $repeat_title .= '<br/>' . round((1 - pow($repeat_discount, ($r - 1)))*100, 1) . '% - ' . $r . ' ' . __('repeats', 'osclass_pay');
                  }
                }
              ?>

              <div class="osp-select2 <?php echo ($repeat_discount > 0 ? 'osp-has-tooltip' : ''); ?>" <?php echo ($repeat_discount > 0 ? 'title="' . $repeat_title . '"' : ''); ?> <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>>
                <select id="<?php echo $type; ?>_repeat" name="<?php echo $type; ?>_repeat" <?php if(osp_fee_is_paid($type, $item_id)) { ?>disabled<?php } ?>>
                  <?php foreach($repeat_array as $r) { ?>
                    <option value="<?php echo $r; ?>" data-repeat-discount="<?php echo (pow($repeat_discount, ($r - 1))); ?>" <?php if($rec_repeat == $r) { ?>selected="selected"<?php } ?> ><?php echo $r; ?>x</option>
                  <?php } ?>
                </select>
              </div>
            </div>
          <?php } ?>

        <?php } ?>
      <?php } ?>
    <?php } ?>

    <?php if(isset($item)) { ?>
      </div>
      <button id="osp-item-promote" type="submit"><i class="fa fa-check-circle"></i> <?php echo ($is_itempay == 1 ? __('Update promotions', 'osclass_pay') : __('Process promotions', 'osclass_pay')); ?></button>
      </form>
    <?php } ?>
  </div>


  <script>
    Tipped.create('.osp-has-tooltip', { maxWidth: 200, radius: false });

    <?php if(!isset($item)) { ?>
      $(document).ready(function(){
        var ospLocId = '';

        if($('[name="regionId"]').val() != '') {
          ospLocId = $('[name="regionId"]').val();
        } else if($('input[name="region"]').val() != '') {
          ospLocId = $('input[name="region"]').val();
        } else if($('[name="countryId"]').val() != '') {
          ospLocId  = $('[name="countryId"]').val();
        } else if($('input[name="country"]').val() != '') {
          ospLocId  = $('input[name="country"]').val();
        }

        ospPromoteUpdate(ospLocId, '10');  // update prices according to active region/country
      });
    <?php } ?>

    <?php if(OSP_DEBUG) { ?>
      console.log('osp item form loaded');
    <?php } ?>
  </script>
<?php } ?>