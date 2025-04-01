<?php
  // Create menu
  // $title = __('Orders', 'osclass_pay');
  // osp_menu($title);


  if(Params::getParam('removeShippingId') > 0) {
    ModelOSP::newInstance()->deleteShipping(Params::getParam('removeShippingId'));
    osc_add_flash_ok_message(__('Shipping option has been successfully removed', 'osclass_pay'), 'admin');
    header('Location:' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=osclass_pay/admin/ecommerce.php&go_to_file=_ecommerce_shipping.php');
    exit;
  }


  // $orders = ModelOSP::newInstance()->getOrders();
  $per_page = (Params::getParam('per_page') > 0 ? Params::getParam('per_page') : 25);
  $params = Params::getParamsAsArray();
  $params['per_page'] = $per_page;

  $shippings = ModelOSP::newInstance()->getShippings($params);
  $count_all = ModelOSP::newInstance()->getShippings($params, true);
?>



<div class="mb-body">
  <?php if(osp_param('enable_shipping') == 0) { ?>
    <div class="mb-errors">
      <div class="mb-line"><?php _e('Shipping is disabled. You can enable it in Settings page.', 'osclass_pay'); ?></div>
    </div>
  <?php } ?>
  
  <div class="mb-box mb-shipping">
    <div class="mb-head"><i class="fa fa-truck"></i> <?php _e('Shippings', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('List of all shipping options created by sellers.', 'osclass_pay'); ?></div>
      </div>
      
      <form name="promo_form" action="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=osclass_pay/admin/ecommerce.php&go_to_file=_ecommerce_shipping.php" method="POST" enctype="multipart/form-data" >
        <input type="hidden" name="shippingSearch" value="1"/>
        
        <div id="mb-search-table">
          <div class="mb-col-2">
            <label for="id"><?php _e('ID', 'osclass_pay'); ?></label>
            <input type="text" name="id" value="<?php echo Params::getParam('id'); ?>" />
          </div>

          <div class="mb-col-8">
            <label for="name"><?php _e('Name', 'osclass_pay'); ?></label>
            <input type="text" name="name" value="<?php echo Params::getParam('name'); ?>" />
          </div>

          <div class="mb-col-4">
            <label for="sort"><?php _e('Sorting', 'osclass_pay'); ?></label>
            <select name="sort">
              <option value="DESC" <?php if(Params::getParam('sort') == '' || Params::getParam('sort') == 'DESC') { ?>selected="selected"<?php } ?>><?php _e('By ID Descending', 'osclass_pay'); ?></option>
              <option value="ASC" <?php if(Params::getParam('sort') == 'ASC') { ?>selected="selected"<?php } ?>><?php _e('By ID Ascending', 'osclass_pay'); ?></option>
            </select>
          </div>
          
          <div class="mb-col-3">
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

      <div class="mb-table mb-table-orders">
        <div class="mb-table-head">
          <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
          <div class="mb-col-3 mb-align-left"><?php _e('User', 'osclass_pay');?></div>
          <div class="mb-col-3 mb-align-left"><?php _e('Name', 'osclass_pay');?></div>
          <div class="mb-col-4 mb-align-left"><?php _e('Description', 'osclass_pay');?></div>
          <div class="mb-col-3 mb-align-left"><?php _e('Delivery', 'osclass_pay');?></div>
          <div class="mb-col-3 mb-align-left"><?php _e('Location restrictions', 'osclass_pay');?></div>
          <div class="mb-col-2 mb-align-right"><?php _e('Fee', 'osclass_pay');?></div>
          <div class="mb-col-2"><?php _e('Date', 'osclass_pay');?></div>
          <div class="mb-col-2">&nbsp;</div>
        </div>

        <?php if(count($shippings) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No shippings has been found', 'osclass_pay'); ?></span>
          </div>
        <?php } else { ?>
          <?php foreach($shippings as $s) { ?>
            <?php
              $user = User::newInstance()->findByPrimaryKey($s['fk_i_user_id']);
              
              $location = array();
              
              if($s['fk_c_country_code'] != '') {
                $country = Country::newInstance()->findByCode($s['fk_c_country_code']);
                $location[] = isset($country['s_name']) ? $country['s_name'] : '';
              }
              
              if($s['fk_i_region_id'] > 0) {
                $region = Region::newInstance()->findByPrimaryKey($s['fk_i_region_id']);
                $location[] = isset($region['s_name']) ? $region['s_name'] : '';
              }
              
              if($s['fk_i_city_id'] > 0) {
                $city = City::newInstance()->findByPrimaryKey($s['fk_i_city_id']);
                $location[] = isset($city['s_name']) ? $city['s_name'] : '';
              }
              
              $location = trim(implode(', ', array_filter($location)));
              $location = ($location == '' ? '-' : $location);
            ?>
            
            <div class="mb-table-row">
              <div class="mb-col-1"><?php echo $s['pk_i_id']; ?></div>
              <div class="mb-col-3 mb-align-left">
                <?php if(isset($user['pk_i_id'])) { ?>
                  <a href="<?php echo osc_admin_base_url(true); ?>?page=users&action=edit&id=<?php echo $s['pk_i_id']; ?>"><?php echo $user['s_name']; ?></a>
                <?php } else { ?>
                  <strong><?php _e('N/A', 'osclass_pay'); ?></strong>
                <?php } ?>
              </div>
              <div class="mb-col-3 mb-align-left mb-bold"><?php echo $s['s_name']; ?></div>
              <div class="mb-col-4 mb-align-left"><?php echo $s['s_description']; ?></div>
              <div class="mb-col-3 mb-align-left"><?php echo $s['s_delivery']; ?></div>
              <div class="mb-col-3 mb-align-left"><?php echo $location; ?></div>
              <div class="mb-col-2 mb-align-right mb-bold"><?php echo osp_format_price($s['f_fee'], 9, $s['fk_c_currency_code']); ?></div>
              <div class="mb-col-2 mb-gray"><?php echo date('Y-m-d', strtotime($s['dt_date'])); ?></div>
              <div class="mb-col-2 mb-align-right">
                <?php if(osp_is_demo()) { ?>
                  <a href="#" class="mb-disabled mb-btn mb-button-red"><i class="fa fa-trash"></i> <span><?php _e('Delete', 'osclass_pay'); ?></span></a>
                <?php } else { ?>
                  <a class="mb-btn mb-button-red" onclick="return confirm('<?php echo osc_esc_js(__('Are you sure you want to remove this shipping option? Action cannot be undone', 'osclass_pay')); ?>');" href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=osclass_pay/admin/ecommerce.php&go_to_file=_ecommerce_shipping.php&removeShippingId=<?php echo $s['pk_i_id']; ?>"><i class="fa fa-trash"></i> <span><?php _e('Delete', 'osclass_pay'); ?></span></a>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
          
          <?php 
            $param_string = '&go_to_file=_ecommerce_shipping.php&id=' . Params::getParam('id') . '&name=' . Params::getParam('name') . '&per_page=' . Params::getParam('per_page') . '&sort=' . Params::getParam('sort');
            echo osp_admin_paginate('osclass_pay/admin/ecommerce.php', Params::getParam('pageId'), $per_page, $count_all, '', $param_string); 
          ?>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<?php echo osp_footer(); ?>