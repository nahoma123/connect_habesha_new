<?php
  // Create menu
  $title = __('Items Promotion', 'osclass_pay');
  osp_menu($title);

  $links = array();
  $links[] = array('file' => '_premium.php', 'icon' => 'fa-star', 'title' => __('Mark as Premium', 'osclass_pay'));
  $links[] = array('file' => '_publish.php', 'icon' => 'fa-plus-circle', 'title' => __('Pay per Publish', 'osclass_pay'));
  $links[] = array('file' => '_highlight.php', 'icon' => 'fa-lightbulb-o', 'title' => __('Highlight', 'osclass_pay'));
  $links[] = array('file' => '_image.php', 'icon' => 'fa-image', 'title' => __('Pay to Show Images', 'osclass_pay'));
  $links[] = array('file' => '_movetotop.php', 'icon' => 'fa-arrow-circle-up', 'title' => __('Move to Top', 'osclass_pay'));
  $links[] = array('file' => '_republish.php', 'icon' => 'fa-repeat', 'title' => __('Pay for Republish', 'osclass_pay'));

  $file = osp_submenu('item.php', $links, Params::getParam('go_to_file'));  //core, links, current

  require_once $file;
?>