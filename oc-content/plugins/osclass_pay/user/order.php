<?php
  osp_user_menu('order');

  $user_id = osc_logged_user_id();
  $page_id = (Params::getParam('pageId') > 0 ? Params::getParam('pageId') : 0);
  $params = Params::getParamsAsArray();

  //$sales = ModelOSP::newInstance()->getSales($user_id);
  //$sale_items = array_keys($sales);
  
  $per_page = 20;
  $params['customer'] = $user_id;
  $params['per_page'] = $per_page;
  
  $orders = ModelOSP::newInstance()->getOrdersWithItems($params);
  $count_all = ModelOSP::newInstance()->getOrdersWithItems($params, true);

  $is_seller = osp_user_is_seller(osc_logged_user_id());
?>

<div class="osp-body osp-body-order">
  <div id="osp-tab-menu">
    <a href="<?php echo osc_route_url('osp-order'); ?>" class="osp-active"><?php _e('Purchases', 'osclass_pay'); ?></a>
    
    <?php if($is_seller) { ?>
      <a href="<?php echo osc_route_url('osp-sales'); ?>"><?php _e('Sales', 'osclass_pay'); ?></a>
      <?php if(osp_param('enable_user_management') == 1) { ?><a href="<?php echo osc_route_url('osp-products'); ?>"><?php _e('Products mng.', 'osclass_pay'); ?></a><?php } ?>
      <?php if(osp_param('enable_user_management') == 1) { ?><a href="<?php echo osc_route_url('osp-manager'); ?>"><?php _e('Orders mng.', 'osclass_pay'); ?></a><?php } ?>
      <?php if(osp_param('enable_shipping') == 1) { ?><a href="<?php echo osc_route_url('osp-shipping'); ?>"><?php _e('Shipping', 'osclass_pay'); ?></a><?php } ?>
    <?php } ?>
  </div>

  <div class="osp-tab osp-active">
    <div class="osp-h2">
      <?php echo __('List of all products you have bought (does not mean promotions, membership or credit packs).', 'osclass_pay'); ?>
      <?php echo sprintf(__('If you want to cancel order, please contact us via %s.', 'osclass_pay'), '<a target="_blank" href="' . osc_contact_url() . '">' . __('Contact form', 'osclass_pay') . '</a>'); ?>
    </div>

    <div class="osp-pay-msg dlvr">
      <?php 
        $loc = trim(implode(', ', array_filter(array(osc_user_country(), osc_user_region(), osc_user_city(), osc_user_zip(), osc_user_address())))); 
        $mob = trim(osc_user_phone_mobile() != '' ? osc_user_phone_mobile() : osc_user_phone_land());
      ?>
      <?php echo sprintf(__('Ordered products will be delivered to address stored in %s section.', 'osclass_pay'), '<a href="' . osc_user_profile_url() . '">' . __('your profile', 'osclass_pay') . '</a>'); ?><br/>
      <?php 
        if($loc <> '') {
          echo sprintf(__('Your current delivery address: %s', 'osclass_pay'), '<strong>' . $loc . '</strong>'); 
        } else {
          echo '<strong>' . __('You have not entered your address!', 'osclass_pay') . '</strong>'; 
        }
        
        echo '<br/>';

        if($mob <> '') {
          echo sprintf(__('Your current phone number: %s', 'osclass_pay'), '<strong>' . $mob . '</strong>'); 
        } else {
          echo '<strong>' . __('You have not entered any phone number!', 'osclass_pay') . '</strong>'; 
        }
      ?>
    </div>


    <?php if(osp_param('status_disable') <> 1) { ?>
      <div class="osp-status-info">
        <div class="osp-cycle-title"><?php _e('Order status', 'osclass_pay'); ?></div>

        <div class="osp-order-cycle">
          <div class="osp-step osp-s1">
            <i class="fa fa-hourglass-half"></i>
            <strong><?php _e('Processing', 'osclass_pay'); ?></strong>
            <span><?php _e('Our team is preparing your order', 'osclass_pay'); ?></span>
          </div>

          <div class="osp-step osp-s2">
            <i class="fa fa-truck"></i>
            <strong><?php _e('Shipped', 'osclass_pay'); ?></strong>
            <span><?php _e('Your order is on way to you', 'osclass_pay'); ?></span>
          </div>

          <div class="osp-step osp-s3">
            <i class="fa fa-check"></i>
            <strong><?php _e('Completed', 'osclass_pay'); ?></strong>
            <span><?php _e('Order is delivered and completed', 'osclass_pay'); ?></span>
          </div>
        </div>
      </div>
    <?php } ?>


    <div class="osp-table-orders">
      <div class="osp-head-row">
        <div class="osp-col orderid"><?php _e('ID', 'osclass_pay'); ?></div>
        <div class="osp-col item"><?php _e('Products', 'osclass_pay'); ?></div>
        <div class="osp-col paid"><?php _e('Amount', 'osclass_pay'); ?></div>
        <div class="osp-col date"><?php _e('Date', 'osclass_pay'); ?></div>
        <div class="osp-col status"><?php _e('Order Status', 'osclass_pay'); ?></div>
        <div class="osp-col payment">&nbsp;</div>
      </div>

      <?php if(is_array($orders) && count($orders) > 0) { ?>
        <div class="osp-table-wrap">
          <?php foreach($orders as $order) { ?>
            <?php 
              $order_items = $order['order_items']; 

              $payment = ModelOSP::newInstance()->getPayment($order['fk_i_payment_id']);
              $payment_title  = __('Order ID', 'osclass_pay') . ': #' . $order['pk_i_id'] . '<br/>';
              $payment_title .= __('Transaction', 'osclass_pay') . ': ' . $payment['s_code'] . '<br/>';
              $payment_title .= __('Provider', 'osclass_pay') . ': ' . $payment['s_source'] . '<br/>';
              $payment_title .= __('Date', 'osclass_pay') . ': ' . $payment['dt_date'];
            ?>

            <div class="osp-row">
              <div class="osp-col orderid">#<?php echo $order['pk_i_id']; ?></div>
              <div class="osp-col item">
                <?php if(is_array($order_items) && count($order_items) > 0) { ?>
                  <?php foreach($order_items as $order_item) { ?>
                    <?php if($order_item['fk_i_item_id'] !== NULL) { ?>
                      <?php
                        $item = Item::newInstance()->findByPrimaryKey($order_item['fk_i_item_id']);
                        View::newInstance()->_exportVariableToView('item', $item); 

                        $amt_title = '';
                        
                        if($order_item['f_amount'] < $order_item['f_amount_regular']) {
                          $amt_title = osc_esc_html(__('Regular price: %s', 'osclass_pay'), osp_format_price($order_item['f_amount_regular'], 9, $order_item['s_currency_code']));
                        }
                        
                        if(trim($order_item['s_amount_comment']) != '') {
                          $amt_title .= ($amt_title == '' ? '' : ', ') . osc_esc_html($order_item['s_amount_comment']); 
                        }
                      ?>
                        
                      <div class="osp-line">
                        <span class="qnt"><?php echo $order_item['i_quantity']; ?>x</span>
                        <span class="lnk"><a <?php echo (osc_item_id() > 0 ? 'target="_blank"' : 'onclick="return false;"'); ?>" href="<?php echo (osc_item_id() > 0 ? osc_item_url() : '#'); ?>"><?php echo osc_highlight(osc_item_title() <> '' ? osc_item_title() : $order_item['s_title'] . ' [' . __('Removed', 'osclass_pay') . ']', 60); ?></a>, </span>
                        <span class="amt osp-has-tooltip" title="<?php echo $amt_title; ?>"><?php echo osp_format_price($order_item['f_amount'], 9, $order_item['s_currency_code']); ?></span>
                      </div>
                    <?php } else { ?>
                      <div class="osp-line">
                        <span class="qnt"><?php echo $order_item['i_quantity']; ?>x</span>
                        <span class="shp">
                          <?php 
                            if($order_item['fk_i_shipping_id'] > 0) {
                              $shipping_owner = User::newInstance()->findByPrimaryKey($order_item['fk_i_user_id']);
                              echo sprintf(__('Shipping: %s from %s', 'osclass_pay'), osc_highlight($order_item['s_title'], 70), @$shipping_owner['s_name'] <> '' ? '<a href="mailto:' . $shipping_owner['s_email'] . '">' . $shipping_owner['s_name'] . '</a>' : __('Unknown', 'osclass_pay')); 
                            } else {
                              echo sprintf(__('Shipping: %s', 'osclass_pay'), osc_highlight($order_item['s_title'], 70)); 
                            }
                          ?>
                        </span>, 
                        <span class="amt"><?php echo osp_format_price($order_item['f_amount'], 9, $order_item['s_currency_code']); ?></span>
                      </div>                    
                    <?php } ?>
                  <?php } ?>
                <?php } ?>
              </div>
              
              <div class="osp-col paid osp-has-tooltip" title="<?php echo osc_esc_html(trim($order['s_amount_comment'])); ?>">
                <strong><?php echo osp_format_price($order['f_amount'], 9, $order['s_currency_code']); ?></strong>
                
                <?php if($order['f_amount'] < $order['f_amount_regular']) { ?>
                  <div><?php echo osp_format_price($order['f_amount_regular'], 9, $order['s_currency_code']); ?></div>
                <?php } ?>
              </div>

              <div class="osp-col date osp-has-tooltip" title="<?php echo osc_esc_html($order['dt_date']); ?>"><?php echo date('j. M Y', strtotime($order['dt_date'])); ?></div>

              <div class="osp-col status st<?php echo (osp_param('status_disable') == 1 ? 2 : $order['i_status']); ?>">
                <?php 
                  if(osp_param('status_disable') == 1) {
                    $text = '<i class="fa fa-check"></i> ' . __('Completed', 'osclass_pay');
                    $title = __('Order completed', 'osclass_pay');
                  } else if($order['i_status'] == OSP_ORDER_PROCESSING) {
                    $text = '<i class="fa fa-hourglass-half"></i> ' . __('Processing', 'osclass_pay');
                    $title = __('We are processing this order', 'osclass_pay');
                  } else if($order['i_status'] == OSP_ORDER_SHIPPED) {
                    $text = '<i class="fa fa-truck"></i> ' . __('Shipped', 'osclass_pay');
                    $title = __('We have shipped products to buyer\'s address', 'osclass_pay');
                  } else if($order['i_status'] == OSP_ORDER_COMPLETED) {
                    $text = '<i class="fa fa-check"></i> ' . __('Completed', 'osclass_pay');
                    $title = __('Order is delivered and completed', 'osclass_pay');
                  } else if($order['i_status'] == OSP_ORDER_CANCELLED) {
                    $text = '<i class="fa fa-times"></i> ' . __('Cancelled', 'osclass_pay');
                    $title = __('Order has been cancelled', 'osclass_pay');
                  }
                ?>

                <span class="osp-has-tooltip" title="<?php echo osc_esc_html($title); ?>"><?php echo $text; ?></span>
              </div>

              <div class="osp-col payment osp-has-tooltip-right" title="<?php echo osc_esc_html($payment_title); ?>"><i class="fa fa-list-ul"></i></div>
            </div>
          <?php } ?>
        </div>
        
        <?php echo osp_paginate('osp-order-paginate', Params::getParam('pageId'), $per_page, $count_all); ?>
      <?php } else { ?>
        <div class="osp-row osp-row-empty">
          <i class="fa fa-warning"></i><span><?php _e('You have no orders yet.', 'osclass_pay'); ?></span>
        </div>
      <?php } ?>
    
    </div>
  </div>

</div>