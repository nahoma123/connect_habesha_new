<?php

// NEW BANK TRANSFER INITIATED
function osp_email_new_bt($transaction_id) {
  osp_include_mailer();

  $mPages = new Page() ;
  $aPage = $mPages->findByInternalName('osp_email_bt_new') ;
  $locale = osc_current_user_locale() ;
  $content = array();
  
  if(isset($aPage['locale'][$locale]['s_title'])) {
    $content = $aPage['locale'][$locale];
  } else {
    $content = current($aPage['locale'] <> '' ? $aPage['locale'] : array());
  }


  $bt = ModelOSP::newInstance()->getBankTransferByTransactionId($transaction_id);
  $user = User::newInstance()->findByPrimaryKey($bt['i_user_id']);
  $data = osp_get_custom($bt['s_extra']);
  
  if(@$bt['i_user_id'] <= 0 || $user['s_email'] == '') {
    $user = array(
      's_name' => @$data['name'],
      's_email' => @$data['email']
    );
  }

  $user_id = @$data['user'];
  $item_id = @$data['itemid'];
  
  $words = array();
  $words[] = array('{ACCOUNT}', '{TRANSACTION_ID}', '{VARIABLE_SYMBOL}', '{PRICE}', '{CONTACT_NAME}', '{CONTACT_EMAIL}', '{WEB_URL}', '{WEB_TITLE}');
  $words[] = array(osp_param('bt_iban'), $bt['s_transaction'], $bt['s_variable'], osp_format_price($bt['f_price']), $user['s_name'], $user['s_email'], osc_base_url(), osc_page_title());


  $title = osc_mailBeauty($content['s_title'], $words) ;
  $body  = osc_mailBeauty($content['s_text'], $words) ;

  if(trim($user['s_email']) <> '') {
    $emailParams = array(
      'subject' => $title,
      'to' => $user['s_email'],
      'to_name' => $user['s_name'],
      'body' => $body,
      'alt_body' => $body
    );

    osc_sendMail($emailParams);
  }
}



// PUBLISH LISTING - SEND EMAIL WITH AVAILABLE PROMOTIONS
function osp_email_promote($item) {
  if(!isset($item['pk_i_id']) || $item['pk_i_id'] <= 0) {
    return false;
  }
  
  osp_include_mailer();

  $mPages = new Page() ;
  $aPage = $mPages->findByInternalName('osp_email_promote') ;
  $locale = osc_current_user_locale() ;
  $content = array();
  
  if(isset($aPage['locale'][$locale]['s_title'])) {
    $content = $aPage['locale'][$locale];
  } else {
    $content = current($aPage['locale'] <> '' ? $aPage['locale'] : array());
  }

  $item_url = osc_item_url() ;
  $item_link = '<a href="' . $item_url . '" >' . $item['s_title'] . '</a>';

  $promote_url = osc_base_url(true) . '?page=custom&route=osp-item-pay&itemId=' . $item['pk_i_id'];
  $promote_link = '<a href="' . $promote_url . '" >' . $promote_url . '</a>';

  $account_url = osc_base_url(true) . '?page=custom&route=osp-item';
  $account_link = '<a href="' . $account_url . '" >' . __('My account - Promotions section', 'osclass_pay') . '</a>';

  $words = array();
  $words[] = array('{ITEM_ID}', '{CONTACT_NAME}', '{CONTACT_EMAIL}', '{WEB_URL}', '{ITEM_TITLE}', '{ITEM_URL}', '{WEB_TITLE}', '{ACCOUNT_LINK}', '{PROMOTE_LINK}', '{START_PUBLISH}', '{END_PUBLISH}', '{START_IMAGE}', '{END_IMAGE}', '{START_PREMIUM}', '{END_PREMIUM}', '{START_HIGHLIGHT}', '{END_HIGHLIGHT}', '{START_MOVETOTOP}', '{END_MOVETOTOP}', '{START_REPUBLISH}', '{END_REPUBLISH}');
  $words[] = array($item['pk_i_id'], $item['s_contact_name'], $item['s_contact_email'], osc_base_url(), $item['s_title'], $item_link, osc_page_title(), $account_link, $promote_link, '', '', '', '', '', '', '', '', '', '', '', '');


  $types = array(OSP_TYPE_PUBLISH, OSP_TYPE_IMAGE, OSP_TYPE_PREMIUM, OSP_TYPE_HIGHLIGHT, OSP_TYPE_TOP, OSP_TYPE_REPUBLISH);

  $count = 0;
  foreach($types as $type) {
    $fee = osp_get_fee($type, 1, $item['pk_i_id']);

    if(!osp_fee_is_allowed($type) || $fee <= 0) {
      if($type == OSP_TYPE_PUBLISH) {
        //$content['s_text'] = preg_replace('|{START_PUBLISH}(.*){END_PUBLISH}|', '', $content['s_text']);
        $content['s_text'] = preg_replace('/{START_PUBLISH}[\s\S]+?{END_PUBLISH}/', '', $content['s_text']);
        
      } else if($type == OSP_TYPE_IMAGE) {
        //$content['s_text'] = preg_replace('|{START_IMAGE}(.*){END_IMAGE}|', '', $content['s_text']);
        $content['s_text'] = preg_replace('/{START_IMAGE}[\s\S]+?{END_IMAGE}/', '', $content['s_text']);
        
      } else if($type == OSP_TYPE_PREMIUM) {
        //$content['s_text'] = preg_replace('|{START_PREMIUM}(.*){END_PREMIUM}|', '', $content['s_text']);
        $content['s_text'] = preg_replace('/{START_PREMIUM}[\s\S]+?{END_PREMIUM}/', '', $content['s_text']);
        
      } else if($type == OSP_TYPE_HIGHLIGHT) {
        //$content['s_text'] = preg_replace('|{START_HIGHLIGHT}(.*){END_HIGHLIGHT}|', '', $content['s_text']);
        $content['s_text'] = preg_replace('/{START_HIGHLIGHT}[\s\S]+?{END_HIGHLIGHT}/', '', $content['s_text']);
        
      } else if($type == OSP_TYPE_TOP) {
        //$content['s_text'] = preg_replace('|{START_MOVETOTOP}(.*){END_MOVETOTOP}|', '', $content['s_text']);
        $content['s_text'] = preg_replace('/{START_MOVETOTOP}[\s\S]+?{END_MOVETOTOP}/', '', $content['s_text']);
        
      } else if($type == OSP_TYPE_REPUBLISH) {
        //$content['s_text'] = preg_replace('|{START_REPUBLISH}(.*){END_REPUBLISH}|', '', $content['s_text']);
        $content['s_text'] = preg_replace('/{START_REPUBLISH}[\s\S]+?{END_REPUBLISH}/', '', $content['s_text']);

      }
    } else {
      $count++;
    }
  }

  if($count == 0) {
    return false;
  }

  $title = osc_mailBeauty($content['s_title'], $words) ;
  $body  = osc_mailBeauty($content['s_text'], $words) ;

  $emailParams = array(
    'subject' => $title,
    'to' => $item['s_contact_email'],
    'to_name' => $item['s_contact_name'],
    'body' => $body,
    'alt_body' => $body
  );

  osc_sendMail($emailParams);
}



// PROMOTION HAS EXPIRED
function osp_email_expired($item, $notify) {
  osp_include_mailer();

  $mPages = new Page() ;
  $aPage = $mPages->findByInternalName('osp_email_expired') ;
  $locale = osc_current_user_locale() ;
  $content = array();
  
  if(isset($aPage['locale'][$locale]['s_title'])) {
    $content = $aPage['locale'][$locale];
  } else {
    $content = current($aPage['locale'] <> '' ? $aPage['locale'] : array());
  }
  
  if(!is_array($item) || empty($item) || !isset($item['pk_i_id']) || @$item['pk_i_id'] <= 0) {
    return false;
  }

  //$item_url = osc_item_url();
  $item_url = osc_item_url_ns($item['pk_i_id']) ;


  $item_link = '<a href="' . $item_url . '" >' . $item['s_title'] . '</a>';

  $promote_url = osc_base_url(true) . '?page=custom&route=osp-item-pay&itemId=' . $item['pk_i_id'];
  $promote_link = '<a href="' . $promote_url . '" >' . $promote_url . '</a>';

  $account_url = osc_base_url(true) . '?page=custom&route=osp-item';
  $account_link = '<a href="' . $account_url . '" >' . __('My account - Promotions section', 'osclass_pay') . '</a>';


  $words = array();
  $words[] = array('{ITEM_ID}', '{CONTACT_NAME}', '{CONTACT_EMAIL}', '{WEB_URL}', '{ITEM_TITLE}', '{ITEM_URL}', '{WEB_TITLE}', '{ACCOUNT_LINK}', '{PROMOTE_LINK}', '{START_PREMIUM}', '{END_PREMIUM}', '{START_HIGHLIGHT}', '{END_HIGHLIGHT}', '{START_REPUBLISH}', '{END_REPUBLISH}');
  $words[] = array($item['pk_i_id'], $item['s_contact_name'], $item['s_contact_email'], osc_base_url(), $item['s_title'], $item_link, osc_page_title(), $account_link, $promote_link, '', '', '', '', '', '');

  $types = array(OSP_TYPE_PUBLISH, OSP_TYPE_IMAGE, OSP_TYPE_PREMIUM, OSP_TYPE_HIGHLIGHT, OSP_TYPE_TOP, OSP_TYPE_REPUBLISH);

  foreach($types as $type) {
    if(!in_array($type, $notify)) {
      if($type == OSP_TYPE_PREMIUM) {
        //$content['s_text'] = preg_replace('|{START_PREMIUM}(.*){END_PREMIUM}|', '', $content['s_text']);
        $content['s_text'] = preg_replace('/{START_PREMIUM}[\s\S]+?{END_PREMIUM}/', '', $content['s_text']);

      } else if($type == OSP_TYPE_HIGHLIGHT) {
        //$content['s_text'] = preg_replace('|{START_HIGHLIGHT}(.*){END_HIGHLIGHT}|', '', $content['s_text']);
        $content['s_text'] = preg_replace('/{START_HIGHLIGHT}[\s\S]+?{END_HIGHLIGHT}/', '', $content['s_text']);

      } else if($type == OSP_TYPE_REPUBLISH) {
        //$content['s_text'] = preg_replace('|{START_REPUBLISH}(.*){END_REPUBLISH}|', '', $content['s_text']);
        $content['s_text'] = preg_replace('/{START_REPUBLISH}[\s\S]+?{END_REPUBLISH}/', '', $content['s_text']);

      }
    }
  }


  $title = osc_mailBeauty($content['s_title'], $words) ;
  $body  = osc_mailBeauty($content['s_text'], $words) ;

  $emailParams = array(
    'subject' => $title,
    'to' => $item['s_contact_email'],
    'to_name' => $item['s_contact_name'],
    'body' => $body,
    'alt_body' => $body
  );

  osc_sendMail($emailParams);
}



// MEMBERSHIP
function osp_email_expired_membership($user, $group) {
  osp_include_mailer();

  $mPages = new Page() ;
  $aPage = $mPages->findByInternalName('osp_email_expired_membership') ;
  $locale = osc_current_user_locale() ;
  $content = array();
  
  if(isset($aPage['locale'][$locale]['s_title'])) {
    $content = $aPage['locale'][$locale];
  } else {
    $content = current($aPage['locale'] <> '' ? $aPage['locale'] : array());
  }

  $account_url = osc_base_url(true) . '?page=custom&route=osp-item';
  $account_link = '<a href="' . $account_url . '" >' . __('My account - Promotions section', 'osclass_pay') . '</a>';

  if(isset($group['i_discount']) && $group['i_discount'] <> '' && $group['i_discount'] > 0) {
    $group_name = $group['s_name'] . ' (' . $group['i_discount'] . '%)';
  } else {
    $group_name = $group['s_name'];
  }

  $words = array();
  $words[] = array('{USER_ID}', '{CONTACT_NAME}', '{CONTACT_EMAIL}', '{WEB_URL}', '{GROUP}', '{WEB_TITLE}', '{ACCOUNT_LINK}');
  $words[] = array($user['pk_i_id'], $user['s_name'], $user['s_email'], osc_base_url(), $group_name, osc_page_title(), $account_link);


  $title = osc_mailBeauty($content['s_title'], $words) ;
  $body  = osc_mailBeauty($content['s_text'], $words) ;

  $emailParams = array(
    'subject' => $title,
    'to' => $user['s_email'],
    'to_name' => $user['s_name'],
    'body' => $body,
    'alt_body' => $body
  );

  osc_sendMail($emailParams);
}


// BONUS CREDITS PERIODICAL
function osp_email_bonus_credit($user, $credit, $group = array()) {
  osp_include_mailer();

  $mPages = new Page() ;
  $aPage = $mPages->findByInternalName('osp_email_bonus_credit') ;
  $locale = osc_current_user_locale() ;
  $content = array();
  
  if(isset($aPage['locale'][$locale]['s_title'])) {
    $content = $aPage['locale'][$locale];
  } else {
    $content = current($aPage['locale'] <> '' ? $aPage['locale'] : array());
  }

  $account_url = osc_base_url(true) . '?page=custom&route=osp-item';
  $account_link = '<a href="' . $account_url . '" >' . __('My account - Promotions section', 'osclass_pay') . '</a>';

  if(isset($group['i_pbonus']) && $group['i_pbonus'] <> '' && $group['i_pbonus'] > 0) {
    $group_desc = '<p>' . sprintf(__('For your membership in %s we added to you %s more credits!', 'osclass_pay'), '<strong>' . $group['s_name'] . '</strong>', '<strong>' . round($group['i_pbonus'], 2) . '%</strong>') . '</p>';
  } else {
    $group_desc = '';
  }

  $words = array();
  $words[] = array('{USER_ID}', '{CONTACT_NAME}', '{CONTACT_EMAIL}', '{WEB_URL}', '{GROUP_BONUS}', '{WEB_TITLE}', '{ACCOUNT_LINK}', '{CREDIT}');
  $words[] = array($user['pk_i_id'], $user['s_name'], $user['s_email'], osc_base_url(), $group_desc, osc_page_title(), $account_link, '<strong>' . $credit . '</strong>');


  $title = osc_mailBeauty($content['s_title'], $words) ;
  $body  = osc_mailBeauty($content['s_text'], $words) ;

  $emailParams = array(
    'subject' => $title,
    'to' => $user['s_email'],
    'to_name' => $user['s_name'],
    'body' => $body,
    'alt_body' => $body
  );

  osc_sendMail($emailParams);
}



// BANNER MANAGEMENT - SEND ADMIN APPROVAL/REJECTION OF BANNER
function osp_email_banner($banner) {
  osp_include_mailer();

  $mPages = new Page() ;
  $aPage = $mPages->findByInternalName('osp_email_banner') ;
  $locale = osc_current_user_locale() ;
  $content = array();

  if(isset($aPage['locale'][$locale]['s_title'])) {
    $content = $aPage['locale'][$locale];
  } else {
    $content = current($aPage['locale'] <> '' ? $aPage['locale'] : array());
  }

  $name = '<strong>' . $banner['s_name'] . '</strong>';


  // Status: 0 - pending, 1 - approved, 2 - paid, 9 - rejected, 10 - removed
  if($banner['i_status'] == 1) {
    $status = __('APPROVED', 'osclass_pay');
  } else if($banner['i_status'] == 9) {
    $status = __('REJECTED', 'osclass_pay');
  }

  $status = '<strong>' . $status . '</strong>';

  $user = User::newInstance()->findByPrimaryKey($banner['fk_i_user_id']);
  $pay_url = osc_base_url(true) . '?page=custom&route=osp-banner';
  $comment = ($banner['s_comment'] <> '' ? $banner['s_comment'] : '-');

  $words = array();
  $words[] = array('{BANNER_NAME}', '{STATUS}', '{PAYMENT_LINK}', '{COMMENT}', '{WEB_URL}', '{WEB_TITLE}', '{START_APPROVED}', '{END_APPROVED}', '{START_REJECTED}', '{END_REJECTED}');
  $words[] = array($name, $status, $pay_url, $comment, osc_base_url(), osc_page_title(), '', '', '', '');


  if($banner['i_status'] == 1) {
    //$content['s_text'] = preg_replace('|{START_REJECTED}(.*){END_REJECTED}|', '', $content['s_text']);
    $content['s_text'] = preg_replace('/{START_REJECTED}[\s\S]+?{END_REJECTED}/', '', $content['s_text']);
  } else if($banner['i_status'] == 9) {
    //$content['s_text'] = preg_replace('|{START_APPROVED}(.*){END_APPROVED}|', '', $content['s_text']);
    $content['s_text'] = preg_replace('/{START_APPROVED}[\s\S]+?{END_APPROVED}/', '', $content['s_text']);
  } else {
    return false;
  }


  $title = osc_mailBeauty($content['s_title'], $words) ;
  $body  = osc_mailBeauty($content['s_text'], $words) ;

  $emailParams = array(
    'subject' => $title,
    'to' => @$user['s_email'],
    'to_name' => __('Customer', 'osclass_pay'),
    'body' => $body,
    'alt_body' => $body
  );

  osc_sendMail($emailParams);
}



// ORDERS MANAGEMENT
function osp_email_order($order_id, $new = 0) {
  osp_include_mailer();

  $mPages = new Page() ;
  $aPage = $mPages->findByInternalName('osp_email_order') ;
  $locale = osc_current_user_locale() ;
  $content = array();
  
  if(isset($aPage['locale'][$locale]['s_title'])) {
    $content = $aPage['locale'][$locale];
  } else {
    $content = current($aPage['locale'] <> '' ? $aPage['locale'] : array());
  }

  $order = ModelOSP::newInstance()->getOrder($order_id);
  $order['i_status'] = (int)(isset($order['i_status']) ? $order['i_status'] : 0);
  $order['i_status'] = ($order['i_status'] > 0 ? $order['i_status'] : 0);
  
  $buyer = User::newInstance()->findByPrimaryKey($order['fk_i_user_id']);

  $order_url = osc_base_url(true) . '?page=custom&route=osp-order';
  $order_link = '<a href="' . $order_url . '" >' . __('My account - Orders section', 'osclass_pay') . '</a>';

  $comment = (trim($order['s_comment']) == '' ? '-' : $order['s_comment']);  

  $item_ids = array_filter(explode(',', trim($order['s_item_id'])));
  $cart_items = array_filter(explode('|', trim($order['s_cart'])));
  $order_content = '';

  /*
  $c = 0;
  if(count($item_ids) > 0) {
    foreach($item_ids as $i) {
      $item = Item::newInstance()->findByPrimaryKey($i);
      
      if($item !== false && isset($item['s_title'])) {
        $order_content .= explode('x', $cart_items[$c])[1] . 'x <a target="_blank" href="' . osc_item_url_ns($i) . '">' . osc_highlight($item['s_title'], 30) . '</a><br/>';
      } else {
        // item does not exists, should not happen 
      }
      
      $c++;
    }
  }
  */
  
  $c = 0;
  if(count($item_ids) > 0) {
    foreach($item_ids as $i) {
      $cart_item = explode('x', $cart_items[$c]);
      
      if($cart_item[0] == OSP_TYPE_SHIPPING) {
        $shipping = ModelOSP::newInstance()->getShipping($i);
        $name = __('Shipping', 'osclass_pay');
        
        if(isset($shipping['s_name']) && $shipping['s_name'] != '') {
          $name = $shipping['s_name'];
        }
        
        $order_content .= explode('x', $cart_items[$c])[1] . 'x <span>' . osc_highlight($name, 30) . '</span><br/>';

      } else {
        $item = Item::newInstance()->findByPrimaryKey($i);
        
        if($item !== false && isset($item['s_title'])) {
          $order_content .= explode('x', $cart_items[$c])[1] . 'x <a target="_blank" href="' . osc_item_url_ns($i) . '">' . osc_highlight($item['s_title'], 30) . '</a><br/>';
        } else {
          // item does not exists, should not happen 
        }
      }
      
      $c++;
    }
  }

  $order_content .= '---<br/>';
  $order_content .= __('Total amount', 'osclass_pay') . ': <strong>' . osp_format_price($order['f_amount']) . '</strong>';


  $words = array();
  $words[] = array('{ORDER_ID}', '{ORDER_STATUS}', '{CONTACT_NAME}', '{CONTACT_EMAIL}', '{WEB_URL}', '{ORDER_LINK}', '{ORDER_CONTENT}', '{COMMENT}', '{WEB_TITLE}', '{START_NEW}', '{END_NEW}', '{START_PROCESSING}', '{END_PROCESSING}', '{START_SHIPPED}', '{END_SHIPPED}', '{START_COMPLETED}', '{END_COMPLETED}', '{START_CANCELLED}', '{END_CANCELLED}');
  $words[] = array($order['pk_i_id'], $order['i_status'], $buyer['s_name'], $buyer['s_email'], osc_base_url(), $order_link, $order_content, $comment, osc_page_title(), '', '', '', '', '', '', '', '', '', '');


  if($new <> 1) {
    //$content['s_text'] = preg_replace('|{START_NEW}(.*){END_NEW}|', '', $content['s_text']);
    $content['s_text'] = preg_replace('/{START_NEW}[\s\S]+?{END_NEW}/', '', $content['s_text']);
  }

  if($order['i_status'] <> OSP_ORDER_PROCESSING) {
    //$content['s_text'] = preg_replace('|{START_PROCESSING}(.*){END_PROCESSING}|', '', $content['s_text']);
    $content['s_text'] = preg_replace('/{START_PROCESSING}[\s\S]+?{END_PROCESSING}/', '', $content['s_text']);
  }

  if($order['i_status'] <> OSP_ORDER_SHIPPED || $new == 1) {
    //$content['s_text'] = preg_replace('|{START_SHIPPED}(.*){END_SHIPPED}|', '', $content['s_text']);
    $content['s_text'] = preg_replace('/{START_SHIPPED}[\s\S]+?{END_SHIPPED}/', '', $content['s_text']);
  }

  if($order['i_status'] <> OSP_ORDER_COMPLETED || $new == 1) {
    //$content['s_text'] = preg_replace('|{START_COMPLETED}(.*){END_COMPLETED}|', '', $content['s_text']);
    $content['s_text'] = preg_replace('/{START_COMPLETED}[\s\S]+?{END_COMPLETED}/', '', $content['s_text']);
  }

  if($order['i_status'] <> OSP_ORDER_CANCELLED || $new == 1) {
    //$content['s_text'] = preg_replace('|{START_CANCELLED}(.*){END_CANCELLED}|', '', $content['s_text']);
    $content['s_text'] = preg_replace('/{START_CANCELLED}[\s\S]+?{END_CANCELLED}/', '', $content['s_text']);
  }


  $title = osc_mailBeauty($content['s_title'], $words) ;
  $body  = osc_mailBeauty($content['s_text'], $words) ;

  $emailParams = array(
    'subject' => $title,
    'to' => $buyer['s_email'],
    'to_name' => $buyer['s_name'],
    'body' => $body,
    'alt_body' => $body
  );

  osc_sendMail($emailParams);
}

?>