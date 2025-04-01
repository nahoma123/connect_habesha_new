<?php
  // Create menu
  // $title = __('Products Management', 'osclass_pay');
  // osp_menu($title);

  $stock_management = osp_param('stock_management');

  if(Params::getParam('plugin_action') == 'item') {
    $params = Params::getParamsAsArray();

    foreach(array_keys($params) as $p) {
      $detail = explode('_', $p);

      if($detail[0] == 'qty' || $detail[0] == 'sell') {
        $params['qty_' . $detail[1]] = isset($params['qty_' . $detail[1]]) ? $params['qty_' . $detail[1]] : 0;
        ModelOSP::newInstance()->updateItemData2(array(
          'fk_i_item_id' => $detail[1],
          'i_sell' => (Params::getParam('sell_' . $detail[1]) == 1 ? 1 : 0),
          'i_quantity' => (Params::getParam('qty_' . $detail[1]) > 0 ? Params::getParam('qty_' . $detail[1]) : 0),
          'i_shipping' => (Params::getParam('shp_' . $detail[1]) == 1 ? 1 : 0)
        ), 0);
      }
    }

    message_ok(__('Item data were successfully updated', 'osclass_pay'));
  }
  
  
  $per_page = (Params::getParam('per_page') > 0 ? Params::getParam('per_page') : 25);
  $params = Params::getParamsAsArray();

  $items = ModelOSP::newInstance()->getItemDataList2($params);
  $count_all = ModelOSP::newInstance()->getItemDataList2($params, true);
?>



<div class="mb-body">

  <div class="mb-box mb-quantity">
    <div class="mb-head"><i class="fa fa-database"></i> <?php _e('Products Management', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('Bellow are shown quantities and sell status for all your listings.', 'osclass_pay'); ?></div>
      </div>
      
      
      <form name="promo_form" action="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=osclass_pay/admin/ecommerce.php&go_to_file=_ecommerce_products.php" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="orderSearch" value="1"/>
        
        <div id="mb-search-table" class="mb-order-search">
          <div class="mb-col-2">
            <label for="id"><?php _e('ID', 'osclass_pay'); ?></label>
            <input type="text" name="id" value="<?php echo Params::getParam('id'); ?>" />
          </div>

          <div class="mb-col-4">
            <label for="user"><?php _e('User', 'osclass_pay'); ?></label>
            <input type="text" name="user" value="<?php echo Params::getParam('user'); ?>" />
          </div>

          <div class="mb-col-4">
            <label for="item"><?php _e('Item title/description', 'osclass_pay'); ?></label>
            <input type="text" name="item" value="<?php echo Params::getParam('item'); ?>" />
          </div>
          
          <div class="mb-col-3">
            <label for="address"><?php _e('Item location', 'osclass_pay'); ?></label>
            <input type="text" name="address" value="<?php echo Params::getParam('address'); ?>" />
          </div>
         
          <div class="mb-col-3">
            <label for="date"><?php _e('Publish date', 'osclass_pay'); ?></label>
            <input type="text" name="date" value="<?php echo Params::getParam('date'); ?>" placeholder="YYYY-MM-DD"/>
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

          <div class="mb-col-2">
            <label for="">&nbsp;</label>
            <button type="submit" class="mb-button mb-button-black"><i class="fa fa-search"></i> <?php _e('Search', 'osclass_pay'); ?></button>
          </div>
        </div>
      </form>

      <div class="mb-table mb-table-qty">
        <div class="mb-table-head">
          <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
          <div class="mb-col-6 mb-align-left"><?php _e('Item Title', 'osclass_pay');?></div>
          <div class="mb-col-4 mb-align-left"><?php _e('Availability', 'osclass_pay');?></div>
          <div class="mb-col-4 mb-align-left"><?php _e('Shipping', 'osclass_pay');?></div>
          <?php if($stock_management == 1) { ?>
            <div class="mb-col-2"><?php _e('Quantity', 'osclass_pay');?></div>
          <?php } ?>
          <div class="mb-col-7 mb-align-left"><?php _e('Status', 'osclass_pay');?></div>
        </div>

        <?php if(count($items) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No listings has been found', 'osclass_pay'); ?></span>
          </div>
        <?php } else { ?>
          <form name="promo_form" id="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
            <input type="hidden" name="page" value="plugins" />
            <input type="hidden" name="action" value="renderplugin" />
            <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>ecommerce.php" />
            <input type="hidden" name="go_to_file" value="_ecommerce_products.php" />
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
                <div class="mb-col-4 mb-input-col">
                  <select name="sell_<?php echo $i['fk_i_item_id']; ?>" id="item-sell">
                    <option value="1" <?php if($i['i_sell'] == 1) { ?>selected="selected"<?php } ?>><?php _e('Available for sale', 'osclass_pay'); ?></option>
                    <option value="0" <?php if($i['i_sell'] == '' || $i['i_sell'] == 0) { ?>selected="selected"<?php } ?>><?php _e('Not available for sale', 'osclass_pay'); ?></option>
                  </select>
                </div>

                <div class="mb-col-4 mb-input-col">
                  <select name="shp_<?php echo $i['fk_i_item_id']; ?>" id="item-ship">
                    <option value="1" <?php if($i['i_shipping'] == 1) { ?>selected="selected"<?php } ?>><?php _e('Require shipping', 'osclass_pay'); ?></option>
                    <option value="0" <?php if($i['i_shipping'] == '' || $i['i_shipping'] == 0) { ?>selected="selected"<?php } ?>><?php _e('No shipping fee', 'osclass_pay'); ?></option>
                  </select>
                </div>
                
                <?php if($stock_management == 1) { ?>
                  <div class="mb-col-2 mb-input-col">
                    <input type="text" id="item-quantity" name="qty_<?php echo $i['fk_i_item_id']; ?>" value="<?php echo $i['i_quantity']; ?>" />
                  </div>
                <?php } ?>

                <div class="mb-col-7 mb-bt-status mb-align-left">
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

            <?php 
              $param_string = '&go_to_file=_ecommerce_products.php&per_page=' . Params::getParam('per_page');
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