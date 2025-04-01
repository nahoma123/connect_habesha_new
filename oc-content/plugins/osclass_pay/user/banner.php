<?php
  osp_user_menu('banner');

  $banners = ModelOSP::newInstance()->getBanners(-1, osc_logged_user_id());

  $user_id = osc_logged_user_id();
  $remove_id = Params::getParam('removeId');

  if($remove_id <> '' && $remove_id > 0) {
    osp_cart_remove($user_id, OSP_TYPE_BANNER . 'x1x' . $remove_id);
    ModelOSP::newInstance()->deleteBanner($remove_id);
    osc_add_flash_ok_message(__('Banner successfully removed.', 'osclass_pay'));
    osp_redirect(osc_route_url('osp-banner'));
  }

?>

<div class="osp-body osp-body-banner">
  <div class="osp-h1">
    <span><?php _e('Your banners', 'osclass_pay'); ?></span>
  </div>

  <div class="osp-h2">
    <?php echo sprintf(__('If you are looking forward to have your advertisement on our site, anywhere you see button "%s" you can submit own banner. After admin approval and budget payment your advert will be visible on our site.', 'osclass_pay'), '<strong>' . __('Advertise here!', 'osclass_pay') . '</strong>'); ?>
  </div>

  <div class="osp-table-banners">
    <div class="osp-head-row">
      <div class="osp-col name"><?php _e('Name', 'osclass_pay'); ?></div>
      <div class="osp-col views"><?php _e('Views', 'osclass_pay'); ?></div>
      <div class="osp-col clicks"><?php _e('Clicks', 'osclass_pay'); ?></div>
      <div class="osp-col spent"><?php _e('Spent', 'osclass_pay'); ?></div>
      <div class="osp-col budget"><?php _e('Budget', 'osclass_pay'); ?></div>
      <div class="osp-col status"><?php _e('Status', 'osclass_pay'); ?></div>
      <div class="osp-col add">&nbsp;</div>
    </div>

    <?php if(count($banners) > 0) { ?>
      <div class="osp-table-wrap">
        <?php foreach($banners as $b) { ?>
          <?php 
            if($b['i_ba_advert_id'] <> '' && $b['i_ba_advert_id'] > 0 && osp_plugin_ready('banner_ads')) {
              $a = ModelBA::newInstance()->getAdvert($b['i_ba_advert_id']);
              $spent = $a['i_views']*$a['d_price_view'] + $a['i_clicks']*$a['d_price_click'];
              $advert_exists = true;
            } else {
              $a = array();
              $spent = '-';
              $advert_exists = false;
            }
          ?>

          <div class="osp-row">
            <div class="osp-col name">
              <?php if($b['i_ba_advert_id'] <> '' && $b['i_ba_advert_id'] > 0 && osp_plugin_ready('banner_ads')) { ?>
                <a target="_blank" class="osp-has-tooltip" title="<?php echo osc_esc_html(__('Click here to show banner details', 'osclass_pay')); ?>" href="<?php echo osc_route_url('ba-advert', array('key' => $b['s_key']) ); ?>"><?php echo osc_highlight($b['s_name'], 24); ?></a>
              <?php } else { ?>
                <?php echo osc_highlight($b['s_name'], 24); ?>
              <?php } ?>

              <?php if($b['i_status'] > 0) { ?>
                <i class="fa fa-commenting osp-review-comment osp-has-tooltip" title="<?php echo osc_esc_html(__('Review comment', 'osclass_pay') . ': ' . ($b['s_comment'] <> '' ? $b['s_comment'] : '-')); ?>"></i>
              <?php } ?>
            </div>
            <div class="osp-col views">
              <?php echo isset($a['i_views']) ? $a['i_views'] . 'x' : '-'; ?>
              <span><?php echo isset($a['i_views']) ? '(' . osp_format_price($a['i_views']*$a['d_price_view']) . ')' : ''; ?></span>
            </div>
            <div class="osp-col clicks">
              <?php echo isset($a['i_clicks']) ? $a['i_clicks'] . 'x' : '-'; ?>
              <span><?php echo isset($a['i_clicks']) ? '(' . osp_format_price($a['i_clicks']*$a['d_price_click']) . ')' : ''; ?></span>
            </div>
            <div class="osp-col spent">
              <?php echo $advert_exists ? osp_format_price($spent) : '-'; ?>
              <span><?php echo $advert_exists ? '(' . round(($spent / ($b['d_budget'] > 0 ? $b['d_budget'] : 1))*100, 1) . '%)' : ''; ?></span>
            </div>
            <div class="osp-col budget"><?php echo osp_format_price($b['d_budget']); ?></div>
            <div class="osp-col status st<?php echo $b['i_status']; ?> sp<?php echo ($spent >= $b['d_budget'] ? 1 : 0); ?>">
              <?php 
                if($b['i_status'] == 0) {
                  $text = '<i class="fa fa-hourglass-o"></i> ' . __('Pending review', 'osclass_pay');
                  $title = __('Pending review by admin', 'osclass_pay');
                } else if($b['i_status'] == 1) {
                  $text = '<i class="fa fa-hourglass-half"></i> ' . __('Pending payment', 'osclass_pay');
                  $title = __('Pending payment by user', 'osclass_pay');
                } else if($b['i_status'] == 2) {
                  if($spent >= $b['d_budget']) {
                    $text = '<i class="fa fa-hand-stop-o"></i> ' . __('Budget spent', 'osclass_pay');
                    $title = __('Budget has been spent and banner is not active anymore', 'osclass_pay');
                  } else {
                    $text = '<i class="fa fa-check"></i> ' . __('Paid & Active', 'osclass_pay');
                    $title = __('Banner is paid and active, visible in front', 'osclass_pay');
                  }
                } else if($b['i_status'] == 9) {
                  $text = '<i class="fa fa-times"></i> ' . __('Rejected', 'osclass_pay');
                  $title = __('Rejected by admin, check review title for details', 'osclass_pay');
                }
              ?>

              <span class="osp-has-tooltip" title="<?php echo osc_esc_html($title); ?>">
                <?php echo $text; ?>
              </span>
            </div>
            <div class="osp-col add">
              <?php if($b['i_status'] == 1) { ?>
                <a class= "osp-banner-add-cart" href="<?php echo osp_cart_add(OSP_TYPE_BANNER, 1, $b['pk_i_id'], round($b['d_budget'], 2)); ?>"><?php _e('Pay now', 'osclass_pay'); ?></a>
              <?php } else { ?>
                &nbsp;
              <?php } ?>
            </div>
            <div class="osp-col remove">
              <a href="<?php echo osc_route_url('osp-banner-remove', array('removeId' => $b['pk_i_id']));?>" class="osp-has-tooltip" title="<?php echo osc_esc_html(__('Remove banner', 'osclass_pay')); ?>" onclick="return confirm('<?php echo osc_esc_html(__('Are you sure you want to delete this banner? Action cannot be undone.', 'osclass_pay')); ?>')"><i class="fa fa-trash-o"></i></a>
            </div>
          </div>
        <?php } ?>
      </div>
    <?php } else { ?>
      <div class="osp-row osp-row-empty">
        <i class="fa fa-warning"></i><span><?php _e('You have no banners', 'osclass_pay'); ?></span>
      </div>
    <?php } ?>
  </div>
</div>