<?php
  $check = osp_category_restrict_check(Params::getParam('category'));
  $block = $check[0];
  $array = $check[2];
  $array_ids = array();

  if(count($array) > 0) {
    foreach($array as $a) {
      $array_ids[] = $a['group_id'];
    }
  }

  $html = '';
?>


<?php if($block) { ?>
  <div class="osp-restrict-category-wrap">
    <div class="osp-restrict-category">
      <i class="fa fa-eye-slash"></i>
      <strong class="osp-restrict-line"><?php _e('This content is for premium members only!', 'osclass_pay'); ?></strong>

      <?php if(count($array) > 0) { ?>
        <div class="osp-restrict-line"><?php _e('You need to be member of one of following user groups to be able to see content.', 'osclass_pay'); ?></div>

        <div class="osp-restrict-groups">
          <?php 
            $is_restricted_category = 1;
            $groups_allowed = $array_ids;
            require_once 'group.php'; 
          ?>
        </div>
      <?php } ?>

      <div class="osp-restrict-line"><a href="<?php echo osc_route_url('osp-membership'); ?>"><?php _e('Become a premium member', 'osclass_pay'); ?></a></div>

    </div>
  </div>
<?php } ?>