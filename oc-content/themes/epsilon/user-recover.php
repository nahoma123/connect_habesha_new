<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo eps_language_dir(); ?>" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
<head>
  <?php osc_current_web_theme_path('head.php'); ?>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="googlebot" content="noindex, nofollow" />
  <script type="text/javascript" src="<?php echo osc_current_web_theme_js_url('jquery.validate.min.js'); ?>"></script>
</head>

<body id="user-recover" class="pre-account recover">
  <?php UserForm::js_validation(); ?>
  <?php osc_current_web_theme_path('header.php'); ?>

  <section class="container">
    <div class="box">
      <h1><?php _e('Reset password', 'epsilon'); ?></h1>

      <a class="alt-action" href="<?php echo osc_user_login_url(); ?>"><?php _e('You already know password? Login to your account', 'epsilon'); ?> →</a>

      <form action="<?php echo osc_base_url(true) ; ?>" method="post" >
        <input type="hidden" name="page" value="login" />
        <input type="hidden" name="action" value="recover_post" />

        <div class="row">
          <label for="email"><?php _e('E-mail', 'epsilon') ; ?></label>
          <span class="input-box"><?php UserForm::email_text(); ?></span>
        </div>

        <?php osc_run_hook('user_recover_form'); ?>

        <?php eps_show_recaptcha('recover_password'); ?>

        <button type="submit" class="btn"><?php _e('Send a new password', 'epsilon') ; ?></button>

        <a class="alt-action2" href="<?php echo osc_register_account_url(); ?>"><?php _e('Create a new account', 'epsilon'); ?></a>
      </form>
    </div>
  </div>

  <?php osc_current_web_theme_path('footer.php'); ?>

  <script type="text/javascript">
    $(document).ready(function(){
      $('input[name="s_email"]').attr('placeholder', '<?php echo osc_esc_js(__('your.email@dot.com', 'epsilon')); ?>').attr('required', true).prop('type', 'email');
    });

    // Added Javascript from rejected hunk - NOTE: Targets #s_phone_mobile which is NOT present in this form.
    document.addEventListener('DOMContentLoaded', function () {
        const phoneInput = document.querySelector('#s_phone_mobile'); // THIS WILL BE NULL

        if (phoneInput) { // This block will likely never execute
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

            // Remove all spaces and invalid characters (only allow digits and "+")
            phoneValue = phoneValue.replace(/[^+\d]/g, '');

            // If the number doesn't start with "+", consider it invalid
            if (!phoneValue.startsWith('+')) {
                 if (phoneValue.length > 0 && !isFinalValidation) {
                    // Handle case where user types digits without '+' - maybe clear?
                 } else {
                    input.value = ''; // Reset the input or set to a default value
                    return;
                 }
            }

             // Re-check after cleaning
             if (!phoneValue.startsWith('+') && phoneValue.length > 0) {
                 input.value = '';
                 return;
            } else if (!phoneValue.startsWith('+') && phoneValue.length === 0) {
                return; // Allow empty
            }


            // Extract the country code
            const countryCode = phoneValue.substring(0, 4); // First 4 characters (e.g., "+251")

            if (countryCode === '+251') {
                // Handle Ethiopian numbers
                const localNumber = phoneValue.substring(4).replace(/[^0-9]/g, ''); // Extract local part after "+251"

                if (!isFinalValidation) {
                    // Allow partial typing for Ethiopian numbers
                    input.value = '+251 ' + localNumber.substring(0, 9); // Add a space after the country code
                    return;
                }

                // Final validation: Ensure the local part is exactly 9 digits and starts with "9"
                if (localNumber.length === 9 && localNumber.startsWith('9')) {
                    input.value = '+251 ' + localNumber; // Add a space after the country code
                } else {
                    // Invalid Ethiopian number: Reset or provide feedback
                    input.value = '+251';
                }
            } else {
                // For other international numbers (starting with +)
                input.value = phoneValue; // Leave unchanged after cleaning
            }
        }
    });


  </script>
</body>
</html>