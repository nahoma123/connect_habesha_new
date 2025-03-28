<?php
/*
 * Copyright 2014 Osclass
 * Copyright 2023 Osclass by OsclassPoint.com
 *
 * Osclass maintained & developed by OsclassPoint.com
 * You may not use this file except in compliance with the License.
 * You may download copy of Osclass at
 *
 *     https://osclass-classifieds.com/download
 *
 * Do not edit or add to this file if you wish to upgrade Osclass to newer
 * versions in the future. Software is distributed on an "AS IS" basis, without
 * warranties or conditions of any kind, either express or implied. Do not remove
 * this NOTICE section as it contains license information and copyrights.
 */


/**
 * Helper Utils
 * @package Osclass
 * @subpackage Helpers
 * @author Osclass
 */


/**
 * Getting from View the $key index
 *
 * @param string $key
 * @return array
 */
function __get($key) {
  return View::newInstance()->_get($key);
}

/**
 * Get variable from $_GET or $_POST
 *
 * @param string $key
 * @return mixed
 */
function osc_get_param($key) {
  return Params::getParam($key);
}

/**
 * Generic function for view layer, return the $field of $item
 * with specific $locale
 *
 * @param array $item
 * @param string $field
 * @param string $locale
 * @return string
 */
function osc_field($item, $field, $locale) {
  if($item !== null && is_array($item) && !empty($item)) {
    if($locale == '') {
      if(isset($item[$field])) {
        return $item[$field];
      }
    } else {
      //if(isset($item["locale"]) && !empty($item['locale']) && isset($item["locale"][$locale]) && isset($item["locale"][$locale][$field])) {
      if(isset($item['locale']) && isset($item['locale'][$locale]) && isset($item['locale'][$locale][$field]) && $item['locale'][$locale][$field] <> '') {
        return $item['locale'][$locale][$field];
      }else{
        if(isset($item['locale'])){
          foreach($item['locale'] as $locale => $data) {
            if(isset($item['locale'][$locale][$field])) {
              return $item['locale'][$locale][$field];
            }
          }
        }
      }
    }
  }
  
  return '';
}

/**
 * Print all widgets belonging to $location
 *
 * @param string $location
 * @return void
 */
function osc_show_widgets($location) {
  $widgets = Widget::newInstance()->findByLocation($location);
  foreach ($widgets as $w)
    echo $w['s_content'];
}

/**
 * Print all widgets named $description
 *
 * @param string $description
 * @return void
 */
function osc_show_widgets_by_description($description) {
  $widgets = Widget::newInstance()->findByDescription($description);
  foreach ($widgets as $w)
    echo $w['s_content'];
}

/**
 * Print recaptcha html, if $section = "recover_password"
 * set 'recover_time' at session.
 *
 * @param  string $section
 * @return void
 */
function osc_show_recaptcha($section = '') {
  if( osc_recaptcha_public_key() ) {
      switch($section) {
        case('recover_password'):
          Session::newInstance()->_set('recover_captcha_not_set',0);
          $time  = (int)Session::newInstance()->_get('recover_time');
          if((time()-$time)<=1200) {
            echo _osc_recaptcha_get_html(osc_recaptcha_public_key(), substr(osc_language(), 0, 2))."<br />";
          }
          else{
            Session::newInstance()->_set('recover_captcha_not_set',1);
          }
          break;

        default:
          echo _osc_recaptcha_get_html(osc_recaptcha_public_key(), substr(osc_language(), 0, 2))."<br />";
          break;
      }
  }
}

function _osc_recaptcha_get_html($siteKey, $lang) {
  echo '<div class="g-recaptcha" data-sitekey="' . $siteKey . '"></div>';
  echo '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=' . $lang . '"></script>';
}

/**
 * Formats the date using the appropiate format.
 *
 * @param string $date
 * @return string
 */
function osc_format_date($date, $dateformat = null) {
  if($dateformat==null) {
    $dateformat = osc_date_format();
  }

  $month = array('', __('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December'));
  $month_short = array('', __('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'), __('Jul'), __('Aug'), __('Sep'), __('Oct'), __('Nov'), __('Dec'));
  $day = array('', __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'), __('Sunday'));
  $day_short = array('', __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun'));
  $ampm = array('AM' => __('AM'), 'PM' => __('PM'), 'am' => __('am'), 'pm' => __('pm'));


  $time = strtotime($date);
  $dateformat = preg_replace('|(?<!\\\)F|', osc_escape_string($month[date('n', $time)]), $dateformat);
  $dateformat = preg_replace('|(?<!\\\)M|', osc_escape_string($month_short[date('n', $time)]), $dateformat);
  $dateformat = preg_replace('|(?<!\\\)l|', osc_escape_string($day[date('N', $time)]), $dateformat);
  $dateformat = preg_replace('|(?<!\\\)D|', osc_escape_string($day_short[date('N', $time)]), $dateformat);
  $dateformat = preg_replace('|(?<!\\\)A|', osc_escape_string($ampm[date('A', $time)]), $dateformat);
  $dateformat = preg_replace('|(?<!\\\)a|', osc_escape_string($ampm[date('a', $time)]), $dateformat);
  return date($dateformat, $time);
}


/**
 * Escapes letters and numbers of a string
 *
 * @since 2.4
 * @param string $string
 * @return string
 */
function osc_escape_string($string) {
  $string = preg_replace('/^([0-9])/', '\\\\\\\\\1', $string);
  $string = preg_replace('/([a-z])/i', '\\\\\1', $string);
  return $string;
}

/**
 * Prints the user's account menu
 *
 * @param array $options array with options of the form array('name' => 'display name', 'url' => 'url of link')
 * @return void
 */
function osc_private_user_menu($options = null)
{
  if($options == null) {
    $options = array();
    $options[] = array('name' => __('Public Profile'), 'url' => osc_user_public_profile_url(osc_logged_user_id()), 'class' => 'opt_publicprofile');
    $options[] = array('name' => __('Dashboard'), 'url' => osc_user_dashboard_url(), 'class' => 'opt_dashboard');
    $options[] = array('name' => __('Manage your listings'), 'url' => osc_user_list_items_url(), 'class' => 'opt_items');
    $options[] = array('name' => __('Manage your alerts'), 'url' => osc_user_alerts_url(), 'class' => 'opt_alerts');
    $options[] = array('name' => __('My profile'), 'url' => osc_user_profile_url(), 'class' => 'opt_account');
    $options[] = array('name' => __('Log out'), 'url' => osc_user_logout_url(), 'class' => 'opt_logout');
  }

  $options = osc_apply_filter('user_menu_filter', $options);

  echo '<script type="text/javascript">';
  echo '$(".user_menu > :first-child").addClass("first");';
  echo '$(".user_menu > :last-child").addClass("last");';
  echo '</script>';
  echo '<ul class="user_menu">';

  $var_l = count($options);
  for($var_o = 0; $var_o < ($var_l-1); $var_o++) {
    echo '<li class="' . $options[$var_o]['class'] . '" ><a href="' . $options[$var_o]['url'] . '" >' . $options[$var_o]['name'] . '</a></li>';
  }

  osc_run_hook('user_menu');

  echo '<li class="' . $options[$var_l-1]['class'] . '" ><a href="' . $options[$var_l-1]['url'] . '" >' . $options[$var_l-1]['name'] . '</a></li>';

  echo '</ul>';
}

/**
 * Gets prepared text, with:
 * - higlight search pattern and search city
 * - maxim length of text
 *
 * @param string $txt
 * @param int  $len
 * @param string $start_tag
 * @param string $end_tag
 * @return string
 */
function osc_highlight($txt, $len = 300, $start_tag = '<strong>', $end_tag = '</strong>') {
  $txt = strip_tags((string)$txt);
  $txt = str_replace(array("\n\r","\r\n","\n","\r","\t"), ' ', $txt);
  $txt = trim($txt);
  $txt = preg_replace('/\s+/', ' ', $txt);
  if( mb_strlen($txt, 'UTF-8') > $len ) {
    $txt = mb_substr($txt, 0, $len, 'UTF-8') . "...";
  }
  $query = osc_search_pattern();
  $query = str_replace(array('(',')','+','-','~','>','<'), array('','','','','','',''), $query);

  $query = str_replace(
    array('\\', '^', '$', '.', '[', '|', '?', '*', '{', '}', '/', ']'),
    array('\\\\', '\\^', '\\$', '\\.', '\\[', '\\|', '\\?', '\\*', '\\{', '\\}', '\\/', '\\]'),
    $query);

  $query = preg_replace('/\s+/', ' ', $query);

  $words = array();
  if(preg_match_all('/"([^"]*)"/', $query, $matches)) {
    $l = count($matches[1]);
    for($k=0;$k<$l;$k++) {
      $words[] = $matches[1][$k];
    }
  }

  $query = trim(preg_replace('/\s+/', ' ', preg_replace('/"([^"]*)"/', '', $query)));
  $words = array_merge($words, explode(" ", $query));

  foreach($words as $word) {
    if($word!='') {
      $txt = preg_replace("/(\PL|\s+|^)($word)(\PL|\s+|$)/i", "$01" . $start_tag . "$02". $end_tag . "$03", $txt);
    }
  }
  return $txt;
}


/**
 *
 */
function osc_get_http_referer() {
  $ref = Rewrite::newInstance()->get_http_referer();
  
  if($ref!='') {
    return $ref;
  } else if(Session::newInstance()->_getReferer()!='') {
    return Session::newInstance()->_getReferer();
  } else if(Params::existServerParam('HTTP_REFERER')){
    if(filter_var(Params::getServerParam('HTTP_REFERER', false, false), FILTER_VALIDATE_URL)) {
      return Params::getServerParam('HTTP_REFERER', false, false);
    }
  }
  
  return '';
}

function osc_add_route($id, $regexp, $url, $file, $user_menu = false, $location = "custom", $section = "custom", $title = "Custom") {
  Rewrite::newInstance()->addRoute($id, $regexp, $url, $file, $user_menu, $location, $section, $title);
}

/**
 * Set current subdomain parameter into array. There can only be 1 array key at same time!
 *
 * @return array
 */
function osc_get_subdomain_params() {
  $options = array();
  
  if(osc_subdomain_slug() != '') {
    if(osc_subdomain_type() == 'country' && Params::getParam('sCountry') != '') {
      $options['sCountry'] = Params::getParam('sCountry');
    } else if(osc_subdomain_type() == 'region' && Params::getParam('sRegion') != '') {
      $options['sRegion'] = Params::getParam('sRegion');
    } else if(osc_subdomain_type() == 'city' && Params::getParam('sCity') != '') {
      $options['sCity'] = Params::getParam('sCity');
    } else if(osc_subdomain_type() == 'category' && Params::getParam('sCategory') != '') {
      $options['sCategory'] = Params::getParam('sCategory');
    } else if(osc_subdomain_type() == 'user' && Params::getParam('sUser') != '') {
      $options['sUser'] = Params::getParam('sUser');
    } else if(osc_subdomain_type() == 'language' && Params::getParam('sLanguage') != '') {
      $options['sLanguage'] = Params::getParam('sLanguage');
    }
  }
  
  return $options;
}


/**
 * Generate different versions of filter conditions for SQL based on subdomains
 *
 * @return array
 */
function osc_subdomains_filter_conditions() {
  $output = array(
    'enabled' => osc_subdomain_enabled(),
    'is_subdomain' => osc_is_subdomain(),
    'type' => osc_subdomain_type(),
    'id' => osc_subdomain_id(),
    'name' => osc_subdomain_name(),
    'slug' => osc_subdomain_slug(),
    'param' => osc_subdomain_param(),
    'in_clause' => '',
    'sql_in' => '',
    'sql_where' => '',
    'tables_where' => array()
  ); 

  $id = osc_subdomain_id();
  $type = osc_subdomain_type();


  $in_clause = '';
  $sql_in = '';
  $sql_where = '';
  $tables_where = array();
  $tree_in = '';
  
  
  if(osc_subdomain_enabled() && osc_is_subdomain()) {
    // create list that can be place as: column in ( .. function .. )
    // create where statement in format t_table.column = "val"
    if($type == 'country' && $id <> '') {
      $sql_in = sprintf('SELECT fk_i_item_id FROM %st_item_location WHERE fk_c_country_code = "%s"', DB_TABLE_PREFIX, osc_esc_html($id));
      $tables_where[] = DB_TABLE_PREFIX.'t_item_location';
      $sql_where = sprintf(' AND %st_item_location.fk_c_country_code = "%s"', DB_TABLE_PREFIX, osc_esc_html($id));
      $in_clause = '("' . osc_esc_html($id) . '")';
      
    } else if($type == 'region' && $id > 0) {
      $sql_in = sprintf('SELECT fk_i_item_id FROM %st_item_location WHERE fk_i_region_id = %d', DB_TABLE_PREFIX, osc_esc_html($id));
      $tables_where[] = DB_TABLE_PREFIX.'t_item_location';
      $sql_where = sprintf(' AND %st_item_location.fk_i_region_id = %d', DB_TABLE_PREFIX, osc_esc_html($id));
      $in_clause = '(' . osc_esc_html($id) . ')';
      
    } else if($type == 'city' && $id > 0) {
      $sql_in = sprintf('SELECT fk_i_item_id FROM %st_item_location WHERE fk_i_city_id = %d', DB_TABLE_PREFIX, osc_esc_html($id));
      $tables_where[] = DB_TABLE_PREFIX.'t_item_location';
      $sql_where = sprintf(' AND %st_item_location.fk_i_city_id = %d', DB_TABLE_PREFIX, osc_esc_html($id));
      $in_clause = '(' . osc_esc_html($id) . ')';

    } else if($type == 'language' && $id <> '') {
      $sql_in = sprintf('SELECT fk_i_item_id FROM %st_item_description WHERE fk_c_locale_code = "%s"', DB_TABLE_PREFIX, osc_esc_html($id));
      $tables_where[] = DB_TABLE_PREFIX.'t_locale';
      $sql_where = sprintf(' AND %st_locale.fk_c_locale_code = "%s"', DB_TABLE_PREFIX, osc_esc_html($id));
      $in_clause = '(' . osc_esc_html($id) . ')';
      
    } else if($type == 'category' && $id > 0) {
      // we need to prepare tree with cat ID and all it's subcategories
      $tree_details = Category::newInstance()->toSubTree(1);
      $tree = array();
      
      if(is_array($tree_details) && count($tree_details) > 0) {
        $tree = array_column($tree_details, 'pk_i_id');
      }
      
      $tree[] = osc_esc_html($id);
      $tree_in = implode(',', $tree);
      
      $sql_in = sprintf('SELECT pk_i_id FROM %st_item WHERE fk_i_category_id in (%s)', DB_TABLE_PREFIX, osc_esc_html($tree_in));
      $sql_where = sprintf(' AND %st_item.fk_i_category_id in (%s)', DB_TABLE_PREFIX, osc_esc_html($tree_in));
      $in_clause = '(' . osc_esc_html($tree_in) . ')';
      
    } else if($type == 'user' && $id > 0) {
      $sql_in = sprintf('SELECT pk_i_id FROM %st_item WHERE fk_i_user_id = %d', DB_TABLE_PREFIX, osc_esc_html($id));
      $sql_where = sprintf(' AND %st_item.fk_i_user_id = %d', DB_TABLE_PREFIX, osc_esc_html($id));
      $in_clause = '(' . osc_esc_html($id) . ')';
    }


    $tables_where = array_unique($tables_where);
    
    $output['in_clause'] = $in_clause;
    $output['sql_in'] = $sql_in;
    $output['sql_where'] = $sql_where;
    $output['tables_where'] = $tables_where;
  }
  
  return $output;
}


/**
 * Get URL of location files JSON.
 *
 * @return string
 */
function osc_get_locations_json_url() {
  return 'https://osclass-classifieds.com/extras/locations_v3/list.json';
}

/**
 * Get URL of location SQL.
 *
 * @param string $location
 * @return string
 */
function osc_get_locations_sql_url($location) {
  $location = rawurlencode($location);
  return 'https://osclass-classifieds.com/extras/locations_v3/'.$location;
}

/**
 * Get URL of language files JSON.
 *
 * @return string
 */
function osc_get_languages_json_url() {
  return 'https://osclass-classifieds.com/extras/languages/list.json';
}

/**
 * Get URL of language ZIP.
 *
 * @param string $language
 * @return string
 */
function osc_get_language_zip_url($language) {
  $location = rawurlencode($language);
  return 'https://osclass-classifieds.com/extras/languages/'.$language;
}