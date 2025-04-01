<?php 

  // WARNING FLASH MESSAGES ARE CREATED IN INDEX.PHP TO ADD THEM INTO HEADER ON SAME PAGE

  $item_id = Params::getParam('itemId');
  $item = Item::newInstance()->findByPrimaryKey($item_id);
  
  if(!$item || !isset($item['pk_i_id']) || $item['pk_i_id'] <= 0) {
    osc_add_flash_error_message(__('Item does not exist!', 'osclass_pay'));
    osp_redirect(osc_base_url());
  }
  
  $is_publish = Params::getParam('isPublish');   // item was just published == 1
  $currency = osp_currency();

  $types = array(OSP_TYPE_PUBLISH, OSP_TYPE_IMAGE, OSP_TYPE_TOP, OSP_TYPE_PREMIUM, OSP_TYPE_HIGHLIGHT, OSP_TYPE_REPUBLISH);


  if(osc_is_web_user_logged_in()) {
    $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
  } else if($item['fk_i_user_id'] > 0) {
    $user = User::newInstance()->findByPrimaryKey($item['fk_i_user_id']);
  } else {
    $user = array('pk_i_id' => 0, 's_name' => $item['s_contact_name'], 's_email' => $item['s_contact_email']);
  }

  $publish_record = osp_get_fee_record(OSP_TYPE_PUBLISH, $item_id, 0);
 
  if(Params::getParam('removeType') <> '') {
    ModelOSP::newInstance()->deleteItem(Params::getParam('removeType'), Params::getParam('itemId'));
    osc_add_flash_ok_message(__('Promotion removed', 'osclass_pay'));
    osp_redirect(osc_route_url('osp-item-pay', array('itemId' => $item_id)));
  }


  if(Params::getParam('manage_promote') == 1) {
    osc_add_flash_ok_message(__('Item promotions has been updated', 'osclass_pay'));
    osp_item_promote_manage();
    osp_redirect(osc_route_url('osp-item-pay', array('itemId' => $item_id)));
  }


  // CHECK IF PAY-PER-PUBLISH IS NOT PAID AND REQUIRED
  $publish_pay = false;
  if(
    osp_fee_is_allowed(OSP_TYPE_PUBLISH) 
    && osp_fee_exists(OSP_TYPE_PUBLISH, $item_id) 
    && !osp_fee_is_paid(OSP_TYPE_PUBLISH, $item_id)
    && osp_get_fee(OSP_TYPE_PUBLISH, 1, $item_id) > 0
  ) {
    $publish_pay = true;
  }
?>

<div class="osp-body osp-body-itempay">
  <?php if((osc_is_web_user_logged_in() && osc_logged_user_id() == $item['fk_i_user_id'] || !osc_is_web_user_logged_in()) || isset($publish_record['i_item_id']) || osc_is_admin_user_logged_in()) { ?>

    <div class="osp-manage-top">
      <?php if($is_publish == 1) { ?>
        <?php if(!$publish_pay) { ?>
          <div class="osp-h1"><?php _e('Your listing was published successfully, you can now promote it and make it more visible.', 'osclass_pay'); ?></div>
        <?php } else { ?>
          <div class="osp-h1"><?php _e('Your listing was published successfully, however in order to make it visible you must pay publish fee. You can also promote this listing and make it more visible.', 'osclass_pay'); ?></div>
        <?php } ?>

      <?php } else { ?>
        <?php if(!$publish_pay) { ?>
          <div class="osp-h1"><?php _e('Promote your listing and make it more visible.', 'osclass_pay'); ?></div>
        <?php } else { ?>
          <div class="osp-h1"><?php _e('This category is paid and is required to pay publish fee, you can also promote your listing and make it more visible.', 'osclass_pay'); ?></div>
        <?php } ?>
      <?php } ?>

      <div class="osp-h2"><?php echo sprintf(__('If you would like to change promotion options, you can do it in section below or login to your account and promote listing in %s of user account.', 'osclass_pay'), '<a href="' . osc_route_url('osp-item') . '">' . __('Promotions section', 'osclass_pay') . '</a>'); ?></div>
 
      <?php if(!isset($publish_record['i_item_id']) || (isset($publish_record['i_item_id']) && osp_fee_is_paid(OSP_TYPE_PUBLISH, $item_id))) { ?>
        <?php View::newInstance()->_exportVariableToView('item', Item::newInstance()->findByPrimaryKey($item_id)); ?>
        <a class="osp-top-button" href="<?php echo osc_item_url(); ?>"><?php _e('Open listing', 'osclass_pay'); ?> <i class="fa fa-link"></i></a>
      <?php } ?>
    </div>

    
    <?php osp_show_itempay_promote($item); ?>

    <?php osc_run_hook('osp_itempay_before_cart_items'); ?>


    <div class="osp-cart">
      <div class="osp-cart-head-row">
        <div class="osp-cart-col code"><?php _e('ID', 'osclass_pay'); ?></div>
        <div class="osp-cart-col prod"><?php _e('Product', 'osclass_pay'); ?></div>
        <div class="osp-cart-col qty"><?php _e('Qty', 'osclass_pay'); ?></div>
        <div class="osp-cart-col pric"><?php _e('Total Price', 'osclass_pay'); ?></div>
        <div class="osp-cart-col delt">&nbsp;</div>
      </div>

      <?php 
        $i = 1; 
        $total = 0; 
        $count = 0; 
      ?>

      <?php foreach($types as $type) { ?>
        <?php if(osp_fee_is_allowed($type) && osp_fee_exists($type, $item_id) && !osp_fee_is_paid($type, $item_id)) { ?>

          <?php 
            $record = osp_get_fee_record($type, $item_id, 0);
            $price = osp_get_fee($type, 1, $item_id, $record['i_hours'], $record['i_repeat']);
          ?>

          <?php if($price > 0) { ?>
            <?php
              $total = $total + $price;
              $count = $count + 1;
              $code_array = array('', $type, 1, $item_id, $record['i_hours'], $record['i_repeat']);
              $code = array_filter($code_array);
              $code = implode('x', $code);
            ?>

         
            <div class="osp-cart-row" data-code="<?php echo $code; ?>" data-row-id="<?php echo $i; ?>">
              <div class="osp-cart-col code"><?php echo $i; ?></div>
              <div class="osp-cart-col prod">
                <span class="p1 osp-<?php echo $type; ?>"><?php echo osp_product_type_name($type); ?></span>
                <span class="px">
                  <span class="p2"><?php echo osp_product_cart_name($code_array); ?></span>
                  <span class="p3"><?php echo osp_product_cart_text($type); ?></span>
                </span>
              </div>
              <div class="osp-cart-col qty">1x</div>
              <div class="osp-cart-col pric"><?php echo osp_format_price($price); ?></div>

              <div class="osp-cart-col delt">
                <?php if($type <> OSP_TYPE_PUBLISH) { ?>
                  <a href="<?php echo osc_route_url('osp-item-pay-remove', array('removeType' => $type, 'itemId' => $item_id));?>"><i class="fa fa-trash-o"></i></a>
                <?php } else { ?>
                  <a class="osp-disabled osp-has-tooltip" href="#" onclick="return false;" title="<?php echo osc_esc_html(__('You cannot remove this fee as it is required', 'osclass_pay')); ?>"><i class="fa fa-trash-o"></i></a>
                <?php } ?>
              </div>
            </div>

            <?php $i++; ?>
          <?php } ?>
        <?php } ?>
      <?php } ?>

      <?php if($count > 0) { ?>
        <div class="osp-cart-row osp-cart-total">
          <div class="osp-cart-col code">&nbsp;</div>
          <div class="osp-cart-col prod"><?php _e('Cart summary', 'osclass_pay'); ?></div>
          <div class="osp-cart-col qty"><?php echo $count; ?>x</div>
          <div class="osp-cart-col pric"><span><?php echo osp_format_price($total); ?></span></div>
        </div>
      <?php } else { ?>
        <div class="osp-cart-row osp-cart-empty">
          <i class="fa fa-warning"></i><span><?php _e('No promotions has been selected', 'osclass_pay'); ?></span>
        </div>
      <?php } ?>
    </div>

    <?php osc_run_hook('osp_itempay_before_pay_buttons'); ?>


    <?php if($total > 0) { ?>
      <label class="osp-pay-label"><?php _e('Click on method to initiate payment', 'osclass_pay'); ?></label>
      <ul class="osp-pay-button">
        <?php 
          $checksum = osp_create_checksum($item_id, @$user['pk_i_id'], @$user['s_email'], round($total, 2));
        
          if(osc_is_admin_user_logged_in() && osc_apply_filter('osp_itempay_payment_admin', true) !== false) {
            osp_admin_button(round($total, 2), sprintf(__('Pay fee %s for item %s by admin', 'osclass_pay'), osp_format_price($total, 2), $item_id), '901x2x'.$item_id.'x'.round($total, 2), array('user' => @$user['pk_i_id'], 'itemid' => $item_id, 'email' => @$user['s_email'], 'amount' => round($total, 2)));
          }

          if(osp_param('bt_enabled') == 1 && osc_apply_filter('osp_itempay_payment_transfer', true) !== false) {
            osp_transfer_button(round($total, 2), sprintf(__('Pay fee %s for item %s', 'osclass_pay'), osp_format_price($total, 2), $item_id), '901x2x'.$item_id.'x'.round($total, 2), array('user' => @$user['pk_i_id'], 'itemid' => $item_id, 'name' => @$user['s_name'], 'email' => @$user['s_email'], 'amount' => round($total, 2), 'checksum' => $checksum));
          }

          if(osc_is_web_user_logged_in()) {
            if(osc_apply_filter('osp_itempay_payment_wallet', true) !== false) {
              osp_wallet_button(round($total, 2), sprintf(__('Pay fee %s for item %s', 'osclass_pay'), osp_format_price($total, 2), $item_id), '901x2x'.$item_id.'x'.round($total, 2), array('user' => @$user['pk_i_id'], 'itemid' => $item_id, 'email' => @$user['s_email'], 'amount' => round($total, 2)));
            }
            
            if(osc_apply_filter('osp_itempay_payment_money', true) !== false) {
              osp_buttons(round($total, 2), sprintf(__('Pay fee %s for item %s', 'osclass_pay'), osp_format_price($total, 2), $item_id), '901x2x'.$item_id, array('user' => @$user['pk_i_id'], 'itemid' => $item_id, 'email' => @$user['s_email'], 'amount' => round($total, 2)));
            }
          } else {
            if(osc_apply_filter('osp_itempay_payment_money', true) !== false) {
              osp_buttons(round($total, 2), sprintf(__('Pay fee %s for item %s', 'osclass_pay'), osp_format_price($total, 2), $item_id), '901x2x'.$item_id, array('user' => $item['fk_i_user_id'], 'itemid' => $item_id, 'email' => $item['s_contact_email'], 'amount' => round($total, 2)));
            }
          }
        ?>
      </ul>
    <?php } ?>

    <?php osc_run_hook('osp_itempay_after_pay_buttons'); ?>

  <?php } else { ?>
    <div class="osp-h1"><?php _e('There was problem showing promote options', 'osclass_pay'); ?></div>
    <div class="osp-h2"><?php _e('This is not your listing or it has not been activated yet.', 'osclass_pay'); ?></div>
    </br>
  <?php } ?>
</div>


<?php osp_buttons_js(); ?>


<?php 
  if($count <= 0 && $item_id > 0) {
    //View::newInstance()->_exportVariableToView('item', Item::newInstance()->findByPrimaryKey($item_id));
    //osc_add_flash_ok_message(__('No promotions has been selected. Promote listing from it\'s page or from Promote section in user account.', 'osclass_pay'));
    //osc_redirect_to(osc_item_url());
  } 
?>