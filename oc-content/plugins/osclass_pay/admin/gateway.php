<?php
  // Create menu
  $title = __('Payment Gateways', 'osclass_pay');
  osp_menu($title);

  $links = array();
  $links[] = array('file' => '_gateway_transfer.php', 'icon' => 'fa-bank', 'title' => __('Bank Transfers Management', 'osclass_pay'));
  $links[] = array('file' => '_gateway_settings.php', 'icon' => 'fa-wrench', 'title' => __('Settings', 'osclass_pay'));


  $file = osp_submenu('gateway.php', $links, Params::getParam('go_to_file'));  //core, links, current

  require_once $file;
?>