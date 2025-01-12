<?php
  // Create menu
  // $title = __('Product selling', 'osclass_pay');
  // osp_menu($title);

  $selling_allow = osp_param_update('selling_allow', 'plugin_action', 'check', 'plugin-osclass_pay');
  $selling_category = osp_param_update('selling_category', 'plugin_action', 'value', 'plugin-osclass_pay');
  $stock_management = osp_param_update('stock_management', 'plugin_action', 'check', 'plugin-osclass_pay');
  $quantity_show = osp_param_update('quantity_show', 'plugin_action', 'check', 'plugin-osclass_pay');
  $cart_button_hook = osp_param_update('cart_button_hook', 'plugin_action', 'check', 'plugin-osclass_pay');
  $selling_apply_membership = osp_param_update('selling_apply_membership', 'plugin_action', 'check', 'plugin-osclass_pay');
  $seller_all = osp_param_update('seller_all', 'plugin_action', 'check', 'plugin-osclass_pay');
  $status_disable = osp_param_update('status_disable', 'plugin_action', 'check', 'plugin-osclass_pay');
  $filter_button_hook = osp_param_update('filter_button_hook', 'plugin_action', 'check', 'plugin-osclass_pay');
  $enable_shipping = osp_param_update('enable_shipping', 'plugin_action', 'check', 'plugin-osclass_pay');
  $default_shipping = osp_param_update('default_shipping', 'plugin_action', 'value', 'plugin-osclass_pay');
  $enable_user_management = osp_param_update('enable_user_management', 'plugin_action', 'check', 'plugin-osclass_pay');
  $exchangeratesapikey = osp_param_update('exchangeratesapikey', 'plugin_action', 'value', 'plugin-osclass_pay');
  $use_exchange_rates_plugin = osp_param_update('use_exchange_rates_plugin', 'plugin_action', 'check', 'plugin-osclass_pay');

  $selling_category_array = explode(',', $selling_category);
  $category_all = Category::newInstance()->listAll();
  
  if(Params::getParam('plugin_action') == 'done') {
    message_ok(__('Settings were successfully saved', 'osclass_pay'));
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
        <input type="hidden" name="go_to_file" value="_ecommerce_settings.php" />
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

        <div class="mb-row mb-row-select-multiple">
          <label for="selling_category_multiple" class="h8"><span><?php _e('Restrict Product Selling by Category', 'osclass_pay'); ?></span></label> 

          <input type="hidden" name="selling_category" id="selling_category" value="<?php echo $selling_category; ?>"/>
          <select id="selling_category_multiple" name="selling_category_multiple" multiple>
            <?php echo osp_cat_list($selling_category_array, $selling_category_all); ?>
          </select>

          <div class="mb-explain">
            <div class="mb-line"><?php _e('If no category selected, product selling will be enabled in all categories.', 'osclass_pay'); ?></div>
          </div>
        </div>

        <div class="mb-row">
          <label for="seller_all" class="h2"><span><?php _e('Enable All Registered to Sell Products', 'osclass_pay'); ?></span></label> 
          <input name="seller_all" id="seller_all" class="element-slide" type="checkbox" <?php echo ($seller_all == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, all registered users can sell their products. If disabled, you can manually select sellers in User management section.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="stock_management" class="h3"><span><?php _e('Enable Stock Management', 'osclass_pay'); ?></span></label> 
          <input name="stock_management" id="stock_management" class="element-slide" type="checkbox" <?php echo ($stock_management == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, it is possible to set available quantities for products.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="quantity_show" class="h4"><span><?php _e('Show Product Available Quantity', 'osclass_pay'); ?></span></label> 
          <input name="quantity_show" id="quantity_show" class="element-slide" type="checkbox" <?php echo ($quantity_show == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, your customers can see available quantities for each product.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="cart_button_hook" class="h5"><span><?php _e('Hook "Add to Cart" Button to Item Page', 'osclass_pay'); ?></span></label> 
          <input name="cart_button_hook" id="cart_button_hook" class="element-slide" type="checkbox" <?php echo ($cart_button_hook == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, no theme modifications are required in order to enable product selling.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="selling_apply_membership" class="h6"><span><?php _e('Apply Membership Discount', 'osclass_pay'); ?></span></label> 
          <input name="selling_apply_membership" id="selling_apply_membership" class="element-slide" type="checkbox" <?php echo ($selling_apply_membership == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, user membership discount is applied on product as well.', 'osclass_pay'); ?></div>
        </div>


        <div class="mb-row">
          <label for="status_disable" class="h7"><span><?php _e('Disable Order Status', 'osclass_pay'); ?></span></label> 
          <input name="status_disable" id="status_disable" class="element-slide" type="checkbox" <?php echo ($status_disable == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('Disable order status and all orders will have status Completed that cannot be changed.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="filter_button_hook" class="h8"><span><?php _e('Hook "Listing Type" Select Box to Search Page', 'osclass_pay'); ?></span></label> 
          <input name="filter_button_hook" id="filter_button_hook" class="element-slide" type="checkbox" <?php echo ($filter_button_hook == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, select box with listing type is added to search page, once category has been selected. There are 3 entries: All listings, "Buy now" listings, Other listings.', 'osclass_pay'); ?></div>
        </div>
        
        
        <div class="mb-row">
          <label for="enable_user_management" class="h9"><span><?php _e('Enable User Management', 'osclass_pay'); ?></span></label> 
          <input name="enable_user_management" id="enable_user_management" class="element-slide" type="checkbox" <?php echo ($enable_user_management == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, sellers can manage their orders (status) and products (avl. for sale, quantity, shipping).', 'osclass_pay'); ?></div>
        </div>
        
        
        <div class="mb-row">
          <label for="enable_shipping" class="h10"><span><?php _e('Enable Shipping', 'osclass_pay'); ?></span></label> 
          <input name="enable_shipping" id="enable_shipping" class="element-slide" type="checkbox" <?php echo ($enable_shipping == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php _e('When enabled, shippings will be enabled to customers.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="default_shipping" class="h11"><span><?php _e('Default Shipping Price', 'osclass_pay'); ?></span></label> 
          <input name="default_shipping" id="default_shipping" type="number" value="<?php echo $default_shipping; ?>" step="0.01"/>
          <div class="mb-input-desc"><?php echo osp_currency_symbol(); ?></div>

          <div class="mb-explain"><?php _e('Default shipping amount in case seller did not defined custom shipping or no seller shipping match to order criteria.', 'osclass_pay'); ?></div>
        </div>

        <div class="mb-row">
          <label for="exchangeratesapikey" class="h12"><span><?php _e('ExchangeRatesApi.io API key', 'osclass_pay'); ?></span></label> 
          <input name="exchangeratesapikey" id="exchangeratesapikey" size=50 type="password" value="<?php echo $exchangeratesapikey; ?>" />

          <div class="mb-explain"><?php _e('API key to refresh currency exchange rates.', 'osclass_pay'); ?> <a href="https://exchangeratesapi.io/pricing/"><?php _e('Get your API key', 'osclass_pay'); ?></a></div>
        </div>

        <div class="mb-row">
          <label for="use_exchange_rates_plugin"><span><?php _e('Use Exchange Rates Plugin for Conversions', 'osclass_pay'); ?></span></label> 
          <input name="use_exchange_rates_plugin" id="use_exchange_rates_plugin" class="element-slide" type="checkbox" <?php echo ($use_exchange_rates_plugin == 1 ? 'checked' : ''); ?> />
          
          <div class="mb-explain"><?php echo sprintf(__('When enabled, currency conversions and exchange rates will be used from %s and Osclass Pay features & API will not be used.', 'osclass_pay'), '<a href="https://osclasspoint.com/osclass-plugins/payments-and-shopping/exchange-rate-multicurrency-plugin-i92" target="_blank">' . __('Exchange Rates Plugin', 'osclass_pay') . '</a>'); ?></div>
        </div>


        <div class="mb-row">&nbsp;</div>

        <div class="mb-foot">
          <button type="submit" class="mb-button"><?php _e('Update', 'osclass_pay');?></button>
        </div>
      </form>
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
      <div class="mb-row mb-help"><span class="sup">(3)</span> <div class="h3"><?php _e('When enabled, users and admins & users can set available quantities on their products. When quantity is on 0, listing cannot be bought anymore.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(4)</span> <div class="h4"><?php _e('When enabled, available quantities for each product are shown next "Add to cart" button.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(5)</span> <div class="h5"><?php _e('Enable to hook "Add to cart" button into hook item_detail on item page. Then there are no modifications required in order to enable product selling.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(6)</span> <div class="h6"><?php _e('Enable to apply membership discount on price of selling items.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(7)</span> <div class="h7"><?php _e('There will be no order status tracking, status cannot be changed. All existing orders will have status Completed and all new orders will be directly marked as Completed.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><span class="sup">(8)</span> <div class="h8"><?php _e('You may enable product selling just in selected categories. If you select no category, it will be enabled in all categories. Use control key or shift key to select more than one value.', 'osclass_pay'); ?></div></div>
      <div class="mb-row mb-help"><div><?php _e('No matter what is currency set on item, price will be converted into currency used in osclass pay plugin.', 'osclass_pay'); ?></div></div>
    </div>
  </div>
</div>


<?php echo osp_footer(); ?>