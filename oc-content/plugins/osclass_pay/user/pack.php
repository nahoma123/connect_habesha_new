<?php
  osp_user_menu('pack');

  $user_id = osc_logged_user_id();
  $currency = osp_currency();
  $symbol = osp_currency_symbol();
  $packs = osp_get_user_packs($user_id);
  $wallet = osp_get_wallet();
  $style = (osp_param('pack_style') == 1 ? 'gallery' : 'list');
  $group = ModelOSP::newInstance()->getGroup(osp_get_user_group());


  @$user = User::newInstance()->findByPrimaryKey($user_id);


  if($currency == 'BTC') {
    $amount = isset($wallet['formatted_amount']) ? $wallet['formatted_amount'] : 0;
    $formatted_amount = osp_format_btc($amount);
    $credit_msg = sprintf(__('Buy Credit pack to save time with checkout. Your current credit is %s', 'osclass_pay'), '<strong>' . $formatted_amount . '</strong>');
  } else {
    $amount = isset($wallet['i_amount']) ? $wallet['i_amount'] : 0;
    if($amount != 0) {
      $formatted_amount = osp_format_price(osp_price_divide($amount)/1000000);
      $credit_msg = sprintf(__('Buy Credit pack to save time with checkout. Your current credit is %s', 'osclass_pay'), '<strong>' . $formatted_amount . '</strong>');
    } else {
      $credit_msg = __('Your wallet is empty. Buy some credits.', 'osclass_pay');
    }
  }


  // APPLY CREDIT VOUCHER
  if(Params::getParam('ospPackAction') == 'voucher' && Params::getParam('voucher') <> '') {
    $voucher_valid = osp_check_voucher_code(Params::getParam('voucher'), 1);

    if($voucher_valid['error'] <> 'OK') {
      osc_add_flash_error_message(sprintf(__('Voucher %s has not been processed: %s', 'osclass_pay'), '<u>' . Params::getParam('voucher') . '</u>', $voucher_valid['message']));
      osp_redirect(osc_route_url('osp-pack'));
      exit;

    } else {
      // validation above allow only "AMOUNT" voucher types here
      $voucher = ModelOSP::newInstance()->getVoucherByCode(Params::getParam('voucher'));
      
      $amount = $voucher['d_amount'];
    
      osp_wallet_update(osc_logged_user_id(), $amount);
      ModelOSP::newInstance()->saveLog(sprintf(__('Credit for user %s (%s) at %s by voucher %s', 'osclass_pay'), $user['s_name'], osp_format_price($amount), osc_page_title(), Params::getParam('voucher')), 'wallet_' . date('YmdHis'), $amount, osp_currency(), osc_logged_user_email(), osc_logged_user_id(), '', OSP_TYPE_PACK, 'VOUCHER');
      ModelOSP::newInstance()->updateVoucherUsage($voucher['pk_i_id'], 1);

      $new_amount = osp_get_wallet_amount(osc_logged_user_id());

      osc_add_flash_ok_message(sprintf(__('Voucher %s has been processed correctly, your wallet now has %s credits: %s', 'osclass_pay'), '<u>' . Params::getParam('voucher') . '</u>', osp_format_price($new_amount), $voucher_valid['message']));
      osp_redirect(osc_route_url('osp-pack'));
      exit;
    }
    
  }
?>


<div class="osp-body osp-body-pack" <?php if(osp_param('wallet_enabled') <> 1) { ?>style="display:none!important;"<?php } ?>>
  <div class="osp-h1">
    <?php echo $credit_msg; ?>
  </div>


  <?php if(osp_param('wallet_periodically') <> '' && osp_param('wallet_periodically') > 0) { ?>
    <?php
      if(osp_param('wallet_period') == 'w') {
        $period = __('week', 'osclass_pay');
      } else if(osp_param('wallet_period') == 'm') {
        $period = __('month', 'osclass_pay');
      } else if(osp_param('wallet_period') == 'q') {
        $period = __('quarter', 'osclass_pay');
      }

      $credit = osp_param('wallet_periodically');
      $bonus = '';
      $group = ModelOSP::newInstance()->getUserGroupRecord(osc_logged_user_id());

      if(isset($group['i_pbonus']) && $group['i_pbonus'] <> '' && $group['i_pbonus'] > 0) {
        $credit = $credit*(1+$group['i_pbonus']/100);
        $bonus = ' <em>(' . sprintf(__('%s more based on your membership in %s', 'osclass_pay'), $group['i_pbonus'] . '%', $group['s_name']) . ')</em>';
      }

      $credit = osp_format_price($credit);
    ?>

    <span class="osp-pack-bonus"><?php echo sprintf(__('We value our customers, as bonus for your registration we boost your wallet with %s each %s!', 'osclass_pay'), '<strong>' . $credit . '</strong>' . $bonus, $period); ?></span>
  <?php } ?>

  
  <?php if(osp_vouchers_enabled()) { ?>
    <div id="osp-vcr">
      <div class="osp-h2"><i class="fa fa-tag"></i> <?php _e('Voucher', 'osclass_pay'); ?></div>
      <div class="osp-line"><?php _e('Boost your wallet credits with voucher if you\'ve received one!', 'osclass_pay'); ?></div>

      <form action="<?php echo osc_route_url('osp-pack'); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="ospPackAction" value="voucher" />

        <label for="voucher"><?php _e('Enter voucher code', 'osclass_pay'); ?></label>
        <input type="text" id="voucher" name="voucher" placeholder="<?php echo osc_esc_html(__('Example: WALLET50', 'osclass_pay')); ?>" />

        <button type="submit"><i class="fa fa-check"></i> <?php _e('Apply', 'osclass_pay'); ?></button>
      </form>

    </div>
  <?php } ?>


  <?php if(osp_param('wallet_referral') <> '' && osp_param('wallet_referral') > 0) { ?>
    <div id="osp-aff">
      <div class="osp-h2"><i class="fa fa-handshake-o"></i> <?php _e('Affiliate program', 'osclass_pay'); ?></div>
      <div class="osp-line"><?php echo sprintf(__('Invite your friend and both get bonus %s!', 'osclass_pay'), '<strong>' . osp_format_price(osp_param('wallet_referral')) . '</strong>'); ?></div>
      <div class="osp-line"><?php echo sprintf(__('Your referral code is %s', 'osclass_pay'), '<span class="osp-referral-code">' . osp_get_referral() . '</span>'); ?></div>
      <div class="osp-line"><?php echo __('Share it with your friends or send them your Invite link.', 'osclass_pay'); ?></div>
      <div class="osp-line osp-lab"><?php _e('Your invite link:', 'osclass_pay'); ?></div>
      <div class="osp-line osp-invite-link"><?php echo osp_invite_link(); ?></div>
      <div class="osp-line osp-lab"><?php _e('Click to share your invite link:', 'osclass_pay'); ?></div>
      <div class="osp-line osp-share">
        <a class="osp-fb" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(osp_invite_link()); ?>">
          <i class="fa fa-facebook"></i> <?php _e('Share on Facebook', 'osclass_pay'); ?>
        </a>

        <a class="osp-wa" target="_blank" href="https://wa.me/?text=<?php echo urlencode(osp_invite_link()); ?>" data-action="share/whatsapp/share">
          <i class="fa fa-whatsapp"></i> <?php _e('Share on Whatsapp', 'osclass_pay'); ?>
        </a>

        <a class="osp-tw" target="_blank" href="https://twitter.com/intent/tweet?text=<?php echo urlencode(osp_invite_link()); ?>">
          <i class="fa fa-twitter"></i> <?php _e('Share on Twitter', 'osclass_pay'); ?>
        </a>

        <a class="osp-li" target="_blank" href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(osp_invite_link()); ?>&title=<?php echo urlencode(sprintf(__('Register and get bonus %s!', 'osclass_pay'), osp_format_price(osp_param('wallet_referral')))); ?>&summary=&source=">
          <i class="fa fa-linkedin"></i> <?php _e('Share on LinkedIn', 'osclass_pay'); ?>
        </a>
      </div>
    </div>
  <?php } ?>


  <div class="osp-lab"><?php _e('Credit Packs', 'osclass_pay'); ?></div>

  <div class="osp-content">
    <?php foreach($packs as $p) { ?>
      <div class="osp-pack <?php echo $style; ?>" data-pack="<?php echo $p['pk_i_id']; ?>" style="background-color:<?php echo $p['s_color']; ?>;color:<?php echo osp_text_color($p['s_color']); ?>">
        <div class="osp-left">
          <div class="osp-h2"><?php echo $p['s_name']; ?></div>
          <div class="osp-desc"><?php echo $p['s_description']; ?></div>
          <?php if($p['i_group'] <> 0) { ?>
            <div class="osp-group osp-has-tooltip" title="<?php echo osc_esc_html(sprintf(__('Exclusive for %s members!', 'osclass_pay'), $group['s_name'])); ?>"><i class="fa fa-diamond"></i></div>
          <?php } ?>
        </div>

        <div class="osp-right1">
          <div class="osp-price"><?php echo osp_format_price($p['f_price'] + $p['f_extra']); ?></div>
          <div class="osp-cost"><?php _e('Pay just', 'osclass_pay'); ?> <?php echo osp_format_price($p['f_price']); ?></div>

          <?php if($p['f_price'] > 0 && $p['f_extra'] > 0) { ?>
            <div class="osp-perc">+<?php echo round($p['f_extra']/$p['f_price']*100); ?><span>%</span></div>
          <?php } ?>
        </div>

        <div class="osp-right2">
          <a href="<?php echo osp_cart_add(OSP_TYPE_PACK, 1, $p['pk_i_id'], round($p['f_price'] + $p['f_extra'], 2)); ?>"><?php _e('Add to cart', 'osclass_pay'); ?></a>
        </div>

      </div>
    <?php } ?>
  </div>

  <?php if(isset($group['i_discount']) && $group['i_discount'] <> '' && $group['i_discount'] > 0) { ?>
    <div class="osp-pay-msg"><?php echo sprintf(__('Your membershipt discount %s is not applied on packages as it would lead to double discount.', 'osclass_pay'), round($group['i_discount']) . '%'); ?></div>
  <?php } ?>
</div>