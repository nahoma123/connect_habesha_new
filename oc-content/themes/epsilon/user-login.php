<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo eps_language_dir(); ?>" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
<head>
  <?php osc_current_web_theme_path('head.php'); ?>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="googlebot" content="noindex, nofollow" />
  <script type="text/javascript" src="<?php echo osc_current_web_theme_js_url('jquery.validate.min.js'); ?>"></script>
</head>

<body id="user-login" class="pre-account login">
  <?php UserForm::js_validation(); ?>
  <?php osc_current_web_theme_path('header.php'); ?>

  <section class="container">
    <div class="box">
      <h1><?php _e('Sign-in to your account', 'epsilon'); ?></h1>

      <?php if(function_exists('fl_call_after_install') || function_exists('gc_login_button') || function_exists('fjl_login_button')) { ?>
        <div class="social">
          <?php if(function_exists('fl_call_after_install') && facebook_login_link() !== false) { ?>
            <a class="facebook" href="<?php echo facebook_login_link(); ?>" title="<?php echo osc_esc_html(__('Login with Facebook', 'epsilon')); ?>">
              <i class="fab fa-facebook"></i>
              <span><?php _e('Login with Facebook', 'epsilon'); ?></span>
            </a>
          <?php } ?>

          <?php if(function_exists('ggl_login_link') && ggl_login_link() !== false) { ?>
            <a class="google" href="<?php echo ggl_login_link(); ?>" title="<?php echo osc_esc_html(__('Sign in with Google', 'epsilon')); ?>">
              <i class="fab fa-google"></i>
              <span><?php _e('Sign in with Google', 'epsilon'); ?></span>
            </a>
          <?php } ?>
          
          <?php if(function_exists('fjl_login_button')) { ?>
            <a target="_top" href="javascript:void(0);" class="facebook fl-button fjl-button" onclick="fjlCheckLoginState();" title="<?php echo osc_esc_html(__('Connect with Facebook', 'epsilon')); ?>">
              <i class="fab fa-facebook-square"></i>
              <span><?php _e('Continue with Facebook', 'epsilon'); ?></span>
            </a>
          <?php } ?>
        </div>
      <?php } ?>

      <a class="alt-action" href="<?php echo osc_register_account_url(); ?>"><?php _e('No account yet? Register a new account', 'epsilon'); ?> &#8594;</a>

      <form action="<?php echo osc_base_url(true); ?>" method="post" >
        <input type="hidden" name="page" value="login" />
        <input type="hidden" name="action" value="login_post" />
        
        <?php osc_run_hook('user_pre_login_form'); ?>

        <div class="row">
          <label for="phone"><?php _e('Phone', 'epsilon'); ?></label>
          <span class="input-box"><?php UserForm::email_login_text(); ?></span>
          <div style="text-align:left;font-style:italic;font-size:12px;margin-top:-13px;margin-bottom:15px;color:#0178d6;">Email login will be available soon.</div>
        </div>

        <div class="row">
          <label for="password"><?php _e('Password', 'epsilon'); ?></label>
          <span class="input-box">
            <?php UserForm::password_login_text(); ?>
            <a href="#" class="toggle-pass" title="<?php echo osc_esc_html(__('Show/hide password', 'epsilon')); ?>"><i class="fa fa-eye-slash"></i></a>
          </span>
        </div>

        <div class="input-box-check">
          <?php UserForm::rememberme_login_checkbox();?>
          <label for="remember"><?php _e('Remember me', 'epsilon'); ?></label>
        </div>

        <div class="user-reg-hook"><?php osc_run_hook('user_login_form'); ?></div>

        <div class="row fr">
        </div>
        
        <?php eps_show_recaptcha('login'); ?>

        <button type="submit" class="btn"><?php _e('Log in', 'epsilon');?></button>

        <a class="alt-action2" href="<?php echo osc_recover_user_password_url(); ?>"><?php _e('I forgot my password', 'epsilon'); ?></a>
      </form>
    </div>
  </section>

  <?php osc_current_web_theme_path('footer.php'); ?>
  
  <script type="text/javascript">
    $(document).ready(function(){
      $('input[name="email"]').attr('placeholder', '<?php echo osc_esc_js(__('Phone', 'epsilon')); ?>').attr('required', true);
      $('input[name="password"]').attr('placeholder', '<?php echo osc_esc_js(__('Password', 'epsilon')); ?>').attr('required', true);
    });

    document.addEventListener('DOMContentLoaded', function () {
    const phoneInput = document.querySelector('input[name="email"]'); // Replace 'email' with your input field name or ID

    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            validateAndNormalizePhone(phoneInput);
        });

        phoneInput.addEventListener('blur', function () {
            validateAndNormalizePhone(phoneInput, true); // Final validation on blur
        });
    }

    function validateAndNormalizePhone(input, isFinalValidation = false) {
        let phoneValue = input.value.trim();

        // Remove all spaces and invalid characters (only allow digits and "+")
        phoneValue = phoneValue.replace(/[^+\d]/g, '');

        // If the number doesn't start with "+", consider it invalid
        if (!phoneValue.startsWith('+')) {
            input.value = ''; // Reset the input or set to a default value
            return;
        }

        // Extract the country code
        const countryCode = phoneValue.substring(0, 4); // First 4 characters (e.g., "+251")

        if (countryCode === '+251') {
            // Handle Ethiopian numbers
            const localNumber = phoneValue.substring(4).replace(/[^0-9]/g, ''); // Extract local part after "+251"

            if (!isFinalValidation) {
                // Allow partial typing for Ethiopian numbers
                input.value = '+251' + localNumber.substring(0, 9); // Limit to 9 digits for local part
                return;
            }

            // Final validation: Ensure the local part is exactly 9 digits and starts with "9"
            if (localNumber.length === 9 && localNumber.startsWith('9')) {
                input.value = '+251' + localNumber;
            } else {
                // Invalid Ethiopian number: Reset or provide feedback
                input.value = '+251';
            }
        } else {
            // For other international numbers, leave unchanged
            input.value = phoneValue;
        }
    }
});



    
  </script>
</body>
</html>