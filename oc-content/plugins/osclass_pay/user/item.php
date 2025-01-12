<?php
  osp_user_menu('item');

  $user_id = osc_logged_user_id();
  $user = User::newInstance()->findByPrimaryKey($user_id);

  $per_page = (Params::getParam('per_page') != '') ? Params::getParam('per_page') : 12;   //itemsPerPage
  $page_id = (Params::getParam('pageId') != '') ? Params::getParam('pageId') : 0;
  $total_items = Item::newInstance()->countByUserID(osc_logged_user_id());

  $items = ModelOSP::newInstance()->findByUserID(osc_logged_user_id(), ($page_id > 0 ? (($page_id - 1) * $per_page) : 0), $per_page);
  $items = Item::newInstance()->extendData($items);
  
  $free_limit_101 = osp_get_user_group_limit(osc_logged_user_id());
  $user_active_items = ModelOSP::newInstance()->countUserItems(osc_logged_user_id());
  $group = ModelOSP::newInstance()->getGroup(osp_get_user_group());

  View::newInstance()->_exportVariableToView('items', $items);
?>


<div class="osp-body osp-body-item">
  <?php if(osp_fee_is_allowed(OSP_TYPE_PUBLISH) && osp_get_user_group() > 0 && osp_get_user_group_limit() > 0) { ?>
    <div class="osp-h2"><?php echo sprintf(__('As a member of %s group, you can publish %s listings for free. Your remaining limit is %s free items.', 'osclass_pay'), '<b>' . $group['s_name'] . '</b>', '<b>' . $free_limit_101 . '</b>', '<b>' . max($free_limit_101 - $user_active_items, 0) . '</b>'); ?></div>  
  <?php } ?>
  
  <div class="osp-h1"><?php _e('Your listings', 'osclass_pay'); ?></div>


  <div class="osp-inside">
    <?php if(osc_count_items() == 0) { ?>
      <div class="osp-cart-row osp-cart-empty">
        <i class="fa fa-warning"></i><span><?php _e('You don\'t have any listing yet', 'osclass_pay'); ?></span>
      </div>
    <?php } else { ?>
      <?php while(osc_has_items()) { ?>
        <?php
          $expire = osc_item_field('dt_expiration');
          $check = explode('-', $expire);
          $year = (int)$check[0];
        ?>

        <div class="osp-item" >
          <div class="osp-top">
            <?php osc_reset_resources(); ?>

            <div class="osp-img">
              <?php if(osc_count_item_resources() >= 1) { ?>
                <img src="<?php echo osc_resource_thumbnail_url(); ?>"/>
              <?php } else { ?>
                <img src="<?php echo osp_url(); ?>img/no-image/no-image.png"/>
              <?php } ?>
            </div>

            <div class="osp-left">
              <div class="osp-h2">
                <a href="<?php echo osc_item_url(); ?>"><?php echo osc_highlight(osc_item_title(), 30); ?></a>
                
                <?php if(osc_item_is_inactive()) { ?><span class="osp-item-inactive"><?php _e('Inactive', 'osclass_pay'); ?></span><?php } ?>
              </div>
              <div class="osp-date">
                <?php _e('Published on', 'osclass_pay') ; ?> <?php echo osc_format_date(osc_item_pub_date()); ?>, 
                <?php if($year > 2010 && $year < 2090) { ?>
                  <?php _e('Expire on', 'osclass_pay') ; ?> <?php echo osc_format_date(osc_item_field('dt_expiration')); ?>
                <?php } else { ?>
                  <?php _e('Never expire', 'osclass_pay') ; ?>
                <?php } ?>
              </div>
            </div>

            <div class="osp-right">
              <?php echo osc_format_price(osc_item_price()); ?>
            </div>
          </div>

          <div class="osp-bot">
            <div class="osp-stat">
              <?php 
                $has = false; 
                $types = array(OSP_TYPE_PUBLISH, OSP_TYPE_IMAGE, OSP_TYPE_TOP, OSP_TYPE_PREMIUM, OSP_TYPE_HIGHLIGHT, OSP_TYPE_REPUBLISH);
              ?>

              <?php foreach($types as $type) { ?>
                <?php if(osp_fee_is_allowed($type) && osp_fee_is_paid($type, osc_item_id()) && osp_fee_exists($type, osc_item_id())) { ?>
                  <?php  
                    $has = true;
                    $fee_item = osp_get_fee_record($type, osc_item_id(), 1);
                    $title = osp_product_title(array($type, osc_item_id(), $fee_item['dt_expire'], $fee_item['i_repeat'], $fee_item));
                  ?>

                  <span class="osp-<?php echo $type; ?> osp-has-tooltip" title="<?php echo ($title['long'] <> '' ? osc_esc_html($title['long']) : ''); ?>"><?php echo $title['short']; ?></span>
                <?php } ?>

                <?php if(($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE) && osp_fee_is_allowed($type) && !osp_fee_is_paid($type, osc_item_id()) && osp_fee_exists($type, osc_item_id())) { ?>
                  <?php
                    $has = true;
                    if($type == OSP_TYPE_PUBLISH) {
                      $title['short'] = __('Publish fee not paid', 'osclass_pay');
                      $title['long'] = __('This listing will not be visible until Publish Fee is paid!', 'osclass_pay');
                    } else if($type == OSP_TYPE_IMAGE) {
                      $title['short'] = __('Image fee not paid', 'osclass_pay');
                      $title['long'] = __('Images on this listing will not be visible until Image Fee is paid!', 'osclass_pay');
                    }
                  ?>

                  <span class="osp-issue osp-has-tooltip" title="<?php echo ($title['long'] <> '' ? osc_esc_html($title['long']) : ''); ?>"><?php echo $title['short']; ?></span>
                <?php } ?>
              <?php } ?>

              <?php if(!$has) { ?>
                <span class="osp-none"><?php _e('No promotions', 'osclass_pay'); ?></span>
              <?php } ?>
            </div>
          </div>

          <div class="osp-promote">
            <span class="osp-text"><?php _e('Promote', 'osclass_pay'); ?></span>
            <span class="osp-icon"><i class="fa fa-angle-down"></i></span>
          </div>

          <?php echo osp_item_options(osc_item_id()); ?>
        </div>
      <?php } ?>
      
      <?php echo osp_item_paginate($page_id, $per_page, $total_items); ?>
    <?php } ?>
  </div>
</div>