<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo eps_language_dir(); ?>"
  lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">

<head>
  <?php osc_current_web_theme_path('head.php'); ?>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="googlebot" content="noindex, nofollow" />
  <script type="text/javascript" src="<?php echo osc_current_web_theme_js_url('jquery.validate.min.js'); ?>"></script>
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

      <?php if (function_exists('fl_call_after_install') || function_exists('gc_login_button') || function_exists('fjl_login_button')) { ?>
        <div class="social">
          <?php if (function_exists('fl_call_after_install')) { ?>
            <a class="facebook" href="<?php echo facebook_login_link(); ?>"
              title="<?php echo osc_esc_html(__('Connect with Facebook', 'epsilon')); ?>">
              <i class="fab fa-facebook-square"></i>
              <span><?php _e('Continue with Facebook', 'epsilon'); ?></span>
            </a>
          <?php } ?>

          <?php if (function_exists('ggl_login_link')) { ?>
            <a class="google" href="<?php echo ggl_login_link(); ?>"
              title="<?php echo osc_esc_html(__('Connect with Google', 'epsilon')); ?>">
              <i class="fab fa-google"></i>
              <span><?php _e('Continue with Google', 'epsilon'); ?></span>
            </a>
          <?php } ?>

          <?php if (function_exists('fjl_login_button')) { ?>
            <a target="_top" href="javascript:void(0);" class="facebook fl-button fjl-button"
              onclick="fjlCheckLoginState();" title="<?php echo osc_esc_html(__('Connect with Facebook', 'epsilon')); ?>">
              <i class="fab fa-facebook-square"></i>
              <span><?php _e('Continue with Facebook', 'epsilon'); ?></span>
            </a>
          <?php } ?>
        </div>
      <?php } ?>

      <a class="alt-action"
        href="<?php echo osc_user_login_url(); ?>"><?php _e('Already have an account? Log in', 'epsilon'); ?>
        &#8594;</a>

      <form name="register" id="register" action="<?php echo osc_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="register" />
        <input type="hidden" name="action" value="register_post" />
        <input type="hidden" name="s_method" id="s_method_hidden" value="2" />

        <?php osc_run_hook('user_pre_register_form'); ?>

        <ul id="error_list"></ul>

        <div class="row nm">
          <?php /*<label for="name"><?php _e('Name', 'epsilon'); ?> <span class="req">*</span></label>*/ ?>
          <div class="input-box"><?php UserForm::name_text(); ?></div>
        </div>

        <?php /*<div class="row nm">
<label for="name"><?php _e('Verification Method', 'epsilon'); ?> <span class="req">*</span></label>
<select name="s_method" id="s_method" >
<option value="">Select verification method</option>
<option value="1">Phone</option>
<option value="2">Email</option>
</select>
</div>*/ ?>

        <div class="row em" style="display:none;">
          <?php /*<label for="email"><?php _e('E-mail', 'epsilon'); ?> <span class="req">*</span></label>*/ ?>
          <div class="input-box"><?php UserForm::email_text(); ?></div>

        </div>

        <div class="row mb">
          <?php /*<label for="phone"><?php _e('Mobile Phone', 'epsilon'); ?></label>*/ ?>
          <div class="input-box"><?php UserForm::mobile_text(osc_user()); ?></div>
          <div
            style="text-align:left;font-style:italic;font-size:12px;margin-top:-13px;margin-bottom:15px;color:#0178d6;">
            * Your phone number is private. You can change this later in your settings.</div>
        </div>

        <div class="radio-group">
  <label for="category_id">
    <?php _e('Please select your gender. I am a:', 'epsilon'); ?> <span class="req">*</span>
  </label>
  <?php if (!empty($categories)) { ?>
    <?php foreach ($categories as $index => $category) { ?>
      <div class="radio-option" style="margin-top: <?php echo ($index > 0) ? '8px' : '0'; ?>;">
        <label class="radio-label">
          <input type="radio" name="category_id" value="<?php echo $category['pk_i_id']; ?>" <?php echo ($index === 0) ? 'checked' : ''; ?>>
          <span class="radio-text"><?php echo $category['s_name']; ?></span>
        </label>
      </div>
    <?php } ?>
  <?php } else { ?>
    <p><?php _e('No categories available.', 'epsilon'); ?></p>
  <?php } ?>
</div>


        <div id="communicationMethodContainer" style="display: none;">
          <label id="socialNetworkLabel" for="additionalAccountContainer"
            style="margin-top: 12px;"><?php _e('Social Networking numbers', 'epsilon'); ?></label>

          <!-- Account Name Input -->
          <input type="text" id="accountName" name="primary_accounts" class="account-input"
            placeholder="Ex:- +251 911002244, @Merry_26">
          <!-- Communication Method Selection -->
          <div class="checkbox-container">
            <label>
              <input type="checkbox" name="primary_methods[]" value="Telegram"> <span>Telegram</span>
            </label>
            <label>
              <input type="checkbox" name="primary_methods[]" value="WhatsApp"> <span>WhatsApp</span>
            </label>
            <label>
              <input type="checkbox" name="primary_methods[]" value="SMS"> <span>SMS (text)</span>
            </label>
            <label>
              <input type="checkbox" name="primary_methods[]" value="DirectCall"> <span> Direct call</span>
            </label>
          </div>


          <!-- Additional Account Container -->
          <div id="additionalAccountContainer">
            <!-- Additional Account Name Input -->
            <input type="text" id="additionalAccountName" name="additional_accounts" class="account-input"
              placeholder="Ex:- +251 911002244, @Merry_26">

            <!-- Communication Method Selection for Additional Account -->
            <div class="checkbox-container">
              <label>
                <input type="checkbox" name="additional_methods[]" value="Telegram"> <span>Telegram</span>
              </label>
              <label>
                <input type="checkbox" name="additional_methods[]" value="WhatsApp"> <span>WhatsApp</span>
              </label>
              <label>
                <input type="checkbox" name="additional_methods[]" value="SMS"> <span>SMS (text)</span>
              </label>
              <label>
                <input type="checkbox" name="additional_methods[]" value="DirectCall"> <span>Direct call</span>
              </label>
            </div>
          </div>
        </div>

        <div class="row p1">
          <?php /*<label for="password"><?php _e('Password', 'epsilon'); ?> <span class="req">*</span></label>*/ ?>
          <div class="input-box">
            <?php UserForm::password_text(); ?>
            <a href="#" class="toggle-pass" title="<?php echo osc_esc_html(__('Show/hide password', 'epsilon')); ?>"><i
                class="fa fa-eye-slash"></i></a>
          </div>
        </div>

        <div class="row p2">
          <?php /*<label for="password"><?php _e('Re-type password', 'epsilon'); ?> <span class="req">*</span></label>*/ ?>
          <div class="input-box">
            <?php UserForm::check_password_text(); ?>
            <a href="#" class="toggle-pass" title="<?php echo osc_esc_html(__('Show/hide password', 'epsilon')); ?>"><i
                class="fa fa-eye-slash"></i></a>
          </div>
        </div>

        <div class="row p2">

          <div class="td-wrap d1 input-box" style="width:100%;font-size:11px">
            <?php _e('By creating an account, you agree to our', 'epsilon'); ?>
            <a href="<?php echo osc_base_url(); ?>index.php?page=page&id=23"
              target="_blank"><?php _e('Terms of Service', 'epsilon'); ?></a>,
            <a href="<?php echo osc_base_url(); ?>index.php?page=page&id=32"
              target="_blank"><?php _e('Privacy Policy', 'epsilon'); ?></a>, and
            <a href="<?php echo osc_base_url(); ?>index.php?page=page&id=33"
              target="_blank"><?php _e('Cookie Use.', 'epsilon'); ?></a>
            <span class="req">*</span>
          </div>
        </div>

        <div class="user-reg-hook"><?php osc_run_hook('user_register_form'); ?></div>

        <?php eps_show_recaptcha('register'); ?>

        <button type="submit" class="btn"><?php _e('Create account', 'epsilon'); ?></button>
      </form>
    </div>
    </div>

    <?php osc_current_web_theme_path('footer.php'); ?>

    <script type="text/javascript">
      // JavaScript to handle dynamic button and additional account field
      const accountNameInput = document.getElementById('accountName');
      const addRemoveButtonContainer = document.getElementById('addRemoveButtonContainer');
      const additionalAccountContainer = document.getElementById('additionalAccountContainer');
      const additionalAccountInput = document.getElementById('additionalAccountName');
      const additionalCheckboxes = additionalAccountContainer.querySelectorAll('input[type="checkbox"]');

      // Function to create the "Add Additional Account" button
      const createAddRemoveButton = () => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'add-remove-button';
        button.textContent = 'Add Additional Account';
        button.addEventListener('click', toggleAdditionalAccount);
        return button;
      };


      // Function to handle input changes in the first account field
      const handleAccountInput = () => {
        if (accountNameInput.value.trim() !== '') {
          // If the button doesn't exist, create it
          if (!addRemoveButtonContainer.querySelector('button')) {
            const button = createAddRemoveButton();
            addRemoveButtonContainer.appendChild(button);
          }
        } else {
          // If the field is empty, remove the button and hide the additional account field
          addRemoveButtonContainer.innerHTML = ''; // Remove the button
          additionalAccountContainer.style.display = 'none'; // Hide additional account field
        }
      };

      // Add event listeners to the first account field
      accountNameInput.addEventListener('input', handleAccountInput);
      accountNameInput.addEventListener('change', handleAccountInput);



      $(document).ready(function () {

        $('#choose_mobile').on('click', function () {
          //$('input[type="checkbox"]').not(this).prop('checked', false);
          $('#s_email').removeAttr('required');
          $('#sphone').show();
          $('#semail').hide();
          $('#s_email').css('display', 'none');
          $('#s_phone_mobile').attr('required', true);
          $('#s_method_hidden').val('1');
          $('input[name="s_phone_mobile"]').attr('placeholder', '<?php echo osc_esc_js(__('phone number', 'epsilon')); ?>');
        });

        $('#choose_email').on('click', function () {
          //$('input[type="checkbox"]').not(this).prop('checked', false);
          $('#s_phone_mobile').removeAttr('required');
          $('#sphone').hide();
          $('#semail').show();
          $('#s_email').attr('required', true);
          $('#s_email').css('display', 'block');
          $('#s_method_hidden').val('2');
        });

        $('#s_method').change(function () {
          if ($(this).val() == 1) {
            $('#s_email').removeAttr('required');
            $('#sphone').show();
            $('#semail').hide();
            $('#s_email').css('display', 'none');
            $('#s_phone_mobile').attr('required', true);
          } else {
            $('#s_phone_mobile').removeAttr('required');
            $('#sphone').hide();
            $('#semail').show();
            $('#s_email').attr('required', true);
            $('#s_email').css('display', 'block');
          }
        });

        let typingTimer;
        const typingDelay = 300;

        $('#s_name').on('keyup', function () {
          clearTimeout(typingTimer);
          const $this = $(this);
          typingTimer = setTimeout(() => {
            const value = $this.val(); // Get the value
            $('#s_email').val(value.replace(/\s+/g, "") + '@xxx.com');
          }, typingDelay);
        });

        // Optional: Clear timer on keydown
        $('#s_name').on('keydown', function () {
          clearTimeout(typingTimer);
        });

        $('input[name="s_name"]').attr('placeholder', '<?php echo osc_esc_js(__('Name', 'epsilon')); ?>').attr('required', true);
        $('input[name="s_email"]').attr('placeholder', '<?php echo osc_esc_js(__('Email', 'epsilon')); ?>').attr('required', true).prop('type', 'email');
        $('input[name="s_phone_mobile"]').attr('placeholder', '<?php echo osc_esc_js(__('phone number', 'epsilon')); ?>');
        $('input[name="s_password"]').attr('placeholder', '<?php echo osc_esc_js(__('Password', 'epsilon')); ?>').attr('required', true);
        $('input[name="s_password2"]').attr('placeholder', '<?php echo osc_esc_js(__('Repeat password', 'epsilon')); ?>').attr('required', true);
        $('#sphone').hide();


        setTimeout(() => {
          const value = $this.val(); // Get the value
          $('input[name="s_phone_mobile"]').attr('placeholder', '<?php echo osc_esc_js(__('phone number', 'epsilon')); ?>');
        }, 2000);

      });

      document.addEventListener('DOMContentLoaded', function () {
        const phoneInput = document.querySelector('#s_phone_mobile'); // Replace 'email' with your input field name or ID

        if (phoneInput) {
          phoneInput.addEventListener('input', function () {
            validateAndNormalizePhone(phoneInput);
          });

          phoneInput.addEventListener('blur', function () {
            validateAndNormalizePhone(phoneInput, true); // Final validation on blur
          });

          // Ensure the space is removed before form submission
          phoneInput.form.addEventListener('submit', function (e) {
            phoneInput.value = phoneInput.value.replace(/\s/g, ''); // Remove all spaces
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
            // For other international numbers, leave unchanged
            input.value = phoneValue;
          }
        }
      });
      document.addEventListener('DOMContentLoaded', function () {
        const phoneInput = document.querySelector('input[name="s_phone_mobile"]');
        const communicationMethodContainer = document.getElementById('communicationMethodContainer');

        if (phoneInput) {
          phoneInput.addEventListener('input', function () {
            const value = phoneInput.value.trim();

            // Reveal the container if the phone number field is not empty
            if (value.length > 0) {
              communicationMethodContainer.style.display = 'block';
            } else {
              communicationMethodContainer.style.display = 'none';
            }
          });
        }
      });

    </script>
    <style>
      /* Style for the Add/Remove button */
      .add-remove-button {
        margin-top: 10px;
        /* Space above the button */
        margin-bottom: 20px;
        /* Space below the button */
        padding: 8px 16px;
        /* Padding for better proportions */
        font-size: 14px;
        /* Slightly larger font size */
        color: #fff;
        /* White text */
        background-color: #007bff;
        /* Blue background */
        border: none;
        /* Remove default border */
        border-radius: 5px;
        /* Rounded corners */
        cursor: pointer;
        /* Pointer cursor on hover */
        transition: background-color 0.3s ease;
        /* Smooth hover effect */
      }

      /* Hover effect for the button */
      .add-remove-button:hover {
        background-color: #0056b3;
        /* Darker blue on hover */
      }

      /* Active effect for the button */
      .add-remove-button:active {
        background-color: #004080;
        /* Even darker blue on click */
      }


      #additionalAccountName,
      #AccountName {
        margin-top: 10px;
        ;
      }

      .checkbox-container {
        margin-top: 8px;
        display: flex;
        flex-direction: row;
        /* Arrange items in a row */
        align-items: center;
        /* Vertically center items */
        gap: 0;
        padding-left: 5px;
        margin-left: 3px;
        /* Ensure no gap between labels */
      }


      .checkbox-container label {
        display: flex;
        align-items: center;
        /* Vertically center checkbox and text */
        margin: 0;
        /* Remove any default margins */
        font-size: 12px;
        /* Reduced text size */
      }

      .checkbox-container input[type="checkbox"] {
        margin: 0 0px 0 0;
        /* Minimal space between checkbox and text */
      }

      .checkbox-container input {
        margin-right: 0px;
      }

      .checkbox-container span {
        margin-left: -4px;
        margin-right: 4px;
        font-size: 12.3px;
        display: inline-flex;
        /* Enable flexbox for the span */
        align-items: center;
        /* Vertically center the text inside the span */
        justify-content: center;
        /* Horizontally center the text (in case span width changes) */
        line-height: 1;
        /* Ensure text is not stretched */
        padding-top: 0.5px;
      }


      /* Style for the checkbox container */
      .checkbox-container {
        display: flex;
        justify-content: space-between;
        /* Distribute space between labels */
        margin-left: -5px;
        margin-bottom: 5px;
        align-items: center;
      }

      /* Style for the checkbox labels */
      .checkbox-container label {
        font-size: 14px;
        /* margin-right: 11px !important; */
        display: flex;
        align-items: center;
        gap: 5px;
        /* margin-right: 5px !important; */
        /* margin-left: 3px !important; */
      }

      .checkbox-container input {
        margin-right: 0px;
        height: 9px !important;
        width: 9px !important;
      }

      #communicationMethodContainer {
        margin-top: 10px;
        margin-bottom: 20px;
      }

      /* Style for the Add/Remove button */
      .add-remove-button {
        margin-top: 10px;
        padding: 5px 10px;
        font-size: 12px;
        cursor: pointer;
      }

      /* Hide the additional account field by default */
      #additionalAccountContainer {
        margin-top: 10px;
      }



      .radio-group {
        font-family: Arial, sans-serif;
        margin-bottom: 16px;
      }

      .radio-group p {
        font-weight: bold;
        margin-bottom: 8px;
      }

      .radio-group label {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        font-size: 14px;
        cursor: pointer;
        font-family: "Comfortaa", sans-serif;
      }


      .radio-label input[type="radio"] {
    width: 11px;
    height: 11px;
    margin-left: 0px;
}
      .radio-group .custom-radio {
        width: 18px;
        height: 18px;
        border: 0.4px solid #3b49df;
        border-radius: 50%;
        margin-right: 8px;
        position: relative;
      }

      .radio-group input[type="radio"]:checked+.custom-radio {
        background-color: #3b49df;
        border-color: #3b49df;
      }

      .radio-group input[type="radio"]:checked+.custom-radio::after {
        content: '';
        width: 10px;
        height: 10px;
        background-color: white;
        border-radius: 50%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
      }

      .radio-group small {
        font-size: 12px;
        color: #666;
      }

      #socialNetworkLabel {
        margin-bottom: 10px;
        color: #0178d6;
      }

      .radio-group {
      display: flex;
      flex-direction: column;
      /* Stack radio options vertically */
      gap: 8px;
      /* Small gap between items */

    }
    .radio-group label {
    font-size: 14px;
    margin: 0 0 3px 0;
    display: block;
}

    .radio-group label[for="category_id"] {
      margin-bottom: 8px;
      /* Add spacing between main label and options */
    }
    label {
    font-weight: 600;
}
    </style>
</body>

</html>