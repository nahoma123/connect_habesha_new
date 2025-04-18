<?php
// Get user data
$user = osc_user();
if (!$user) {
    // Redirect or display error if user not found
    osc_current_web_theme_path('404.php');
    exit;
}

// User Location
$user_location_array = array(osc_user_address(), osc_user_zip(), osc_user_city_area(), osc_user_city(), osc_user_region(), osc_user_country());
$user_location_array = array_filter($user_location_array); // Remove empty elements
$user_location = implode(', ', $user_location_array);

// User Type
$is_company = ($user['b_company'] == 1);

// User Item Count - Ensure this key exists or fetch count differently if needed
$user_item_count = isset($user['i_items']) ? $user['i_items'] : osc_count_user_items(osc_user_id()); // Safer way to get count

// Registration and Online Status
$reg_type = '';
$last_online = '';
if (!empty($user['dt_reg_date'])) {
    $reg_type = sprintf(__('Registered for %s', 'epsilon'), eps_smart_date2($user['dt_reg_date']));
} else {
    $reg_type = __('Registered user', 'epsilon');
}
if (!empty($user['dt_access_date'])) {
    $last_online = sprintf(__('Last online %s', 'epsilon'), eps_smart_date($user['dt_access_date']));
} else {
     $last_online = __('Last online: Unknown', 'epsilon');
}


// User About Info - Use s_info field
$user_about = !empty($user['s_info']) ? nl2br(strip_tags($user['s_info'])) : '';

// Contact Name
$contact_name = (osc_user_name() <> '' ? osc_user_name() : __('Anonymous', 'epsilon'));

// --- Phone Data Preparation ---
// Ensure eps_get_phone exists before calling
if (!function_exists('eps_get_phone')) {
    // Simple fallback if theme function is missing (less ideal)
    function eps_get_phone($phone) {
         $is_logged_in = osc_is_web_user_logged_in();
         $masked = $is_logged_in ? $phone : '***';
         if (strlen($phone) > 4 && !$is_logged_in) {
              $masked = substr($phone, 0, 2) . '***' . substr($phone, -2);
         }
        return array(
            'found' => !empty($phone),
            'masked' => $masked,
            'class' => $is_logged_in ? 'logged' : 'not-logged',
            'url' => $is_logged_in ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : osc_user_login_url(),
            'title' => $is_logged_in ? osc_esc_html($phone) : __('Login to view phone', 'epsilon'),
            'part1' => $is_logged_in ? substr($phone, 0, floor(strlen($phone) / 2)) : '',
            'part2' => $is_logged_in ? substr($phone, floor(strlen($phone) / 2)) : '',
        );
    }
}

// Check the user's preference for showing contact info
$show_phone_on_profile = isset($user['show_on_profile']) ? $user['show_on_profile'] : 'yes'; // Default to yes

$user_phone_mobile_data = eps_get_phone(isset($user['s_phone_mobile']) ? $user['s_phone_mobile'] : '');
$user_phone_land_data = eps_get_phone(isset($user['s_phone_land']) ? $user['s_phone_land'] : '');

// --- Optional Contact Data ---
$primary_methods_str = isset($user['primary_methods']) ? $user['primary_methods'] : '';
$primary_account_val = isset($user['primary_accounts']) ? trim($user['primary_accounts']) : '';
$additional_methods_str = isset($user['additional_methods']) ? $user['additional_methods'] : '';
$additional_account_val = isset($user['additional_accounts']) ? trim($user['additional_accounts']) : '';

// --- Helper Function Definition ---
/**
 * Generates the HTML for an optional contact field, mimicking theme's phone behavior.
 * (Corrected Version)
 */
function generate_contact_methods_enhanced($account_value, $methods_string, $show_flag, $field_label = 'Contact') {
    if (empty($account_value) || $show_flag === 'no') {
        return;
    }

    $methods = !empty($methods_string) ? explode(',', $methods_string) : [];
    $icons_html = '';
    $has_phone_icon = false;

    foreach ($methods as $method) {
        $method = trim(strtolower($method));
        switch ($method) {
            case 'whatsapp': $icons_html .= '<i class="icon-spacing fab fa-whatsapp" title="WhatsApp"></i>'; break;
            case 'telegram': $icons_html .= '<i class="icon-spacing fab fa-telegram-plane" title="Telegram"></i>'; break;
            case 'sms': $icons_html .= '<i class="icon-spacing fas fa-sms" title="SMS"></i>'; break;
            case 'directcall':
                if (!$has_phone_icon) {
                     $icons_html .= '<i class="icon-spacing fas fa-phone-alt" title="Direct Call"></i>';
                     $has_phone_icon = true;
                }
                break;
        }
    }

    $phone_data = function_exists('eps_get_phone') ? eps_get_phone($account_value) : array('found' => false);
    $is_phone = $phone_data['found'];
    $is_logged_in = osc_is_web_user_logged_in();

    $container_tag = 'div';
    $container_classes = ['contact-method']; // Use specific class for styling
    $data_attributes = '';
    $link_href = '#';
    $title_attr = osc_esc_html($field_label);
    $display_value = '';

    if ($is_phone) {
        $container_tag = 'a';
        $container_classes[] = 'phone'; // Crucial: Add 'phone' class for theme JS
        $container_classes[] = $phone_data['class']; // 'logged' or 'not-logged'
        $link_href = $phone_data['url'];
        $title_attr = osc_esc_html($phone_data['title']);
        $data_attributes = sprintf(
            ' data-prefix="tel" data-part1="%s" data-part2="%s"',
            osc_esc_html($phone_data['part1']),
            osc_esc_html($phone_data['part2'])
        );
        $display_value = $phone_data['masked'];
        if (!$has_phone_icon) {
            $icons_html .= '<i class="icon-spacing fas fa-phone-alt" title="' . osc_esc_html(__('Call', 'epsilon')) . '"></i>';
            $has_phone_icon = true;
        }
    } else { // Not a phone number
        if ($is_logged_in) {
            $container_classes[] = 'logged'; // Still add logged class
            $display_value = osc_esc_html($account_value);
             if (filter_var($account_value, FILTER_VALIDATE_URL)) {
                 $container_tag = 'a'; $link_href = $account_value; $title_attr = osc_esc_html(__('Visit link', 'epsilon')); $data_attributes = ' target="_blank" rel="nofollow noreferrer"';
             } elseif (filter_var($account_value, FILTER_VALIDATE_EMAIL)) {
                 $container_tag = 'a'; $link_href = 'mailto:' . $account_value; $title_attr = osc_esc_html(__('Send email', 'epsilon'));
             } else {
                 $container_tag = 'div'; $title_attr = osc_esc_html($field_label . ': ' . $account_value);
             }
        } else { // Logged out, non-phone
            $container_tag = 'a'; // Make clickable for login prompt
            $container_classes[] = 'phone'; // Crucial: Add 'phone' class for theme JS login trigger
            $container_classes[] = 'not-logged';
            $link_href = '#'; // Let theme JS handle redirect
            $title_attr = osc_esc_html(__('Login to view contact', 'epsilon'));
            $len = mb_strlen($account_value);
            if ($len > 5) { $display_value = osc_esc_html(mb_substr($account_value, 0, 2)) . '***' . osc_esc_html(mb_substr($account_value, -2)); }
            elseif ($len > 1) { $display_value = osc_esc_html(mb_substr($account_value, 0, 1)) . '***'; }
            else { $display_value = '***'; }
            $data_attributes .= ' data-login-url="' . osc_esc_html(osc_user_login_url()) . '"';
            // $data_attributes .= ' data-part1="" data-part2=""'; // Add only if absolutely required by theme JS
        }
    }

    if ($container_tag === 'a') { $link_href = osc_esc_html($link_href); }

    // Output with original theme structure in mind: an outer div/a, then span, then icons
    echo sprintf(
        '<%s class="%s"%s title="%s"%s>',
        $container_tag,
        implode(' ', $container_classes),
        ($container_tag === 'a' ? ' href="' . $link_href . '"' : ''),
        $title_attr,
        $data_attributes
    );
    echo sprintf('<span class="contact-value">%s</span>', $display_value); // Add specific class to span if needed
    echo $icons_html;
    echo sprintf('</%s>', $container_tag);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo eps_language_dir(); // Original function ?>" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">

<head>
  <?php osc_current_web_theme_path('head.php'); ?>
  <meta name="robots" content="index, follow" />
  <meta name="googlebot" content="index, follow" />
  <?php // Keep original title logic if preferred ?>
  <title><?php echo meta_title(); ?></title>
  <meta name="title" content="<?php echo osc_esc_html(meta_title()); ?>" />
</head>

<body id="public"> <?php // Original ID ?>
  <?php
  View::newInstance()->_exportVariableToView('user', $user); // Keep original variable exports
  osc_current_web_theme_path('header.php');
  View::newInstance()->_exportVariableToView('user', $user);
  ?>

  <div class="container primary"> <?php // Original class ?>
    <div id="item-side">
      <?php osc_run_hook('user_public_profile_sidebar_top'); ?>
      <div class="box" id="seller"> <?php // Original ID ?>
        <div class="line1">
          <div class="img">
            <img src="<?php echo eps_profile_picture(osc_user_id(), 'small'); // Original function ?>" alt="<?php echo osc_esc_html($contact_name); ?>" />

            <?php // Keep original online check logic ?>
            <?php if (function_exists('eps_user_is_online') && eps_user_is_online(osc_user_id())) { ?>
              <div class="online" title="<?php echo osc_esc_html(__('User is online', 'epsilon')); ?>"></div>
            <?php } else { ?>
              <div class="online off" title="<?php echo osc_esc_html(__('User is offline', 'epsilon')); ?>"></div>
            <?php } ?>
          </div>

          <div class="data">
            <strong class="name"><?php echo $contact_name; ?></strong>
            <div class="items"><?php echo sprintf(__('%d active listings', 'epsilon'), $user_item_count); ?></div>
            <?php if ($is_company) { ?>
              <div class="pro"><?php _e('Pro', 'epsilon'); ?></div>
            <?php } ?>
          </div>
        </div>

        <?php // Original Rating Plugin Hook ?>
        <?php if (function_exists('ur_show_rating_link')) { ?>
          <div class="line-rating">
            <span class="ur-fdb">
              <span class="strs"><?php echo ur_show_rating_stars(osc_user_id()); ?></span>
              <span class="lnk"><?php echo ur_add_rating_link(osc_user_id()); ?></span>
            </span>
          </div>
        <?php } ?>

        <div class="line2">
            <?php if($last_online) { ?><div class="date"><?php echo $last_online; ?></div><?php } ?>
            <?php if($reg_type) { ?><div class="reg"><?php echo $reg_type; ?></div><?php } ?>
        </div>

        <?php // Original Chat Button ?>
        <?php if (function_exists('eps_chat_button') && eps_chat_button(osc_user_id())) { ?>
          <div class="line-chat"><?php echo eps_chat_button(osc_user_id()); ?></div>
        <?php } ?>

        <div class="line3">
          <?php if ($user_location != '') { ?>
            <div class="address"><i class="fas fa-map-marked-alt"></i> <?php echo $user_location; ?></div>
          <?php } ?>

          <?php // --- Display Main Mobile Phone (Original Structure) --- ?>
          <?php if($user_phone_mobile_data['found'] && $show_phone_on_profile !== "no" ) { ?>
                <a class="phone-mobile phone <?php echo $user_phone_mobile_data['class']; ?>" title="<?php echo osc_esc_html($user_phone_mobile_data['title']); ?>" data-prefix="tel" href="<?php echo osc_esc_html($user_phone_mobile_data['url']); ?>" data-part1="<?php echo osc_esc_html($user_phone_mobile_data['part1']); ?>" data-part2="<?php echo osc_esc_html($user_phone_mobile_data['part2']); ?>">
                  <span><?php echo $user_phone_mobile_data['masked']; ?></span>
                  <i class="fas fa-phone-alt"></i> <?php // Original icon placement ?>
                </a>
          <?php } ?>

          <?php // --- Display Main Landline Phone (Original Structure) --- ?>
          <?php if($user_phone_land_data['found'] && $show_phone_on_profile !== "no") { ?>
                <a class="phone-land phone <?php echo $user_phone_land_data['class']; ?>" title="<?php echo osc_esc_html($user_phone_land_data['title']); ?>" data-prefix="tel" href="<?php echo osc_esc_html($user_phone_land_data['url']); ?>" data-part1="<?php echo osc_esc_html($user_phone_land_data['part1']); ?>" data-part2="<?php echo osc_esc_html($user_phone_land_data['part2']); ?>">
                  <span><?php echo $user_phone_land_data['masked']; ?></span>
                  <i class="fas fa-phone-alt"></i> <?php // Original icon placement ?>
                </a>
          <?php } ?>

          <?php
            // --- NEW: Display Primary Optional Contact ---
            generate_contact_methods_enhanced(
                $primary_account_val,
                $primary_methods_str,
                $show_phone_on_profile, // Use the same flag
                __('Primary Contact', 'epsilon') // Label
            );

            // --- NEW: Display Additional Optional Contact ---
            generate_contact_methods_enhanced(
                $additional_account_val,
                $additional_methods_str,
                $show_phone_on_profile, // Use the same flag
                __('Additional Contact', 'epsilon') // Label
            );
          ?>

        </div> <?php // End line3 ?>

      </div> <?php // End #seller box ?>

      <?php // Original Contact Form Button (Corrected Logic) ?>
      <?php
      if (function_exists('getBoolPreference') && getBoolPreference('item_contact_form_disabled') != 1 && osc_logged_user_id() != osc_user_id()) {
          if (function_exists('eps_item_fancy_url')) {
              $contact_url = eps_item_fancy_url('contact_public', array('userId' => osc_user_id()));
      ?>
          <a href="<?php echo osc_esc_html($contact_url); ?>"
             class="open-form public-contact master-button" <?php /* Original classes */ ?>
             data-type="contact_public"> <?php /* Original data-type */ ?>
              <i class="fas fa-envelope-open"></i> <?php /* Original icon */ ?>
              <span><?php _e('Send message', 'epsilon'); ?></span>
          </a>
      <?php
          }
      }
      ?>

      <?php // Original All Seller Items Button ?>
      <a href="<?php echo osc_search_url(array('page' => 'search', 'userId' => osc_user_id())); ?>"
        class="seller-button seller-items"><?php echo __('All seller items', 'epsilon') . ' (' . $user_item_count . ')'; ?></a>

      <?php // Original Website Button ?>
      <?php if (trim(osc_user_website()) <> '') { ?>
        <a href="<?php echo osc_user_website(); ?>" target="_blank" rel="nofollow noreferrer"
          class="seller-button seller-url">
          <i class="fas fa-external-link-alt"></i>
          <span><?php echo rtrim(str_replace(array('https://', 'http://'), '', osc_user_website()), '/'); // Original display logic ?></span>
        </a>
      <?php } ?>

      <?php // Original About Box ?>
      <?php if ($user_about <> '') { ?>
        <div class="box" id="about">
          <strong><?php _e('About seller', 'epsilon'); ?></strong>
          <div><?php echo $user_about; ?></div>
        </div>
      <?php } ?>

      <?php // Original Banner Hook ?>
      <?php if(function_exists('eps_banner')) { echo eps_banner('public_profile_sidebar'); } ?>
      <?php osc_run_hook('user_public_profile_sidebar_bottom'); ?>
    </div>
    <!-- LISTINGS OF SELLER (Original Structure) -->
    <div id="public-main">
      <?php osc_run_hook('user_public_profile_items_top'); ?>

      <?php if(function_exists('eps_banner')) { echo eps_banner('public_profile_top'); } ?>

      <h1><?php echo sprintf(__('%s\'s listings', 'epsilon'), $contact_name); ?></h1>

      <?php if (osc_count_items() > 0) { ?>
        <div class="products list"> <?php // Original class ?>
          <?php
          $c = 1;
          while (osc_has_items()) {
            // Original draw item function call
            if(function_exists('eps_draw_item')) { eps_draw_item($c); }
            else { osc_current_web_theme_path('loop-item.php'); } // Fallback if function doesn't exist

            // Original banner placement logic
            if ($c == 3 && osc_count_items() > 3 && function_exists('eps_banner')) {
              echo eps_banner('public_profile_middle');
            }

            $c++;
          }
          ?>
        </div>

        <?php // Original pagination function call ?>
        <div class="paginate"><?php if(function_exists('eps_fix_arrow')) { echo eps_fix_arrow(osc_pagination_items()); } else { echo osc_pagination_items(); }; ?></div>

      <?php } else { ?>
        <div class="empty"><?php _e('User has no active listings', 'epsilon'); ?></div>
      <?php } ?>

      <?php if(function_exists('eps_banner')) { echo eps_banner('public_profile_bottom'); } ?>
       <?php osc_run_hook('user_public_profile_items_bottom'); ?>
    </div>

  </div>

  <?php osc_current_web_theme_path('footer.php'); ?>

  <?php // NO custom JavaScript formatting needed here, rely on theme's JS ?>

  <?php // Minimal CSS needed ONLY for the new contact methods ?>
  <style>
    /* --- CSS ONLY for Optional Contact Fields --- */

    /* Style the container for the new contact methods */
    /* Try to match the appearance of .phone-mobile / .phone-land */
    .line3 .contact-method {
        display: block; /* Match block display of phone links */
        margin: 6px 0 2px 0; /* Copy margin from original example CSS */
        padding: 4px 8px; /* Adjust padding - experiment */
        border-radius: 8px; /* Copy border-radius */
        background-color: rgba(1, 120, 214, 0.12); /* Copy background */
        font-size: 14px; /* Copy font-size */
        font-weight: 600; /* Copy font-weight */
        width: fit-content; /* Allow width to adjust */
        color: #0178d6; /* Copy color */
        transition: 0.2s; /* Copy transition */
        text-decoration: none; /* Remove underline for links */
        line-height: 1.5;
    }

    /* Ensure links within also get the color */
    .line3 a.contact-method {
        color: #0178d6;
    }
    .line3 a.contact-method:hover {
        background-color: rgba(1, 120, 214, 0.2); /* Simple hover */
        /* color: #005a9e; */
    }

    /* Style the text span inside */
    .contact-method span.contact-value {
        font-family: "Comfortaa", sans-serif; /* Copy font */
        margin-right: 5px; /* Space before icons */
        display: inline-block; /* Ensure margin works */
    }

    /* Style the icons */
    .contact-method i.icon-spacing {
        margin-left: 3px; /* Space between icons */
        font-size: 16px; /* Adjust size if needed */
        color: inherit; /* Inherit color from parent (.contact-method) */
        vertical-align: middle; /* Align icons with text */
    }
     .contact-method i.icon-spacing:first-of-type {
         margin-left: 0; /* No space before the first icon */
     }

    /* --- End Minimal CSS --- */


    /* --- Keep Original CSS from user's first post if needed --- */
    /* Paste the original CSS here if it contained other necessary rules */
    @media screen and (max-width: 767px) {
        body .oc-chat.oc-closed {
            bottom: 55px !important; /* Adjust as needed */
            width: 46px;
            height: 46px;
            min-height: 46px;
        }
    }
    /* Keep any other original styles */

    /* Ensure theme's main phone styles aren't broken */
    .phone-mobile span, .phone-land span {
         margin-right: 5px; /* Restore original spacing if it was changed */
    }
    /* --- End Original CSS --- */
    </style>
</body>
</html>