<?php
  /**
   * User Groups Page - Standard/VIP Separation
   *
   * This file displays membership groups, separated into Standard and VIP sections.
   * Buttons allow users to scroll smoothly to each section and highlight the items within.
   */

  // --- Initialization and Data Fetching ---
  $restricted_cat = (@$is_restricted_category == 1 ? 1 : 0);
  $restricted_groups = (@$is_restricted_category == 1 ? $groups_allowed : array());

  if($restricted_cat <> 1) {
    // Display top user menu if not in a restricted category context
    osp_user_menu('group');
  }

  $user_id = osc_logged_user_id();
  $currency = osp_currency();
  $symbol = osp_currency_symbol();
  $groups = ModelOSP::newInstance()->getUserGroupsByCategory($user_id); // Fetch all groups relevant to user/category
  $group = ModelOSP::newInstance()->getGroup(osp_get_user_group());   // Current user's group details
  $ugroup = ModelOSP::newInstance()->getUserGroupRecord($user_id); // Current user's group record (like expiry)
  $repeat = array(); // Placeholder for repeat purchase options (likely unused in this specific display logic)

  $group_avl_repeats = array_filter(array_unique(explode(',', OSP_GROUP_DAYS_REPEATS)));

  // --- Data Preparation (Repeat options - adjust if needed elsewhere) ---
  // Note: This prepares repeat data but isn't directly used in the final display loops below.
  // It's kept in case other parts of the original template rely on it.
  foreach($groups as $g_key => $g_val) {
      $repeat[$g_val['pk_i_id']] = array();
      foreach($group_avl_repeats as $gr) {
          $repeat[$g_val['pk_i_id']][] = array(
              'quantity' => $gr,
              'title' => osp_format_membership_days($g_val['i_days']*$gr),
              'days' => $g_val['i_days']*$gr,
              'price' => osp_get_fee(OSP_TYPE_MEMBERSHIP, 1, $g_val['pk_i_id'], $g_val['i_days']*$gr),
              'price_formatted' => osp_format_price(osp_get_fee(OSP_TYPE_MEMBERSHIP, 1, $g_val['pk_i_id'], $g_val['i_days']*$gr))
          );
      }
      // DEVELOPER NOTE: If your $groups array doesn't naturally include a type identifier,
      // you might need to add logic here or modify ModelOSP::getUserGroupsByCategory
      // Example placeholder (replace with your actual logic):
      // if(!isset($groups[$g_key]['s_type'])) {
      //     $groups[$g_key]['s_type'] = ($g_val['i_rank'] > 5) ? 'vip' : 'standard'; // Example based on rank
      // }
  }

  // --- Separate groups into VIP and Standard ---
  $vip_groups = [];
  $standard_groups = [];
  foreach($groups as $g) {
      // --- !!! IMPORTANT: ADJUST THIS CONDITION !!! ---
      // This determines how VIP groups are identified.
      // Modify 's_type' and 'vip' if your data uses different field names or values.
      if(isset($g['s_type']) && strtolower(trim($g['s_type'])) == 'vip') {
          $vip_groups[] = $g;
      } else {
          // All other groups are considered Standard
          $standard_groups[] = $g;
      }
  }
  // --- End Separation ---

  $style = (osp_param('group_style') == 1 ? 'gallery' : 'list'); // 'list' or 'gallery' style for groups
  $user = User::newInstance()->findByPrimaryKey($user_id); // Get full user details

?>

<div class="osp-body osp-body-group" <?php if(osp_param('groups_enabled') <> 1) { ?>style="display:none!important;"<?php } ?>>

  <!-- --- User Status Message --- -->
  <div class="osp-h1">
    <?php
      $can_prolong = false; // Can the user extend their current membership?
      if(osp_get_user_group() == 0) {
        // User has no membership
        _e('You haven\'t purchased a membership yet.', 'osclass_pay');
      } else {
        // User has a membership
        if (isset($ugroup['dt_expire']) && (date('Y', strtotime($ugroup['dt_expire'])) > 2090 || date('Y', strtotime($ugroup['dt_expire'])) < 1980)) {
          // Membership expiry is effectively "never"
          $expire_string = __('with no expiration', 'osclass_pay');
        } else if (isset($ugroup['dt_expire'])) {
          // Membership has a specific expiry date
          $can_prolong = true; // User can potentially extend
          $expire_string = __('until', 'osclass_pay') . ' ' . osc_format_date($ugroup['dt_expire']);
        } else {
           // Expiry date couldn't be determined
           $expire_string = __('status unknown', 'osclass_pay');
        }

        // Display the membership status message
        if(isset($group['s_name'])) { // Check if current group details were found
             echo sprintf(__('You have the %s membership %s.', 'osclass_pay'), '<strong>' . osc_esc_html($group['s_name']) . '</strong>', $expire_string);
        } else {
             _e('Your current membership details could not be retrieved.', 'osclass_pay');
        }
      }
    ?>
  </div>

  <?php // Display message about package discounts if applicable
    if(isset($ugroup['i_discount']) && $ugroup['i_discount'] <> '' && $ugroup['i_discount'] > 0) { ?>
    <div class="osp-pay-msg"><?php echo sprintf(__('Your membership discount %s is not applied on packages as it would lead to double discount.', 'osclass_pay'), round($ugroup['i_discount']) . '%'); ?></div>
  <?php } ?>
  <!-- --- End User Status Message --- -->


  <!-- --- Navigation Buttons --- -->
  <div class="osp-group-nav" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #eee; text-align: center;">
      <div class="osp-group-nav-buttons">
          <button type="button" id="show-standard" class="osp-button-nav active"><?php _e('Standard', 'osclass_pay'); ?></button>
          <button type="button" id="show-vip" class="osp-button-nav"><?php _e('VIP', 'osclass_pay'); ?></button>
      </div>
  </div>
  <!-- --- End Navigation Buttons --- -->


  <!-- === STANDARD MEMBERSHIPS SECTION === -->
  <div id="standard-section" class="osp-group-section">
    <h2 class="osp-group-section-title"><?php _e('Standard Memberships', 'osclass_pay'); ?></h2>
    <div class="osp-content osp-content-standard">
       <?php if(count($standard_groups) > 0) { ?>
         <?php foreach($standard_groups as $g) { ?>
           <?php // Check if group should be shown based on category restrictions
             if($restricted_cat <> 1 || ($restricted_cat == 1 && in_array($g['pk_i_id'], $restricted_groups))) { ?>

             <!-- --- START: Standard Group Item HTML --- -->
             <div class="osp-group <?php if(osp_get_user_group() == $g['pk_i_id']) {?>active<?php } ?> <?php echo $style; ?>" data-group="<?php echo $g['pk_i_id']; ?>" data-rank="<?php echo $g['i_rank']; ?>">
                 <div class="osp-top" style="background-color:<?php echo osc_esc_html($g['s_color']); ?>;color:<?php echo osp_text_color($g['s_color']); ?>">
                    <?php if(@$group['pk_i_id'] == $g['pk_i_id']) { // Mark if this is user's current group ?>
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
                      <?php // Disable purchase button if user has this group and cannot prolong
                        if(!$can_prolong && @$g['pk_i_id'] == osp_get_user_group()) { ?>
                        <a class="osp_cart_add osp-disabled" href="#" onclick="return false;" title="<?php echo osc_esc_html(__('You already have this membership and it does not expire or cannot be prolonged.', 'osclass_pay'));?>"><?php echo osp_group_label($g['pk_i_id'], $g['i_rank']); ?></a>
                      <?php } else { // Show purchase/add-to-cart button ?>
                        <a class="osp_cart_add" href="<?php echo osp_cart_add(OSP_TYPE_MEMBERSHIP, 1, $g['pk_i_id'], $g['i_days']); ?>"><?php echo osp_group_label($g['pk_i_id'], $g['i_rank']); ?></a>
                      <?php } ?>
                    </div>
                 </div>
                 <div class="osp-right2">
                    <?php // --- Group Benefits/Details --- ?>
                    <?php if(ModelOSP::newInstance()->checkGroupDiscount()) { ?>
                      <?php if($g['i_discount'] > 0) { ?>
                        <div class="osp-perc"><?php _e('Flat discount', 'osclass_pay'); ?>: <strong><?php echo round($g['i_discount']); ?><span>%</span></strong></div>
                      <?php } else { ?>
                        <div class="osp-perc osp-none"><?php _e('No additional discount', 'osclass_pay'); ?></div>
                      <?php } ?>
                    <?php } ?>

                    <?php if(ModelOSP::newInstance()->checkGroupBonus() && osp_param('wallet_periodically') <> '' && osp_param('wallet_periodically') > 0) { ?>
                      <?php if($g['i_pbonus'] > 0) { ?>
                        <?php /* Periodic bonus details */
                          if(osp_param('wallet_period') == 'w') { $period = __('week', 'osclass_pay'); }
                          else if(osp_param('wallet_period') == 'm') { $period = __('month', 'osclass_pay'); }
                          else if(osp_param('wallet_period') == 'q') { $period = __('quarter', 'osclass_pay'); }
                          else { $period = __('period', 'osclass_pay'); }
                          $ptitle = sprintf(__('Get %s more credits each %s!', 'osclass_pay'), '<strong>' . round($g['i_pbonus']) . '%</strong>', $period);
                        ?>
                        <div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html($ptitle); ?>"><?php echo $ptitle; ?></div>
                      <?php } else { ?>
                        <div class="osp-perc osp-none"><?php _e('No extra credits', 'osclass_pay'); ?></div>
                      <?php } ?>
                    <?php } ?>

                    <?php if(osp_param('groups_limit_items') == 1) { ?>
                      <?php /* Item limit details */
                        $def_max_items = osp_param('groups_max_items'); $def_max_items_days = osp_param('groups_max_items_days');
                        $method = osp_param('groups_max_items_type'); $group_max_items = $g['i_max_items']; $group_max_items_days = $g['i_max_items_days'];
                        $mi_content = sprintf(__('%s free listings in %s days', 'osclass_pay'), $group_max_items, $group_max_items_days);
                        $mi_title = sprintf(__('Members of %s group can publish %s listings in %s days. By default you can only publish %s items in %s days.', 'osclass_pay'), '<strong>' . $g['s_name'] . '</strong>', $group_max_items, $group_max_items_days, $def_max_items, $def_max_items_days);
                        if($method == 2 || $method == 3) { $mi_title .= ' (' . __('Premium listings are not counted', 'osclass_pay') . ').'; }
                      ?>
                      <div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html($mi_title); ?>"><?php echo $mi_content; ?></div>
                    <?php } ?>

                    <?php if(ModelOSP::newInstance()->checkGroupCustom() && trim($g['s_custom']) <> '') { ?>
                      <div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html($g['s_custom']); ?>"><?php echo $g['s_custom']; ?></div>
                    <?php } ?>

                    <?php if(ModelOSP::newInstance()->checkGroupPacks() && osp_param('wallet_enabled') == 1) { ?>
                      <?php $packs = ModelOSP::newInstance()->getPacks($g['pk_i_id'], 1); ?>
                      <?php if(!empty($packs)) { ?>
                        <?php /* Exclusive packs details */
                          $pnames = ''; foreach($packs as $p) { $pnames .= ($pnames != '' ? ', ' : '') . $p['s_name']; }
                        ?>
                        <div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html(__('Exclusive credit packs:', 'osclass_pay') . ' ' . $pnames); ?>"><?php _e('Exclusive packs:', 'osclass_pay'); ?> <?php echo osc_esc_html($pnames); ?></div>
                      <?php } else { ?>
                        <div class="osp-perc osp-none"><?php _e('No exclusive credit packs', 'osclass_pay'); ?></div>
                      <?php } ?>
                    <?php } ?>

                    <?php if(osp_fee_is_allowed(OSP_TYPE_PUBLISH)) { ?>
                      <div class="osp-perc<?php if($g['i_free_items_101'] <= 0) { ?> osp-none<?php } ?>"><?php _e('Free active listings:', 'osclass_pay'); ?> <?php echo ($g['i_free_items_101'] > 0 ? $g['i_free_items_101'] : 0); ?></div>
                    <?php } ?>

                    <?php if(ModelOSP::newInstance()->checkGroupCategory()) { ?>
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
                 </div>
             </div>
             <!-- --- END: Standard Group Item HTML --- -->

           <?php } // End category restriction check ?>
         <?php } // End foreach standard_groups ?>
       <?php } else { // No standard groups found ?>
           <p style="text-align: center; margin-top: 20px;"><?php _e('No Standard memberships are currently available.', 'osclass_pay'); ?></p>
       <?php } ?>
    </div>
  </div>
  <!-- === END STANDARD MEMBERSHIPS SECTION === -->


  <!-- === VIP MEMBERSHIPS SECTION === -->
  <div id="vip-section" class="osp-group-section" style="margin-top: 40px; padding-top: 40px; border-top: 1px solid #ccc;">
    <h2 class="osp-group-section-title"><?php _e('VIP Memberships', 'osclass_pay'); ?></h2>
    <div class="osp-content osp-content-vip">
      <?php if(count($vip_groups) > 0) { ?>
        <?php foreach($vip_groups as $g) { ?>
           <?php // Check if group should be shown based on category restrictions
             if($restricted_cat <> 1 || ($restricted_cat == 1 && in_array($g['pk_i_id'], $restricted_groups))) { ?>

             <!-- --- START: VIP Group Item HTML (Structure identical to Standard) --- -->
             <div class="osp-group <?php if(osp_get_user_group() == $g['pk_i_id']) {?>active<?php } ?> <?php echo $style; ?>" data-group="<?php echo $g['pk_i_id']; ?>" data-rank="<?php echo $g['i_rank']; ?>">
                 <div class="osp-top" style="background-color:<?php echo osc_esc_html($g['s_color']); ?>;color:<?php echo osp_text_color($g['s_color']); ?>">
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
                        <a class="osp_cart_add osp-disabled" href="#" onclick="return false;" title="<?php echo osc_esc_html(__('You already have this membership and it does not expire or cannot be prolonged.', 'osclass_pay'));?>"><?php echo osp_group_label($g['pk_i_id'], $g['i_rank']); ?></a>
                      <?php } else { ?>
                        <a class="osp_cart_add" href="<?php echo osp_cart_add(OSP_TYPE_MEMBERSHIP, 1, $g['pk_i_id'], $g['i_days']); ?>"><?php echo osp_group_label($g['pk_i_id'], $g['i_rank']); ?></a>
                      <?php } ?>
                    </div>
                 </div>
                 <div class="osp-right2">
                    <?php // --- Group Benefits/Details (Copy/paste benefits logic from Standard or customize for VIP) --- ?>
                    <?php if(ModelOSP::newInstance()->checkGroupDiscount()) { ?>
                      <?php if($g['i_discount'] > 0) { ?>
                        <div class="osp-perc"><?php _e('Flat discount', 'osclass_pay'); ?>: <strong><?php echo round($g['i_discount']); ?><span>%</span></strong></div>
                      <?php } else { ?>
                        <div class="osp-perc osp-none"><?php _e('No additional discount', 'osclass_pay'); ?></div>
                      <?php } ?>
                    <?php } ?>

                    <?php if(ModelOSP::newInstance()->checkGroupBonus() && osp_param('wallet_periodically') <> '' && osp_param('wallet_periodically') > 0) { ?>
                      <?php if($g['i_pbonus'] > 0) { ?>
                        <?php /* Periodic bonus details */
                          if(osp_param('wallet_period') == 'w') { $period = __('week', 'osclass_pay'); }
                          else if(osp_param('wallet_period') == 'm') { $period = __('month', 'osclass_pay'); }
                          else if(osp_param('wallet_period') == 'q') { $period = __('quarter', 'osclass_pay'); }
                          else { $period = __('period', 'osclass_pay'); }
                          $ptitle = sprintf(__('Get %s more credits each %s!', 'osclass_pay'), '<strong>' . round($g['i_pbonus']) . '%</strong>', $period);
                        ?>
                        <div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html($ptitle); ?>"><?php echo $ptitle; ?></div>
                      <?php } else { ?>
                        <div class="osp-perc osp-none"><?php _e('No extra credits', 'osclass_pay'); ?></div>
                      <?php } ?>
                    <?php } ?>

                    <?php if(osp_param('groups_limit_items') == 1) { ?>
                      <?php /* Item limit details */
                        $def_max_items = osp_param('groups_max_items'); $def_max_items_days = osp_param('groups_max_items_days');
                        $method = osp_param('groups_max_items_type'); $group_max_items = $g['i_max_items']; $group_max_items_days = $g['i_max_items_days'];
                        $mi_content = sprintf(__('%s free listings in %s days', 'osclass_pay'), $group_max_items, $group_max_items_days);
                        $mi_title = sprintf(__('Members of %s group can publish %s listings in %s days. By default you can only publish %s items in %s days.', 'osclass_pay'), '<strong>' . $g['s_name'] . '</strong>', $group_max_items, $group_max_items_days, $def_max_items, $def_max_items_days);
                        if($method == 2 || $method == 3) { $mi_title .= ' (' . __('Premium listings are not counted', 'osclass_pay') . ').'; }
                      ?>
                      <div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html($mi_title); ?>"><?php echo $mi_content; ?></div>
                    <?php } ?>

                    <?php if(ModelOSP::newInstance()->checkGroupCustom() && trim($g['s_custom']) <> '') { ?>
                      <div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html($g['s_custom']); ?>"><?php echo $g['s_custom']; ?></div>
                    <?php } ?>

                    <?php if(ModelOSP::newInstance()->checkGroupPacks() && osp_param('wallet_enabled') == 1) { ?>
                      <?php $packs = ModelOSP::newInstance()->getPacks($g['pk_i_id'], 1); ?>
                      <?php if(!empty($packs)) { ?>
                        <?php /* Exclusive packs details */
                          $pnames = ''; foreach($packs as $p) { $pnames .= ($pnames != '' ? ', ' : '') . $p['s_name']; }
                        ?>
                        <div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html(__('Exclusive credit packs:', 'osclass_pay') . ' ' . $pnames); ?>"><?php _e('Exclusive packs:', 'osclass_pay'); ?> <?php echo osc_esc_html($pnames); ?></div>
                      <?php } else { ?>
                        <div class="osp-perc osp-none"><?php _e('No exclusive credit packs', 'osclass_pay'); ?></div>
                      <?php } ?>
                    <?php } ?>

                    <?php if(osp_fee_is_allowed(OSP_TYPE_PUBLISH)) { ?>
                      <div class="osp-perc<?php if($g['i_free_items_101'] <= 0) { ?> osp-none<?php } ?>"><?php _e('Free active listings:', 'osclass_pay'); ?> <?php echo ($g['i_free_items_101'] > 0 ? $g['i_free_items_101'] : 0); ?></div>
                    <?php } ?>

                    <?php if(ModelOSP::newInstance()->checkGroupCategory()) { ?>
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

          </div>
             </div>
             <!-- --- END: VIP Group Item HTML --- -->

           <?php } // End category restriction check ?>
        <?php } // End foreach vip_groups ?>
      <?php } else { // No VIP groups found ?>
          <p style="text-align: center; margin-top: 20px;"><?php _e('No VIP memberships are currently available.', 'osclass_pay'); ?></p>
      <?php } ?>
    </div>
  </div>
  <!-- === END VIP MEMBERSHIPS SECTION === -->

</div> <!-- End osp-body -->


<!-- === JAVASCRIPT & CSS === -->
<?php
// It's generally better to enqueue scripts and styles using Osclass hooks if possible.
// However, including them directly here will also work.
?>
<script type="text/javascript">
jQuery(document).ready(function($) {

  /**
   * Smooth scrolls to a target section and temporarily highlights items within it.
   * Also sets the corresponding navigation button to 'active'.
   *
   * @param {string} targetSelector CSS selector for the section to scroll to (e.g., '#vip-section').
   * @param {Element} buttonToActivate The button element that was clicked.
   */
  function smoothScrollAndHighlight(targetSelector, buttonToActivate) {
    var section = $(targetSelector);
    // Adjust this value based on your fixed header height (if any) + desired spacing
    var scrollOffset = 60; // Pixels to offset scroll position from top

    if(section.length) { // Check if the target section exists on the page
      // Make the clicked button active, others inactive
      $('.osp-button-nav').removeClass('active');
      $(buttonToActivate).addClass('active');

      // Perform the smooth scroll animation
      $('html, body').animate({
        scrollTop: section.offset().top - scrollOffset
      }, 800, function(){ // Animation complete callback (runs after scrolling)

        // Find the group items WITHIN the scrolled-to section
        var itemsToHighlight = section.find('.osp-group');

        // Add highlight class to the items
        itemsToHighlight.addClass('highlight');

        // Remove the highlight class after a short delay (1 second)
        setTimeout(function(){
          itemsToHighlight.removeClass('highlight');
        }, 1000); // 1000 milliseconds = 1 second
      });
    } else {
      console.warn("Target section for scrolling not found:", targetSelector);
    }
  }

  // --- Attach Click Event Handlers to Buttons ---
  $('#show-standard').click(function(e){
    e.preventDefault(); // Prevent default button action (like page jump)
    smoothScrollAndHighlight('#standard-section', this); // Scroll to Standard, pass this button
  });

  $('#show-vip').click(function(e){
    e.preventDefault();
    smoothScrollAndHighlight('#vip-section', this); // Scroll to VIP, pass this button
  });

  // --- Initial State ---
  // Ensure 'Standard' button is active on page load as it's displayed first
  // (The 'active' class is already set in the HTML, this just confirms)
  // $('#show-standard').addClass('active'); // Can be removed if HTML is correct
  // $('#show-vip').removeClass('active'); // Ensure VIP isn't active initially

});
</script>

<style type="text/css">
  /* --- Navigation Buttons --- */
  .osp-group-nav-buttons {
      display: inline-block; /* Allows text-align: center on parent */
  }
  button.osp-button-nav {
      padding: 10px 25px;
      font-size: 1em;
      font-weight: bold;
      cursor: pointer;
      border: 1px solid #ccc;
      background-color: #f5f5f5; /* Light gray background */
      color: #555;             /* Dark gray text */
      margin: 0 5px;            /* Space between buttons */
      border-radius: 4px;       /* Slightly rounded corners */
      transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease; /* Smooth transitions */
      outline: none;            /* Remove default browser outline */
      box-shadow: 0 2px 4px rgba(0,0,0,0.05); /* Subtle shadow */
      vertical-align: middle; /* Align if needed */
  }
  button.osp-button-nav:hover {
      background-color: #e9e9e9; /* Slightly darker on hover */
      border-color: #bbb;
      color: #333;
  }
  button.osp-button-nav.active {
      background-color: #007bff; /* Primary color for active state (e.g., blue) */
      color: #fff;             /* White text for active state */
      border-color: #0056b3;   /* Darker border for active state */
      box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); /* Inner shadow for active state */
  }
  button.osp-button-nav:focus { /* Style for keyboard navigation */
      box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.5);
  }

  /* --- Section Titles --- */
  .osp-group-section-title {
    text-align: center;
    font-size: 1.6em;       /* Make titles stand out */
    margin-bottom: 25px;    /* Space below title */
    color: #333;           /* Dark text color */
    font-weight: 400;       /* Normal font weight */
  }

  /* --- Group Item Highlighting --- */
  .osp-group.highlight {
    background-color: #fff9c4 !important; /* Light yellow highlight. !important might be needed if inline styles conflict. */
    box-shadow: 0 0 12px rgba(224, 200, 80, 0.6); /* Soft yellow glow */
    border-radius: 5px;       /* Consistent rounded corners */
    /* Smooth transition for highlight appearing */
    transition: background-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
  }

  /* --- Base Group Item Styling --- */
  .osp-group {
    /* Add margin between group items */
    margin-bottom: 20px;
    border-radius: 5px; /* Match highlight radius for consistency */
    /* Transition for highlight fading out */
    transition: background-color 0.7s ease-in-out, box-shadow 0.7s ease-in-out;
    overflow: hidden; /* Contain background colors and shadows nicely */
    border: 1px solid #eee; /* Optional subtle border */
  }
  /* Clearfix for floated elements inside osp-group if needed */
  .osp-group::after {
      content: "";
      display: table;
      clear: both;
  }

  /* --- Section Spacing ---  */
   #vip-section {
       margin-top: 40px;      /* Space above VIP section */
       padding-top: 40px;     /* Space between top border and VIP title */
       border-top: 1px solid #ccc; /* Separator line */
   }
   #standard-section {
       padding-top: 20px;     /* Space between nav buttons and Standard title */
   }

</style>
<!-- === END JAVASCRIPT & CSS === -->