<?php
  $restricted_cat = (@$is_restricted_category == 1 ? 1 : 0);
  $restricted_groups = (@$is_restricted_category == 1 ? $groups_allowed : array());

  if($restricted_cat <> 1) {
    osp_user_menu('group');
  }

  $user_id = osc_logged_user_id();
  $currency = osp_currency();
  $symbol = osp_currency_symbol();
  $groups = ModelOSP::newInstance()->getGroups();
  $group = ModelOSP::newInstance()->getGroup(osp_get_user_group());
  $ugroup = ModelOSP::newInstance()->getUserGroupRecord($user_id);
  $repeat = array();

  $group_avl_repeats = array_filter(array_unique(explode(',', OSP_GROUP_DAYS_REPEATS)));

  foreach($groups as $g) {
    $repeat[$g['pk_i_id']] = array();
    
    foreach($group_avl_repeats as $gr) {
      $repeat[$g['pk_i_id']][] = array(
        'quantity' => $gr, 
        // 'title' => sprintf(__('%s days', 'osclass_pay'), $g['i_days']*$gr),
        'title' => osp_format_membership_days($g['i_days']*$gr),
        'days' => $g['i_days']*$gr,
        'price' => osp_get_fee(OSP_TYPE_MEMBERSHIP, 1, $g['pk_i_id'], $g['i_days']*$gr),
        'price_formatted' => osp_format_price(osp_get_fee(OSP_TYPE_MEMBERSHIP, 1, $g['pk_i_id'], $g['i_days']*$gr))
      );
    }
  }
  
  $style = (osp_param('group_style') == 1 ? 'gallery' : 'list');
  $user = User::newInstance()->findByPrimaryKey($user_id);
?>


<div class="osp-body osp-body-group" <?php if(osp_param('groups_enabled') <> 1) { ?>style="display:none!important;<?php } ?>">
  <div class="osp-h1">
    <?php 
      $can_prolong = false;

      if(osp_get_user_group() == 0) {
        _e('You are not member of any group', 'osclass_pay');
      } else {
        if(date('Y', strtotime($ugroup['dt_expire'])) > 2090 || date('Y', strtotime($ugroup['dt_expire'])) < 1980) {
          $expire_string = __('with no expiration', 'osclass_pay');
        } else {
          $can_prolong = true;
          $expire_string = __('until', 'osclass_pay') . ' ' . osc_format_date($ugroup['dt_expire']);
        }

        echo sprintf(__('You are member of %s group %s. This group has flat discount %s on all promotion products!', 'osclass_pay'), '<strong>' . $group['s_name'] . '</strong>', $expire_string, '<strong>' . $group['i_discount'] . '%</strong>');
      }
    ?>
  </div>


  <?php if(isset($ugroup['i_discount']) && $ugroup['i_discount'] <> '' && $ugroup['i_discount'] > 0) { ?>
    <div class="osp-pay-msg"><?php echo sprintf(__('Your membershipt discount %s is not applied on packages as it would lead to double discount.', 'osclass_pay'), round($ugroup['i_discount']) . '%'); ?></div>
  <?php } ?>


  <div class="osp-content">
    <?php foreach($groups as $g) { ?>
      <?php if($restricted_cat <> 1 || ($restricted_cat == 1 && in_array($g['pk_i_id'], $restricted_groups))) { ?>
        <div class="osp-group <?php if(osp_get_user_group() == $g['pk_i_id']) {?>active<?php } ?> <?php echo $style; ?>" data-group="<?php echo $g['pk_i_id']; ?>" data-rank="<?php echo $g['i_rank']; ?>">
          <!-- Not used, can be removed in future -->
          <?php if(1==2) { ?>
          <input type="hidden" id="osp_group_price_<?php echo $g['pk_i_id']; ?>" value="<?php echo osp_format_price($g['f_price'], 0); ?>"/>
          <input type="hidden" id="osp_group_price_last_<?php echo $g['pk_i_id']; ?>" value="<?php echo osp_format_price($g['f_price'], 0); ?>"/>
          <input type="hidden" id="osp_group_days_<?php echo $g['pk_i_id']; ?>" value="<?php echo $g['i_days']; ?>"/>
          <?php } ?>
          
          <div class="osp-top" style="background-color:<?php echo $g['s_color']; ?>;color:<?php echo osp_text_color($g['s_color']); ?>">
            <?php if(@$group['pk_i_id'] == $g['pk_i_id']) { ?>
              <span class="osp-is-active osp-has-tooltip" title="<?php echo osc_esc_html(__('You are member of this group', 'osclass_pay')); ?>"><i class="fa fa-check"></i></span>
            <?php } ?>

            <div class="osp-left">
              <div class="osp-h2"><?php echo $g['s_name']; ?></div>
              <div class="osp-desc"><?php echo $g['s_description']; ?></div>
            </div>

            <div class="osp-right1">
              <div class="osp-price"><?php echo osp_format_price($g['f_price']); ?></div>
              <div class="osp-cost">/ <?php _e('user', 'osclass_pay'); ?> / <span><?php echo $g['i_days'] . '</span> ' . __('days', 'osclass_pay'); ?></div>
            </div>

            <div class="osp-cart-keep">
              <?php if(!$can_prolong && @$g['pk_i_id'] == osp_get_user_group()) { ?>
                <a class="osp_cart_add osp-disabled" href="#" onclick="return false;"><?php echo osp_group_label(@$g['i_rank']); ?></a>
              <?php } else { ?>
                <a class="osp_cart_add" href="<?php echo osp_cart_add(OSP_TYPE_MEMBERSHIP, 1, $g['pk_i_id'], $g['i_days']); ?>"><?php echo osp_group_label($g['pk_i_id'], $g['i_rank']); ?></a>
              <?php } ?>
            </div>
          </div>


          <div class="osp-right2">
            <?php if(ModelOSP::newInstance()->checkGroupDiscount()) { ?>
              <?php if($g['i_discount'] > 0) { ?>
                <div class="osp-perc"><?php _e('Flat discount', 'osclass_pay'); ?>: <strong><?php echo round($g['i_discount']); ?><span>%</span></strong></div>
              <?php } else { ?>
                <div class="osp-perc osp-none"><?php _e('No additional discount', 'osclass_pay'); ?></div>
              <?php } ?>
            <?php } ?>

            <?php if(ModelOSP::newInstance()->checkGroupBonus()) { ?>
              <?php if(osp_param('wallet_periodically') <> '' && osp_param('wallet_periodically') > 0) { ?>
                <?php if($g['i_pbonus'] > 0) { ?>
                  <?php
                    if(osp_param('wallet_period') == 'w') {
                      $period = __('week', 'osclass_pay');
                    } else if(osp_param('wallet_period') == 'm') {
                      $period = __('month', 'osclass_pay');
                    } else if(osp_param('wallet_period') == 'q') {
                      $period = __('quarter', 'osclass_pay');
                    }

                    $ptitle = sprintf(__('Get %s more credits each %s!', 'osclass_pay'), '<strong>' . round($g['i_pbonus']) . '%</strong>', $period);
                  ?>

                  <div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html($ptitle); ?>"><?php echo $ptitle; ?></div>
                <?php } else { ?>
                  <div class="osp-perc osp-none"><?php _e('No extra credits', 'osclass_pay'); ?></div>
                <?php } ?>
              <?php } ?>
            <?php } ?>

            <?php if(osp_param('groups_limit_items') == 1) { ?>
              <?php 
                $def_max_items = osp_param('groups_max_items');
                $def_max_items_days = osp_param('groups_max_items_days');
                $method = osp_param('groups_max_items_type');
                $group_max_items = $g['i_max_items'];
                $group_max_items_days = $g['i_max_items_days'];

                $mi_content = sprintf(__('%s free listings in %s days', 'osclass_pay'), $group_max_items, $group_max_items_days);

                $mi_title = sprintf(__('Members of %s group can publish %s listings in %s days. By default you can only publish %s items in %s days.', 'osclass_pay'), '<strong>' . $g['s_name'] . '</strong>', $group_max_items, $group_max_items_days, $def_max_items, $def_max_items_days);

                if($method == 2 || $method == 3) {
                  $mi_title .= ' (' . __('Premium listings are not counted', 'osclass_pay') . ').';
                }
              ?>

              <div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html($mi_title); ?>"><?php echo $mi_content; ?></div>
            <?php } ?>


            <?php if(ModelOSP::newInstance()->checkGroupCustom()) { ?>
              <?php if($g['s_custom'] <> '') { ?>
                <div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html($g['s_custom']); ?>"><?php echo $g['s_custom']; ?></div>
              <?php } else { ?>
                <div class="osp-perc osp-none">-</div>
              <?php } ?>
            <?php } ?>



            <?php if(ModelOSP::newInstance()->checkGroupPacks()) { ?>
              <?php $packs = ModelOSP::newInstance()->getPacks($g['pk_i_id'], 1); ?>

              <?php if(!empty($packs) && osp_param('wallet_enabled') == 1) { ?>
                <?php
                  $pnames = '';
                  foreach($packs as $p) {
                    if($pnames != '') {
                      $pnames .= ', ';
                    }

                    $pnames .= $p['s_name'];
                  }
                ?>

                <div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html(__('Exclusive credit packs:', 'osclass_pay') . ' ' . $pnames); ?>"><?php _e('Exclusive packs:', 'osclass_pay'); ?> <?php echo $pnames; ?></div>
              <?php } else { ?>
                <div class="osp-perc osp-none"><?php _e('No exclusive credit packs', 'osclass_pay'); ?></div>
              <?php } ?>
            <?php } ?>

            <?php if(osp_fee_is_allowed(OSP_TYPE_PUBLISH)) { ?>
              <div class="osp-perc<?php if($g['i_free_items_101'] <= 0) { ?> osp-none<?php } ?>"><?php _e('Free active listings:', 'osclass_pay'); ?> <?php echo ($g['i_free_items_101'] > 0 ? $g['i_free_items_101'] : 0); ?></div>

            <?php } ?>

            <?php if(ModelOSP::newInstance()->checkGroupCategory()) { ?>
              <?php if(osp_param('groups_category') == 1) { ?>
                <?php if(trim($g['s_category']) <> '') { ?>
                  <?php
                    $ids = explode(',', trim($g['s_category']));
                    $ids = array_filter($ids);

                    $names = array();
                    foreach($ids as $i) {
                      $cat = Category::newInstance()->findByPrimaryKey($i);
                      $names[] = $cat['s_name'];
                    }

                    $names = array_filter($names);
                    $categories = implode(', ', $names);
                  ?>

                  <div class="osp-cats osp-has-tooltip" title="<?php echo osc_esc_html(__('Exclusive access to categories:', 'osclass_pay') . ' ' . $categories); ?>"><?php _e('Exclusive access to categories:', 'osclass_pay'); ?> <?php echo $categories; ?></div>
                <?php } else { ?>
                  <div class="osp-cats osp-none"><?php _e('No exclusive access to categories', 'osclass_pay'); ?></div>
                <?php } ?>
              <?php } ?>
            <?php } ?>


            <label class="osp-label" for="osp-select-group"><?php _e('Duration', 'osclass_pay'); ?></label>
            <select class="osp-select osp-select-group" id="osp-select-group" name="osp-select-group" data-group="<?php echo $g['pk_i_id']; ?>">
              <?php $k = 0; ?>
              <?php foreach($repeat[$g['pk_i_id']] as $r) { ?>
                <option value="<?php echo $r['quantity']; ?>" data-days="<?php echo osc_esc_html($r['days']); ?>" data-price="<?php echo osc_esc_html($r['price']); ?>" data-price-formatted="<?php echo osc_esc_html($r['price_formatted']); ?>" <?php if($k == 0) { ?>selected="selected"<?php } ?>><?php echo osc_esc_html($r['title']); ?></option>
                <?php $k++; ?>
              <?php } ?>
            </select>
          </div>

        </div>
      <?php } ?>
    <?php } ?>
  </div>
</div>