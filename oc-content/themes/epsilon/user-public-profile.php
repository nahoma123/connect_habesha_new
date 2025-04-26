<?php
// Ensure helper functions are defined (ideally move these to functions.php)


function xethio_format_ethiopian_phone($phoneNumber) {
  // Trim whitespace first
  $num = trim($phoneNumber);

  // If it's empty or clearly not a phone number candidate, return original
  if (empty($num) || strpos($num, '@') !== false || strpos($num, ' ') !== false) {
       // Check if it's ALREADY correctly formatted (+251 9...)
       if (preg_match('/^\+251\s9\d{8}$/', $num)) {
           return $num; // It's already perfect
       }
       // Otherwise, likely not a simple number we should format
       return $phoneNumber;
  }

  // Clean the number: remove all non-digits EXCEPT a leading '+'
  $cleanedNum = preg_replace('/[^\d+]/', '', $num);
  if (strpos($num, '+') !== 0 && strpos($cleanedNum, '+') === 0) {
      // Remove '+' if it wasn't at the start of the original string
      $cleanedNum = str_replace('+', '', $cleanedNum);
  }
   // Remove internal '+' if any snuck through (e.g., +251+9...)
  if (substr_count($cleanedNum, '+') > 1 || (strpos($cleanedNum, '+') > 0) ) {
     $cleanedNum = preg_replace('/\+/', '', $cleanedNum);
     if (strpos($num, '+') === 0) { // Add back leading plus if original had it
          $cleanedNum = '+' . $cleanedNum;
     }
  }

  // Use regex to capture the core 9 digits (9 followed by 8 digits)
  if (preg_match('/^(?:\+?251|0)?(9\d{8})$/', $cleanedNum, $matches)) {
      // Found a valid pattern, format it with the space
      return '+251 ' . $matches[1]; // $matches[1] contains '9XXXXXXXX'
  }

  // No valid Ethiopian pattern found, return the original input
  return $phoneNumber;
}


function generate_contact_methods_enhanced($account_value, $methods_string, $field_label = 'Contact') {
  // Format the input value FIRST using the improved function
  $formatted_account_value = function_exists('xethio_format_ethiopian_phone') ? xethio_format_ethiopian_phone($account_value) : $account_value;

  if (empty($formatted_account_value)) {
      return; // Nothing to display
  }

  $methods = !empty($methods_string) ? explode(',', $methods_string) : [];
  $icons_html = '';
  $has_phone_icon = false;

  // Generate icons based on methods_string
  foreach ($methods as $method) {
      $method = trim(strtolower($method));
      switch ($method) {
          case 'whatsapp':
              $icons_html .= '<i class="icon-spacing fab fa-whatsapp" title="WhatsApp"></i>';
              break;
          case 'telegram':
              $icons_html .= '<i class="icon-spacing fab fa-telegram-plane" title="Telegram"></i>';
              break;
          case 'sms':
              $icons_html .= '<i class="icon-spacing fas fa-sms" title="SMS"></i>';
              break;
          case 'directcall':
              // Only add the phone icon once, even if 'directcall' appears multiple times
              if (!$has_phone_icon) {
                  $icons_html .= '<i class="icon-spacing fas fa-phone-alt" title="Direct Call"></i>';
                  $has_phone_icon = true;
              }
              break;
      }
  }

  // Check if the formatted value is likely a phone number using eps_get_phone
  $phone_data = function_exists('eps_get_phone') ? eps_get_phone($formatted_account_value) : array('found' => false);
  $is_phone = isset($phone_data['found']) && $phone_data['found'];
  $is_logged_in = osc_is_web_user_logged_in();

  $container_tag = 'div'; // Default to div
  $container_classes = ['contact-method'];
  $data_attributes = '';
  $link_href = '#';
  $title_attr = osc_esc_html($field_label);
  $display_value = '';

  if ($is_phone) {
      // --- Phone Number Logic ---
      $container_classes[] = 'phone'; // It's definitely a phone

      if ($is_logged_in) {
          // --- LOGGED IN: Show full number, direct tel: link ---
          $container_tag = 'a';
          $container_classes[] = 'logged'; // Class for styling logged-in state
          $display_value = osc_esc_html($phone_data['phone']); // Use the FULL, potentially formatted number
          $link_href = 'tel:' . preg_replace('/\s+/', '', $phone_data['phone']); // Create clean tel: link
          // Use a title indicating the action (call) or just the number itself
          $title_attr = osc_esc_html(sprintf(__('Call %s', 'epsilon'), $phone_data['phone']));
          $data_attributes = ''; // No data attributes needed for reveal

      } else {
          // --- NOT LOGGED IN: Show masked number, link to login ---
          $container_tag = 'a';
          $container_classes[] = 'not-logged'; // Use class from eps_get_phone or set here
          $display_value = $phone_data['masked']; // Show the masked version
          $link_href = osc_esc_html($phone_data['url']); // URL should be login URL from eps_get_phone
          $title_attr = osc_esc_html($phone_data['title']); // Title should indicate login required
          // Optional: Add data attribute for login URL if needed by JS elsewhere, but link already goes there
          $data_attributes = ' data-login-url="' . osc_esc_html(osc_user_login_url()) . '"';
      }

      // Optional: Add specific phone type class if needed (e.g., based on format)
      // if (strpos($phone_data['phone'], '+251 ') === 0) {
      //     $container_classes[] = 'phone-mobile';
      // }

  } else {
      // --- Not a Phone Number (Email, URL, Plain Text) Logic ---
      // Use $formatted_account_value as it's not treated as a phone
      if ($is_logged_in) {
          $container_classes[] = 'logged'; // General logged-in class
          $display_value = osc_esc_html($formatted_account_value); // Show the potentially formatted value

          if (filter_var($formatted_account_value, FILTER_VALIDATE_URL)) {
              $container_tag = 'a';
              $link_href = $formatted_account_value;
              $title_attr = osc_esc_html(__('Visit link', 'epsilon'));
              $data_attributes = ' target="_blank" rel="nofollow noreferrer"';
          } elseif (filter_var($formatted_account_value, FILTER_VALIDATE_EMAIL)) {
              $container_tag = 'a';
              $link_href = 'mailto:' . $formatted_account_value;
              $title_attr = osc_esc_html(__('Send email', 'epsilon'));
          } else {
              // Just plain text
              $container_tag = 'div';
              $title_attr = osc_esc_html($field_label . ': ' . $formatted_account_value);
              $link_href = '#'; // No link for div
          }
      } else { // Not a phone, not logged in
          $container_tag = 'a'; // Link to login to view
          $container_classes[] = 'not-logged'; // Generic class for not logged in
          $link_href = osc_user_login_url();
          $title_attr = osc_esc_html(__('Login to view contact', 'epsilon'));

          // Simple masking for non-phone, non-logged in
          $len = mb_strlen($formatted_account_value);
          if ($len > 5) {
              $display_value = osc_esc_html(mb_substr($formatted_account_value, 0, 2)) . '***' . osc_esc_html(mb_substr($formatted_account_value, -2));
          } elseif ($len > 1) {
              $display_value = osc_esc_html(mb_substr($formatted_account_value, 0, 1)) . '***';
          } else {
              $display_value = '***';
          }
          $data_attributes = ' data-login-url="' . osc_esc_html(osc_user_login_url()) . '"';
      }
  }

  // Final Output Generation
  // Set href attribute only if it's a link and not linking to '#'
  $link_href_attr = '';
  if ($container_tag === 'a' && $link_href !== '#') {
      $link_href_attr = ' href="' . osc_esc_html($link_href) . '"';
  }

  // Build the HTML output
  echo sprintf(
      '<%s class="%s"%s title="%s"%s>',
      $container_tag,
      implode(' ', array_unique($container_classes)), // Use array_unique to avoid duplicate classes
      $link_href_attr,
      $title_attr,
      $data_attributes // Includes login URL if not logged in and not phone, empty otherwise for phones
  );
  // Use span.contact-value for the displayed text
  echo sprintf('<span class="contact-value">%s</span>', $display_value);
  echo $icons_html; // Append icons (WhatsApp, Telegram, etc.)
  echo sprintf('</%s>', $container_tag);
}


?>

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

// Check the user's preference for showing contact info
$show_phone_on_profile = isset($user['show_on_profile']) ? $user['show_on_profile'] : 'yes'; // Default to yes

$user_phone_mobile_data = eps_get_phone(isset($user['s_phone_mobile']) ? $user['s_phone_mobile'] : '');
$user_phone_land_data = eps_get_phone(isset($user['s_phone_land']) ? $user['s_phone_land'] : '');

// --- Optional Contact Data ---
$primary_methods_str = isset($user['primary_methods']) ? $user['primary_methods'] : '';
$primary_account_val = isset($user['primary_accounts']) ? trim($user['primary_accounts']) : '';
$additional_methods_str = isset($user['additional_methods']) ? $user['additional_methods'] : '';
$additional_account_val = isset($user['additional_accounts']) ? trim($user['additional_accounts']) : '';

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

          <?php
                // --- CORRECTED: Use $user variable ---
                $formatted_mobile = (function_exists('xethio_format_ethiopian_phone'))
                                    ? xethio_format_ethiopian_phone(isset($user['s_phone_mobile']) ? $user['s_phone_mobile'] : '') // Use $user
                                    : (isset($user['s_phone_mobile']) ? $user['s_phone_mobile'] : ''); // Use $user
                $user_phone_mobile_data = eps_get_phone($formatted_mobile);
              ?>
              <?php // The $show_phone_on_profile check was already correctly using the variable derived from $user at the top ?>
              <?php if($user_phone_mobile_data['found'] && $show_phone_on_profile=="yes") { ?>
                    <a class="phone-mobile phone <?php echo $user_phone_mobile_data['class']; ?>" title="<?php echo osc_esc_html($user_phone_mobile_data['title']); ?>" data-prefix="tel" href="<?php echo osc_esc_html($user_phone_mobile_data['url']); ?>" data-part1="<?php echo osc_esc_html($user_phone_mobile_data['part1']); ?>" data-part2="<?php echo osc_esc_html($user_phone_mobile_data['part2']); ?>">
                      <span><?php echo $user_phone_mobile_data['masked']; ?></span><i class="fas fa-phone-alt"></i>
                    </a>
              <?php } ?>

              <?php
                // --- CORRECTED: Use $user variable ---
                $formatted_land = (function_exists('xethio_format_ethiopian_phone'))
                                  ? xethio_format_ethiopian_phone(isset($user['s_phone_land']) ? $user['s_phone_land'] : '') // Use $user
                                  : (isset($user['s_phone_land']) ? $user['s_phone_land'] : ''); // Use $user
                $user_phone_land_data = eps_get_phone($formatted_land);
              ?>
              <?php if($user_phone_land_data['found']) { // Landline doesn't seem to have the show_on_profile check in either file ?>
                    <a class="phone-land phone <?php echo $user_phone_land_data['class']; ?>" title="<?php echo osc_esc_html($user_phone_land_data['title']); ?>" data-prefix="tel" href="<?php echo osc_esc_html($user_phone_land_data['url']); ?>" data-part1="<?php echo osc_esc_html($user_phone_land_data['part1']); ?>" data-part2="<?php echo osc_esc_html($user_phone_land_data['part2']); ?>">
                      <span><?php echo $user_phone_land_data['masked']; ?></span><i class="fas fa-phone-alt"></i>
                    </a>
              <?php } ?>

              <?php
                // Display Primary Optional Contact (This part was already correct)
                generate_contact_methods_enhanced( $primary_account_val, $primary_methods_str,  __('Primary Contact', 'epsilon') );
                // Display Additional Optional Contact (This part was already correct)
                generate_contact_methods_enhanced( $additional_account_val, $additional_methods_str,  __('Additional Contact', 'epsilon') );
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
    #seller .line3 .phone i {
      font-size: 16px;
    }

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
<script>
  /**
 * Formats a raw Ethiopian phone number string into +251 9XXXXXXXXX format.
 *
 * @param {string} phoneNumber The raw phone number string.
 * @returns {string} The formatted number or the original string if not applicable.
 */


</script>
</body>

</html>