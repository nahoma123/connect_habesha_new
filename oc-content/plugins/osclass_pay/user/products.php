<?php
  osp_user_menu('order');
  
  $user_id = osc_logged_user_id();
  $params = Params::getParamsAsArray();
  $page_id = (Params::getParam('pageId') > 0 ? Params::getParam('pageId') : 0);


  if(Params::getParam('what') == 'updateProducts') {
    $model = ModelOSP::newInstance();
    
    foreach($params as $key => $val) {
      $key_ = explode('_', $key);
      
      if($key_[0] == 'qty') {
        $item_id = $key_[1];
        
        $data = array(
          'fk_i_item_id' => osc_esc_html($item_id),
          'i_quantity' => osc_esc_html(Params::getParam('qty_' . $item_id)),
          'i_sell' => osc_esc_html(Params::getParam('avl_' . $item_id)),
          'i_shipping' => osc_esc_html(Params::getParam('shp_' . $item_id))
        );
          
        $item = Item::newInstance()->findByPrimaryKey($item_id);
        
        if($item['fk_i_user_id'] == $user_id) {
          $model->updateItemData2($data);
        }
      }
    }
   
    osc_add_flash_ok_message(__('Products has been successfully updated', 'osclass_pay'));
    
    if($page_id > 0) {
      header('Location:' . osc_route_url('osp-products-paginate', array('pageId' => $page_id)));
    } else {
      header('Location:' . osc_route_url('osp-products'));
    }
    
    exit;    
  }
  
  $params = Params::getParamsAsArray();

  $per_page = 20; 
  $params['per_page'] = $per_page;
  $items = ModelOSP::newInstance()->getUserProducts($params);
  $count_all = ModelOSP::newInstance()->getUserProducts($params, true);

  $is_seller = osp_user_is_seller(osc_logged_user_id());
  
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
      <?php if(osp_param('enable_user_management') == 1) { ?><a href="<?php echo osc_route_url('osp-products'); ?>" class="osp-active"><?php _e('Products mng.', 'osclass_pay'); ?></a><?php } ?>
      <?php if(osp_param('enable_user_management') == 1) { ?><a href="<?php echo osc_route_url('osp-manager'); ?>"><?php _e('Orders mng.', 'osclass_pay'); ?></a><?php } ?>
      <?php if(osp_param('enable_shipping') == 1) { ?><a href="<?php echo osc_route_url('osp-shipping'); ?>"><?php _e('Shipping', 'osclass_pay'); ?></a><?php } ?>
    <?php } ?>
  </div>

  <div class="osp-tab osp-active">
    <div class="osp-h2">
      <?php echo __('You can manage your items/products availabilities, quantities, shipping requirements here.', 'osclass_pay'); ?>
    </div>

    <div id="osp-search-box">
      <form action="<?php echo osc_route_url('osp-products'); ?>" method="GET" class="nocsrf">
        <input type="hidden" name="what" value="searchProducts"/>
        <input type="hidden" name="pageId" value="<?php echo osc_esc_html($page_id); ?>"/>
        
        <div class="osp-col w40 kw">
          <label for="ospKeyword"><?php _e('Keyword', 'osclass_pay'); ?></label>
          <input type="text" name="ospKeyword" id="ospKeyword" value="<?php echo osc_esc_html(Params::getParam('ospKeyword')); ?>" placeholder="<?php echo osc_esc_html(__('Item title, description, ...', 'osclass_pay')); ?>"/>
        </div>

        <div class="osp-col w20 av">
          <label for="ospAvailability"><?php _e('Availability', 'osclass_pay'); ?></label>
          <select name="ospAvailability">
            <option value="9" <?php if(!Params::existParam('ospAvailability') || Params::getParam('ospAvailability') == 9) { ?>selected="selected"<?php } ?>><?php _e('All', 'osclass_pay'); ?></option>
            <option value="0" <?php if(Params::existParam('ospAvailability') && Params::getParam('ospAvailability') == 0) { ?>selected="selected"<?php } ?>><?php _e('Not available for sale', 'osclass_pay'); ?></option>
            <option value="1" <?php if(Params::getParam('ospAvailability') == 1) { ?>selected="selected"<?php } ?>><?php _e('Available for sale', 'osclass_pay'); ?></option>
          </select>
        </div>
        
        <div class="osp-col w20 sh">
          <label for="ospShipping"><?php _e('Shipping', 'osclass_pay'); ?></label>
          <select name="ospShipping">
            <option value="9" <?php if(!Params::existParam('ospShipping') || Params::getParam('ospShipping') == 9) { ?>selected="selected"<?php } ?>><?php _e('All', 'osclass_pay'); ?></option>
            <option value="0" <?php if(Params::existParam('ospShipping') && Params::getParam('ospShipping') == 0) { ?>selected="selected"<?php } ?>><?php _e('No shipping fee', 'osclass_pay'); ?></option>
            <option value="1" <?php if(Params::getParam('ospShipping') == 1) { ?>selected="selected"<?php } ?>><?php _e('Require shipping', 'osclass_pay'); ?></option>
          </select>
        </div>

        <div class="osp-col w20 bt">
          <label for="">&nbsp;</label>
          <button type="submit"><i class="fa fa-search"></i> <?php _e('Search', 'osclass_pay'); ?></button>
        </div>
        
        <div class="osp-col w40 chk">
          <input type="checkbox" name="ospLowStock" id="ospLowStock" value="1" <?php if(Params::getParam('ospLowStock') == 1) { ?>checked<?php } ?>/>
          <label for="ospLowStock"><?php _e('Low stock (<= 3 qty)', 'osclass_pay'); ?></label>
        </div>
      </form>
    </div>
    
    <div class="osp-table-products">
      <div class="osp-head-row">
        <div class="osp-col id"><?php _e('ID', 'osclass_pay'); ?></div>
        <div class="osp-col item"><?php _e('Title', 'osclass_pay'); ?></div>
        <div class="osp-col price"><?php _e('Price', 'osclass_pay'); ?></div>
        <div class="osp-col avl"><?php _e('Available', 'osclass_pay'); ?></div>
        <div class="osp-col shipping"><?php _e('Shipping', 'osclass_pay'); ?></div>
        <div class="osp-col qty"><?php _e('Quantity', 'osclass_pay'); ?></div>
      </div>

      <?php if(count($items) > 0) { ?>
        <form action="<?php echo osc_route_url('osp-products'); ?>" method="POST">
          <input type="hidden" name="what" value="updateProducts"/>
          <input type="hidden" name="pageId" value="<?php echo osc_esc_html(Params::getParam('pageId')); ?>"/>
          
          <div class="osp-table-wrap">
            <?php foreach($items as $item) { ?>
              <?php 
                $item_detail = Item::newInstance()->findByPrimaryKey($item['pk_i_id']); 
                View::newInstance()->_exportVariableToView('item', $item_detail); 
                
                $status = 9;
                if(osc_item_price() <= 0) {
                  $status = 9;
                } else if ($item['i_sell'] != 1) {
                  $status = 0;
                } else if ($item['i_quantity'] <= 0) { 
                  $status = 1;
                } else {
                  $status = 2;
                }                
              ?>
              
              <div class="osp-row osp-st-<?php echo $status; ?>">
                <div class="osp-col id"><?php echo $item['pk_i_id']; ?></div>
                <div class="osp-col item"><a target="_blank" href="<?php echo osc_item_url(); ?>"><?php echo osc_highlight(osc_item_title(), 60); ?></a></div>
                <div class="osp-col price"><?php echo osc_item_formatted_price(); ?></div>

                <div class="osp-col avl osp-has-tooltip" <?php if(osc_item_price() <= 0) { ?>title="<?php echo osc_esc_html(__('Product with "Free" and "Check with seller" price cannot be sold.', 'osclass_pay')); ?>"<?php } ?>>
                  <select class="osp-prod-avl" name="avl_<?php echo $item['pk_i_id']; ?>" <?php if(osc_item_price() <= 0) { ?>disabled<?php } ?>>
                    <option value="0" <?php if($item['i_sell'] != 1) { ?>selected="selected"<?php } ?>><?php _e('Not available', 'osclass_pay'); ?></option>
                    <option value="1" <?php if($item['i_sell'] == 1) { ?>selected="selected"<?php } ?>><?php _e('Available for sale', 'osclass_pay'); ?></option>
                  </select>
                </div>
                
                <div class="osp-col shipping osp-has-tooltip" <?php if(osc_item_price() <= 0) { ?>title="<?php echo osc_esc_html(__('Product with "Free" and "Check with seller" price cannot be sold.', 'osclass_pay')); ?>"<?php } ?>>
                  <select class="osp-prod-shipping" name="shp_<?php echo $item['pk_i_id']; ?>" <?php if(osc_item_price() <= 0) { ?>disabled<?php } ?>>
                    <option value="0" <?php if($item['i_shipping'] != 1) { ?>selected="selected"<?php } ?>><?php _e('No shipping fee', 'osclass_pay'); ?></option>
                    <option value="1" <?php if($item['i_shipping'] == 1) { ?>selected="selected"<?php } ?>><?php _e('Require shipping', 'osclass_pay'); ?></option>
                  </select>
                </div>
                
                <div class="osp-col qty osp-has-tooltip" <?php if(osc_item_price() <= 0) { ?>title="<?php echo osc_esc_html(__('Product with "Free" and "Check with seller" price cannot be sold.', 'osclass_pay')); ?>"<?php } ?>>
                  <input class="osp-prod-qty" type="number" <?php if(osc_item_price() <= 0) { ?>disabled<?php } ?> value="<?php echo ($item['i_quantity'] > 0 ? $item['i_quantity'] : 0); ?>" name="qty_<?php echo $item['pk_i_id']; ?>"/>
                </div>
              </div>
            <?php } ?>
          </div>

          <?php echo osp_paginate('osp-products-paginate', Params::getParam('pageId'), $per_page, $count_all, '', array('ospKeyword' => Params::getParam('ospKeyword'), 'ospAvailability' => Params::getParam('ospAvailability'), 'ospShipping' => Params::getParam('ospShipping'))); ?>
          
          <div class="osp-button-row">
            <button type="submit" id="osp-update-prods"><?php _e('Update products', 'osclass_pay'); ?></button>
          </div>
        </form>
      <?php } else { ?>
        <div class="osp-row osp-row-empty">
          <i class="fa fa-warning"></i><span><?php _e('You have no products', 'osclass_pay'); ?></span>
        </div>
      <?php } ?>
    </div>
  </div>
</div>