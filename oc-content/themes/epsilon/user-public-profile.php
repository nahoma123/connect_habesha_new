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


function xethio_format_ethiopian_phone($phoneNumber) {
  // Trim whitespace first
  $num = trim($phoneNumber);

  // If it's empty, return original
  if (empty($num)) {
      return $phoneNumber;
  }

  // Avoid formatting obvious non-numbers or already formatted numbers
  if (strpos($num, '@') !== false || strpos($num, ' ') !== false || !preg_match('/^[+0-9]+$/', preg_replace('/\s+/', '', $num)) ) {
       // If it contains '@', spaces already, or non-numeric/non-+ characters, assume it's not a simple number to format.
       // Handle potentially already correct format '+251 9...'
       if (preg_match('/^\+251\s9\d{8}$/', $num)) {
           return $num; // It's already correct
       }
      // Otherwise return original for things like usernames, complex strings etc.
      return $phoneNumber;
  }


  // Remove non-digit characters, except keep '+' if it's at the start
  $cleanedNum = preg_replace('/\D/', '', $num); // Remove all non-digits first
   if (strpos($num, '+') === 0) {
       $cleanedNum = '+' . $cleanedNum; // Add plus back if it was originally there
   }


  $coreDigits = null;

  // Try matching different Ethiopian formats
  if (strpos($cleanedNum, '+2519') === 0 && strlen($cleanedNum) === 13) { // +2519XXXXXXXX
      $coreDigits = substr($cleanedNum, 4);
  } elseif (strpos($cleanedNum, '2519') === 0 && strlen($cleanedNum) === 12) { // 2519XXXXXXXX
      $coreDigits = substr($cleanedNum, 3);
  } elseif (strpos($cleanedNum, '09') === 0 && strlen($cleanedNum) === 10) { // 09XXXXXXXX
      $coreDigits = substr($cleanedNum, 2); // Get the 8 digits after '09'
  } elseif (strpos($cleanedNum, '9') === 0 && strlen($cleanedNum) === 9) { // 9XXXXXXXX
      $coreDigits = substr($cleanedNum, 1); // Get the 8 digits after '9'
  }

  // If we found the 8 core digits after the prefix '9'
  if ($coreDigits !== null && strlen($coreDigits) === 8) {
      // Format as +251 9XXXXXXXXX (assuming the prefix was 9)
      return '+251 9' . $coreDigits;
  } else {
      // Return original if no valid pattern found or core digits length mismatch
      return $phoneNumber;
  }
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

      // --- ADD THIS LINE AT THE VERY BEGINNING ---
      $formatted_phone = function_exists('xethio_format_ethiopian_phone') ? xethio_format_ethiopian_phone($phone) : $phone;
      // --- Use $formatted_phone instead of $phone below ---
  
      $is_logged_in = osc_is_web_user_logged_in();
      $show_pref = osc_user() ? (isset(osc_user()['show_on_profile']) ? osc_user()['show_on_profile'] : 'yes') : 'yes'; // Get preference
  
  
      $masked = $formatted_phone; // Start with the formatted number
      $is_visible = $is_logged_in || ($show_pref !== 'no'); // Simplified visibility logic, refine as needed
      $should_mask = !$is_visible || ($show_pref === 'no' && !$is_logged_in); // Mask if preference is no AND not logged in, or generally not visible
  
      // Apply masking *to the formatted number* if needed
      if ($should_mask && !empty($formatted_phone)) {
           $masked = '***'; // Simple mask
           // More complex masking like the original example:
            $len = mb_strlen($formatted_phone);
            if ($len > 5) { $masked = mb_substr($formatted_phone, 0, 2) . '***' . mb_substr($formatted_phone, -2); }
            elseif ($len > 1) { $masked = mb_substr($formatted_phone, 0, 1) . '***'; }
            else { $masked = '***'; }
      }
  
  
      // Generate parts *from the formatted number* for theme JS reveal
      $part1 = '';
      $part2 = '';
      if (!empty($formatted_phone) && !$should_mask) { // Generate parts only if visible/not masked
          $part1 = mb_substr($formatted_phone, 0, floor(mb_strlen($formatted_phone) / 2));
          $part2 = mb_substr($formatted_phone, floor(mb_strlen($formatted_phone) / 2));
      }
  
  
      return array(
          'found' => !empty($formatted_phone),
          'raw'   => $formatted_phone, // Keep the formatted raw value
          'masked' => $masked,        // The potentially masked value for display
          'class' => $is_logged_in ? 'logged' : 'not-logged', // Class still based on login status for theme JS trigger
          'url' => $is_logged_in ? 'tel:' . preg_replace('/[^0-9+]/', '', $formatted_phone) : osc_user_login_url(),
          'title' => $is_logged_in ? osc_esc_html($formatted_phone) : __('Login to view phone', 'epsilon'),
          'part1' => osc_esc_html($part1), // Use parts generated from formatted number
          'part2' => osc_esc_html($part2), // Use parts generated from formatted number
      );
  }}

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
/**
 * Generates the HTML for an optional contact field (primary or additional account)
 * on the public user profile.
 *
 * It formats Ethiopian phone numbers using xethio_format_ethiopian_phone() first,
 * then utilizes the theme's eps_get_phone() function to handle masking,
 * splitting for reveal (data-part1/2), and CSS class assignment (logged/not-logged).
 * Handles non-phone values (usernames, emails, URLs) appropriately based on login status.
 *
 * @param string $account_value The raw account value from the database.
 * @param string $methods_string Comma-separated string of communication methods (e.g., "Telegram,WhatsApp").
 * @param string $show_flag User's preference ('yes' or 'no') whether to show contact info.
 * @param string $field_label Label for the field (e.g., "Primary Contact").
 * @return void Outputs HTML directly.
 */
function generate_contact_methods_enhanced($account_value, $methods_string, $show_flag, $field_label = 'Contact') {

  // 1. Format the account value first (handles Ethiopian phone numbers)
  $formatted_account_value = function_exists('xethio_format_ethiopian_phone') ? xethio_format_ethiopian_phone($account_value) : $account_value;

  // 2. Check if we should display anything
  // Skip if empty *after formatting* or if show_flag is 'no'
  if (empty($formatted_account_value) || $show_flag === 'no') {
      return; // Output nothing
  }

  // 3. Process communication method icons
  $methods = !empty($methods_string) ? explode(',', $methods_string) : [];
  $icons_html = '';
  $has_phone_icon = false; // Track if a call-related icon is already added

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
              // Avoid duplicate phone icons if multiple call methods are selected
              if (!$has_phone_icon) {
                  $icons_html .= '<i class="icon-spacing fas fa-phone-alt" title="Direct Call"></i>';
                  $has_phone_icon = true;
              }
              break;
              // Add other icons here if needed
      }
  }

  // 4. Use theme's function to check if it's a phone and get masking/parts data
  // IMPORTANT: Assumes eps_get_phone has been modified to accept and use the pre-formatted number!
  $phone_data = function_exists('eps_get_phone') ? eps_get_phone($formatted_account_value) : array('found' => false);

  // Determine if eps_get_phone recognized the formatted value as a phone number
  $is_phone = isset($phone_data['found']) && $phone_data['found'];
  $is_logged_in = osc_is_web_user_logged_in();

  // 5. Prepare variables for HTML output
  $container_tag = 'div'; // Default container tag
  $container_classes = ['contact-method']; // Base CSS class
  $data_attributes = ''; // For data-* attributes used by JS
  $link_href = '#'; // Default link target
  $title_attr = osc_esc_html($field_label); // Default title attribute
  $display_value = ''; // The text to display inside the container

  // 6. Determine output based on type (phone vs. other) and login status
  if ($is_phone) {
      // --- It's a Phone Number ---
      // Use the data returned by the modified eps_get_phone
      $container_tag = 'a'; // Phone numbers are usually clickable links
      $container_classes[] = 'phone'; // Crucial class for theme JS (reveal/login prompt)
      $container_classes[] = $phone_data['class']; // Adds 'logged' or 'not-logged' based on login status

      $link_href = $phone_data['url']; // tel: link or login URL
      $title_attr = osc_esc_html($phone_data['title']); // Title (full number or login prompt)

      // Add data attributes needed by theme's JS to reconstruct the number on click/hover
      // These parts are generated *from the formatted number* inside the modified eps_get_phone
      $data_attributes = sprintf(
          ' data-prefix="tel" data-part1="%s" data-part2="%s"',
          isset($phone_data['part1']) ? $phone_data['part1'] : '', // Use the parts from eps_get_phone
          isset($phone_data['part2']) ? $phone_data['part2'] : ''  // Use the parts from eps_get_phone
      );

      // The value to display initially (could be masked '***' or partially masked)
      $display_value = $phone_data['masked'];

      // Add a default phone icon if 'DirectCall' wasn't explicitly chosen
      if (!$has_phone_icon) {
          $icons_html .= '<i class="icon-spacing fas fa-phone-alt" title="' . osc_esc_html(__('Call', 'epsilon')) . '"></i>';
          $has_phone_icon = true;
      }

  } else {
      // --- Not a Phone Number (e.g., Username, Email, URL) ---
      if ($is_logged_in) {
          // --- User is Logged In ---
          $container_classes[] = 'logged'; // Mark as logged in for consistent styling maybe
          $display_value = osc_esc_html($formatted_account_value); // Show the full (formatted) value

          // Check if it's a clickable type (URL or Email)
          if (filter_var($formatted_account_value, FILTER_VALIDATE_URL)) {
              $container_tag = 'a';
              $link_href = $formatted_account_value;
              $title_attr = osc_esc_html(__('Visit link', 'epsilon'));
              $data_attributes = ' target="_blank" rel="nofollow noreferrer"'; // Open external links safely
          } elseif (filter_var($formatted_account_value, FILTER_VALIDATE_EMAIL)) {
              $container_tag = 'a';
              $link_href = 'mailto:' . $formatted_account_value;
              $title_attr = osc_esc_html(__('Send email', 'epsilon'));
          } else {
              // Just display as text (e.g., username)
              $container_tag = 'div';
              $title_attr = osc_esc_html($field_label . ': ' . $formatted_account_value); // More descriptive title
          }

      } else {
          // --- User is Logged Out ---
          $container_tag = 'a'; // Make it clickable to trigger login prompt
          $container_classes[] = 'phone'; // Add 'phone' class - likely REQUIRED by theme JS to trigger login action
          $container_classes[] = 'not-logged'; // Mark as not logged in

          $link_href = '#'; // Let the theme's JS handle the redirect based on class/data attributes
          $title_attr = osc_esc_html(__('Login to view contact', 'epsilon'));

          // Mask the non-phone value
          $len = mb_strlen($formatted_account_value);
          if ($len > 5) {
              $display_value = osc_esc_html(mb_substr($formatted_account_value, 0, 2)) . '***' . osc_esc_html(mb_substr($formatted_account_value, -2));
          } elseif ($len > 1) {
              $display_value = osc_esc_html(mb_substr($formatted_account_value, 0, 1)) . '***';
          } else {
              $display_value = '***';
          }

          // Add data attribute for login URL if theme JS needs it
          $data_attributes = ' data-login-url="' . osc_esc_html(osc_user_login_url()) . '"';
      }
  }

  // 7. Assemble and Output the HTML
  // Escape link href if it's an actual link
  if ($container_tag === 'a') {
      $link_href = osc_esc_html($link_href);
  }

  // Build the opening tag
  echo sprintf(
      '<%s class="%s"%s title="%s"%s>',
      $container_tag,                          // 'a' or 'div'
      implode(' ', $container_classes),        // CSS classes (e.g., "contact-method phone logged")
      ($container_tag === 'a' ? ' href="' . $link_href . '"' : ''), // Add href only for <a> tags
      $title_attr,                             // Tooltip text
      $data_attributes                         // data-* attributes for JS
  );

  // Output the visible text (masked or full value)
  echo sprintf('<span class="contact-value">%s</span>', $display_value);

  // Output the communication method icons
  echo $icons_html;

  // Build the closing tag
  echo sprintf('</%s>', $container_tag);

} // --- End of function generate_contact_methods_enhanced ---

?>



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
<script>
  /**
 * Formats a raw Ethiopian phone number string into +251 9XXXXXXXXX format.
 *
 * @param {string} phoneNumber The raw phone number string.
 * @returns {string} The formatted number or the original string if not applicable.
 */
function formatPhoneNumber(phoneNumber) {
    // Trim whitespace first
    let num = phoneNumber.trim();

    // If it's empty, contains '*' (masked), or already has a space after +251, return original
    // (Refined check to avoid re-formatting already correct numbers)
    if (!num || num.includes('*') || /^\+251\s/.test(num)) {
        return phoneNumber;
    }

    // Remove non-digit characters, except keep '+' if it's at the start
    let cleanedNum = num.startsWith('+') ? '+' + num.substring(1).replace(/\D/g, '') : num.replace(/\D/g, '');

    let coreDigits = null;

    // Try matching different Ethiopian formats
    if (cleanedNum.startsWith('+2519') && cleanedNum.length === 13) { // +2519XXXXXXXX
        coreDigits = cleanedNum.substring(4);
    } else if (cleanedNum.startsWith('2519') && cleanedNum.length === 12) { // 2519XXXXXXXX
        coreDigits = cleanedNum.substring(3);
    } else if (cleanedNum.startsWith('09') && cleanedNum.length === 10) { // 09XXXXXXXX
        coreDigits = cleanedNum.substring(2); // Get the 8 digits after '09'
    } else if (cleanedNum.startsWith('9') && cleanedNum.length === 9) { // 9XXXXXXXX
        coreDigits = cleanedNum.substring(1); // Get the 8 digits after '9'
    }

    // If we found the 8 core digits after the prefix '9'
    if (coreDigits && coreDigits.length === 8) {
        // Format as +251 9XXXXXXXXX (assuming the prefix was 9)
        return '+251 9' + coreDigits; // Concatenate directly
    } else {
        // Return original if no valid pattern found or core digits length mismatch
        return phoneNumber;
    }
}

function applyFormattingToElement(spanElement) {
    if (!spanElement) {
        console.log("ApplyFormatting: No span element found."); // DEBUG
        return;
    }

    const parentLink = spanElement.closest('a'); // Get the parent link
     // ** Check parent class and span content again before formatting **
    if (parentLink && parentLink.classList.contains('logged')) {
        const originalValue = spanElement.textContent;
        // Avoid re-formatting if it already has spaces
        if (originalValue && !originalValue.includes(' ')) {
            const formattedValue = formatPhoneNumber(originalValue);
            if (originalValue !== formattedValue) {
                spanElement.textContent = formattedValue;
                console.log("ApplyFormatting: Applied format:", formattedValue, "to element:", spanElement); // DEBUG
            } else {
                 console.log("ApplyFormatting: Value unchanged after format attempt on:", originalValue); // DEBUG
            }
        } else {
             console.log("ApplyFormatting: Skipped (already has spaces or empty):", originalValue); // DEBUG
        }
    } else {
         console.log("ApplyFormatting: Skipped (parent link doesn't have .logged class). Parent:", parentLink); // DEBUG
    }

}

/**
 * Runs formatting on initially visible phone numbers.
 */
function runInitialFormatting() {
    console.log("Running initial formatting..."); // Debug

    // Define the selector
    const selector = 'a.phone.logged span, a.contact-method.phone.logged span.contact-value';
    console.log("Using selector:", selector); // Debug: Log the selector

    // Execute the query
    const spansToFormat = document.querySelectorAll(selector);

    // Log the result
    console.log("Found spans:", spansToFormat); // Debug: See if the NodeList is empty or contains elements

    // Check the count
    if (spansToFormat.length === 0) {
        console.warn("No elements found matching the selector for initial formatting. Check HTML classes ('phone', 'logged', 'contact-method', 'contact-value') and structure.");
    } else {
        // Proceed with formatting if elements were found
        spansToFormat.forEach(span => {
            // Add logging inside the loop for detailed info
            console.log("Attempting to format span:", span, "with text:", span.textContent);
            applyFormattingToElement(span);
        });
    }
}

// --- Run on initial page load ---
// Ensure DOM is fully ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runInitialFormatting);
} else {
    // DOMContentLoaded has already fired
    setTimeout(runInitialFormatting, 0); // Run immediately but defer slightly
}


// --- Re-apply formatting after theme JS reveals number on click ---
const contactContainer = document.querySelector('#seller .line3');
if (contactContainer) {
    console.log("Setting up click listener..."); // DEBUG
    contactContainer.addEventListener('click', function(event) {
        // Target the link that was *just* clicked and might reveal
        const clickedLink = event.target.closest('a.phone'); // Target any phone link clicked

        if (clickedLink && clickedLink.classList.contains('not-logged')) { // Only act if it was masked
            console.log("Clicked masked link:", clickedLink); // DEBUG

            // ** Use MutationObserver for reliability instead of setTimeout **
            const observer = new MutationObserver((mutationsList, observerInstance) => {
                 // Check if the 'logged' class was added or 'not-logged' was removed
                if (clickedLink.classList.contains('logged')) {
                    console.log("MutationObserver: Detected .logged class added to:", clickedLink); // DEBUG
                    const spanToFormat = clickedLink.querySelector('span') || clickedLink.querySelector('span.contact-value');
                    if (spanToFormat) {
                        applyFormattingToElement(spanToFormat);
                    }
                    observerInstance.disconnect(); // Stop observing once formatted
                }
            });

            // Observe attribute changes (specifically the 'class' attribute) on the clicked link
            observer.observe(clickedLink, { attributes: true, attributeFilter: ['class'] });

            // Optional: Timeout as a fallback in case mutation observer doesn't trigger (shouldn't be needed)
             setTimeout(() => {
                 if(observer) observer.disconnect(); // Disconnect fallback timeout observer if it's still running
                 console.log("Observer fallback timeout reached for:", clickedLink); // DEBUG
             }, 1000); // 1 second fallback

        } else if (clickedLink && clickedLink.classList.contains('logged')){
             console.log("Clicked an already revealed link:", clickedLink); // DEBUG
             // Optionally re-format even revealed ones on click? Usually not needed.
             // const spanToFormat = clickedLink.querySelector('span') || clickedLink.querySelector('span.contact-value');
             // if(spanToFormat) applyFormattingToElement(spanToFormat);
        }
    });
} else {
    console.error("Could not find contact container '#seller .line3'."); // DEBUG
}

</script>
</body>

</html>