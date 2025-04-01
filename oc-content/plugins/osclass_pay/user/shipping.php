<?php
  osp_user_menu('order');

  $user_id = osc_logged_user_id();
  $page_id = osc_esc_html(Params::getParam('pageId') > 0 ? Params::getParam('pageId') : 0);
  $status = osc_esc_html(Params::getParam('status') != '' ? Params::getParam('status') : 'ALL');
  $edit_id = osc_esc_html(Params::getParam('editId'));

  //ModelOSP::newInstance()->generateOrderItems();
  

  $params = Params::getParamsAsArray();
  
  
  // Remove shipping
  if(Params::getParam('removeId') > 0) {
    $ship = ModelOSP::newInstance()->getShipping(Params::getParam('removeId'));
    
    if(isset($ship['fk_i_user_id']) && $ship['fk_i_user_id'] == $user_id) {
      ModelOSP::newInstance()->deleteShipping(osc_esc_html(Params::getParam('removeId')));
      osc_add_flash_ok_message(__('Shipping has been successfully removed', 'osclass_pay'));
      header('Location:' . osc_route_url('osp-shipping'));
      exit;
    }
  }
  

  if(Params::getParam('what') == 'updateShipping') {
    $stat = (Params::getParam('s_status') == 1 ? 1 : 0);
    
    if(floatval(Params::getParam('f_fee')) <= 0) {
      osc_add_flash_error_message(__('Shipping fee cannot be 0, shipping option has been disabled until price is fixed.', 'osclass_pay'));
      $stat = 0;
    }
    
    $data = array(
      'fk_i_user_id' => osc_logged_user_id(),
      's_name' => (trim(Params::getParam('s_name')) == '' ? __('Standard', 'osclass_pay') : osc_esc_html(Params::getParam('s_name'))),
      's_description' => osc_esc_html(Params::getParam('s_description')),
      's_delivery' => osc_esc_html(Params::getParam('s_delivery')),
      'f_fee' => osc_esc_html(floatval(Params::getParam('f_fee')) > 0 ? floatval(Params::getParam('f_fee')) : 0),
      'fk_c_country_code' => osc_esc_html(Params::getParam('fk_c_country_code')),
      'fk_c_currency_code' => osp_currency(),
      'i_speed' => osc_esc_html(Params::getParam('i_speed')),
      'i_status' => $stat,
      'dt_date' => date('Y-m-d H:i:s')
    );

    $ship = ModelOSP::newInstance()->getShipping(Params::getParam('pk_i_id'), osc_logged_user_id());
    
  
    if(isset($ship['fk_i_user_id']) && $ship['fk_i_user_id'] == $user_id) {
      ModelOSP::newInstance()->updateShipping($ship['pk_i_id'], $data);
      osc_add_flash_ok_message(__('Shipping option has been successfully updated', 'osclass_pay'));
    } else {
      ModelOSP::newInstance()->insertShipping($data);
      osc_add_flash_ok_message(__('Shipping option has been successfully created', 'osclass_pay'));
    }

    header('Location:' . osc_route_url('osp-shipping'));
    exit;    
  }
  
  
  $is_edit = false;
  $shipping = array();
  
  if($edit_id > 0 && $edit_id != 'new') {
    $is_edit = true;
    $shipping = ModelOSP::newInstance()->getShipping($edit_id, $user_id);
  } else if($edit_id == 'new') {
    $is_edit = true;
    $edit_id = -1;
  }

 
  $shippings = ModelOSP::newInstance()->getUserShippings($user_id);

  $is_seller = osp_user_is_seller(osc_logged_user_id());
  
  if(!$is_seller || osp_param('enable_shipping') != 1) {
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
      <?php if(osp_param('enable_user_management') == 1) { ?><a href="<?php echo osc_route_url('osp-manager'); ?>"><?php _e('Orders mng.', 'osclass_pay'); ?></a><?php } ?>
      <?php if(osp_param('enable_shipping') == 1) { ?><a href="<?php echo osc_route_url('osp-shipping'); ?>" class="osp-active"><?php _e('Shipping', 'osclass_pay'); ?></a><?php } ?>
    <?php } ?>
  </div>

  <div class="osp-tab osp-active">
   
    <?php if($is_edit === true) { ?>
      <form class="osp-form-shipping" action="<?php echo osc_route_url('osp-shipping'); ?>" method="POST">
        <input type="hidden" name="what" value="updateShipping"/>
        <input type="hidden" name="pk_i_id" value="<?php echo osc_esc_html($edit_id); ?>"/>
        <input type="hidden" name="fk_c_currency_code" value="<?php echo osc_esc_html(osp_currency()); ?>"/>
        <input type="hidden" name="fk_i_user_id" value="<?php echo osc_esc_html($user_id); ?>"/>
        
        <strong class="osp-t">
          <?php if($edit_id > 0) { ?>
            <?php _e('Edit shipping option', 'osclass_pay'); ?>
          <?php } else { ?>
            <?php _e('Create a new shipping option', 'osclass_pay'); ?>
          <?php } ?>
        </strong>

        
        <div class="osp-row">
          <label for="s_name"><?php _e('Currier name', 'osclass_pay'); ?></label>
          <div class="osp-input"><input type="text" name="s_name" value="<?php echo isset($shipping['s_name']) ? $shipping['s_name'] : ''; ?>" required/></div>
        </div>

        <div class="osp-row">
          <label for="s_description"><?php _e('Description', 'osclass_pay'); ?></label>
          <div class="osp-input"><textarea name="s_description"><?php echo isset($shipping['s_description']) ? $shipping['s_description'] : ''; ?></textarea></div>
        </div>
        
        <div class="osp-row">
          <label for="s_delivery"><?php _e('Delivery time', 'osclass_pay'); ?></label>
          <div class="osp-input"><input type="text" name="s_delivery" value="<?php echo isset($shipping['s_delivery']) ? $shipping['s_delivery'] : ''; ?>" placeholder="<?php echo osc_esc_html(__('2-4 days', 'osclass_pay')); ?>" required/></div>
        </div>
        
        <div class="osp-row">
          <label for="f_fee"><?php _e('Fee for shipping', 'osclass_pay'); ?></label>
          <div class="osp-input">
            <input type="number" step="0.001" min="0.001" name="f_fee" value="<?php echo isset($shipping['f_fee']) ? $shipping['f_fee'] : ''; ?>" required/>
            <div class="osp-input-desc"><?php echo osp_currency(); ?></div>
          </div>
        </div>

        <div class="osp-row">
          <label for="fk_c_country_code"><?php _e('Country restriction', 'osclass_pay'); ?></label>
          <div class="osp-input">
            <?php $countries = Country::newInstance()->listAll(); ?>
            
            <select name="fk_c_country_code">
              <option value=""><?php _e('All countries', 'osclass_pay'); ?>

              <?php if(is_array($countries) && count($countries) > 0) { ?>
                <?php foreach($countries as $ctr) { ?>
                  <option value="<?php echo $ctr['pk_c_code']; ?>" <?php if(@$shipping['fk_c_country_code'] == $ctr['pk_c_code']) { ?>selected="selected"<?php } ?>><?php echo $ctr['s_name']; ?></option>
                <?php } ?>
              <?php } ?>
            </select>
          </div>
        </div>
        
        <div class="osp-row">
          <label for="i_speed"><?php _e('Speed index', 'osclass_pay'); ?></label>
          <div class="osp-input">
            <select name="i_speed">
              <?php for($i=1;$i<=10;$i++) { ?>
                <option value="<?php echo $i; ?>" <?php if(@$shipping['i_speed'] == $i) { ?>selected="selected"<?php } ?>>
                  <?php echo $i . ($i == 1 ? ' - ' . __('Fastest', 'osclass_pay') : '')  . ($i == 5 ? ' - ' . __('Standard', 'osclass_pay') : '') . ($i == 10 ? ' - ' . __('Slowest', 'osclass_pay') : ''); ?>
                </option>
              <?php } ?>
            </select>
          </div>
        </div>
        
        <div class="osp-row">
          <label for="s_status"><?php _e('Status', 'osclass_pay'); ?></label>
          <div class="osp-input">
            <select name="s_status">
              <option value="1" <?php if(@$shipping['i_status'] == 1 || @$shipping['i_status'] == '') { ?>selected="selected"<?php } ?>><?php _e('Enabled', 'osclass_pay'); ?></option>
              <option value="9" <?php if(@$shipping['i_status'] == 9) { ?>selected="selected"<?php } ?>><?php _e('Disabled', 'osclass_pay'); ?></option>
            </select>
          </div>
        </div>
        
        <div class="osp-button-row">
          <?php if($edit_id > 0) { ?>
            <button type="submit" id="osp-update-shipping"><?php _e('Update shipping option', 'osclass_pay'); ?></button>
          <?php } else { ?>
            <button type="submit" id="osp-update-shipping"><?php _e('Add shipping option', 'osclass_pay'); ?></button>
          <?php } ?>
        </div>
      </form>
      
    <?php } else { ?>

      <div class="osp-h2">
        <?php echo sprintf(__('List of your shipping options. Default shipping price, if no option is defined, will be %s', 'osclass_pay'), osp_format_price(osp_param('default_shipping'))); ?>
      </div>
    
      <div class="osp-table-shipping">
        <div class="osp-head-row">
          <div class="osp-col id"><?php _e('ID', 'osclass_pay'); ?></div>
          <div class="osp-col name"><?php _e('Name', 'osclass_pay'); ?></div>
          <div class="osp-col delivery"><?php _e('Delivery', 'osclass_pay'); ?></div>
          <div class="osp-col amount"><?php _e('Amount', 'osclass_pay'); ?></div>
          <div class="osp-col edit">&nbsp;</div>
        </div>

        <?php if(count($shippings) > 0) { ?>
          <div class="osp-table-wrap">
            <?php foreach($shippings as $s) { ?>
              <?php 
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

              <div class="osp-row osp-st-<?php echo ($s['i_status'] == 1 ? 1 : 0); ?>" data-shipping-id="<?php echo $s['pk_i_id']; ?>">
                <div class="osp-col id"><?php echo $s['pk_i_id']; ?></div>
                <div class="osp-col name">
                  <strong><?php echo $s['s_name']; ?></strong>
                  <div><?php echo $s['s_description']; ?></div>
                </div>
                <div class="osp-col delivery">
                  <strong><?php echo $s['s_delivery']; ?> (<?php echo sprintf(__('Index: %s', 'osclass_pay'), $s['i_speed']); ?>)</strong>
                  <div><?php echo $location; ?></div>  
                </div>

                <div class="osp-col amount"><?php echo osp_format_price($s['f_fee'], 9, $s['fk_c_currency_code']); ?></div>
                <div class="osp-col edit">
                  <a href="<?php echo osc_route_url('osp-shipping-remove', array('removeId' => $s['pk_i_id'])); ?>" class="osp-btn osp-remove" onclick="return confirm('<?php echo osc_esc_js(__('Are you sure you want to remove this shipping option? Action cannot be undone', 'osclass_pay')); ?>');"><i class="fa fa-trash"></i></a>
                  <a href="<?php echo osc_route_url('osp-shipping-edit', array('editId' => $s['pk_i_id'])); ?>" class="osp-btn osp-edit"><i class="fa fa-edit"></i> <?php _e('Edit', 'osclass_pay'); ?></a>
                </div>
              </div>
            <?php } ?>
          </div>
        <?php } else { ?>
          <div class="osp-row osp-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('You have shipping options', 'osclass_pay'); ?></span>
          </div>
        <?php } ?>
      </div>

      <div class="osp-button-row">
        <a href="<?php echo osc_route_url('osp-shipping-edit', array('editId' => 'new')); ?>" id="osp-add-shipping"><?php _e('Add a new shipping', 'osclass_pay'); ?></a>
      </div>
    <?php } ?>

  </div>
</div>