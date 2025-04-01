<?php
  osp_user_menu('order');

  $user_id = osc_logged_user_id();
  $page_id = (Params::getParam('pageId') > 0 ? Params::getParam('pageId') : 0);
  $params = Params::getParamsAsArray();

  //$sales = ModelOSP::newInstance()->getSales($user_id);
  //$sale_items = array_keys($sales);
  
  $per_page = 20;
  $params['user'] = $user_id;
  $params['per_page'] = $per_page;
  
  $orders = ModelOSP::newInstance()->getUserSales($params);
  $count_all = ModelOSP::newInstance()->getUserSales($params, true);
  
  $is_seller = osp_user_is_seller(osc_logged_user_id());
  
  if(!$is_seller) {
    osc_add_flash_warning_message(__('This section is not available for you', 'osclass_pay'));
    header('Location:' . osc_route_url('osp-order'));
    exit;
  }
?>

<div class="osp-body osp-body-order">
  <div id="osp-tab-menu">
    <a href="<?php echo osc_route_url('osp-order'); ?>"><?php _e('Purchases', 'osclass_pay'); ?></a>
    
    <?php if($is_seller) { ?>
      <a href="<?php echo osc_route_url('osp-sales'); ?>" class="osp-active"><?php _e('Sales', 'osclass_pay'); ?></a>
      <?php if(osp_param('enable_user_management') == 1) { ?><a href="<?php echo osc_route_url('osp-products'); ?>"><?php _e('Products mng.', 'osclass_pay'); ?></a><?php } ?>
      <?php if(osp_param('enable_user_management') == 1) { ?><a href="<?php echo osc_route_url('osp-manager'); ?>"><?php _e('Orders mng.', 'osclass_pay'); ?></a><?php } ?>
      <?php if(osp_param('enable_shipping') == 1) { ?><a href="<?php echo osc_route_url('osp-shipping'); ?>"><?php _e('Shipping', 'osclass_pay'); ?></a><?php } ?>
    <?php } ?>
  </div>

  <div class="osp-tab osp-active">
    <div class="osp-h2">
      <?php echo __('List of all your product sales.', 'osclass_pay'); ?>
    </div>

    <div class="osp-table-sales">
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
              $order_amount = 0;
              $order_amount_regular = 0;

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
                    <?php
                      $order_amount += $order_item['f_amount'];
                      $order_amount_regular += $order_item['f_amount_regular'];
                    ?>                    
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
                        <span class="shp"><?php echo sprintf(__('Shipping: %s', 'osclass_pay'), osc_highlight($order_item['s_title'], 70)); ?></span>, 
                        <span class="amt"><?php echo osp_format_price($order_item['f_amount'], 9, $order_item['s_currency_code']); ?></span>
                      </div>                    
                    <?php } ?>
                  <?php } ?>
                <?php } ?>
              </div>
              
              <div class="osp-col paid osp-has-tooltip" title="<?php echo osc_esc_html(trim($order['s_amount_comment'])); ?>">
                <strong><?php echo osp_format_price($order_amount, 9, $order['s_currency_code']); ?></strong>
                
                <?php if($order_amount < $order_amount_regular) { ?>
                  <div><?php echo osp_format_price($order_amount_regular, 9, $order['s_currency_code']); ?></div>
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
        
        <?php echo osp_paginate('osp-sales-paginate', Params::getParam('pageId'), $per_page, $count_all); ?>
      <?php } else { ?>
        <div class="osp-row osp-row-empty">
          <i class="fa fa-warning"></i><span><?php _e('You have not sold products yet', 'osclass_pay'); ?></span>
        </div>
      <?php } ?>
    </div>
  </div>
</div>