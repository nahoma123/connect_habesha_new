<?php

// INCLUDE MAILER SCRIPT
function osp_include_mailer() {
  if(file_exists(osc_lib_path() . 'vendor/phpmailer/phpmailer/class.phpmailer.php')) {
    require_once osc_lib_path() . 'vendor/phpmailer/phpmailer/class.phpmailer.php';
  } else if(file_exists(osc_lib_path() . 'phpmailer/class.phpmailer.php')) {
    require_once osc_lib_path() . 'phpmailer/class.phpmailer.php';
  }
}


// CREATE CHECKSUM
function osp_create_checksum($item_id, $user_id, $email, $amount) {
  $arr = array(
    'item_id' => ($item_id > 0 ? $item_id : 0),
    'user_id' => ($user_id > 0 ? $user_id : 0),
    'email' => $email,
    'amount' => number_format($amount, 2),
    'crypt' => osp_param('crypt_key')
  );
  
  $data = md5(json_encode($arr, JSON_NUMERIC_CHECK));

  return $data;
}


// FORMAT MEMBERSHIP DAYS
function osp_format_membership_days($days) {
  $format = OSP_GROUP_DAYS_FORMAT;      // D, W, M, Y
  
  $days = intval($days);
  $weeks = round($days/7);  
  $months = round($days/30);
  $years = round($days/365);
  
  
  if($format == 'D') {
    if($day == 1) {
      return __('1 day', 'osclass_pay');
    } else {
      return sprintf(__('%d days', 'osclass_pay'), $days);
    }
    
  } else if ($format == 'W') {
    if($weeks == 1) {
      return __('1 week', 'osclass_pay');
    } else {
      return sprintf(__('%d weeks', 'osclass_pay'), $weeks);
    }
    
  } else if ($format == 'M') {
    if($months == 1) {
      return __('1 month', 'osclass_pay');
    } else {
      return sprintf(__('%d months', 'osclass_pay'), $months);
    }

  } else if ($format == 'Y') {
    if($years == 1) {
      return __('1 year', 'osclass_pay');
    } else {
      return sprintf(__('%d years', 'osclass_pay'), $years);
    }
    
  } else if ($format == 'YM') {
    $year_months = '';

    if($years == 0) {
      if($months == 1) {
        return __('1 month', 'osclass_pay');
      } else {
        return sprintf(__('%d months', 'osclass_pay'), $months);
      }
    }

    if($months % 12 != 0) {
      $year_months = ' ' . sprintf(__('%d months', 'osclass_pay'), $months % 12);
    }
    
    if($years == 1) {
      return __('1 year', 'osclass_pay') . $year_months;
    } else {
      return sprintf(__('%d years', 'osclass_pay'), $years) . $year_months;
    }
  }
  
  return sprintf(__('%d days', 'osclass_pay'), $days);

}


// FIX CART AMOUNTS WITH SHIPPING
function osp_cart_shipping_amounts_fix() {
  if(osc_get_osclass_section() == 'osp-cart' || Params::getParam('route') == 'osp-cart') {
    $user_id = osc_logged_user_id();
    $user = User::newInstance()->findByPrimaryKey($user_id);
    $cart = osp_cart_content($user_id);
   
    // CHECK SHIPPING OPTIONS
    $req_shippings = array();
    $req_shipping_ids = array();
    $req_shipping_user_ids = array();
    $incart_shippings = array();
    $incart_shipping_ids = array();
    $incart_shipping_user_ids = array();

    if(is_array($cart) && count($cart) > 0) {
      
      // Check for required shippings
      foreach($cart as $c) {
        if($c[1] == OSP_TYPE_PRODUCT) {
          $item_id = $c[3];
          $item = Item::newInstance()->findByPrimaryKey($item_id);
          $item_data = ModelOSP::newInstance()->getItemData($item_id);

          if(@$item_data['i_shipping'] != 0 && isset($item['pk_i_id']) && !in_array($item['fk_i_user_id'], $req_shipping_user_ids)) {
            $opts = ModelOSP::newInstance()->getUserShippings($item['fk_i_user_id'], $user['fk_c_country_code'], 1);
            
            $req_shipping_user_ids[] = $item['fk_i_user_id'];
            $req_shippings[$item['fk_i_user_id']] = array(
              'fk_i_user_id' => $item['fk_i_user_id'],
              'options' => $opts,
              'use_default' => empty($opts) ? 1 : 0
            );
            
            // Create list of options those can be in cart
            if(is_array($opts) && count($opts) > 0) {
              foreach($opts as $op) {
                $req_shipping_ids[] = $op['pk_i_id'];
              }
            } else {
              $req_shipping_ids[] = 'stn-' . $item['fk_i_user_id'];
            }
          }
        }
      }
      
      $req_shipping_ids = array_filter(array_unique($req_shipping_ids));

      
      // Check what shippings we have in cart
      foreach($cart as $c) {
        if($c[1] == OSP_TYPE_SHIPPING) {
          $ship = ModelOSP::newInstance()->getShipping($c[3]);
          
          if(isset($ship['pk_i_id']) && $ship['i_status'] == 1 && in_array($ship['pk_i_id'], $req_shipping_ids) && !in_array($ship['fk_i_user_id'], $incart_shipping_user_ids)) {
            $incart_shipping_ids[] = $ship['pk_i_id'];
            $incart_shipping_user_ids[] = $ship['fk_i_user_id'];
            $incart_shippings[$ship['fk_i_user_id']] = $ship;
          } else {
            //osp_cart_remove($user_id, implode('x', $c));
          }
        }
      }
      
      $incart_shipping_ids = array_filter(array_unique($incart_shipping_ids));
    }
    
    
    // Add required shippings to cart
    if(count($req_shipping_ids) > 0) {
      foreach($req_shippings as $req_ship) {
        if(!in_array($req_ship['fk_i_user_id'], $incart_shipping_user_ids)) {
          if($req_ship['use_default'] == 1) {
            $rid = 'stn-' . $req_ship['fk_i_user_id'];   // to identify to whom standard shipping belongs!!
            $fee = (osp_param('default_shipping') > 0 ? osp_param('default_shipping') : 0);
          } else {
            $rid = $req_ship['options'][0]['pk_i_id'];
            $fee = $req_ship['options'][0]['f_fee'];
          }
          
          if($fee > 0) {
            $to_add_shipping = OSP_TYPE_SHIPPING . 'x1x' . $rid . 'x' . $fee . 'x' . $req_ship['fk_i_user_id'];
            
            if(!in_array($to_add_shipping, array_column($cart, 0))) {
              osp_cart_update($user_id, $to_add_shipping);
            }
          }
        }
      }
    }
    
    // Remove unwanted (not necessary) shippings
    foreach($cart as $c) {
      if($c[1] == OSP_TYPE_SHIPPING) {
        $ship = ModelOSP::newInstance()->getShipping($c[3]);
        
        $sparts = explode('-', $c[3]);

        if(
          $sparts[0] != 'stn' && (!isset($ship['pk_i_id']) || $ship['i_status'] != 1 || !in_array($ship['fk_i_user_id'], $req_shipping_user_ids))
          || $sparts[0] == 'stn' && (!isset($req_shippings[$sparts[1]]) || $req_shippings[$sparts[1]]['use_default'] != 1)
        ) {
          osp_cart_remove($user_id, implode('x', $c));
        }
      }
    }
  }
  
}

osc_add_hook('init', 'osp_cart_shipping_amounts_fix', 5);


// GET ORDER STATUS BASED ON ORDER ITEMS
function osp_order_status_based_on_items($order_items) {
  $status = array();
  $set = '';

  if(is_array($order_items) && count($order_items) > 0) {
    foreach($order_items as $oi) {
      if($oi['fk_i_shipping_id'] <= 0 || $oi['fk_i_shipping_id'] == null) {  // exclude shipping entries
        $status[] = $oi['i_status'];
      }
    }
        
    if(in_array(OSP_ORDER_PROCESSING, $status)) {
      $set = OSP_ORDER_PROCESSING;
    } else if(in_array(OSP_ORDER_SHIPPED, $status)) {
      $set = OSP_ORDER_SHIPPED;
    } else if(in_array(OSP_ORDER_COMPLETED, $status)) {
      $set = OSP_ORDER_COMPLETED;
    } else {
      $set = OSP_ORDER_CANCELLED;
    }
  }
  
  return $set;
}


// RESOLVE ORDER STATUS
function osp_resolve_order_status($order_id) {
  $items = ModelOSP::newInstance()->getOrderItems($order_id);
  $status = array();
  
  if(is_array($items) && count($items) > 0) {
    foreach($items as $i) {
      if($i['fk_i_shipping_id'] <= 0 || $i['fk_i_shipping_id'] == null) {  // exclude shipping entries
        $status[] = $i['i_status'];
      }
    }

    if(in_array(OSP_ORDER_PROCESSING, $status)) {
      $set = OSP_ORDER_PROCESSING;
    } else if(in_array(OSP_ORDER_SHIPPED, $status)) {
      $set = OSP_ORDER_SHIPPED;
    } else if(in_array(OSP_ORDER_COMPLETED, $status)) {
      $set = OSP_ORDER_COMPLETED;
    } else {
      $set = OSP_ORDER_CANCELLED;
    }

    ModelOSP::newInstance()->updateOrderStatus($order_id, $set);
  }
}


// RESOLVE ORDER STATUS
function osp_order_status_name($status) {
  if($status == OSP_ORDER_PROCESSING) {
    return __('Processing', 'osclass_pay');
  } else if($status == OSP_ORDER_SHIPPED) {
    return __('Shipped', 'osclass_pay');
  } else if($status == OSP_ORDER_COMPLETED) {
    return __('Completed', 'osclass_pay');
  } else if($status == OSP_ORDER_CANCELLED) {
    return __('Cancelled', 'osclass_pay');
  }
  
  return __('Unknown', 'osclass_pay');
}


// GENERATE PAGINATION IN FRONT
function osp_paginate($route, $page_id, $per_page, $count_all, $class = '', $custom_params = array()) {
  $html = '';
  $page_id = (int)$page_id;
  $page_id = ($page_id <= 0 ? 1 : $page_id);
  $param_string = '';
  
  if(is_array($custom_params) && count($custom_params) > 0) {  // as array
    $check_link = osc_route_url($route, array('pageId' => 1));
    $param_string = (strpos($check_link, '?') !== false ? '&' : '?');
    $params = array();
    
    foreach($custom_params as $key => $val) {
      if($key != '' && trim($val) != '') {
        $params[] = $key . '=' . trim(osc_esc_html($val));
      }
    }
    
    $param_string .= implode('&', array_filter($params));
    
    if($param_string == '?' || $param_string == '&') {
      $param_string = '';
    }
  }

  if($per_page < $count_all) {
    $html .= '<div class="osp-pagination ' . $class . '">';

    $pages = ceil($count_all/$per_page); 
    $page_actual = ($page_id == '' ? 1 : $page_id);

    if($pages > 6) {

      // Too many pages to list them all
      if($page_id == 1) { 
        $ids = array(1,2,3, $pages);

      } else if ($page_id > 1 && $page_id < $pages) {
        $ids = array(1,$page_id-1, $page_id, $page_id+1, $pages);

      } else {
        $ids = array(1, $page_id-2, $page_id-1, $page_id);
      }

      $old = -1;
      $ids = array_unique(array_filter($ids));

      foreach($ids as $i) {
        $url = osc_route_url($route, array('pageId' => $i)) . $param_string;

        if($old <> -1 && $old <> $i - 1) {
          $html .= '<span>&middot;&middot;&middot;</span>';
        }

        $html .= '<a href="' . $url . '" ' . ($page_actual == $i ? 'class="osp-active"' : '') . '>' . $i . '</a>';
        $old = $i;
      }

    } else {

      // List all pages
      for ($i = 1; $i <= $pages; $i++) {
        $url = osc_route_url($route, array('pageId' => $i)) . $param_string;

        $html .= '<a href="' . $url . '" ' . ($page_actual == $i ? 'class="osp-active"' : '') . '>' . $i . '</a>';
      }
    }

    $html .= '</div>';
  }
  

  return $html;
}



// GENERATE ADMIN PAGINATION
function osp_admin_paginate($file, $page_id, $per_page, $count_all, $class = '', $params = '') {
  $html = '';
  $page_id = (int)$page_id;
  $page_id = ($page_id <= 0 ? 1 : $page_id);
  $base_link = osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=' . $file . $params;

  if($per_page < $count_all) {
    $html .= '<div id="mb-pagination" class="' . $class . '">';
    $html .= '<div class="mb-pagination-wrap">';
    $html .= '<div>' . __('Page:', 'booking') . '</div>';

    $pages = ceil($count_all/$per_page); 
    $page_actual = ($page_id == '' ? 1 : $page_id);

    if($pages > 6) {

      // Too many pages to list them all
      if($page_id == 1) { 
        $ids = array(1,2,3, $pages);

      } else if ($page_id > 1 && $page_id < $pages) {
        $ids = array(1,$page_id-1, $page_id, $page_id+1, $pages);

      } else {
        $ids = array(1, $page_id-2, $page_id-1, $page_id);
      }

      $old = -1;
      $ids = array_unique(array_filter($ids));

      foreach($ids as $i) {
        $url = $base_link . '&pageId=' . $i;
        
        if($old <> -1 && $old <> $i - 1) {
          $html .= '<span>&middot;&middot;&middot;</span>';
        }

        $html .= '<a href="' . $url . '" ' . ($page_actual == $i ? 'class="mb-active"' : '') . '>' . $i . '</a>';
        $old = $i;
      }

    } else {

      // List all pages
      for ($i = 1; $i <= $pages; $i++) {
        $url = $base_link . '&pageId=' . $i;
        $html .= '<a href="' . $url . '" ' . ($page_actual == $i ? 'class="mb-active"' : '') . '>' . $i . '</a>';
      }
    }

    $html .= '</div>';
    $html .= '</div>';
  }

  return $html;
}


// ALLOW SEARCH BY ITEMS AVAILABLE FOR SALE
function osp_filter_extend() {
  if(Params::getParam('sBuyNow') <> '') {
    if(Params::getParam('sBuyNow') == 1) {  // only items those has eCommerce option enabled
      $value = 1;
    } else if (Params::getParam('sBuyNow') == 2) {  // only items those has eCommerce option disabled
      $value = 0;
    } else {  // all items
      $value = 9;
    }
    
    if($value != 9) {
      Search::newInstance()->addJoinTable( DB_TABLE_PREFIX.'t_osp_item_data.fk_i_item_id', DB_TABLE_PREFIX.'t_osp_item_data', DB_TABLE_PREFIX.'t_item.pk_i_id = '.DB_TABLE_PREFIX.'t_osp_item_data.fk_i_item_id', 'LEFT OUTER' ); 
      Search::newInstance()->addConditions(sprintf("coalesce(%st_osp_item_data.i_sell, 0) = %s", DB_TABLE_PREFIX, $value));
    }
  }
}

osc_add_hook('search_conditions', 'osp_filter_extend');


// ECOMMERCE PRODUCT SELECT BOX
function osp_buynow_select() {
  $current = Params::getParam('sBuyNow');
  
  if($current != 1 && $current != 2) {
    $current = 0;
  }
  
  $html = '';

  $html .= '<fieldset><div class="row osp-row">';
  $html .= '<label for="sBuyNow">' . __('Type', 'osclass_pay') . '</label>';

  $html .= '<select id="sBuyNow" name="sBuyNow">';
  $html .= '<option value="" ' . ($current == 0 ? 'selected="selected"' : '') . '>' . __('All listings', 'osclass_pay') . '</option>';
  $html .= '<option value="1" ' . ($current == 1 ? 'selected="selected"' : '') . '>' . __('"Buy now" listings', 'osclass_pay') . '</option>';
  $html .= '<option value="2" ' . ($current == 2 ? 'selected="selected"' : '') . '>' . __('Other listings', 'osclass_pay') . '</option>';

  $html .= '</select>';
  $html .= '</div></fieldset>';

  return $html;
}

// ECOMMERCE PRODUCT SELECT BOX HOOK
function osp_buynow_select_hook() {
  if(osp_param('filter_button_hook') == 1) {
    echo osp_buynow_select();
  }
}

osc_add_hook('search_form', 'osp_buynow_select_hook');


// GENERATE PAGINATION ON ITEMS LIST
function osp_item_paginate($page_id, $per_page, $count_all, $class = '') {
  $html = '';
  $page_id = (int)$page_id;
  $page_id = ($page_id <= 0 ? 1 : $page_id);

  if($per_page < $count_all) {
    $html .= '<div class="osp-pagination ' . $class . '">';

    $pages = ceil($count_all/$per_page); 
    $page_actual = ($page_id == '' ? 1 : $page_id);

    if($pages > 6) {

      // Too many pages to list them all
      if($page_id == 1) { 
        $ids = array(1,2,3, $pages);

      } else if ($page_id > 1 && $page_id < $pages) {
        $ids = array(1,$page_id-1, $page_id, $page_id+1, $pages);

      } else {
        $ids = array(1, $page_id-2, $page_id-1, $page_id);
      }

      $old = -1;
      $ids = array_unique(array_filter($ids));

      foreach($ids as $i) {
        $url = osc_route_url('osp-item-page', array('pageId' => $i));

        if($old <> -1 && $old <> $i - 1) {
          $html .= '<span>&middot;&middot;&middot;</span>';
        }

        $html .= '<a href="' . $url . '" ' . ($page_actual == $i ? 'class="osp-active"' : '') . '>' . $i . '</a>';
        $old = $i;
      }

    } else {

      // List all pages
      for ($i = 1; $i <= $pages; $i++) {
        $url = osc_route_url('osp-item-page', array('pageId' => $i));

        $html .= '<a href="' . $url . '" ' . ($page_actual == $i ? 'class="osp-active"' : '') . '>' . $i . '</a>';
      }
    }

    $html .= '</div>';
  }

  return $html;
}


// CHECK KOMFORTKASSE ORDERS VIA CRON
function osp_check_komfortkasse_orders() {
  if(osp_param('komfortkasse_enabled') == 1) {
    require_once osc_content_path() . 'plugins/osclass_pay/payments/komfortkasse/process.php';
  }
}

osc_add_hook('cron_daily', 'osp_check_komfortkasse_orders');



// CHECK IF VOUCHER IS VALID BY CODE
function osp_check_voucher_code($code, $is_credit = 0, $cart_amount = NULL, $cart_content = NULL) {
  $voucher = ModelOSP::newInstance()->getVoucherByCode($code);
  return osp_check_voucher_id(@$voucher['pk_i_id'], $is_credit);
}



// CHECK IF VOUCHER IS VALID BY ID
function osp_check_voucher_id($id, $is_credit = 0, $cart_amount = NULL, $cart_content = NULL) {
  $voucher = ModelOSP::newInstance()->getVoucher($id);
  
  if($cart_amount === NULL) {
    $cart = osp_cart_price(osc_logged_user_id(), 1); 
    $cart_amount = (isset($cart['price']) ? $cart['price'] : 0);
  }
  
  if($cart_amount <= 0) {
    $cart_amount = 0;
  }

  if(!isset($voucher['pk_i_id']) || $voucher['pk_i_id'] <= 0) {
    return array('error' => 'NOTFOUND', 'message' => __('Invalid code, no voucher has been found', 'osclass_pay'));

  } else if($is_credit == 0 && $voucher['s_type'] <> 'PERCENT' && $voucher['s_type'] <> 'AMOUNT') {
    return array('error' => 'INVALIDTYPECART', 'message' => __('Invalid voucher type, this type of voucher cannot be added into cart', 'osclass_pay'));

  } else if($is_credit == 1 && $voucher['s_type'] <> 'CREDIT') {
    return array('error' => 'INVALIDTYPECREDIT', 'message' => __('Invalid voucher type, this type of voucher is not credit type', 'osclass_pay'));

  } else if($voucher['dt_date_from'] <> '' && date('Y-m-d H:i:s', strtotime($voucher['dt_date_from'])) > date('Y-m-d H:i:s')) {
    return array('error' => 'NOTSTARTED', 'message' => sprintf(__('Voucher is not valid yet, promotion starts on %s', 'osclass_pay'), date('Y-m-d', strtotime($voucher['dt_date_from']))));

  } else if($voucher['dt_date_to'] <> '' && date('Y-m-d H:i:s', strtotime($voucher['dt_date_to'])) < date('Y-m-d H:i:s')) {
    return array('error' => 'ENDED', 'message' => sprintf(__('Voucher is not valid anymore, promotion has ended on %s', 'osclass_pay'), date('Y-m-d', strtotime($voucher['dt_date_to']))));

  } else if($voucher['i_quantity_used'] >= $voucher['i_quantity']) {
    return array('error' => 'USAGE', 'message' => __('Voucher is not valid anymore, it has reached maximum usage already', 'osclass_pay'));

  } else if($voucher['i_active'] <> 1) {
    return array('error' => 'INACTIVE', 'message' => __('Voucher is not valid anymore, promotion has been cancelled', 'osclass_pay'));

  } else if($voucher['s_user_ids'] <> '' && !in_array(osc_logged_user_id(), array_filter(explode(',', $voucher['s_user_ids'])))) {
    return array('error' => 'RESTRICTUSER', 'message' => __('Voucher is not available for you as it is restricted just to certain customers', 'osclass_pay'));

  } else if($voucher['s_group_ids'] <> '' && !in_array(osp_get_user_group(osc_logged_user_id()), array_filter(explode(',', $voucher['s_group_ids'])))) {
    return array('error' => 'RESTRICTGROUP', 'message' => __('Voucher is not available for you as it is restricted just to certain membership groups', 'osclass_pay'));

  } else if($voucher['d_min_amount'] > 0 && $cart_amount < $voucher['d_min_amount'] && $is_credit == 0) {
    return array('error' => 'MINAMOUNT', 'message' => sprintf(__('Voucher could not be used, minimum order amount for voucher is %s', 'osclass_pay'), osp_format_price($voucher['d_min_amount'])));

  } else if($voucher['i_quantity_per_user'] <= ModelOSP::newInstance()->getVoucherStats($voucher['pk_i_id'], osc_logged_user_id())) {
    return array('error' => 'MAXUSERQUANTITY', 'message' => sprintf(__('You have used voucher %dx already, that is maximum allowed usage per customer', 'osclass_pay'), $voucher['i_quantity_per_user']));

  } else if($voucher['s_restricted_products'] <> '' && $is_credit == 0) {
    $restricted = array_filter(explode(',', $voucher['s_restricted_products']));

    if($cart_content === NULL) {
      $cart_content = osp_cart_content(osc_logged_user_id());
    }
    
    if(is_array($cart_content) && count($cart_content) > 0) {
      foreach($cart_content as $c) {
        $type = $c[1];

        if(in_array($c[1], $restricted)) {
          return array('error' => 'PRODUCTNOTALLOWED', 'message' => sprintf(__('Voucher could not be used, as cart contains one or more restricted products for this voucher (%s)', 'osclass_pay'), osp_product_type_name($c[1])));
        }
      }
    }
  }




  if($voucher['s_type'] == 'AMOUNT') {
    $discount = osp_format_price($voucher['d_amount'] > $cart_amount ? $cart_amount : $voucher['d_amount']);
  } else if($voucher['s_type'] == 'PERCENT') {
    $discount = osp_format_price($cart_amount*($voucher['d_amount']/100));
    $discount .= ' (' . round($voucher['d_amount']) . '%)';
  } else if ($voucher['s_type'] == 'CREDIT') {
    $discount = osp_format_price($voucher['d_amount']);
  }

  if($voucher['s_type'] <> 'CREDIT') {
    return array('error' => 'OK', 'message' => sprintf(__('%s discount has been applied', 'osclass_pay'), $discount));
  } else {
    return array('error' => 'OK', 'message' => sprintf(__('%s has been added into your wallet', 'osclass_pay'), $discount));
  }
}





// CHECK IF VOUCHER PLUGIN IS INSTALLED
function osp_vouchers_enabled() {
  if(function_exists('vcr_call_after_install')) {
    return true;
  }

  return false;
}


// GET LOCALE
function osp_locale($data, $field, $is_admin = false) {
  if($is_admin) {
    return osp_locale_admin($data, $field);
  } else {
    return osp_locale_front($data, $field);
  } 
}


// GET LOCALE FRONT
function osp_locale_front($data, $field) {
  if(isset($data['locale'])) { 
    if(isset($data['locale'][$field]) && $data['locale'][$field] <> '') {
      return $data['locale'][$field];
    }
  }

  if(isset($data[$field]) && $data[$field] <> '') {
    return $data[$field];
  }

  return '';  
}

// GET LOCALE ADMIN
function osp_locale_admin($data, $field, $type = '') {
  if(isset($data['locale'])) { 
    if(isset($data['locale'][$field]) && $data['locale'][$field] <> '') {
      return $data['locale'][$field];
    }
  }

  return '';  
}


// CREATE LOCALE SELECT BOX
function osp_locale_box($file, $go_to_file = '', $scroll_to = '') {
  $html = '';
  $locales = OSCLocale::newInstance()->listAllEnabled();
  $current = osp_get_locale();

  $string = '';
  $scroll = '';

  if($go_to_file <> '') {
    $string = '&go_to_file=' . $go_to_file;
  }

  if($scroll_to <> '') {
    $scroll = '&scrollTo=' . $scroll_to;
  }

  $html .= '<select rel="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=osclass_pay/admin/' . $file . $string . $scroll .'" class="mb-select mb-select-locale" id="ospLocale" name="ospLocale">';

  foreach( $locales as $l ) {
    $html .= '<option value="' . $l['pk_c_code'] . '" ' . ($current == $l['pk_c_code'] ? 'selected="selected"' : '') . '>' . $l['s_name'] . '</option>';
  }
 
  $html .= '</select>';
  return $html;
}



// GET CURRENT OR DEFAULT ADMIN LOCALE
function osp_get_locale() {
  $locales = OSCLocale::newInstance()->listAllEnabled();

  if(Params::getParam('ospLocale') <> '') {
    $current = Params::getParam('ospLocale');
  } else {
    $current = (osc_current_user_locale() <> '' ? osc_current_user_locale() : osc_current_admin_locale());
    $current_exists = false;

    // check if current locale exist in front-office
    foreach( $locales as $l ) {
      if($current == $l['pk_c_code']) {
        $current_exists = true;
      }
    }

    if( !$current_exists ) {
      $i = 0;
      foreach( $locales as $l ) {
        if( $i==0 ) {
          $current = $l['pk_c_code'];
        }

        $i++;
      }
    }
  }

  return $current;
}


// NOTIFY USER ABOUT ITEM LIMITS
function osp_user_limit_message() {
  if(osc_get_osclass_location() == 'user' && (osc_get_osclass_section() == 'dashboard' || osc_get_osclass_section() == 'items')) {
    if(osp_param('groups_limit_items') == 1 && Cookie::newInstance()->get_value('ospHideULBox') != 1) {
      $data = osp_limit_items('data');

      $message = sprintf(__('An account limit has been introduced where you can have up to %s ads live at any moment. If you need more, you can become premium member to unlock further benefits. To read more about it please visit our FAQ page or contact us. You have currently active %s listings out of your limit %s listings (published in %s days).', 'osclass_pay'), $data['def_max_items'], $data['count'], $data['max_items'], $data['max_items_days']);
      
      $html = '';
      $html .= '<div class="osp-limit-box-wrap" style="display:none;">';
      $html .= '<div id="osp-limit-box">';
      $html .= '<a href="' . osc_base_url(true) . '?ospHideUserLimitBox=1" class="osp-box-close"><i class="fa fa-times"></i></a>';
      $html .= '<strong>' . __('Premium membership and account limit', 'osclass_pay') . '</strong>';
      $html .= '<span>' . $message . '</span>';
      $html .= '<a href="' . osc_route_url('osp-membership') . '" class="osp-purchase">' . __('Buy', 'osclass_pay') . '</a>';

      $html .= '</div>';
      $html .= '</div>';

      echo $html;
    }
  }
}

osc_add_hook('footer', 'osp_user_limit_message');


// LISTENER FOR USER LIMIT CLOSE BOX COOKIES
function osp_user_limit_box_hide_manage() {
  if(Params::getParam('ospHideUserLimitBox') == 1) {
    Cookie::newInstance()->push('ospHideULBox', 1);
	  Cookie::newInstance()->set();
    exit;
  }
}

osc_add_hook('init', 'osp_user_limit_box_hide_manage');


// FREE CREDITS IN WALLET AFTER LOGIN
function osp_credits_login() {
  if(osc_is_web_user_logged_in()) { 
    if(osp_param('wallet_enabled') == 1) {
      $in_wallet = osp_get_wallet_amount(osc_logged_user_id());

      if($in_wallet > 0) {
        osc_add_flash_info_message(sprintf(__('You have %s in your wallet. %s to promote your listings!', 'osclass_pay'), osp_format_price($in_wallet), '<a href="' . osc_route_url('osp-item') . '">' . __('Click here', 'osclass_pay') . '</a>'));
      }
    }
  }
}

osc_add_hook('after_login', 'osp_credits_login');


// CHECK IF USER IS SELLER
function osp_user_is_seller($user_id) {
  $sellers = array_filter(explode(',', osp_param('seller_users')));

  if(osc_is_web_user_logged_in() && (in_array($user_id, $sellers) || osp_param('seller_all') == 1)) {
    return true;
  }
  
  return false;  
}


// PRODUCT - ITEM ADD TO CART
function osp_product_to_cart_link($item_id = -1, $quantity = 1, $is_hook = 0, $offer_id = NULL) {
  $sellers = explode(',', osp_param('seller_users'));
  $html = '';

  if($item_id == -1) {
    $item_id = osc_item_id();
  }

  if($offer_id > 0) {
    $ptype = OSP_TYPE_OFFER; 

  } else {
    $ptype = OSP_TYPE_PRODUCT; 
  }
  
  if(osp_param('selling_allow') == 1 && $item_id <> '' && $item_id > 0) {
    $item = Item::newInstance()->findByPrimaryKey($item_id);
    
    if($offer_id > 0 || isset($item['fk_i_category_id']) && (osp_param('selling_category') == '' || in_array($item['fk_i_category_id'], explode(',', osp_param('selling_category'))))) {
      $data = ModelOSP::newInstance()->getItemData($item_id);
      $qty = @$data['i_quantity'] > 0 ? @$data['i_quantity'] : 0;
      $title = '';
      $class = '';
      
      if(osp_param('stock_management') == 1 && osp_param('quantity_show') == 1) {
        $class = 'osp-has-quantity'; 
      }

      if(($offer_id > 0 || @$data['i_sell'] == 1) && (in_array($item['fk_i_user_id'], $sellers) || osp_param('seller_all') == 1)) {
        if($item['i_price'] <> '' && $item['i_price'] > 0 || $offer_id > 0) {
          $price = 0;
          
          if($offer_id > 0) {
            $offer = ModelMO::newInstance()->getOfferById($offer_id);

            if(isset($offer['i_price']) && $offer['i_price'] > 0) {
              $qty = (int)($offer['i_quantity'] > 1 ? $offer['i_quantity'] : 1);
              $price = $offer['i_price']/$qty;
            }
            
          } else {
            $price = $item['i_price']/1000000;
          }
          
          $html .= '<div class="osp-product ' . ($is_hook == 1 ? 'osp-is-hook' : '') . ($offer_id > 0 ? 'osp-is-offer' : '') . '">';

          if(($qty <= 0 && osp_param('stock_management') == 1 && $offer_id === null) || ($item['fk_i_user_id'] == osc_logged_user_id() && osc_is_web_user_logged_in())) {
            if($item['fk_i_user_id'] == osc_logged_user_id() && osc_is_web_user_logged_in()) {
              $title = osc_esc_html(__('This is your product, you cannot buy it!', 'osclass_pay'));
            }

            $html .= '<a class="osp-product-to-cart osp-disabled osp-has-tooltip ' . $class . '" title="' . $title . '" href="#" onclick="return false;">' . OSP_SVG_ADD_TO_CART . __('Add to cart', 'osclass_pay') . '</a>';
          } else {
            $html .= '<a class="osp-product-to-cart ' . $class . '" href="' . osp_cart_add($ptype, $quantity, ($offer_id > 0 ? $offer_id : $item['pk_i_id']), round(osp_convert($price, $item['fk_c_currency_code']), 2)) . '">' . OSP_SVG_ADD_TO_CART . __('Add to cart', 'osclass_pay') . '</a>';
          }

          if(osp_param('stock_management') == 1 && osp_param('quantity_show') == 1) {
            if($qty > 0) {
              $html .= '<div class="osp-product-quantity"><strong>' . $qty . 'x</strong><span>' . __('In stock', 'osclass_pay') . '</span></div>';
            } else {
              $html .= '<div class="osp-product-quantity"><strong class="osp-sold-out">' . __('Sold out!', 'osclass_pay') . '</strong></div>';
            }
          }

          $html .= '</div>';
        }
      }
    }
  }

  return $html;
}


function osp_product_to_cart_link_hook($item) {
  if(osp_param('cart_button_hook') == 1) {
    echo osp_product_to_cart_link(isset($item['pk_i_id']) ? $item['pk_i_id'] : $item, 1, 1);
  }
}

osc_add_hook('item_detail', 'osp_product_to_cart_link_hook', 9);


// CHECK IF QUANTITY IS EDITABLE FOR SELECTED PRODUCT TYPE
function osp_quantity_editable($type) {
  if($type == OSP_TYPE_PRODUCT || $type == OSP_TYPE_PACK) {
    return true;
  }

  return false;
}


// ADD BANNER FORM TO FOOTER
function osp_banner_footer() {
  if(osp_param('banner_allow') == 1 && osp_plugin_ready('banner_ads')) {
    require_once 'user/banner_form.php';
  }
}

osc_add_hook('footer', 'osp_banner_footer');


// CREATE BANNER BUTTON
function osp_banner_button($group_id, $echo = true) {
  if(osp_param('banner_allow') == 1 && osp_plugin_ready('banner_ads')) {
    //ba_show_banner($group_id);
    $txt = '<a href="#" class="osp-advertise-here" onclick="banner_create(\'' . $group_id . '\');return false;">' . __('Advertise here!', 'osclass_pay'). '</a>';

    if($echo) {
      echo $txt;
    } else {
      return $txt;
    }
  }
}


function osp_hook_banner_button($group_id, $echo = true) {
  if(osp_param('banner_hook') == 1 && osp_plugin_ready('banner_ads')) {
    osp_banner_button($group_id, $echo);
  }
}

// osc_add_hook('ba_show_banner', 'osp_hook_banner_button');


// CHECK IF PLUGIN IS INSTALLED
function osp_plugin_ready($name) {
  if($name == 'banner_ads' && function_exists('ba_call_after_install')) {
    return true;
  }

  return false;
}


// PREPARE PAYMENT DATA WHEN PAYMENT WAS SUCCESSUL
function osp_prepare_payment_data($amount, $payment_id, $user_id, $product_type) {
  // $product_type[0] == payment type
  // $product_type[1] == quantity
  // $product_type[2] == item_id / user_id / group id (for membership)
  // $product_type[3] == hours (for republish) / expire date (for membership)
  // $product_type[4] == repeat (for republish)

  $payment_details = array(
    'type' => $product_type[0],
    'quantity' => $product_type[1],
    'item_id' => $product_type[2],
    'hours' => (isset($product_type[3]) ? $product_type[3] : ''),
    'repeat' => (isset($product_type[4]) ? $product_type[4] : ''),
    'payment_id' => $payment_id,
    'pack_user_id' => ($product_type[0] == OSP_TYPE_PACK ? $user_id : ''),
    'pack_value' => ($product_type[0] == OSP_TYPE_PACK ? $amount : 0),
    'banner_id' => ($product_type[0] == OSP_TYPE_BANNER ? $product_type[2] : ''),
    'banner_budget' => ($product_type[0] == OSP_TYPE_BANNER ? $amount : 0),
    'shipping_id' => ($product_type[0] == OSP_TYPE_SHIPPING ? $product_type[2] : ''),
    'shipping_fee' => ($product_type[0] == OSP_TYPE_SHIPPING ? $amount : 0),
    'voucher_id' => ($product_type[0] == OSP_TYPE_VOUCHER ? $product_type[2] : ''),
    'product_id' => ($product_type[0] == OSP_TYPE_PRODUCT ? $product_type[2] : ''),
    'reservation_id' => ($product_type[0] == OSP_TYPE_BOOKING ? $product_type[2] : ''),
    'auction_id' => (in_array($product_type[0], array(OSP_TYPE_AUCTION_PAY,OSP_TYPE_AUCTION_BUYOUT,OSP_TYPE_AUCTION_FEE,OSP_TYPE_AUCTION_BID_FEE)) ? $product_type[2] : ''),
    'offer_id' => (in_array($product_type[0], array(OSP_TYPE_OFFER)) ? $product_type[2] : ''),
    'group_user_id' => $user_id,
    'group_id' => (isset($product_type[2]) ? $product_type[2] : ''),
    'group_expire' => (isset($product_type[3]) ? $product_type[3] : ''),
    'multiple_type' => (isset($product_type[1]) ? $product_type[1] : ''),
    'multiple_amount' => $amount,
    'user_id' => $user_id
  );

  return $payment_details;
}


// ITEM SELL FORM
function osp_item_sell_form($catId = false, $item_id = false) {
  $html = '';
  $current_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  $sellers = explode(',', osp_param('seller_users'));
  $quantity = 1;
  $sell = 0;
  $shipping = 0;

  if($item_id) {
    $item_data = ModelOSP::newInstance()->getItemData($item_id);
    $quantity = @$item_data['i_quantity'];
    $sell = @$item_data['i_sell'];
    $shipping = @$item_data['i_shipping'];
  }


  // for oc-admin
  if (strpos($current_url, osc_admin_base_url()) !== false) {
    if(
      osp_param('selling_allow') == 1 
      && (osp_param('selling_category') == '' || in_array($catId, explode(',', osp_param('selling_category'))))
      && (in_array(osc_logged_user_id(), $sellers) || (osp_param('seller_all') == 1 && osc_is_web_user_logged_in()) || osc_is_admin_user_logged_in())
    ) { 
      $html .= '<div class="form-row">';
      $html .= '<div class="form-label" for="osp_sell">' . __('Enable Sale', 'osclass_pay') . '</div>';
      $html .= '<div class="form-controls">';
      $html .= '<select id="osp_sell" name="osp_sell">';
      $html .= '<option value="0" ' . ($sell == 0 ? 'selected="selected"' : '') . '>' . __('No', 'osclass_pay') . '</option>';
      $html .= '<option value="1" ' . ($sell == 1 ? 'selected="selected"' : '') . '>' . __('Yes', 'osclass_pay') . '</option>';
      $html .= '</select>';
      $html .= '<p id="osp_sell_info">' . __('Enable online sales for this item. Payment goes to site owner account.', 'osclass_pay') . '</p>';
      $html .= '</div>';
      $html .= '</div>';

      if(osp_param('enable_shipping') == 1) {
        $html .= '<div class="form-row">';
        $html .= '<div class="form-label" for="osp_shipping">' . __('Shipping', 'osclass_pay') . '</div>';
        $html .= '<div class="form-controls">';
        $html .= '<select id="osp_shipping" name="osp_shipping">';
        $html .= '<option value="0" ' . ($shipping == 0 ? 'selected="selected"' : '') . '>' . __('No', 'osclass_pay') . '</option>';
        $html .= '<option value="1" ' . ($shipping == 1 ? 'selected="selected"' : '') . '>' . __('Yes', 'osclass_pay') . '</option>';
        $html .= '</select>';
        $html .= '<p id="osp_sell_info">' . __('Require shipping for this item.', 'osclass_pay') . ' <a href="' . osc_route_url('osp-shipping') . '" target="_blank">' . __('Manage my shipping options', 'osclass_pay') . '</a>.</p>';
        $html .= '</div>';
        $html .= '</div>';
      }
      
      if(osp_param('stock_management') == 1) {
        $html .= '<div class="form-row">';
        $html .= '<div class="form-label" for="osp_quantity">' . __('Available Quantity', 'osclass_pay') . '</div>';
        $html .= '<div class="form-controls">';
        $html .= '<input id="osp_quantity" type="text" name="osp_quantity" value="' . $quantity . '">';
        $html .= '<p id="osp_quantity_info">' . __('Enter how much quantity of this product is available.', 'osclass_pay') . '</p>';
        $html .= '</div>';
        $html .= '</div>';
      }
    }
  } else {
    if(
      osc_is_web_user_logged_in()
      && osp_param('selling_allow') == 1 
      && (osp_param('selling_category') == '' || in_array($catId, explode(',', osp_param('selling_category'))))
      && (in_array(osc_logged_user_id(), $sellers) || osp_param('seller_all') == 1)
    ) { 
      $html .= '<div class="control-group">';
      $html .= '<label class="control-label" for="osp_sell">' . __('Enable Sale', 'osclass_pay') . '</label>';
      $html .= '<div class="controls">';
      $html .= '<select id="osp_sell" name="osp_sell">';
      $html .= '<option value="0" ' . ($sell == 0 ? 'selected="selected"' : '') . '>' . __('No', 'osclass_pay') . '</option>';
      $html .= '<option value="1" ' . ($sell == 1 ? 'selected="selected"' : '') . '>' . __('Yes', 'osclass_pay') . '</option>';
      $html .= '</select>';
      $html .= '<p id="osp_sell_info">' . __('Enable online sales for this item. Payment goes to site owner account.', 'osclass_pay') . '</p>';
      $html .= '</div>';
      $html .= '</div>';

      if(osp_param('enable_shipping') == 1) {
        $html .= '<div class="control-group">';
        $html .= '<label class="control-label" for="osp_shipping">' . __('Shipping', 'osclass_pay') . '</label>';
        $html .= '<div class="controls">';
        $html .= '<select id="osp_shipping" name="osp_shipping">';
        $html .= '<option value="0" ' . ($shipping == 0 ? 'selected="selected"' : '') . '>' . __('No', 'osclass_pay') . '</option>';
        $html .= '<option value="1" ' . ($shipping == 1 ? 'selected="selected"' : '') . '>' . __('Yes', 'osclass_pay') . '</option>';
        $html .= '</select>';
        $html .= '<p id="osp_sell_info">' . __('Require shipping for this item.', 'osclass_pay') . ' <a href="' . osc_route_url('osp-shipping') . '" target="_blank">' . __('Manage my shipping options', 'osclass_pay') . '</a>.</p>';
        $html .= '</div>';
        $html .= '</div>';        
      }

      if(osp_param('stock_management') == 1) {
        $html .= '<div class="control-group">';
        $html .= '<label class="control-label" for="osp_quantity">' . __('Available Quantity', 'osclass_pay') . '</label>';
        $html .= '<div class="controls">';
        $html .= '<input id="osp_quantity" type="text" name="osp_quantity" value="' . $quantity . '">';
        $html .= '<p id="osp_quantity_info">' . __('Enter how much quantity of this product is available.', 'osclass_pay') . '</p>';
        $html .= '</div>';
        $html .= '</div>';
      }
    }
  }

  echo $html;
}

osc_add_hook('item_form', 'osp_item_sell_form', 8);
osc_add_hook('item_edit', 'osp_item_sell_form', 8);


// INSERT SALE AND QUANTITY WHEN LISTING IS PUBLISHED
function osp_item_post_data_insert($item, $is_hook = 1) {
  $data = array(
    'fk_i_item_id' => $item['pk_i_id'],
    'i_sell' => Params::getParam('osp_sell'),
    'i_quantity' => Params::getParam('osp_quantity'),
    'i_shipping' => Params::getParam('osp_shipping')
  );
  
  ModelOSP::newInstance()->updateItemData2($data, $is_hook);
}

osc_add_hook('posted_item', 'osp_item_post_data_insert', 1);
osc_add_hook('edited_item', 'osp_item_post_data_insert', 1);



// CREATE USER INVITE LINK
function osp_invite_link() {
  $code = osp_get_referral();
  $url = osc_register_account_url();

  if (osc_rewrite_enabled()) {
    $url .= '?ospref=' . $code;
  } else {
    $url .= '&ospref=' . $code;
  }

  return $url;
}


// USER REFERRAL FORM
function osp_user_referral_form() {
  $current_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

  if(osp_param('wallet_enabled') == 1 && osp_param('wallet_referral') <> '' && osp_param('wallet_referral') > 0) { 

    // for oc-admin
    if (strpos($current_url, osc_admin_base_url()) !== false) {
      $html  = '<div class="form-row">';
      $html .= '<div class="form-label" for="osp_referral">' . __('Referral code', 'osclass_pay') . '</div>';
      $html .= '<div class="form-controls">';
      $html .= '<input id="osp_referral" type="text" name="osp_referral" value="' . Params::getParam('ospref') . '" autocomplete="off">';
      $html .= '<p id="osp_referral_info">' . sprintf(__('Get %s for using referral code from your friend', 'osclass_pay'), osp_format_price(osp_param('wallet_referral'))) . '</p>';
      $html .= '</div>';
      $html .= '</div>';
    } else {
      $html  = '<div class="control-group">';
      $html .= '<label class="control-label" for="osp_referral">' . __('Referral code', 'osclass_pay') . '</label>';
      $html .= '<div class="controls">';
      $html .= '<input id="osp_referral" type="text" name="osp_referral" value="' . Params::getParam('ospref') . '" autocomplete="off">';
      $html .= '<p id="osp_referral_info">' . sprintf(__('Get %s for using referral code from your friend', 'osclass_pay'), osp_format_price(osp_param('wallet_referral'))) . '</p>';
      $html .= '</div>';
      $html .= '</div>';
    }

    echo $html;
  }
}

osc_add_hook('user_register_form', 'osp_user_referral_form');


// GENERATE REFERRAL CODE BASED ON SECRET AND USER ID
function osp_get_referral() {
  if(osc_logged_user_id() > 0) {
    $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
    
    if($user !== false && isset($user['s_secret'])) {
      return strtoupper(substr($user['s_secret'], 0, 6) . '_' . osc_logged_user_id());
    }
  }
  
  return '';
}


// CHECK IF REFERRAL CODE IS CORRECT
function osp_check_referral($string) {
  $referral = explode('_', $string);

  $secret = $referral[0];
  $user_id = @$referral[1];

  $user = User::newInstance()->findByPrimaryKey($user_id);

  if($user !== false && isset($user['s_secret'])) {
    if(strtoupper(substr($user['s_secret'], 0, 6)) == strtoupper($secret)) {
      return true;
    }
  }

  return false;
}


// MANAGE CREDITS WHEN REFERRAL ENTERED
function osp_user_referral_manage($user_id) {
  if(is_array($user_id)) { $user_id = $user_id['pk_i_id']; }

  if(osp_param('wallet_enabled') == 1 && osp_param('wallet_referral') <> '' && osp_param('wallet_referral') > 0) {
    $referral = Params::getParam('osp_referral');

    if($referral <> '') {
      $detail = explode('_', $referral);
      $secret = $detail[0];
      $user_ref_id = @$detail[1];

      // REFERRAL CODE IS VALID, ADD CREDITS
      if(osp_check_referral($referral)) {

        // newly registered user
        $user_reg = User::newInstance()->findByPrimaryKey($user_id);
        ModelOSP::newInstance()->saveLog(sprintf(__('Credit for using referral code %s to user %s (%s) at %s', 'osclass_pay'), $referral, $user_reg['s_name'], (osp_param('wallet_referral') . osp_currency_symbol()), osc_page_title()), 'wallet_' . $user_reg['pk_i_id'] . '_' . date('YmdHis'), osp_param('wallet_referral'), osp_currency(), $user_reg['s_email'], $user_reg['pk_i_id'], 'Referral registration credits', OSP_TYPE_PACK, 'REFERRAL');
        osp_wallet_update($user_reg['pk_i_id'], osp_param('wallet_referral'));

        // referral user
        $user_ref = User::newInstance()->findByPrimaryKey($user_ref_id);
        ModelOSP::newInstance()->saveLog(sprintf(__('Credit for providing referral code %s to user %s (%s) at %s', 'osclass_pay'), $referral, $user_ref['s_name'], (osp_param('wallet_referral') . osp_currency_symbol()), osc_page_title()), 'wallet_' . $user_reg['pk_i_id'] . '_' . date('YmdHis'), osp_param('wallet_referral'), osp_currency(), $user_ref['s_email'], $user_ref['pk_i_id'], 'Referral registration credits', OSP_TYPE_PACK, 'REFERRAL');
        osp_wallet_update($user_ref['pk_i_id'], osp_param('wallet_referral'));

        osc_add_flash_ok_message(sprintf(__('Referral code %s successfully used. You and your friend got bonus %s to wallet', 'osclass_pay'), $referral, osp_format_price(osp_param('wallet_referral'))));
      } else {
        osc_add_flash_error_message(sprintf(__('Referral code %s was not recognized.', 'osclass_pay'), $referral));
      }
    }
  }
}

osc_add_hook('user_register_completed', 'osp_user_referral_manage');


// PAYMENT PATH
function osp_path() {
  return osc_content_path() . 'plugins/' . osc_plugin_folder(__FILE__);
}


// PAYMENT URL
function osp_url() {
  return osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__);
}



// UPDATE ITEM ACTIVE
function osp_item_active($item_id, $value = 0) {
  if($value == 1 && function_exists('iv_add_item') && function_exists('iv_count_validated')) {
    $enabled = osc_get_preference('enable','plugin-item_validation');
    $min_auto_validated = osc_get_preference('min_auto_validated','plugin-item_validation');

    $item = Item::newInstance()->findByPrimaryKey($item_id);
    $user_id = $item['fk_i_user_id'];
    $user_count = iv_count_validated( $user_id );

    $count_pass = true;
    if( $min_auto_validated <= 0 || $min_auto_validated == '' ) {
      $count_pass = false;
    } else {
      if( $min_auto_validated <= $user_count ) {
        $count_pass = true;
      } else {
        $count_pass = false;
      }
    }

    if( isset($item['pk_i_id']) && $item['pk_i_id'] > 0 && $enabled == 1) {
      if( !$count_pass ) {
        osc_add_flash_info_message(__('Listing will not be visible until administrator approves it', 'osclass_pay'));
        return false;   // item validation require item to be validate it. Do not activate it

      } else {
        return Item::newInstance()->dao->query(sprintf('UPDATE %st_item SET b_active = %d WHERE pk_i_id = %d', DB_TABLE_PREFIX, $value, $item_id));
      }
    } else {
      return Item::newInstance()->dao->query(sprintf('UPDATE %st_item SET b_active = %d WHERE pk_i_id = %d', DB_TABLE_PREFIX, $value, $item_id));
    }
  } else {
    return Item::newInstance()->dao->query(sprintf('UPDATE %st_item SET b_active = %d WHERE pk_i_id = %d', DB_TABLE_PREFIX, $value, $item_id));
  }
}


// FORMAT PRICE
function osp_format_price($price, $use_symbol = 1, $custom_symbol = '', $custom_decimals = -1) {
  if($use_symbol == 0) {
    $symbol = '';
  } else if($use_symbol == 1) {
    $symbol = osp_currency_symbol();
  } else if($use_symbol == 9) {
    $symbol = osp_currency_symbol($custom_symbol);
  } else {
    $symbol = osp_currency();
  }

  $decimals = (int)($custom_decimals <> -1 ? $custom_decimals : osp_param('price_decimals'));
  $position = (int)osp_param('price_position');  // 0 - after price, 1 - before price
  $space = osp_param('price_space'); // space between price and sybmol, true or false
  $decimal_symbol = osp_param('price_decimal_symbol');
  $thousands = osp_param('price_thousand_symbol');

  if($space) {
    $space = ' ';
  } else {
    $space = '';
  }

  $price = number_format((float)$price, $decimals, $decimal_symbol, $thousands);

  if($use_symbol == 0) {
    return $price;
  } else if($position == 0) {
    return $price . $space . $symbol;
  } else {
    return $symbol . $space . $price;
  }
}


// ON ITEM ACTIVATE, CHECK IF PAID. IF NOT, REFUSE
function osc_item_manage_activate($item_id) {
  if(osp_fee_exists(OSP_TYPE_PUBLISH, $item_id) && !osp_fee_is_paid(OSP_TYPE_PUBLISH, $item_id)) {
    $fee = osp_get_fee(OSP_TYPE_PUBLISH, 1, $item_id);

    osp_item_active($item_id, 0);   // deactivate back if not paid
    osc_add_flash_error_message(sprintf(__('Item ID %s has not been paid yet, you cannot activate item until publish fee %s is paid. In order to show listing click on "Pay Publish Fee" link.', 'osclass_pay'), $item_id, $fee.osp_currency_symbol()), 'admin');
  }
}

osc_add_hook('activate_item', 'osc_item_manage_activate');


// GENERATE ITEM PAYMENT OPTIONS
function osp_item_options($item_id) {
  $types = array(OSP_TYPE_PUBLISH, OSP_TYPE_IMAGE, OSP_TYPE_TOP, OSP_TYPE_PREMIUM, OSP_TYPE_HIGHLIGHT, OSP_TYPE_REPUBLISH);
  $currency = osp_currency_symbol();
  $count = 0;

  $html = '<div class="osp-options" data-item="' . $item_id . '">';
  foreach($types as $type) {
    if(osp_fee_is_allowed($type)) {
      if($type == OSP_TYPE_PUBLISH) {

        if(!osp_fee_is_paid($type, $item_id) && osp_fee_exists($type, $item_id)) {
          $fee = osp_get_fee($type, 1, $item_id);

          if($fee > 0) {
            $html .= '<div class="osp-o-row osp-has-tooltip-left" title="' . osc_esc_html(__('In order to show your listing it is required to pay publish fee', 'osclass_pay')) . '"><i class="fa fa-plus-circle"></i> ' . __('Publish Fee', 'osclass_pay') . '</div>';
            $html .= '<div class="osp-block" data-type="' . $type . '">';
            $html .= '<div class="osp-line"><label for="' . $type . '_' . $item_id . '"><input class="osp-input" type="checkbox" code="' . $type . 'x1x' . $item_id . '" name="' . $type . '" value="1" id="' . $type . '_' . $item_id . '" checked="checked"><div>' . __('Publish fee', 'osclass_pay') . '<em>(<span>' . osp_format_price($fee) . '</span>)</em></div></label></div>';
            $html .= '</div>';
            $count++;
          }
        }

      } else if($type == OSP_TYPE_TOP) {

        $fee = osp_get_fee($type, 1, $item_id);

        if($fee > 0) {
          $html .= '<div class="osp-o-row osp-has-tooltip-left" title="' . osc_esc_html(__('Your listings will be moved to top position in search results', 'osclass_pay')) . '"><i class="fa fa-arrow-circle-up"></i> ' . __('Move to Top', 'osclass_pay') . '</div>';
          $html .= '<div class="osp-block" data-type="' . $type . '">';
          $html .= '<div class="osp-line"><label for="' . $type . '_' . $item_id . '"><input class="osp-input" type="checkbox" code="' . $type . 'x1x' . $item_id . '" name="' . $type . '" value="1" id="' . $type . '_' . $item_id . '"><div>' . __('Move to top fee', 'osclass_pay') . '<em>(<span>' . osp_format_price($fee) . '</span>)</em></div></label></div>';
          $html .= '</div>';
          $count++;
        }

      } else if ($type == OSP_TYPE_IMAGE) {

        if(!osp_fee_is_paid($type, $item_id) && osp_fee_exists($type, $item_id)) {
          $fee = osp_get_fee($type, 1, $item_id);

          if($fee > 0) {
            $html .= '<div class="osp-o-row osp-has-tooltip-left" title="' . osc_esc_html(__('In order to show images on your listing it is required to pay fee', 'osclass_pay')) . '"><i class="fa fa-camera"></i> ' . __('Show Images Fee', 'osclass_pay') . '</div>';
            $html .= '<div class="osp-block" data-type="' . $type . '">';
            $html .= '<div class="osp-line"><label for="' . $type . '_' . $item_id . '"><input class="osp-input" type="checkbox" code="' . $type . 'x1x' . $item_id . '" name="' . $type . '" value="1" id="' . $type . '_' . $item_id . '" checked="checked"><div>' . __('Show images fee', 'osclass_pay') . '<em>(<span>' . osp_format_price($fee) . '</span>)</em></div></label></div>';
            $html .= '</div>';
            $count++;
          }
        }

      } else if ($type == OSP_TYPE_PREMIUM || $type == OSP_TYPE_HIGHLIGHT) {

        if($type == OSP_TYPE_PREMIUM) {
          $icon = '<i class="fa fa-star"></i>';
          if(!osp_fee_is_paid($type, $item_id)) {
            $title = __('Make Premium', 'osclass_pay');
          } else {
            $title = __('Extend Premium', 'osclass_pay');
          }

          $tooltip = osc_esc_html(__('Make your listing unique on home and search page!', 'osclass_pay'));

          $duration = (osp_param('premium_duration') <> '' ? osp_param('premium_duration') : 24);
        } else {
          $icon = '<i class="fa fa-paint-brush"></i>';
          if(!osp_fee_is_paid($type, $item_id)) {
            $title = __('Highlight Item', 'osclass_pay');
          } else {
            $title = __('Extend Highlight', 'osclass_pay');
          }

          $tooltip = osc_esc_html(__('Make listing more visible and attract more people!', 'osclass_pay'));
          $duration = (osp_param('highlight_duration') <> '' ? osp_param('highlight_duration') : 24);
        }

        $duration_array = explode(',', $duration);


        $html .= '<div class="osp-o-row osp-has-tooltip-left" title="' . $tooltip . '">' . $icon . ' ' . $title . '</div>';
        $html .= '<div class="osp-block" data-type="' . $type . '">';

        foreach($duration_array as $d) {
          $fee = osp_get_fee($type, 1, $item_id, $d);
          $html .= '<div class="osp-line"><label for="' . $type . '_' . $item_id . '_' . $d . '"><input class="osp-input" type="checkbox" code="' . $type . 'x1x' . $item_id . 'x' . $d . '" name="' . $type . '" value="' . $d . '" id="' . $type . '_' . $item_id . '_' . $d . '"><div>' . osp_duration_name($d) . ' <em>(<span>' . osp_format_price($fee) . '</span>)</em></div></label></div>';
        }

        $html .= '</div>';
        $count++;

      } else if ($type == OSP_TYPE_REPUBLISH) {

        if(!osp_fee_is_paid($type, $item_id)) {
          $title = __('Automatically Republish Item', 'osclass_pay');
        } else {
          $title = __('Extend Republish', 'osclass_pay');
        }

        $duration = (osp_param('republish_duration') <> '' ? osp_param('republish_duration') : 24);
        $duration_array = explode(',', $duration);
        $repeat = (osp_param('republish_repeat') <> '' ? osp_param('republish_repeat') : 1);
        $repeat_array = explode(',', $repeat);


        $html .= '<div class="osp-o-row osp-has-tooltip-left" title="' . osc_esc_html(__('Listing will be instantly renewed and then republished multiple times in selected intervals', 'osclass_pay')) . '"><i class="fa fa-repeat"></i> ' . $title . '</div>';
        $html .= '<div class="osp-block" data-type="' . $type . '_1">';
        $html .= '<div class="osp-small-title">' . __('How often republish item?', 'osclass_pay') . '</div>';

        foreach($duration_array as $d) {
          $fee = osp_get_fee($type, 1, $item_id, $d);
          $html .= '<div class="osp-line"><label for="' . $type . '_' . $item_id . '_' . $d . '_1"><input class="osp-input" type="checkbox" code="' . $type . 'x1x' . $item_id . 'x' . $d . '" name="' . $type . '_1" value="' . $d . '" id="' . $type . '_' . $item_id . '_' . $d . '_1"><div>' . osp_duration_name($d) . ' <em>(<span>' . osp_format_price($fee) . '</span>)</em></div></label></div>';
        }

        $html .= '</div>';

        $html .= '<div class="osp-block" data-type="' . $type . '_2">';
        $html .= '<div class="osp-small-title">' . __('How many times?', 'osclass_pay') . '</div>';

        foreach($repeat_array as $r) {
          $html .= '<div class="osp-line"><label for="' . $type . '_' . $item_id . '_' . $r . '_2"><input class="osp-input" type="checkbox" code="x' . $r . '" name="' . $type . '_2" value="' . $r . '" id="' . $type . '_' . $item_id . '_' . $r . '_2"><div>' . __('Repeat', 'osclass_pay') . ' ' . $r . 'x</div></label></div>';
        }

        $html .= '</div>';
        $count++;

      }
    }
  }

  if($count > 0) {
    $html .= '<div class="osp-b-line">';
    $html .= '<a class="osp-item-to-cart" data-item="' . $item_id . '" href="' . osp_cart_add(OSP_TYPE_MULTIPLE, 1, $item_id, '') . '"><i class="fa fa-plus-circle"></i> ' . __('Add to cart', 'osclass_pay') . '</a>';
    $html .= '</div>';
  } else {
    $html .= '<div class="osp-b-line osp-b-line-empty">' . __('No promotion options available', 'osclass_pay') . '</div>';
  }

  $html .= '</div>';

  return $html;
}


// CREATE CART STRING
function osp_create_cart_string($multiple_type, $user_id = '', $item_id = '') {
  if($multiple_type == 1) {
    $cart_string = ModelOSP::newInstance()->getCart($user_id);
  } else {
    $cart_string = ModelOSP::newInstance()->itemsToCartString($item_id);
  }

  return $cart_string;
}


// PUBLISH-EDIT ITEM, ADD PAYMENT OPTIONS
function osp_item_publish_form($category_id = '') {
  if(defined('OC_ADMIN') && OC_ADMIN) {
    return false;
  }
  
  include_once 'user/item_post.php';
}

osc_add_hook('item_form', 'osp_item_publish_form', 9);



// ADD TEXT TO STATUS COLUMN IN OC-ADMIN - LIST ITEMS
function osp_admin_item_title($line, $item) {
  $publish_allow = osp_param('publish_allow');
  $item_id = $item['pk_i_id'];
  
  if($publish_allow == 1 && osp_fee_exists(OSP_TYPE_PUBLISH, $item_id)) {
    if(osp_fee_is_paid(OSP_TYPE_PUBLISH, $item_id)) {
      $line['status'] .= ' <span class="osp-pay-line osp-is-paid">' . __('Paid', 'osclass_pay') . '</span>';
    } else {
      $line['status'] .= ' <span class="osp-pay-line">' . __('Unpaid', 'osclass_pay') . '</span>';
    }
  }

  $types = array(OSP_TYPE_IMAGE, OSP_TYPE_HIGHLIGHT, OSP_TYPE_REPUBLISH);
  $icons = array('fa-camera', 'fa-paint-brush', 'fa-repeat');
  $message1 = array(__('Show image paid', 'osclass_pay'), __('Highlight paid', 'osclass_pay'), __('Republish paid', 'osclass_pay'));
  $message2 = array(__('Show image not paid', 'osclass_pay'), __('Highlight pending payment', 'osclass_pay'), __('Republish pending payment', 'osclass_pay'));



  $i = 0;
  foreach($types as $type) {
    if(osp_fee_is_allowed($type) && osp_fee_exists($type, $item_id)) {
      $line['status'] .= ' <span class="osp-st-line pt' . $type . '" title="' . osc_esc_html(osp_fee_is_paid($type, $item_id) ? $message1[$i] : $message2[$i]) . '"><i class="fa ' . $icons[$i] . '"></i></span>';
    }

    $i++;
  }

  return $line;
}

osc_add_filter('items_processing_row', 'osp_admin_item_title', 10);



// ADMIN ACTIONS - ITEM TOOLBAR
function osp_admin_item_toolbar($list, $item) {
  $types = array(OSP_TYPE_TOP, OSP_TYPE_IMAGE, OSP_TYPE_HIGHLIGHT, OSP_TYPE_PUBLISH);
  $a = array(__('Move to Top', 'osclass_pay'), __('Pay Show Image', 'osclass_pay'), __('Pay Highlight', 'osclass_pay'), __('Pay Publish Fee', 'osclass_pay'));
  $b = array('', __('Disable Show Image', 'osclass_pay'), __('Unhighlight', 'osclass_pay'), '');
  $c = array('', __('Require Pay Show Image', 'osclass_pay'), '', __('Require Pay Publish Fee', 'osclass_pay'));
  $item_id = $item['pk_i_id'];
  $item = Item::newInstance()->findByPrimaryKey($item_id);

  $i = 0;
  foreach($types as $type) {
    if(osp_fee_is_allowed($type)) {
      $fee = ModelOSP::newInstance()->getCategoryFee($type, $item['fk_i_category_id']);

      if(osp_fee_exists($type, $item_id)) {
        if(!osp_fee_is_paid($type, $item_id)) {
          // Mark
          $list = array_merge(array('<a href="' . osc_route_admin_url('osp-admin-mark', array('type' => $type, 'itemId' => $item_id, 'what' => 1, 'iPage' => Params::getParam('iPage'), 'iDisplayLength' => Params::getParam('iDisplayLength'))) . '" >' . $a[$i] . '</a>'), $list);
        } else {
          if($type <> OSP_TYPE_PUBLISH && $type <> OSP_TYPE_TOP) {
            // Unmark
            $list = array_merge(array('<a href="' . osc_route_admin_url('osp-admin-mark', array('type' => $type, 'itemId' => $item_id, 'what' => 0, 'iPage' => Params::getParam('iPage'), 'iDisplayLength' => Params::getParam('iDisplayLength'))) . '" >' . $b[$i] . '</a>'), $list);
          }
        }
      } else {
        if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE) {
          // Require
          if($fee > 0) {
            $list = array_merge(array('<a href="' . osc_route_admin_url('osp-admin-mark', array('type' => $type, 'itemId' => $item_id, 'what' => 2, 'iPage' => Params::getParam('iPage'), 'iDisplayLength' => Params::getParam('iDisplayLength'))) . '" >' . $c[$i] . '</a>'), $list);
          }
        } else {
          // Mark
          $list = array_merge(array('<a href="' . osc_route_admin_url('osp-admin-mark', array('type' => $type, 'itemId' => $item_id, 'what' => 1, 'iPage' => Params::getParam('iPage'), 'iDisplayLength' => Params::getParam('iDisplayLength'))) . '" >' . $a[$i] . '</a>'), $list);
        }
      }
    }
    
    $i++;
  }

  return $list;
}

osc_add_filter('more_actions_manage_items', 'osp_admin_item_toolbar', 5);



// USER REGISTRATION - ADD REGISTRATION CREDITS AND ASSIGN TO GROUP
function osp_user_register($user_id) {
  if(is_array($user_id)) { $user_id = $user_id['pk_i_id']; }

  $user = User::newInstance()->findByPrimaryKey($user_id);
  $group = osp_param('groups_registration');


  if($group <> '' && $group > 0) {
    osp_user_group_update($user_id, $group);
  }

  if(osp_param('wallet_enabled') == 1) {
    $credit = osp_param('wallet_registration');

    if($credit <> '' && $credit > 0) {
      osp_wallet_update($user_id, $credit);
      ModelOSP::newInstance()->saveLog(sprintf(__('Credit for registration to user %s (%s) at %s', 'osclass_pay'), $user['s_name'], ($credit . osp_currency_symbol()), osc_page_title()), 'wallet_' . date('YmdHis'), $credit, osp_currency(), $user['s_email'], $user['pk_i_id'], __('Registration bonus credits', 'osclass_pay'), OSP_TYPE_PACK, 'REGISTRATION');
      osc_add_flash_ok_message(sprintf(__('As a bonus for registration we have just boost your wallet with %s. Feel free to use them for promotion of your listings!', 'osclass_pay'), osp_format_price($credit)));
    }
  }
}

osc_add_hook('user_register_completed', 'osp_user_register');


// USER ACCOUNT LINKS
function osp_user_menu($current) {
  if(osp_param('horizontal_menu') == 1) {
    return false;
  }
  
  View::newInstance()->_exportVariableToView('user', User::newInstance()->findByPrimaryKey(osc_logged_user_id()));

  $cart = osp_cart_price();
  $count = 7;

  if(osp_param('wallet_enabled') <> 1) { $count--; }
  if(osp_param('groups_enabled') <> 1) { $count--; }
  if(osp_param('banner_allow') <> 1) { $count--; }
  if(osp_param('selling_allow') <> 1) { $count--; }

  $html  = '<div class="osp-um">';
  $html .= '<ul class="osp-um-inside elem' . $count . '">';
  $html .= '<li class="item"><a ' . ($current == 'item' ? 'class="active"' : '') . ' href="' . osc_route_url('osp-item') . '"><i class="fa fa-list"></i><span>' . __('Items', 'osclass_pay') . '</span></a></li>';

  if(osp_param('wallet_enabled') == 1) {
    $html .= '<li class="wallet"><a ' . ($current == 'pack' ? 'class="active"' : '') . ' href="' . osc_route_url('osp-pack') . '"><i class="fa fa-tags"></i><span>' . __('Wallet & Packs', 'osclass_pay') . '</span></a></li>';
  }

  if(osp_param('groups_enabled') == 1) {
    $html .= '<li class="groups"><a ' . ($current == 'group' ? 'class="active"' : '') . ' href="' . osc_route_url('osp-membership') . '"><i class="fa fa-star"></i><span>' . __('Membership', 'osclass_pay') . '</span></a></li>';
  }

  if(osp_param('banner_allow') == 1) {
    $html .= '<li class="banner"><a ' . ($current == 'banner' ? 'class="active"' : '') . ' href="' . osc_route_url('osp-banner') . '"><i class="fa fa-newspaper-o"></i><span>' . __('Banners', 'osclass_pay') . '</span></a></li>';
  }

  if(osp_param('selling_allow') == 1) {
    $html .= '<li class="order"><a ' . ($current == 'order' ? 'class="active"' : '') . ' href="' . osc_route_url('osp-order') . '"><i class="fa fa-handshake-o"></i><span>' . __('Orders & Sales', 'osclass_pay') . '</span></a></li>';
  }

  $html .= '<li class="cart"><a ' . ($current == 'cart' ? 'class="active"' : '') . ' href="' . osc_route_url('osp-cart') . '"><i class="fa fa-shopping-cart"></i><span>' . __('Cart', 'osclass_pay') . ' <em>' . osp_format_price($cart['price']) . '</em></span></a></li>';
  $html .= '<li class="payments"><a ' . ($current == 'payments' ? 'class="active"' : '') . ' href="' . osc_route_url('osp-payments', array('history' => 1)) . '"><i class="fa fa-cc-mastercard"></i><span>' . __('Payments', 'osclass_pay') . '</span></a></li>';
  $html .= '</ul>';
  $html .= '</div>';

  echo $html;
}


// GET CRYPT KEY
function osp_crypt_key() {
  return osp_param('crypt_key');
}


// ENCRYPT FUNCTION
function osp_crypt($string) {
  if(trim($string) == '') {
    return '';
  } else if(function_exists('openssl_encrypt')) {
    $encryption_key = base64_decode(osp_crypt_key());
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
    $encrypted = openssl_encrypt($string, 'AES-256-CBC', $encryption_key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
  } else if(function_exists('mcrypt_encrypt')) {
    return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, osp_crypt_key(), $string, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
  } else {
    return $string;
  }
}


// DECRYPT FUNCTION
function osp_decrypt($string){
  if(trim($string) == '') {
    return '';
  } else if(function_exists('openssl_encrypt')) {
    $encryption_key = base64_decode(osp_crypt_key());
    list($encrypted_data, $iv) = explode('::', base64_decode($string), 2);
    return openssl_decrypt($encrypted_data, 'AES-256-CBC', $encryption_key, 0, $iv);
  } else if(function_exists('mcrypt_encrypt')) {
    return str_replace("\0", "", mcrypt_decrypt(MCRYPT_RIJNDAEL_256, osp_crypt_key(), base64_decode($string), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
  } else {
    return $string;
  }
}


// FORMAT BITCOIN PAYMENT
function osp_format_btc($btc, $symbol = "BTC") {
  if($btc < 0.00001) {
    return ($btc*1000000) . 'bits';
  } else if($btc < 0.01) {
    return ($btc*1000) . 'm' . $symbol;
  }
  
  return $btc . $symbol;
}


// DEVIDE OSCLASS PRICE BY MILION
function osp_price_divide($price) {
  return $price/1000000; 
}


// JAVASCRIPT SCROLL TO DIV
function osp_js_scroll($block) { 
  ?>

  <script type="text/javascript">
    $(document).ready(function() {
      if($('<?php echo $block; ?>').length) { 
        var flash = $('.mb-head').nextAll('.flashmessage');
        flash = flash.add('#content-render > .flashmessage:not(.jsMessage)');
        flash.each(function(){
          $(this).removeAttr('style');
          $(this).removeAttr('style');
          $(this).find('a.btn').remove();
          $(this).html($(this).text().trim());

          if($(this).text() != '') {
            $('<?php echo $block; ?>').before($(this).wrap('<div/>').parent().html());
            $(this).hide(0);
          }
        });

        var flashCount = 0;

        if(flash.length > 0) {
          flashCount = flash.length;
        }

        $('html,body').animate({scrollTop: $('<?php echo $block; ?>').offset().top - 70 - parseInt(flashCount*64)}, 0);

      }
    });
  </script>

  <?php
}


// BLOCK CATEGORIES AND THEIR ITEMS RESTRICTED TO SPECIFIED USER GROUPS
function osp_category_restrict() {
  $check = osp_category_restrict_check();
  $block = isset($check[0]) ? $check[0] : '';
  $category = isset($check[1]) ? $check[1] : '';
  $array = isset($check[2]) ? $check[2] : array();


  if(osc_is_admin_user_logged_in() && count($array) > 0) {
    $collect = '';
    $j = 1;

    if(count($array) > 0) {
      foreach($array as $a) {
        if($j > 1) {
          $collect .= ', ';
        }

        $collect .=  '<strong>' . $a['group_name'] . ' (#' . $a['group_id'] . ')</strong>';
        $j++;
      }
    }

    osc_add_flash_error_message(sprintf(__('Important! Osclass Pay plugin has restricted this category and it\'s content to members of following user groups: %s. You see this message because you are logged in as admin in same browser. This message is not visible to your customers.', 'osclass_pay'), $collect));
  } else if ($block) {
    osp_redirect(osc_route_url('osp-restrict', array('category' => $category)));
  }
}

osc_add_hook('header', 'osp_category_restrict');


// CATEGORY CHECK FOR RESTRICTIONS
function osp_category_restrict_check($category_id = '') {
  $user_id = osc_logged_user_id();
  $user_group = ModelOSP::newInstance()->getUserGroup($user_id);
  $current = $category_id;
  $restriction_enabled = osp_param('groups_category');

  if($restriction_enabled == 1 && (osc_is_search_page() || osc_is_ad_page() || ($category_id <> '' && osc_get_osclass_location() == 'custom'))) {
    if(osc_is_search_page() && osc_get_osclass_location() <> 'custom') {
      $current = osc_search_category_id();
      $current = (@$current[0] > 0 ? $current[0] : Params::getParam('sCategory'));
    }

    if(osc_is_ad_page() && osc_get_osclass_location() <> 'custom') {
      $current = osc_item_category_id();
    }


    $array = array();
    
    if($current <> '' && $current > 0) {
      $groups = ModelOSP::newInstance()->getGroups();

      if(count($groups) > 0) {
        foreach($groups as $g) {
          if(trim($g['s_category']) <> '') {
            $cats = explode(',', $g['s_category']);

            if(count($cats) > 0) {
              foreach($cats as $c) {
                if($current == $c) {
                  $array[] = array('category_id' => $c, 'group_id' => $g['pk_i_id'], 'group_name' => $g['s_name'], 'group_array' => $g);
                }
              }
            }
          }

        }
      }
    }

    $block = true;

    if(count($array) > 0) {
      foreach($array as $a) {
        if($user_group == $a['group_id']) {
          $block = false;
        }
      }
    } else {
      $block = false;
    }


    return array($block, $current, $array);
  }

  return array(false);
}


// GET USER GROUP DISCOUNT
function osp_user_group_discount($user_id = -1) {
  if($user_id == -1) {
    $user_id = osc_logged_user_id();
  }


  if($user_id > 0) {
    $group = ModelOSP::newInstance()->getUserGroup($user_id);

    if($group > 0) {
      $group_full = ModelOSP::newInstance()->getGroup($group);
      $percentage = (isset($group_full['i_discount']) ? $group_full['i_discount'] : 0)/100;
    } else {
      $percentage = 0;
    }
    
    return $percentage;
  } else {
    return 0;
  }
}



// GET USER GROUP
// Returns ID of group, not group record
function osp_get_user_group($user_id = -1) {
  if($user_id == -1) {
    $user_id = osc_logged_user_id();
  }

  if($user_id > 0) {
    $group = ModelOSP::newInstance()->getUserGroup($user_id);
    return $group;
  }

  return 0;
}

// GET USER GROUP LIMIT
function osp_get_user_group_limit($user_id = -1, $type = OSP_TYPE_PUBLISH) {
  if($user_id == -1) {
    $user_id = osc_logged_user_id();
  }

  if($user_id > 0) {
    $group_id = ModelOSP::newInstance()->getUserGroup($user_id);
    
    if($group_id > 0) {
      $group = ModelOSP::newInstance()->getGroup($group_id);

      if(isset($group['i_free_items_' . $type]) && $group['i_free_items_' . $type] > 0) {
        return (int)$group['i_free_items_' . $type];
      }
    }
  }

  return 0;
}

// GET USER GROUP REMAINING (FREE) LIMIT
function osp_get_user_group_remaining_limit($user_id = -1, $type = OSP_TYPE_PUBLISH) {
  if($user_id == -1) {
    $user_id = osc_logged_user_id();
  }
  
  
  $free_limit = (int)osp_get_user_group_limit($user_id, $type);
  $user_active_items = (int)ModelOSP::newInstance()->countUserItems($user_id);

  if($free_limit > 0) {
    $remaining_limit = ($free_limit - $user_active_items);
    
    if($remaining_limit > 0) {
      return (int)$remaining_limit;
    }
  }
  
  return 0;
}


// UPDATE USER IN GROUP
function osp_user_group_update($user_id, $group_id, $expire = NULL) {
  ModelOSP::newInstance()->updateUserGroup($user_id, $group_id, $expire);  
}


// LIST OF AVAILABLE DURATION HOURS
function osp_available_duration($is_republish = false) {
  if($is_republish) {  // not activated yet
    return '0.0833,0.1666,0.25,0.333,0.5,0.75,1,2,3,4,6,12,24,48,72,168,360,720,1464,2184,4368,8760';
  }
  
  return '1,2,3,4,6,12,24,48,72,168,360,720,1464,2184,4368,8760';
}


// LIST OF AVAILABLE REPEATS
function osp_available_repeat() {
  return '1,2,3,4,5,10,20,30,50,100,200,500';
}


// NAME OF HOURS
function osp_duration_name($hours) {
  if ($hours <= 0) {
    return sprintf(__('%d hours', 'osclass_pay'), round($hours));   // in case of negative
  } else if($hours > 0 && $hours < 1) {  // minutes
    $minutes = round($hours*60);
    return sprintf(__('%d minutes', 'osclass_pay'), $minutes);
  } else if($hours == 1) {
    return sprintf(__('%d hour', 'osclass_pay'), round($hours));
  } else if($hours < 24) {
    return sprintf(__('%d hours', 'osclass_pay'), round($hours));
  } else if($hours == 24) {
    return sprintf(__('%d day', 'osclass_pay'), round($hours/24));
  } else if($hours < 168) {
    return sprintf(__('%d days', 'osclass_pay'), round($hours/24));
  } else if($hours == 168) {
    return sprintf(__('%d week', 'osclass_pay'), round($hours/24/7));
  } else if($hours == 360) {
    return sprintf(__('%d weeks', 'osclass_pay'), round($hours/24/7));
  } else if($hours == 720) {
    return sprintf(__('%d month', 'osclass_pay'), round($hours/24/30));
  } else if($hours < 8760) {
    return sprintf(__('%d months', 'osclass_pay'), round($hours/24/30));
  } else if($hours == 8760) {
    return sprintf(__('%d year', 'osclass_pay'), round($hours/24/365));
  } else {
    return sprintf(__('%d years', 'osclass_pay'), round($hours/24/365));
  }
}


// GET FEE TO BE PAID
function osp_get_fee($type, $quantity, $item_id = NULL, $hours = NULL, $repeat = NULL) {
  $fee = 0;
  $quantity = ($quantity > 0 ? $quantity : 1);
  $discount = osp_user_group_discount();
  $hours = (float)$hours;
  $repeat = (float)$repeat;
  
  if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE ||  $type == OSP_TYPE_TOP || $type == OSP_TYPE_PACK) {
    $hours = null;
    $repeat = null;
  }

  if($type == OSP_TYPE_PREMIUM || $type == OSP_TYPE_HIGHLIGHT || $type == OSP_TYPE_MEMBERSHIP) {
    $repeat = null;
  }


  // ITEM FEES
  if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_PREMIUM || $type == OSP_TYPE_HIGHLIGHT || $type == OSP_TYPE_IMAGE || $type == OSP_TYPE_REPUBLISH || $type == OSP_TYPE_TOP) {
    if(osp_fee_is_allowed($type) && $item_id <> '' && $item_id > 0) {
      $item = Item::newInstance()->findByPrimaryKey($item_id);

      if(isset($item['pk_i_id'])) {
        $repeat_discount = (float)osp_param('republish_repeat_discount')/100;
        $fee = ModelOSP::newInstance()->getFee($type, $item['fk_i_category_id'], $item['fk_c_country_code'], $item['fk_i_region_id'], $hours);

        if($repeat == '' || $repeat <= 0) {
          $repeat = 1;
        }

        if($repeat_discount == '' || $repeat_discount > 1 || $repeat_discount < 0) {
          $repeat_discount = 1;
        } else {
          $repeat_discount = 1 - $repeat_discount;
        }
        
        if($hours <> '' && $hours > 0) {
          if($type == OSP_TYPE_REPUBLISH && $repeat > 1) {
            // if there is 5% repeat discount (0.05) and repeat times is set to 3, then final discount is (1 - 0.95^3) = (1 - 0.857) = 14%
            $fee = $fee * $repeat *(pow($repeat_discount, ($repeat - 1)));
          }
        }
      }
    }

  } else if($type == OSP_TYPE_PACK) {
    $pack = ModelOSP::newInstance()->getPack($item_id);
    $fee = $pack['f_price'];

  } else if($type == OSP_TYPE_MEMBERSHIP) {
    $group = ModelOSP::newInstance()->getGroup($item_id);
    $fee = $group['f_price'] * intval($hours / ($group['i_days'] > 0 ? $group['i_days'] : 30));

  } else if($type == OSP_TYPE_BANNER) {
    $banner = ModelOSP::newInstance()->getBanner($item_id);
    $fee = $banner['d_budget'];
    
  } else if($type == OSP_TYPE_SHIPPING) {
    $shipping = ModelOSP::newInstance()->getShipping($item_id);
    $fee = isset($shipping['f_fee']) ? $shipping['f_fee'] : 0;
    
    if(!isset($shipping['f_fee'])) {
      $fee = (osp_param('default_shipping') > 0 ? osp_param('default_shipping') : 0);
    }
    
    if(osp_param('enable_shipping') != 1) {
      $fee = 0;
    }
    
  } else if($type == OSP_TYPE_PRODUCT) {
    $item = Item::newInstance()->findByPrimaryKey($item_id);
    
    if($item === false || !isset($item['pk_i_id'])) {
      $fee = 0;
    } else {
      $fee = round(osp_convert($item['i_price']/1000000, $item['fk_c_currency_code']), 2);
    }
    
  } else if($type == OSP_TYPE_VOUCHER) {
    $voucher = ModelOSP::newInstance()->getVoucher($item_id);
    $cart = osp_cart_price(osc_logged_user_id(), 1); 
    $cart_amount = (isset($cart['price']) ? $cart['price'] : 0);

    if($voucher['s_type'] == 'AMOUNT') {
      $fee = -($voucher['d_amount'] > $cart_amount ? $cart_amount : $voucher['d_amount']);

    } else if($voucher['s_type'] == 'PERCENT') {
      $fee = -$cart_amount*($voucher['d_amount']/100);

    }

  } else if($type == OSP_TYPE_BOOKING) {
    $reservation = ModelOSP::newInstance()->getBooking($item_id);
    $fee = round(osp_convert($reservation['d_price'], $reservation['fk_c_currency_code']), 2);

  } else if($type == OSP_TYPE_AUCTION_PAY) {
    $auction = ModelOSP::newInstance()->getAuction($item_id);
    
    if($auction['b_enabled'] == 1 && $auction['b_canceled'] == 0 && $auction['b_finished'] == 1 && $auction['b_accepted'] == 1 && $auction['b_paid'] == 0 && $auction['fk_i_winner_user_id'] == osc_logged_user_id()) {
      $fee = round(osp_convert($auction['f_winning_amount'] + $auction['f_shipping_cost'], $auction['fk_c_currency_code']), 2);
    }
    
  } else if($type == OSP_TYPE_AUCTION_BUYOUT) {
    $auction = ModelOSP::newInstance()->getAuction($item_id);

    if($auction['b_enabled'] == 1 && $auction['b_canceled'] == 0 && $auction['b_finished'] == 0 && $auction['b_accepted'] != 1 && $auction['b_paid'] == 0) {
      $fee = round(osp_convert($auction['f_buyout_amount'] + $auction['f_shipping_cost'], $auction['fk_c_currency_code']), 2);
    }
    
  } else if($type == OSP_TYPE_AUCTION_FEE) {
    $auction = ModelOSP::newInstance()->getAuction($item_id);
    
    if($auction['b_enabled'] == 1 && $auction['b_canceled'] == 0 && $auction['b_fee_paid'] == 0) {
      $fee = round(osp_convert($auction['f_fee'], osc_get_preference('default_currency_code','plugin-auction')), 2);
    }
    
  } else if($type == OSP_TYPE_AUCTION_BID_FEE) {
    $auction = ModelOSP::newInstance()->getAuction($item_id);
    
    if($auction['b_enabled'] == 1 && $auction['b_canceled'] == 0 && $auction['b_finished'] == 1 && $auction['b_bid_fee_paid'] == 0) {
      $fee = round(osp_convert($auction['f_bid_fee'], $auction['fk_c_currency_code']), 2);
    }
    
  } else if($type == OSP_TYPE_OFFER) {
    
    $offer = ModelMO::newInstance()->getOfferById($item_id);
    
    if(isset($offer['fk_i_item_id'])) {
      $item = Item::newInstance()->findByPrimaryKey($offer['fk_i_item_id']);
      
      if(isset($item['pk_i_id']) && $offer['i_status'] == 1 && $offer['i_user_id'] == osc_logged_user_id()) {
        $qty = (int)($offer['i_quantity'] > 1 ? $offer['i_quantity'] : 1);      // offer price is for overall quantity, not per 1 piece
        $fee = round(osp_convert(($offer['i_price'] / $qty)/1000000, $item['fk_c_currency_code']), 2);
      }
    }
  }
  
  
  // APPLY QUANTITY
  $fee = $fee * $quantity;

  // APPLY MEMBERSHIP DISCOUNT
  if(!in_array($type, array(OSP_TYPE_PACK, OSP_TYPE_MEMBERSHIP, OSP_TYPE_VOUCHER, OSP_TYPE_BOOKING, OSP_TYPE_OFFER, OSP_TYPE_SHIPPING, OSP_TYPE_AUCTION_PAY, OSP_TYPE_AUCTION_BUYOUT))) {   // group discount cannot be applied on packs and membership (would lead to double discounts)
    if(($type == OSP_TYPE_PRODUCT && osp_param('selling_apply_membership') == 1) || $type <> OSP_TYPE_PRODUCT) {
      if($discount > 0 && $discount <= 1) {
        $fee = $fee*(1-$discount);
      }
    }
  }


  return $fee;
}


// GET FEE RECORD
function osp_get_fee_record($type, $item_id, $paid) {
  return ModelOSP::newInstance()->getItem($type, $item_id, $paid);
}


// GET CURRENCY FOR PAYMENT
function osp_currency() {
  return osp_param('currency');
}


// CHECK IF FEE IS PAID
function osp_fee_is_paid($type, $item_id = 0) {
  if($item_id > 0 && $item_id <> '') {
    if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_PREMIUM || $type == OSP_TYPE_HIGHLIGHT || $type == OSP_TYPE_IMAGE || $type == OSP_TYPE_REPUBLISH || $type == OSP_TYPE_TOP) {
      return ModelOSP::newInstance()->feeIsPaid($type, $item_id);
    } else {
      return false;
    } 
  }

  return false;  
}


// CHECK IF FEE EXISTS
function osp_fee_exists($type, $item_id, $paid = -1) {
  if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_PREMIUM || $type == OSP_TYPE_HIGHLIGHT || $type == OSP_TYPE_IMAGE || $type == OSP_TYPE_REPUBLISH || $type == OSP_TYPE_TOP) {
    return ModelOSP::newInstance()->feeExists($type, $item_id, $paid);
  } else {
    return false;
  }
}


// SPECIAL CHECK FOR PROMOTE FORM
function osp_fee_is_paid_special($type, $item_id = 0) {
  if($item_id > 0 && $item_id <> '') {
    if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE) {
      if(osp_fee_exists($type, $item_id) && osp_fee_is_paid(OSP_TYPE_PUBLISH, $item_id)) {
        return true;
      } else if (!osp_fee_exists($type, $item_id)) {
        return true;
      }
    }
  }

  return false;
}


// CHECK IF FEE IS ALLOWED
function osp_fee_is_allowed($type) {
  $allowed = false;
  
  if($type == OSP_TYPE_PUBLISH) {
    $allowed = osp_param('publish_allow');
  } else if($type == OSP_TYPE_PREMIUM) {
    $allowed = osp_param('premium_allow');
  } else if($type == OSP_TYPE_HIGHLIGHT) {
    $allowed = osp_param('highlight_allow');
  } else if($type == OSP_TYPE_IMAGE) {
    $allowed = osp_param('image_allow');
  } else if($type == OSP_TYPE_REPUBLISH) {
    $allowed = osp_param('republish_allow');
  } else if($type == OSP_TYPE_TOP) {
    $allowed = osp_param('movetotop_allow');
  }
  
  return $allowed;
}


// CREATE CARD CONTENT NAME FOR PAYMENT GATEWAYS
function osp_cart_content_name($user_id = NULL) {
  if($user_id === NULL) {
    $user_id = osc_logged_user_id();
  }
  
  if($user_id <= 0) {
    return __('Promotion services', 'osclass_pay');
  }
  
  // $cart_string = ModelOSP::newInstance()->getCart($user_id);
  $cart = osp_cart_content($user_id);
  $output = '';
  $output_arr = array();
  $count_arr = array();
  
  if(is_array($cart) && count($cart) > 0) {
    // Get qty for each type first
    foreach($cart as $c) {
      $type = $c[1];
      $qty = (int)$c[2];

      if(isset($count_arr[$type])) {
        $count_arr[$type] += $qty;
      } else {
        $count_arr[$type] = $qty;
      }
    }
    
    // Now create texts
    foreach($count_arr as $type => $qty) {
      $output_arr[] = $qty . 'x ' . osp_product_type_name($type);
    }
  }
  
  $output = trim(implode(', ', $output_arr));
  
  if($output == '') { 
    return __('Promotion services', 'osclass_pay');
  }

  return $output; 
}


// GET PAYMENT TYPE NAME
function osp_product_type_name($type) {
  if($type == OSP_TYPE_PUBLISH) {
    return __('Publish fee', 'osclass_pay');
  } else if($type == OSP_TYPE_PREMIUM) {
    return __('Make Premium', 'osclass_pay');
  } else if($type == OSP_TYPE_HIGHLIGHT) {
    return __('Highlight', 'osclass_pay');
  } else if($type == OSP_TYPE_IMAGE) {
    return __('Show Image', 'osclass_pay');
  } else if($type == OSP_TYPE_PACK) {
    return __('Credit Pack', 'osclass_pay');
  } else if($type == OSP_TYPE_REPUBLISH) {
    return __('Republish', 'osclass_pay');
  } else if($type == OSP_TYPE_MEMBERSHIP) {
    return __('Membership', 'osclass_pay');
  } else if($type == OSP_TYPE_MULTIPLE) {
    return __('Pay Items', 'osclass_pay');
  } else if($type == OSP_TYPE_REPUBLISH) {
    return __('Republish', 'osclass_pay');
  } else if($type == OSP_TYPE_TOP) {
    return __('Move to Top', 'osclass_pay');
  } else if($type == OSP_TYPE_BANNER) {
    return __('Banner', 'osclass_pay');
  } else if($type == OSP_TYPE_SHIPPING) {
    return __('Shipping', 'osclass_pay');
  } else if($type == OSP_TYPE_PRODUCT) {
    return __('Product', 'osclass_pay');
  } else if($type == OSP_TYPE_BOOKING) {
    return __('Pay booking', 'osclass_pay');
  } else if($type == OSP_TYPE_AUCTION_PAY) {
    return __('Auction', 'osclass_pay');
  } else if($type == OSP_TYPE_AUCTION_BUYOUT) {
    return __('Auction', 'osclass_pay');
  } else if($type == OSP_TYPE_AUCTION_FEE) {
    return __('Auction', 'osclass_pay');
  } else if($type == OSP_TYPE_AUCTION_BID_FEE) {
    return __('Auction', 'osclass_pay');
  } else if($type == OSP_TYPE_VOUCHER) {
    return __('Voucher', 'osclass_pay');
  } else if($type == OSP_TYPE_OFFER) {
    return __('Offer', 'osclass_pay');
  } 
  
  return __('Unknown', 'osclass_pay') . ' (' . $type . ')';
}


// GET FEE DURATION LIST
function osp_fee_duration($type) {
  if($type == OSP_TYPE_PREMIUM) {
    $h = osp_param('premium_duration');
  } else if($type == OSP_TYPE_HIGHLIGHT) {
    $h = osp_param('highlight_duration');
  } else if($type == OSP_TYPE_REPUBLISH) {
    $h = osp_param('republish_duration');
  } 

  if($h <> '') {
    return explode(',', $h);
  } 

  return false;
}


// GET CATEGORY DEFAULT FEE
function osp_category_default_fee($type, $hours = NULL) {
  $fee = 0;

  if($type == OSP_TYPE_PUBLISH) {
    $fee = osp_param('publish_fee');
  } else if($type == OSP_TYPE_PREMIUM) {
    $fee = osp_param('premium_fee');
  } else if($type == OSP_TYPE_HIGHLIGHT) {
    $fee = osp_param('highlight_fee');
  } else if($type == OSP_TYPE_IMAGE) {
    $fee = osp_param('image_fee');
  } else if($type == OSP_TYPE_REPUBLISH) {
    $fee = osp_param('republish_fee');
  } else if($type == OSP_TYPE_TOP) {
    $fee = osp_param('movetotop_fee');
  }

  $fee = osp_hours_uplift($fee, $hours);
  
  return floatval($fee > 0 ? $fee : 0);
}


// HOURS UPLIFT FOR CALCULATION OF DEFAULT 24H FEE TO DIFFERENT HOURS
function osp_hours_uplift($fee, $hours = NULL) {
  $fee = floatval($fee);
  $price = $fee;

  if($hours <> '' && $hours > 0) {
    $days = $hours / 24;

    if(osp_param('def_price_calc') == 'LINEAR') {
      $price = $fee * $days;
    } else {
      if($days < 1) {
        $price = $fee * pow($days, 1/4);
      } else if($days > 1) {
        $price = $fee * pow($days, 1/2);
      } else {
        $price = $fee;
      }
    }
  }

  return $price;
}


// PAY FEE
function osp_pay_fee($details) {
  $type = @$details['type'];
  $multiple_type = @$details['multiple_type'];
  $quantity = @$details['quantity'];
  $item_id = @$details['item_id'];
  $user_id = @$details['user_id'];
  $user_id = ($user_id > 0 ? $user_id : osc_logged_user_id());
  $payment_id = @$details['payment_id'];

  $hours = @$details['hours'];
  $minutes = ($hours > 0 ? round($hours*60) : 0);
  $repeat = @$details['repeat'];
  $pack_user_id = @$details['pack_user_id'];
  $pack_value = @$details['pack_value'];
  $group_user_id = @$details['group_user_id'];
  $group_id = @$details['group_id'];
  $product_id = @$details['product_id'];
  $group_days = @$details['group_days'];
  $banner_id = @$details['banner_id'];
  $voucher_id = @$details['voucher_id'];
  $reservation_id = @$details['reservation_id'];
  $auction_id = @$details['auction_id'];
  $offer_id = @$details['offer_id'];
  

  if($type == OSP_TYPE_PUBLISH) {
    osp_item_active($item_id, 1);
    return ModelOSP::newInstance()->payFee($type, $item_id, $payment_id);

  } else if($type == OSP_TYPE_IMAGE) {
    return ModelOSP::newInstance()->payFee($type, $item_id, $payment_id);

  } else if($type == OSP_TYPE_TOP) {
    Item::newInstance()->dao->query(sprintf('UPDATE %st_item SET dt_pub_date = "%s" WHERE pk_i_id = %d', DB_TABLE_PREFIX, date('Y-m-d H:i:s'), $item_id));
    Item::newInstance()->dao->query(sprintf('UPDATE %st_item SET dt_mod_date = NULL WHERE pk_i_id = %d', DB_TABLE_PREFIX, $item_id));
    return ModelOSP::newInstance()->payFee($type, $item_id, $payment_id);

  } else if($type == OSP_TYPE_PREMIUM) {
    Item::newInstance()->dao->query(sprintf('UPDATE %st_item SET b_premium = %d WHERE pk_i_id = %d', DB_TABLE_PREFIX, 1, $item_id));

    $curr_date = date('Y-m-d H:i:s');
    $expire = date('Y-m-d H:i:s', strtotime(" + " . $minutes . " minutes", strtotime($curr_date)));

    return ModelOSP::newInstance()->payFee($type, $item_id, $payment_id, $expire, $hours);

  } else if($type == OSP_TYPE_HIGHLIGHT) {
    $curr_date = date('Y-m-d H:i:s');
    $expire = date('Y-m-d H:i:s', strtotime(" + " . $minutes . " minutes", strtotime($curr_date)));

    return ModelOSP::newInstance()->payFee($type, $item_id, $payment_id, $expire, $hours);

  } else if($type == OSP_TYPE_REPUBLISH) {
    $curr_date = date('Y-m-d H:i:s');
    $expire = date('Y-m-d H:i:s', strtotime(" + " . $minutes . " minutes", strtotime($curr_date)));
    Item::newInstance()->dao->query(sprintf('UPDATE %st_item SET dt_pub_date = NOW() WHERE pk_i_id = %d', DB_TABLE_PREFIX, $item_id));

    return ModelOSP::newInstance()->payFee($type, $item_id, $payment_id, $expire, $hours, $repeat);
    
  } else if($type == OSP_TYPE_PACK) {
    if($quantity > 1) {
      $pack_value = $pack_value * $quantity;
    }

    osp_wallet_update($pack_user_id, $pack_value);
    return ModelOSP::newInstance()->payFee($type, -1, $payment_id);

  } else if($type == OSP_TYPE_MEMBERSHIP) {
    $start_date = date('Y-m-d H:i:s');

    if($quantity > 1) {
      $group_days = $group_days * $quantity;
    }

    $user_group = ModelOSP::newInstance()->getUserGroupRecord($group_user_id);
    $pay_group = ModelOSP::newInstance()->getGroup($group_id);

    // new user group is lower or same current one, transfer remaining days to new
    if(@$user_group['fk_i_group_id'] > 0 && $pay_group['i_rank'] <= @$user_group['i_rank'] && strtotime($user_group['dt_expire']) >= strtotime($start_date)) {
      $start_date = date('Y-m-d H:i:s', strtotime($user_group['dt_expire']));

      if(@$user_group['i_rank'] > 0) {
        osc_add_flash_ok_message(__('You have selected same or lower membership group, your remaining expiration days of group were transfered.', 'osclass_pay'));
      } else {
        osc_add_flash_ok_message(__('Your remaining expiration days of group were transfered.', 'osclass_pay'));
      }
    }

 
    // new user group is higher than current one, do not transfer remaining days
    if(@$user_group['fk_i_group_id'] > 0 && $pay_group['i_rank'] > @$user_group['i_rank'] && strtotime($group['dt_expire']) > $start_date && @$pay_group['i_rank'] > 0) {
      osc_add_flash_ok_message(__('You have selected same or higher membership group, we have moved remaining expiration days to your new group.', 'osclass_pay'));
    }

    $expire = date('Y-m-d H:i:s', strtotime(" + " . $group_days . " days", strtotime($start_date)));

    osp_user_group_update($group_user_id, $group_id, $expire);
    return ModelOSP::newInstance()->payFee($type, -1, $payment_id, $expire);

  } else if($type == OSP_TYPE_BANNER) {

    if(osp_plugin_ready('banner_ads')) {
      $b = ModelOSP::newInstance()->getBanner($banner_id);
      $advert_id = ModelBA::newInstance()->insertAdvert($b['i_type'], $b['fk_s_banner_id'], $b['s_name'], $b['s_key'], $b['s_url'], $b['s_code'], $b['d_price_view'], $b['d_price_click'], $b['d_budget'], '2099-01-01', $b['s_category'], $b['s_size_width'], $b['s_size_height']);
      ModelOSP::newInstance()->updateBannerAdvertId($banner_id, $advert_id);
    }

    ModelOSP::newInstance()->updateBannerStatus($banner_id, 2);
    return ModelOSP::newInstance()->payFee($type, -1, $payment_id);

  } else if($type == OSP_TYPE_SHIPPING) {
    // Do nothing, no action for shipping
    return true;

  } else if($type == OSP_TYPE_VOUCHER) {
    ModelOSP::newInstance()->updateVoucherUsage($voucher_id, 1);
    return true;
    
  } else if($type == OSP_TYPE_BOOKING) {
    ModelOSP::newInstance()->updateBookingPaid($reservation_id);
    
    if(function_exists('bkg_email_reservation_paid')) {
      bkg_email_reservation_paid($reservation_id);
      bkg_email_reservation_paid_admin($reservation_id);
    }
    
    osp_cart_drop($user_id);
    return true;

  } else if($type == OSP_TYPE_AUCTION_PAY) {
    //ModelOSP::newInstance()->updateAuction($auction_id, array('b_paid' => 1, 'fk_i_payment_id' => $payment_id));
    $log = ModelOSP::newInstance()->getPayment($payment_id);
    osp_cart_drop($user_id);
    
    osc_run_hook('act_auction_paid', $auction_id, $payment_id, (isset($log['fk_i_user_id']) ? $log['fk_i_user_id'] : $user_id));
    return true;
    
  } else if($type == OSP_TYPE_AUCTION_BUYOUT) {
    //ModelOSP::newInstance()->updateAuction($auction_id, array('b_paid' => 1, 'b_paid_buyout' => 1, 'b_accepted' => 1, 'fk_i_payment_id' => $payment_id));
    $log = ModelOSP::newInstance()->getPayment($payment_id);
    osp_cart_drop($user_id);
    
    osc_run_hook('act_auction_buyout_paid', $auction_id, $payment_id, (isset($log['fk_i_user_id']) ? $log['fk_i_user_id'] : $user_id));
    return true;
    
  } else if($type == OSP_TYPE_AUCTION_FEE) {
    //ModelOSP::newInstance()->updateAuction($auction_id, array('b_fee_paid' => 1, 'fk_i_fee_payment_id' => $payment_id));
    $log = ModelOSP::newInstance()->getPayment($payment_id);
    osp_cart_drop($user_id);
    
    osc_run_hook('act_auction_fee_paid', $auction_id, $payment_id, (isset($log['fk_i_user_id']) ? $log['fk_i_user_id'] : $user_id));
    return true;
    
  } else if($type == OSP_TYPE_AUCTION_BID_FEE) {
    //ModelOSP::newInstance()->updateAuction($auction_id, array('b_bid_fee_paid' => 1, 'fk_i_bid_fee_payment_id' => $payment_id));
    $log = ModelOSP::newInstance()->getPayment($payment_id);
    osp_cart_drop($user_id);
    
    osc_run_hook('act_auction_bid_fee_paid', $auction_id, $payment_id, (isset($log['fk_i_user_id']) ? $log['fk_i_user_id'] : $user_id));
    return true;


  } else if($type == OSP_TYPE_PRODUCT || $type == OSP_TYPE_OFFER) {

    // Pay fee for product
    ModelOSP::newInstance()->payFee(OSP_TYPE_PRODUCT, -1, $payment_id);
    
    // Identify product id = item id
    if($type == OSP_TYPE_OFFER) {
      $offer = ModelMO::newInstance()->getOfferById($offer_id);
      
      if(!isset($offer['i_paid'])) {
        osc_add_flash_error_message(sprintf(__('Invalid offer! Offer #%d does not exists.', 'osclass_pay'), $offer_id));
        return false;
        
      } else if($offer['i_paid'] == 1) {
        osc_add_flash_error_message(sprintf(__('Offer #%d has already been paid.', 'osclass_pay'), $offer_id));
        return false;
        
      } else {
        $product_id = $offer['fk_i_item_id']; 
      }
    }
    
    
    // Check if there is enough quantity
    if(osp_param('stock_management') == 1) {
      $item = Item::newInstance()->findByPrimaryKey($product_id);
      $item_data = ModelOSP::newInstance()->getItemData($product_id);
      $avl_quantity = isset($item_data['i_quantity']) ? $item_data['i_quantity'] : 0;

      // Show worning just for standard 
      if($quantity > $avl_quantity && $type == OSP_TYPE_PRODUCT) {
        osc_add_flash_warning_message(sprintf(__('Insufficient quantity on stock for product %s! We have %s items on stock, you have requested %s. Our team may contact you.', 'osclass_pay'), '<strong>' . @$item['s_title'] . '</strong>', $avl_quantity, $quantity));
      }
    }

    $update_qty = (osp_param('stock_management') == 0 ? 0 : $quantity);
    ModelOSP::newInstance()->updateItemQuantity($product_id, -$update_qty);

    // Create order record at the end
    $order_id = ModelOSP::newInstance()->createOrder($payment_id);
    
    // Do offer-specific actions
    if($type == OSP_TYPE_OFFER) {
      ModelMO::newInstance()->payOfferById($offer_id);

      $log = ModelOSP::newInstance()->getPayment($payment_id);
      osc_run_hook('mo_offer_paid', $offer_id, $payment_id, (isset($log['fk_i_user_id']) ? $log['fk_i_user_id'] : $user_id));
    }
    
    osp_email_order($order_id, 1);


  } else if($type == OSP_TYPE_MULTIPLE) {

    if($multiple_type == 1) { //cart
      $cart_string = ModelOSP::newInstance()->getCart($user_id);
      $cart = osp_cart_content($user_id);

      foreach($cart as $c) {
        $type = $c[1];
        $item = array('type' => $c[1], 'quantity' => $c[2], 'item_id' => $c[3], 'payment_id' => $payment_id);

        if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE || $type == OSP_TYPE_PREMIUM || $type == OSP_TYPE_HIGHLIGHT || $type == OSP_TYPE_REPUBLISH || $type == OSP_TYPE_TOP) {
          $item = array_merge($item, array('hours' => @$c[4], 'repeat' => @$c[5]));

        } else if($type == OSP_TYPE_PACK) {
          $item = array_merge($item, array('pack_id' => $c[3], 'pack_value' => $c[4], 'pack_user_id' => $user_id));

        } else if($type == OSP_TYPE_MEMBERSHIP) {
          $item = array_merge($item, array('group_id' => $c[3], 'group_days' => $c[4], 'group_user_id' => $user_id));

        } else if($type == OSP_TYPE_BANNER) {
          $item = array_merge($item, array('banner_id' => $c[3], 'banner_budget' => $c[4]));

        } else if($type == OSP_TYPE_SHIPPING) {
          $item = array_merge($item, array('shipping_id' => $c[3], 'shipping_fee' => $c[4]));
          
        } else if($type == OSP_TYPE_VOUCHER) {
          $item = array_merge($item, array('voucher_id' => $c[3]));

        } else if($type == OSP_TYPE_PRODUCT) {
          $item = array_merge($item, array('product_id' => $c[3]));

        } else if($type == OSP_TYPE_BOOKING) {
          $item = array_merge($item, array('reservation_id' => $c[3]));
          
        } else if($type == OSP_TYPE_AUCTION_PAY || $type == OSP_TYPE_AUCTION_BUYOUT || $type == OSP_TYPE_AUCTION_FEE || $type == OSP_TYPE_AUCTION_BID_FEE) {
          $item = array_merge($item, array('auction_id' => $c[3]));

        } else if($type == OSP_TYPE_OFFER) {
          $item = array_merge($item, array('offer_id' => $c[3]));

        }


        $cart = array($c[1], $c[2], $c[3], @$c[4], @$c[5]);
        $cart = array_filter($cart);
        $content = implode('x', $cart);
  
        //osp_cart_remove($user_id, $content);
        osp_pay_fee($item);

      }

    } else if ($multiple_type == 2) { // items promotion
      $types = array(OSP_TYPE_PUBLISH, OSP_TYPE_IMAGE, OSP_TYPE_TOP, OSP_TYPE_PREMIUM, OSP_TYPE_HIGHLIGHT, OSP_TYPE_REPUBLISH);

      foreach($types as $type) {
        if(osp_fee_is_allowed($type) && osp_fee_exists($type, $item_id) && !osp_fee_is_paid($type, $item_id)) {
          $record = osp_get_fee_record($type, $item_id, 0);
          $item = array('type' => $type, 'quantity' => 1, 'payment_id' => $payment_id, 'item_id' => $item_id);

          if(isset($record['i_hours']) && $record['i_hours'] <> '' && $record['i_hours'] > 0) {
            $item = array_merge($item, array('hours' => $record['i_hours']));
          }

          if(isset($record['i_repeat']) && $record['i_repeat'] <> '' && $record['i_repeat'] > 0) {
            $item = array_merge($item, array('repeat' => $record['i_repeat']));
          }

          $cart = array($type, 1, $item_id, @$record['i_hours'], @$record['i_repeat']);
          $cart = array_filter($cart);
          $content = implode('x', $cart);

          //osp_cart_remove($user_id, $content);   // remove paid item from cart, even it was paid outside cart
          osp_pay_fee($item);
        }
      }
    }
  } else {
    return false;
  }

  osp_cart_drop($user_id);
}


// REDIRECT AFTER PAYMENT (used in wallet)
function osp_pay_url_redirect($product = NULL) {
  $type = isset($product[0]) ? $product[0] : '';
  $multiple_type = isset($product[1]) ? $product[1] : '';
  $item_id = isset($product[2]) ? $product[2] : '';

  if($type == OSP_TYPE_MULTIPLE) {
    if($multiple_type == 1) { 
      if(osc_is_web_user_logged_in()) {
        return osc_route_url('osp-cart');
      } else {  
        return osc_base_url();
      }
      
    } else if($multiple_type == 2 && $item_id <> '' && $item_id > 0) { 
      return osc_route_url('osp-item-pay', array('itemId' => $item_id));
    }
  }

  if($item_id <> '' && $item_id > 0) {
    $item = Item::newInstance()->findByPrimaryKey($item_id);
    
    if($item !== false && isset($item['pk_i_id'])) {
      //View::newInstance()->_exportVariableToView('item', $item);
      return osc_item_url_from_item($item);
    }
  }

  if(osc_is_web_user_logged_in()) {
    return osc_route_url('osp-cart');
  }

  return osc_base_url();
}


// CREATE PAYMENT URL
function osp_pay_url($type, $item_id = NULL, $extend = NULL) {
  return osc_route_url('osp-item-pay', array('type'=> $type, 'itemId' => $item_id, 'extend' => $extend));
}


// PREPARE PAYMENT CUSTOM
function osp_prepare_custom($extra_array = null) {
  if($extra_array != null) {
    if(is_array($extra_array)) {
      $extra = '';
      $i = 0;
      foreach($extra_array as $k => $v) {
        if($i == 0) {
          $extra .= $k.",".$v;
        } else {
          $extra .= "|".$k.",".$v;
        }
        $i++;
      }
    } else {
      $extra = $extra_array;
    }
  } else {
    $extra = "";
  }
  
  return $extra;
}


// GET CUSTOM OF PAYMENT
function osp_get_custom($custom) {
  if (osc_rewrite_enabled()) {
    $custom = urldecode($custom);
  }

  $tmp = array();
  if(preg_match_all('@\|?([^,]+),([^\|]*)@', $custom, $m)){
    $l = count($m[1]);
    for($k=0;$k<$l;$k++) {
      $tmp[$m[1][$k]] = $m[2][$k];
    }
  }
  
  return $tmp;
}


// CREATE PRODUCT TITLE IN CART
function osp_product_cart_name($product) {
  $type = $product[1];
  $quantity = $product[2];
  $id = $product[3];
  $duration = isset($product[4]) ? $product[4] : '';
  $repeat = isset($product[5]) ? $product[5] : '';

  if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE || $type == OSP_TYPE_TOP || $type == OSP_TYPE_PREMIUM || $type == OSP_TYPE_HIGHLIGHT || $type == OSP_TYPE_REPUBLISH) {
    View::newInstance()->_exportVariableToView('item', Item::newInstance()->findByPrimaryKey($id));
    $link_title = __('ID', 'osclass_pay') . ': ' . osc_item_id() . '<br/>' . __('Title', 'osclass_pay') . ': ' . osc_item_title() . '<br/>' . __('Price', 'osclass_pay') . ': ' . osc_item_formated_price();
    $link_name = '<a href="' . osc_item_url() . '" target="_blank" class="osp-has-tooltip" title="' . osc_esc_html($link_title) . '">' . osc_highlight(osc_item_title(), 30) . '</a>';
  }


  if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE || $type == OSP_TYPE_TOP) {
    return sprintf(__('Item %s', 'osclass_pay'), $link_name);

  } else if($type == OSP_TYPE_PREMIUM || $type == OSP_TYPE_HIGHLIGHT) {
    return sprintf(__('Item %s (%s)', 'osclass_pay'), $link_name, osp_duration_name($duration));

  } else if($type == OSP_TYPE_REPUBLISH) {
    return sprintf(__('Item %s (%s, repeat %sx)', 'osclass_pay'), $link_name, osp_duration_name($duration), $repeat);

  } else if($type == OSP_TYPE_PACK) {
    $pack = ModelOSP::newInstance()->getPack($id);
    return sprintf(__('Credit pack %s (get %s, pay %s)', 'osclass_pay'), '<u>' . $pack['s_name'] . '</u>', osp_format_price($pack['f_price'] + $pack['f_extra']), osp_format_price($pack['f_price']));

  } else if($type == OSP_TYPE_MEMBERSHIP) {
    $group = ModelOSP::newInstance()->getGroup($id);
    if($duration == '' || $duration <= 0) {
      $duration = ($group['i_days'] > 0 ? $group['i_days'] : 30);

      if($duration == '' || $duration <= 0) {
        $duration = 30;
      }
    }

    return sprintf(__('Membership in %s (%s days)', 'osclass_pay'), '<u>' . $group['s_name'] . '</u>', $duration);


  } else if($type == OSP_TYPE_BANNER) {
    $banner = ModelOSP::newInstance()->getBanner($id);
    return sprintf(__('Banner %s (budget %s)', 'osclass_pay'), '<u>' . $banner['s_name'] . '</u>', osp_format_price($banner['d_budget']));

  } else if($type == OSP_TYPE_SHIPPING) {
    $id = explode('-', $id);
    
    if($id[0] != 'stn') {
      $id = $id[0];
      $shipping = ModelOSP::newInstance()->getShipping($id);
      
      if(isset($shipping['pk_i_id'])) {
        $shipper = User::newInstance()->findByPrimaryKey($shipping['fk_i_user_id']);
        
        if(isset($shipper['pk_i_id'])) {
          $shipper_link = '<a class="osp-shipper" href="' . osc_user_public_profile_url($shipping['fk_i_user_id']) . '" target="_blank" data-shipping-id="' . $id . '">' . $shipper['s_name'] . '</a>';
        } else {
          $shipper_link = '<strong class="osp-shipper" data-shipping-id="' . $id . '">' . __('Unknown seller', 'osclass_pay') . '</strong>';
        }
        
        
        $html = sprintf(__('Delivery by %s from %s', 'osclass_pay'), '<span class="osp-shipping-name">' . $shipping['s_name'] . ' (' . $shipping['s_delivery'] . ')</span>', $shipper_link);
        
        $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
        $opts = ModelOSP::newInstance()->getUserShippings($shipping['fk_i_user_id'], $user['fk_c_country_code'], 1);

        if(count($opts) > 1) {
          $html .= '<div class="osp-cart-ship">';
          $html .= '<strong>' . __('Change', 'osclass_pay') . '<i class="fa fa-caret-down"></i></strong>';
          $html .= '<div class="osp-ship-opts">';
          $html .= '<span>' . __('Available delivery options', 'osclass_pay') . '</span>';
          
          foreach($opts as $op) {
            $html .= '<a class="' . ($op['pk_i_id'] == $id ? 'osp-active' : '') . '" href="' . osp_cart_add(OSP_TYPE_SHIPPING, 1, $op['pk_i_id'], $op['f_fee'], $op['fk_i_user_id']) . '">' . ($op['pk_i_id'] == $id ? '<i class="fa fa-check"></i>' : '') . '<span>' . $op['s_name'] . ' (' . $op['s_delivery'] . '):</span><strong>' . osp_format_price($op['f_fee']) . '</strong></a>';
          }
          
          $html .= '</div>';
          $html .= '</div>';
        }

        return $html;
        
      } else {
        return __('Unknown shipping', 'osclass_pay');
      }
    } else {
      $shipper_id = $id[1];
      $shipper = User::newInstance()->findByPrimaryKey($shipper_id);
      return sprintf(__('Standard shipping from %s', 'osclass_pay'), '<strong>' . (@$shipper['s_name'] <> '' ? $shipper['s_name'] : __('N/A', 'osclass_pay')) . '</strong>');
    }

  } else if($type == OSP_TYPE_VOUCHER) {
    $voucher = ModelOSP::newInstance()->getVoucher($id);

    if($voucher['s_type'] == 'AMOUNT') {
      $voucher_discount = osp_format_price($voucher['d_amount']);
    } else if($voucher['s_type'] == 'PERCENT') {
      $voucher_discount = round($voucher['d_amount'], 2) . '%';
    }

    return sprintf(__('Voucher %s (-%s)', 'osclass_pay'), '<u>' . $voucher['s_code'] . '</u>', $voucher_discount);
    
  } else if($type == OSP_TYPE_BOOKING) {
    $reservation = ModelOSP::newInstance()->getBooking($id);
    return sprintf(__('Booking / Reservation #%s', 'osclass_pay'), '<u>' . $reservation['s_code'] . '</u>');

  } else if($type == OSP_TYPE_OFFER) {
    $offer = ModelMO::newInstance()->getOfferById($id);
    $item = Item::newInstance()->findByPrimaryKey(@$offer['fk_i_item_id']);
    
    return sprintf(__('Pay custom offer #%d (%s)', 'osclass_pay'), $id, osc_highlight(@$item['s_title'], 30));

  } else if($type == OSP_TYPE_AUCTION_PAY) {
    $auction = ModelOSP::newInstance()->getAuction($id);
    $item = Item::newInstance()->findByPrimaryKey(@$auction['fk_i_item_id']);
    
    return sprintf(__('Buy auctioned item #%d (%s, +shipping)', 'osclass_pay'), $id, osc_highlight(@$item['s_title'], 30));

  } else if($type == OSP_TYPE_AUCTION_BUYOUT) {
    $auction = ModelOSP::newInstance()->getAuction($id);
    $item = Item::newInstance()->findByPrimaryKey(@$auction['fk_i_item_id']);
    
    return sprintf(__('Buyout auctioned item #%d (%s, +shipping)', 'osclass_pay'), $id, osc_highlight(@$item['s_title'], 30));

  } else if($type == OSP_TYPE_AUCTION_FEE) {
    $auction = ModelOSP::newInstance()->getAuction($id);
    $item = Item::newInstance()->findByPrimaryKey(@$auction['fk_i_item_id']);
    
    return sprintf(__('Auction fee #%d (%s)', 'osclass_pay'), $id, osc_highlight(@$item['s_title'], 30));

  } else if($type == OSP_TYPE_AUCTION_BID_FEE) {
    $auction = ModelOSP::newInstance()->getAuction($id);
    $item = Item::newInstance()->findByPrimaryKey(@$auction['fk_i_item_id']);
    
    return sprintf(__('Auction win bid fee #%d (%s)', 'osclass_pay'), $id, osc_highlight(@$item['s_title'], 30));


  } else if($type == OSP_TYPE_PRODUCT) {
    $item = Item::newInstance()->findByPrimaryKey($id);
    View::newInstance()->_exportVariableToView('item', $item);

    $link_title = __('ID', 'osclass_pay') . ': ' . osc_item_id() . '<br/>' . __('Title', 'osclass_pay') . ': ' . osc_item_title() . '<br/>' . __('Price', 'osclass_pay') . ': ' . osc_item_formated_price();
    $link_name = '<a href="' . osc_item_url() . '" target="_blank" class="osp-has-tooltip" title="' . osc_esc_html($link_title) . '">' . osc_highlight(osc_item_title(), 30) . '</a>';

    return sprintf(__('Purchase %s for %s', 'osclass_pay'), $link_name, osp_format_price(osp_convert($item['i_price']/1000000, $item['fk_c_currency_code'])));

  } else if($type == OSP_TYPE_MULTIPLE) {
    return __('Pay cart items', 'osclass_pay');
  }
  
  return __('Not recognized', 'osclass_pay') . ' (' . $type . ')';
}


// CREATE PRODUCT TITLE IN LOGS
function osp_cart_string_to_title($cart) {
  if($cart == null || trim($cart) == '') {
    return '';
  }

  $cart = explode('|', $cart);
  $title = array();

  foreach($cart as $c) {
    $product = explode('x', $c);

    $type = $product[0];
   
    $quantity = isset($product[1]) ? $product[1] : '';
    $id = isset($product[2]) ? $product[2] : '';
    $duration = isset($product[3]) ? $product[3] : '';
    $repeat = isset($product[4]) ? $product[4] : '';

    if(in_array($type, array(OSP_TYPE_PUBLISH, OSP_TYPE_IMAGE, OSP_TYPE_TOP, OSP_TYPE_PREMIUM, OSP_TYPE_HIGHLIGHT, OSP_TYPE_REPUBLISH, OSP_TYPE_PRODUCT))) {
      $item = Item::newInstance()->findByPrimaryKey($id);
      $item_title = '<strong>' . (isset($item['s_title']) ? osc_highlight($item['s_title'], 30) . ' (#' . $id . ')' : '#' . $id) . '</strong>';
    }

    if($type == OSP_TYPE_PUBLISH) {
      $title[] = sprintf(__('%sx Publish fee for item %s', 'osclass_pay'), $quantity, $item_title);
    } else if($type == OSP_TYPE_IMAGE) {
      $title[] = sprintf(__('%sx Image fee for item %s', 'osclass_pay'), $quantity, $item_title);
    } else if($type == OSP_TYPE_TOP) {
      $title[] = sprintf(__('%sx Move to top fee for item %s', 'osclass_pay'), $quantity, $item_title);
    } else if($type == OSP_TYPE_PREMIUM) {
      $title[] = sprintf(__('%sx Premium fee for item %s, expire in %s', 'osclass_pay'), $quantity, $item_title, osp_duration_name($duration));
    } else if($type == OSP_TYPE_HIGHLIGHT) {
      $title[] = sprintf(__('%sx Highlight fee for item %s, expire in %s', 'osclass_pay'), $quantity, $item_title, osp_duration_name($duration));
    } else if($type == OSP_TYPE_REPUBLISH) {
      $title[] = sprintf(__('%sx Republish fee for item %s, expire in %s, repeat %sx', 'osclass_pay'), $quantity, $item_title, osp_duration_name($duration), $repeat);
    } else if($type == OSP_TYPE_MEMBERSHIP) {
      $group = ModelOSP::newInstance()->getGroup($id);
      $group_name = '<strong>' . (isset($group['s_name']) ? osc_highlight($group['s_name'], 30) : '#' . $id) . '</strong>';
      $title[] = sprintf(__('%sx Membership in group %s, expire in %s days', 'osclass_pay'), $quantity, $group_name, $duration);

    } else if($type == OSP_TYPE_PACK) {
      $pack = ModelOSP::newInstance()->getPack($id);
      $pack_name = '<strong>' . (isset($pack['s_name']) ? osc_highlight($pack['s_name'], 30) : '#' . $id) . '</strong>';
      $title[] = sprintf(__('%sx Credit pack %s (get %s, pay %s)', 'osclass_pay'), $quantity, $pack_name, osp_format_price($pack['f_price'] + $pack['f_extra']), osp_format_price($pack['f_price']));

    } else if($type == OSP_TYPE_BANNER) {
      $banner = ModelOSP::newInstance()->getBanner($id);
      $banner_name = '<strong>' . (isset($banner['s_name']) ? osc_highlight($banner['s_name'], 30) : '#' . $id) . '</strong>';
      $title[] = sprintf(__('%sx Banner %s (budget %s)', 'osclass_pay'), $quantity, $banner_name, osp_format_price(isset($banner['d_budget']) ? $banner['d_budget'] : 0));

    } else if($type == OSP_TYPE_SHIPPING) {
      $shipping = ModelOSP::newInstance()->getShipping($id);

      if(isset($shipping['pk_i_id'])) {
        $title[] = sprintf(__('%sx Shipping %s (%s)', 'osclass_pay'), $quantity, $shipping['s_name'], $shipping['s_delivery']);
      } else {
        $title[] = sprintf(__('%sx Unknown shipping', 'osclass_pay'), $quantity);
      }
      
      $id = explode('-', $id);
      
      if($id[0] != 'stn') {
        $id = $id[0];
        $shipping = ModelOSP::newInstance()->getShipping($id);
        
        if(isset($shipping['pk_i_id'])) {
          $shipper = User::newInstance()->findByPrimaryKey($shipping['fk_i_user_id']);
          $title[] = sprintf(__('%sx Shipping %s (%s) from %s', 'osclass_pay'), $quantity, $shipping['s_name'], $shipping['s_delivery'], (@$shipper['s_name'] <> '' ? $shipper['s_name'] : __('N/A', 'osclass_pay')));
        } else {
          $title[] = __('Unknown shipping', 'osclass_pay');
        }
      } else {
        $shipper_id = $id[1];
        $shipper = User::newInstance()->findByPrimaryKey($shipper_id);
        $title[] = sprintf(__('%sx Standard shipping from %s', 'osclass_pay'), $quantity, (@$shipper['s_name'] <> '' ? $shipper['s_name'] : __('N/A', 'osclass_pay')));
      }

    } else if($type == OSP_TYPE_VOUCHER) {
      $voucher = ModelOSP::newInstance()->getVoucher($id);

      if($voucher['s_type'] == 'AMOUNT') {
        $voucher_discount = osp_format_price($voucher['d_amount']);
      } else if($voucher['s_type'] == 'PERCENT') {
        $voucher_discount = round($voucher['d_amount'], 2) . '%';
      }

      $title[] = sprintf(__('%sx Voucher %s (-%s)', 'osclass_pay'), $quantity, $voucher['s_code'], $voucher_discount);

    } else if($type == OSP_TYPE_BOOKING) {
      $reservation = ModelOSP::newInstance()->getBooking($id);
      $title[] = sprintf(__('%sx Booking / Reservation %s', 'osclass_pay'), 1, isset($reservation['s_code']) ? $reservation['s_code'] : $id);

    } else if($type == OSP_TYPE_OFFER) {
      $offer = ModelMO::newInstance()->getOfferById($id);
      $item = Item::newInstance()->findByPrimaryKey(@$offer['fk_i_item_id']);
      
      $title[] = sprintf(__('%sx Pay custom offer #%d (%s)', 'osclass_pay'), 1, $id, osc_highlight(@$item['s_title'], 30));


    } else if($type == OSP_TYPE_AUCTION_PAY) {
      $auction = ModelOSP::newInstance()->getAuction($id);
      $item = Item::newInstance()->findByPrimaryKey(@$auction['fk_i_item_id']);
      
      $title[] = sprintf(__('%sx Auctioned item #%d (%s, +shipping)', 'osclass_pay'), 1, $id, osc_highlight(@$item['s_title'], 30));

    } else if($type == OSP_TYPE_AUCTION_BUYOUT) {
      $auction = ModelOSP::newInstance()->getAuction($id);
      $item = Item::newInstance()->findByPrimaryKey(@$auction['fk_i_item_id']);
      
      $title[] = sprintf(__('%sx Buyout auctioned item #%d (%s, +shipping)', 'osclass_pay'), 1, $id, osc_highlight(@$item['s_title'], 30));

    } else if($type == OSP_TYPE_AUCTION_FEE) {
      $auction = ModelOSP::newInstance()->getAuction($id);
      $item = Item::newInstance()->findByPrimaryKey(@$auction['fk_i_item_id']);
      
      $title[] = sprintf(__('%sx Auction fee #%d (%s)', 'osclass_pay'), 1, $id, osc_highlight(@$item['s_title'], 30));

    } else if($type == OSP_TYPE_AUCTION_BID_FEE) {
      $auction = ModelOSP::newInstance()->getAuction($id);
      $item = Item::newInstance()->findByPrimaryKey(@$auction['fk_i_item_id']);
      
      $title[] = sprintf(__('%sx Auction win bid fee #%d (%s)', 'osclass_pay'), 1, $id, osc_highlight(@$item['s_title'], 30));
      
      
    } else if($type == OSP_TYPE_PRODUCT) {
      $title[] = sprintf(__('%sx Purchase item %s, regular unit price %s', 'osclass_pay'), $quantity, $item_title, osp_format_price(osp_convert(@$item['i_price']/1000000, @$item['fk_c_currency_code'])));

    } else if($type == OSP_TYPE_MULTIPLE) {
      $title[] = __('Pay cart items', 'osclass_pay');

    } else {
      $title[] = sprintf(__('Not recognized (%s)', 'osclass_pay'), $c);
    }
  }

  $title = array_filter($title);
  $title_string = implode('<br/>', $title);

  return $title_string;
}


// CREATE PRODUCT TEXT IN CART - ITEMPAY
function osp_product_cart_text($type) {

  if($type == OSP_TYPE_PUBLISH) {
    return __('In order to show your listing, it is required to pay publish fee.', 'osclass_pay');

  } else if($type == OSP_TYPE_IMAGE) {
    return __('To show images on your listing, it is required to pay image fee.', 'osclass_pay');

  } else if($type == OSP_TYPE_TOP) {
    return __('Your listing will be moved to top position and will look like newly published.', 'osclass_pay');

  } else if($type == OSP_TYPE_PREMIUM) {
    return __('Make your listing more visible on home and search page.', 'osclass_pay');

  } else if($type == OSP_TYPE_HIGHLIGHT) {
    return __('Listing will be highlighted between other listings and attract more people.', 'osclass_pay');

  } else if($type == OSP_TYPE_REPUBLISH) {
    return __('Your item will be instantly renewed and then automatically republished in selected period repeatedly.', 'osclass_pay');

  } else if($type == OSP_TYPE_PACK) {
    return __('Save time with transaction and get credits on your account.', 'osclass_pay');

  } else if($type == OSP_TYPE_MEMBERSHIP) {
    return __('Get access to unique content or use global discounts for promotions.', 'osclass_pay');

  } else if($type == OSP_TYPE_BANNER) {
    return __('Pay banner budget and advertise on our site.', 'osclass_pay');

  } else if($type == OSP_TYPE_SHIPPING) {
    return __('Pay shipping fee related to delivery of product.', 'osclass_pay');
    
  } else if($type == OSP_TYPE_PRODUCT) {
    return __('Purchase item.', 'osclass_pay');

  } else if($type == OSP_TYPE_MULTIPLE) {
    return __('Pay cart items.', 'osclass_pay');

  } else if($type == OSP_TYPE_VOUCHER) {
    return __('Get discount on your order.', 'osclass_pay');

  } else if($type == OSP_TYPE_BOOKING) {
    return __('Pay for reservation/booking.', 'osclass_pay');

  } else if($type == OSP_TYPE_OFFER) {
    return __('Pay custom offer.', 'osclass_pay');
    
  } else if($type == OSP_TYPE_AUCTION_PAY) {
    return __('Pay for auctioned item (+shipping).', 'osclass_pay');
    
  } else if($type == OSP_TYPE_AUCTION_BUYOUT) {
    return __('Buyout auctioned item (+shipping).', 'osclass_pay');
    
  } else if($type == OSP_TYPE_AUCTION_FEE) {
    return __('Pay auction fee.', 'osclass_pay');
    
  } else if($type == OSP_TYPE_AUCTION_BID_FEE) {
    return __('Pay auction win bid fee.', 'osclass_pay');

  }

  return __('Not recognized.', 'osclass_pay');
}



function osp_cart_quantity_check() {
  $user_id = osc_logged_user_id();

  if(osp_param('stock_management') == 1 && osc_is_web_user_logged_in()) {
    $cart = explode('|', ModelOSP::newInstance()->getCart($user_id));  

    if(count($cart) > 0) {
      foreach($cart as $c) {
        $product = explode('x', $c);

        if(@$product[0] == OSP_TYPE_PRODUCT) {
          $cart_qty = $product[1];

          $item_data = ModelOSP::newInstance()->getItemData(@$product[2]);
          $avl_qty = isset($item_data['i_quantity']) ? $item_data['i_quantity'] : 0;

          if($avl_qty < $cart_qty) {
            osp_cart_remove($user_id, $c);
            osc_add_flash_warning_message(sprintf(__('Not enough products in stock, only %s pieces are available. This product has been removed from cart.', 'osclass_pay'), $avl_qty));
          }
        }
      }
    }
  }
}

osc_add_hook('init', 'osp_cart_quantity_check');


// CREATE PRODUCT DESCRIPTION IN ITEM LIST
function osp_product_title($product) {
  $type = $product[0];
  $id = $product[1];
  $duration = $product[2];
  $repeat = $product[3];
  $fee_item = $product[4];

  $return = array();

  if($type == OSP_TYPE_PUBLISH) {
    $return = array('short' => __('Publish Paid', 'osclass_pay'), 'long' => '');
  } else if($type == OSP_TYPE_IMAGE) {
    $return = array('short' => __('Image Paid', 'osclass_pay'), 'long' => '');
  } else if($type == OSP_TYPE_PREMIUM) {
    $return = array('short' => __('Premium Paid', 'osclass_pay'), 'long' => sprintf(__('Premium mark will expire on %s', 'osclass_pay'), $duration));
  } else if($type == OSP_TYPE_HIGHLIGHT) {
    $return = array('short' => __('Highlight Paid', 'osclass_pay'), 'long' => sprintf(__('Highlight will expire on %s', 'osclass_pay'), $duration));
  } else if($type == OSP_TYPE_REPUBLISH) {
    $return = array('short' => __('Republish Paid', 'osclass_pay'), 'long' => sprintf(__('Replubish every %s, repeat %s more time(s). Next republish on %s', 'osclass_pay'), osp_duration_name($fee_item['i_hours']), $fee_item['i_repeat'], $fee_item['dt_expire']));
  } else if($type == OSP_TYPE_TOP) {
    $item = Item::newInstance()->findByPrimaryKey($id);
    $return = array('short' => __('Moved to Top', 'osclass_pay'), 'long' => sprintf(__('Last move to top on %s', 'osclass_pay'), $item['dt_pub_date']));
  }
  
  return $return;
}



// ADD TO CART LINK
function osp_cart_add($p1, $p2 = '', $p3 = '', $p4 = '', $p5 = '', $p6 = '') {
  $product = $p1;

  if($p1 <> OSP_TYPE_MULTIPLE) {
    if($p2 <> '') { $product .= 'x' . trim($p2); }
    if($p3 <> '') { $product .= 'x' . trim($p3); }
    if($p4 <> '') { $product .= 'x' . trim($p4); }
    if($p5 <> '') { $product .= 'x' . trim($p5); }
    if($p6 <> '') { $product .= 'x' . trim($p6); }

    if (osc_rewrite_enabled() && $p1 == OSP_TYPE_MEMBERSHIP) {
      return osc_base_url(true).'?page=custom&route=osp-cart-update&product='.$product;
    } else {
      return osc_route_url('osp-cart-update', array('product' => $product));
    }
  } else {
    $items = explode('|', $p4);
    $list = '';

    if(count($items) > 0) {
      foreach($items as $i) {
        if($list <> '') {
          $list .= '|';
        }

        $list .= isset($i[0]) ? $i[0] : '';
 
        if(isset($i[1]) && $i[1] <> '') { $product .= 'x' . trim($i[1]); }
        if(isset($i[2]) && $i[2] <> '') { $product .= 'x' . trim($i[2]); }
        if(isset($i[3]) && $i[3] <> '') { $product .= 'x' . trim($i[3]); }
        if(isset($i[4]) && $i[4] <> '') { $product .= 'x' . trim($i[4]); }
      }
    }

    return osc_route_url('osp-cart-update', array('product' => $list));
  }  
}


// UPDATE CART
function osp_cart_update($user_id, $content) {
  $new = explode('|', $content);                                      // new products to be added in format 201x1x32|501x1x8|601x2x1
  $cart = explode('|', ModelOSP::newInstance()->getCart($user_id));   // existing products in cart

  if(count($new) > 0) {
    $j = 0;

    // if URL contains ?xyz=fdsfasd, then remove this part
    if(strpos(end($new), '?') !== false){
      $last = explode('?', end($new));
      $new[count($new)-1] = $last[0];
    }
    
    if(strpos(end($new), '&') !== false){
      $last = explode('&', end($new));
      $new[count($new)-1] = $last[0];
    }
    
    foreach($new as $n) {                                             // checking each new product to be added to cart, if there does not exists same in cart
      if(!empty($n) && !is_array($n)) {
        $n = explode('x', $n);
        
        if(count($cart) > 0) {
          $i = 0;
          $have_shipping_user_ids = array();

          foreach($cart as $c) {
            $c = explode('x', $c);

            if($n[0] == $c[0] && ($n[2] == $c[2] || $n[0] == OSP_TYPE_MEMBERSHIP || ($n[0] == OSP_TYPE_SHIPPING && @$n[4] == @$c[4]))) {                        // it is same product
              // ONLY ONE SHIPPING FEE CAN BE ACTIVE FROM 1 SELLER AT SAME TIME
              if($n[0] == OSP_TYPE_SHIPPING && @$n[4] == @$c[4] && substr(@$n[2], 0, 3) !== 'stn') {
                $shipping_new = ModelOSP::newInstance()->getShipping($n[2]);
                $shipping_old = ModelOSP::newInstance()->getShipping($c[2]);

                if(isset($shipping_new['pk_i_id']) && isset($shipping_old['pk_i_id'])) {
                  if($shipping_new['pk_i_id'] != $shipping_old['pk_i_id'] && $shipping_new['fk_i_user_id'] == $shipping_old['fk_i_user_id']) {
                    //osc_add_flash_ok_message(__('Shipping has been updated', 'osclass_pay'));
                    $c = $n;  // replace old shipping with new one    
                  }
                }
                
                $have_shipping_user_ids[] = @$n[4];
                
              } else if($c[0] == OSP_TYPE_SHIPPING) {
                if(in_array(@$c[4], $have_shipping_user_ids) || @$c[4] == '') {
                  //$c[1] = 0;   // drop, we already have shipping from this user!!
                } else {
                  $have_shipping_user_ids[] = $c[4];
                }
                
              }
  
              // ONLY ONE MEMBERSHIP GROUP CAN BE PURCHASED AT SAME TIME
              if($n[0] == OSP_TYPE_MEMBERSHIP) {
                $group_new = ModelOSP::newInstance()->getGroup($n[2]);
                $group_cart = ModelOSP::newInstance()->getGroup($c[2]);

                if(@$c[2] <> @$n[2]) {
                  osc_add_flash_warning_message(sprintf(__('Membership in %s has been removed from cart and replaced with membership in %s.', 'osclass_pay'), '<strong>' . @$group_cart['s_name'] . '</strong>', '<strong>' . @$group_new['s_name'] . '</strong>'));
                }

                $c = $n;  // replace old with new group
              }


              // ONLY 1 VOUCHER CAN BE USED AT SAME TIME
              if($n[0] == OSP_TYPE_VOUCHER) {
                $voucher_new = ModelOSP::newInstance()->getVoucher($n[2]);
                $voucher_cart = ModelOSP::newInstance()->getVoucher($c[2]);

                if(@$c[2] <> @$n[2]) {
                  osc_add_flash_warning_message(sprintf(__('Voucher %s has been removed from cart and replaced with %s.', 'osclass_pay'), '<strong>' . @$voucher_cart['s_code'] . '</strong>', '<strong>' . @$voucher_new ['s_code'] . '</strong>'));
                } else {
                  //osc_add_flash_warning_message(__('You already had this voucher in cart', 'osclass_pay'));
                }

                $c = $n;  // replace old voucher with new
              }


              $c[1] = @$c[1] + @$n[1];  // add quantity

              if(@$n[0] <> OSP_TYPE_PACK && @$n[0] <> OSP_TYPE_PRODUCT && @$c[1] > 1) {
              //if(@$c[1] > 1) {
                $c[1] = 1;  // only pack can have quantity more than 1
              }


              if(@$n[0] == OSP_TYPE_PRODUCT && osp_param('stock_management') == 1) {      // when product added to cart and stock management allowed, check if quantity is available
                $item_data = ModelOSP::newInstance()->getItemData(@$c[2]);
                $item_data['i_quantity'] = isset($item_data['i_quantity']) ? $item_data['i_quantity'] : 0;

                if(@$item_data['i_quantity'] < @$c[1]) {
                  osc_add_flash_warning_message(sprintf(__('Not enough products in stock, only %s products has been added to cart.', 'osclass_pay'), $item_data['i_quantity']));
                  $c[1] = $item_data['i_quantity'];
                }
              }


              $cart[$i] = implode('x', $c);         // update in cart
              $new[$j] = array();                   // update in new

              if(@$c[1] <= 0) {
                $cart[$i] = array();                // remove if quantity is 0
              }


              if(@$n[1] <= 0) {
                $new[$j] = array();                   // remove if quantity is 0
              }
            }

            $i++;
          }
        }
      }

      $j++;
    }
  }

  $updated = array_filter(array_merge($cart, $new));
  $content = implode('|', $updated); 

  if(trim($content) == '') {
    ModelOSP::newInstance()->deleteCart($user_id);
  } else {
    ModelOSP::newInstance()->updateCart($user_id, trim($content));
  }
}



// REMOVE FROM CART
function osp_cart_remove($user_id, $content) {
  if($user_id == '' || $user_id <= 0) {
    $user_id = osc_logged_user_id();
  }

  $content = explode('x', $content);
  $content[1] = -floatval($content[1]);
  $content = implode('x', $content);

  osp_cart_update($user_id, $content);
}


// DROP CART
function osp_cart_drop($user_id) {
  if($user_id == '' || $user_id <= 0) {
    $user_id = osc_logged_user_id();
  }

  ModelOSP::newInstance()->deleteCart($user_id);
}


// GET CART CONTENT
function osp_cart_content($user_id = '') {
  if($user_id == '' || $user_id == 0) {
    $user_id = osc_logged_user_id();
  }

  $cart = ModelOSP::newInstance()->getCart($user_id);

  if(trim($cart) <> '') {
    $return = array();
    $products = explode('|', $cart);
  
    if(count($products) > 0) {
      foreach($products as $p) {
        $return[] = array_merge(array($p), explode('x', $p));
      }
    }

    array_multisort(array_column($return, 1), SORT_ASC, $return);

    return $return;
  }

  return array();
}


// CALCULATE TOTAL CART PRICE
function osp_cart_price($user_id = '', $exclude_voucher = 0) {
  if($user_id == '' || $user_id == 0) {
    $user_id = osc_logged_user_id();
  }

  $cart = osp_cart_content($user_id);
  $total = 0;
  $count = 0;

  foreach($cart as $c) {
    if(isset($c[1]) && $c[1] <> '') {
      if($exclude_voucher == 1 && $c[1] <> OSP_TYPE_VOUCHER || $exclude_voucher == 0) {
        $price = osp_get_fee($c[1], isset($c[2]) ? $c[2] : '', isset($c[3]) ? $c[3] : '', isset($c[4]) ? $c[4] : '', isset($c[5]) ? $c[5] : ''); 
        $total = $total + $price;
        $count = $count + (float)@$c[2];
      }
    }
  }

  return array('price' => $total, 'quantity' => $count);
}


// ADJUST FUNDS IN WALLET
function osp_wallet_update($user_id, $amount) {
  return ModelOSP::newInstance()->addWallet($user_id, $amount);
}


// GET WALLET RECORD
function osp_get_wallet($user_id = NULL) {
  if($user_id == 0 || $user_id == '') {
    $user_id = osc_logged_user_id();
  }

  return ModelOSP::newInstance()->getWallet($user_id);
}


// GET WALLET AMOUNT
function osp_get_wallet_amount($user_id) {
  $amount = osp_get_wallet($user_id);
  if($amount) {
    return $amount['formatted_amount'];
  } else {
    return 0;
  }
}


// GET USER GROUP - AJAX
function osp_get_group_ajax() {
  $user_id = Params::getParam('id');
  
  if($user_id <> '' && $user_id > 0) {
    $user = User::newInstance()->findByPrimaryKey($user_id);
    $group = ModelOSP::newInstance()->getUserGroupRecord($user_id);
    echo json_encode(array('user' => $user, 'group' => $group));
  } else {
    echo json_encode(array('user' => array('id' => 0), 'group' => array('pk_i_id' => 0)));
  }

  exit;
}

osc_add_hook('ajax_admin_osp_group_data', 'osp_get_group_ajax');


// GET USER WALLET - AJAX
function osp_get_wallet_ajax() {
  $user_id = Params::getParam('id');
  
  if($user_id <> '' && $user_id > 0) {
    $user = User::newInstance()->findByPrimaryKey($user_id);
    $amount = osp_get_wallet_amount($user_id);
    echo json_encode(array('user' => array('id' => $user_id, 'name' => $user['s_name'], 'email' => $user['s_email']), 'amount' => $amount));
  } else {
    echo json_encode(array('user' => array('id' => 0, 'name' => '', 'email' =>''), 'amount' => ''));
  }

  exit;
}

osc_add_hook('ajax_admin_osp_wallet_data', 'osp_get_wallet_ajax');


// PAY WITH CREDITS BUTTON
function osp_wallet_button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = '||') {
  $user_id = osc_logged_user_id();
  $extra = osp_prepare_custom($extra_array) . '|';
  $extra .= 'concept,'.$description.'|';
  $extra .= 'product,'.$itemnumber.'|';
  $wallet = osp_get_wallet($user_id);
  $html = '';

  if(osp_param('wallet_enabled') == 1) {
    if($amount <= 0) {
      $html .= '<li><a class="osp-btn-wallet osp-has-tooltip" href="' . osc_route_url('osp-wallet', array('a' => round($amount, 2), 'desc' => $description, 'extra' => $extra)) . '" title="' . osc_esc_html(__('Click to complete order', 'osclass_pay')) . '">';
      $html .= '<span class="osp-i2">';
      $html .= '<svg x="0px" y="0px" width="48" height="48" viewBox="0 0 469.341 469.341" style="enable-background:new 0 0 469.341 469.341;" xml:space="preserve"> <g> <g> <g> <path d="M437.337,384.007H362.67c-47.052,0-85.333-38.281-85.333-85.333c0-47.052,38.281-85.333,85.333-85.333h74.667 c5.896,0,10.667-4.771,10.667-10.667v-32c0-22.368-17.35-40.559-39.271-42.323l-61.26-107 c-5.677-9.896-14.844-16.969-25.813-19.906c-10.917-2.917-22.333-1.385-32.104,4.302L79.553,128.007H42.67 c-23.531,0-42.667,19.135-42.667,42.667v256c0,23.531,19.135,42.667,42.667,42.667h362.667c23.531,0,42.667-19.135,42.667-42.667 v-32C448.004,388.778,443.233,384.007,437.337,384.007z M360.702,87.411l23.242,40.596h-92.971L360.702,87.411z M121.953,128.007 L300.295,24.184c4.823-2.823,10.458-3.573,15.844-2.135c5.448,1.458,9.99,4.979,12.813,9.906l0.022,0.039l-164.91,96.013H121.953 z"/> <path d="M437.337,234.674H362.67c-35.292,0-64,28.708-64,64c0,35.292,28.708,64,64,64h74.667c17.646,0,32-14.354,32-32v-64 C469.337,249.028,454.983,234.674,437.337,234.674z M362.67,320.007c-11.76,0-21.333-9.573-21.333-21.333 c0-11.76,9.573-21.333,21.333-21.333c11.76,0,21.333,9.573,21.333,21.333C384.004,310.434,374.431,320.007,362.67,320.007z"/> </g> </g> </g> </svg>';
      $html .= '<em>' . __('Wallet', 'osclass_pay') . '</em>';
      $html .= '</span>';
      $html .= '<strong>' . __('Complete order', 'osclass_pay') . '</strong>';
      $html .= '</a></li>'; 
    } else if(isset($wallet['formatted_amount']) && $wallet['formatted_amount'] >= $amount) {
      $html .= '<li><a class="osp-btn-wallet osp-has-tooltip" href="' . osc_route_url('osp-wallet', array('a' => round($amount, 2), 'desc' => $description, 'extra' => $extra)) . '" title="' . osc_esc_html(__('Funds will be withdrawn from your wallet (credits)', 'osclass_pay')) . '">';
      $html .= '<span class="osp-i2">';
      $html .= '<svg x="0px" y="0px" width="48" height="48" viewBox="0 0 469.341 469.341" style="enable-background:new 0 0 469.341 469.341;" xml:space="preserve"> <g> <g> <g> <path d="M437.337,384.007H362.67c-47.052,0-85.333-38.281-85.333-85.333c0-47.052,38.281-85.333,85.333-85.333h74.667 c5.896,0,10.667-4.771,10.667-10.667v-32c0-22.368-17.35-40.559-39.271-42.323l-61.26-107 c-5.677-9.896-14.844-16.969-25.813-19.906c-10.917-2.917-22.333-1.385-32.104,4.302L79.553,128.007H42.67 c-23.531,0-42.667,19.135-42.667,42.667v256c0,23.531,19.135,42.667,42.667,42.667h362.667c23.531,0,42.667-19.135,42.667-42.667 v-32C448.004,388.778,443.233,384.007,437.337,384.007z M360.702,87.411l23.242,40.596h-92.971L360.702,87.411z M121.953,128.007 L300.295,24.184c4.823-2.823,10.458-3.573,15.844-2.135c5.448,1.458,9.99,4.979,12.813,9.906l0.022,0.039l-164.91,96.013H121.953 z"/> <path d="M437.337,234.674H362.67c-35.292,0-64,28.708-64,64c0,35.292,28.708,64,64,64h74.667c17.646,0,32-14.354,32-32v-64 C469.337,249.028,454.983,234.674,437.337,234.674z M362.67,320.007c-11.76,0-21.333-9.573-21.333-21.333 c0-11.76,9.573-21.333,21.333-21.333c11.76,0,21.333,9.573,21.333,21.333C384.004,310.434,374.431,320.007,362.67,320.007z"/> </g> </g> </g> </svg>';
      $html .= '<em>' . __('Wallet', 'osclass_pay') . '</em>';
      $html .= '</span>';
      $html .= '<strong>' . __('Pay with credits', 'osclass_pay') . ' (' . osp_format_price(osp_get_wallet_amount($user_id)) . ')</strong>';
      $html .= '</a></li>';
    } else {
      $html .= '<li><a class="osp-btn-wallet osp-has-tooltip osp-disabled" href="#" title="' . osc_esc_html(__('You do not have enough funds in your wallet', 'osclass_pay')) . '">';
      $html .= '<span class="osp-i2">';
      $html .= '<svg x="0px" y="0px" width="48" height="48" viewBox="0 0 469.341 469.341" style="enable-background:new 0 0 469.341 469.341;" xml:space="preserve"> <g> <g> <g> <path d="M437.337,384.007H362.67c-47.052,0-85.333-38.281-85.333-85.333c0-47.052,38.281-85.333,85.333-85.333h74.667 c5.896,0,10.667-4.771,10.667-10.667v-32c0-22.368-17.35-40.559-39.271-42.323l-61.26-107 c-5.677-9.896-14.844-16.969-25.813-19.906c-10.917-2.917-22.333-1.385-32.104,4.302L79.553,128.007H42.67 c-23.531,0-42.667,19.135-42.667,42.667v256c0,23.531,19.135,42.667,42.667,42.667h362.667c23.531,0,42.667-19.135,42.667-42.667 v-32C448.004,388.778,443.233,384.007,437.337,384.007z M360.702,87.411l23.242,40.596h-92.971L360.702,87.411z M121.953,128.007 L300.295,24.184c4.823-2.823,10.458-3.573,15.844-2.135c5.448,1.458,9.99,4.979,12.813,9.906l0.022,0.039l-164.91,96.013H121.953 z"/> <path d="M437.337,234.674H362.67c-35.292,0-64,28.708-64,64c0,35.292,28.708,64,64,64h74.667c17.646,0,32-14.354,32-32v-64 C469.337,249.028,454.983,234.674,437.337,234.674z M362.67,320.007c-11.76,0-21.333-9.573-21.333-21.333 c0-11.76,9.573-21.333,21.333-21.333c11.76,0,21.333,9.573,21.333,21.333C384.004,310.434,374.431,320.007,362.67,320.007z"/> </g> </g> </g> </svg>';
      $html .= '<em>' . __('Wallet', 'osclass_pay') . '</em>';
      $html .= '</span>';
      $html .= '<strong>' . __('Pay with credits', 'osclass_pay') . ' (' . osp_format_price(osp_get_wallet_amount($user_id)) . ')</strong>';
      $html .= '</a></li>';
    }
  }

  echo $html;
}



// PAY AS ADMIN (no limt)
function osp_admin_button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = '||') {
  $user_id = osc_logged_user_id();
  $extra = osp_prepare_custom($extra_array) . '|';
  $extra .= 'concept,'.$description.'|';
  $extra .= 'product,'.$itemnumber;

  if(osc_is_admin_user_logged_in()) {
    $html  = '<li><a class="osp-btn-admin osp-has-tooltip" href="' . osc_route_url('osp-admin-pay', array('a' => round($amount, 2), 'desc' => urlencode($description), 'extra' => urlencode($extra))) . '" title="' . osc_esc_html(__('This button is visible just in case you are logged in as admin in browser.', 'osclass_pay')) . '">';
    $html .= '<span class="osp-i2">';
    $html .= '<svg enable-background="new 0 0 512 512" height="48" viewBox="0 0 512 512" width="48"><g><path d="m497 201h-22.785c-5.339-21.312-13.722-41.546-25.026-60.407l16.115-16.115c5.858-5.858 5.858-15.355 0-21.213l-56.569-56.569c-5.858-5.858-15.355-5.858-21.213 0l-16.115 16.115c-18.859-11.304-39.096-19.687-60.407-25.026v-22.785c0-8.284-6.716-15-15-15h-80c-8.284 0-15 6.716-15 15v22.785c-21.311 5.338-41.548 13.722-60.407 25.026l-16.115-16.115c-5.858-5.858-15.355-5.858-21.213 0l-56.568 56.569c-5.858 5.858-5.858 15.355 0 21.213l16.114 16.115c-11.304 18.86-19.687 39.094-25.026 60.407h-22.785c-8.284 0-15 6.716-15 15v80c0 8.284 6.716 15 15 15h22.785c5.339 21.313 13.722 41.547 25.026 60.407l-16.115 16.115c-5.858 5.858-5.858 15.355 0 21.213l56.569 56.568c5.858 5.858 15.355 5.858 21.213 0l16.115-16.115c18.86 11.303 39.097 19.687 60.407 25.025v22.787c0 8.284 6.716 15 15 15h80c8.284 0 15-6.716 15-15v-22.786c21.31-5.338 41.547-13.722 60.407-25.025l16.115 16.114c5.858 5.858 15.355 5.858 21.213 0l56.569-56.568c5.858-5.858 5.858-15.355 0-21.213l-16.115-16.115c11.304-18.861 19.687-39.095 25.025-60.407h22.786c8.284 0 15-6.716 15-15v-80c0-8.284-6.716-15-15-15zm-241 210c-85.467 0-155-69.533-155-155s69.533-155 155-155 155 69.533 155 155-69.533 155-155 155z"/><circle cx="256" cy="236" r="35"/><path d="m256 301c-34.763 0-63.236 27.431-64.918 61.784 18.933 11.553 41.162 18.216 64.918 18.216s45.985-6.663 64.918-18.216c-1.682-34.353-30.155-61.784-64.918-61.784z"/><path d="m256 131c-68.925 0-125 56.075-125 125 0 32.759 12.673 62.609 33.366 84.922 6.89-25.146 23.873-46.163 46.309-58.394-12.125-11.815-19.675-28.302-19.675-46.528 0-35.841 29.159-65 65-65s65 29.159 65 65c0 18.226-7.55 34.713-19.675 46.527 22.436 12.232 39.419 33.248 46.309 58.394 20.693-22.312 33.366-52.162 33.366-84.921 0-68.925-56.075-125-125-125z"/></g></svg>';
    $html .= '<em>' . __('AdminPay', 'osclass_pay') . '</em>';
    $html .= '</span>';
    $html .= '<strong>' . __('Pay as Admin', 'osclass_pay') . '</strong>';
    $html .= '</a></li>';
  }

  echo $html;
}


// PAY WITH BANK TRANSFER BUTTON
function osp_transfer_button($amount = '0.00', $description = '', $itemnumber = '', $extra_array = '||') {
  $extra = osp_prepare_custom($extra_array) . '|';
  $extra .= 'concept,'.$description.'|';
  $extra .= 'product,'.$itemnumber;

  $min = (osp_param('bt_min') > 0 ? osp_param('bt_min') : 0);

  if($amount >= $min) {
    $html  = '<li><a class="osp-btn-transfer osp-has-tooltip" href="' . osc_route_url('osp-transfer', array('a' => round($amount, 2), 'desc' => urlencode($description), 'extra' => urlencode($extra))) . '" title="' . osc_esc_html(__('Payment will be accepted after administrator confirms funds delivered to our account.', 'osclass_pay')) . '">';
    $html .= '<span class="osp-i2 osp-i2-tr">';
    $html .= '<svg x="0px" y="0px" width="48" height="48" viewBox="0 0 47.001 47.001" style="enable-background:new 0 0 47.001 47.001;" xml:space="preserve"> <g> <g> <g> <path d="M44.845,42.718H2.136C0.956,42.718,0,43.674,0,44.855c0,1.179,0.956,2.135,2.136,2.135h42.708 c1.18,0,2.136-0.956,2.136-2.135C46.979,43.674,46.023,42.718,44.845,42.718z"/> <path d="M4.805,37.165c-1.18,0-2.136,0.956-2.136,2.136s0.956,2.137,2.136,2.137h37.37c1.18,0,2.136-0.957,2.136-2.137 s-0.956-2.136-2.136-2.136h-0.533V17.945h0.533c0.591,0,1.067-0.478,1.067-1.067s-0.478-1.067-1.067-1.067H4.805 c-0.59,0-1.067,0.478-1.067,1.067s0.478,1.067,1.067,1.067h0.534v19.219H4.805z M37.37,17.945v19.219h-6.406V17.945H37.37z M26.692,17.945v19.219h-6.406V17.945H26.692z M9.609,17.945h6.406v19.219H9.609V17.945z"/> <path d="M2.136,13.891h42.708c0.007,0,0.015,0,0.021,0c1.181,0,2.136-0.956,2.136-2.136c0-0.938-0.604-1.733-1.443-2.021 l-21.19-9.535c-0.557-0.25-1.194-0.25-1.752,0L1.26,9.808c-0.919,0.414-1.424,1.412-1.212,2.396 C0.259,13.188,1.129,13.891,2.136,13.891z"/> </g> </g> </g> </svg>';
    $html .= '<em>' . __('Transfer', 'osclass_pay') . '</em>';
    $html .= '</span>';  
    $html .= '<strong>' . __('Pay via Bank Transfer', 'osclass_pay') . '</strong>';
    $html .= '</a></li>';
  } else {
    $html  = '<li><a class="osp-btn-transfer osp-has-tooltip osp-disabled" disabled="disabled" href="#" onclick="return false;" title="' . osc_esc_html(sprintf(__('Minimum amount for Bank Transfer is %s.', 'osclass_pay'), osp_format_price($min))) . '">';
    $html .= '<span class="osp-i2 osp-i2-tr">';
    $html .= '<svg x="0px" y="0px" width="48" height="48" viewBox="0 0 47.001 47.001" style="enable-background:new 0 0 47.001 47.001;" xml:space="preserve"> <g> <g> <g> <path d="M44.845,42.718H2.136C0.956,42.718,0,43.674,0,44.855c0,1.179,0.956,2.135,2.136,2.135h42.708 c1.18,0,2.136-0.956,2.136-2.135C46.979,43.674,46.023,42.718,44.845,42.718z"/> <path d="M4.805,37.165c-1.18,0-2.136,0.956-2.136,2.136s0.956,2.137,2.136,2.137h37.37c1.18,0,2.136-0.957,2.136-2.137 s-0.956-2.136-2.136-2.136h-0.533V17.945h0.533c0.591,0,1.067-0.478,1.067-1.067s-0.478-1.067-1.067-1.067H4.805 c-0.59,0-1.067,0.478-1.067,1.067s0.478,1.067,1.067,1.067h0.534v19.219H4.805z M37.37,17.945v19.219h-6.406V17.945H37.37z M26.692,17.945v19.219h-6.406V17.945H26.692z M9.609,17.945h6.406v19.219H9.609V17.945z"/> <path d="M2.136,13.891h42.708c0.007,0,0.015,0,0.021,0c1.181,0,2.136-0.956,2.136-2.136c0-0.938-0.604-1.733-1.443-2.021 l-21.19-9.535c-0.557-0.25-1.194-0.25-1.752,0L1.26,9.808c-0.919,0.414-1.424,1.412-1.212,2.396 C0.259,13.188,1.129,13.891,2.136,13.891z"/> </g> </g> </g> </svg>';
    $html .= '<em>' . __('Transfer', 'osclass_pay') . '</em>';
    $html .= '</span>';  
    $html .= '<strong>' . __('Pay via Bank Transfer', 'osclass_pay') . '</strong>';
    $html .= '</a></li>';
  }

  echo $html;
}


// PHP STRING TO CONSOLE.LOG
function osp_to_console($string) {
  $js  = '<script type="text/javascript">';
  $js .= 'console.log("' . $string . '")';
  $js .= '</script>';

  echo $js;
} 


// JAVASCRIPT REDIRECT TO URL
function osp_js_redirect_to($url) {
  $js  = '<script type="text/javascript">';
  $js .= 'window.top.location.href = "' . $url . '"';
  $js .= '</script>';

  echo $js;
}  


// PRINT PAYMENT BUTTONS
function osp_buttons($amount = '0.00', $description = '', $itemnumber = '', $extra_array = null) {
  if(osp_param('paypal_enabled') == 1) {
    PaypalPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('stripe_enabled') == 1) {
    StripePayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('payscz_enabled') == 1) {
    PaysczPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('komfortkasse_enabled') == 1) {
    KomfortkassePayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('przelewy24_enabled') == 1 && in_array(osp_currency(), array('PLN', 'CZK', 'EUR'))) {
    Przelewy24Payment::button($amount, $description, $itemnumber, $extra_array);
  }
  
  if(osp_param('payherelk_enabled') == 1 && in_array(osp_currency(), array('USD', 'LKR'))) {
    PayherelkPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('authorizenet_enabled') == 1 && osp_currency() == 'USD') {
    AuthorizenetPaymentOSP::button($amount, $description, $itemnumber, $extra_array);
  }
  
  if(osp_param('skrill_enabled') == 1) {
    SkrillPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('ccavenue_enabled') == 1 && in_array(osp_currency(), array('INR', 'USD', 'SGD', 'GBP', 'EUR'))) {
    CcavenuePayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('paystack_enabled') == 1 && in_array(osp_currency(), array('GHS', 'NGN', 'USD', 'ZAR', 'KES'))) {
    PaystackPayment::button($amount, $description, $itemnumber, $extra_array);
  }
    
  if(osp_param('blockchain_enabled') == 1) {
    BlockchainPayment::button($amount, $description, $itemnumber, $extra_array);
  }
  
  if(osp_param('braintree_enabled') == 1) {
    BraintreePayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('payza_enabled') == 1) {
    PayzaPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('weaccept_enabled') == 1) {
    WeacceptPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('twocheckout_enabled') == 1) {
    if(osp_param('twocheckout_type') == '' || osp_param('twocheckout_type') == 'onsite') {
      TwoCheckoutPayment::button($amount, $description, $itemnumber, $extra_array);
    } else {
      TwoCheckoutInlinePayment::button($amount, $description, $itemnumber, $extra_array);
    }
  }

  if(osp_param('payumoney_enabled') == 1 && osp_currency() == 'INR') {
    PayumoneyPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('payulatam_enabled') == 1 && in_array(osp_currency(), array('ARS', 'BRL', 'CLP', 'COP', 'MXN', 'PEN', 'USD'))) {
    PayulatamPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('worldpay_enabled') == 1) {
    WorldpayPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('pagseguro_enabled') == 1 && osp_currency() == 'BRL') {
    PagseguroPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('instamojo_enabled') == 1 && osp_currency() == 'INR') {
    InstamojoPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('euplatesc_enabled') == 1 && in_array(osp_currency(), array('EUR', 'USD', 'RON'))) {
    EuPlatescPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('yookassa_enabled') == 1 && osp_currency() == 'RUB') {
    YookassaPayment::button($amount, $description, $itemnumber, $extra_array);
  }
  
  if(osp_param('cardinity_enabled') == 1 && in_array(osp_currency(), array('EUR', 'GBP', 'USD'))) {
    CardinityPayment::button($amount, $description, $itemnumber, $extra_array);
  }
  
  if(osp_param('securionpay_enabled') == 1) {
    SecurionpayPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('begateway_enabled') == 1) {
    BeGatewayPayment::button($amount, $description, $itemnumber, $extra_array);
  }

  if(osp_param('makecommerce_enabled') == 1) {
    MakeCommercePayment::button($amount, $description, $itemnumber, $extra_array);
  }
  
  if(class_exists('ExpresspayPayment') && osp_param('expresspay_enabled') == 1) {
    ExpresspayPayment::button($amount, $description, $itemnumber, $extra_array);
  }
}


// JAVASCRIPT PAYMENT BUTTONS
function osp_buttons_js() {
  if(osp_param('paypal_enabled') == 1) {
    $html  = '<div name="result_div" id="result_div"></div>';
    $html .= '<script type="text/javascript">';
    $html .= 'var rd = document.getElementById("result_div");';
    $html .= '</script>';
    echo $html;     
  }
  
  if(osp_param('braintree_enabled') == 1) { 
    BraintreePayment::dialogJS();
  }
  
  if(osp_param('stripe_enabled') == 1) { 
    StripePayment::dialogJS();
  }

  if(osp_param('authorizenet_enabled') == 1 && osp_currency() == 'USD') { 
    AuthorizenetPaymentOSP::dialogJS();
  }

  if(osp_param('twocheckout_enabled') == 1) { 
    if(osp_param('twocheckout_type') == '' || osp_param('twocheckout_type') == 'onsite') {
      TwoCheckoutPayment::dialogJS();
    }
  }

  if(osp_param('payumoney_enabled') == 1 && osp_currency() == 'INR') { 
    PayumoneyPayment::dialogJS();
  }
  
  if(osp_param('begateway_enabled') == 1) {
    BeGatewayPayment::dialogJS();
  }

  if(class_exists('ExpresspayPayment') && osp_param('expresspay_enabled') == 1) {
    ExpresspayPayment::dialogJS();
  }
  
  //if(osp_param('przelewy24_enabled') == 1 && in_array(osp_currency(), array('PLN', 'EUR', 'CZK'))) { 
  //  Przelewy24Payment::dialogJS();
  //}
}


// GET USER PACKS
function osp_get_user_packs($user_id = NULL){
  return ModelOSP::newInstance()->getPacks(osp_get_user_group($user_id));
}


// BLOCK ITEM IMAGES IF NOT PAID
function osp_manage_images($item_id = '') {
  if(osc_is_ad_page()) {
    if($item_id == '' || $item_id <= 0) {
      $item_id = osc_item_id();
    }

    if($item_id <> '' && $item_id > 0) {
      //if(osp_fee_is_allowed(OSP_TYPE_IMAGE) && !osp_fee_is_paid(OSP_TYPE_IMAGE, $item_id) && osp_fee_exists(OSP_TYPE_IMAGE, $item_id) && osp_get_fee(OSP_TYPE_IMAGE, 1, $item_id) > 0) {
      if(osp_fee_is_allowed(OSP_TYPE_IMAGE) && !osp_fee_is_paid(OSP_TYPE_IMAGE, $item_id) && osp_get_fee(OSP_TYPE_IMAGE, 1, $item_id) > 0) {
        View::newInstance()->_exportVariableToView('resources', array());

        if(osc_is_web_user_logged_in()) {
          if(osc_item_user_id() <> '' && osc_item_user_id() > 0 && osc_logged_user_id() == osc_item_user_id()) {
            osc_add_flash_error_message(__('This category require to pay fee to show listing images. In order to show images to customers, go to your account and pay fee.', 'osclass_pay'));
          }
        }
      }
    }
  } else if( osc_is_search_page() || osc_is_home_page() || (osc_get_osclass_location() == 'user' && osc_get_osclass_section() == 'pub_profile')) {
    $item_id = osc_item_id();

    if($item_id <> '' && $item_id > 0) {
      //if(osp_fee_is_allowed(OSP_TYPE_IMAGE) && !osp_fee_is_paid(OSP_TYPE_IMAGE, $item_id) && osp_fee_exists(OSP_TYPE_IMAGE, $item_id) && osp_get_fee(OSP_TYPE_IMAGE, 1, $item_id) > 0) {
      if(osp_fee_is_allowed(OSP_TYPE_IMAGE) && !osp_fee_is_paid(OSP_TYPE_IMAGE, $item_id) && osp_get_fee(OSP_TYPE_IMAGE, 1, $item_id) > 0) {
        $no_image = array();
        $no_image[] = array('pk_i_id' => 0, 'fk_i_item_id' => $item_id, 's_name' => 'no-image', 's_extension' => 'png', 's_content_type' => 'image/png', 's_path' => 'oc-content/plugins/osclass_pay/img/no-image/');

        View::newInstance()->_exportVariableToView('resources', $no_image);
      }
    }
  }
}


osc_add_hook('header', 'osp_manage_images');
osc_add_hook('highlight_class', 'osp_manage_images');



// HIDE LISTINGS WITHOUT FEE PAID MANUALLY USING HIGHLIGHT CLASS
function osp_item_filter_style($item_id = '') {
  if($item_id == '' || $item_id <= 0) {
    $item_id = osc_item_id();
  }

  if(osp_param('publish_allow') == 1 && $item_id > 0) {  //&& osp_param('publish_item_disable') <> 1
    if(!osp_fee_is_paid(OSP_TYPE_PUBLISH, $item_id) && osp_fee_exists(OSP_TYPE_PUBLISH, $item_id)) {
      echo ' osp-item-not-paid ';
    }
  }
}

osc_add_hook('highlight_class', 'osp_item_filter_style');



// HIGHLIGHT LISTING IF PAID
function osp_item_highlight($item_id = '') { 
  if($item_id == '' || $item_id <= 0) {
    $item_id = osc_item_id();
  }

  if($item_id == '' || $item_id <= 0) {
    $item_id = osc_premium_id();
  }

  if($item_id <> '' && $item_id > 0) {
    if(osp_fee_is_allowed(OSP_TYPE_HIGHLIGHT) && osp_fee_is_paid(OSP_TYPE_HIGHLIGHT, $item_id) && osp_fee_exists(OSP_TYPE_HIGHLIGHT, $item_id)) {
      echo ' osp-item-is-highlight ';
    }
  }
}

osc_add_hook('highlight_class', 'osp_item_highlight');


function osp_item_styles() {
  $css  = '<style>';
 
  if(osp_fee_is_allowed(OSP_TYPE_PUBLISH)) {
    $css .= 'body:not(.user-items):not(.user-dashboard):not(#user-dashboard):not(#user-dashboard):not(.body-ua) .osp-item-not-paid {display:none!important;visibility:hidden;opacity:0;} ';
  }

  if(osp_fee_is_allowed(OSP_TYPE_HIGHLIGHT)) {
    $css .= '.osp-item-is-highlight, .osp-item-is-highlight .simple-wrap { ' . (osp_param('highlight_color') <> '' ? 'background:' . osp_param('highlight_color') . '!important;' : '') . osp_param('highlight_css') . '} ';
  }

  $css .= '</style>';

  echo $css;
}

osc_add_hook('footer', 'osp_item_styles');



// GET FORMATTED CRON RUNS
function osp_get_cron_runs() {
  $runs = array_filter(explode(',', osp_param('cron_runs')));
  $run_text = __('Last 10 cron runs', 'osclass_pay') . ':';

  if(count($runs) >  0) {
    foreach($runs as $r) { 
      $run_text .= "<br/>" . $r; 
    }

    $run_text .= "<br /><br />" . __('Following expirations were checked: Premium, Highlight, Republish, User Membership.', 'osclass_pay');

  } else {
    $run_text = __('Hourly cron was not executed yet. If problem persist, check osclass documentation and contact your hosting provider.', 'osclass_pay');
  }

  return array(__('Last cron run', 'osclass_pay') . ': ' . (isset($runs[0]) ? $runs[0] : __('none', 'osclass_pay')), $run_text);
}


// GET FORMATTED USER CRON RUNS
function osp_get_cron_runs_user() {
  $run = trim(osp_param('cron_runs_user'));
  $text = __('Last per. bonus issued', 'osclass_pay') . ': ' . ($run <> '' ? $run : __('never', 'osclass_pay'));

  if($run <> '') {
    $title = osc_esc_html(__('Last periodical bonus has been send to users on', 'osclass_pay') . ' ' . ($run <> '' ? $run : __('never', 'osclass_pay')));
  } else {
    $title = osc_esc_html(__('Periodical bonus has not been sent yet, check your cron settings and ensure it is working properly.', 'osclass_pay'));
  }

  return array($text, $title);
}


// MB SUPPORTING FUNCTIONS
if(!function_exists('osp_param_update')) {
  function osp_param_update( $param_name, $update_param_name, $type = NULL, $plugin_var_name = NULL ) {
    $val = '';
    if( $type == 'check') {

      // Checkbox input
      if( Params::getParam( $param_name ) == 'on' ) {
        $val = 1;
      } else {
        if( Params::getParam( $update_param_name ) == 'done' ) {
          $val = 0;
        } else {
          $val = ( osc_get_preference( $param_name, $plugin_var_name ) != '' ) ? osc_get_preference( $param_name, $plugin_var_name ) : '';
        }
      }
    } else {

      // Other inputs (text, password, ...)
      if( Params::getParam( $update_param_name ) == 'done' && Params::existParam($param_name)) {
        $val = Params::getParam( $param_name );
      } else {
        if($type == 'value_crypt') {
          $val = ( osc_get_preference( $param_name, $plugin_var_name) != '' ) ? osp_decrypt(osc_get_preference( $param_name, $plugin_var_name )) : '';
        } else {
          $val = ( osc_get_preference( $param_name, $plugin_var_name) != '' ) ? osc_get_preference( $param_name, $plugin_var_name ) : '';
        }
      }
    }


    // If save button was pressed, update param
    if( Params::getParam( $update_param_name ) == 'done' ) {

      if(osc_get_preference( $param_name, $plugin_var_name ) == '') {
        if(!osp_is_demo()) {
          if($type == 'value_crypt') {
            osc_set_preference( $param_name, osp_crypt($val), $plugin_var_name, 'STRING'); 
          } else {
            osc_set_preference( $param_name, $val, $plugin_var_name, 'STRING'); 
          }
        }
      } else {
        $dao_preference = new Preference();

        if(!osp_is_demo()) {
          if($type == 'value_crypt') {
            $dao_preference->update( array( "s_value" => osp_crypt($val) ), array( "s_section" => $plugin_var_name, "s_name" => $param_name ));
          } else {
            $dao_preference->update( array( "s_value" => $val ), array( "s_section" => $plugin_var_name, "s_name" => $param_name ));
          }

          osc_reset_preferences();
          unset($dao_preference);
        }
      }
    }

    return $val;
  }
}


if(!function_exists('mb_generate_rand_int')) {
  function mb_generate_rand_int($length = 18) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
  }
}


if(!function_exists('mb_generate_rand_string')) {
  function mb_generate_rand_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
  }
}


if(!function_exists('message_ok')) {
  function message_ok( $text ) {
    $final  = '<div class="flashmessage flashmessage-ok flashmessage-inline osp-flash">';
    $final .= $text;
    $final .= '</div>';
    echo $final;
  }
}


if(!function_exists('message_error')) {
  function message_error( $text ) {
    $final  = '<div class="flashmessage flashmessage-error flashmessage-inline osp-flash">';
    $final .= $text;
    $final .= '</div>';
    echo $final;
  }
}


if(!function_exists('message_info')) {
  function message_info( $text ) {
    $final  = '<div class="flashmessage flashmessage-info flashmessage-inline osp-flash">';
    $final .= $text;
    $final .= '</div>';
    echo $final;
  }
}


// Cookies work
if(!function_exists('mb_set_cookie')) {
  function mb_set_cookie($name, $val) {
    Cookie::newInstance()->set_expires( 86400 * 30 );
    Cookie::newInstance()->push($name, $val);
    Cookie::newInstance()->set();
  }
}

if(!function_exists('mb_get_cookie')) {
  function mb_get_cookie($name) {
    return Cookie::newInstance()->get_value($name);
  }
}

if(!function_exists('mb_drop_cookie')) {
  function mb_drop_cookie($name) {
    Cookie::newInstance()->pop($name);
  }
}


// OC-ADMIN LOCATIONS
function osp_location_list() {
  $list = array();

  $countries = Country::newInstance()->listAll();


  foreach($countries as $c) {
    $list[] = array('country_code' => $c['pk_c_code'], 'country_name' => $c['s_name'], 'region_id' => '', 'region_name' => '', 'level' => 1);

    $regions = Region::newInstance()->findByCountry($c['pk_c_code']);

    foreach($regions as $r) {
      $list[] = array('country_code' => $c['pk_c_code'], 'country_name' => $c['s_name'], 'region_id' => $r['pk_i_id'], 'region_name' => $r['s_name'], 'level' => 2);
    }
  }

  return $list;
}


// OC-ADMIN CATEGORIES
// CATEGORIES LIST
function osp_category_list() {
  $list = osp_get_categories(Category::newInstance()->toTree());
  return $list;
}



// GET MAIN CATEGORIES
function osp_get_categories($categories) {
  $list = array();

  foreach($categories as $c) {
    $level = 1;
    $list[] = array('pk_i_id' => $c['pk_i_id'], 's_name' => $c['s_name'], 'level' => $level, 'fk_i_parent_id' => $c['fk_i_parent_id'] );
   
    if(isset($c['categories']) && is_array($c['categories'])) {
      $list = array_merge($list, osp_get_subcategories($c['categories'], $level, $c['fk_i_parent_id']));
    }
  }

  return $list;
}



// GET SUBCATEGORIES
function osp_get_subcategories($categories, $level = 0, $parent = 0) {
  $level++;
  $list = array();

  foreach($categories as $c) {
    if($level == 2) {
      $parent = $c['fk_i_parent_id'];
    }

    $list[] = array('pk_i_id' => $c['pk_i_id'], 's_name' => $c['s_name'], 'level' => $level, 'fk_i_parent_id' => $parent );
        
    if(isset($c['categories']) && is_array($c['categories'])) {
      $list = array_merge($list, osp_get_subcategories($c['categories'], $level, $parent));
    }
  }

  return $list;
}



// CREATE CATEGORY TABS
function osp_category_tabs( $level ) {
  $tab = '';

  if( $level == 2) {
    $tab = '<i class="fa fa-angle-right"></i>&nbsp;';
  } else if( $level == 3) {
    $tab = '&nbsp;&nbsp;<i class="fa fa-angle-right"></i><i class="fa fa-angle-right"></i>&nbsp;';
  } else if( $level == 4) {
    $tab = '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-angle-right"></i><i class="fa fa-angle-right"></i><i class="fa fa-angle-right"></i>&nbsp;';
  }

  return $tab;
}


// GET ADMIN URL
function osp_admin_plugin_url($file) {
  return osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=osclass_pay/admin/' . $file;
}



// GET CURRENCY SYMBOL
function osp_currency_symbol($code = '') {
  if($code == '') {
    $cc = strtoupper(osp_currency());
  } else {
    $cc = strtoupper($code);
  }

  $currency = osp_available_currencies();
    
  if(array_key_exists($cc, $currency)){
    if($currency[$cc] <> '') {
      return $currency[$cc];
    }
  }

  return $cc;
}


// GET LIGHT OR DARK TEXT COLOR
function osp_text_color($hex) {
  $r = @hexdec(substr($hex,0,2));
  $g = @hexdec(substr($hex,2,2));
  $b = @hexdec(substr($hex,4,2));

  if($r + $g + $b > 382) {
    return 'rgba(0,0,0,0.6)';
  } else {
    return 'rgba(255,255,255,0.85)';
  }
}


// LIST OF ALL AVAILABLE CURRENCIES AND THEIR CODES
function osp_available_currencies($only_keys = false) {
  $currency = array(
    'AED' => '&#1583;.&#1573;',
    'AFN' => '&#65;&#102;',
    'ALL' => '&#76;&#101;&#107;',
    'AMD' => '',
    'ANG' => '&#402;',
    'AOA' => '&#75;&#122;',
    'ARS' => '&#36;',
    'AUD' => '&#36;',
    'AWG' => '&#402;',
    'AZN' => '&#1084;&#1072;&#1085;',
    'BAM' => '&#75;&#77;',
    'BBD' => '&#36;',
    'BDT' => '&#2547;',
    'BGN' => '&#1083;&#1074;',
    'BHD' => '.&#1583;.&#1576;',
    'BIF' => '&#70;&#66;&#117;',
    'BMD' => '&#36;',
    'BND' => '&#36;',
    'BOB' => '&#36;&#98;',
    'BRL' => '&#82;&#36;',
    'BSD' => '&#36;',
    'BTN' => '&#78;&#117;&#46;',
    'BWP' => '&#80;',
    'BYR' => '&#112;&#46;',
    'BZD' => '&#66;&#90;&#36;',
    'CAD' => '&#36;',
    'CDF' => '&#70;&#67;',
    'CHF' => '&#67;&#72;&#70;',
    'CLF' => '',
    'CLP' => '&#36;',
    'CNY' => '&#165;',
    'COP' => '&#36;',
    'CRC' => '&#8353;',
    'CUP' => '&#8396;',
    'CVE' => '&#36;',
    'CZK' => '&#75;&#269;',
    'DJF' => '&#70;&#100;&#106;',
    'DKK' => '&#107;&#114;',
    'DOP' => '&#82;&#68;&#36;',
    'DZD' => '&#1583;&#1580;',
    'EGP' => '&#163;',
    'ETB' => '&#66;&#114;',
    'EUR' => '&#8364;',
    'FJD' => '&#36;',
    'FKP' => '&#163;',
    'GBP' => '&#163;',
    'GEL' => '&#4314;',
    'GHS' => '&#162;',
    'GIP' => '&#163;',
    'GMD' => '&#68;',
    'GNF' => '&#70;&#71;',
    'GTQ' => '&#81;',
    'GYD' => '&#36;',
    'HKD' => '&#36;',
    'HNL' => '&#76;',
    'HRK' => '&#107;&#110;',
    'HTG' => '&#71;',
    'HUF' => '&#70;&#116;',
    'IDR' => '&#82;&#112;',
    'ILS' => '&#8362;',
    'INR' => '&#8377;',
    'IQD' => '&#1593;.&#1583;',
    'IRR' => '&#65020;',
    'ISK' => '&#107;&#114;',
    'JEP' => '&#163;',
    'JMD' => '&#74;&#36;',
    'JOD' => '&#74;&#68;',
    'JPY' => '&#165;',
    'KES' => '&#75;&#83;&#104;',
    'KGS' => '&#1083;&#1074;',
    'KHR' => '&#6107;',
    'KMF' => '&#67;&#70;',
    'KPW' => '&#8361;',
    'KRW' => '&#8361;',
    'KWD' => '&#1583;.&#1603;',
    'KYD' => '&#36;',
    'KZT' => '&#1083;&#1074;',
    'LAK' => '&#8365;',
    'LBP' => '&#163;',
    'LKR' => '&#8360;',
    'LRD' => '&#36;',
    'LSL' => '&#76;', // ?
    'LTL' => '&#76;&#116;',
    'LVL' => '&#76;&#115;',
    'LYD' => '&#1604;.&#1583;',
    'MAD' => '&#1583;.&#1605;.',
    'MDL' => '&#76;',
    'MGA' => '&#65;&#114;', // ?
    'MKD' => '&#1076;&#1077;&#1085;',
    'MMK' => '&#75;',
    'MNT' => '&#8366;',
    'MOP' => '&#77;&#79;&#80;&#36;',
    'MRO' => '&#85;&#77;',
    'MUR' => '&#8360;',
    'MVR' => '.&#1923;',
    'MWK' => '&#77;&#75;',
    'MXN' => '&#36;',
    'MYR' => '&#82;&#77;',
    'MZN' => '&#77;&#84;',
    'NAD' => '&#36;',
    'NGN' => '&#8358;',
    'NIO' => '&#67;&#36;',
    'NOK' => '&#107;&#114;',
    'NPR' => '&#8360;',
    'NZD' => '&#36;',
    'OMR' => '&#65020;',
    'PAB' => '&#66;&#47;&#46;',
    'PEN' => '&#83;&#47;&#46;',
    'PGK' => '&#75;',
    'PHP' => '&#8369;',
    'PKR' => '&#8360;',
    'PLN' => '&#122;&#322;',
    'PYG' => '&#71;&#115;',
    'QAR' => '&#65020;',
    'RON' => '&#108;&#101;&#105;',
    'RSD' => '&#1044;&#1080;&#1085;&#46;',
    'RUB' => '&#8381;',
    'RWF' => '&#1585;.&#1587;',
    'SAR' => '&#65020;',
    'SBD' => '&#36;',
    'SCR' => '&#8360;',
    'SDG' => '&#163;',
    'SEK' => '&#107;&#114;',
    'SGD' => '&#36;',
    'SHP' => '&#163;',
    'SLL' => '&#76;&#101;',
    'SOS' => '&#83;',
    'SRD' => '&#36;',
    'STD' => '&#68;&#98;',
    'SVC' => '&#36;',
    'SYP' => '&#163;',
    'SZL' => '&#76;',
    'THB' => '&#3647;',
    'TJS' => '&#84;&#74;&#83;',
    'TMT' => '&#109;',
    'TND' => '&#1583;.&#1578;',
    'TOP' => '&#84;&#36;',
    'TRY' => '&#8378;',
    'TTD' => '&#36;',
    'TWD' => '&#78;&#84;&#36;',
    'TZS' => '',
    'UAH' => '&#8372;',
    'UGX' => '&#85;&#83;&#104;',
    'USD' => '&#36;',
    'UYU' => '&#36;&#85;',
    'UZS' => '&#1083;&#1074;',
    'VEF' => '&#66;&#115;',
    'VND' => '&#8363;',
    'VUV' => '&#86;&#84;',
    'WST' => '&#87;&#83;&#36;',
    'XAF' => '&#70;&#67;&#70;&#65;',
    'XCD' => '&#36;',
    'XDR' => '',
    'XOF' => '',
    'XPF' => '&#70;',
    'YER' => '&#65020;',
    'ZAR' => '&#82;',
    'ZMK' => '&#90;&#75;',
    'ZWL' => '&#90;&#36;'
  );

  if($only_keys) {
    return array_keys($currency);
  } else {
    return $currency;
  }
}


// GET PLUGIN PARAMETER
function osp_param($param) {
  return osc_get_preference($param, 'plugin-osclass_pay');
}


// ARRAY TO STRING (for emailing)
function osp_array_to_string($array) {
  $text = '';
  $text = var_export($array, true);
  return $text;
}


// GET CHMOD OF FILE
function osp_get_chmod($path) {
  return intval(substr(decoct(fileperms($path)), -3));
}

// CHECK IF DEMO AND NOT ADMIN
function osp_is_demo() {
  if(defined('DEMO')) {
    if(osc_logged_admin_username() <> 'admin') {
      return true;
    }
  }

  return false;
}

// SIMPLE PHP REDIRECT
function osp_redirect($url) {
  header('Location:'.$url);
  exit;
}


// LIST OF RANKS FOR GROUP IN OC-ADMIN
function osp_admin_group_ranks($group_id, $rank = '') {
  $html = '<select name="group_' . $group_id . '_rank">';

  for($i=0;$i<=10;$i++) {
    $html .= '<option ' . ($rank == $i ? 'selected="selected"' : '') . '>';
    $html .= $i;

    if($i == 0) {
      $html .= ' - ' . __('lowest', 'osclass_pay');
    } else if ($i == 10) { 
      $html .= ' - ' . __('highest', 'osclass_pay');
    }

    $html .= '</option>';
  }

  $html .= '</select>';

  return $html;
}


// GENERATE NAME FOR "ADD TO CART" BUTTON FOR MEMBERSHIP
function osp_group_label($group_id, $rank) {
  if(osc_logged_user_id()) {
    $group = ModelOSP::newInstance()->getUserGroup(osc_logged_user_id());

    if($group > 0) {
      $group_full = ModelOSP::newInstance()->getGroup($group);
      $group_full['i_rank'] = ($group_full['i_rank'] <> '' ? $group_full['i_rank'] : 0);

      if($group == $group_id) {
        return __('Extend Membership', 'osclass_pay');
      } else if($rank < $group_full['i_rank']) {
        return __('Downgrade Plan', 'osclass_pay');
      } else if($rank == $group_full['i_rank']) {
        return __('Become Member', 'osclass_pay');
      } else if($rank > $group_full['i_rank']) {
         return __('Upgrade Plan', 'osclass_pay');
      }   
    }
  }

  return __('Become Member', 'osclass_pay');
}


// CLOSE ALL OPENED HTML TAGS
function osp_closetags($html) {
  preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
  $openedtags = $result[1];
  preg_match_all('#</([a-z]+)>#iU', $html, $result);

  $closedtags = $result[1];
  $len_opened = count($openedtags);

  if (count($closedtags) == $len_opened) {
    return $html;
  }
  
  $openedtags = array_reverse($openedtags);
  for ($i=0; $i < $len_opened; $i++) {
    if (!in_array($openedtags[$i], $closedtags)) {
      $html .= '</'.$openedtags[$i].'>';
    } else {
      unset($closedtags[array_search($openedtags[$i], $closedtags)]);
    }
  }
  
  return $html;
}


// UPDATE CURRENCY RATES
function osp_get_currency_rates() {
  $cur_from = ModelOSP::newInstance()->getCurrencies();
  $cur_to = $cur_from;
  $errors = array();

  // From
  if(count($cur_from) > 0) {
    foreach($cur_from as $f) {
      
      // To
      foreach($cur_to as $t) {
        if($f['pk_c_code'] == $t['pk_c_code']) {
          ModelOSP::newInstance()->replaceCurrency($f['pk_c_code'], $t['pk_c_code'], 1.0);

        } else {
          $url = 'http://api.exchangeratesapi.io/latest?access_key=' . osp_param('exchangeratesapikey') . '&base=' . strtoupper($f['pk_c_code']) . '&symbols=' . strtoupper($t['pk_c_code']);
          $data = file_get_contents($url);

          if($data) {
            $content = json_decode($data, true);
          }

          if(isset($content['error']) && isset($content['error']['code']) && $content['error']['code'] != '') {
            $errors[] = $content['error']['code'] . ': ' . $content['error']['type'] . (@$content['error']['info'] <> '' ? ' - ' . $content['error']['info'] : '') . ' (' . $f['pk_c_code'] . ' => ' . $t['pk_c_code'] . ')';
          }

          if(isset($content['rates'])) {
            $rate = @$content['rates'][$t['pk_c_code']];

            if($rate <> '' && $rate > 0) {
              ModelOSP::newInstance()->replaceCurrency($f['pk_c_code'], $t['pk_c_code'], $rate);
            }
          }
        }
      }
    }
  }
  
  if(empty($errors)) {
    return true;
  } else {
    return '<div>' . implode('<div></div>', $errors) . '</div>';
  }
}

osc_add_hook('cron_daily', 'osp_get_currency_rates');



// CONVERT PRICE
function osp_convert($price, $currency = '') {
  if($currency == '') {
    $currency = osc_item_currency();
  }

  if($currency == '' || $price == '' || $price == 0) {
    return 0;
  }
  
  if(osp_param('use_exchange_rates_plugin') == 1 && function_exists('exr_convert')) {
    return exr_convert($currency, osp_currency(), $price);       // from, to, amount
    
  } else {
    $rate = ModelOSP::newInstance()->getRate($currency);
    return $price * $rate;
  }
}

// STRIPE MULTIPLIER
function osp_stripe_multiplier($currency = '') {
  $currency = ($currency == '' ? osp_currency() : $currency);

  if($currency == 'JPY' || $currency == 'MXN' || $currency == 'MYR') {
    $mult = 1;
  } else { 
    $mult = 100;
  }

  return $mult;
}



// CATEGORIES WORK
function osp_cat_tree($list = array()) {
  if(!is_array($list) || empty($list)) {
    $list = Category::newInstance()->listAll();
  }

  $array = array();
  //$root = Category::newInstance()->findRootCategoriesEnabled();

  foreach($list as $c) {
    if($c['fk_i_parent_id'] <= 0) {
      $array[$c['pk_i_id']] = array('pk_i_id' => $c['pk_i_id'], 's_name' => $c['s_name']);
      $array[$c['pk_i_id']]['sub'] = osp_cat_sub($list, $c['pk_i_id']);
    }
  }

  return $array;
}

function osp_cat_sub($list, $parent_id) {
  $array = array();
  //$cats = Category::newInstance()->findSubcategories($id);

  if(count($list) > 0) {
    foreach($list as $c) {
      if($c['fk_i_parent_id'] == $parent_id) {  echo $c['s_name'];
        $array[$c['pk_i_id']] = array('pk_i_id' => $c['pk_i_id'], 's_name' => $c['s_name']);
        $array[$c['pk_i_id']]['sub'] = osp_cat_sub($list, $c['pk_i_id']);
      }
    }
  }
      
  return $array;
}

function osp_cat_list($selected = array(), $categories = '', $level = 0) {
  if($categories == '' || $level == 0) {
    $categories = osp_cat_tree($categories);
  }


  foreach($categories as $c) {
    echo '<option value="' . $c['pk_i_id'] . '" ' . (in_array($c['pk_i_id'], $selected) ? 'selected="selected"' : '') . '>' . str_repeat('-', $level) . ($level > 0 ? ' ' : '') . $c['s_name'] . '</option>';

    if(@count($c['sub']) > 0) {
      osp_cat_list($selected, $c['sub'], $level + 1);
    }
  }
}


function osp_list_values_ol($values) {
 if(count($values) > 0 && is_array($values)) {
    foreach($values as $v) {
      ?>

      <li class="mb-val" id="val_<?php echo $v['pk_i_id']; ?>">
        <?php osp_div_value($v); ?>
      
        <ol>
          <?php 
            if(isset($v['values']) && count($v['values']) > 0) { 
              osp_list_values_ol($v['values']); 
            }
          ?>
        </ol>
      </li>
    <?php
    }
  }
}
?>