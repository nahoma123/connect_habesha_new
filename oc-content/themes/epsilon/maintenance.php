<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo eps_language_dir(); ?>" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
  <head>
    <?php osc_current_web_theme_path('head.php') ; ?>
    <meta name="robots" content="noindex, nofollow" />
    <meta name="googlebot" content="noindex, nofollow" />
  </head>
  <body id="maintenanc" style="display:block !important;">
    <?php osc_goto_first_locale(); ?>
    <div class="">
        <section class="content loc-error sec-default">
          <div class="inside">
            <div class="maintenance">
              <div class="logo">
                <a href="<?php echo osc_base_url(); ?>"><?php echo eps_logo(); ?></a>
              </div>
              <h1><?php _e('Maintenance', 'epsilon'); ?></h1>
              <h2><?php _e('OOOPS! We are sorry, page is undergoing maintenance.', 'epsilon'); ?></h2>
              <h3><?php _e('Please come back later. Thank you!', 'epsilon'); ?></h3>
            </div>
          </div>
        </section>
    </div> 
    <footer>
  <?php osc_run_hook('footer_top'); ?>
  
  <div class="container">
    <section class="one">
      <div class="col contact">
        <h4><?php _e('About us', 'epsilon'); ?></h4>

        <p class="logo"><?php echo eps_logo(); ?></p>
        <?php if(eps_param('site_name') <> '') { ?><p class="company"><strong><?php echo eps_param('site_name'); ?></strong></p><?php } ?>
        <?php if(eps_param('site_phone') <> '') { ?><p><?php echo __('Phone', 'epsilon') . ': ' . eps_param('site_phone'); ?></p><?php } ?>
        <?php if(eps_param('site_email') <> '') { ?><p><?php echo __('Email', 'epsilon') . ': ' . eps_param('site_email'); ?></p><?php } ?>
        <?php if(eps_param('site_address') <> '') { ?><p><?php echo eps_param('site_address'); ?></p><?php } ?>

        <div class="quick-links">
          <?php if(function_exists('im_messages')) { ?>
            <a class="btn-mini" href="<?php echo osc_route_url('im-threads'); ?>"><?php _e('Messages', 'epsilon'); ?></a>
          <?php } ?>

          <?php if(function_exists('fi_make_favorite')) { ?>
            <a class="btn-mini" href="<?php echo osc_route_url('favorite-lists'); ?>"><?php _e('Favorite', 'epsilon'); ?></a>
          <?php } ?>
          
          <?php if(function_exists('bpr_companies_url')) { ?>
            <a class="btn-mini" href="<?php echo bpr_companies_url(); ?>"><?php _e('Companies', 'epsilon'); ?></a>
          <?php } ?>

          <?php if(function_exists('frm_home')) { ?>
            <a class="btn-mini" href="<?php echo frm_home(); ?>"><?php _e('Forums', 'epsilon'); ?></a>
          <?php } ?>
        
          <?php if(function_exists('blg_home_link')) { ?>
            <a class="btn-mini" href="<?php echo blg_home_link(); ?>"><?php _e('Blog', 'epsilon'); ?></a>
          <?php } ?>
          
          <?php if(function_exists('faq_home_link')) { ?>
            <a class="btn-mini" href="<?php echo faq_home_link(); ?>"><?php _e('FAQ', 'epsilon'); ?></a>
          <?php } ?>
          
          <?php osc_run_hook('footer_links'); ?>
        </div>
      </div>
      
      <div class="col socialx">
        <h4><?php _e('Social media', 'epsilon'); ?></h4>

        <?php osc_reset_resources(); ?>
        
        <?php if(eps_get_social_link('whatsapp') !== false) { ?>
          <a class="whatsapp" href="<?php echo eps_get_social_link('whatsapp'); ?>" data-action="share/whatsapp/share"><i class="fab fa-whatsapp"></i> <?php _e('Whatsapp', 'epsilon'); ?></a>
        <?php } ?>

        <?php if(eps_get_social_link('facebook') !== false) { ?>
          <a class="facebook" href="<?php echo eps_get_social_link('facebook'); ?>" title="<?php echo osc_esc_html(__('Share us on Facebook', 'epsilon')); ?>" target="_blank"><i class="fab fa-facebook-f"></i> <?php _e('Facebook', 'epsilon'); ?></a>
        <?php } ?>

        <?php if(eps_get_social_link('pinterest') !== false) { ?>
          <a class="pinterest" href="<?php echo eps_get_social_link('pinterest'); ?>" title="<?php echo osc_esc_html(__('Share us on Pinterest', 'epsilon')); ?>" target="_blank"><i class="fab fa-pinterest-p"></i> <?php _e('Pinterest', 'epsilon'); ?></a>
        <?php } ?>

        <?php if(eps_get_social_link('instagram') !== false) { ?>
          <a class="instagram" href="<?php echo eps_get_social_link('instagram'); ?>" title="<?php echo osc_esc_html(__('Share us on Instagram', 'epsilon')); ?>" target="_blank"><i class="fab fa-instagram"></i> <?php _e('Instagram', 'epsilon'); ?></a>
        <?php } ?>
        
        <?php if(eps_get_social_link('x') !== false) { ?>
          <a class="twitter" href="<?php echo eps_get_social_link('x'); ?>" title="<?php echo osc_esc_html(__('Tweet us', 'epsilon')); ?>" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="15px" height="15px"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
            <?php _e('Twitter', 'epsilon'); ?>
          </a>
        <?php } ?>

        <?php if(eps_get_social_link('linkedin') !== false) { ?>
          <a class="linkedin" href="<?php echo eps_get_social_link('linkedin'); ?>" title="<?php echo osc_esc_html(__('Share us on LinkedIn', 'epsilon')); ?>" target="_blank"><i class="fab fa-linkedin"></i> <?php _e('LinkedIn', 'epsilon'); ?></a>
        <?php } ?>
      </div>
      
      <div class="col pages">
        <h4><?php _e('Information', 'epsilon'); ?></h4>

        <?php osc_reset_static_pages(); ?>
       
        <?php while(osc_has_static_pages()) { ?>
          <a href="<?php echo osc_static_page_url(); ?>"><?php echo osc_static_page_title();?></a>
        <?php } ?>
        
        <?php if(eps_param('footer_link')) { ?>
          <a href="https://osclass-classifieds.com">Osclass Classifieds</a>
        <?php } ?>
      </div>

      <?php if(osc_count_web_enabled_locales() > 1) { ?>
        <div class="col locale">
          <h4><?php _e('Change language', 'epsilon'); ?></h4>
          <?php osc_goto_first_locale(); ?>

          <?php while(osc_has_web_enabled_locales()) { ?>
            <a class="lang <?php if (osc_locale_code() == osc_current_user_locale()) { ?>active<?php } ?>" href="<?php echo osc_change_language_url(osc_locale_code()); ?>">
              <img src="<?php echo eps_country_flag_image(strtolower(substr(osc_locale_code(), 3))); ?>" alt="<?php echo osc_esc_html(__('Country flag', 'epsilon')); ?>" />
              <span><?php echo osc_locale_name(); ?>&#x200E;</span>
            </a>
          <?php } ?>
        </div>
      <?php } ?>
      
      <div class="footer-hook"><?php osc_run_hook('footer'); ?></div>
      <div class="footer-widgets"><?php osc_show_widgets('footer'); ?></div>
    </section>
    
    <section class="two">
      <?php if(getBoolPreference('web_contact_form_disabled') != 1) { ?>
        <a href="<?php echo osc_contact_url(); ?>"><?php _e('Contact us', 'epsilon'); ?></a>
      <?php } ?>
      
      <?php if(eps_param('footer_link')) { ?>
        <a href="https://osclasspoint.com">Osclass Market</a>
      <?php } ?>
      
      <span><?php _e('Copyright', 'epsilon'); ?> &copy; <?php echo date('Y'); ?> <?php echo eps_param('site_name'); ?> <?php _e('All rights reserved', 'epsilon'); ?>.</span>
    </section>
  </div>
</footer>
  </body>
</html>