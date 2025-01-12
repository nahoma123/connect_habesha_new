<?php
  // Create menu
  $title = __('Product selling', 'osclass_pay');
  osp_menu($title);

  $selling_allow = osp_param_update( 'selling_allow', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $stock_management = osp_param_update( 'stock_management', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $quantity_show = osp_param_update( 'quantity_show', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $cart_button_hook = osp_param_update( 'cart_button_hook', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $selling_apply_membership = osp_param_update( 'selling_apply_membership', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $seller_users = osp_param_update( 'seller_users', 'plugin_action', 'value', 'plugin-osclass_pay' );
  $seller_all = osp_param_update( 'seller_all', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $status_disable = osp_param_update( 'status_disable', 'plugin_action', 'check', 'plugin-osclass_pay' );
  $filter_button_hook = osp_param_update( 'filter_button_hook', 'plugin_action', 'check', 'plugin-osclass_pay' );


  if(Params::getParam('plugin_action') == 'done') {
    message_ok( __('Settings were successfully saved', 'osclass_pay') );
  }


  if(Params::getParam('plugin_action') == 'item') {
    $params = Params::getParamsAsArray();

    foreach(array_keys($params) as $p) {
      $detail = explode('_', $p);

      if($detail[0] == 'qty' || $detail[0] == 'sell') {
        $params['qty_' . $detail[1]] = isset($params['qty_' . $detail[1]]) ? $params['qty_' . $detail[1]] : 0;
        ModelOSP::newInstance()->updateItemData($detail[1], $params['sell_' . $detail[1]], $params['qty_' . $detail[1]], 0);
      }
    }

    message_ok( __('Item data were successfully updated', 'osclass_pay') );
  }


  if(Params::getParam('plugin_action') == 'order') {
    $params = Params::getParamsAsArray();

    foreach(array_keys($params) as $p) {
      $detail = explode('_', $p);

      if($detail[0] == 'status') {

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

      } else if($detail[0] == 'comment') {
        ModelOSP::newInstance()->updateOrderComment($detail[1], $params[$p]);
      }
    }

    message_ok( __('Orders were successfully updated. Products has been restocked.', 'osclass_pay') );
  }



  // ADD USER TO SELLER LIST
  $make_seller = '';
  if(Params::getParam('plugin_action') == 'add_user_to_seller') {
    $email = trim(strtolower(Params::getParam('email')));
    $make_seller = Params::getParam('seller_update');
    $seller_array = array_filter(explode(',', trim($seller_users)));
    $user = User::newInstance()->findByEmail($email);

    if(isset($user['pk_i_id']) && $user['pk_i_id'] <> '' && $user['pk_i_id'] > 0) {
      if($make_seller == 1) { 
        if(!in_array($user['pk_i_id'], $seller_array)) {
          $seller_array[] = $user['pk_i_id'];
          message_ok(sprintf(__('User %s successfully added to seller\'s list. This user is now eligible to sell items.', 'osclass_pay'), '<strong>' . $user['s_name'] . '</strong>'));
        }
      } else {
        if(($key = array_search($user['pk_i_id'], $seller_array)) !== false) {
          unset($seller_array[$key]);
          message_ok(sprintf(__('User %s successfully removed from seller\'s list. This user cannot sell items anymore.', 'osclass_pay'), '<strong>' . $user['s_name'] . '</strong>'));
        }
      }

      $seller_string = implode(',', $seller_array);

      osc_set_preference( 'seller_users', $seller_string, 'plugin-osclass_pay', 'STRING'); 
      osc_reset_preferences();

    } else {
      message_error( __('User not found', 'osclass_pay') );
    }
  }


  // REMOVE SELLER FROM LIST
  if(Params::getParam('what') == 'removeSeller' && Params::getParam('id') <> '' && Params::getParam('id') > 0) {
    $seller_array = array_filter(explode(',', trim(osp_param('seller_users'))));
    $user = User::newInstance()->findByPrimaryKey(Params::getParam('id'));

    if(($key = array_search($user['pk_i_id'], $seller_array)) !== false) {
      unset($seller_array[$key]);
      message_ok(sprintf(__('User %s successfully removed from seller\'s list. This user cannot sell items anymore.', 'osclass_pay'), '<strong>' . $user['s_name'] . '</strong>'));

      $seller_string = implode(',', $seller_array);

      osc_set_preference( 'seller_users', $seller_string, 'plugin-osclass_pay', 'STRING'); 
      osc_reset_preferences();
    }
  }


  // REFRESH CURRENCY RATES
  if(Params::getParam('what') == 'refreshRates') {
    osp_get_currency_rates();
    message_ok(__('Currency rates successfully updated.', 'osclass_pay'));
  }



  // SCROLL TO DIV
  if(Params::getParam('position') == '1') {
    osp_js_scroll('.mb-setting');
  } else if(Params::getParam('position') == '2') {
    osp_js_scroll('.mb-quantity');
  } else if(Params::getParam('position') == '3') {
    osp_js_scroll('.mb-order');
  } else if(Params::getParam('position') == '4') {
    osp_js_scroll('.mb-seller');
  } else if(Params::getParam('position') == '5') {
    osp_js_scroll('.mb-currency');
  }
?>



<div class="mb-body">

  <!-- DEFAULT PARAMETERS -->
  <div class="mb-box mb-setting">
    <div class="mb-head">
      <i class="fa fa-cog"></i> <?php _e('Product Selling Settings', 'osclass_pay'); ?>
    </div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>ecommerce.php" />
        <input type="hidden" name="go_to_file" value="ecommerce.php" />
        <input type="hidden" name="plugin_action" value="done" />
        <input type="hidden" name="position" value="1" />


        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('Keep in mind that all payments for products goes to your account. Users allowed to sell products should be your colleagues!', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="selling_allow" class="h1"><span><?php _e('Enable Product Selling', 'osclass_pay'); ?></span></label> 
          <input name="selling_allow" id="selling_allow" class="element-slide" type="checkbox" <?php echo ($selling_allow == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, selected users can offer products for sell. Payments for products goes to your account.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="seller_all" class="h6"><span><?php _e('Enable All Registered to Sell Products', 'osclass_pay'); ?></span></label> 
          <input name="seller_all" id="seller_all" class="element-slide" type="checkbox" <?php echo ($seller_all == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, all registered users can sell their products. If disabled, you can manually select sellers in section bellow.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="stock_management" class="h2"><span><?php _e('Enable Stock Management', 'osclass_pay'); ?></span></label> 
          <input name="stock_management" id="stock_management" class="element-slide" type="checkbox" <?php echo ($stock_management == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, it is possible to set available quantities for products.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="quantity_show" class="h3"><span><?php _e('Show Product Available Quantity', 'osclass_pay'); ?></span></label> 
          <input name="quantity_show" id="quantity_show" class="element-slide" type="checkbox" <?php echo ($quantity_show == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, your customers can see available quantities for each product.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="cart_button_hook" class="h4"><span><?php _e('Hook "Add to Cart" Button to Item Page', 'osclass_pay'); ?></span></label> 
          <input name="cart_button_hook" id="cart_button_hook" class="element-slide" type="checkbox" <?php echo ($cart_button_hook == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, no theme modifications are required in order to enable product selling.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="selling_apply_membership" class="h5"><span><?php _e('Apply Membership Discount', 'osclass_pay'); ?></span></label> 
          <input name="selling_apply_membership" id="selling_apply_membership" class="element-slide" type="checkbox" <?php echo ($selling_apply_membership == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, user membership discount is applied on product as well.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="status_disable" class="h7"><span><?php _e('Disable Order Status', 'osclass_pay'); ?></span></label> 
          <input name="status_disable" id="status_disable" class="element-slide" type="checkbox" <?php echo ($status_disable == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('Disable order status and all orders will have status Completed that cannot be changed.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="filter_button_hook" class="h4"><span><?php _e('Hook "Listing Type" Select Box to Search Page', 'osclass_pay'); ?></span></label> 
          <input name="filter_button_hook" id="filter_button_hook" class="element-slide" type="checkbox" <?php echo ($filter_button_hook == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, select box with listing type is added to search page, once category has been selected. There are 3 entries: All listings, "Buy now" listings, Other listings.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">&nbsp;</div>

        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
        </div>
      </form>
    </div>
  </div>



  <!-- ADD USER TO SELLERS LIST -->
  <div class="mb-box mb-seller">
    <div class="mb-head"><i class="fa fa-plus-circle"></i> <?php _e('Make users sellers', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>ecommerce.php" />
        <input type="hidden" name="go_to_file" value="ecommerce.php" />
        <input type="hidden" name="plugin_action" value="add_user_to_seller" />
        <input type="hidden" name="position" value="4" />

        <?php
          if(Params::getParam('plugin_action') == 'add_user_to_seller') {
            $user = User::newInstance()->findByEmail($email);

            if(isset($user['pk_i_id']) && $user['pk_i_id'] <> '' && $user['pk_i_id'] > 0) {
              $user_id = $user['pk_i_id'];
              $user_name = $user['s_name'];
              $user_email = $user['s_email'];
            }
          }
        ?>


        <?php if($seller_all == 1) { ?>
          <div class="mb-row mb-errors">
            <div class="mb-line"><?php _e('All users can sell their products! Selection bellow will not be used. You can disable this in section above, option "Enable All Registered to Sell Products".', 'osclass_pay'); ?></div>
          </div>
        <?php } ?>

        <div class="mb-row mb-notes">
          <div class="mb-line"><?php _e('Select users that are allowed to sell their products. Money from payments will go to your account!', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row mb-seller-lookup">
          <div class="mb-line mb-error-block"></div>

          <div class="mb-line">
            <label for="id"><span><?php _e('User ID', 'osclass_pay'); ?></span></label>
            <input type="text" id="id" name="id" readonly="readonly" value="<?php echo (isset($user['pk_i_id']) ? $user['pk_i_id'] : ''); ?>"/>
          </div>

          <div class="mb-line">
            <label for="name"><span><?php _e('Name', 'osclass_pay'); ?></span></label>
            <input type="text" id="name" name="name" placeholder="<?php echo osc_esc_html(__('Type user name or email...', 'osclass_pay')); ?>" value="<?php echo osc_esc_html(isset($user['s_name']) ? $user['s_name'] : ''); ?>"/>
            <div class="mb-explain"><?php _e('Start typing user name or email and select user you want to check from list.', 'osclass_pay'); ?></div>
          </div>

          <div class="mb-line">
            <label for="email"><span><?php _e('Email', 'osclass_pay'); ?></span></label>
            <input type="text" id="email" name="email" readonly="readonly" value="<?php echo (isset($user['s_email']) ? $user['s_email'] : ''); ?>"/>
          </div>

          <div class="mb-row"><div class="mb-line">&nbsp;</div><div class="mb-line" style="border-top:1px solid rgba(0,0,0,0.1);">&nbsp;</div></div>

          <div class="mb-line mb-seller-update-line">
            <label for="seller_update"><span><?php _e('Set User as Seller', 'osclass_pay'); ?></span></label>
            <select id="seller_update" name="seller_update">
              <option value="0" <?php if($make_seller == 0) { ?>selected="selected"<?php } ?>><?php _e('No', 'osclass_pay'); ?></option>
              <option value="1" <?php if($make_seller == 1) { ?>selected="selected"<?php } ?>><?php _e('Yes', 'osclass_pay'); ?></option>
            </select>

            <button type="submit" class="mb-button-green"><i class="fa fa-check"></i> <?php _e('Update', 'osclass_pay');?></button>
          </div>
        </div>
      </form>


      <strong style="float:left;clear:both;margin:40px 0 10px 0;"><?php _e('List of active sellers', 'osclass_pay'); ?></strong>
      <div class="mb-table mb-table-sellers">
        <div class="mb-table-head">
          <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-left"><?php _e('Seller', 'osclass_pay');?></div>
          <div class="mb-col-1"><?php _e('Items', 'osclass_pay');?></div>
          <div class="mb-col-5 mb-align-left"><?php _e('Email', 'osclass_pay');?></div>
          <div class="mb-col-2"><?php _e('Type', 'osclass_pay');?></div>
          <div class="mb-col-6 mb-align-left"><?php _e('Address', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-left"><?php _e('Phone', 'osclass_pay');?></div>
          <div class="mb-col-4 mb-align-left"><?php _e('Web', 'osclass_pay');?></div>
          <div class="mb-col-1">&nbsp;</div>
        </div>

        <?php $sellers = array_filter(explode(',', trim(osp_param('seller_users')))); ?>
          
        <?php if($seller_all == 1) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('You have set that all users can sell, this section will not be used.', 'osclass_pay'); ?></span>
          </div>
        <?php } else if(count($sellers) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No listings has been found', 'osclass_pay'); ?></span>
          </div>
        <?php } else { ?>
          <?php foreach($sellers as $s) { ?>
            <div class="mb-table-row">
              <?php $user = User::newInstance()->findByPrimaryKey($s); ?>

              <div class="mb-col-1"><?php echo $user['pk_i_id']; ?></div>
              <div class="mb-col-2 mb-align-left"><a target="_blank" href="<?php echo osc_admin_base_url(); ?>?page=users&action=edit&id=<?php echo $user['pk_i_id']; ?>"><?php echo $user['s_name']; ?></a></div>
              <div class="mb-col-1"><?php echo $user['i_items']; ?>x</div>
              <div class="mb-col-5 mb-align-left nw"><a href="mailto:<?php echo $user['s_email']; ?>"><?php echo $user['s_email']; ?></a></div>
              <div class="mb-col-2 mb-type t<?php echo $user['b_company']; ?>">
                <span>
                  <?php 
                    if($user['b_company'] == 1) {
                      echo '<i class="fa fa-suitcase"></i> ' . __('Company', 'osclass_pay');
                    } else {
                      echo '<i class="fa fa-user"></i> ' . __('Personal', 'osclass_pay');
                    }
                  ?>
                </span>
              </div>
              <div class="mb-col-6 mb-delivery mb-align-left">
                <?php
                  $addr = trim(implode(', ', array_filter(array($user['fk_c_country_code'], $user['s_region'], $user['s_city'], $user['s_zip'], $user['s_address']))));
                  if($addr <> '') {
                    echo $addr;
                  } else {
                    echo '<em>' . __('Address not set!', 'osclass_pay') . '</em>';
                  }
                ?>
              </div>
              <div class="mb-col-2 mb-align-left">
                <?php
                  $phone = trim(($user['s_phone_mobile'] <> '' ? $user['s_phone_mobile'] : $user['s_phone_land']));
                  if($phone <> '') {
                    echo $phone;
                  } else {
                    echo '<em>' . __('Phone not set!', 'osclass_pay') . '</em>';
                  }
                ?>
              </div>
              <div class="mb-col-4 mb-align-left">
                <?php 
                  if(trim($user['s_website']) <> '') {
                    echo '<a target="_blank" href="' . trim($user['s_website']) . '">' . trim($user['s_website']) . '</a>';
                  }
                ?>
              </div>
              <div class="mb-col-1 mb-align-right"><a class="mb-button-white mb-remove" href="<?php echo osc_admin_base_url(); ?>?page=plugins&action=renderplugin&file=osclass_pay/admin/ecommerce.php&what=removeSeller&id=<?php echo $user['pk_i_id']; ?>"><i class="fa fa-trash-o"></i></a></div>
            </div>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
  </div>


  <div class="mb-box mb-quantity">
    <div class="mb-head"><i class="fa fa-database"></i> <?php _e('Item Data and Quantities', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('Bellow are shown quantities and sell status for all your listings.', 'osclass_pay'); ?></div>
      </div>

      <div class="mb-table mb-table-qty">
        <div class="mb-table-head">
          <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
          <div class="mb-col-6 mb-align-left"><?php _e('Item Title', 'osclass_pay');?></div>
          <div class="mb-col-2"><?php _e('On sale', 'osclass_pay');?></div>
          <?php if($stock_management == 1) { ?>
            <div class="mb-col-2"><?php _e('Quantity', 'osclass_pay');?></div>
          <?php } ?>
          <div class="mb-col-8 mb-align-left"><?php _e('Status', 'osclass_pay');?></div>
        </div>

        <?php 
          $count = ModelOSP::newInstance()->getItemDataList(0, 0, 0, 1)[0]['i_count'];
          $per_page = 25;
          $page = ( Params::getParam('start') <> '' ? floor(Params::getParam('start')/$per_page)+1 : 1 );
          $page_count = ceil($count/$per_page);
          $start = ($page - 1)*$per_page;

          $items = ModelOSP::newInstance()->getItemDataList(0, $start, $per_page); 
        ?>

        <?php if(count($items) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No listings has been found', 'osclass_pay'); ?></span>
          </div>
        <?php } else { ?>
          <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
            <input type="hidden" name="page" value="plugins" />
            <input type="hidden" name="action" value="renderplugin" />
            <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>ecommerce.php" />
            <input type="hidden" name="go_to_file" value="ecommerce.php" />
            <input type="hidden" name="plugin_action" value="item" />
            <input type="hidden" name="position" value="2" />
            <input type="hidden" name="start" value="<?php echo Params::getParam('start'); ?>" />

            <?php
              $sellers = explode(',', trim(osp_param('seller_users')));
              $sellers = array_filter($sellers);
            ?>

            <?php foreach($items as $i) { ?>
              <div class="mb-table-row">
                <?php $item_detail = Item::newInstance()->findByPrimaryKey($i['fk_i_item_id']); ?>

                <div class="mb-col-1"><?php echo $i['fk_i_item_id']; ?></div>
                <div class="mb-col-6 mb-align-left nw">
                  <a href="<?php echo osc_admin_base_url(true); ?>?page=items&action=item_edit&id=<?php echo $i['fk_i_item_id']; ?>" target="_blank"><?php echo $item_detail['s_title']; ?></a>
                </div>
                <div class="mb-col-2 mb-input-col">
                  <select name="sell_<?php echo $i['fk_i_item_id']; ?>" id="item-sell">
                    <option value="1" <?php if($i['i_sell'] == 1) { ?>selected="selected"<?php } ?>><?php _e('Yes', 'osclass_pay'); ?></option>
                    <option value="0" <?php if($i['i_sell'] == '' || $i['i_sell'] == 0) { ?>selected="selected"<?php } ?>><?php _e('No', 'osclass_pay'); ?></option>
                  </select>
                </div>

                <?php if($stock_management == 1) { ?>
                  <div class="mb-col-2 mb-input-col">
                    <input type="text" id="item-quantity" name="qty_<?php echo $i['fk_i_item_id']; ?>" value="<?php echo $i['i_quantity']; ?>" />
                  </div>
                <?php } ?>

                <div class="mb-col-8 mb-bt-status mb-align-left">
                  <?php
                    if(!in_array($item_detail['fk_i_user_id'], $sellers) && osp_param('seller_all') == 0) {
                      echo '<span class="st9"><i class="fa fa-users"></i> ' . __('User is not in seller', 'osclass_pay') . '</span>';
                    } else if($i['i_price'] <= 0 || $i['i_price'] == '') {
                      echo '<span class="st9"><i class="fa fa-dollar"></i> ' . __('Item Price is Free or Check with Seller', 'osclass_pay') . '</span>';
                    } else if($i['i_sell'] == 0) {
                      echo '<span class="st9"><i class="fa fa-times"></i> ' . __('Not allowed', 'osclass_pay') . '</span>';
                    } else if ($i['i_quantity'] <= 0 && $stock_management == 1) {
                      echo '<span class="st2"><i class="fa fa-stack-overflow"></i> ' . __('Unavailable Quantity', 'osclass_pay') . '</span>';
                    } else {
                      echo '<span class="st1"><i class="fa fa-check"></i> ' . __('Active', 'osclass_pay') . '</span>';
                    }
                  ?>
                </div>
              </div>
            <?php } ?>


            <!-- PAGINATION -->
            <?php if($page_count > 1) { ?>
              <div id="mb-pagination">
                <div class="mb-pagination-wrap">
                  <?php $file_path = 'osclass_pay/admin/ecommerce.php&position=2'; ?>
                  <div><?php _e('Page', 'osclass_pay'); ?>:</div> <?php echo osp_add_pagination($count, $per_page, $file_path); ?>
                </div>
              </div>
            <?php } ?>

            <div class="mb-row">&nbsp;</div>

            <div class="mb-foot">
              <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
            </div>
          </form>
        <?php } ?>
      </div>
    </div>
  </div>



  <div class="mb-box mb-order">
    <div class="mb-head"><i class="fa fa-shopping-basket"></i> <?php _e('Item Orders', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('Bellow are shown all product orders.', 'osclass_pay'); ?></div>
      </div>

      <div class="mb-table mb-table-orders">
        <div class="mb-table-head">
          <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-left"><?php _e('Buyer', 'osclass_pay');?></div>
          <div class="mb-col-3 mb-align-left"><?php _e('Delivery Address', 'osclass_pay');?></div>
          <div class="mb-col-5 mb-align-left"><?php _e('Products', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-right"><?php _e('Full Price', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-right mb-bold"><?php _e('Paid', 'osclass_pay');?></div>
          <div class="mb-col-3"><?php _e('Status', 'osclass_pay');?></div>
          <div class="mb-col-3"><?php _e('Comment', 'osclass_pay');?></div>
          <div class="mb-col-2"><?php _e('Date', 'osclass_pay');?></div>
          <div class="mb-col-1">&nbsp;</div>
        </div>

        <?php $orders = ModelOSP::newInstance()->getOrders(); ?>

        <?php if(count($orders) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No product orders has been found', 'osclass_pay'); ?></span>
          </div>
        <?php } else { ?>
          <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
            <input type="hidden" name="page" value="plugins" />
            <input type="hidden" name="action" value="renderplugin" />
            <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>ecommerce.php" />
            <input type="hidden" name="go_to_file" value="ecommerce.php" />
            <input type="hidden" name="plugin_action" value="order" />
            <input type="hidden" name="position" value="3" />

            <?php foreach($orders as $o) { ?>
              <?php 
                // buyer
                $user = array();
                $utitle = '';
                $delivery = '';

                if($o['fk_i_user_id'] <> '' && $o['fk_i_user_id'] > 0) {
                  $user = User::newInstance()->findByPrimaryKey($o['fk_i_user_id']);
               
                  $delivery = trim(implode(', ', array_filter(array($user['fk_c_country_code'], $user['s_region'], $user['s_city'], $user['s_zip'], $user['s_address'])))); 

                  if(isset($user['s_name'])) {
                    $utitle = osc_esc_html(@$user['s_name'] . PHP_EOL . @$user['s_email'] . PHP_EOL . __('Reg. date', 'osclass_pay') . ': ' . @$user['dt_reg_date']);
                  }
                }

                $seller_title = __('Owner of listing, click to send mail.', 'osclass_pay');

                // item
                $items = array_filter(explode(',', trim($o['s_item_id'])));
                $item_text = '';
                $c = 0;
                foreach($items as $i) {
                  $item_detail = Item::newInstance()->findByPrimaryKey($i);

                  $qty = explode('x', array_filter(explode('|', trim($o['s_cart'])))[$c])[1];
 
                  $item_text .= '<span>';
                  $item_text .= '<span>' . $qty . 'x </span>';
                  $item_text .= '<a target="_blank" href="' . osc_admin_base_url(true) . '?page=items&action=item_edit&id=' . $i . '">' . osc_highlight($item_detail['s_title'], 20) . '</a> ';
                  $item_text .= '<a class="mb-seller mb-has-tooltip-light" href="mailto:' . $item_detail['s_contact_email'] . '" title="' . osc_esc_html($seller_title) . '">' . $item_detail['s_contact_name'] . '</a>';
                  $item_text .= '</span>';
                  
                  $c++;
                }


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
                  <span class="<?php echo ($utitle <> '' ? 'mb-has-tooltip-light' : ''); ?>" title="<?php echo $utitle; ?>"><?php echo (isset($user['s_name']) ? $user['s_name'] : __('Unregistered', 'osclass_pay')); ?></span>
                </div>
                <div class="mb-col-3 mb-align-left mb-delivery nw mb-has-tooltip-light <?php echo ($delivery == '' ? 'mb-not-set' : ''); ?>" title="<?php echo osc_esc_html($delivery); ?>"><?php echo ($delivery <> '' ? $delivery : __('Not set!', 'osclass_pay')); ?></div>
                <div class="mb-col-5 mb-align-left mb-items"><?php echo $item_text; ?></div>
                <div class="mb-col-2 mb-align-right nw"><?php echo osp_format_price($o['f_amount_regular'], 9, $o['s_currency_code']); ?></div>
                <div class="mb-col-2 mb-bold mb-align-right nw"><span class="mb-has-tooltip-light" title="<?php echo osc_esc_html($o['s_amount_comment']); ?>"><?php echo osp_format_price($o['f_amount'], 9, $o['s_currency_code']); ?></span></div>

                <div class="mb-col-3 <?php if($status_disable <> 1) { ?>mb-input-col<?php } else { ?>mb-bt-status<?php } ?>">
                  <?php if($status_disable <> 1) { ?>
                    <select name="status_<?php echo $o['pk_i_id']; ?>" id="item-sell">
                      <option value="<?php echo OSP_ORDER_PROCESSING; ?>" <?php if($o['i_status'] == OSP_ORDER_PROCESSING) { ?>selected="selected"<?php } ?>><?php _e('Processing', 'osclass_pay'); ?></option>
                      <option value="<?php echo OSP_ORDER_SHIPPED; ?>" <?php if($o['i_status'] == OSP_ORDER_SHIPPED) { ?>selected="selected"<?php } ?>><?php _e('Shipped', 'osclass_pay'); ?></option>
                      <option value="<?php echo OSP_ORDER_COMPLETED; ?>" <?php if($o['i_status'] == OSP_ORDER_COMPLETED) { ?>selected="selected"<?php } ?>><?php _e('Completed', 'osclass_pay'); ?></option>
                      <option value="<?php echo OSP_ORDER_CANCELLED; ?>" <?php if($o['i_status'] == OSP_ORDER_CANCELLED) { ?>selected="selected"<?php } ?>><?php _e('Cancelled', 'osclass_pay'); ?></option>
                    </select>
                  <?php } else { ?>
                    <span class="st1"><i class="fa fa-check"></i><?php _e('Completed', 'osclass_pay'); ?></span>
                  <?php } ?>
                </div>

                <div class="mb-col-3 mb-input-col">
                  <input type="text" id="order-comment" name="comment_<?php echo $o['pk_i_id']; ?>" value="<?php echo osc_esc_html($o['s_comment']); ?>" />
                </div>

                <div class="mb-col-2 mb-has-tooltip-light" title="<?php echo osc_esc_html($o['dt_date']); ?>"><?php echo date('j. M', strtotime($o['dt_date'])); ?></div>
                <div class="mb-col-1 mb-payment"><i class="fa fa-list-ul mb-has-tooltip" title="<?php echo osc_esc_html($payment_title); ?>"></i></div>
              </div>
            <?php } ?>

            <div class="mb-row">&nbsp;</div>

            <div class="mb-foot">
              <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
            </div>
          </form>
        <?php } ?>
      </div>
    </div>
  </div>


  <!-- CURRENCY LIST -->
  <div class="mb-box mb-currency">
    <div class="mb-head"><i class="fa fa-exchange"></i> <?php _e('Currency Rates', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('In order to make eCommerce functional, it is required to convert any price of item, to currency of plugin, if it is different.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('Plugin use Yahoo service to get currency rate at daily frequency. Make sure your cron is functional.', 'osclass_pay'); ?></div>
      </div>

      <div class="mb-row mb-errors">
        <div class="mb-line"><?php echo sprintf(__('Osclass Pay Plugin has default currency %s and all other currencies are converted to this one', 'osclass_pay'), '<strong>' . osp_currency() . '(' . osp_currency_symbol() . ')</strong>'); ?></div>
      </div>

      <div class="mb-row">
        <div class="mb-line"><?php _e('Your classifieds has configured following currencies as available.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php echo sprintf(__('You can modify this list in %s', 'osclass_pay'), '<a target="_blank" href="' . osc_admin_base_url(true) . '?page=settings&action=currencies">' . __('Settings > Currencies', 'osclass_pay') . '</a>'); ?>.</div>
      </div>

      <div class="mb-row">&nbsp;</div>

      <div class="mb-table mb-table-currencies">
        <div class="mb-table-head">
          <div class="mb-col-1"><?php _e('Code', 'osclass_pay');?></div>
          <div class="mb-col-5 mb-align-left"><?php _e('Name', 'osclass_pay');?></div>
          <div class="mb-col-4"><?php _e('Symbol', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-left"><?php _e('Rate', 'osclass_pay');?></div>
          <div class="mb-col-12 mb-align-left"><?php _e('Detail', 'osclass_pay');?></div>
        </div>

        <?php $currencies = ModelOSP::newInstance()->getCurrencies(); ?>

        <?php foreach($currencies as $code) { ?>
          <?php
            $c = Currency::newInstance()->findByPrimaryKey($code['pk_c_code']);
            $rate = ModelOSP::newInstance()->getRate($c['pk_c_code']);

            $problem = false;
            $def = false;
            if($c['pk_c_code'] == osp_currency()) {
              $rate_text = '-';
              $rate_detail = __('No conversion, osclass pay default currency', 'osclass_pay');
              $def = true;
            } else if($rate == 1.0 && $c['pk_c_code'] <> osp_currency()) {
              $rate_text = '-';
              $rate_detail = __('Not set, run refresh!', 'osclass_pay');
              $problem = true;
            } else {
              $rate_text = number_format($rate, 4);
              $rate_detail  = '<span>1' . osp_currency_symbol() . ' = ' . number_format(1/$rate, 4) . $c['s_description'] . '</span>'; 
              $rate_detail .= '<span>1' . $c['s_description'] . ' = ' . number_format($rate, 4) . osp_currency_symbol() . '</span>'; 

            }
          ?>

          <div class="mb-table-row">
            <div class="mb-col-1"><?php echo $c['pk_c_code']; ?></div>
            <div class="mb-col-5 mb-align-left"><?php echo $c['s_name']; ?></div>
            <div class="mb-col-4"><?php echo $c['s_description']; ?></div>
            <div class="mb-col-2 mb-align-left mb-rate <?php if($problem) { ?>mb-rate-dash<?php } ?>"><?php echo $rate_text; ?></div>
            <div class="mb-col-12 mb-align-left mb-cur-desc <?php if($problem) { ?>mb-rate-null<?php } ?> <?php if($def) { ?>mb-rate-def<?php } ?>"><?php echo $rate_detail; ?></div>
          </div>
        <?php } ?>
      </div>

      <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=osclass_pay/admin/ecommerce.php&what=refreshRates&position=5" class="mb-button-green mb-get-rates"><?php _e('Refresh Rates', 'osclass_pay'); ?></a>

    </div>
  </div>



  <!-- PLUGIN INTEGRATION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-wrench"></i> <?php _e('Plugin Setup', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row">
        <div class="mb-line"><?php _e('If you have not used hook to place "Add to cart" button on item page, you can do it manually by modifying theme files.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('Place following code into your theme files where function osc_item_id() is available (item.php or item-loop-single.php...).', 'osclass_pay'); ?></div>

        <span class="mb-code">&lt;?php if(function_exists('osp_product_to_cart_link')) { echo osp_product_to_cart_link(); } ?&gt;</span>
      </div>
    </div>
  </div>



  <!-- HELP TOPICS -->
  <div class="mb-box" id="mb-help">
    <div class="mb-head"><i class="fa fa-question-circle"></i> <?php _e('Help', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-help"><span class="sup">(1)</span> <div class="h1"><?php _e('Enable to allow direct sales of items on your classifieds. Users marked as sellers can then allow on listings to be purchased. Note that payment for purchase go to your account so only users that you cooperate with should be added to seller list. If listing price is set to Free or Check with seller, listing cannot be purchased by users.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(2)</span> <div class="h2"><?php _e('When enabled, all users can sell their listings. In this case seller list is not used.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(3)</span> <div class="h3"><?php _e('When enabled, users and admins can set available quantities on their products. When quantity is on 0, listing cannot be bought anymore.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(4)</span> <div class="h4"><?php _e('When enabled, available quantities for each product are shown next "Add to cart" button.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(5)</span> <div class="h5"><?php _e('Enable to hook "Add to cart" button into hook item_detail on item page. Then there are no modifications required in order to enable product selling.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(6)</span> <div class="h6"><?php _e('Enable to apply membership discount on price of selling items.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(7)</span> <div class="h7"><?php _e('There will be no order status tracking, status cannot be changed. All existing orders will have status Completed and all new orders will be directly marked as Completed.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><div><?php _e('No matter what is currency set on item, price will be converted into currency used in osclass pay plugin.', 'osclass_pay'); ?></div></div>
    </div>
  </div>
</div>

<?php
  // PREPARE JAVASCRIPT SELLER ARRAY
  $cn = 0;
  $js_seller_array = '';
  foreach(explode(',', osp_param('seller_users')) as $s) {
    if($cn > 0) {
      $js_seller_array .= ',';
    }

    $js_seller_array .= '"' . $s . '"'; 
    $cn++;
  }
?>


<script type="text/javascript">
  var seller_lookup_error = "<?php echo osc_esc_js(__('Error getting data, user not found', 'osclass_pay')); ?>";
  var seller_lookup_url = "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=runhook&hook=osp_wallet_data&id=";
  var seller_lookup_base = "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=userajax";
  var seller_array = [<?php echo $js_seller_array; ?>];
</script>

<?php echo osp_footer(); ?>