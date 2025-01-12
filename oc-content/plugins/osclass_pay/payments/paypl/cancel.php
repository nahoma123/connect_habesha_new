<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  $data = osp_get_custom(Params::getParam('extra'));

  if(osc_is_web_user_logged_in()) { 
    $url = osc_route_url('osp-cart');
  } else {
    View::newInstance()->_exportVariableToView('item', Item::newInstance()->findByPrimaryKey($data['itemid']));
    $url = osc_item_url();
  }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html" charset=iso-8859-1" />
    <script type="text/javascript" src="https://www.paypalobjects.com/js/external/dg.js"></script>
    <title><?php echo osc_page_title(); ?></title>
  </head>

  <body>
    <?php 
      if(osp_param('paypal_standard') == 1) {
        osc_add_flash_info_message(__('Payment has been canceled', 'osclass_pay'));
        osp_js_redirect_to($url);
      }
    ?>

    <script type="text/javascript">
      <?php if($url!='') { ?>
        top.rd.innerHTML = '<?php _e('Payment has been canceled', 'osclass_pay'); ?>.<br/><br/><?php _e('If you do not want to continue the process', 'osclass_pay'); ?> <a href="<?php echo $url; ?>" /><?php _e('click here', 'osclass_pay'); ?></a>';
      <?php } else { ?>
        top.rd.innerHTML = '<?php _e('Payment has been canceled', 'osclass_pay'); ?>.</a>';
      <?php } ?>

      top.dg_<?php echo $data['random'];?>.closeFlow();
    </script>
  </body>
</html>