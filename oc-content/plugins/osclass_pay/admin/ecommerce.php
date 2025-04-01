<?php
  // Create menu
  $title = __('E-commerce', 'osclass_pay');
  osp_menu($title);

  $links = array();
  $links[] = array('file' => '_ecommerce_orders.php', 'icon' => 'fa-shopping-basket', 'title' => __('Orders Management', 'osclass_pay'));
  $links[] = array('file' => '_ecommerce_products.php', 'icon' => 'fa-database', 'title' => __('Product Management', 'osclass_pay'));
  $links[] = array('file' => '_ecommerce_users.php', 'icon' => 'fa-users', 'title' => __('User Management', 'osclass_pay'));
  $links[] = array('file' => '_ecommerce_shipping.php', 'icon' => 'fa-truck', 'title' => __('Shipping', 'osclass_pay'));
  $links[] = array('file' => '_ecommerce_currencies.php', 'icon' => 'fa-exchange', 'title' => __('Currency Rates', 'osclass_pay'));
  $links[] = array('file' => '_ecommerce_settings.php', 'icon' => 'fa-wrench', 'title' => __('Settings', 'osclass_pay'));

  $file = osp_submenu('ecommerce.php', $links, Params::getParam('go_to_file'));  //core, links, current

  require_once $file;
?>