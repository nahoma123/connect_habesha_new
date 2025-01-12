<?php 
  $type = Params::getParam('type');
  $item_id = Params::getParam('itemId');
  $what = Params::getParam('what');  // 0 - undo / 1 - do / 2 - require
  $page = Params::getParam('iPage');
  $display = Params::getParam('iDisplayLength');

  if($item_id <> '' && $item_id > 0 && $type <> '') {
    if($what == 1) {
      if($type == OSP_TYPE_PUBLISH || $type == OSP_TYPE_TOP || $type == OSP_TYPE_IMAGE) {
        if($type == OSP_TYPE_PUBLISH) {
          osp_item_active($item_id, 1);
          $message = __('Publish fee marked as paid successfully.', 'osclass_pay');

        } else if($type == OSP_TYPE_TOP) {
          Item::newInstance()->dao->query(sprintf('UPDATE %st_item SET dt_pub_date = "%s" WHERE pk_i_id = %d', DB_TABLE_PREFIX, date('Y-m-d H:i:s'), $item_id));
          Item::newInstance()->dao->query(sprintf('UPDATE %st_item SET dt_mod_date = NULL WHERE pk_i_id = %d', DB_TABLE_PREFIX, $item_id));
          $message = __('Item has been moved to top successfully.', 'osclass_pay');

        } else if($type == OSP_TYPE_IMAGE) {
          $message = __('Image fee marked as paid successfully.', 'osclass_pay');
        }

        ModelOSP::newInstance()->createItem($type, $item_id, 1, date("Y-m-d H:i:s"), -1);

      } else {
        $message = __('Listing has been marked/promoted successfully with no expiration date.', 'osclass_pay');
        ModelOSP::newInstance()->createItem($type, $item_id, 1, date("Y-m-d H:i:s"), -1, '2099-01-01 00:00:00');
      }
    } else if($what == 0) {
      $message = __('Listing has been unmarked successfully.', 'osclass_pay');
      ModelOSP::newInstance()->deleteItem($type, $item_id);
    } else if($what == 2) {
      ModelOSP::newInstance()->createItem($type, $item_id, 0, date("Y-m-d H:i:s"), -1, '');
      $message = __('Payment requirement has been created.', 'osclass_pay');

      if($type == OSP_TYPE_PUBLISH) {
        if(osp_param('publish_item_disable') == 1) {
          osp_item_active($item_id, 0);
        }

        $fee = osp_get_fee(OSP_TYPE_PUBLISH, 1, $item_id);
        $item = Item::newInstance()->findByPrimaryKey($item_id);
        osp_email_promote($item, $fee);

        $message .= ' ' . __('Item has been deactivated and will be visible after publish fee is paid. Email has been send to item owner.', 'osclass_pay');
      }
    }
  }

  osc_add_flash_ok_message($message, 'admin');
  osp_redirect(osc_admin_base_url(true) . '?page=items&iDisplayLength=' . $display . '&iPage=' . $page);
?>