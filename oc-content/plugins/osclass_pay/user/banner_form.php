<div id="banner-overlay" class="osp-custom-overlay"></div>
<div id="banner-dialog" class="osp-custom-dialog" style="display:none;">
  <div class="osp-inside">
    <div class="osp-top">
      <span><?php echo osc_esc_html(__('Advertisement', 'osclass_pay')); ?></span>
      <div class="osp-close"><i class="fa fa-times"></i></div>
    </div>

    <div class="osp-bot">
      <?php if(!osc_is_web_user_logged_in()) { ?>
        <div class="osp-response-info">
          <i class="fa fa-exclamation-circle"></i>
          <div><?php _e('You must be logged in!', 'osclass_pay'); ?></div>
          <span><?php _e('Only logged in users can submit banner.', 'osclass_pay'); ?></span>
          <a href="<?php echo osc_user_login_url(); ?>"><?php _e('Log in', 'osclass_pay'); ?></a>
        </div>
      <?php } else { ?>
        <div class="osp-response-success" style="display:none;">
          <i class="fa fa-check-circle"></i>
          <div><?php _e('Your banner has been submitted successfully!', 'osclass_pay'); ?></div>
          <span><?php _e('First, our team will validate your banner. After validation we will send you link to pay for this banner.', 'osclass_pay'); ?></span>
        </div>

        <form id="osp-create-banner" action="<?php echo osc_base_url(true); ?>" method="post" class="nocsrf">
          <input type="hidden" name="banner" value="create" />
          <input type="hidden" name="page" value="ajax" />
          <input type="hidden" name="action" value="runhook" />
          <input type="hidden" name="hook" value="banner" />
          <input type="hidden" name="group" id="group" />

          <p class="bt1">
            <label><?php _e('Banner Name', 'osclass_pay'); ?></label>
            <span class="osp-input-box">
              <input id="name" name="name" type="text" size="20" value="" autocomplete="off" required maxlength="20"/>
              <i class="fa fa-pencil"></i>
            </span>
          </p>

          <p class="bt2">
            <label><?php _e('On Click URL', 'osclass_pay'); ?></label>
            <span class="osp-input-box">
              <input id="url" name="url" type="url" value="" autocomplete="off" required/>
              <i class="fa fa-external-link"></i>
            </span>
          </p>

          <p class="bt3">
            <label><?php _e('HTML Code', 'osclass_pay'); ?></label>
            <textarea id="code" name="code" required></textarea>
            <span class="osp-html-info"><?php _e('No links are allowed in HTML code. Place link into On Click URL field.', 'osclass_pay'); ?></span>
          </p>

          <p class="bt4">
            <label><?php _e('Category', 'osclass_pay'); ?></label>
            <span class="osp-input-box">
              <input type="hidden" name="category" id="category"/>
              <select id="category_multiple" name="category_multiple" multiple>
                <?php osc_goto_first_category(); ?>
                <?php while(osc_has_categories()) { ?>
                  <option value="<?php echo osc_category_id(); ?>"><?php echo osc_category_name(); ?></option>
                <?php } ?>
              </select>
            </span>
          </p>

          <p class="bt5">
            <span class="bprice"><span><?php _e('Price per 1 view', 'osclass_pay'); ?>:</span> <strong><?php echo osp_format_price(osp_param('banner_fee_view'), 1, '', 4); ?></strong></span>
            <span class="bprice"><span><?php _e('Price per 1 click', 'osclass_pay'); ?>:</span> <strong><?php echo osp_format_price(osp_param('banner_fee_click'), 1, '', 4); ?></strong></span>
          </p>

          <p class="bt6">
            <label><?php _e('Your Budget', 'osclass_pay'); ?>*</label>
            <span class="osp-input-box">
              <input id="budget" name="budget" type="text" required autocomplete="off"/>
              <i class="fa fa-dollar"><?php echo osp_currency_symbol(); ?></i>
            </span>
          </p>

          <input type="submit" value="<?php echo osc_esc_html(__('Submit Banner', 'osclass_pay')); ?>">
        </form>
      <?php } ?>
    </div>
  </div>
</div>


<script type="text/javascript">
  // CATEGORY MULTI SELECT
  $('select[name="category_multiple"]').change(function(){
    $(this).siblings('input[type="hidden"]').val($(this).val());
  });


  $(function() {
    $('#osp-create-banner').submit(function(e) {
      e.preventDefault();

      var form = $(this);

      $.ajax({
        url: $(this).attr('action'),
        type:'post',
        data: $(this).serialize(),
        success: function(response){
          form.fadeOut(200);
          form.siblings('.osp-response-success').fadeIn(200);

          //console.log(response);
        }
      });
    });
  });


  function banner_create(group) {
    $('#group').attr('value', group);

    $('#banner-dialog').fadeIn(200).fadeIn(200).css('top', ($(document).scrollTop() + Math.round($(window).height()/10)) + 'px');;
    $('#banner-overlay').fadeIn(200);

    return false;
  }


  $('#banner-dialog .osp-close, #banner-overlay').on('click', function(e){ 
    e.stopPropagation();
    $('.osp-custom-dialog').fadeOut(200);
    $('#banner-overlay').fadeOut(200);
  });
</script>