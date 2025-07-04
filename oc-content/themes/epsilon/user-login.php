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
      background-color: #7a4d9e; /* Example primary color */
      color: white;
      padding: 10px 15px;
      border: 1px solid #7a4d9e;
      text-decoration: none;
      display: inline-block; /* Changed back from block for side-by-side potential */
      text-align: center;
      cursor: pointer;
      /* margin-top: 10px; /* Removed default top margin */
      width: 100%; /* Make buttons full width like inputs */
      box-sizing: border-box; /* Include padding and border in the element's total width and height */
    }
    .pre-account > .content > section.container:after {background-image:none;}

    .btn-create-account {
      background-color: #ffffff;
      color: #7a4d9e;
      border: 1px solid #7a4d9e;
      padding: 10px 15px;
      font-size: 14px;
      font-weight: 500;
      line-height: 1.4;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      width: 100%;
      box-sizing: border-box;
      cursor: pointer;
      transition: background-color 0.25s ease-in-out, color 0.25s ease-in-out, border-color 0.25s ease-in-out;
    }

    .btn-create-account:hover {
      background-color: #f9f7fc;
      color: #693c8a;
      border-color: #693c8a;
      box-shadow:
        0 2px 4px rgba(122, 77, 158, 0.1),
        0 4px 8px rgba(122, 77, 158, 0.15),
        0 6px 16px rgba(122, 77, 158, 0.2); /* Layered shadows for depth */
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
        margin-bottom: 50px; /* Add space below the login button */
    }

    /* Adjust spacing for reCAPTCHA if present */
    .g-recaptcha { /* Or whatever class your reCAPTCHA container has */
        margin-bottom: 5px; /* Reduce space below reCAPTCHA */
    }
    .separator-text {
      position: relative;
      text-align: center;
      font-size: 18px;
      font-weight: 700;
      color: #7a4d9e;
      margin: 20px 0;
      letter-spacing: 0.7px;
      text-transform: none; /* <- This ensures "Join Xethio Now!" stays in correct case */
    }

    .separator-text::before,
    .separator-text::after {
      content: "";
      position: absolute;
      top: 50%;
      width: 35%;
      height: 1px;
      background-color: #c8a8e4;
    }

    .separator-text::before {
      left: 0;
    }

    .separator-text::after {
      right: 0;
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

        <div class="separator-text">Join <strong>Xethio</strong> Now!</div>

        <a href="<?php echo osc_register_account_url(); ?>" class="btn btn-create-account">
            <?php _e('Create new account', 'epsilon'); ?>
        </a>

      </form>
    </div>
  </section>

  <?php osc_current_web_theme_path('footer.php'); ?>

  <script type="text/javascript">
    $(document).ready(function(){
      $('input[name="email"]')
        .attr('placeholder', '<?php echo osc_esc_js(__('Phone', 'epsilon')); ?>')
        .attr('required', true)
        .attr('pattern', '^\\+251 9[0-9]{8}$')
        .attr('title', 'Must be +251 9XXXXXXXX');
      $('input[name="password"]')
        .attr('placeholder', '<?php echo osc_esc_js(__('Password', 'epsilon')); ?>')
        .attr('required', true);
    });
  </script>

  <script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function () {
    const phoneInput = document.querySelector('input[name="email"]'); // "email" field is used for phone login

    const fixedPrefix = '+251 ';

    if (phoneInput) {
      // Autofill prefix on focus
      phoneInput.addEventListener('focus', function () {
        if (!this.value.startsWith(fixedPrefix)) {
          this.value = fixedPrefix;
        }
      });

      // Prevent deletion inside the fixed prefix
      phoneInput.addEventListener('keydown', function (e) {
        const cursorPosition = this.selectionStart;

        // Block backspace/delete within prefix zone
        if ((e.key === 'Backspace' || e.key === 'Delete') && cursorPosition <= fixedPrefix.length) {
          e.preventDefault();
        }
      });

      // Normalize on input
      phoneInput.addEventListener('input', function () {
        validateAndNormalizePhone(this);
      });

      // Validate on blur
      phoneInput.addEventListener('blur', function () {
        validateAndNormalizePhone(this, true); // Final validation on blur
      });

      // Clean up value before form submission
      if (phoneInput.form) {
        phoneInput.form.addEventListener('submit', function () {
          phoneInput.value = phoneInput.value.replace(/\s/g, ''); // Remove all spaces
        });
      }

      function validateAndNormalizePhone(input, isFinalValidation = false) {
  let value = input.value.trim();

  // Always enforce the fixed prefix
  if (!value.startsWith(fixedPrefix)) {
    value = fixedPrefix + value.replace(/^\+?251\s?/, '').replace(/[^\d]/g, '');
  }

  // Extract only digits after "+251 "
  let localNumber = value.replace(fixedPrefix, '').replace(/[^\d]/g, '');

  // ← ADD THIS LINE TO TRIM TO 9 DIGITS:
  localNumber = localNumber.substring(0, 9);

  if (isFinalValidation) {
    if (!(localNumber.length === 9 && localNumber.startsWith('9'))) {
      // Optionally show a validation message here
      // input.setCustomValidity("Phone must be +251 9XXXXXXXX");
      // input.reportValidity();
    }
  }

  // Always recompose the field as "+251 " + up-to-9 digits
  input.value = fixedPrefix + localNumber;
}

    }

    // Password toggle
    const togglePassword = document.querySelector('.toggle-pass');
    const passwordInput = document.querySelector('input[name="password"]');

    if (togglePassword && passwordInput) {
      togglePassword.addEventListener('click', function (e) {
        e.preventDefault();
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
      });
    }

    $('input[name="email"]')
      .attr('placeholder', '<?php echo osc_esc_js(__('Phone', 'epsilon')); ?>')
      .attr('required', true)
      .attr('pattern', '^\\+251 9[0-9]{8}$')
      .attr('title', 'Must be +251 9XXXXXXXX');
    $('input[name="password"]')
      .attr('placeholder', '<?php echo osc_esc_js(__('Password', 'epsilon')); ?>')
      .attr('required', true);
  });
  </script>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.querySelector('.toggle-pass');
    // Change this selector to match your real input, e.g. by ID:
    const input = document.querySelector('#password');
    if (!toggle || !input) return;

    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      // Toggle the type
      input.type = input.type === 'password' ? 'text' : 'password';
      // Toggle the eye icon classes
      const icon = this.querySelector('i');
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    });
  });
  </script>

</body>
</html>
