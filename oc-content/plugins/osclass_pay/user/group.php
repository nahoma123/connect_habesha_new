<?php
  // --- PHP Code remains the same as the previous version ---
  $restricted_cat = (@$is_restricted_category == 1 ? 1 : 0);
  $restricted_groups = (@$is_restricted_category == 1 ? $groups_allowed : array());

  if($restricted_cat <> 1) {
    osp_user_menu('group');
  }

  $user_id = osc_logged_user_id();
  $currency = osp_currency();
  $symbol = osp_currency_symbol();
  $groups = ModelOSP::newInstance()->getUserGroupsByCategory($user_id);
  $group = ModelOSP::newInstance()->getGroup(osp_get_user_group());
  $ugroup = ModelOSP::newInstance()->getUserGroupRecord($user_id);
  $repeat = array();

  $group_avl_repeats = array_filter(array_unique(explode(',', OSP_GROUP_DAYS_REPEATS)));

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
      // Add s_type if needed
      // if(!isset($groups[$g_key]['s_type'])) { $groups[$g_key]['s_type'] = ($g_val['pk_i_id'] % 2 != 0) ? 'vip' : 'standard'; }
  }

  $vip_groups = [];
  $standard_groups = [];
  foreach($groups as $g) {
      if(isset($g['s_type']) && strtolower($g['s_type']) == 'vip') {
          $vip_groups[] = $g;
      } else {
          $standard_groups[] = $g;
      }
  }

  $style = (osp_param('group_style') == 1 ? 'gallery' : 'list');
  $user = User::newInstance()->findByPrimaryKey($user_id);
?>

<div class="osp-body osp-body-group" <?php if(osp_param('groups_enabled') <> 1) { ?>style="display:none!important;"<?php } ?>>

  <!-- --- User Status Message (Unchanged) --- -->
  <div class="osp-h1">
    <?php /* ... user status message code ... */ ?>
     <?php
      $can_prolong = false;
      if(osp_get_user_group() == 0) {
        _e('You haven\'t purchased a membership yet.', 'osclass_pay');
      } else {
        if (isset($ugroup['dt_expire']) && (date('Y', strtotime($ugroup['dt_expire'])) > 2090 || date('Y', strtotime($ugroup['dt_expire'])) < 1980)) {
          $expire_string = __('with no expiration', 'osclass_pay');
        } else if (isset($ugroup['dt_expire'])) {
          $can_prolong = true;
          $expire_string = __('until', 'osclass_pay') . ' ' . osc_format_date($ugroup['dt_expire']);
        } else {
           $expire_string = __('status unknown', 'osclass_pay');
        }

        if(isset($group['s_name'])) {
             echo sprintf(__('You have the %s membership %s.', 'osclass_pay'), '<strong>' . $group['s_name'] . '</strong>', $expire_string);
        } else {
             _e('Your current membership details could not be retrieved.', 'osclass_pay');
        }
      }
    ?>
  </div>
  <?php if(isset($ugroup['i_discount']) && $ugroup['i_discount'] <> '' && $ugroup['i_discount'] > 0) { ?>
    <div class="osp-pay-msg"><?php echo sprintf(__('Your membership discount %s is not applied on packages as it would lead to double discount.', 'osclass_pay'), round($ugroup['i_discount']) . '%'); ?></div>
  <?php } ?>
  <!-- --- End User Status Message --- -->


  <!-- --- Buttons to scroll (Unchanged) --- -->
  <div class="osp-group-nav" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #eee; text-align: center;">
      <div class="osp-group-nav-buttons">
          <button type="button" id="show-standard" class="osp-button-nav active"><?php _e('Standard', 'osclass_pay'); ?></button>
          <button type="button" id="show-vip" class="osp-button-nav"><?php _e('VIP', 'osclass_pay'); ?></button>
      </div>
  </div>
  <!-- --- End Buttons --- -->


  <!-- --- Standard Section (HTML Unchanged) --- -->
  <div id="standard-section" class="osp-group-section">
    <h2 class="osp-group-section-title"><?php _e('Standard Memberships', 'osclass_pay'); ?></h2>
    <div class="osp-content osp-content-standard">
       <?php if(count($standard_groups) > 0) { ?>
         <?php foreach($standard_groups as $g) { ?>
           <?php if($restricted_cat <> 1 || ($restricted_cat == 1 && in_array($g['pk_i_id'], $restricted_groups))) { ?>
             <?php // --- START: Standard Group Item HTML (Ensure full content is here) --- ?>
             <div class="osp-group <?php if(osp_get_user_group() == $g['pk_i_id']) {?>active<?php } ?> <?php echo $style; ?>" data-group="<?php echo $g['pk_i_id']; ?>" data-rank="<?php echo $g['i_rank']; ?>">
                <?php /* --- PASTE FULL DETAILS FOR STANDARD GROUP HERE --- */ ?>
                 <div class="osp-top" style="background-color:<?php echo osc_esc_html($g['s_color']); ?>;color:<?php echo osp_text_color($g['s_color']); ?>">
                    <?php /* ... top part ... */ ?>
                     <?php if(@$group['pk_i_id'] == $g['pk_i_id']) { ?><span class="osp-is-active osp-has-tooltip" title="<?php echo osc_esc_html(__('You are member of this group', 'osclass_pay')); ?>"><i class="fa fa-check"></i></span><?php } ?>
                     <div class="osp-left"><div class="osp-h2"><?php echo $g['s_name']; ?></div><div class="osp-desc"><?php echo $g['s_description']; ?></div></div>
                     <div class="osp-right1"><div class="osp-price"><?php echo osp_format_price($g['f_price']); ?></div><div class="osp-cost">/ <?php _e('user', 'osclass_pay'); ?> / <span><?php echo $g['i_days'] . '</span> ' . __('days', 'osclass_pay'); ?></div></div>
                     <div class="osp-cart-keep"><?php if(!$can_prolong && @$g['pk_i_id'] == osp_get_user_group()) { ?><a class="osp_cart_add osp-disabled" href="#" onclick="return false;"><?php echo osp_group_label(@$g['i_rank']); ?></a><?php } else { ?><a class="osp_cart_add" href="<?php echo osp_cart_add(OSP_TYPE_MEMBERSHIP, 1, $g['pk_i_id'], $g['i_days']); ?>"><?php echo osp_group_label($g['pk_i_id'], $g['i_rank']); ?></a><?php } ?></div>
                 </div>
                 <div class="osp-right2">
                    <?php /* ... details part ... */ ?>
                    <?php if(ModelOSP::newInstance()->checkGroupCustom() && $g['s_custom'] <> '') { ?><div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html($g['s_custom']); ?>"><?php echo $g['s_custom']; ?></div><?php } ?>
                 </div>
             </div>
             <?php // --- END: Standard Group Item HTML --- ?>
           <?php } ?>
         <?php } // end foreach standard_groups ?>
       <?php } else { ?>
           <p style="text-align: center; margin-top: 20px;"><?php _e('No Standard memberships are currently available.', 'osclass_pay'); ?></p>
       <?php } ?>
    </div>
  </div>
  <!-- --- End Standard Section --- -->


  <!-- --- VIP Section (HTML Unchanged) --- -->
  <div id="vip-section" class="osp-group-section" style="margin-top: 40px; padding-top: 40px; border-top: 1px solid #ccc;">
    <h2 class="osp-group-section-title"><?php _e('VIP Memberships', 'osclass_pay'); ?></h2>
    <div class="osp-content osp-content-vip">
      <?php if(count($vip_groups) > 0) { ?>
        <?php foreach($vip_groups as $g) { ?>
          <?php if($restricted_cat <> 1 || ($restricted_cat == 1 && in_array($g['pk_i_id'], $restricted_groups))) { ?>
            <?php // --- START: VIP Group Item HTML (Ensure full content is here) --- ?>
             <div class="osp-group <?php if(osp_get_user_group() == $g['pk_i_id']) {?>active<?php } ?> <?php echo $style; ?>" data-group="<?php echo $g['pk_i_id']; ?>" data-rank="<?php echo $g['i_rank']; ?>">
                 <?php /* --- PASTE FULL DETAILS FOR VIP GROUP HERE --- */ ?>
                  <div class="osp-top" style="background-color:<?php echo osc_esc_html($g['s_color']); ?>;color:<?php echo osp_text_color($g['s_color']); ?>">
                     <?php /* ... top part ... */ ?>
                     <?php if(@$group['pk_i_id'] == $g['pk_i_id']) { ?><span class="osp-is-active osp-has-tooltip" title="<?php echo osc_esc_html(__('You are member of this group', 'osclass_pay')); ?>"><i class="fa fa-check"></i></span><?php } ?>
                     <div class="osp-left"><div class="osp-h2"><?php echo $g['s_name']; ?></div><div class="osp-desc"><?php echo $g['s_description']; ?></div></div>
                     <div class="osp-right1"><div class="osp-price"><?php echo osp_format_price($g['f_price']); ?></div><div class="osp-cost">/ <?php _e('user', 'osclass_pay'); ?> / <span><?php echo $g['i_days'] . '</span> ' . __('days', 'osclass_pay'); ?></div></div>
                     <div class="osp-cart-keep"><?php if(!$can_prolong && @$g['pk_i_id'] == osp_get_user_group()) { ?><a class="osp_cart_add osp-disabled" href="#" onclick="return false;"><?php echo osp_group_label(@$g['i_rank']); ?></a><?php } else { ?><a class="osp_cart_add" href="<?php echo osp_cart_add(OSP_TYPE_MEMBERSHIP, 1, $g['pk_i_id'], $g['i_days']); ?>"><?php echo osp_group_label($g['pk_i_id'], $g['i_rank']); ?></a><?php } ?></div>
                 </div>
                 <div class="osp-right2">
                     <?php /* ... details part ... */ ?>
                     <?php if(ModelOSP::newInstance()->checkGroupCustom() && $g['s_custom'] <> '') { ?><div class="osp-perc osp-has-tooltip" title="<?php echo osc_esc_html($g['s_custom']); ?>"><?php echo $g['s_custom']; ?></div><?php } ?>
                 </div>
            </div>
            <?php // --- END: VIP Group Item HTML --- ?>
          <?php } ?>
        <?php } // end foreach vip_groups ?>
      <?php } else { ?>
          <p style="text-align: center; margin-top: 20px;"><?php _e('No VIP memberships are currently available.', 'osclass_pay'); ?></p>
      <?php } ?>
    </div>
  </div>
  <!-- --- End VIP Section --- -->

</div> <!-- End osp-body -->


<!-- --- UPDATED: JavaScript for smooth scrolling and item highlight --- -->
<?php
// Inject JS and CSS at the end
?>
<script type="text/javascript">
jQuery(document).ready(function($) {

  // Function to scroll smoothly and highlight items within the section
  function smoothScrollAndHighlight(targetSelector, buttonToActivate) {
    var section = $(targetSelector);
    if(section.length) { // Check if the section exists
      // Remove active class from all nav buttons
      $('.osp-button-nav').removeClass('active');

      $('html, body').animate({
        // Adjust offset if you have a fixed header, -60 is a common starting point
        scrollTop: section.offset().top - 60
      }, 800, function(){ // 800ms duration for scroll

        // --- MODIFICATION START ---
        // Find the group items WITHIN the scrolled-to section
        var itemsToHighlight = section.find('.osp-group');

        // Add highlight class to the items
        itemsToHighlight.addClass('highlight');
        // --- MODIFICATION END ---

        // Add active class to the clicked button
        $(buttonToActivate).addClass('active');

        // Remove highlight from items after 1 second (1000ms)
        setTimeout(function(){
          // --- MODIFICATION START ---
          itemsToHighlight.removeClass('highlight');
          // --- MODIFICATION END ---
        }, 1000);
      });
    }
  }

  // Attach click handlers to buttons (Unchanged)
  $('#show-vip').click(function(e){
    e.preventDefault();
    smoothScrollAndHighlight('#vip-section', this);
  });

  $('#show-standard').click(function(e){
    e.preventDefault();
    smoothScrollAndHighlight('#standard-section', this);
  });

  // Set initial active button (Unchanged)
  $('#show-standard').addClass('active');
  $('#show-vip').removeClass('active');

});
</script>

<!-- --- UPDATED: CSS for item highlight effect and buttons --- -->
<style type="text/css">
  /* Enhanced Button Navigation Area (Unchanged) */
  .osp-group-nav-buttons { display: inline-block; }
  button.osp-button-nav { padding: 10px 25px; font-size: 1em; font-weight: bold; cursor: pointer; border: 1px solid #ccc; background-color: #f5f5f5; color: #555; margin: 0 5px; border-radius: 4px; transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; outline: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
  button.osp-button-nav:hover { background-color: #e9e9e9; border-color: #bbb; color: #333; }
  button.osp-button-nav.active { background-color: #007bff; color: #fff; border-color: #0056b3; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); }
  button.osp-button-nav:focus { box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.5); }

  /* Section Titles (Unchanged) */
  .osp-group-section-title { text-align: center; font-size: 1.6em; margin-bottom: 25px; color: #333; font-weight: normal; }

  /* --- MODIFICATION START: Highlight Effect for Items --- */
  .osp-group.highlight {
    background-color: #fff9c4 !important; /* Use !important if necessary to override inline styles, or adjust specificity */
    /* Add a subtle border or shadow if needed */
     box-shadow: 0 0 10px rgba(212, 172, 13, 0.5);
    border-radius: 5px; /* Optional: Round corners */
    /* Ensure transition applies to the background */
    transition: background-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
  }
   /* Base transition for fade-out on the item itself */
  .osp-group {
    /* Ensure base transition includes properties changed by .highlight */
    transition: background-color 0.7s ease-in-out, box-shadow 0.7s ease-in-out;
    /* Add margin if items are too close together */
    margin-bottom: 15px; /* Example margin */
    border-radius: 5px; /* Match highlight radius */
  }
  /* --- MODIFICATION END --- */

  /* Default Section Spacing (Unchanged - keeps sections apart) */
   #vip-section {
       margin-top: 40px;
       padding-top: 40px; /* Padding helps separate title from items */
       border-top: 1px solid #ccc;
   }
   #standard-section {
       padding-top: 20px; /* Padding helps separate title from items */
   }

</style>