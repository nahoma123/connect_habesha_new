<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo eps_language_dir(); ?>" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
<head>
  <?php osc_current_web_theme_path('head.php'); ?>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="googlebot" content="noindex, nofollow" />
  <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
<link rel="dns-prefetch" href="//www.googletagmanager.com">

  <script type="text/javascript" src="<?php echo osc_current_web_theme_js_url('jquery.validate.min.js'); ?>"></script>

  <!-- Add this CSS to your theme's stylesheet or keep it here -->
  <style>
    .pre-account a.alt-action2 {
      margin: 0px;
    }
    #forgot_password{
      margin-top: -10px;
      font-size: small;
    }

    .btn { /* Assuming this is your primary login button style */
      background-color: #0178d6; /* Example primary color */
      color: white;
      padding: 10px 15px;
      border: 1px solid #0178d6;
      text-decoration: none;
      display: inline-block; /* Changed back from block for side-by-side potential */
      text-align: center;
      cursor: pointer;
      /* margin-top: 10px; /* Removed default top margin */
      width: 100%; /* Make buttons full width like inputs */
      box-sizing: border-box; /* Include padding and border in the element's total width and height */
    }

    .btn-create-account { /* Style for the new create account button */
      background-color: white; /* Inverted background */
      color: #0178d6; /* Inverted text color (matches original background) */
      border: 1px solid #0178d6; /* Keep or adjust border */
      display: block; /* Make the link behave like a block element */
      margin-top: 10px; /* Add space above create account button */
    }

    /* Optional: Add hover effects */
    .btn:hover {
       opacity: 0.9;
    }

    .btn-create-account:hover {
      background-color: #f0f0f0; /* Slight change on hover */
    }

    /* --- Styles for Forgot Password Link --- */
    .alt-action2 {
      display: block; /* Make it take its own line */
      text-align: right; /* Align text to the right */
      margin-top: -10px; /* Pull it up closer to elements above (Adjust value as needed) */
      margin-bottom: 15px; /* Add space below it, before the Login button */
      font-size: 0.9em; /* Optional: Make it slightly smaller */
      clear: both; /* Just in case of floats above */
    }
    /* --- End Styles for Forgot Password Link --- */

    /* Adjust Login button margin */
    form button.btn[type="submit"] { /* Target specifically the submit button */
        margin-top: 5px; /* Add some space above the login button */
        margin-bottom: 10px; /* Add space below the login button */
    }

    /* Adjust spacing for reCAPTCHA if present */
    .g-recaptcha { /* Or whatever class your reCAPTCHA container has */
        margin-bottom: 5px; /* Reduce space below reCAPTCHA */
    }

  </style>
</head>

<body id="user-login" class="pre-account login">
  <?php UserForm::js_validation(); ?>
  <?php osc_current_web_theme_path('header.php'); ?>

  <section class="container">
    <div class="box">
      <h1><?php _e('Log in to your account', 'epsilon'); ?></h1>

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

      <a class="alt-action" href="<?php echo osc_register_account_url(); ?>"><?php _e('Don\'t have an account? Create a new account', 'epsilon'); ?> →</a>

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

        <a id="forgot_password" class="alt-action2" href="<?php echo osc_recover_user_password_url(); ?>"><?php _e('Forgot password?', 'epsilon'); ?></a>


        <div class="input-box-check">
          <?php UserForm::rememberme_login_checkbox();?>
          <label for="remember"><?php _e('Remember me', 'epsilon'); ?></label>
        </div>

        <div class="user-reg-hook"><?php osc_run_hook('user_login_form'); ?></div>

        <div class="row fr">
        </div>

        <?php eps_show_recaptcha('login'); ?>

        <!-- MOVED Forgot Password Link Here -->

        <button type="submit" class="btn"><?php _e('Log in', 'epsilon');?></button>

        <a href="<?php echo osc_register_account_url(); ?>" class="btn btn-create-account">
            <?php _e('Create New Account', 'epsilon'); ?>
        </a>

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
      const phoneInput = document.querySelector('input[name="email"]'); // Assuming 'email' is used for phone input based on context

      if (phoneInput) {
          phoneInput.addEventListener('input', function () {
              validateAndNormalizePhone(phoneInput);
          });

          phoneInput.addEventListener('blur', function () {
              validateAndNormalizePhone(phoneInput, true); // Final validation on blur
          });

          // Ensure the space is removed before form submission
          if (phoneInput.form) {
            phoneInput.form.addEventListener('submit', function (e) {
                phoneInput.value = phoneInput.value.replace(/\s/g, ''); // Remove all spaces
            });
          }
      }

      function validateAndNormalizePhone(input, isFinalValidation = false) {
          let phoneValue = input.value.trim();

          // Remove all non-digit characters except for the leading '+'
          phoneValue = phoneValue.replace(/[^\d+]/g, '');
          if (phoneValue.length > 1) {
             phoneValue = '+' + phoneValue.substring(1).replace(/\+/g, ''); // Ensure only one '+' at the start
          } else if (phoneValue !== '+' && phoneValue !== '') {
             phoneValue = '+' + phoneValue; // Add '+' if missing and not empty
          }


          // If the number doesn't start with "+", consider it potentially incomplete or invalid
          if (!phoneValue.startsWith('+') && phoneValue.length > 0) {
              // Optionally prepend '+' or handle as error - current logic adds '+' above
               if (!phoneValue.startsWith('+')) phoneValue = '+' + phoneValue.replace(/\+/g, ''); // Re-ensure '+'
          }

          // Handle Ethiopian numbers specifically: +251 9XXXXXXXX
          if (phoneValue.startsWith('+251')) {
              const localNumber = phoneValue.substring(4).replace(/[^0-9]/g, ''); // Extract local part after "+251"

              // Format with space during typing for readability
              let formattedNumber = '+251 ' + localNumber.substring(0, 9); // Limit length while typing

              if (isFinalValidation) {
                  // Final validation: Ensure the local part is exactly 9 digits and starts with "9"
                  if (localNumber.length === 9 && localNumber.startsWith('9')) {
                      formattedNumber = '+251 ' + localNumber; // Correct format
                  } else {
                      // Invalid Ethiopian number structure on blur: Provide feedback or reset partially
                      // Resetting to '+251 ' might be user-friendly
                      formattedNumber = '+251 ';
                      // Or input.setCustomValidity("Ethiopian phone number must be +251 9XXXXXXXX"); input.reportValidity();
                  }
              }
               input.value = formattedNumber.trim(); // Update input field, remove trailing space if any

          } else {
              // For other international numbers, just keep the cleaned value
              // You might want to add more validation rules for other country codes if needed
              input.value = phoneValue;
          }
      }

      // Password Toggle
      // const togglePassword = document.querySelector('.toggle-pass');
      // const passwordInput = document.querySelector('input[name="password"]'); // Ensure this selector is correct

      // if (togglePassword && passwordInput) {
      //     togglePassword.addEventListener('click', function (e) {
      //         e.preventDefault();
      //         // Toggle the type attribute
      //         const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      //         passwordInput.setAttribute('type', type);
      //         // Toggle the eye icon
      //         this.querySelector('i').classList.toggle('fa-eye');
      //         this.querySelector('i').classList.toggle('fa-eye-slash');
      //     });
      // }

    });
  </script>
</body>
</html>