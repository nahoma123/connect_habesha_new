<?php
  // Create menu 
  //$title = __('Payment Gateways', 'osclass_pay');
  //osp_menu($title);


  // GET & UPDATE PARAMETERS
  // $variable = osp_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value or value_crypt

  $bt_enabled = osp_param('bt_enabled');
  
  // MANAGE BANK TRANSFERS
  if(Params::getParam('btId') <> '' && Params::getParam('btId') > 0 && Params::getParam('status') <> '') {
    $id = Params::getParam('btId');
    $status = Params::getParam('status');

    if($status == 1) {
      // Pay it
      $bt = ModelOSP::newInstance()->getBankTransferById($id);
      $tdata = osp_get_custom($bt['s_extra']);
      $user = User::newInstance()->findByPrimaryKey($bt['i_user_id']);

      if(@$user['pk_i_id'] <= 0 || trim(@$user['s_email']) == '') {
        $user = array(
          'pk_i_id' => 0,
          's_name' => @$tdata['name'],
          's_email' => @$tdata['email']
        );
      }

      ModelOSP::newInstance()->updateBankTransfer($id, $status);
      osc_add_flash_ok_message(__('Bank transfer accepted successfully', 'osclass_pay'), 'admin');

      // pay cart content
      $cart = $bt['s_cart'];

      if($cart <> '' && $bt['dt_date_paid'] == '') {  // run promotion just in case it was not already accepted
        $products = explode('|', $cart);
  
        if(count($products) > 0) {

          // SAVE TRANSACTION LOG
          $payment_id = ModelOSP::newInstance()->saveLog(
            $bt['s_description'], //concept
            $bt['s_transaction'], // transaction code
            $bt['f_price'], //amount
            strtoupper(osp_currency()), //currency
            isset($user['s_email']) ? $user['s_email'] : '', // payer's email
            $bt['i_user_id'], //user
            $bt['s_cart'], // cart string
            OSP_TYPE_MULTIPLE, //product type
            'TRANSFER' //source
          );


          foreach($products as $p) {
            $c = array_merge(array($p), explode('x', $p));

            $type = $c[1];
            $item = array('type' => $c[1], 'quantity' => $c[2], 'item_id' => $c[3], 'payment_id' => $payment_id);

            if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_IMAGE || $type == OSP_TYPE_PREMIUM || $type == OSP_TYPE_HIGHLIGHT || $type == OSP_TYPE_REPUBLISH || $type == OSP_TYPE_TOP) {
              $item = array_merge($item, array('hours' => @$c[4], 'repeat' => @$c[5]));

            } else if($type == OSP_TYPE_PACK) {
              $item = array_merge($item, array('pack_id' => $c[3], 'pack_value' => $c[4], 'pack_user_id' => $bt['i_user_id']));

            } else if($type == OSP_TYPE_MEMBERSHIP) {
              $item = array_merge($item, array('group_id' => $c[3], 'group_days' => $c[4], 'group_user_id' => $bt['i_user_id']));

            } else if($type == OSP_TYPE_BANNER) {
              $item = array_merge($item, array('banner_id' => $c[3], 'banner_budget' => $c[4]));

            } else if($type == OSP_TYPE_PRODUCT) {
              if(osp_param('stock_management') == 1) {
                $item = Item::newInstance()->findByPrimaryKey($c[3]);
                $item_data = ModelOSP::newInstance()->getItemData($c[3]);
                $avl_quantity = isset($item_data['i_quantity']) ? $item_data['i_quantity'] : 0;

                if($c[2] > $avl_quantity) {
                  osc_add_flash_warning_message(sprintf(__('Insufficient quantity on stock for product %s! We have %s items on stock, you have requested %s. Our team may contact you.', 'osclass_pay'), '<strong>' . @$item['s_title'] . '</strong>', $avl_quantity, $c[2]));
                }
              }

              $update_qty = (osp_param('stock_management') == 0 ? 0 : $c[2]);

              ModelOSP::newInstance()->updateItemQuantity($c[3], -$update_qty); 
            }

            $order_id = ModelOSP::newInstance()->createOrder($payment_id);

            if($order_id !== false) {
              osp_email_order($order_id, 1);
            }

            osp_pay_fee($item);
          }
        }
      }

    } else if($status == 2) {
      // Cancel it
      ModelOSP::newInstance()->updateBankTransfer($id, $status);
      osc_add_flash_ok_message(__('Bank transfer cancelled successfully', 'osclass_pay'), 'admin');
    } else if($status == 9) {
      // Remove it
      ModelOSP::newInstance()->deleteBankTransfer($id);
      osc_add_flash_ok_message(__('Bank transfer removed successfully', 'osclass_pay'), 'admin');
    }

    osp_redirect(osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/gateway.php&go_to_file=_gateway_transfer.php'));
  }
?>

<div class="mb-body">

  <!-- BANK TRANSFERS -->
  <?php if($bt_enabled != 1) { ?>
    <div class="mb-row mb-errors">
      <div class="mb-line"><?php _e('Bank transfers are disabled! You can enable bank transfer payment method in Gateway Settings section.', 'osclass_pay'); ?></div>
    </div>
  <?php } ?>
  
  <div class="mb-box mb-transfer">
    <div class="mb-head"><i class="fa fa-id-card"></i> <?php _e('Payment Methods', 'osclass_pay'); ?></div>

    <div class="mb-inside">
      <div class="mb-row mb-notes">
        <div class="mb-line"><?php _e('Bellow are shown all bank transfers ordered by status and date.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('If you have received funds to your bank account, accept payment.', 'osclass_pay'); ?></div>
        <div class="mb-line"><?php _e('Approve transaction just in case you see it on your bank account, this action cannot be undone.', 'osclass_pay'); ?></div>
      </div>

      <div class="mb-table mb-table-transfer">
        <div class="mb-table-head">
          <div class="mb-col-1"><?php _e('ID', 'osclass_pay');?></div>
          <div class="mb-col-3"><?php _e('Transaction', 'osclass_pay'); ?></div>
          <div class="mb-col-2"><?php _e('User', 'osclass_pay'); ?></div>
          <div class="mb-col-3"><?php _e('Variable Symbol', 'osclass_pay'); ?></div>
          <div class="mb-col-3"><?php _e('Amount', 'osclass_pay'); ?></div>
          <div class="mb-col-1">&nbsp;</div>
          <div class="mb-col-2"><?php _e('Status', 'osclass_pay'); ?></div>
          <div class="mb-col-3"><?php _e('Date', 'osclass_pay'); ?></div>
          <div class="mb-col-3"><?php _e('Accept Date', 'osclass_pay'); ?></div>
          <div class="mb-col-3">&nbsp;</div>
        </div>

        <?php $transfers = ModelOSP::newInstance()->getBankTransfers(); ?>

        <?php if(count($transfers) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No bank transfers has been found', 'osclass_pay'); ?></span>
          </div>
        <?php } else { ?>
          <?php foreach($transfers as $t) { ?>
            <?php 
              $tdata = osp_get_custom($t['s_extra']);
              $user = User::newInstance()->findByPrimaryKey($t['i_user_id']);
            ?>

            <div class="mb-table-row">
              <div class="mb-col-1"><?php echo $t['pk_i_id']; ?></div>
              <div class="mb-col-3"><?php echo $t['s_transaction']; ?></div>
              <div class="mb-col-2">
                <?php if(@$user['pk_i_id'] > 0 && trim($user['s_name']) <> '') { ?>
                  <?php echo '<a href="' . osc_admin_base_url(true) . '?page=users&action=edit&id=' . $user['pk_i_id'] . '" target="_blank">' . $user['s_name'] . '</a>'; ?>
                <?php } else if(trim(@$tdata['email']) <> '') { ?>
                  <span title="<?php echo osc_esc_html(@$tdata['email']); ?>" class="mb-has-tooltip"><?php echo (@$tdata['name'] <> '' ? @$tdata['name'] : @$tdata['email']); ?></span>
                <?php } else { ?>
                  <?php echo __('Unknown', 'osclass_pay'); ?>
                <?php } ?>
              </div>
              <div class="mb-col-3"><span class="mb-has-tooltip" title="<?php echo osc_esc_html(__('This code should be used as variable symbol in transaction you have received', 'osclass_pay')); ?>"><?php echo $t['s_variable']; ?></span></div>
              <div class="mb-col-3"><?php echo osp_format_price($t['f_price']); ?></div>
              <div class="mb-col-1">
                <?php if(osp_cart_string_to_title($t['s_cart']) <> '') { ?>
                  <i class="fa fa-search mb-has-tooltip mb-log-details" title="<?php echo osc_esc_html(str_replace('<br/>', PHP_EOL, osp_cart_string_to_title($t['s_cart']))); ?>"></i>
                <?php } ?>
              </div>
              <div class="mb-col-2 mb-bt-status">
                <span class="st<?php echo $t['i_paid']; ?>">
                  <?php
                    if($t['i_paid'] == 0) {
                      echo '<i class="fa fa-hourglass-half"></i> ' . __('Pending', 'osclass_pay');
                    } else if($t['i_paid'] == 1) {
                      echo '<i class="fa fa-check"></i> ' . __('Paid', 'osclass_pay');
                    } else {
                      echo '<i class="fa fa-times"></i> ' . __('Cancelled', 'osclass_pay');
                    }
                  ?>
                </span>
              </div>
              <div class="mb-col-3"><?php echo $t['dt_date']; ?></div>
              <div class="mb-col-3"><?php echo ($t['dt_date_paid'] <> '' ? $t['dt_date_paid'] : '-'); ?></div>
              <div class="mb-col-3 mb-bt-buttons mb-align-right">
                <?php if($t['i_paid'] <> 1) { ?>
                  <a href="<?php echo osc_route_admin_url('osp-admin-transfer', array('btId' => $t['pk_i_id'], 'status' => 1)); ?>" class="mb-btn mb-bt-accept mb-button-green mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Accept payment', 'osclass_pay')); ?>"><i class="fa fa-check"></i> <span><?php _e('Accept', 'osclass_pay'); ?></span></a>
                <?php } ?>

                <?php if($t['i_paid'] == 0) { ?>
                  <a href="<?php echo osc_route_admin_url('osp-admin-transfer', array('btId' => $t['pk_i_id'], 'status' => 2)); ?>" class="mb-btn mb-bt-cancel mb-button-white mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Cancel payment', 'osclass_pay')); ?>"><i class="fa fa-times"></i></a>
                <?php } ?>

                <?php if($t['i_paid'] == 2) { ?>
                  <a href="<?php echo osc_route_admin_url('osp-admin-transfer', array('btId' => $t['pk_i_id'], 'status' => 9)); ?>" class="mb-btn mb-bt-remove mb-button-red mb-has-tooltip-light" title="<?php echo osc_esc_html(__('Remove payment', 'osclass_pay')); ?>" onclick="return confirm('<?php echo osc_esc_js(__('Are you sure you want to remove this transfer? Action cannot be undone.', 'osclass_pay')); ?>')"><i class="fa fa-trash"></i></a>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<?php echo osp_footer(); ?>