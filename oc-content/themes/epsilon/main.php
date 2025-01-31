<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo eps_language_dir(); ?>"
  lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">

<head>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css" />

  <?php osc_current_web_theme_path('head.php'); ?>
</head>

<body id="home" class="layout-<?php echo (eps_param('home_layout') <> '' ? eps_param('home_layout') : 'default'); ?><?php if (eps_device() <> '') {
           echo ' dvc-' . eps_device();
         } ?>">
  <?php osc_current_web_theme_path('header.php'); ?>

  <?php osc_run_hook('home_top'); ?>

  <?php if (eps_banner('home_top') !== false) { ?>
    <div class="container banner-box<?php if (eps_is_demo()) { ?> is-demo<?php } ?>">
      <div class="inside"><?php echo eps_banner('home_top'); ?></div>
    </div>
  <?php } ?>

  <?php $location_cookie = eps_location_from_cookies(); ?>

  <?php osc_run_hook('home_search_pre'); ?>

  <section class="home-search">
    <div class="container">
      <div class="box">
        <?php if (eps_param('interactive_title') == 1) { ?>
          <h1><?php _e('Where would you like to have fun?', 'epsilon'); ?>
            <div>
              <span class="l1"><?php _e('Addis Ababa?', 'epsilon'); ?></span>
              <span class="l2"><?php _e('Hawassa?', 'epsilon'); ?></span>
              <span class="l3"><?php _e('Adama?', 'epsilon'); ?></span>
              <span class="l4"><?php _e('Dire Dawa?', 'epsilon'); ?></span>
              <span class="l5"><?php _e('Arba Minch?', 'epsilon'); ?></span>
            </div>
          </h1>
        <?php } else { ?>
          <h1><?php _e('What would you like to search?', 'epsilon'); ?></h1>
        <?php } ?>

        <form action="<?php echo osc_base_url(true); ?>" method="GET" class="nocsrf">
          <input type="hidden" name="page" value="search" />

          <?php osc_run_hook('home_search_top'); ?>

          <?php if ($location_cookie['success'] == true) { ?>
            <?php if ($location_cookie['fk_i_city_id'] > 0) { ?>
              <input type="hidden" class="loc-inp" name="sCity"
                value="<?php echo osc_esc_html($location_cookie['fk_i_city_id']); ?>" />
            <?php } else if ($location_cookie['fk_i_region_id'] > 0) { ?>
                <input type="hidden" class="loc-inp" name="sRegion"
                  value="<?php echo osc_esc_html($location_cookie['fk_i_region_id']); ?>" />
            <?php } else if ($location_cookie['fk_c_country_code'] <> '') { ?>
                  <input type="hidden" class="loc-inp" name="sCountry"
                    value="<?php echo osc_esc_html($location_cookie['fk_c_country_code']); ?>" />
            <?php } ?>
          <?php } ?>

          <div class="input-box picker pattern">
            <input type="text" name="sPattern" class="pattern" placeholder="<?php _e('Enter keyword...', 'epsilon'); ?>"
              value="<?php echo osc_esc_html(Params::getParam('sPattern')); ?>" autocomplete="off" />
            <i class="clean fas fa-times-circle"></i>
            <div class="results">
              <div class="loaded"></div>
              <div class="default"><?php eps_default_pattern_content(); ?></div>
            </div>
          </div>

          <button class="btn" type="submit"><i class="fa fa-search"></i>
            <span><?php _e('Search', 'epsilon'); ?></span></button>

          <?php osc_run_hook('home_search_bottom'); ?>
        </form>


        <?php eps_get_latest_searches(20) ?>
        <?php $i = 0; ?>
        <?php if (osc_count_latest_searches() > 0) { ?>
          <div class="latest-search">
            <?php while (osc_has_latest_searches()) { ?>
              <a href="<?php echo osc_search_url(array('page' => 'search', 'sPattern' => osc_esc_html(osc_latest_search_text()))); ?>"
                data-text="<?php echo osc_esc_html(osc_latest_search_text()); ?>"><?php echo osc_highlight(osc_latest_search_text(), 18); ?></a>
              <?php $i++;
              if ($i > 20) {
                break;
              } ?>
            <?php } ?>
          </div>
        <?php } ?>


        <h2><?php _e('Top Cities', 'epsilon'); ?></h2>


        <style>
          /* Mobile Version */
          #mobile-version {
            display: none;
            overflow: hidden;
            width: 100%;
          }

          #mobile-slider {
            width: 100%;
          }

          #mobile-slider .city-box_mobile {
            text-align: center;
          }

          /* Responsive Behavior */
          @media (max-width: 768px) {
            #desktop-version {
              display: none;
            }

            #mobile-version {
              display: block;
            }
          }

          .city_icons_mobile {
            width: 150px;
            height: 100px;
            border-radius: 10px;
            object-fit: cover;
          }

          .city-container_mobile {
            display: flex;
            width: 100%;
            gap: 20px;
          }

          .city-box_mobile {
            flex: 0 0 auto;
            text-align: center;
            cursor: pointer;
          }
        </style>
      </div>
    </div>

    <!-- Desktop Version -->
    <div id="desktop-version">
      <div id="home-cat" class="city-container">
        <?php
        osc_goto_first_category();
        $new_categories = explode(',', eps_param('categories_new'));
        $hot_categories = explode(',', eps_param('categories_hot'));
        ?>

        <a href="<?php echo osc_search_url(array('page' => 'search', 'sCity' => '15235021', 'sLocation' => 'Addis+Ababa')); ?>"
          class="city-box">
          <span class="img-city_icon">
            <img class="city_icons custom-crop" style="object-fit: cover; object-position: 1px center;"
              src="https://connecthabesha.net/oc-content/themes/epsilon/images/ababa_addis.jpeg" />
          </span>

          <h3><span><?php _e('Addis Ababa', 'epsilon'); ?></span></h3>
        </a>

        <a href="<?php echo osc_search_url(array('page' => 'search', 'sCity' => '15235080', 'sLocation' => 'Hawassa+(Awassa)')); ?>"
          class="city-box">
          <span class="img-city_icon">
            <img style="width: 100%; height: 100%; object-fit: cover;     object-position: -55px center;"
              class="city_icons" src="https://connecthabesha.net/oc-content/themes/epsilon/images/hawassa.jpeg" />
          </span>

          <h3><span><?php _e('Hawassa', 'epsilon'); ?></span></h3>
        </a>

        <a href="<?php echo osc_search_url(array('page' => 'search', 'sCity' => '15235017', 'sLocation' => 'Adama')); ?>"
          class="city-box">
          <span class="img-city_icon">
            <img class="city_icons" src="https://connecthabesha.net/oc-content/themes/epsilon/images/adama_3.jpg" />
          </span>

          <h3><span><?php _e('Adama', 'epsilon'); ?></span></h3>
        </a>

        <a href="<?php echo osc_search_url(array('page' => 'search', 'sCity' => '15235043', 'sLocation' => 'Dire+Dawa')); ?>"
          class="city-box">
          <span class="img-city_icon">
            <img class="city_icons"
              style="width: 100%; height: 100%; object-fit: cover; object-position: center; transition: transform 0.3s ease; transform: scale(1.1);"
              src="https://connecthabesha.net/oc-content/themes/epsilon/images/dawa_dire.jpeg" />
          </span>

          <h3><span><?php _e('Dire Dawa', 'epsilon'); ?></span></h3>
        </a>
        <a href="<?php echo osc_search_url(array('page' => 'search', 'sCity' => '15235089', 'sLocation' => 'Arba+Minch')); ?>"
          class="city-box">
          <span class="img-city_icon">
            <img class="city_icons" src="https://connecthabesha.net/oc-content/themes/epsilon/images/minch_arba.jpeg" />
          </span>

          <h3><span><?php _e('Arba Minch', 'epsilon'); ?></span></h3>
        </a>
      </div>
    </div>

    <!-- Mobile Version -->
    <div id="mobile-version">
      <div id="mobile-slider">
        <a href="<?php echo osc_search_url(array('page' => 'search', 'sCity' => '15235021', 'sLocation' => 'Addis+Ababa')); ?>"
          class="city-box_mobile">
          <div>
            <img class="city_icons_mobile"
              style="width: 100%; height: 100%; object-fit: cover; object-position: 1px center;"
              src="https://connecthabesha.net/oc-content/themes/epsilon/images/ababa_addis.jpeg" />
          </div>
          <h3>Addis Ababa</h3>
        </a>
        <a href="<?php echo osc_search_url(array('page' => 'search', 'sCity' => '15235080', 'sLocation' => 'Hawassa+(Awassa)')); ?>"
          class="city-box_mobile">
          <div>
            <img class="city_icons_mobile"
              style="width: 100%; height: 100%; object-fit: cover;     object-position: -65px center;"
              src="https://connecthabesha.net/oc-content/themes/epsilon/images/hawassa.jpeg" />
          </div>
          <h3>Hawassa</h3>
        </a>
        <a href="<?php echo osc_search_url(array('page' => 'search', 'sCity' => '15235017', 'sLocation' => 'Adama')); ?>"
          class="city-box_mobile">
          <div>
            <img class="city_icons_mobile"
              src="https://connecthabesha.net/oc-content/themes/epsilon/images/adama_3.jpg" />
          </div>
          <h3>Adama</h3>
        </a>

        <a href="<?php echo osc_search_url(array('page' => 'search', 'sCity' => '15235043', 'sLocation' => 'Dire+Dawa')); ?>"
          class="city-box_mobile">
          <div>
            <img class="city_icons_mobile"
              style="width: 100%; height: 100%; object-fit: cover;  transform: scale(1.1); /* Slightly zoom in */"
              src="https://connecthabesha.net/oc-content/themes/epsilon/images/dawa_dire.jpeg" />
          </div>
          <h3>Dire Dawa</h3>
        </a>
        <a href="<?php echo osc_search_url(array('page' => 'search', 'sCity' => '15235089', 'sLocation' => 'Arba+Minch')); ?>"
          class="city-box_mobile">
          <div>
            <img class="city_icons_mobile"
              src="https://connecthabesha.net/oc-content/themes/epsilon/images/minch_arba.jpeg" />
          </div>
          <h3>Arba Minch</h3>
        </a>
      </div>
    </div>

  </section>

  <?php osc_run_hook('home_search_after'); ?>

  <?php if (eps_param('location_home') == 1 && $location_cookie['success'] === true) { ?>
    <?php
    $default_items = View::newInstance()->_get('items');
    View::newInstance()->_exportVariableToView('items', eps_location_items($location_cookie));
    ?>

    <section class="home-location">
      <div class="container">
        <div class="block">
          <h2>
            <span><?php echo sprintf(__('Latest listings near %s', 'epsilon'), osc_location_native_name_selector($location_cookie, 's_name')); ?></span>
            <a href="#" class="change-location btn btn-secondary mini">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" width="18" height="18">
                <path
                  d="M347.94 129.86L203.6 195.83a31.938 31.938 0 0 0-15.77 15.77l-65.97 144.34c-7.61 16.65 9.54 33.81 26.2 26.2l144.34-65.97a31.938 31.938 0 0 0 15.77-15.77l65.97-144.34c7.61-16.66-9.54-33.81-26.2-26.2zm-77.36 148.72c-12.47 12.47-32.69 12.47-45.16 0-12.47-12.47-12.47-32.69 0-45.16 12.47-12.47 32.69-12.47 45.16 0 12.47 12.47 12.47 32.69 0 45.16zM248 8C111.03 8 0 119.03 0 256s111.03 248 248 248 248-111.03 248-248S384.97 8 248 8zm0 448c-110.28 0-200-89.72-200-200S137.72 56 248 56s200 89.72 200 200-89.72 200-200 200z">
                </path>
              </svg>
              <?php _e('Change location', 'epsilon'); ?>
            </a>
          </h2>

          <?php if (osc_count_items() > 0) { ?>
            <div class="nice-scroll-wrap">
              <div class="nice-scroll-prev"><i class="fas fa-caret-left"></i></div>

              <div id="location-items" class="products grid nice-scroll">
                <?php
                $c = 1;

                while (osc_has_items()) {
                  eps_draw_item($c, false, 'verytall ' . eps_param('loc_design'));
                  $c++;
                }

                View::newInstance()->_erase('items');
                ?>
              </div>

              <div class="nice-scroll-next"><i class="fas fa-caret-right"></i></div>
            </div>
          <?php } else { ?>
            <div class="empty-alt"><?php _e('No listings found close to your location', 'epsilon'); ?></div>
          <?php } ?>
        </div>
      </div>
    </section>

    <?php View::newInstance()->_exportVariableToView('items', $default_items); ?>
  <?php } ?>


  <?php
  $has_day_offer = 0;
  if (eps_param('enable_day_offer') == 1 && eps_param('day_offer_id') > 0) {
    $day_offer = Item::newInstance()->findByPrimaryKey(eps_param('day_offer_id'));

    if ($day_offer !== false && isset($day_offer['pk_i_id'])) {
      $has_day_offer = 1;
    }
  }

  //osc_get_premiums(eps_param('premium_home_count') - $has_day_offer); 
  $premium_items = eps_premium_items(eps_param('premium_home_count') - $has_day_offer, @$day_offer['pk_i_id']);
  ?>

  <?php if (eps_param('premium_home') == 1 && $premium_items > 0) { ?>
    <?php
    $default_items = View::newInstance()->_get('items');
    View::newInstance()->_exportVariableToView('items', $premium_items);
    ?>
    <?php /*<section class="home-premium">
<div class="container">
<div class="block">
 <h2><?php _e('Today\'s premium selection', 'epsilon'); ?></h2>
 
 <div class="nice-scroll-wrap">
   <div class="nice-scroll-prev"><i class="fas fa-caret-left"></i></div>
   
   <div id="premium-items" class="products grid nice-scroll no-visible-scroll">
     <?php 
       $c = 1; 

       if($has_day_offer == 1) {
         View::newInstance()->_exportVariableToView('item', $day_offer);
         eps_draw_item($c, false, eps_param('premium_home_design'));
         $c++;
       }
       
       while(osc_has_items()) {
         eps_draw_item($c, false, eps_param('premium_home_design'));
         $c++;
       }
     ?>
   </div>
   
   <div class="nice-scroll-next"><i class="fas fa-caret-right"></i></div>
 </div>
</div>
</div>
</section>*/ ?>

    <?php View::newInstance()->_exportVariableToView('items', $default_items); ?>
  <?php } ?>

  <?php osc_run_hook('home_premium'); ?>

  <?php if (function_exists('blg_param') && eps_param('blog_home') == 1) { ?>
    <?php $blogs = ModelBLG::newInstance()->getActiveBlogs(); ?>

    <?php if (is_array($blogs) && count($blogs) > 0) { ?>
      <?php $i = 1; ?>
      <?php $blog_limit = eps_param('blog_home_count'); ?>

      <section class="home-blog">
        <div class="container">
          <div class="block">
            <h2>
              <span><?php _e('News on blog', 'epsilon'); ?></span>
              <a href="<?php echo blg_home_link(); ?>" class="btn btn-secondary mini">
                <svg width="18" height="17" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                  <path
                    d="M448 0H64C28.7 0 0 28.7 0 64v288c0 35.3 28.7 64 64 64h96v84c0 7.1 5.8 12 12 12 2.4 0 4.9-.7 7.1-2.4L304 416h144c35.3 0 64-28.7 64-64V64c0-35.3-28.7-64-64-64zm16 352c0 8.8-7.2 16-16 16H288l-12.8 9.6L208 428v-60H64c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16h384c8.8 0 16 7.2 16 16v288zM164.9 243.2l-4.8 42.8c-.6 5.7 4.2 10.6 10 10l42.8-4.8 85.5-85.5-48-48-85.5 85.5zm159.3-133.9c-7-7-18.4-7-25.4 0l-28.3 28.3 48 48 28.3-28.3c7-7 7-18.4 0-25.4l-22.6-22.6z">
                  </path>
                </svg>
                <?php _e('Explore blog', 'epsilon'); ?>
              </a>
            </h2>

            <div class="blog-box <?php echo (eps_param('blog_home_design') <> 'grid' ? 'list' : 'grid'); ?>">
              <?php foreach ($blogs as $b) { ?>
                <?php if ($i <= $blog_limit) { ?>
                  <a
                    href="<?php echo osc_route_url('blg-post', array('blogSlug' => osc_sanitizeString(blg_get_slug($b)), 'blogId' => $b['pk_i_id'])); ?>">
                    <img src="<?php echo blg_img_link($b['s_image']); ?>"
                      alt="<?php echo osc_esc_html(strip_tags(blg_get_title($b))); ?>" />

                    <div class="data">
                      <h3><?php echo strip_tags(blg_get_title($b)); ?></h3>
                      <div class="desc">
                        <?php echo strip_tags(osc_highlight(blg_get_subtitle($b) <> '' ? blg_get_subtitle($b) : blg_get_description($b), 250)); ?>
                      </div>
                    </div>
                  </a>
                <?php } ?>

                <?php $i++; ?>
              <?php } ?>
            </div>
          </div>
        </div>
      </section>
    <?php } ?>
  <?php } ?>

  <?php if (eps_banner('home_middle') !== false) { ?>
    <div class="container banner-box<?php if (eps_is_demo()) { ?> is-demo<?php } ?>">
      <div class="inside"><?php echo eps_banner('home_middle'); ?></div>
    </div>
  <?php } ?>

  <?php if (function_exists('fi_most_favorited_items') && eps_param('favorite_home') == 1) { ?>
    <?php $favorite_items = eps_favorited_items(4); ?>

    <?php if (count($favorite_items) > 0) { ?>
      <?php
      $default_items = View::newInstance()->_get('items');
      View::newInstance()->_exportVariableToView('items', $favorite_items);
      ?>

      <section class="home-favorite">
        <div class="container">
          <div class="block">
            <h2>
              <span><?php _e('Most favorited listings', 'epsilon'); ?></span>

              <a href="<?php echo osc_route_url('favorite-lists'); ?>" class="btn btn-secondary mini">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="18" height="18">
                  <path
                    d="M528.1 171.5L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6zM388.6 312.3l23.7 138.4L288 385.4l-124.3 65.3 23.7-138.4-100.6-98 139-20.2 62.2-126 62.2 126 139 20.2-100.6 98z" />
                </svg>
                <?php _e('Manage your favorites', 'epsilon'); ?> (<?php echo eps_count_favorite(); ?>)
              </a>
            </h2>

            <div class="nice-scroll-wrap">
              <div class="nice-scroll-prev"><i class="fas fa-caret-left"></i></div>

              <div id="favorite-items" class="products grid nice-scroll no-visible-scroll">
                <?php
                $c = 1;

                while (osc_has_items()) {
                  eps_draw_item($c, false, eps_param('favorite_design'));
                  $c++;
                }
                ?>
              </div>

              <div class="nice-scroll-next"><i class="fas fa-caret-right"></i></div>
            </div>
          </div>
        </div>
      </section>

      <?php View::newInstance()->_exportVariableToView('items', $default_items); ?>
    <?php } ?>
  <?php } ?>


  <?php if (function_exists('bpr_companies_block') && eps_param('company_home') == 1) { ?>
    <?php $sellers = ModelBPR::newInstance()->getSellers(1, -1, -1, 10, '', '', '', 'NEW'); ?>

    <?php if (is_array($sellers) && count($sellers) > 0) { ?>
      <section class="home-business">
        <div class="container">
          <div class="block">
            <h2>
              <span><?php _e('Recommended companies', 'epsilon'); ?></span>

              <a href="<?php echo osc_route_url('bpr-list'); ?>" class="btn btn-secondary mini">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="18" height="18">
                  <path
                    d="M464 128h-80V80c0-26.51-21.49-48-48-48H176c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v256c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V176c0-26.51-21.49-48-48-48zM176 80h160v48H176V80zM54 176h404c3.31 0 6 2.69 6 6v74H48v-74c0-3.31 2.69-6 6-6zm404 256H54c-3.31 0-6-2.69-6-6V304h144v24c0 13.25 10.75 24 24 24h80c13.25 0 24-10.75 24-24v-24h144v122c0 3.31-2.69 6-6 6z">
                  </path>
                </svg>
                <?php _e('Explore companies', 'epsilon'); ?>
              </a>
            </h2>

            <div class="business-box">
              <?php echo bpr_companies_block(eps_param('company_home_count'), 'NEW'); ?>
            </div>
          </div>
        </div>
      </section>
    <?php } ?>
  <?php } ?>




  <?php if (eps_param('users_home') == 1) { ?>
    <?php $users = eps_get_users('by_items', eps_param('users_home_count')); ?>
    <?php if (is_array($users) && count($users) > 0) { ?>
      <section class="home-users">
        <div class="container">
          <div id="users-list-block" class="block">
            <h2>
              <span><?php _e('Best sellers', 'epsilon'); ?></span>
            </h2>

            <div class="nice-scroll-wrap">
              <div class="nice-scroll-prev"><i class="fas fa-caret-left"></i></div>

              <div id="users-list" class="nice-scroll no-visible-scroll">
                <?php
                foreach ($users as $user) {
                  ?>
                  <a href="<?php echo eps_user_public_profile_url($user['pk_i_id']); ?>" class="user">
                    <div class="img">
                      <img class="<?php echo (eps_is_lazy() ? 'lazy' : ''); ?>"
                        src="<?php echo (eps_is_lazy() ? eps_get_load_image() : eps_profile_picture($user['pk_i_id'], 'small')); ?>"
                        data-src="<?php echo eps_profile_picture($user['pk_i_id'], 'small'); ?>"
                        alt="<?php echo osc_esc_html($user['s_name']); ?>" />

                      <?php if (eps_user_is_online($user['pk_i_id'])) { ?>
                        <div class="online" title="<?php echo osc_esc_html(__('User is online', 'epsilon')); ?>"></div>
                      <?php } else { ?>
                        <div class="online off" title="<?php echo osc_esc_html(__('User is offline', 'epsilon')); ?>"></div>
                      <?php } ?>
                    </div>

                    <?php if ($user['b_company'] == 1) { ?>
                      <span class="business"
                        title="<?php echo osc_esc_html(__('Professional seller', 'epsilon')); ?>"><?php _e('Pro', 'epsilon'); ?></span>
                    <?php } ?>

                    <strong class="name"><?php echo $user['s_name']; ?></strong>
                    <span class="items"><?php echo sprintf(__('%d items', 'epsilon'), $user['i_items']); ?></span>
                  </a>
                  <?php
                }
                ?>
              </div>

              <div class="nice-scroll-next"><i class="fas fa-caret-right"></i></div>
            </div>
          </div>
        </div>
      </section>
    <?php } ?>
  <?php } ?>


  <?php View::newInstance()->_exportVariableToView('latestItems', eps_random_items()); ?>

  <?php if (osc_count_latest_items() > 0) { ?>
    <section class="home-latest">
      <div class="container">
        <div class="block">
          <h2><?php _e('Latest Adverts', 'epsilon'); ?></h2>

          <div id="latest-items" class="products grid">
            <?php
            $c = 1;

            while (osc_has_latest_items()) {
              eps_draw_item($c, false, 'medium ' . eps_param('latest_design'));
              $c++;
            }
            ?>
          </div>
        </div>
      </div>
    </section>
  <?php } ?>

  <?php osc_run_hook('home_latest'); ?>


  <?php if (eps_param('recent_home') == 1) { ?>
    <?php $recent_items = eps_recent_ads(eps_param('recent_design'), eps_param('recent_count'), 'onhome', true); ?>

    <?php if (is_array($recent_items) && count($recent_items) > 0) { ?>
      <?php
      $default_items = View::newInstance()->_get('items');
      View::newInstance()->_exportVariableToView('items', $recent_items);
      ?>

      <section class="home-recent">
        <div class="container">
          <div id="recent-ads" class="block onhome">
            <h2>
              <span><?php _e('Recently viewed listings', 'epsilon'); ?></span>
            </h2>

            <div class="nice-scroll-wrap">
              <div class="nice-scroll-prev"><i class="fas fa-caret-left"></i></div>

              <div id="recent-items" class="products grid nice-scroll no-visible-scroll">
                <?php
                $c = 1;

                while (osc_has_items()) {
                  eps_draw_item($c, false, eps_param('recent_design'));
                  $c++;
                }
                ?>
              </div>

              <div class="nice-scroll-next"><i class="fas fa-caret-right"></i></div>
            </div>
          </div>
        </div>
      </section>

      <?php View::newInstance()->_exportVariableToView('items', $default_items); ?>
    <?php } ?>
  <?php } ?>



  <?php if (eps_banner('home_bottom') !== false) { ?>
    <div class="container banner-box<?php if (eps_is_demo()) { ?> is-demo<?php } ?>">
      <div class="inside"><?php echo eps_banner('home_bottom'); ?></div>
    </div>
  <?php } ?>

  <?php osc_run_hook('home_bottom'); ?>

  <?php osc_current_web_theme_path('footer.php'); ?>
  <script src="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js"></script>
  <script>
    $(document).ready(function () {
      // Mobile version carousel
      $('#mobile-slider').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        // autoplay: true,
        autoplaySpeed: 3000,
        arrows: true
      });
    });
  </script>

  <style>
    /* City names animation */
    #mobile-slider img {
      width: 100%;
      border-radius: 50%;
      /* Add this line to make the image round */
      height: 100%;
      /* height: 90vh; */
      object-fit: cover;
      display: block;
      /* margin: 0 auto; */
    }

    .slick-prev,
    .slick-next {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
      background-color: rgba(0, 0, 0, 0.5);
      color: white;
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      cursor: pointer;
    }

    .slick-prev {
      left: 10px;
    }

    .slick-next {
      right: 10px;
    }

    /* Container for the slider */
    #mobile-slider {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      /* Adjust spacing between items */
      justify-content: center;
    }

    /* City box styling */
    .city-box_mobile {
      text-align: center;
      text-decoration: none;
      color: inherit;
    }

    /* Image container */
    .city-box_mobile div {
      width: 200px;
      /* Adjust the size of the circle */
      height: 200px;
      /* Must match the width for a perfect circle */
      border-radius: 50%;
      /* Makes the container round */
      overflow: hidden;
      /* Crops the image to fit the circle */
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto;
      /* Centers the container */
    }

    /* Image styling */
    .city_icons_mobile {
      width: 100%;
      height: 100%;
      object-fit: cover;
      /* Ensures the image covers the container without distortion */
      display: block;
    }

    /* Heading styling */
    .city-box_mobile h3 {
      margin-top: 10px;
      /* Adjust spacing between image and text */
      font-size: 16px;
      /* Adjust font size */
      color: #333;
      /* Adjust text color */
    }

    @media (max-width: 768px) {
      .city-box_mobile div {
        width: 200px;
        height: 200px;
      }

      .city-box_mobile h3 {
        font-size: 14px;
      }
    }

    /* end of city names animation */

    /* Container for the city boxes */
    /* Container for the city boxes */
    #desktop-version {
      width: 100%;
      max-width: 100%;
      margin: 10px;
      padding: 0;
    }

    #home-cat {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-start;
      gap: 10px;
      max-width: 100%;
      margin: 0 auto;
      padding: 0;
    }

    #home-cat a.city-box {
      width: auto;
      flex: 1 1 calc(20% - 8px);
      /* Adjust the width for responsive layout */
      height: 200px;
      /* Fixed height for each item */
      text-align: center;
      margin: 0;
      transition: transform 0.3s ease;
    }

    #home-cat a.city-box:hover {
      transform: scale(1.05);
      /* Slight zoom effect on hover */
    }

    #home-cat a.city-box>div {
      position: relative;
      width: 160px;
      /* Adjust the size of the circle */
      height: 160px;
      /* Same as width to make it round */
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      /* Ensures the image doesn't overflow the circle */
      border-radius: 50%;
      /* Makes the container circular */
    }

    /* Styling the image itself */
    .city_icons {
      width: 160px;
      /* Ensure the image is as large as the container */
      height: 160px;
      /* Same as width for circular aspect */
      border-radius: 50%;
      /* Makes the image circular */
      object-fit: cover;
      /* Ensures the image covers the circular frame without distortion */
      object-position: center;
      /* Default cropping */
      transition: transform 0.3s ease;
      /* Smooth transition for hover effects */
    }

    /* Add control for cropping the image */
    .city_icons.custom-crop {
      object-position: top center;
      /* Custom cropping: aligns the top center of the image */
    }

    /* Optional hover effect for the image */
    .city_icons:hover {
      transform: scale(1.1);
      /* Slight zoom effect on hover for the image */
    }

    /* Heading styling */
    .city-box h3 {
      font-size: 16px;
      /* Adjust font size */
      color: #333;
      /* Adjust text color */
      margin-top: 5px;
    }

    .img-container {
      width: 160px;
      /* Adjust the size */
      height: 160px;
      /* Keep it square */
      border-radius: 50%;
      /* Makes the container circular */
      overflow: hidden;
      /* Ensures image fits inside the circle */
      margin: 0 auto;
      /* Center the circle within the parent */
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f0f0f0;
      /* Optional: Add background to ensure a fallback for empty images */
    }

    .img-city_icon {
      overflow: hidden;
      display: inline-block;
      height: 160px;
      width: 160px;
      border-radius: 50%;
      /* Make the image round */
    }

    .home-search h1 div {
      vertical-align: -10px;
    }
  </style>
</body>

</html>