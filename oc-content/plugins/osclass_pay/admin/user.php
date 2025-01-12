<?php
  // Create menu
  $title = __('User Settings', 'osclass_pay');
  osp_menu($title);

  $links = array();
  $links[] = array('file' => '_wallet.php', 'icon' => 'fa-folder-open', 'title' => __('Wallet & Packs', 'osclass_pay'));
  $links[] = array('file' => '_group.php', 'icon' => 'fa-group', 'title' => __('User Groups', 'osclass_pay'));

  $file = osp_submenu('user.php', $links, Params::getParam('go_to_file'));  //core, links, current

  require_once $file;
?>

