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
        →</a>

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

           <!-- Container for the dynamic button -->
           <div id="addRemoveButtonContainer"></div>

           <!-- Additional Account Container -->
           <div id="additionalAccountContainer" style="display: none;"> <!-- Initially hidden -->
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
  </section> <!-- Corrected closing tag placement -->

  <?php osc_current_web_theme_path('footer.php'); ?>

  <script type="text/javascript">
    // JavaScript to handle dynamic button and additional account field
    const accountNameInput = document.getElementById('accountName');
    const addRemoveButtonContainer = document.getElementById('addRemoveButtonContainer');
    const additionalAccountContainer = document.getElementById('additionalAccountContainer');
    const additionalAccountInput = document.getElementById('additionalAccountName');
    const additionalCheckboxes = additionalAccountContainer.querySelectorAll('input[type="checkbox"]');

    let isAdditionalAccountVisible = false;

    // Function to create the Add/Remove button
    const createAddRemoveButton = (isRemove = false) => {
        addRemoveButtonContainer.innerHTML = ''; // Clear previous button
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'add-remove-button';
        button.textContent = isRemove ? 'Remove Additional Account' : 'Add Additional Account';
        button.addEventListener('click', toggleAdditionalAccount);
        addRemoveButtonContainer.appendChild(button);
    };

    // Function to toggle the additional account field and button text
    const toggleAdditionalAccount = () => {
        isAdditionalAccountVisible = !isAdditionalAccountVisible;
        additionalAccountContainer.style.display = isAdditionalAccountVisible ? 'block' : 'none';

        // Clear additional account fields if hiding
        if (!isAdditionalAccountVisible) {
            additionalAccountInput.value = '';
            additionalCheckboxes.forEach(cb => cb.checked = false);
        }

        // Update button text
        createAddRemoveButton(isAdditionalAccountVisible);
    };


    // Function to handle input changes in the first account field
    const handleAccountInput = () => {
        if (accountNameInput.value.trim() !== '') {
            // If the button doesn't exist or the additional field is hidden, create the "Add" button
            if (!addRemoveButtonContainer.querySelector('button') || !isAdditionalAccountVisible) {
                createAddRemoveButton(false); // Create "Add" button
            } else {
                 // If button exists and field is visible, ensure it says "Remove"
                createAddRemoveButton(true); // Create "Remove" button
            }
        } else {
            // If the field is empty, remove the button and hide the additional account field
            addRemoveButtonContainer.innerHTML = ''; // Remove the button
            additionalAccountContainer.style.display = 'none'; // Hide additional account field
            isAdditionalAccountVisible = false; // Reset visibility state
             // Clear additional account fields if primary is cleared
            additionalAccountInput.value = '';
            additionalCheckboxes.forEach(cb => cb.checked = false);
        }
    };

    // Add event listeners to the first account field
    accountNameInput.addEventListener('input', handleAccountInput);
    accountNameInput.addEventListener('change', handleAccountInput);


    $(document).ready(function () {

        // NOTE: The following click handlers for #choose_mobile and #choose_email
        // reference elements that are not present in the current HTML.
        // This logic might be deprecated or require corresponding HTML elements to function.
        $('#choose_mobile').on('click', function () {
          //$('input[type="checkbox"]').not(this).prop('checked', false);
          $('#s_email').removeAttr('required');
          $('#sphone').show(); // #sphone likely refers to a container for the phone input
          $('#semail').hide(); // #semail likely refers to a container for the email input
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

        // NOTE: This change handler for #s_method references a select box
        // that is commented out in the HTML. This logic might not be active.
        $('#s_method').change(function () {
          if ($(this).val() == 1) { // Phone selected
            $('#s_email').removeAttr('required');
            $('#sphone').show();
            $('#semail').hide();
            $('#s_email').css('display', 'none');
            $('#s_phone_mobile').attr('required', true);
            $('#s_method_hidden').val('1');
          } else { // Email selected (or default)
            $('#s_phone_mobile').removeAttr('required');
            $('#sphone').hide();
            $('#semail').show();
            $('#s_email').attr('required', true);
            $('#s_email').css('display', 'block'); // This might conflict with the inline style="display:none;"
            $('#s_method_hidden').val('2');
          }
        });

        let typingTimer;
        const typingDelay = 300;

        // Auto-fill email logic (currently sets to name@xxx.com)
        $('#s_name').on('keyup', function () {
          clearTimeout(typingTimer);
          const $this = $(this);
          typingTimer = setTimeout(() => {
            const value = $this.val(); // Get the value
            // Consider if this auto-population is desired/correct logic
            // $('#s_email').val(value.replace(/\s+/g, "") + '@xxx.com');
          }, typingDelay);
        });

        // Optional: Clear timer on keydown
        $('#s_name').on('keydown', function () {
          clearTimeout(typingTimer);
        });

        // Set placeholders and required attributes dynamically
        $('input[name="s_name"]').attr('placeholder', '<?php echo osc_esc_js(__('Name', 'epsilon')); ?>').attr('required', true);
        $('input[name="s_email"]').attr('placeholder', '<?php echo osc_esc_js(__('Email', 'epsilon')); ?>').attr('required', true).prop('type', 'email');
        $('input[name="s_phone_mobile"]').attr('placeholder', '<?php echo osc_esc_js(__('phone number', 'epsilon')); ?>'); // No required here initially
        $('input[name="s_password"]').attr('placeholder', '<?php echo osc_esc_js(__('Password', 'epsilon')); ?>').attr('required', true);
        $('input[name="s_password2"]').attr('placeholder', '<?php echo osc_esc_js(__('Repeat password', 'epsilon')); ?>').attr('required', true);

        // $('#sphone').hide(); // Hide phone container initially if verification method logic was active

        // Initial placeholder check/set (redundant if placeholder set above)
        setTimeout(() => {
          // const value = $this.val(); // $this is not defined here
          $('input[name="s_phone_mobile"]').attr('placeholder', '<?php echo osc_esc_js(__('phone number', 'epsilon')); ?>');
        }, 500); // Delay slightly reduced

    });

    // Phone validation and normalization logic
    document.addEventListener('DOMContentLoaded', function () {
      const phoneInput = document.querySelector('#s_phone_mobile'); // Target mobile phone input

      if (phoneInput) {
        phoneInput.addEventListener('input', function () {
          validateAndNormalizePhone(phoneInput);
        });

        phoneInput.addEventListener('blur', function () {
          validateAndNormalizePhone(phoneInput, true); // Final validation on blur
        });

        // Ensure the space is removed before form submission
        if(phoneInput.form) {
            phoneInput.form.addEventListener('submit', function (e) {
                phoneInput.value = phoneInput.value.replace(/\s/g, ''); // Remove all spaces
            });
        }
      }

      function validateAndNormalizePhone(input, isFinalValidation = false) {
        let phoneValue = input.value; // Don't trim immediately to allow spaces during typing

        // Remove invalid characters (allow digits, '+', and space for formatting)
        phoneValue = phoneValue.replace(/[^+\d\s]/g, '');

        // Normalize spaces (replace multiple spaces with one)
        phoneValue = phoneValue.replace(/\s+/g, ' ').trim();

        // If it doesn't start with '+', reset or handle as invalid (maybe add + automatically?)
         if (phoneValue.length > 0 && !phoneValue.startsWith('+')) {
             phoneValue = '+' + phoneValue.replace(/[^ \d]/g,''); // Prepend + if missing and remove other non-digits/spaces
         }


        // Ethiopian Number Logic (+251)
        if (phoneValue.startsWith('+251')) {
            let countryCode = '+251';
            // Extract number part after potential space
            let localNumber = phoneValue.substring(countryCode.length).trim();
            // Remove non-digits from local part
            localNumber = localNumber.replace(/\D/g, '');

            if (!isFinalValidation) {
                // Allow typing, format with space after code, limit length
                input.value = countryCode + (localNumber.length > 0 ? ' ' + localNumber.substring(0, 9) : '');
            } else {
                // Final Validation: Ensure 9 digits starting with 9
                if (localNumber.length === 9 && localNumber.startsWith('9')) {
                    input.value = countryCode + ' ' + localNumber;
                } else if (localNumber.length === 0) {
                    // Allow just +251 on blur if nothing else entered
                     input.value = countryCode;
                } else {
                    // Invalid Ethiopian number on blur - reset or indicate error
                    // Option 1: Reset to just country code
                    input.value = countryCode;
                    // Option 2: Mark field as invalid (requires CSS/JS)
                    // input.classList.add('invalid-phone');
                     // Maybe set placeholder back?
                    // input.placeholder = '<?php echo osc_esc_js(__('Invalid Ethiopian number (+251 9XXXXXXXX)', 'epsilon')); ?>';

                }
            }
        } else {
             // For other international numbers or partially typed numbers starting with '+'
             // Just keep the cleaned value during typing
             // On blur, maybe add basic validation if needed (e.g., min length)
              input.value = phoneValue; // Keep cleaned value
              // Example final validation: ensure minimum length?
              // if (isFinalValidation && phoneValue.replace(/\s/g, '').length < 7) { // Example: min 7 chars excluding spaces
              //    input.value = ''; // Reset if too short on blur
              // }
        }

      }


       // Logic to show/hide communication methods based on phone input
       const communicationMethodContainer = document.getElementById('communicationMethodContainer');
        if (phoneInput && communicationMethodContainer) {
            const checkPhoneInput = () => {
                 // Check if phone number looks potentially valid enough (e.g., starts with + and has some digits)
                 const cleanedPhone = phoneInput.value.replace(/\s/g, ''); // Remove spaces for check
                 if (cleanedPhone.length > 4 && cleanedPhone.startsWith('+')) { // Example: +xxx requires > 4 chars
                     communicationMethodContainer.style.display = 'block';
                     // Trigger the button check now that the container is visible
                     handleAccountInput();
                 } else {
                     communicationMethodContainer.style.display = 'none';
                     // Also hide the additional account section if communication container hides
                     additionalAccountContainer.style.display = 'none';
                     isAdditionalAccountVisible = false;
                      addRemoveButtonContainer.innerHTML = ''; // Remove button too
                 }
            };

            phoneInput.addEventListener('input', checkPhoneInput);
             phoneInput.addEventListener('blur', checkPhoneInput); // Also check on blur
             checkPhoneInput(); // Initial check on page load
        }

    });


  </script>
  <style>
      /* Style for the Add/Remove button */
      .add-remove-button {
        margin-top: 10px;
        /* Space above the button */
        margin-bottom: 15px; /* Reduced space below */
        padding: 6px 12px; /* Slightly smaller padding */
        font-size: 13px;   /* Slightly smaller font */
        color: #fff;
        /* White text */
        background-color: #007bff;
        /* Blue background */
        border: none;
        /* Remove default border */
        border-radius: 4px; /* Slightly less rounded corners */
        cursor: pointer;
        /* Pointer cursor on hover */
        transition: background-color 0.3s ease;
        /* Smooth hover effect */
        display: block; /* Make it block level */
        width: fit-content; /* Adjust width to content */
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

      /* Input field styling */
      .account-input {
          width: 100%; /* Make inputs take full width */
          padding: 8px;
          margin-bottom: 8px; /* Space below input */
          border: 1px solid #ccc;
          border-radius: 4px;
          box-sizing: border-box; /* Include padding and border in width */
      }


      #additionalAccountContainer .account-input, /* Target additional specifically if needed */
      #accountName { /* Target primary specifically */
        margin-top: 0; /* Reset top margin if label provides spacing */
      }

        #socialNetworkLabel {
            display: block; /* Ensure label is block */
            margin-bottom: 5px; /* Space below label */
            font-weight: 600; /* Make label bold like others */
            color: #333; /* Standard label color */
            font-size: 14px; /* Match other labels if needed */
            margin-top: 12px;
            /* color: #0178d6; /* Original blue color */
         }

      /* General Checkbox container */
        .checkbox-container {
            display: flex;
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
            gap: 10px; /* Space between checkbox groups */
            margin-bottom: 10px; /* Space below the checkbox row */
            padding-left: 0; /* Remove default padding */
            margin-left: 0; /* Remove default margin */
        }

        /* Individual Checkbox Label */
        .checkbox-container label {
            display: flex;
            align-items: center;
            font-size: 13px; /* Slightly smaller text */
            font-weight: normal; /* Normal weight for checkbox text */
            margin: 0; /* Reset margin */
            cursor: pointer;
             white-space: nowrap; /* Prevent text wrapping within label */
        }

        /* Checkbox Input */
        .checkbox-container input[type="checkbox"] {
            margin: 0; /* Reset margin */
            margin-right: 4px; /* Space between checkbox and text */
            width: 12px; /* Slightly smaller checkbox */
            height: 12px;
            vertical-align: middle; /* Align checkbox nicely */
        }

        /* Text Span inside Label */
        .checkbox-container label span {
            /* vertical-align: middle; */ /* Align text with checkbox */
            /* No negative margins needed if alignment is correct */
            line-height: 1; /* Ensure consistent line height */
             padding-top: 1px; /* Fine-tune vertical alignment */
        }


      /* Communication Container styling */
      #communicationMethodContainer {
        margin-top: 15px;
        margin-bottom: 15px; /* Consistent spacing */
        padding: 15px; /* Add some padding */
        border: 1px solid #e0e0e0; /* Add a light border */
        border-radius: 5px; /* Rounded corners */
        background-color: #f9f9f9; /* Light background */
      }

      /* Additional Account Container styling */
      #additionalAccountContainer {
        margin-top: 15px; /* Space above additional section */
        padding-top: 10px; /* Padding inside */
        border-top: 1px dashed #ccc; /* Separator line */
      }


      /* Radio Group Styling */
      .radio-group {
        font-family: inherit; /* Use theme font */
        margin-bottom: 16px;
        display: flex;
        flex-direction: column; /* Stack vertically */
        gap: 8px; /* Space between label and options */
      }

       /* Main Label for Radio Group */
       .radio-group > label[for="category_id"] {
            display: block; /* Ensure it's block */
            margin-bottom: 5px; /* Space below main label */
            font-weight: 600; /* Bold */
            font-size: 14px; /* Standard label size */
        }

        /* Container for each radio option */
         .radio-option {
            /* margin-top adjust handled by gap in radio-group */
         }

        /* Label for individual radio button */
        .radio-label {
            display: flex;
            align-items: center;
            font-size: 14px;
            cursor: pointer;
            font-weight: normal; /* Normal weight for options */
            margin-bottom: 0; /* Remove bottom margin */
        }

        /* Radio Input */
        .radio-label input[type="radio"] {
            width: 13px; /* Adjust size */
            height: 13px;
            margin: 0; /* Reset margin */
            margin-right: 6px; /* Space between radio and text */
        }

         /* Text Span for Radio Option */
         .radio-label .radio-text {
             line-height: 1; /* Consistent line height */
         }


      .radio-group p { /* Style for 'No categories available' */
        font-weight: normal;
        margin-bottom: 8px;
        font-style: italic;
        color: #666;
      }

      /* General label styling (if needed to override theme defaults) */
       label {
           font-weight: 600; /* Common weight for labels */
           display: block; /* Make labels block elements */
           margin-bottom: 3px; /* Small space below labels */
           font-size: 14px;
       }
        /* Ensure required asterisk is styled */
        .req {
            color: red;
            font-weight: bold;
            margin-left: 2px;
        }
  </style>
</body>

</html>