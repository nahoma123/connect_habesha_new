<?php
// Ensure helper functions are defined (ideally move these to functions.php)
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
    function generate_contact_methods_enhanced($account_value, $methods_string,  $field_label = 'Contact') {
      $formatted_account_value = function_exists('xethio_format_ethiopian_phone') ? xethio_format_ethiopian_phone($account_value) : $account_value;
      
      if (empty($formatted_account_value)) {
          return;
      }
  
      $methods = !empty($methods_string) ? explode(',', $methods_string) : [];
      $icons_html = '';
      $has_phone_icon = false;
  
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
                  if (!$has_phone_icon) {
                      $icons_html .= '<i class="icon-spacing fas fa-phone-alt" title="Direct Call"></i>';
                      $has_phone_icon = true;
                  }
                  break;
          }
      }
  
      $phone_data = function_exists('eps_get_phone') ? eps_get_phone($formatted_account_value) : array('found' => false);
      $is_phone = isset($phone_data['found']) && $phone_data['found'];
      $is_logged_in = osc_is_web_user_logged_in();
  
      $container_tag = 'div';
      $container_classes = ['contact-method'];
      $data_attributes = '';
      $link_href = '#';
      $title_attr = osc_esc_html($field_label);
      $display_value = '';
  
      if ($is_phone) {
          $container_tag = 'a';
          $container_classes[] = 'phone';
          $container_classes[] = $phone_data['class'];
          $link_href = $phone_data['url'];
          $title_attr = osc_esc_html($phone_data['title']);
          $data_attributes = sprintf(
              ' data-prefix="tel" data-part1="%s" data-part2="%s"',
              isset($phone_data['part1']) ? $phone_data['part1'] : '',
              isset($phone_data['part2']) ? $phone_data['part2'] : ''
          );
          $display_value = $phone_data['masked'];
        } else {
          if ($is_logged_in) {
              $container_classes[] = 'logged';
              $display_value = osc_esc_html($formatted_account_value);
  
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
                  $container_tag = 'div';
                  $title_attr = osc_esc_html($field_label . ': ' . $formatted_account_value);
              }
          } else {
              $container_tag = 'a';
              $container_classes[] = 'phone';
              $container_classes[] = 'not-logged';
              $link_href = '#';
              $title_attr = osc_esc_html(__('Login to view contact', 'epsilon'));
  
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
  
      if ($container_tag === 'a') {
          $link_href = osc_esc_html($link_href);
      }
  
      echo sprintf(
          '<%s class="%s"%s title="%s"%s>',
          $container_tag,
          implode(' ', $container_classes),
          ($container_tag === 'a' ? ' href="' . $link_href . '"' : ''),
          $title_attr,
          $data_attributes
      );
      echo sprintf('<span class="contact-value">%s</span>', $display_value);
      echo $icons_html;
      echo sprintf('</%s>', $container_tag);
  }

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo eps_language_dir(); ?>" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
<head>
  <?php osc_current_web_theme_path('head.php'); ?>
  <link rel="stylesheet" media="print" href="<?php echo osc_current_web_theme_url('css/print.css?v=' . date('YmdHis')); ?>">
  <style>
    @media screen and (max-width: 767px) {
      body .oc-chat.oc-closed { bottom: 55px !important; width: 46px; height: 46px; min-height: 46px; }
    }
    #seller .line3 .phone i {
      font-size: 16px;
    }
    .icon-spacing { margin-right: 3px; }
    .contact-method { display: flex; width: fit-content; align-items: center; margin-bottom: 10px; font-size: 14px; margin: 6px 0 2px 0; font-weight: 600; padding: 2px 8px; border-radius: 8px; background-color: rgba(1, 120, 214, 0.12); transition: 0.2s; }
    .contact-method i { font-size: 16px; color: #0178d6; vertical-align: middle; }
    .contact-method span, .contact-method span.contact-value { font-weight: 600; color: #0178d6; margin-right: 5px; font-family: "Comfortaa", sans-serif; font-size: 14px; line-height: 1.5; display: inline-block; }
    .phone-mobile span, .phone-land span { margin-right: 5px; color: #0178d6; }
    .line3 .contact-method { display: block; margin: 6px 0 2px 0; padding: 4px 8px; border-radius: 8px; background-color: rgba(1, 120, 214, 0.12); font-size: 14px; font-weight: 600; width: fit-content; color: #0178d6; transition: 0.2s; text-decoration: none; line-height: 1.5; }
    .line3 a.contact-method { color: #0178d6; }
    .line3 a.contact-method:hover { background-color: rgba(1, 120, 214, 0.2); }
    .contact-method span.contact-value { margin-right: 5px; display: inline-block; }
    .contact-method i.icon-spacing { margin-left: 3px; font-size: 16px; color: inherit; vertical-align: middle; }
    .contact-method i.icon-spacing:first-of-type { margin-left: 0; }
  </style>
  <?php
    $itemviewer = (Params::getParam('itemviewer') == 1 ? 1 : 0);
    $item_extra = eps_item_extra(osc_item_id());
    $location_array = array_filter(array(osc_item_city(), osc_item_region(), osc_item_country_code()));
    $location = implode(', ', $location_array);
    $location_full_array = array_filter(array(osc_item_address(), osc_item_zip(), osc_item_city_area(), osc_item_city(), osc_item_region(), osc_item_country()));
    $location_full = implode('<br/>', $location_full_array);
    $is_company = false; 
    $item_user = null; 
    $user_item_count = 0; $show_phone_on_profile = 'yes'; $user_location = '';
    if(osc_item_user_id() <> 0) {
      $item_user = eps_get_user(osc_item_user_id());
      if($item_user) {
          View::newInstance()->_exportVariableToView('user', $item_user);
          $user_item_count = isset($item_user['i_items']) ? $item_user['i_items'] : osc_count_user_items(osc_item_user_id());
          $is_company = ($item_user['b_company'] == 1);
          $item_user_location_array = array_filter(array(@$item_user['s_address'], @$item_user['s_zip'], @$item_user['s_city_area'], @$item_user['s_city'], @$item_user['s_region'], @$item_user['s_country']));
          $user_location = implode(', ', $item_user_location_array);
          $show_phone_on_profile = isset($item_user['show_on_profile']) ? $item_user['show_on_profile'] : 'yes';
      } else { $item_user = false; $user_item_count = Item::newInstance()->countItemTypesByUserId(osc_item_user_id(), 'active'); }
    } else { $item_user = false; $user_item_count = Item::newInstance()->countItemTypesByEmail(osc_item_contact_email(), 'active'); $show_phone_on_profile = 'yes'; }
    $contact_name = (osc_item_contact_name() <> '' ? osc_item_contact_name() : __('Anonymous', 'epsilon'));
    $reg_type = ''; $last_online = '';
    if($item_user && isset($item_user['dt_reg_date']) && $item_user['dt_reg_date'] <> '') { $reg_type = sprintf(__('Member for %s', 'epsilon'), eps_smart_date2($item_user['dt_reg_date'])); } else if ($item_user) { $reg_type = __('Registered user', 'epsilon'); } else { $reg_type = __('Unregistered user', 'epsilon'); }
    if($item_user && isset($item_user['dt_access_date']) && $item_user['dt_access_date'] != '') { $last_online = sprintf(__('Last online %s', 'epsilon'), eps_smart_date($item_user['dt_access_date'])); }
    $user_phone_mobile_data = eps_get_phone(isset($item_user['s_phone_mobile']) ? $item_user['s_phone_mobile'] : '');
    $user_phone_land_data = eps_get_phone(isset($item_user['s_phone_land']) ? $item_user['s_phone_land'] : '');
    $primary_methods_str = isset($item_user['primary_methods']) ? $item_user['primary_methods'] : '';
    $primary_account_val = isset($item_user['primary_accounts']) ? trim($item_user['primary_accounts']) : '';
    $additional_methods_str = isset($item_user['additional_methods']) ? $item_user['additional_methods'] : '';
    $additional_account_val = isset($item_user['additional_accounts']) ? trim($item_user['additional_accounts']) : '';
    $email_data = eps_get_item_email();
    $has_cf = false; while(osc_has_item_meta()) { if(osc_item_meta_value() != '') { $has_cf = true; break; } } View::newInstance()->_reset('metafields');
    $make_offer_enabled = false; if(function_exists('mo_call_after_install')) { /* ... make offer logic ... */ }
    $item_search_url = osc_search_url(array('page' => 'search', 'sCategory' => osc_item_category_id(), 'sCountry' => osc_item_country_code(), 'sRegion' => osc_item_region_id(), 'sCity' => osc_item_city_id())); if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '' && strpos($_SERVER['HTTP_REFERER'], osc_base_url()) !== false) { $item_search_url = false; }
    $dimNormal = explode('x', osc_get_preference('dimNormal', 'osclass')); $aspect_ratio = round($dimNormal[0]/$dimNormal[1], 3); $gallery_padding_top = round(1/$aspect_ratio*100, 2);
    osc_reset_resources(); osc_get_item_resources(); $resource_url = osc_resource_url();
  ?>
  <meta property="og:title" content="<?php echo osc_esc_html(osc_item_title()); ?>" />
  <?php if(osc_count_item_resources() > 0) { ?><meta property="og:image" content="<?php echo $resource_url; ?>" /><?php } ?>
  <meta property="og:site_name" content="<?php echo osc_esc_html(osc_page_title()); ?>"/>
  <meta property="og:url" content="<?php echo osc_item_url(); ?>" />
  <meta property="og:description" content="<?php echo osc_esc_html(osc_highlight(osc_item_description(), 500)); ?>" />
  <meta property="og:type" content="article" />
  <meta property="og:locale" content="<?php echo osc_current_user_locale(); ?>" />
  <meta property="product:retailer_item_id" content="<?php echo osc_item_id(); ?>" />
  <?php if(eps_check_category_price(osc_item_category_id())) { ?>
  <meta property="product:price:amount" content="<?php echo osc_esc_html(strip_tags(osc_item_price()/1000000)); ?>" />
  <?php if(osc_item_price() <> '' and osc_item_price() <> 0) { ?><meta property="product:price:currency" content="<?php echo osc_item_currency(); ?>" /><?php } ?>
  <?php } ?>
  <span itemscope itemtype="http://schema.org/Product">
    <meta itemprop="name" content="<?php echo osc_esc_html(osc_item_title()); ?>" />
    <meta itemprop="description" content="<?php echo osc_esc_html(osc_highlight(osc_item_description(), 500)); ?>" />
    <?php if(osc_count_item_resources() > 0) { ?><meta itemprop="image" content="<?php echo $resource_url; ?>" /><?php } ?>
  </span>
</head>
<body id="item" class="<?php if(eps_device() <> '') { echo ' dvc-' . eps_device(); } ?><?php if(osc_item_is_expired()) { ?> expired<?php } ?>">
  <?php osc_current_web_theme_path('header.php') ; ?>

  <div class="container primary">
    <?php osc_run_hook('item_top'); ?>
    <?php echo eps_banner('item_top'); ?>

    <div class="data-box<?php echo osc_esc_html(@$item_extra['i_sold'] == 1 ?  ' sold' : ''); ?>" title="<?php echo osc_esc_html(@$item_extra['i_sold'] == 1 ? __('Sold', 'epsilon') : ''); ?>">
      <div id="item-main">
        <?php if(osc_images_enabled_at_items()) { ?>
          <div id="item-image" class="<?php if(osc_count_item_resources() <= 0 ) { ?>noimg<?php } ?>">
            <?php if($item_search_url !== false) { ?>
              <a href="<?php echo $item_search_url; ?>" class="mlink back isMobile"><i class="fas fa-arrow-left"></i></a>
            <?php } else { ?>
              <a href="#" onclick="history.back();return false;" class="mlink back isMobile"><i class="fas fa-arrow-left"></i></a>
            <?php } ?>
            <?php if(eps_param('messenger_replace_button') == 1 && function_exists('im_contact_button') && im_contact_button(osc_item(), true) !== false) { ?>
              <a href="<?php echo im_contact_button(osc_item(), true); ?>" class="mlink contact isMobile"><i class="fas fa-envelope-open"></i></a>
            <?php } else if(getBoolPreference('item_contact_form_disabled') != 1) { ?>
              <a href="<?php echo eps_item_fancy_url('contact'); ?>" data-type="contact" class="mlink contact isMobile open-form"><i class="fas fa-envelope-open"></i></a>
            <?php } ?>
            <?php osc_get_item_resources(); osc_reset_resources(); ?>
            <?php if(osc_count_item_resources() > 0 ) { ?>
              <div class="swiper-container<?php if(osc_count_item_resources() <= 1) { ?> hide-buttons<?php } ?>">
                <div class="swiper-wrapper">
                  <?php for($i = 0;osc_has_item_resources(); $i++) { ?>
                    <li class="swiper-slide ratio<?php echo str_replace(':', 'to', eps_param('gallery_ratio')); ?>" <?php if(eps_param('gallery_ratio') == '') { ?>style="padding-top:<?php echo $gallery_padding_top; ?>%;"<?php } ?>>
                      <a href="<?php echo osc_resource_url(); ?>">
                        <img class="<?php echo (eps_is_lazy() ? 'lazy' : ''); ?>" <?php echo (eps_is_lazy_browser() ? 'loading="lazy"' : ''); ?> src="<?php echo (eps_is_lazy() ? eps_get_load_image() : osc_resource_url()); ?>" data-src="<?php echo osc_resource_url(); ?>" alt="<?php echo osc_esc_html(osc_item_title()); ?> - <?php echo $i+1;?>/<?php echo osc_count_item_resources(); ?>"/>
                      </a>
                    </li>
                  <?php } ?>
                </div>
                <div class="swiper-pg"></div>
                <div class="swiper-button swiper-next"><i class="fas fa-caret-right"></i></div>
                <div class="swiper-button swiper-prev"><i class="fas fa-caret-left"></i></div>
              </div>
            <?php } ?>
            <?php osc_get_item_resources(); osc_reset_resources(); ?>
            <?php if(osc_count_item_resources() > 0 ) { ?>
              <div class="swiper-thumbs">
                <ul>
                  <?php for($i = 0;osc_has_item_resources(); $i++) { ?>
                    <li class="<?php if($i == 0) { ?>active<?php } ?>" data-id="<?php echo $i; ?>">
                      <img class="<?php echo (eps_is_lazy() ? 'lazy' : ''); ?>" <?php echo (eps_is_lazy_browser() ? 'loading="lazy"' : ''); ?> src="<?php echo (eps_is_lazy() ? eps_get_load_image() : osc_resource_thumbnail_url()); ?>" data-src="<?php echo osc_resource_thumbnail_url(); ?>" alt="<?php echo osc_esc_html(osc_item_title()); ?> - <?php echo $i+1;?>"/>
                    </li>
                  <?php } ?>
                </ul>
              </div>
            <?php } ?>
          </div>
        <?php } ?>
        <?php osc_run_hook('item_images'); ?>

        <div class="basic">
          <h1 class="row"><?php echo osc_item_title(); ?></h1>
          <?php osc_run_hook('item_title'); ?>
          <div class="labels">
            <?php if(osc_item_is_premium()) { ?><span class="premium"><?php _e('Premium', 'epsilon'); ?></span><?php } ?>
            <?php if(osc_item_is_expired()) { ?><span class="expired"><?php _e('Expired', 'epsilon'); ?></span><?php } ?>
            <?php if($item_extra['i_sold'] == 1) { ?><span class="sold"><?php _e('Sold', 'epsilon'); ?></span><?php } ?>
            <?php if($item_extra['i_sold'] == 2) { ?><span class="reserved"><?php _e('Reserved', 'epsilon'); ?></span><?php } ?>
          </div>
          <div class="row details">
            <span><?php echo osc_item_category(); ?></span>
            <?php if(!in_array(osc_item_category_id(), eps_extra_fields_hide())) { ?>
              <?php if(eps_get_simple_name($item_extra['i_condition'], 'condition', false) <> '') { ?><span><?php echo eps_get_simple_name($item_extra['i_condition'], 'condition', false); ?></span><?php } ?>
              <?php if(eps_get_simple_name($item_extra['i_transaction'], 'transaction', false) <> '') { ?><span><?php echo eps_get_simple_name($item_extra['i_transaction'], 'transaction', false); ?></span><?php } ?>
            <?php } ?>
            <span><?php echo sprintf(__('ID: %d', 'epsilon'), osc_item_id()); ?></span>
          </div>
          <?php if(eps_check_category_price(osc_item_category_id())) { ?>
            <div class="row price under-header p-<?php echo osc_esc_html(osc_item_price()); ?>x<?php if(osc_item_price() <= 0) { ?> isstring<?php } ?>"><?php echo osc_item_formated_price(); ?></div>
          <?php } ?>
          <?php if(function_exists('mo_show_offer_link_raw') && mo_show_offer_link_raw() !== false) { ?>
            <a href="<?php echo mo_show_offer_link_raw(); ?>" class="mo-open-offer mo-button-create mo-make-offer-price"><?php _e('Make price offer', 'epsilon'); ?></a>
          <?php } ?>
           <?php eps_make_favorite(); ?>
        </div>

        <div class="props<?php if($has_cf) { ?> style<?php } ?>">
          <?php if($has_cf) { ?>
            <h2><?php _e('Attributes', 'epsilon'); ?></h2>
            <div class="custom-fields">
              <?php while(osc_has_item_meta()) { ?>
                <?php $meta = osc_item_meta(); $meta_type = @$meta['e_type']; $meta_value = @$meta['s_value']; if($meta_type != 'CHECKBOX') { $meta_value = osc_item_meta_value(); } ?>
                <?php if(osc_item_meta_value() != '') { ?>
                  <div class="field type-<?php echo osc_esc_html($meta_type); ?> name-<?php echo osc_esc_html(strtoupper(str_replace(' ', '-', osc_item_meta_name()))); ?> value-<?php echo osc_esc_html($meta_value); ?>">
                    <span class="name"><?php echo osc_item_meta_name(); ?></span>
                    <span class="value"><?php echo osc_item_meta_value(); ?></span>
                  </div>
                <?php } ?>
              <?php } ?>
            </div>
          <?php } ?>
          <div id="item-hook"><?php osc_run_hook('item_detail', osc_item()); ?></div>
        </div>
        <?php osc_run_hook('item_meta'); ?>
        <?php echo eps_banner('item_description'); ?>

        <div class="row description">
          <h2><?php _e('Description', 'epsilon'); ?></h2>
          <div class="desc-parts">
            <div class="desc-text">
              <?php if(eps_param('shorten_description') == 1) { ?>
                <div class="text visible"><?php if(function_exists('show_qrcode')) { ?><div class="qr-code"><strong><?php _e('Scan QR', 'epsilon'); ?></strong><?php show_qrcode(); ?></div><?php } ?><?php echo substr(strip_tags(osc_item_description()), 0, 720) . (strlen(strip_tags(osc_item_description())) > 720 ? '...' : ''); ?></div>
                <?php if(strlen(osc_item_description()) > 720) { ?>
                  <div class="text hidden"><?php if(function_exists('show_qrcode')) { ?><div class="qr-code"><strong><?php _e('Scan QR', 'epsilon'); ?></strong><?php show_qrcode(); ?></div><?php } ?><?php echo osc_item_description(); ?></div>
                  <div class="links"><a href="#" class="read-more-desc"><?php _e('Read more', 'epsilon'); ?> <i class="fas fa-angle-down"></i></a></div>
                <?php } ?>
              <?php } else { ?>
                <div class="text visible"><?php if(function_exists('show_qrcode')) { ?><div class="qr-code"><strong><?php _e('Scan QR', 'epsilon'); ?></strong><?php show_qrcode(); ?></div><?php } ?><?php echo osc_item_description(); ?></div>
              <?php } ?>
            </div>
            <?php osc_run_hook('item_description'); ?>
            <div class="location">
              <h2><i class="fas fa-map-marked-alt"></i> <?php _e('Location', 'epsilon'); ?></h2>
              <?php if($location <> '') { ?>
                <div class="row address"><?php echo $location_full; ?></div>
                <?php if(osc_item_latitude() <> 0 && osc_item_longitude() <> 0) { ?><div class="row cords"><?php echo osc_item_latitude(); ?>, <?php echo osc_item_longitude(); ?></div><?php } ?>
                <a target="_blank" class="directions" href="https://maps.google.com/maps?daddr=<?php echo urlencode($location); ?>"><?php _e('Get directions', 'epsilon'); ?> →</a>
              <?php } else { ?> <?php _e('Unknown location', 'epsilon'); ?> <?php } ?>
            </div>
          </div>
          <div id="location-hook"><?php osc_run_hook('location'); ?></div>
        </div>

        <?php if(osc_comments_enabled()) { ?>
          <div class="box" id="comments">
            <h2><?php _e('Comments', 'epsilon'); ?></h2>
            <div class="wrap">
              <?php if(osc_item_total_comments() > 0) { ?>
                <?php while(osc_has_item_comments()) { ?>
                  <?php $comment_author = (osc_comment_author_name() == '' ? __('Anonymous', 'epsilon') : osc_comment_author_name()); ?>
                  <div class="comment">
                    <a class="author" href="<?php echo (osc_comment_user_id() ? eps_user_public_profile_url(osc_comment_user_id()) : '#'); ?>" <?php echo (osc_comment_user_id() > 0 ? '' : 'onclick="return false;"'); ?>>
                      <img class="img <?php echo (eps_is_lazy() ? 'lazy' : ''); ?>" <?php echo (eps_is_lazy_browser() ? 'loading="lazy"' : ''); ?> src="<?php echo (eps_is_lazy() ? eps_get_load_image() : eps_profile_picture(osc_comment_user_id(), 'medium')); ?>" data-src="<?php echo eps_profile_picture(osc_comment_user_id(), 'medium'); ?>" alt="<?php echo osc_esc_html(osc_comment_author_name()); ?>"/>
                      <strong class="name"><?php echo $comment_author; ?></strong>
                    </a>
                    <div class="data">
                      <?php if(osc_comment_title() != '') { ?><h3><?php echo osc_comment_title(); ?></h3><?php } ?>
                      <div class="date"><?php echo eps_smart_date(osc_comment_pub_date()); ?></div>
                      <?php if(function_exists('osc_enable_comment_rating') && osc_enable_comment_rating()) { /* ... rating stars ... */ } ?>
                      <div class="body"><?php echo nl2br(osc_comment_body()); ?></div>
                      <?php if(osc_comment_user_id() && (osc_comment_user_id() == osc_logged_user_id())) { ?>
                        <a rel="nofollow" class="remove" href="<?php echo osc_delete_comment_url(); ?>" title="<?php echo osc_esc_html(__('Delete your comment', 'epsilon')); ?>"><i class="fas fa-trash-alt"></i> <span><?php _e('Delete', 'epsilon'); ?></span></a>
                      <?php } ?>
                    </div>
                  </div>
                  <?php if(function_exists('osc_enable_comment_reply') && osc_enable_comment_reply()) { ?>
                    <?php osc_get_comment_replies(); ?>
                    <?php if(osc_count_comment_replies() > 0) { ?>
                      <div id="comment-replies">
                        <?php while (osc_has_comment_replies()) { /* ... reply details ... */ } ?>
                      </div>
                    <?php } ?>
                  <?php } ?>
<?php
                  // Check if comment replies are enabled AND if the current user has permission
                  if(
                    function_exists('osc_enable_comment_reply')
                    && osc_enable_comment_reply()
                    && (
                      // Permission checks based on admin setting:
                      osc_comment_reply_user_type() == '' // Anyone can reply
                      || (osc_comment_reply_user_type() == 'LOGGED' && osc_is_web_user_logged_in()) // Only logged-in users
                      || (osc_comment_reply_user_type() == 'OWNER' && ( (osc_logged_user_id() == osc_item_user_id() && osc_item_user_id() > 0) || (osc_is_web_user_logged_in() && osc_logged_user_email() == osc_item_contact_email()) ) ) // Only item owner (checks ID or email if logged in)
                      || (osc_comment_reply_user_type() == 'ADMIN' && osc_is_admin_user_logged_in()) // Only admins
                    )
                  ) {
                  ?>
                    <p class="comment-reply-row">
                        <?php $reply_params = array('replyToCommentId' => osc_comment_id()); ?>
                        <a class="btn btn-secondary comment-reply open-form" href="<?php echo eps_item_fancy_url('comment', $reply_params); ?>"><?php _e('Reply', 'epsilon'); ?></a>
                    </p>
                  <?php
                  } // End if for showing reply button
                  ?>
                <?php } // End while(osc_has_item_comments()) loop ?>
                <div class="paginate"><?php echo osc_comments_pagination(); ?></div>
              <?php } else { ?> <div class="empty-comments"><?php _e('No comments has been added yet', 'epsilon'); ?></div> <?php } ?>
              <?php if(osc_reg_user_post_comments() && osc_is_web_user_logged_in() || !osc_reg_user_post_comments()) { ?>
                <a class="open-form add btn<?php echo (osc_enable_comment_rating() ? ' has-rating' : ''); ?>" href="<?php echo eps_item_fancy_url('comment'); ?>" data-type="comment"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16" height="16"><path d="M448 0H64C28.7 0 0 28.7 0 64v288c0 35.3 28.7 64 64 64h96v84c0 9.8 11.2 15.5 19.1 9.7L304 416h144c35.3 0 64-28.7 64-64V64c0-35.3-28.7-64-64-64zm-80 216c0 8.8-7.2 16-16 16h-72v72c0 8.8-7.2 16-16 16h-16c-8.8 0-16-7.2-16-16v-72h-72c-8.8 0-16-7.2-16-16v-16c0-8.8 7.2-16 16-16h72v-72c0-8.8 7.2-16 16-16h16c8.8 0 16 7.2 16 16v72h72c8.8 0 16 7.2 16 16v16z"/></svg><?php _e('Add comment', 'epsilon'); ?></a>
              <?php } ?>
            </div>
          </div>
        <?php } ?>
        <?php osc_run_hook('item_comment'); ?>

        <div id="shortcuts">
           <?php /* Original shortcuts commented out */ ?>
        </div>
      </div>

      <!-- SIDEBAR - RIGHT -->
      <div id="item-side">
        <?php osc_run_hook('item_sidebar_top'); ?>
        <?php if($email_data['visible']) { ?>
          <a class="master-button email <?php echo $email_data['class']; ?>" title="<?php echo osc_esc_html($email_data['title']); ?>" href="#" data-prefix="mailto" data-part1="<?php echo osc_esc_html($email_data['part1']); ?>" data-part2="<?php echo osc_esc_html($email_data['part2']); ?>"><i class="fas fa-at"></i><span><?php echo $email_data['masked']; ?></span></a>
        <?php } ?>
        <?php osc_run_hook('item_contact'); ?>

        <div class="box" id="seller">
          <div class="line1">
            <div class="img">
              <img src="<?php echo eps_profile_picture(osc_item_user_id(), 'small'); ?>" alt="<?php echo osc_esc_html($contact_name); ?>" />
              <?php if(osc_item_user_id() > 0) { if(eps_user_is_online(osc_item_user_id())) { ?><div class="online" title="<?php echo osc_esc_html(__('User is online', 'epsilon')); ?>"></div><?php } else { ?><div class="online off" title="<?php echo osc_esc_html(__('User is offline', 'epsilon')); ?>"></div><?php } } ?>
            </div>
            <div class="data">
              <?php if(osc_item_user_id() > 0) { ?><a class="name" href="<?php echo eps_user_public_profile_url(osc_item_user_id()); ?>"><?php echo $contact_name; ?></a><?php } else { ?><strong class="name"><?php echo $contact_name; ?></strong><?php } ?>
              <div class="items"><?php echo sprintf(__('%d active adverts', 'epsilon'), $user_item_count); ?></div>
              <?php if($is_company) { ?><div class="pro"><?php _e('Pro', 'epsilon'); ?></div><?php } ?>
            </div>
          </div>
          <?php if(osc_item_user_id() > 0 && function_exists('ur_show_rating_link')) { ?>
            <div class="line-rating"><span class="ur-fdb"><span class="strs"><?php echo ur_show_rating_stars(osc_item_user_id(), osc_contact_email(), osc_item_id()); ?></span><span class="lnk"><?php echo ur_add_rating_link(osc_item_user_id(), osc_item_id()); ?></span></span></div>
          <?php } ?>
          <div class="line2">
            <div class="date"><?php echo $last_online; ?></div>
            <div class="reg"><?php echo $reg_type; ?></div>
          </div>
          <?php if(osc_item_user_id() > 0 && function_exists('eps_chat_button') && eps_chat_button(osc_item_user_id())) { ?>
            <div class="line-chat"><?php echo eps_chat_button(osc_item_user_id()); ?></div>
          <?php } ?>

          <?php if($item_user) { // Only show location/phones if we have user data ?>
            <div class="line3">
              <?php if ($user_location != '') { ?>
                <div class="address"><i class="fas fa-map-marked-alt"></i> <?php echo $user_location; ?></div>
              <?php } ?>

              <?php if($user_phone_mobile_data['found'] && $show_phone_on_profile=="yes") { ?>
                    <a class="phone-mobile phone <?php echo $user_phone_mobile_data['class']; ?>" title="<?php echo osc_esc_html($user_phone_mobile_data['title']); ?>" data-prefix="tel" href="<?php echo osc_esc_html($user_phone_mobile_data['url']); ?>" data-part1="<?php echo osc_esc_html($user_phone_mobile_data['part1']); ?>" data-part2="<?php echo osc_esc_html($user_phone_mobile_data['part2']); ?>">
                      <span><?php echo $user_phone_mobile_data['masked']; ?></span><i class="fas fa-phone-alt"></i>
                    </a>
              <?php } ?>

              <?php if($user_phone_land_data['found']) { ?>
                    <a class="phone-land phone <?php echo $user_phone_land_data['class']; ?>" title="<?php echo osc_esc_html($user_phone_land_data['title']); ?>" data-prefix="tel" href="<?php echo osc_esc_html($user_phone_land_data['url']); ?>" data-part1="<?php echo osc_esc_html($user_phone_land_data['part1']); ?>" data-part2="<?php echo osc_esc_html($user_phone_land_data['part2']); ?>">
                      <span><?php echo $user_phone_land_data['masked']; ?></span><i class="fas fa-phone-alt"></i>
                    </a>
              <?php } ?>

              <?php
                // Display Primary Optional Contact
                generate_contact_methods_enhanced( $primary_account_val, $primary_methods_str,  __('Primary Contact', 'epsilon') );
                // Display Additional Optional Contact
                generate_contact_methods_enhanced( $additional_account_val, $additional_methods_str,  __('Additional Contact', 'epsilon') );
              ?>

            </div> <?php // End line3 ?>
          <?php } // End if($item_user) ?>
        </div> <?php // End #seller box ?>

        <?php if(osc_item_user_id() > 0) { ?>
          <a href="<?php echo eps_user_public_profile_url(osc_item_user_id()); ?>" class="seller-button seller-profile"><?php echo __('Seller\'s profile', 'epsilon'); ?></a>
          <a href="<?php echo osc_search_url(array('page' => 'search', 'userId' => osc_item_user_id())); ?>" class="seller-button seller-items"><?php echo __('All seller items', 'epsilon') . ' (' . $user_item_count . ')'; ?></a>
          <?php if(trim(osc_user_website()) <> '') { ?>
            <a href="<?php echo osc_user_website(); ?>" target="_blank" rel="nofollow noreferrer" class="seller-button seller-url"><i class="fas fa-external-link-alt"></i><span><?php echo rtrim(str_replace(array('https://', 'http://'), '', osc_user_website()), '/'); ?></span></a>
          <?php } ?>
        <?php } ?>

        <?php if(function_exists('sp_buttons')) { ?>
          <div class="sms-payments"><?php echo sp_buttons(osc_item_id());?></div>
        <?php } ?>

        <?php if(osc_is_web_user_logged_in() && osc_item_user_id() == osc_logged_user_id()) { ?>
          <div class="manage-delimit"></div>
          <?php if(osc_item_is_inactive()) { if((function_exists('iv_add_item') && osc_get_preference('enable','plugin-item_validation') <> 1) || !function_exists('iv_add_item')) { ?><a class="manage-button activate" target="_blank" href="<?php echo osc_item_activate_url(); ?>"><?php _e('Validate', 'epsilon'); ?></a><?php } } ?>
          <a class="manage-button edit" href="<?php echo osc_item_edit_url(); ?>"><i class="fas fa-edit"></i> <span><?php _e('Edit', 'epsilon'); ?></span></a>
          <a class="manage-button delete" href="<?php echo osc_item_delete_url(); ?>" onclick="return confirm('<?php _e('Are you sure you want to delete this listing? This action cannot be undone.', 'epsilon'); ?>?')"><i class="fas fa-trash-alt"></i> <span><?php _e('Remove', 'epsilon'); ?></span></a>
        <?php } ?>

        <?php echo eps_banner('item_sidebar'); ?>

        <div class="box" id="protection">
          <h2><?php _e('Useful Tips:', 'epsilon'); ?></h2>
          <div class="point"><div class="icon i1"><i class="far fa-credit-card"></i></div><span><?php _e('Don’t pay anyone before meeting them in person.', 'epsilon'); ?></span></div>
          <div class="point"><div class="icon i2"><i class="fas fa-cash-register"></i></div><span><?php _e('Be careful with people who ask for payment before you meet them.', 'epsilon'); ?></span></div>
          <div class="point"><div class="icon i3"><i class="fas fa-user-secret"></i></div><span><?php _e('Don\'t meet someone you don\'t know in a secluded or unknown location; meet in a public place. Also, do not invite strangers into your home.', 'epsilon'); ?></span></div>
        </div>

        <a href="#" class="report-button"><i class="fas fa-flag"></i><span><?php _e('Report advert', 'epsilon'); ?></span></a>
        <div class="report-wrap" style="display:none;">
          <div id="report">
            <img src="<?php echo osc_current_web_theme_url('images/report.png'); ?>" alt="<?php echo osc_esc_html(__('Report', 'epsilon')); ?>" />
            <div class="header"><?php _e('Report advert', 'epsilon'); ?></div>
            <div class="subheader"><?php _e('If you think this Ad is inappropriate, offensive, or fake, please let us know. Select one of the following reasons:', 'epsilon'); ?></div>
            <div class="text">
              <a href="<?php echo osc_item_link_spam() ; ?>" rel="nofollow"><?php _e('Spam', 'epsilon') ; ?></a><a href="<?php echo osc_item_link_bad_category() ; ?>" rel="nofollow"><?php _e('Misclassified', 'epsilon') ; ?></a><a href="<?php echo osc_item_link_repeated() ; ?>" rel="nofollow"><?php _e('Duplicated', 'epsilon') ; ?></a><a href="<?php echo osc_item_link_expired() ; ?>" rel="nofollow"><?php _e('Expired', 'epsilon') ; ?></a><a href="<?php echo osc_item_link_offensive() ; ?>" rel="nofollow"><?php _e('Offensive', 'epsilon') ; ?></a>
            </div>
          </div>
        </div>
        <?php echo eps_banner('item_sidebar_bottom'); ?>
        <?php osc_run_hook('item_sidebar_bottom'); ?>
      </div> <!-- End #item-side -->
    </div> <!-- End .data-box -->

    <?php
      if(function_exists('eps_param') && function_exists('eps_related_ads') && eps_param('related') == 1) {
        eps_related_ads( 'category', eps_param('related_design'), eps_param('related_count') );
      }
      if(function_exists('eps_banner')) { echo eps_banner('item_bottom'); }
      if(function_exists('eps_param') && function_exists('eps_recent_ads') && eps_param('recent_item') == 1) {
        eps_recent_ads( eps_param('recent_design'), eps_param('recent_count'), 'onitem' );
      }
    ?>
  </div> <!-- End .container.primary -->

  <?php if(getBoolPreference('item_contact_form_disabled') != 1) { ?>
    <a href="<?php echo eps_item_fancy_url('contact'); ?>" class="open-form contact btn btn-secondary sticky-button isMobile" data-type="contact"><i class="fas fa-envelope-open"></i><span><?php _e('Send message', 'epsilon'); ?></span></a>
  <?php } ?>

  <div class="share-item-data" style="display:none">
    <a class="whatsapp" href="whatsapp://send?text=<?php echo urlencode(osc_item_url()); ?>" data-action="share/whatsapp/share"><i class="fab fa-whatsapp"></i> <?php _e('Share on Whatsapp', 'epsilon'); ?></a></span>
    <a class="facebook" title="<?php echo osc_esc_html(__('Share on Facebook', 'epsilon')); ?>" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(osc_item_url()); ?>"><i class="fab fa-facebook"></i> <?php _e('Share on Facebook', 'epsilon'); ?></a>
    <a class="twitter" title="<?php echo osc_esc_html(__('Share on Twitter', 'epsilon')); ?>" target="_blank" href="https://twitter.com/intent/tweet?text=<?php echo urlencode(osc_item_title()); ?>&url=<?php echo urlencode(osc_item_url()); ?>"><i class="fab fa-twitter"></i> <?php _e('Share on Twitter', 'epsilon'); ?></a>
    <a class="pinterest" title="<?php echo osc_esc_html(__('Share on Pinterest', 'epsilon')); ?>" target="_blank" href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(osc_item_url()); ?>&media=<?php echo urlencode($resource_url); ?>&description=<?php echo htmlspecialchars(osc_item_title()); ?>"><i class="fab fa-pinterest"></i> <?php _e('Share on Pinterest', 'epsilon'); ?></a>
    <a class="friend open-form" href="<?php echo eps_item_fancy_url('friend'); ?>" data-type="friend"><i class="fas fa-user-friends"></i> <?php _e('Send to friend', 'epsilon'); ?></a>
  </div>

  <?php osc_run_hook('item_bottom'); ?>

  <script type="text/javascript">
    $(document).ready(function(){
      $('.mlink.share').on('click', () => { if (navigator.share) { navigator.share({ title: '<?php echo osc_esc_js(osc_highlight(osc_item_title(), 40) . ' - ' . osc_item_formated_price()); ?>', text: '<?php echo osc_esc_js(osc_highlight(osc_item_title(), 40) . ' - ' . osc_item_formated_price()); ?>', url: '<?php echo osc_esc_js(osc_item_url()); ?>', }).catch((error) => console.log('ERROR: ', error)); } return false; });
      $('.main-data > .img .mlink.share').on('click', () => { if (navigator.share) { navigator.share({ title: '<?php echo osc_esc_js(osc_highlight(osc_item_title(), 30) . ' - ' . osc_item_formated_price()); ?>', text: '<?php echo osc_esc_js(osc_highlight(osc_item_title(), 30) . ' - ' . osc_item_formated_price()); ?>', url: '<?php echo osc_esc_js(osc_item_url()); ?>', }).catch((error) => console.log('ER', error)); } else { if(($('#item-summary').is(':hidden') || $('.share-item-data').is(':hidden')) && $('.main-data > .img .mlink.share').hasClass('shown')) { $('.main-data > .img .mlink.share').removeClass('shown'); } if(!$('.main-data > .img .mlink.share').hasClass('shown')) { $('.share-item-data').fadeIn(200); if(!$('#item-summary').hasClass('shown')) { $('#item-summary').addClass('shown').show(0).css('overflow', 'visible').css('bottom', '-100px').css('opacity', '0').stop(false, false).animate( {bottom:'8px', opacity:1}, 250); } } else { $('.share-item-data').fadeOut(200); if($('#listing .item .data').offset().top - 50 > $(window).scrollTop()) { $('#item-summary').removeClass('shown').stop(false, false).animate( {bottom:'-100px', opacity:0}, 250, function() {$('#item-summary').hide(0);}); } } $('.main-data > .img .mlink.share').toggleClass('shown'); } return false; });
    });

    function formatPhoneNumber(phoneNumber) {
        let num = phoneNumber.trim(); if (!num || num.includes('*') || /^\+251\s/.test(num)) { return phoneNumber; } let cleanedNum = num.startsWith('+') ? '+' + num.substring(1).replace(/\D/g, '') : num.replace(/\D/g, ''); let coreDigits = null;
        if (cleanedNum.startsWith('+2519') && cleanedNum.length === 13) { coreDigits = cleanedNum.substring(4); } else if (cleanedNum.startsWith('2519') && cleanedNum.length === 12) { coreDigits = cleanedNum.substring(3); } else if (cleanedNum.startsWith('09') && cleanedNum.length === 10) { coreDigits = cleanedNum.substring(2); } else if (cleanedNum.startsWith('9') && cleanedNum.length === 9) { coreDigits = cleanedNum.substring(1); }
        if (coreDigits && coreDigits.length === 8) { return '+251 9' + coreDigits; } else { return phoneNumber; }
    }
    function applyFormattingToElement(spanElement) {
        if (!spanElement) { /* console.log("ApplyFormatting: No span element found."); */ return; } const parentLink = spanElement.closest('a');
        if (parentLink && parentLink.classList.contains('logged')) { const originalValue = spanElement.textContent; if (originalValue && !originalValue.includes(' ') && !originalValue.includes('*')) { const formattedValue = formatPhoneNumber(originalValue); if (originalValue !== formattedValue) { spanElement.textContent = formattedValue; /* console.log("Applied format:", formattedValue); */ } } }
    }
    function runInitialFormatting() {
        // console.log("Running initial formatting on item page...");
        const selector = '#item-side a.phone.logged span, #item-side a.contact-method.phone.logged span.contact-value'; const spansToFormat = document.querySelectorAll(selector);
        if (spansToFormat.length > 0) { spansToFormat.forEach(span => { applyFormattingToElement(span); }); }
    }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', runInitialFormatting); } else { setTimeout(runInitialFormatting, 0); }
    const contactContainer = document.querySelector('#item-side');
    if (contactContainer) {
        contactContainer.addEventListener('click', function(event) { const clickedLink = event.target.closest('a.phone.not-logged'); if (clickedLink) { /* console.log("Clicked masked link:", clickedLink); */ const observer = new MutationObserver((mutationsList, observerInstance) => { for(let mutation of mutationsList) { if (mutation.type === 'attributes' && mutation.attributeName === 'class') { if (clickedLink.classList.contains('logged')) { /* console.log("Detected .logged class added"); */ const spanToFormat = clickedLink.querySelector('span') || clickedLink.querySelector('span.contact-value'); if (spanToFormat) { setTimeout(() => applyFormattingToElement(spanToFormat), 50); } observerInstance.disconnect(); break; } } } }); observer.observe(clickedLink, { attributes: true, attributeFilter: ['class'] }); setTimeout(() => { if(observer) observer.disconnect(); /* console.log("Observer fallback timeout"); */ }, 1000); } });
    } else { console.error("Could not find contact container '#item-side'."); }
  </script>

  <?php osc_current_web_theme_path('footer.php') ; ?>
</body>
</html>