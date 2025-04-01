<?php
  // Create menu
  // $title = __('Orders', 'osclass_pay');
  // osp_menu($title);


  if(Params::getParam('plugin_action') == 'done') {
    message_ok( __('Settings were successfully saved', 'osclass_pay') );
  }

  $status_disable = osp_param('status_disable');

  if(Params::getParam('plugin_action') == 'order') {
    $params = Params::getParamsAsArray();

    foreach(array_keys($params) as $p) {
      $detail = explode('_', $p);

      if($detail[0] == 'stat') {

        // On cancelled order restock products, on renewed order unstock products
        $order = ModelOSP::newInstance()->getOrder($detail[1]);
        if($params[$p] == OSP_ORDER_CANCELLED && $order['i_status'] <> OSP_ORDER_CANCELLED) {
          ModelOSP::newInstance()->restockOrder($detail[1], '+');
        } else if($params[$p] <> OSP_ORDER_CANCELLED && $order['i_status'] == OSP_ORDER_CANCELLED) {
          ModelOSP::newInstance()->restockOrder($detail[1], '-');
        }

        ModelOSP::newInstance()->updateOrderStatus($detail[1], $params[$p]);

        // order status has changed
        if($params[$p] <> $order['i_status'] && $status_disable <> 1) {
          osp_email_order($order['pk_i_id'], 0);
        }

      } else if($detail[0] == 'comm') {
        ModelOSP::newInstance()->updateOrderComment($detail[1], $params[$p]);
      }
    }

    message_ok( __('Orders were successfully updated. Products has been restocked.', 'osclass_pay') );
  }


  // $orders = ModelOSP::newInstance()->getOrders();
  $per_page = (Params::getParam('per_page') > 0 ? Params::getParam('per_page') : 25);
  $params = Params::getParamsAsArray();

  $orders = ModelOSP::newInstance()->getOrders2($params);
  $count_all = ModelOSP::newInstance()->getOrders2($params, true);
?>



<div class="mb-body">
  <div class="mb-box mb-order">
    <div class="mb-head"><i class="fa fa-shopping-basket"></i> <?php _e('Orders', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('Bellow are shown all product orders.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('Order status has no impact on status of order items. Order status is automatically updated based on updates of order items.', 'osclass_pay'); ?></div>
      </div>
      
      <form name="promo_form" action="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=osclass_pay/admin/ecommerce.php&go_to_file=_ecommerce_orders.php" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="orderSearch" value="1"/>
        
        <div id="mb-search-table" class="mb-order-search">
          <div class="mb-col-2">
            <label for="id"><?php _e('ID', 'osclass_pay'); ?></label>
            <input type="text" name="id" value="<?php echo Params::getParam('id'); ?>" />
          </div>

          <div class="mb-col-5">
            <label for="user"><?php _e('Buyer/Seller', 'osclass_pay'); ?></label>
            <input type="text" name="user" value="<?php echo Params::getParam('user'); ?>" />
          </div>

          <div class="mb-col-4">
            <label for="address"><?php _e('Delivery Address', 'osclass_pay'); ?></label>
            <input type="text" name="address" value="<?php echo Params::getParam('address'); ?>" />
          </div>

          <div class="mb-col-4">
            <label for="item"><?php _e('Product', 'osclass_pay'); ?></label>
            <input type="text" name="item" value="<?php echo Params::getParam('item'); ?>" />
          </div>
          
          <div class="mb-col-3">
            <label for="payment"><?php _e('Payment', 'osclass_pay'); ?></label>
            <input type="text" name="payment" value="<?php echo Params::getParam('payment'); ?>" />
          </div>
          
          <div class="mb-col-3">
            <label for="comment"><?php _e('Comment', 'osclass_pay'); ?></label>
            <input type="text" name="comment" value="<?php echo Params::getParam('comment'); ?>" />
          </div>
          
          <div class="mb-col-2 mb-bt">
            <label for="">&nbsp;</label>
            <button type="submit" class="mb-button mb-button-black"><i class="fa fa-search"></i> <?php _e('Search', 'osclass_pay'); ?></button>
          </div>
          
          <div class="mb-col-2 mb-ma">
            <label for="amount_min"><?php _e('Min. Paid', 'osclass_pay'); ?></label>
            <input type="text" name="amount_min" value="<?php echo Params::getParam('amount_min'); ?>" />
          </div>
          
          <div class="mb-col-2">
            <label for="amount_max"><?php _e('Max. Paid', 'osclass_pay'); ?></label>
            <input type="text" name="amount_max" value="<?php echo Params::getParam('amount_max'); ?>" />
          </div>
          
          <div class="mb-col-2">
            <label for="currency"><?php _e('Currency', 'osclass_pay'); ?></label>
            <input type="text" name="currency" value="<?php echo Params::getParam('currency'); ?>" />
          </div>

          <div class="mb-col-3">
            <label for="date"><?php _e('Date', 'osclass_pay'); ?></label>
            <input type="text" name="date" value="<?php echo Params::getParam('date'); ?>" placeholder="YYYY-MM-DD"/>
          </div>
          
          <div class="mb-col-3">
            <label for="status"><?php _e('Status', 'osclass_pay'); ?></label>
            <select name="status">
              <option value="" <?php if(Params::getParam('status') == '') { ?>selected="selected"<?php } ?>><?php _e('All', 'osclass_pay'); ?></option>
              <option value="0" <?php if(Params::getParam('status') == '0') { ?>selected="selected"<?php } ?>><?php _e('In progress', 'osclass_pay'); ?></option>
              <option value="1" <?php if(Params::getParam('status') == '1') { ?>selected="selected"<?php } ?>><?php _e('Shipped', 'osclass_pay'); ?></option>
              <option value="2" <?php if(Params::getParam('status') == '2') { ?>selected="selected"<?php } ?>><?php _e('Completed', 'osclass_pay'); ?></option>
              <option value="9" <?php if(Params::getParam('status') == '9') { ?>selected="selected"<?php } ?>><?php _e('Cancelled', 'osclass_pay'); ?></option>
            </select>
          </div>

          <div class="mb-col-3">
            <label for="sort"><?php _e('Sorting', 'osclass_pay'); ?></label>
            <select name="sort">
              <option value="DESC" <?php if(Params::getParam('sort') == '' || Params::getParam('sort') == 'DESC') { ?>selected="selected"<?php } ?>><?php _e('By ID Descending', 'osclass_pay'); ?></option>
              <option value="ASC" <?php if(Params::getParam('sort') == 'ASC') { ?>selected="selected"<?php } ?>><?php _e('By ID Ascending', 'osclass_pay'); ?></option>
            </select>
          </div>
          
          <div class="mb-col-2">
            <label for="per_page"><?php _e('Per Page', 'osclass_pay'); ?></label>
            <select name="per_page">
              <option value="10" <?php if(Params::getParam('per_page') == '10') { ?>selected="selected"<?php } ?>>10</option>
              <option value="15" <?php if(Params::getParam('per_page') == '15') { ?>selected="selected"<?php } ?>>15</option>
              <option value="25" <?php if(Params::getParam('per_page') == '' || Params::getParam('per_page') == '25') { ?>selected="selected"<?php } ?>>25</option>
              <option value="50" <?php if(Params::getParam('per_page') == '50') { ?>selected="selected"<?php } ?>>50</option>
              <option value="100" <?php if(Params::getParam('per_page') == '100') { ?>selected="selected"<?php } ?>>100</option>
              <option value="200" <?php if(Params::getParam('per_page') == '200') { ?>selected="selected"<?php } ?>>200</option>
              <option value="500" <?php if(Params::getParam('per_page') == '500') { ?>selected="selected"<?php } ?>>500</option>
              <option value="1000" <?php if(Params::getParam('per_page') == '1000') { ?>selected="selected"<?php } ?>>1000</option>
            </select>
          </div>

        </div>
      </form>

      <div class="mb-table mb-table-orders">
        <div class="mb-table-head">
          <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-left"><?php _e('Buyer', 'osclass_pay');?></div>
          <div class="mb-col-3 mb-align-left"><?php _e('Phone/Address', 'osclass_pay');?></div>
          <div class="mb-col-10 mb-align-left"><?php _e('Product/Seller/Price/Status', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-right mb-bold"><?php _e('Amount', 'osclass_pay');?></div>
          <div class="mb-col-3 mb-align-left"><?php _e('Order status/Comment', 'osclass_pay');?></div>
          <div class="mb-col-2"><?php _e('Date', 'osclass_pay');?></div>
          <div class="mb-col-1">&nbsp;</div>
        </div>

        <?php if(count($orders) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No product orders has been found', 'osclass_pay'); ?></span>
          </div>
        <?php } else { ?>
          <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
            <input type="hidden" name="page" value="plugins" />
            <input type="hidden" name="action" value="renderplugin" />
            <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>ecommerce.php" />
            <input type="hidden" name="go_to_file" value="_ecommerce_orders.php" />
            <input type="hidden" name="plugin_action" value="order" />
            <input type="hidden" name="position" value="3" />

            <input type="hidden" name="status" value="<?php echo Params::getParam('status'); ?>" />
            <input type="hidden" name="currency" value="<?php echo Params::getParam('currency'); ?>" />
            <input type="hidden" name="date" value="<?php echo Params::getParam('date'); ?>" />
            <input type="hidden" name="payment" value="<?php echo Params::getParam('payment'); ?>" />
            <input type="hidden" name="user" value="<?php echo Params::getParam('user'); ?>" />
            <input type="hidden" name="id" value="<?php echo Params::getParam('id'); ?>" />
            <input type="hidden" name="address" value="<?php echo Params::getParam('address'); ?>" />
            <input type="hidden" name="comment" value="<?php echo Params::getParam('comment'); ?>" />
            <input type="hidden" name="amount_min" value="<?php echo Params::getParam('amount_min'); ?>" />
            <input type="hidden" name="amount_max" value="<?php echo Params::getParam('amount_max'); ?>" />
            <input type="hidden" name="item" value="<?php echo Params::getParam('item'); ?>" />
            <input type="hidden" name="per_page" value="<?php echo Params::getParam('per_page'); ?>" />
            <input type="hidden" name="sort" value="<?php echo Params::getParam('sort'); ?>" />
            <input type="hidden" name="pageId" value="<?php echo Params::getParam('pageId'); ?>" />


            <?php foreach($orders as $o) { ?>
              <?php 
                // buyer
                $user = array();
                $utitle = '';
                $delivery = '';
                $order_items = $o['order_items'];
                $phone = '';

                if($o['fk_i_user_id'] <> '' && $o['fk_i_user_id'] > 0) {
                  $user = User::newInstance()->findByPrimaryKey($o['fk_i_user_id']);
               
                  if(isset($user['pk_i_id'])) {
                    $delivery = trim(implode(', ', array_filter(array($user['s_country'], $user['s_region'], $user['s_city'], $user['s_city_area'], $user['s_zip'], $user['s_address'])))); 
                    $phone = trim($user['s_phone_mobile'] <> '' ? $user['s_phone_mobile'] : $user['s_phone_land']);
                    $utitle = osc_esc_html($user['s_name'] . PHP_EOL . $user['s_email'] . PHP_EOL . __('Reg. date', 'osclass_pay') . ': ' . $user['dt_reg_date']);
                  }
                }

                $seller_title = __('Owner of listing, click to send mail.', 'osclass_pay');

                // payment
                $payment = ModelOSP::newInstance()->getPayment($o['fk_i_payment_id']);
                $payment_title  = __('ID', 'osclass_pay') . ': ' . $payment['pk_i_id'] . PHP_EOL;
                $payment_title .= __('Transaction', 'osclass_pay') . ': ' . $payment['s_code'] . PHP_EOL;
                $payment_title .= __('Gateway', 'osclass_pay') . ': ' . $payment['s_source'] . PHP_EOL;
                $payment_title .= __('Description', 'osclass_pay') . ': ' . $payment['s_concept'] . PHP_EOL;
                $payment_title .= __('Amount', 'osclass_pay') . ': ' . osp_format_price($payment['i_amount']/1000000000000, 9, $payment['s_currency_code']);
              ?>


              <div class="mb-table-row">
                <div class="mb-col-1"><?php echo $o['pk_i_id']; ?></div>
                <div class="mb-col-2 mb-align-left mb-user">
                  <span class="<?php echo ($utitle <> '' ? 'mb-has-tooltip-light' : ''); ?>" title="<?php echo $utitle; ?>"><?php echo (isset($user['s_name']) ? $user['s_name'] : sprintf(__('User #%s', 'osclass_pay'), $o['fk_i_user_id'])); ?></span>
                </div>
                <div class="mb-col-3 mb-align-left mb-delivery nw">
                  <div class="mb-phone <?php echo ($phone == '' ? 'mb-not-set' : ''); ?>"><?php echo ($phone <> '' ? $phone : __('Phone not set!', 'osclass_pay')); ?></div>
                  <div class="mb-address <?php echo ($delivery == '' ? 'mb-not-set' : ''); ?>"><?php echo ($delivery <> '' ? $delivery : __('Address not set!', 'osclass_pay')); ?></div>
                </div>
                <div class="mb-col-10 mb-align-left mb-items">
                  <?php foreach($order_items as $oi) { ?>
                    <?php 
                      $oi_user = User::newInstance()->findByPrimaryKey($oi['fk_i_user_id']);
                      $item_data = ModelOSP::newInstance()->getItemData($oi['fk_i_item_id']);
                    ?>
                    
                    <div class="mb-oi">
                      <span class="mb-oiq"><?php echo $oi['i_quantity']; ?>x</span>

                      <?php if($oi['fk_i_shipping_id'] !== null) { ?>
                        <div class="mb-ois"><span><?php echo sprintf(__('Shipping: %s', 'osclass_pay'), $oi['s_title']); ?></span></div>
                      <?php } else { ?>
                        <span class="mb-oii">
                          <?php if(isset($item_data['i_shipping']) && $item_data['i_shipping'] == 1) { ?>
                            <i class="fa fa-truck osp-icon-tanitan mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Tangible product, must be shipped', 'osclass_pay')); ?>"></i>
                          <?php } else if(isset($item_data['i_shipping']) && $item_data['i_shipping'] == 0) { ?>
                            <i class="fa fa-wrench osp-icon-tanitan osp-itan mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Intangible product (service), does not require shipping', 'osclass_pay')); ?>"></i>
                          <?php } else { ?>
                            <i class="fa fa-question osp-icon-tanitan osp-utan mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Shipping requirement unknown', 'osclass_pay')); ?>"></i>
                          <?php } ?>                      
                        
                          <a class="mb-oiia" target="_blank" href="<?php echo osc_item_url_ns($oi['fk_i_item_id']); ?>"><?php echo osc_highlight(($oi['s_title'] <> '' ? $oi['s_title'] : sprintf(__('Item #%s', 'osclass_pay'), $oi['fk_i_item_id'])), 50); ?></a>
                        </span>
                      <?php } ?>
                      
                      <a class="mb-oiu" target="_blank" href="<?php echo osc_admin_base_url(true); ?>?page=users&action=edit&id=<?php echo $oi['fk_i_user_id']; ?>"><?php echo osc_highlight(@$oi_user['s_name'] <> '' ? $oi_user['s_name'] : __('N/A', 'osclass_pay'), 20); ?></a>
                      <strong><?php echo osp_format_price($oi['f_amount'], 9, $oi['s_currency_code']); ?></strong>
                      
                      <?php if($oi['fk_i_shipping_id'] === null) { ?>
                        <span class="mb-status mb-st-<?php echo $oi['i_status']; ?>"><?php echo osp_order_status_name($oi['i_status']); ?></span>
                      <?php } ?>
                    </div>
                  <?php } ?>
                </div>

                <div class="mb-col-2 mb-align-right nw">
                  <strong class="mb-has-tooltip-light" title="<?php echo osc_esc_html($o['s_amount_comment']); ?>"><?php echo osp_format_price($o['f_amount'], 9, $o['s_currency_code']); ?></strong>
                  
                  <?php if($o['f_amount_regular'] > $o['f_amount']) { ?>
                    <div class="mb-regular"><?php echo osp_format_price($o['f_amount_regular'], 9, $o['s_currency_code']); ?></div>
                  <?php } ?>
                </div>

                <div class="mb-col-3 <?php if($status_disable == 1) { ?>mb-bt-status<?php } ?>">
                  <?php if($status_disable <> 1) { ?>
                    <select name="stat_<?php echo $o['pk_i_id']; ?>" id="item-sell" style="margin:-5px 0 2px 0;">
                      <option value="<?php echo OSP_ORDER_PROCESSING; ?>" <?php if($o['i_status'] == OSP_ORDER_PROCESSING) { ?>selected="selected"<?php } ?>><?php _e('Processing', 'osclass_pay'); ?></option>
                      <option value="<?php echo OSP_ORDER_SHIPPED; ?>" <?php if($o['i_status'] == OSP_ORDER_SHIPPED) { ?>selected="selected"<?php } ?>><?php _e('Shipped', 'osclass_pay'); ?></option>
                      <option value="<?php echo OSP_ORDER_COMPLETED; ?>" <?php if($o['i_status'] == OSP_ORDER_COMPLETED) { ?>selected="selected"<?php } ?>><?php _e('Completed', 'osclass_pay'); ?></option>
                      <option value="<?php echo OSP_ORDER_CANCELLED; ?>" <?php if($o['i_status'] == OSP_ORDER_CANCELLED) { ?>selected="selected"<?php } ?>><?php _e('Cancelled', 'osclass_pay'); ?></option>
                    </select>
                  <?php } else { ?>
                    <span class="st1"><i class="fa fa-check"></i><?php _e('Completed', 'osclass_pay'); ?></span>
                  <?php } ?>
                  
                  <input type="text" id="order-comment" style="margin:2px 0 -5px 0;" name="comm_<?php echo $o['pk_i_id']; ?>" value="<?php echo osc_esc_html($o['s_comment']); ?>" placeholder="<?php echo osc_esc_html(__('Order comment or note', 'osclass_pay')); ?>" />
                </div>

                <div class="mb-col-2 mb-has-tooltip-light mb-date" title="<?php echo osc_esc_html($o['dt_date']); ?>"><?php echo date('j. M Y', strtotime($o['dt_date'])); ?></div>
                <div class="mb-col-1 mb-payment"><i class="fa fa-list-ul mb-has-tooltip" title="<?php echo osc_esc_html($payment_title); ?>"></i></div>
              </div>
            <?php } ?>
            
            <?php 
              $param_string = '&go_to_file=_ecommerce_orders.php&status=' . Params::getParam('status') . '&currency=' . Params::getParam('currency') . '&date=' . Params::getParam('date') . '&payment=' . Params::getParam('payment') . '&user=' . Params::getParam('user') . '&id=' . Params::getParam('id') . '&address=' . Params::getParam('address') . '&comment=' . Params::getParam('comment') . '&amount_min=' . Params::getParam('amount_min') . '&amount_max=' . Params::getParam('amount_max') . '&item=' . Params::getParam('item') . '&per_page=' . Params::getParam('per_page') . '&sort=' . Params::getParam('sort');
              echo osp_admin_paginate('osclass_pay/admin/ecommerce.php', Params::getParam('pageId'), $per_page, $count_all, '', $param_string); 
            ?>

            <div class="mb-row">&nbsp;</div>

            <div class="mb-foot">
              <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
            </div>
          </form>
        <?php } ?>

      </div>
    </div>
  </div>
</div>

<?php echo osp_footer(); ?>