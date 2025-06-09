<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
      dir="<?php echo eps_language_dir(); ?>"
      lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
<head>
  <?php osc_current_web_theme_path('head.php'); ?>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="googlebot" content="noindex, nofollow" />
  <script type="text/javascript"
          src="<?php echo osc_current_web_theme_js_url('jquery.validate.min.js'); ?>">
  </script>
</head>
<?php
  // Fetch all categories
  $categories = Category::newInstance()->findRootCategories();
?>
<body id="body-user-register" class="pre-account register">
  <?php UserForm::js_validation(); ?>
  <?php osc_current_web_theme_path('header.php'); ?>

  <section class="container">
    <div class="box">
      <h1><?php _e('Create a new account', 'epsilon'); ?></h1>

      <!-- Social login buttons -->
      <?php if (function_exists('fl_call_after_install')
             || function_exists('ggl_login_link')
             || function_exists('fjl_login_button')) { ?>
        <div class="social">
          <?php if (function_exists('fl_call_after_install')) { ?>
            <a class="facebook"
               href="<?php echo facebook_login_link(); ?>"
               title="<?php echo osc_esc_html(__('Connect with Facebook','epsilon')); ?>">
              <i class="fab fa-facebook-square"></i>
              <span><?php _e('Continue with Facebook','epsilon'); ?></span>
            </a>
          <?php } ?>
          <?php if (function_exists('ggl_login_link')) { ?>
            <a class="google"
               href="<?php echo ggl_login_link(); ?>"
               title="<?php echo osc_esc_html(__('Connect with Google','epsilon')); ?>">
              <i class="fab fa-google"></i>
              <span><?php _e('Continue with Google','epsilon'); ?></span>
            </a>
          <?php } ?>
          <?php if (function_exists('fjl_login_button')) { ?>
            <a target="_top" href="javascript:void(0);"
               class="facebook fl-button fjl-button"
               onclick="fjlCheckLoginState();"
               title="<?php echo osc_esc_html(__('Connect with Facebook','epsilon')); ?>">
              <i class="fab fa-facebook-square"></i>
              <span><?php _e('Continue with Facebook','epsilon'); ?></span>
            </a>
          <?php } ?>
        </div>
      <?php } ?>

      <a class="alt-action"
         href="<?php echo osc_user_login_url(); ?>">
        <?php _e('Already have an account? Log in','epsilon'); ?> &#8594;
      </a>

      <form name="register" id="register"
            action="<?php echo osc_base_url(true); ?>" method="post">
        <input type="hidden" name="page"  value="register" />
        <input type="hidden" name="action" value="register_post" />
        <!-- Phone-only mode -->
        <input type="hidden" name="s_method"
               id="s_method_hidden" value="1" />

        <?php osc_run_hook('user_pre_register_form'); ?>

        <ul id="error_list"></ul>

        <!-- Name field -->
        <div class="row nm">
          <div class="input-box">
            <?php UserForm::name_text(); ?>
          </div>
        </div>

        <!-- Hidden email—will be auto-filled from the phone on submit -->
<input type="hidden" name="s_email" id="s_email" value="" />


        <!-- Phone field -->
        <div class="row mb">
          <div class="input-box">
            <?php UserForm::mobile_text(osc_user()); ?>
          </div>
          <div style="text-align:left;
                      font-style:italic;
                      font-size:12px;
                      margin-top:-13px;
                      margin-bottom:15px;
                      color:#0178d6;">
            * Your phone number is private. You can change this later in your settings.
          </div>
        </div>

        <!-- Gender selection (no default checked, but still required) -->
        <div class="radio-group">
          <label for="category_id">
            <?php _e('Please select your gender. I am a:','epsilon'); ?>
            <span class="req">*</span>
          </label>
          <?php if (!empty($categories)) {
            foreach ($categories as $index => $category) { ?>
              <div class="radio-option"
                   style="margin-top:<?php echo ($index>0)?'8px':'0'; ?>;">
                <label class="radio-label">
                  <input type="radio"
                         name="category_id"
                         value="<?php echo $category['pk_i_id']; ?>"
                         required />
                  <span class="radio-text">
                    <?php echo $category['s_name']; ?>
                  </span>
                </label>
              </div>
          <?php }
          } else { ?>
            <p><?php _e('No categories available.','epsilon'); ?></p>
          <?php } ?>
        </div>

        <!-- (rest of your form: communication methods, passwords, TOS, recaptcha, etc.) -->

        <div class="row p1">
          <div class="input-box">
            <?php UserForm::password_text(); ?>
            <a href="#" class="toggle-pass"
               title="<?php echo osc_esc_html(__('Show/hide password','epsilon')); ?>">
              <i class="fa fa-eye-slash"></i>
            </a>
          </div>
        </div>
        <div class="row p2">
          <div class="input-box">
            <?php UserForm::check_password_text(); ?>
            <a href="#" class="toggle-pass"
               title="<?php echo osc_esc_html(__('Show/hide password','epsilon')); ?>">
              <i class="fa fa-eye-slash"></i>
            </a>
          </div>
        </div>

        <div class="row p2">
          <div class="td-wrap d1 input-box"
               style="width:100%;font-size:11px">
            <?php _e('By creating an account, you agree to our','epsilon'); ?>
            <a href="<?php echo osc_base_url(); ?>index.php?page=page&id=23"
               target="_blank"><?php _e('Terms of Service','epsilon'); ?></a>,
            <a href="<?php echo osc_base_url(); ?>index.php?page=page&id=32"
               target="_blank"><?php _e('Privacy Policy','epsilon'); ?></a>,
            <a href="<?php echo osc_base_url(); ?>index.php?page=page&id=33"
               target="_blank"><?php _e('Cookie Use.','epsilon'); ?></a>
            <span class="req">*</span>
          </div>
        </div>

        <div class="user-reg-hook">
          <?php osc_run_hook('user_register_form'); ?>
        </div>

        <?php eps_show_recaptcha('register'); ?>

        <button type="submit" class="btn">
          <?php _e('Create account','epsilon'); ?>
        </button>
      </form>
    </div>
  </section>
<script>
jQuery(function($){
  //
  // 1) Placeholder mapping (exactly as you had it)
  //
  const map = {
    's_name'           : '<?php _e("Name","epsilon"); ?>',
    //'s_email'          : '<?php _e("Email address","epsilon"); ?>',
    's_phone_mobile'   : '<?php _e("+251 9XXXXXXXX","epsilon"); ?>',
    's_password'       : '<?php _e("Password","epsilon"); ?>',
    's_password2'      : '<?php _e("Repeat password","epsilon"); ?>',
    's_password_repeat': '<?php _e("Repeat password","epsilon"); ?>'
  };
  $.each(map, function(id, txt){
    const $el = $('#'+id);
    if($el.length) $el.attr('placeholder', txt);
  });

  //
  // 2) Auto-fill the hidden email as soon as they type (or on blur/change)
  //
  function updateHiddenEmail(){
    const digits = $('#s_phone_mobile').val().replace(/\D/g,'');
    if(digits.length) {
      $('#s_email').val(digits + '@phoneonly.local');
    }
  }

  // run on page load (in case they click “Submit” immediately)
  updateHiddenEmail();

  // keep it fresh on every change/blur/input event
  $('#s_phone_mobile').on('input blur change', updateHiddenEmail);

  //
  // 3) (optionally) remove your old form-submit handler that
  //    only populated #s_email on submit — it’s no longer needed
  //
});
</script>


  <?php osc_current_web_theme_path('footer.php'); ?>

  <!-- Phone formatting script -->
  <script>
  (function(){
    const phoneInput = document.getElementById('s_phone_mobile');
    const prefix     = '+251 ';
    if (!phoneInput) return;
    phoneInput.addEventListener('focus', e => {
      if (!e.target.value.startsWith(prefix)) e.target.value = prefix;
    });
    phoneInput.addEventListener('keydown', e => {
      if ((e.key==='Backspace'||e.key==='Delete')
          && e.target.selectionStart<=prefix.length) {
        e.preventDefault();
      }
    });
    phoneInput.addEventListener('input', e => formatPhone(e.target));
    phoneInput.addEventListener('blur',  e => formatPhone(e.target,true));
    phoneInput.form.addEventListener('submit',()=>{
      phoneInput.value=phoneInput.value.replace(/\s/g,'');
    });
    function formatPhone(input, finalCheck=false) {
      let v=input.value.replace(/[^\d+]/g,'');
      if (!v.startsWith('+251')) v='+251'+v.replace(/^\+?251/,'');
      let local=v.slice(4).replace(/\D/g,'');
      if(finalCheck && !(local.length===9 && local.startsWith('9'))) {
        input.value=prefix;
      } else {
        input.value=prefix+local;
      }
    }
  })();
  </script>
    <!-- Phone formatting script -->
  <script>
  (function(){
    /* … your existing phone-formatting code … */
  })();
  </script>

  <!-- Auto-fill hidden email from the phone -->
  <script>
  jQuery(function($){
    $('#register').on('submit', function(){
      // grab just the digits from "+251 9XXXXXXXX"
      const digits = $('#s_phone_mobile').val().replace(/\D/g, '');
      // set the hidden email so Osclass validation passes
      $('#s_email').val(digits + '@phoneonly.local');
    });
  });
  </script>

  <?php osc_current_web_theme_path('footer.php'); ?>


  <!-- Name→Email autofill (hidden fiel
