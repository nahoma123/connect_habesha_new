<?php
  osp_user_menu('order');

  $user_id = osc_logged_user_id();
  $page_id = osc_esc_html(Params::getParam('pageId') > 0 ? Params::getParam('pageId') : 0);
  $status = osc_esc_html(Params::getParam('status') != '' ? Params::getParam('status') : 'ALL');

  //ModelOSP::newInstance()->generateOrderItems();
  

  $params = Params::getParamsAsArray();

  if(Params::getParam('what') == 'updateOrders') {
    foreach($params as $key => $val) {
      $key_ = explode('_', $key);
      
      if($key_[0] == 'stat') {
        $id = osc_esc_html($key_[1]);
        $order_item = ModelOSP::newInstance()->getOrderItem($id);
        
        if($order_item['fk_i_user_id'] == $user_id) {
          ModelOSP::newInstance()->updateOrderItem($id, array(
            'i_status' => osc_esc_html($val),
            'dt_last_update' => date('Y-m-d H:i:s')
          ));
          
          // Update order status based on order item status
          osp_resolve_order_status($order_item['fk_i_order_id']);
        }
      }
    }
   
    osc_add_flash_ok_message(__('Orders has been successfully updated', 'osclass_pay'));
    
    if($page_id > 0) {
      header('Location:' . osc_route_url('osp-manager-paginate', array('pageId' => $page_id)));
    } else {
      header('Location:' . osc_route_url('osp-manager'));
    }
    
    exit;    
  }
  
  $per_page = 20;
  $params['user'] = $user_id;
  $params['per_page'] = $per_page;
  //$params['status'] = $status;
  
  $orders = ModelOSP::newInstance()->getOrdersWithItems($params);
  $count_all = ModelOSP::newInstance()->getOrdersWithItems($params, true);
  
  $sellers = explode(',', osp_param('seller_users'));
  $sellers = array_filter($sellers);

  $is_seller = false;
  if(in_array(osc_logged_user_id(), $sellers) || osp_param('seller_all') == 1) {
    $is_seller = true;
  }
  
  if(!$is_seller || osp_param('enable_user_management') != 1) {
    osc_add_flash_warning_message(__('This section is not available for you', 'osclass_pay'));
    header('Location:' . osc_route_url('osp-order'));
    exit;
  }
?>

<div class="osp-body osp-body-order">
  <div id="osp-tab-menu">
    <a href="<?php echo osc_route_url('osp-order'); ?>"><?php _e('Purchases', 'osclass_pay'); ?></a>
    
    <?php if($is_seller) { ?>
      <a href="<?php echo osc_route_url('osp-sales'); ?>"><?php _e('Sales', 'osclass_pay'); ?></a>
      <?php if(osp_param('enable_user_management') == 1) { ?><a href="<?php echo osc_route_url('osp-products'); ?>"><?php _e('Products mng.', 'osclass_pay'); ?></a><?php } ?>
      <?php if(osp_param('enable_user_management') == 1) { ?><a href="<?php echo osc_route_url('osp-manager'); ?>" class="osp-active"><?php _e('Orders mng.', 'osclass_pay'); ?></a><?php } ?>
      <?php if(osp_param('enable_shipping') == 1) { ?><a href="<?php echo osc_route_url('osp-shipping'); ?>"><?php _e('Shipping', 'osclass_pay'); ?></a><?php } ?>
    <?php } ?>
  </div>

  <div class="osp-tab osp-active">
    <div class="osp-h2">
      <?php echo __('Update status of order items once ordered items were shipped or delivered to customer. If order has been cancelled, update status of all items to cancelled.', 'osclass_pay'); ?>
    </div>
    
    <div id="osp-search-box">
      <form action="<?php echo osc_route_url('osp-manager'); ?>" method="GET" class="nocsrf">
        <input type="hidden" name="what" value="searchOrders"/>
        <input type="hidden" name="pageId" value="<?php echo osc_esc_html($page_id); ?>"/>
        
        <div class="osp-col w50 kw">
          <label for="ospKeyword"><?php _e('Keyword', 'osclass_pay'); ?></label>
          <input type="text" name="ospKeyword" id="ospKeyword" value="<?php echo osc_esc_html(Params::getParam('ospKeyword')); ?>" placeholder="<?php echo osc_esc_html(__('Customer name, email, item title, ...', 'osclass_pay')); ?>"/>
        </div>

        <div class="osp-col w30 st">
          <label for="ospStatus"><?php _e('Status', 'osclass_pay'); ?></label>
          <select name="ospStatus" id="ospStatus" <?php if(osp_param('status_disable') == 1) { ?>disabled<?php } ?>>
            <?php if(osp_param('status_disable') == 1) { ?>
              <option value="" selected="selected"><?php _e('All', 'osclass_pay'); ?></option>
            <?php } else { ?>
              <option value="" <?php if(Params::getParam('ospStatus') == "") { ?>selected="selected"<?php } ?>><?php _e('All', 'osclass_pay'); ?></option>
              <option value="0" <?php if(Params::getParam('ospStatus') == 0) { ?>selected="selected"<?php } ?>><?php _e('Processing', 'osclass_pay'); ?></option>
              <option value="1" <?php if(Params::getParam('ospStatus') == 1) { ?>selected="selected"<?php } ?>><?php _e('Shipped', 'osclass_pay'); ?></option>
              <option value="2" <?php if(Params::getParam('ospStatus') == 2) { ?>selected="selected"<?php } ?>><?php _e('Completed', 'osclass_pay'); ?></option>
              <option value="9" <?php if(Params::getParam('ospStatus') == 9) { ?>selected="selected"<?php } ?>><?php _e('Cancelled', 'osclass_pay'); ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="osp-col w20 bt">
          <label for="">&nbsp;</label>
          <button type="submit"><i class="fa fa-search"></i> <?php _e('Search', 'osclass_pay'); ?></button>
        </div>
      </form>
    </div>

    <div class="osp-table-manager">
      <div class="osp-head-row">
        <div class="osp-col order"><?php _e('Order details', 'osclass_pay'); ?></div>
        <div class="osp-col item">
          <span class="qnt"><?php _e('Qt', 'osclass_pay'); ?></span>
          <span class="lnk"><?php _e('Products', 'osclass_pay'); ?></span>
          <span class="amt"><?php _e('Paid', 'osclass_pay'); ?></span>
          <span class="stat"><?php _e('Status', 'osclass_pay'); ?></span>
        </div>
      </div>

      <?php if(count($orders) > 0) { ?>
        <form action="<?php echo osc_route_url('osp-manager'); ?>" method="POST">
          <input type="hidden" name="what" value="updateOrders"/>
          <input type="hidden" name="pageId" value="<?php echo osc_esc_html(Params::getParam('pageId')); ?>"/>
          
          <div class="osp-table-wrap">
            <?php foreach($orders as $order) { ?>
              <?php
                $order_items = $order['order_items'];
                $user_order_status = osp_order_status_based_on_items($order_items);
              
                // one or more items require shipping
                $order_user = User::newInstance()->findByPrimaryKey($order['fk_i_user_id']);
                $order_user_name = (@$order_user['s_name'] <> '' ? $order_user['s_name'] : __('N/A', 'osclass_pay'));
                $order_user_email = (@$order_user['s_email'] <> '' ? $order_user['s_email'] : '');
                $order_user_address = '';
                $order_user_phone = (@$order_user['s_phone_mobile'] <> '' ? $order_user['s_phone_mobile'] : @$order_user['s_phone_land']);

                if(isset($order_user['pk_i_id'])) {
                  $order_user_address = trim(implode(', ', array_filter(array($order_user['s_country'], $order_user['s_region'], $order_user['s_city'], $order_user['s_city_area'], $order_user['s_zip'], $order_user['s_address']))));
                }
                
                if(!isset($order_user['pk_i_id']) || $order_user_address == '') {
                  $order_user_address = __('Unknown address', 'osclass_pay');
                }
              ?>
              
              <div class="osp-row osp-st-<?php echo $user_order_status; ?>">
                <div class="osp-col order">
                  <div class="osp-line"><?php echo sprintf(__('%s from %s', 'osclass_pay'), '<strong>' . sprintf(__('Order #%s', 'osclass_pay') . '</strong>', $order['pk_i_id']), '<span class="osp-date osp-has-tooltip" title="' . osc_esc_html($order['dt_date']) . '">' .  date('j. M Y', strtotime($order['dt_date'])) . '</span>'); ?></div>

                    <div class="osp-line deliv">
                      <i class="fa fa-level-up fa-level-up-alt"></i>

                      <?php if($order['i_shipping_count'] > 0) { ?>
                        <span><?php echo sprintf(__('Deliver to %s (%s): %s', 'osclass_pay'), '<a href="' . ($order_user_email != '' ? 'mailto:' . $order_user_email : '') . '">' . $order_user_name . '</a>', $order_user_phone, $order_user_address); ?></span>
                      <?php } else { ?>
                        <span><?php echo sprintf(__('Customer %s, shipping is not required.', 'osclass_pay'), '<a href="' . ($order_user_email != '' ? 'mailto:' . $order_user_email : '') . '">' . $order_user_name . '</a>'); ?></span>
                      <?php } ?>
                    </div>
                </div>
                
                <div class="osp-col item">
                  <?php if(is_array($order_items) && count($order_items) > 0) { ?>
                    <?php foreach($order_items as $order_item) { ?>
                      <?php if($order_item['fk_i_item_id'] !== NULL) { ?>
                        <?php
                          $item = Item::newInstance()->findByPrimaryKey($order_item['fk_i_item_id']);
                          View::newInstance()->_exportVariableToView('item', $item); 
                          $item_data = ModelOSP::newInstance()->getItemData($order_item['fk_i_item_id']);
                        ?>
                        
                        <div class="osp-line">
                          <span class="qnt"><?php echo $order_item['i_quantity']; ?>x</span>
                          <span class="lnk">
                            <?php if(isset($item_data['i_shipping']) && $item_data['i_shipping'] == 1) { ?>
                              <i class="fa fa-truck osp-icon-tanitan osp-has-tooltip" title="<?php echo osc_esc_html(__('Tangible product, must be shipped', 'osclass_pay')); ?>"></i>
                            <?php } else if(isset($item_data['i_shipping']) && $item_data['i_shipping'] == 0) { ?>
                              <i class="fa fa-wrench osp-icon-tanitan osp-itan osp-has-tooltip" title="<?php echo osc_esc_html(__('Intangible product (service), does not require shipping', 'osclass_pay')); ?>"></i>
                            <?php } else { ?>
                              <i class="fa fa-question osp-icon-tanitan osp-utan osp-has-tooltip" title="<?php echo osc_esc_html(__('Shipping requirement unknown', 'osclass_pay')); ?>"></i>
                            <?php } ?>
                          
                            <a <?php echo (osc_item_id() > 0 ? 'target="_blank"' : 'onclick="return false;"'); ?>" href="<?php echo (osc_item_id() > 0 ? osc_item_url() : '#'); ?>"><?php echo osc_highlight(osc_item_title() <> '' ? osc_item_title() : $order_item['s_title'] . ' [' . __('Removed', 'osclass_pay') . ']', 60); ?></a>
                          </span>
                          <strong class="amt osp-has-tooltip" title="<?php echo osc_esc_html(trim($order_item['s_amount_comment'])); ?>"><?php echo osp_format_price($order_item['f_amount'], 9, $order_item['s_currency_code']); ?></strong>

                          <div class="stat osp-has-tooltip" <?php if(osp_param('status_disable') == 1) { ?>title="<?php echo osc_esc_html(__('Status cannot be updated.', 'osclass_pay')); ?>"<?php } ?>>
                            <select class="osp-ord-stat" name="stat_<?php echo $order_item['pk_i_id']; ?>" <?php if(osp_param('status_disable') == 1) { ?>disabled<?php } ?>>
                              <?php if(osp_param('status_disable') == 1) { ?>
                                <option value="" selected="selected"><?php _e('Completed', 'osclass_pay'); ?></option>
                              <?php } else { ?>
                                <option value="0" <?php if($order_item['i_status'] == 0) { ?>selected="selected"<?php } ?>><?php _e('Processing', 'osclass_pay'); ?></option>
                                <?php if(isset($item_data['i_shipping']) && $item_data['i_shipping'] == 1) { ?><option value="1" <?php if($order_item['i_status'] == 1) { ?>selected="selected"<?php } ?>><?php _e('Shipped', 'osclass_pay'); ?></option><?php } ?>
                                <option value="2" <?php if($order_item['i_status'] == 2) { ?>selected="selected"<?php } ?>><?php _e('Completed', 'osclass_pay'); ?></option>
                                <option value="9" <?php if($order_item['i_status'] == 9) { ?>selected="selected"<?php } ?>><?php _e('Cancelled', 'osclass_pay'); ?></option>
                              <?php } ?>
                            </select>
                          </div>
                        </div>
                      <?php } else { ?>
                        <div class="osp-line">
                          <span class="qnt"><?php echo $order_item['i_quantity']; ?>x</span>
                          <span class="shp"><em><?php echo sprintf(__('Shipping: %s', 'osclass_pay'), osc_highlight($order_item['s_title'], 70)); ?></em></span>
                          <strong class="amt"><?php echo osp_format_price($order_item['f_amount'], 9, $order_item['s_currency_code']); ?></strong>
                        </div>
                      <?php } ?>
                    <?php } ?>
                  <?php } ?>
                </div>
              </div>
            <?php } ?>
            
            <?php echo osp_paginate('osp-manager-paginate', Params::getParam('pageId'), $per_page, $count_all, '', array('ospKeyword' => Params::getParam('ospKeyword'), 'ospStatus' => Params::getParam('ospStatus'))); ?>
          </div>
          
          <div class="osp-button-row">
            <button type="submit" id="osp-update-orders"><?php _e('Update orders', 'osclass_pay'); ?></button>
          </div>
        </form>
      <?php } else { ?>
        <div class="osp-row osp-row-empty">
          <i class="fa fa-warning"></i><span><?php _e('No orders found', 'osclass_pay'); ?></span>
        </div>
      <?php } ?>
    </div>
  </div>
</div>