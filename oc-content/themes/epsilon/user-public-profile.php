<?php
$user = osc_user();

$user_location_array = array(osc_user_address(), osc_user_zip(), osc_user_city_area(), osc_user_city(), osc_user_region(), osc_user_country());
$user_location_array = array_filter($user_location_array);
$user_location = implode(', ', $user_location_array);

$is_company = false;
$user_item_count = $user['i_items'];

if ($user['b_company'] == 1) {
  $is_company = true;
}

// GET REGISTRATION DATE AND TYPE
$reg_type = '';
$last_online = '';

if ($user && $user['dt_reg_date'] <> '') {
  $reg_type = sprintf(__('Registered for %s', 'epsilon'), eps_smart_date2($user['dt_reg_date']));
} else if ($user) {
  $reg_type = __('Registered user', 'epsilon');
} else {
  $reg_type = __('Unregistered user', 'epsilon');
}


if ($user) {
  $last_online = sprintf(__('Last online %s', 'epsilon'), eps_smart_date($user['dt_access_date']));
}

$user_about = nl2br(strip_tags(osc_user_info()));
$contact_name = (osc_user_name() <> '' ? osc_user_name() : __('Anonymous', 'epsilon'));

$user_phone_mobile_data = eps_get_phone($user['s_phone_mobile']);


$show_phone = $user['show_on_profile'];
if ($show_phone == 'no') {
  $user_phone_mobile_data['found'] = false;
}

$user_phone_land_data = eps_get_phone($user['s_phone_land']);
$user_phone_mobile_data = eps_get_phone(isset($user['s_phone_mobile']) ? $user['s_phone_mobile'] : '');
$user_phone_land_data = eps_get_phone(isset($user['s_phone_land']) ? $user['s_phone_land'] : '');
$show_phone_on_profile = $user['show_on_profile'];

?>
<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo eps_language_dir(); ?>"
  lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">

<head>
  <?php osc_current_web_theme_path('head.php'); ?>
  <meta name="robots" content="index, follow" />
  <meta name="googlebot" content="index, follow" />
</head>

<body id="public">
  <?php
  View::newInstance()->_exportVariableToView('user', $user);
  osc_current_web_theme_path('header.php');
  View::newInstance()->_exportVariableToView('user', $user);
  ?>

  <div class="container primary">
    <div id="item-side">
      <?php osc_run_hook('user_public_profile_sidebar_top'); ?>

      <div class="box" id="seller">
        <div class="line1">
          <div class="img">
            <img src="<?php echo eps_profile_picture(osc_user_id(), 'small'); ?>"
              alt="<?php echo osc_esc_html($contact_name); ?>" />

            <?php if (eps_user_is_online(osc_user_id())) { ?>
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

        <?php if (function_exists('ur_show_rating_link')) { ?>
          <div class="line-rating">
            <span class="ur-fdb">
              <span class="strs"><?php echo ur_show_rating_stars(osc_user_id(), osc_user_email()); ?></span>
              <span class="lnk"><?php echo ur_add_rating_link(osc_user_id()); ?></span>
            </span>
          </div>
        <?php } ?>

        <div class="line2">
          <div class="date"><?php echo $last_online; ?></div>
          <div class="reg"><?php echo $reg_type; ?></div>
        </div>

        <?php if (eps_chat_button(osc_user_id())) { ?>
          <div class="line-chat"><?php echo eps_chat_button(osc_user_id()); ?></div>
        <?php } ?>

        <div class="line3">
          <?php if ($user_location != '') { ?>
            <div class="address"><i class="fas fa-map-marked-alt"></i> <?php echo $user_location; ?></div>
          <?php } ?>


          <?php if($user_phone_mobile_data['found'] && !($show_phone_on_profile == "no") ) { ?>
                <a class="phone-mobile phone <?php echo $user_phone_mobile_data['class']; ?>" title="<?php echo osc_esc_html($user_phone_mobile_data['title']); ?>" data-prefix="tel" href="<?php echo $user_phone_mobile_data['url']; ?>" data-part1="<?php echo osc_esc_html($user_phone_mobile_data['part1']); ?>" data-part2="<?php echo osc_esc_html($user_phone_mobile_data['part2']); ?>">
                  <span><?php echo $user_phone_mobile_data['masked']; ?></span>
                  <i class="fas fa-phone-alt"></i>
                </a>
              <?php } ?>

              <?php if($user_phone_land_data['found'] && !($show_phone_on_profile == "no")) { ?>
                <a class="phone-land phone <?php echo $user_phone_land_data['class']; ?>" title="<?php echo osc_esc_html($user_phone_land_data['title']); ?>" data-prefix="tel" href="<?php echo $user_phone_land_data['url']; ?>" data-part1="<?php echo osc_esc_html($user_phone_land_data['part1']); ?>" data-part2="<?php echo osc_esc_html($user_phone_land_data['part2']); ?>">
                  <span><?php echo $user_phone_land_data['masked']; ?></span>
                  <i class="fas fa-phone-alt"></i>
                </a>
              <?php } ?>
        </div>

        <?php
// Parse primary and additional methods/accounts
$primary_methods = !empty($user['primary_methods']) ? explode(',', $user['primary_methods']) : [];
$primary_account = !empty($user['primary_accounts']) ? trim($user['primary_accounts']) : '';
$additional_methods = !empty($user['additional_methods']) ? explode(',', $user['additional_methods']) : [];
$additional_account = !empty($user['additional_accounts']) ? trim($user['additional_accounts']) : '';

// Function to generate icons for a given account and methods
function generate_contact_methods($account, $methods, $show_phone_on_profilehow) {
    if (empty($account)) {
        return;
    }

    $icons = [];
    foreach ($methods as $method) {
        $method = trim($method);
        switch (strtolower($method)) {
            case 'whatsapp':
                $icons[] = '<i class="icon-spacing fab fa-whatsapp" title="WhatsApp"></i>';
                break;
            case 'telegram':
                $icons[] = '<i class="icon-spacing fab fa-telegram-plane" title="Telegram"></i>';
                break;
            case 'sms':
                $icons[] = '<i class="icon-spacing fas fa-sms" title="SMS"></i>';
                break;
            case 'directcall':
                $icons[] = '<i class="icon-spacing fas fa-phone-alt" title="DirectCall"></i>';
                break;
            default:
                $icons[] = '<i class="icon-spacing fas fa-question-circle" title="Unknown"></i>';
                break;
        }
    }

    if (!empty($icons)) {
        echo '<div class="contact-method phone-mobile phone">';
        echo '<span>' . $account . '</span>';
        echo implode('', $icons); // Display all icons
        echo '</div>';
    }
}

// Display primary methods with their shared account
generate_contact_methods($primary_account, $primary_methods, $user['show_on_profile']);

// Display additional methods with their shared account
generate_contact_methods($additional_account, $additional_methods, $user['show_on_profile']);
?>
      </div>

      <?php if (getBoolPreference('item_contact_form_disabled') != 1) { ?>
        <a href="<?php echo eps_item_fancy_url('contact_public', array('userId' => osc_user_id())); ?>"
          class="open-form public-contact master-button" data-type="contact_public">
          <i class="fas fa-envelope-open"></i>
          <span><?php _e('Send message', 'epsilon'); ?></span>
        </a>
      <?php } ?>

      <a href="<?php echo osc_search_url(array('page' => 'search', 'userId' => osc_user_id())); ?>"
        class="seller-button seller-items"><?php echo __('All seller items', 'epsilon') . ' (' . $user_item_count . ')'; ?></a>

      <?php if (trim(osc_user_website()) <> '') { ?>
        <a href="<?php echo osc_user_website(); ?>" target="_blank" rel="nofollow noreferrer"
          class="seller-button seller-url">
          <i class="fas fa-external-link-alt"></i>
          <span><?php echo rtrim(str_replace(array('https://', 'http://'), '', osc_user_website()), '/'); ?></span>
        </a>
      <?php } ?>

      <?php if ($user_about <> '') { ?>
        <div class="box" id="about">
          <strong><?php _e('About seller', 'epsilon'); ?></strong>
          <div><?php echo $user_about; ?></div>
        </div>
      <?php } ?>



      <?php echo eps_banner('public_profile_sidebar'); ?>
      <?php osc_run_hook('user_public_profile_sidebar_bottom'); ?>
    </div>


    <!-- LISTINGS OF SELLER -->
    <div id="public-main">
      <?php osc_run_hook('user_public_profile_items_top'); ?>

      <?php echo eps_banner('public_profile_top'); ?>

      <h1><?php echo sprintf(__('%s\'s listings', 'epsilon'), $contact_name); ?></h1>

      <?php if(osc_version() >= 830) { ?>
        <form name="user-public-profile-search" action="<?php echo osc_base_url(true); ?>" method="get" class="user-public-profile-search-form nocsrf">
          <input type="hidden" name="page" value="user"/>
          <input type="hidden" name="action" value="pub_profile"/>
          <input type="hidden" name="id" value="<?php echo osc_esc_html($user['pk_i_id']); ?>"/>

          <?php osc_run_hook('user_public_profile_search_form_top'); ?>

          <div class="control-group">
            <label class="control-label" for="sPattern"><?php _e('Keyword', 'epsilon'); ?></label>

            <div class="controls">
              <?php UserForm::search_pattern_text(); ?>
            </div>
          </div>

          <div class="control-group">
            <label class="control-label" for="sCategory"><?php _e('Category', 'epsilon'); ?></label>

            <div class="controls">
              <?php UserForm::search_category_select(); ?>
            </div>
          </div>

          <div class="control-group">
            <label class="control-label" for="sCity"><?php _e('City', 'epsilon'); ?></label>

            <div class="controls">
              <?php UserForm::search_city_select(); ?>
            </div>
          </div>

          <?php osc_run_hook('user_public_profile_search_form_bottom'); ?>

          <div class="actions">
            <button type="submit" class="btn btn-primary"><?php _e('Apply', 'epsilon'); ?></button>
          </div>
        </form>
      <?php } ?>

      <?php if (osc_count_items() > 0) { ?>
        <div class="products list">
          <?php
            $c = 1;
            while(osc_has_items()) {
              eps_draw_item($c);

              if($c == 3 && osc_count_items() > 3) {
                echo eps_banner('public_profile_middle');
              }

              $c++;
            }
          ?>
        </div>

        <div class="paginate"><?php echo eps_fix_arrow(osc_pagination_items()); ?></div>

      <?php } else { ?>
        <div class="empty"><?php _e('User has no active listings', 'epsilon'); ?></div>
      <?php } ?>

      <?php echo eps_banner('public_profile_bottom'); ?>
    </div>
  </div>

  <?php osc_current_web_theme_path('footer.php'); ?>
  <script>
    function formatPhoneNumber(phoneNumber) {
    // Check if the number is in the Ethiopian format
    const ethiopianRegex1 = /^\+2519\d{8}$/; // +2519XXXXXXXX
    const ethiopianRegex2 = /^09\d{8}$/; // 09XXXXXXXX

    if (ethiopianRegex1.test(phoneNumber)) {
        // Already in the correct format, just add a space for readability
        return phoneNumber.slice(0, 4) + ' ' + phoneNumber.slice(4);
    } else if (ethiopianRegex2.test(phoneNumber)) {
        // Convert 09XXXXXXXX to +251 9XXXXXXXX
        return '+251 ' + phoneNumber.slice(1);
    } else {
        // Not an Ethiopian phone number, return as is
        return phoneNumber.slice(0, 4) + ' ' + phoneNumber.slice(4);

    }
}
document.querySelectorAll('.contact-method span, .phone-mobile span, .phone-land span').forEach(span => {
    const originalValue = span.textContent.trim();
    const formattedValue = formatPhoneNumber(originalValue);
    console.log('Before:', originalValue);
    console.log('After:', formattedValue);
    span.textContent = formattedValue;
});


  </script>
  <style>
    /* Style for the additional account field */
    @media screen and (max-width: 767px) {
    body .oc-chat.oc-closed {
        bottom: 55px !important; /* Adjust as needed */
        width: 46px;
        height: 46px;
        min-height: 46px;
    }
}

.icon-spacing {
    margin-right: 3px;
}/*
      .oc-chat-button {
        margin-right: 0 !important;
      } */
      .contact-method {
    display: flex;
    width: fit-content; /* This will make the width fit the content */
    align-items: center;
    margin-bottom: 10px;
    font-size: 14px;
    margin: 6px 0 2px 0;
    font-weight: 600;
        padding: 2px 8px;
    border-radius: 8px;
    background-color: rgba(1, 120, 214, 0.12);
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
    border-bottom-left-radius: 8px;
    transition: 0.2s;
}

.contact-method i {
    /* margin-right: 10px; */
    font-size: 18px;
    color: #0178d6; /* Icon color */
}

.contact-method span {
    font-weight: 600;
    color: #0178d6;
    margin-right: 1px;
}
.phone-mobile span{
    margin-right: 5px;
}

.contact-method span{
  font-family: "Comfortaa", sans-serif;
  font-size: 14px;
  line-height: 1.5;
}
    </style>
</body>

</html>