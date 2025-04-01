<?php
  osp_user_menu('cart');
  $user_id = osc_logged_user_id();
  $user = User::newInstance()->findByPrimaryKey($user_id);
  $product = urldecode(Params::getParam('product'));
  $remove = urldecode(Params::getParam('remove'));
  $contains_pack = false;
  $contains_product = false;
  $contains_offer = false;
  $contains_auction = false;
  $contains_auction_buyout = false;
  $contains_booking = false;
  $shipping_error = array();
  $address_missing = false;
  $phone_missing = false;

  if($product <> '') {
    osp_cart_update($user_id, $product);
    osc_add_flash_ok_message(__('Cart updated', 'osclass_pay'));
    osp_redirect(osc_route_url('osp-cart'));
    exit;
  }

  if($remove <> '') {
    osp_cart_remove($user_id, $remove);
    osc_add_flash_ok_message(__('Product removed from cart', 'osclass_pay'));
    osp_redirect(osc_route_url('osp-cart'));
    exit;
  }


  // PROCESS VOUCHER
  if(Params::getParam('ospAction') == 'voucher') {
    if(Params::getParam('voucher') <> '') {
      $voucher_valid = osp_check_voucher_code(Params::getParam('voucher'));

      if($voucher_valid['error'] <> 'OK') {
        osc_add_flash_error_message(sprintf(__('Voucher %s has not been added into cart: %s', 'osclass_pay'), '<u>' . Params::getParam('voucher') . '</u>', $voucher_valid['message']));
        osp_redirect(osc_route_url('osp-cart'));
        exit;

      } else {
        $voucher = ModelOSP::newInstance()->getVoucherByCode(Params::getParam('voucher'));
        osp_cart_update($user_id, OSP_TYPE_VOUCHER . 'x1x' . $voucher['pk_i_id']);          // If there is voucher in cart, it will be removed & replaced

        osc_add_flash_ok_message(sprintf(__('Voucher %s has been added into cart: %s', 'osclass_pay'), '<u>' . Params::getParam('voucher') . '</u>', $voucher_valid['message']));
        osp_redirect(osc_route_url('osp-cart'));
        exit;

      }
    }
  }


  $cart = osp_cart_content($user_id);


 
  // Check if we have still some shippings in cart
  $has_shipping = false;
  foreach($cart as $c) {
    if($c[1] == OSP_TYPE_SHIPPING) {
      $has_shipping = true;
      break;
    }
  }


  // foreach($cart as $c) {
    // if($c[1] == OSP_TYPE_SHIPPING) {
      // $has_shipping = true;
      // break;
    // }
  // }
    
  //print_r($req_shippings); 


  if($remove <> '') {
    osp_cart_remove($user_id, $remove);
    osc_add_flash_ok_message(__('Product removed from cart', 'osclass_pay'));
    osp_redirect(osc_route_url('osp-cart'));
    exit;
  }
  
  $cart_string = ModelOSP::newInstance()->getCart($user_id);
  $cart = osp_cart_content($user_id);
  
  //print_r($cart);
?>

<div class="osp-body osp-body-cart">
  <div class="osp-h1">
    <?php if(count($cart) > 0) { ?>
      <?php echo sprintf(__('You have %s products in your cart', 'osclass_pay'), count($cart)); ?>
    <?php } else { ?>
      <?php _e('Your cart is empty', 'osclass_pay'); ?>
    <?php } ?>
  </div>

  <?php osc_run_hook('osp_cart_before_cart_items'); ?>

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

    <?php if(count($cart) > 0) { ?>
      <div class="osp-table-wrap-cart">
        <?php foreach($cart as $c) { ?>
          <?php 
            $price = osp_get_fee($c[1], isset($c[2]) ? $c[2] : '', isset($c[3]) ? $c[3] : '', isset($c[4]) ? $c[4] : '', isset($c[5]) ? $c[5] : '');
            
            if($price <= 0 && $c[1] != OSP_TYPE_VOUCHER) {
              osp_cart_remove(osc_logged_user_id(), implode('x', $c));
              osc_add_flash_warning_message(__('Some products has been removed from your cart due to incorrect price', 'osclass_pay'));
              osp_redirect(osc_route_url('osp-cart'));
            }
            
            if($c[1] == OSP_TYPE_BOOKING) {
              $reservation = ModelOSP::newInstance()->getBooking(@$c[3]);

              if($reservation === false || $reservation['b_paid'] == 1) {
                osp_cart_remove($user_id, $c);
                osc_add_flash_warning_message(__('Booking/Reservation has already been paid or it was removed. Booking/Reservation has been removed from cart.', 'osclass_pay'));
                osp_redirect(osc_route_url('osp-cart'));

              }
            }
            
            $total = $total + $price;
            $count = $count + ($c[1] == OSP_TYPE_VOUCHER ? 0 : $c[2]);

            if($c[1] == OSP_TYPE_PACK) {
              $contains_pack = true;
            }

            if($c[1] == OSP_TYPE_PRODUCT) {
              $contains_product = true;
            }

            if($c[1] == OSP_TYPE_OFFER) {
              $contains_offer = true;
            }
            
            if($c[1] == OSP_TYPE_AUCTION_PAY || $c[1] == OSP_TYPE_AUCTION_BUYOUT) { // || $c[1] == OSP_TYPE_AUCTION_FEE) {
              $contains_auction = true;
            }
            
            if($c[1] == OSP_TYPE_AUCTION_BUYOUT) {
              $contains_auction_buyout = true;
            }
            
            
            if($c[1] == OSP_TYPE_BOOKING) {
              $contains_booking = true;
            }

            if($c[1] == OSP_TYPE_VOUCHER) {
              $voucher_valid = osp_check_voucher_id($c[3]);
              
              if($voucher_valid['error'] <> 'OK') {
                $voucher = ModelOSP::newInstance()->getVoucher($c[3]);

                osp_cart_remove($user_id, implode('x', $c));
                osc_add_flash_error_message(sprintf(__('Voucher %s removed from cart: %s', 'osclass_pay'), ($voucher['s_code'] <> '' ? $voucher['s_code'] : '----'), $voucher_valid['message']));
                osp_redirect(osc_route_url('osp-cart'));
                exit;
              }
            }
          ?>
          
          <div class="osp-cart-row" data-code="<?php echo $c[0]; ?>" data-row-id="<?php echo $i; ?>">
            <div class="osp-cart-col code"><?php echo $i; ?></div>
            <div class="osp-cart-col prod t<?php echo $c[1]; ?>">
              <span class="p1 osp-<?php echo $c[1]; ?>">
                <?php if($c[1] == OSP_TYPE_MEMBERSHIP) { ?>
                  <?php $group = ModelOSP::newInstance()->getGroup($c[3]); ?>
                  <i class="fa fa-star" style="color:<?php echo $group['s_color']; ?>;"></i>
                <?php } else if($c[1] == OSP_TYPE_PACK) { ?>
                  <?php $pack = ModelOSP::newInstance()->getPack($c[3]); ?>
                  <i class="fa fa-tag" style="color:<?php echo $pack['s_color']; ?>;"></i>
                <?php } ?>

                <?php echo osp_product_type_name($c[1]); ?>
              </span>
              <span class="p2"><?php echo osp_product_cart_name($c); ?></span>
            </div>
            <div class="osp-cart-col osp-has-tooltip qty <?php if(osp_quantity_editable($c[1])) { ?>osp-editable<?php } ?>" <?php if(osp_quantity_editable($c[1])) { ?>title="<?php echo osc_esc_html(__('Click to update quantity', 'osclass_pay')); ?>"<?php } ?>><span><?php echo ($c[1] == OSP_TYPE_VOUCHER ? '' : $c[2] . 'x'); ?></span></div>
            <div class="osp-cart-col pric <?php if($c[2] > 1) { ?>has-unit osp-has-tooltip<?php } ?>" <?php if($c[2] > 1) { ?>title="<?php echo osc_esc_html(__('Unit price', 'osclass_pay') . ': ' . osp_format_price($price/$c[2])); ?>"<?php } ?>><?php echo osp_format_price($price); ?></div>

            <div class="osp-cart-col delt">
              <?php if($c[1] == OSP_TYPE_SHIPPING) { ?>
                <a href="#" onclick="return false;" class="osp-disabled osp-has-tooltip" title="<?php echo osc_esc_html(__('Shipping is required and cannot be removed. If you remove all products those require shipping, shipping will be removed from cart automatically', 'osclass_pay')); ?>"><i class="fa fa-trash-o"></i></a>
              <?php } else { ?>
                <a href="<?php echo osc_route_url('osp-cart-remove', array('remove' => $c[0]));?>"><i class="fa fa-trash-o"></i></a>
              <?php } ?>
            </div>
          </div>

          <?php $i++; ?>
        <?php } ?>
      </div>

      <?php 
       if($total < 0) {
         $total = 0;
       }
      ?>

      <div class="osp-cart-row osp-cart-total">
        <div class="osp-cart-col code">&nbsp;</div>
        <div class="osp-cart-col prod"><?php _e('Cart summary', 'osclass_pay'); ?></div>
        <div class="osp-cart-col qty"><?php echo $count; ?>x</div>
        <div class="osp-cart-col pric"><span><?php echo osp_format_price($total); ?></span></div>
      </div>

    <?php } else { ?>
      <div class="osp-cart-row osp-cart-empty">
        <i class="fa fa-warning"></i><span><?php _e('No products in your cart', 'osclass_pay'); ?></span>
      </div>
    <?php } ?>
  </div>
</div>

<?php if(osp_vouchers_enabled()) { ?>
  <div class="osp-voucher-box">
    <div class="osp-voucher-inside">
      <form action="<?php echo osc_route_url('osp-cart'); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="ospAction" value="voucher" />

        <label for="voucher"><?php _e('Add a promo code', 'osclass_pay'); ?></label>
        <input type="text" id="voucher" name="voucher" placeholder="<?php echo osc_esc_html(__('Add a promo code', 'osclass_pay')); ?>"/>

        <button type="submit"><?php _e('Apply', 'osclass_pay'); ?></button>
      </form>
    </div>
  </div>
<?php } ?>

<?php if($has_shipping === true) { ?>
  <?php 
    $address = trim(implode(', ', array_filter(array($user['s_country'], $user['s_region'], $user['s_city'], $user['s_city_area'], $user['s_zip'], $user['s_address']))));

    if($user['s_city'] == '' || $user['s_address'] == '') {
      $address_missing = true;
    }
    
    if($user['s_city'] == '') { $shipping_error[] = __('City is missing', 'osclass_pay'); }
    //if($user['s_zip'] == '') { $shipping_error[] = __('ZIP code is missing', 'osclass_pay'); }
    if($user['s_address'] == '') { $shipping_error[] = __('Address is missing', 'osclass_pay'); }
    
    $phone = (trim($user['s_phone_mobile']) <> '' ? $user['s_phone_mobile'] : $user['s_phone_land']);
    
    if($phone == '') {
      $phone_missing = true;
      $shipping_error[] = __('Phone number is missing, you must enter at least one phone number (mobile or land)', 'osclass_pay');
    }

    $shipping_error = trim(implode(', ', $shipping_error));
  ?>
  
  <?php if($address_missing || $phone_missing) { ?>
    <div class="osp-pay-err">
      <?php _e('One or more products in your cart require shipping. In order to complete order, you must fill city, address and at least 1 phone number in your profile.', 'osclass_pay'); ?><br/>
      <strong><?php _e('Errors:', 'osclass_pay'); ?></strong> <?php echo $shipping_error; ?>.<br/>
      <a href="<?php echo osc_user_profile_url(); ?>"><?php _e('Update profile', 'osclass_pay'); ?></a>
    </div>
  <?php } else { ?>
    <div class="osp-pay-msg dlvr">
      <?php _e('One or more products in your cart require shipping, these will be shipped to:', 'osclass_pay'); ?><br/>
      <strong>
        <?php echo $user['s_name']; ?><br/>
        <?php echo $phone; ?><br/>
        <?php echo $address; ?>
      </strong>
    </div>
  <?php } ?>
<?php } ?>

<?php $wallet = osp_get_wallet($user_id); ?>

<?php if(isset($wallet['formatted_amount']) && $wallet['formatted_amount'] >= 0 && $contains_pack && $total > 0) { ?>
  <div class="osp-pay-msg"><?php _e('You cannot pay with your credits as your cart contains Credit Pack. In order to pay with your credits, please remove Credit Pack from your cart.', 'osclass_pay'); ?></div>
<?php } ?>

<?php if(isset($wallet['formatted_amount']) && $wallet['formatted_amount'] >= 0 && $contains_product && $total > 0) { ?>
  <div class="osp-pay-msg"><?php _e('You cannot pay with your credits as your cart contains Product. Payments for products using credits are not allowed.', 'osclass_pay'); ?></div>
<?php } ?>

<?php if(isset($wallet['formatted_amount']) && $wallet['formatted_amount'] >= 0 && $contains_booking && $total > 0) { ?>
  <div class="osp-pay-msg"><?php _e('You cannot pay with your credits as your cart contains Booking/Reservation. Payments for bookings/reservations using credits are not allowed.', 'osclass_pay'); ?></div>
<?php } ?>

<?php if(isset($wallet['formatted_amount']) && $wallet['formatted_amount'] >= 0 && $contains_auction && $total > 0) { ?>
  <div class="osp-pay-msg"><?php _e('You cannot pay with your credits as your cart contains Auctioned item. Payments for auctioned items using credits are not allowed.', 'osclass_pay'); ?></div>
<?php } ?>

<?php if(isset($wallet['formatted_amount']) && $wallet['formatted_amount'] >= 0 && $contains_offer && $total > 0) { ?>
  <div class="osp-pay-msg"><?php _e('You cannot pay with your credits as your cart contains Customer item offer. Payments for custom offers using credits are not allowed.', 'osclass_pay'); ?></div>
<?php } ?>

<?php if(osp_param('bt_enabled') == 1 && osc_apply_filter('osp_cart_payment_transfer', true) !== false && $contains_auction_buyout && $total > 0) { ?>
  <div class="osp-pay-msg"><?php _e('You cannot pay auction buyout using bank transfer.', 'osclass_pay'); ?></div>
<?php } ?>



<?php osc_run_hook('osp_cart_before_pay_buttons'); ?>


<?php if($total > 0 && !$phone_missing && !$address_missing) { ?>
  <ul class="osp-pay-button">
    <label><?php _e('Click on preferred method to initiate payment', 'osclass_pay'); ?></label>
    <?php 
      $checksum = osp_create_checksum(@$user['pk_i_id'], @$user['pk_i_id'], @$user['s_email'], round($total, 2));

      if(osc_is_admin_user_logged_in() && osc_apply_filter('osp_cart_payment_admin', true) !== false) {
        osp_admin_button(round($total, 2), sprintf(__('Pay %s cart items for %s by admin', 'osclass_pay'), $count, osp_format_price($total, 2)), '901x1x'.$user_id, array('user' => @$user['pk_i_id'], 'itemid' => @$user['pk_i_id'], 'email' => @$user['s_email'], 'amount' => round($total, 2)));
      }

      if(!$contains_pack && !$contains_product && !$contains_offer && !$contains_booking && !$contains_auction && osc_apply_filter('osp_cart_payment_wallet', true) !== false) {
        osp_wallet_button(round($total, 2), sprintf(__('Pay %s cart items for %s', 'osclass_pay'), $count, osp_format_price($total, 2)), '901x1x'.$user_id, array('user' => @$user['pk_i_id'], 'itemid' => @$user['pk_i_id'], 'email' => @$user['s_email'], 'amount' => round($total, 2)));
      } 


      if(osp_param('bt_enabled') == 1 && osc_apply_filter('osp_cart_payment_transfer', true) !== false && !$contains_auction_buyout) {
        osp_transfer_button(round($total, 2), sprintf(__('Pay %s cart items for %s', 'osclass_pay'), $count, osp_format_price($total, 2)), '901x1x'.$user_id, array('user' => @$user['pk_i_id'], 'itemid' => @$user['pk_i_id'], 'email' => @$user['s_email'], 'name' => @$user['s_name'], 'amount' => round($total, 2), 'checksum' => $checksum));
      }

      if(osc_apply_filter('osp_cart_payment_money', true) !== false) {
        osp_buttons(round($total, 2), sprintf(__('Pay %s cart items for %s', 'osclass_pay'), $count, osp_format_price($total, 2)), '901x1x'.$user_id, array('user' => @$user['pk_i_id'], 'itemid' => @$user['pk_i_id'], 'email' => @$user['s_email'], 'amount' => round($total, 2)));
      }
    ?>
  </ul>

  <?php osp_buttons_js(); ?>
<?php } else if($total <= 0 && $count > 0) { ?>
  <ul class="osp-pay-button">
    <label><?php _e('Click on button to complete order', 'osclass_pay'); ?></label>
    <?php osp_wallet_button(round($total, 2), sprintf(__('Free checkout for %s cart items', 'osclass_pay'), $count), '901x1x'.$user_id, array('user' => @$user['pk_i_id'], 'itemid' => @$user['pk_i_id'], 'email' => @$user['s_email'], 'amount' => round($total, 2))); ?>
  </ul>
<?php } ?>

<?php osc_run_hook('osp_cart_after_pay_buttons'); ?>