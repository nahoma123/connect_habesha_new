<?php
  // Create menu
  $title = __('Banner & Advertisement', 'osclass_pay');
  osp_menu($title);

  $banner_allow = osp_param_update( 'banner_allow', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $banner_hook = osp_param_update( 'banner_hook', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $banner_fee_view = osp_param_update( 'banner_fee_view', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $banner_fee_click = osp_param_update( 'banner_fee_click', 'plugin_action', 'value', 'plugin-osclass_pay' );


  // UPDATE STATUS
  if(Params::getParam('what') == 'status' && Params::getParam('status') <> '' && Params::getParam('id') > 0) {
    Params::setParam('position', 2);

    $id = Params::getParam('id');
    $status = Params::getParam('status');
    $comment = Params::getParam('comment');
    $banner = ModelOSP::newInstance()->getBanner($id);


    if($status == 1) {

      ModelOSP::newInstance()->updateBannerStatus($id, $status, $comment);
      $banner['i_status'] = $status;
      $banner['s_comment'] = $comment;
      $cart_string = OSP_TYPE_BANNER . 'x1x' . $banner['pk_i_id'] . 'x' . round($banner['d_budget'], 2);
      osp_cart_update($banner['fk_i_user_id'], $cart_string);
      osp_email_banner($banner);
      message_ok( __('Banner approved successfully. Notification email to pay banner was send to customer.', 'osclass_pay') );

    } else if ($status == 9) {

      ModelOSP::newInstance()->updateBannerStatus($id, $status, $comment);
      $banner['i_status'] = $status;
      $banner['s_comment'] = $comment;
      osp_email_banner($banner);
      message_ok( __('Banner rejected successfully.', 'osclass_pay') );

    } else if($status == 10) {

      if(!osp_is_demo()) {
        ModelOSP::newInstance()->deleteBanner($id);
        message_ok( __('Banner removed successfully.', 'osclass_pay') );
      } else {
        message_info( __('This is demo site, you cannot remove banner.', 'osclass_pay') );
      }

    }
  }


  if(Params::getParam('plugin_action') == 'done') {
    message_ok( __('Settings were successfully saved', 'osclass_pay') );
  }


  // SCROLL TO DIV
  if(Params::getParam('position') == '1') {
    osp_js_scroll('.mb-setting');
  } else if(Params::getParam('position') == '2') {
    osp_js_scroll('.mb-banners');
  }
?>


<div class="mb-body">

  <!-- DEFAULT PARAMETERS -->
  <div class="mb-box mb-setting">
    <div class="mb-head"><i class="fa fa-cog"></i> <?php _e('Banner Settings', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>banner.php" />
        <input type="hidden" name="go_to_file" value="banner.php" />
        <input type="hidden" name="plugin_action" value="done" />
        <input type="hidden" name="position" value="1" />

        <?php if(!osp_plugin_ready('banner_ads')) { ?>
          <div class="mb-row mb-errors">
            <div class="mb-line"><?php _e('This functionality require usage of plugin BANNER ADS. You must install this plugin in order to use banner functionality.', 'osclass_pay'); ?></div>
          </div>
        <?php } ?>

        <?php if(osp_plugin_ready('banner_ads') && (osp_currency_symbol() <> osc_get_preference('currency', 'plugin-banner_ads') && osp_currency() <> osc_get_preference('currency', 'plugin-banner_ads'))) { ?>
          <div class="mb-row mb-notes">
            <div class="mb-line"><?php _e('Banner Ads plugin may use different currency, make sure to use same currency in both plugins to avoid missunderstandings.', 'osclass_pay'); ?></div>
          </div>
        <?php } ?>


        <div class="mb-row">
          <label for="banner_allow" class="h1"><span><?php _e('Enable Banners', 'osclass_pay'); ?></span></label> 
          <input name="banner_allow" id="banner_allow" class="element-slide" type="checkbox" <?php echo ($banner_allow == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, users can pay for banners.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="banner_hook" class="h4"><span><?php _e('Hook Advertise Button to Banner', 'osclass_pay'); ?></span></label> 
          <input name="banner_hook" id="banner_hook" class="element-slide" type="checkbox" <?php echo ($banner_hook == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, "Advertise here!" button is automatically hooked to each banner created by Banner Ads Plugin.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="banner_fee_view" class="h2"><span><?php _e('Fee for 1 view', 'osclass_pay'); ?></span></label> 
          <input size="10" name="banner_fee_view" id="banner_fee_view" class="mb-short" type="text" style="text-align:right;" value="<?php echo number_format((float)$banner_fee_view, 3, '.', ''); ?>" />
          <div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div>
        </div>


        <div class="mb-row">
          <label for="banner_fee_click" class="h3"><span><?php _e('Fee for 1 click', 'osclass_pay'); ?></span></label> 
          <input size="10" name="banner_fee_click" id="banner_fee_click" class="mb-short" type="text" style="text-align:right;" value="<?php echo number_format((float)$banner_fee_click, 3, '.', ''); ?>" />
          <div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div>
        </div>

        <div class="mb-row">&nbsp;</div>


        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
        </div>
      </form>
    </div>
  </div>



  <div class="mb-box mb-banners">
    <div class="mb-head"><i class="fa fa-newspaper-o"></i> <?php _e('Banners', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('Bellow are shown all banners ordered by status and creation date.', 'osclass_pay'); ?></div>
      </div>

      <div class="mb-table mb-table-banner">
        <div class="mb-table-head">
          <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-left"><?php _e('User', 'osclass_pay'); ?></div>
          <div class="mb-col-3 mb-align-left"><?php _e('Banner Name', 'osclass_pay'); ?></div>
          <div class="mb-col-2"><?php _e('BA ID', 'osclass_pay'); ?></div>
          <div class="mb-col-2"><?php _e('Key', 'osclass_pay'); ?></div>
          <div class="mb-col-2"><?php _e('1 view fee', 'osclass_pay'); ?></div>
          <div class="mb-col-2"><?php _e('1 click fee', 'osclass_pay'); ?></div>
          <div class="mb-col-2"><?php _e('Budget', 'osclass_pay'); ?></div>
          <div class="mb-col-4 mb-align-left"><?php _e('Status', 'osclass_pay'); ?></div>
          <div class="mb-col-4 mb-align-left"><?php _e('Action', 'osclass_pay'); ?></div>
        </div>

        <?php $banners = ModelOSP::newInstance()->getBanners(); ?>

        <?php if(count($banners) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No banners has been found', 'osclass_pay'); ?></span>
          </div>
        <?php } else { ?>
          <?php foreach($banners as $b) { ?>
            <?php
              $user = User::newInstance()->findByPrimaryKey($b['fk_i_user_id']);
              $utitle = osc_esc_html(@$user['s_name'] . '<br/>' . @$user['s_email'] . '<br/>' . __('Reg. date', 'osclass_pay') . ': ' . @$user['dt_reg_date']);
            ?>

            <div class="mb-table-row">
              <div class="mb-line mb-top-line">
                <div class="mb-col-1"><?php echo $b['pk_i_id']; ?></div>
                <div class="mb-col-2 mb-align-left mb-has-tooltip-light mb-banner-mail" title="<?php echo osc_esc_html($utitle); ?>">
                  <?php if(isset($user['pk_i_id']) && $user['pk_i_id'] > 0) { ?>
                    <a href="<?php echo osc_admin_base_url(true); ?>?page=users&action=edit&id=<?php echo $user['pk_i_id']; ?>" target="_blank"><?php echo $user['s_name']; ?></a>
                  <?php } else { ?>
                    <em>- <?php echo sprintf(__('User #%d removed', 'osclass_pay'), $b['fk_i_user_id']); ?> -</em>
                  <?php } ?>
                </div>
                
                <div class="mb-col-3 mb-align-left mb-banner-name mb-has-tooltip-light" title="<?php echo osc_esc_html($b['s_name']); ?>"><?php echo $b['s_name']; ?></div>
                <div class="mb-col-2 mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to show linked advert in Banner Ads Plugin', 'osclass_pay')); ?>">
                  <?php if($b['i_ba_advert_id'] <> '' && $b['i_ba_advert_id'] > 0 && osp_plugin_ready('banner_ads')) { ?>
                    <a target="_blank" href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=banner_ads/admin/advert_edit.php&advertId=<?php echo $b['i_ba_advert_id']; ?>"><?php echo __('Open Advert', 'osclass_pay'); ?></a>
                  <?php } else { ?>
                    -
                  <?php } ?>
                </div>
                
                <div class="mb-col-2 mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Click to show banner stats', 'osclass_pay')); ?>">
                  <?php if($b['i_ba_advert_id'] <> '' && $b['i_ba_advert_id'] > 0 && osp_plugin_ready('banner_ads')) { ?>
                    <a target="_blank" href="<?php echo osc_route_url('ba-advert', array('key' => $b['s_key']) ); ?>"><?php echo $b['s_key']; ?></a>
                  <?php } else { ?>
                    <?php echo $b['s_key']; ?>
                  <?php } ?>
                </div>
                <div class="mb-col-2"><?php echo osp_format_price($b['d_price_view'], 1, '', 4); ?></div>
                <div class="mb-col-2"><?php echo osp_format_price($b['d_price_click'], 1, '', 4); ?></div>
                <div class="mb-col-2"><?php echo osp_format_price($b['d_budget']); ?></div>

                <div class="mb-col-4 mb-bt-status mb-align-left">
                  <span class="st<?php echo $b['i_status']; ?>">
                    <?php
                      if($b['i_status'] == 0) {
                        echo '<i class="fa fa-hourglass-o"></i> ' . __('Pending review', 'osclass_pay');
                      } else if($b['i_status'] == 1) {
                        echo '<i class="fa fa-hourglass-half"></i> ' . __('Pending payment', 'osclass_pay');
                      } else if($b['i_status'] == 2) {
                        echo '<i class="fa fa-check"></i> ' . __('Paid', 'osclass_pay');
                      } else {
                        echo '<i class="fa fa-times"></i> ' . __('Rejected', 'osclass_pay');
                      }
                    ?>
                  </span>
                </div>

                <div class="mb-col-4 mb-bt-buttons">
                  <?php if($b['i_status'] == 0 || $b['i_status'] == 9) { ?>
                    <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=osclass_pay/admin/banner.php&what=status&status=1&id=<?php echo $b['pk_i_id']; ?>" class="mb-banner-accept mb-button-green mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Approve banner', 'osclass_pay')); ?>"><i class="fa fa-check"></i></a>
                    <form name="promo_form" id="promo_form" class="mb-banner-comment mb-approve-form" style="display:none;" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
                      <input type="hidden" name="page" value="plugins" />
                      <input type="hidden" name="action" value="renderplugin" />
                      <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>banner.php" />
                      <input type="hidden" name="go_to_file" value="banner.php" />
                      <input type="hidden" name="what" value="status" />
                      <input type="hidden" name="id" value="<?php echo $b['pk_i_id']; ?>" />
                      <input type="hidden" name="status" value="1" />
                      <input type="hidden" name="position" value="2" />

                      <input type="text" name="comment" id="comment" placeholder="<?php echo osc_esc_html(__('Comment for author (optional) ...', 'osclass_pay')); ?>"/>
                      <button type="submit"><?php echo __('Approve', 'osclass_pay'); ?></button>
                    </form>
                  <?php } ?>

                  <?php if($b['i_status'] == 0) { ?>
                    <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=osclass_pay/admin/banner.php&what=status&status=9&id=<?php echo $b['pk_i_id']; ?>" class="mb-banner-reject mb-button-white mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Reject banner', 'osclass_pay')); ?>"><i class="fa fa-times"></i></a>

                    <form name="promo_form" id="promo_form" class="mb-banner-comment mb-reject-form" style="display:none;" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
                      <input type="hidden" name="page" value="plugins" />
                      <input type="hidden" name="action" value="renderplugin" />
                      <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>banner.php" />
                      <input type="hidden" name="go_to_file" value="banner.php" />
                      <input type="hidden" name="what" value="status" />
                      <input type="hidden" name="id" value="<?php echo $b['pk_i_id']; ?>" />
                      <input type="hidden" name="status" value="9" />
                      <input type="hidden" name="position" value="2" />

                      <input type="text" name="comment" id="comment" placeholder="<?php echo osc_esc_html(__('Comment for author (optional) ...', 'osclass_pay')); ?>"/>
                      <button type="submit"><?php echo __('Reject', 'osclass_pay'); ?></button>
                    </form>
                  <?php } ?>

                  <?php if($b['i_status'] <> 0) { ?>
                    <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=osclass_pay/admin/banner.php&what=status&status=10&id=<?php echo $b['pk_i_id']; ?>" class="mb-banner-remove mb-button-red mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Remove banner', 'osclass_pay')); ?>" onclick="return confirm('<?php echo osc_esc_js(__('Are you sure you want to remove this banner? Action cannot be undone.', 'osclass_pay')); ?>')"><i class="fa fa-trash"></i></a>
                  <?php } ?>

                  <div class="mb-expand">
                    <i class="fa fa-angle-down "></i>
                    <span><?php _e('Details', 'osclass_pay'); ?></span>
                  </div>
                </div>
              </div>

              <div class="mb-line mb-next-line">
                <div class="mb-col-6">
                  <div class="label"><?php _e('Banner code', 'osclass_pay'); ?></div>
                  <span class="mb-banner-code"><?php echo htmlspecialchars($b['s_code']); ?></span>
                </div>

                <div class="mb-col-9">
                  <div class="label"><?php _e('Showcase', 'osclass_pay'); ?></div>
                  <span class="mb-banner-showcase">
                    <?php 
                      // echo osp_closetags($b['s_code']);  // this does not work OK
                      echo htmlentities($b['s_code']);
                    ?>
                  </span>
                </div>

                <div class="mb-col-3">
                  <div class="label"><?php _e('Visible in categories', 'osclass_pay'); ?></div>
                  <span class="mb-banner-category">
                   <?php
                     if(trim($b['s_category']) == '') {
                       echo '<span>' . __('All categories', 'osclass_pay') . '</span>';
                     } else {
                       $cat_array = explode(',', $b['s_category']);
                       $cat_array = array_filter($cat_array);

                       if(count($cat_array) > 0) {
                         foreach($cat_array as $c) {
                           $c = trim($c);
                           $cat = Category::newInstance()->findByPrimaryKey($c);
                           echo '<span>' . $cat['s_name'] . ' (id ' . $cat['pk_i_id'] . ')</span>';
                         }
                       } else {
                         echo '<span>' . __('All categories', 'osclass_pay') . '</span>';
                       }
                     }
                   ?>
                  </span>
                </div>
                
                <div class="mb-col-6 mb-banner-list">
                  <div class="mb-banner-entry e1">
                    <div class="label"><?php _e('On Click URL', 'osclass_pay'); ?></div>
                    <div class="mb-entry-data mb-has-tooltip-light" title="<?php echo osc_esc_html($b['s_url']); ?>"><?php echo $b['s_url']; ?></div>
                  </div>

                  <div class="mb-banner-entry e2">
                    <div class="label"><?php _e('Banner size (width x height)', 'osclass_pay'); ?></div>
                    <div class="mb-entry-data"><?php echo $b['s_size_width']; ?> x <?php echo $b['s_size_height']; ?></div>
                  </div>

                  <div class="mb-banner-entry e3">
                    <div class="label"><?php _e('Reviewer comment', 'osclass_pay'); ?></div>
                    <div class="mb-entry-data mb-has-tooltip-light" title="<?php echo $b['s_comment']; ?>"><?php echo $b['s_comment'] <> '' ? $b['s_comment'] : '-'; ?></div>
                  </div>
                </div>
              </div>
            </div>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
  </div>




  <!-- PLUGIN INTEGRATION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-wrench"></i> <?php _e('Plugin Setup', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row">
        <div class="mb-line"><?php _e('If you have disabled "Hook Advertise Button to Banner", you must add button to submit user banner manually to theme files.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('Make sure you understand basic terms. Banner created in Osclass Pay Plugin will result in creating Advert in Banner Ads Plugin. Term Banner in Banner Ads Plugin is container for showing adverts.', 'osclass_pay'); ?></div>
        <div class="mb-line">
          <?php _e('First create banner in Banner Ads Plugin that will be container for client buttons. Follow guide in this plugin how to show banners in theme. You can create banner on following link:', 'osclass_pay'); ?><br/>
          <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=banner_ads/admin/banners.php" target="_blank"><?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=banner_ads/admin/banners.php</a>
        </div>
        <div class="mb-line"><?php _e('When banner is created, get it\'s ID and to show "Advertise Here!" button in your theme, place anywhere you like or need following code.', 'osclass_pay'); ?></div>

        <span class="mb-code">&lt;?php if(function_exists('osp_banner_button')) { osp_banner_button({banner_id}); } ?&gt;</span>

        <div class="mb-line"><br/><?php _e('Make sure to replace {banner_id} with ID of your banner. Example:', 'osclass_pay'); ?> osp_banner_button(7);</div>
      </div>
    </div>
  </div>


  <!-- HELP TOPICS -->
  <div class="mb-box" id="mb-help">
    <div class="mb-head"><i class="fa fa-question-circle"></i> <?php _e('Help', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-help"><span class="sup">(1)</span> <div class="h1"><?php echo sprintf(__('When enabled, users can %s their listings.', 'osclass_pay'), __('make premium', 'osclass_pay')); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(2)</span> <div class="h2"><?php _e('Price for 1 view of banner.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(3)</span> <div class="h3"><?php _e('Price for 1 click on banner.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(4)</span> <div class="h4"><?php _e('Enable to add "Advertise here!" button under each banner created by Banner Ads Plugin. No theme modifications are required, however it can break design of banners. If it does not work for you, check setup guide above.', 'osclass_pay'); ?></div></div>
    </div>
  </div>
</div>

<?php echo osp_footer(); ?>