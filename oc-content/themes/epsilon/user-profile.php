<?php
$locales = __get('locales');
$user = osc_user();
$location_type = eps_param('profile_location');

$show_on_profile = isset($user['show_on_profile']) ? $user['show_on_profile'] : 'no'; // Default to 'no' if not set


if (osc_profile_img_users_enabled()) {
  osc_enqueue_script('cropper');
  osc_enqueue_style('cropper', osc_assets_url('js/cropper/cropper.min.css'));
}

$location_text = @array_values(array_filter(array(osc_user_city(), osc_user_region(), osc_user_country())))[0];

$primaryMethods = explode(',', $user['primary_methods']); // Convert comma-separated string to array
$additionalMethods = explode(',', $user['additional_methods']); // Convert comma-separated string to array
$primaryAccounts = $user['primary_accounts']; // Primary account details
$additionalAccounts = $user['additional_accounts']; // Additional account details


?>
<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo eps_language_dir(); ?>"
  lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">

<head>
  <?php osc_current_web_theme_path('head.php'); ?>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="googlebot" content="noindex, nofollow" />
</head>

<body id="user-profile" class="body-ua">
  <?php
  osc_current_web_theme_path('header.php');

  if ($location_type == 0) {
    UserForm::location_javascript();
  }
  ?>

  <div class="container primary">
    <div id="user-menu"><?php eps_user_menu(); ?></div>

    <div id="user-main">
      <?php osc_run_hook('user_profile_top'); ?>

      <h1><?php _e('My Profile', 'epsilon'); ?></h1>

      <div class="profile-box prim">
        <form action="<?php echo osc_base_url(true); ?>" method="post" class="profile">
          <div class="wrap">
            <div class="left-block">
              <input type="hidden" name="page" value="user" />
              <input type="hidden" name="action" value="profile_post" />

              <?php if (osc_profile_img_users_enabled()) { ?>
                <div class="control-group profile-img">
                  <label class="control-label" for="name"><?php _e('Profile picture (avatar)', 'epsilon'); ?></label>
                  <div class="controls">
                    <div class="user-img">
                      <div class="img-preview">
                        <img src="<?php echo osc_user_profile_img_url(osc_logged_user_id()); ?>"
                          alt="<?php echo osc_esc_html(osc_logged_user_name()); ?>" />
                      </div>
                    </div>

                    <div class="user-img-button">
                      <?php UserForm::upload_profile_img(); ?>
                    </div>
                  </div>
                </div>
              <?php } ?>

              <div class="row">
                <label for="name"><?php _e('Your name', 'epsilon'); ?> <span class="req">*</span></label>
                <div class="input-box"><?php UserForm::name_text(osc_user()); ?></div>

                <?php if (function_exists('profile_picture_show') && !osc_profile_img_users_enabled()) { ?>
                  <a href="#" class="update-avatar"><?php _e('Update avatar', 'epsilon'); ?></a>
                  <?php echo profile_picture_show(); ?>
                <?php } ?>
              </div>

              <div class="row hide-email">
                <label for="email"><?php _e('E-mail', 'epsilon'); ?> <a href="#"
                    class="change-email"><?php _e('Edit', 'epsilon'); ?></a></label>
                <div class="input-box"><input type="text" disabled
                    value="<?php echo osc_esc_html(osc_user_email()); ?>" /></div>
              </div>

              <div class="row" style="margin-top:20px">
                <label for="phoneMobile"><?php _e("Phone number (you'll receive a four-digit code)", 'epsilon'); ?>
                  <span class="req">*</span></label>
                <div class="input-box"><?php UserForm::mobile_text(osc_user()); ?></div>
              </div>
              <div class="radio-group">
                <label for="show_on_profile">
                  <?php _e('Show phone on profile?', 'epsilon'); ?> <span class="req">*</span>
                </label>

                <div class="radio-option">
                  <label class="radio-label">
                    <input type="radio" name="show_on_profile" value="yes" checked=""> <span class="radio-text">Yes
                      (visible to all registered users)</span>
                  </label>
                </div>

                <div class="radio-option" style="margin-top: 8px;">
                  <label class="radio-label">
                    <input type="radio" name="show_on_profile" value="no" <?php echo ($show_on_profile === 'no') ? 'checked' : ''; ?>>
                    <span class="radio-text">No (not visible to anyone)</span>
                  </label>
                </div>
              </div>


              <label id="socialNetworkLabel" for="additionalAccountContainer" style="margin-top: 12px;"
                ><?php _e('Social Networking numbers', 'epsilon'); ?></label>

              <!-- Primary Account Name Input -->
              <input type="text" id="accountName" name="primary_accounts" class="account-input"
               placeholder="Ex:- +251 911002244, @Merry_26"
                value="<?php echo osc_esc_html($primaryAccounts); ?>"><br/>

              <div class="row p1">
                <!-- Communication Method Selection -->
                <div class="checkbox-container">
                  <label>
                    <input type="checkbox" name="primary_methods[]" value="Telegram" <?php echo in_array('Telegram', $primaryMethods) ? 'checked' : ''; ?>>
                    <span>Telegram</span>
                  </label>
                  <label>
                    <input type="checkbox" name="primary_methods[]" value="WhatsApp" <?php echo in_array('WhatsApp', $primaryMethods) ? 'checked' : ''; ?>>
                    <span>WhatsApp</span>
                  </label>
                  <label>
                    <input type="checkbox" name="primary_methods[]" value="SMS" <?php echo in_array('SMS', $primaryMethods) ? 'checked' : ''; ?>>
                    <span>SMS (text)</span>
                  </label>
                  <label>
                    <input type="checkbox" name="primary_methods[]" value="DirectCall" <?php echo in_array('DirectCall', $primaryMethods) ? 'checked' : ''; ?>>
                    <span>Direct call</span>
                  </label>
                </div>
              </div>

              <!-- Add/Remove Button Container
              <div id="addRemoveButtonContainer"
                style="display: <?php echo !empty($primaryAccounts) ? 'block' : 'none'; ?>;">
                <button type="button"
                  class="add-remove-button"><?php echo !empty($additionalAccounts) ? 'Remove Additional Account' : 'Add Additional Account'; ?></button>
              </div> -->

            <!-- Additional Account Container -->
            <div id="additionalAccountContainer" style="display: block;">
              <!-- Additional Account Name Input -->
              <input type="text" id="additionalAccountName" name="additional_accounts" class="account-input"
                placeholder="Ex:- +251 911002244, @Merry_26"
                value="<?php echo osc_esc_html($additionalAccounts); ?>"><br/>

              <!-- Communication Method Selection for Additional Account -->
              <div class="checkbox-container">
                <label>
                  <input type="checkbox" name="additional_methods[]" value="Telegram" <?php echo in_array('Telegram', $additionalMethods) ? 'checked' : ''; ?>> <span>Telegram</span>
                </label>
                <label>
                  <input type="checkbox" name="additional_methods[]" value="WhatsApp" <?php echo in_array('WhatsApp', $additionalMethods) ? 'checked' : ''; ?>> <span>WhatsApp</span>
                </label>
                <label>
                  <input type="checkbox" name="additional_methods[]" value="SMS" <?php echo in_array('SMS', $additionalMethods) ? 'checked' : ''; ?>> <span>SMS (text)</span>
                </label>
                <label>
                  <input type="checkbox" name="additional_methods[]" value="DirectCall" <?php echo in_array('DirectCall', $additionalMethods) ? 'checked' : ''; ?>> <span>Direct call</span>
                </label>
              </div>




              <!--
              <div class="row" style="margin-top:20px">
                <label for="phoneLand"><?php _e('Land Phone', 'epsilon'); ?></label>
                <div class="input-box"><?php UserForm::phone_land_text(osc_user()); ?></div>
              </div>

              <div class="row">
                <label for="webSite"><?php _e('Website', 'epsilon'); ?></label>
                <div class="input-box"><?php UserForm::website_text(osc_user()); ?></div>
              </div>

              <div class="row">
                <label for="user_type"><?php _e('User type', 'epsilon'); ?></label>
                <div class="input-box">
                  <?php UserForm::is_company_select(osc_user(), __('Personal', 'epsilon'), __('Company', 'epsilon')); ?>
                </div>
              </div> -->

              <div class="hooksrow"><?php osc_run_hook('user_form'); ?></div>

              <!-- <div class="row">
                <label for="info"><?php _e('About you', 'epsilon'); ?></label>
                <?php UserForm::multilanguage_info($locales, osc_user()); ?>
              </div> -->
            </div>
            </div>



            <div id="user-loc" class="right-block navigator-fill-selects">
              <?php osc_run_hook('user_profile_sidebar'); ?>


              <h2><?php _e('Location information', 'epsilon'); ?></h2>

              <?php if (eps_param('default_location') == 1) { ?>
                <div class="row navigator">
                  <a href="#" class="locate-me">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                      <path
                        d="M256 168c-48.6 0-88 39.4-88 88s39.4 88 88 88 88-39.4 88-88-39.4-88-88-88zm0 128c-22.06 0-40-17.94-40-40s17.94-40 40-40 40 17.94 40 40-17.94 40-40 40zm240-64h-49.66C435.49 145.19 366.81 76.51 280 65.66V16c0-8.84-7.16-16-16-16h-16c-8.84 0-16 7.16-16 16v49.66C145.19 76.51 76.51 145.19 65.66 232H16c-8.84 0-16 7.16-16 16v16c0 8.84 7.16 16 16 16h49.66C76.51 366.81 145.19 435.49 232 446.34V496c0 8.84 7.16 16 16 16h16c8.84 0 16-7.16 16-16v-49.66C366.81 435.49 435.49 366.8 446.34 280H496c8.84 0 16-7.16 16-16v-16c0-8.84-7.16-16-16-16zM256 400c-79.4 0-144-64.6-144-144s64.6-144 144-144 144 64.6 144 144-64.6 144-144 144z" />
                    </svg>
                    <strong><?php _e('Use current location', 'epsilon'); ?></strong>
                    <span class="status">
                      <span class="init"><?php _e('Click to find closest city to your location', 'epsilon'); ?></span>
                      <span class="not-supported"
                        style="display:none;"><?php _e('Geolocation is not supported by your browser', 'epsilon'); ?></span>
                      <span class="failed"
                        style="display:none;"><?php _e('Unable to retrieve your location, it may be blocked', 'epsilon'); ?></span>
                      <span class="failed-unfound"
                        style="display:none;"><?php _e('Unable to retrieve your location, no close city found', 'epsilon'); ?></span>
                      <span class="loading" style="display:none;"><?php _e('Locating...', 'epsilon'); ?></span>
                      <span class="success" style="display:none;"></span>
                      <span class="refresh"
                        style="display:none;"><?php _e('Refresh page to take effect', 'epsilon'); ?></span>
                    </span>
                  </a>
                </div>
              <?php } ?>
              <?php if ($location_type == 0) { ?>
  <div class="row">
    <label for="country"><?php _e('Country', 'epsilon'); ?></label>
    <div class="input-box">
      <?php
      // Disable the country select dropdown
      $country_select = UserForm::country_select(osc_get_countries(), osc_user(), true);
      $country_select = str_replace('<select', '<select disabled', $country_select);
      echo $country_select;
      ?>
      <!-- Hidden input to submit the country value -->
      <input type="hidden" name="countryId" value="<?php echo osc_esc_html($user['fk_c_country_code']); ?>" />
      <input type="hidden" name="category_id" value="<?php echo osc_esc_html($user['category_id']); ?>" />
    </div>
  </div>

  <div class="row">
    <label for="region"><?php _e('Region', 'epsilon'); ?></label>
    <div class="input-box"><?php UserForm::region_select(osc_get_regions(), osc_user()); ?></div>
  </div>

  <div class="row">
    <label for="city"><?php _e('City', 'epsilon'); ?></label>
    <div class="input-box"><?php UserForm::city_select(osc_get_cities(), osc_user()); ?></div>
  </div>

<?php } else if ($location_type == 1) { ?>
  <input type="hidden" name="countryId" id="sCountry" value="<?php echo osc_esc_html($user['fk_c_country_code']); ?>" />
  <input type="hidden" name="regionId" id="sRegion" value="<?php echo osc_esc_html($user['fk_i_region_id']); ?>" />
  <input type="hidden" name="cityId" id="sCity" value="<?php echo osc_esc_html($user['fk_i_city_id']); ?>" />

  <div class="row">
    <label for="sLocation" class="auto-width"><?php _e('Location', 'epsilon'); ?></label>
    <div class="input-box picker location only-search">
      <input name="sLocation" type="text" class="location-pick" id="sLocation"
        placeholder="<?php echo osc_esc_html(__('Start typing region, city...', 'epsilon')); ?>"
        value="<?php echo osc_esc_html($location_text); ?>" autocomplete="off" />
      <i class="clean fas fa-times-circle"></i>
      <div class="results"></div>
    </div>
  </div>
<?php } ?>

<div class="row">
  <label for="cityArea"><?php _e('City Area', 'epsilon'); ?></label>
  <div class="input-box"><?php UserForm::city_area_text(osc_user()); ?></div>
</div>

              <!-- <div class="row">
                <label for="address"><?php _e('Address', 'epsilon'); ?></label>
                <div class="input-box"><?php UserForm::address_text(osc_user()); ?></div>
              </div>

              <div class="row">
                <label for="address"><?php _e('ZIP', 'epsilon'); ?></label>
                <div class="input-box"><?php UserForm::zip_text(osc_user()); ?></div>
              </div> -->
            </div>
          </div>

          <div class="row user-buttons">
            <button type="submit" class="btn btn-primary mbBg"><?php _e('Update', 'epsilon'); ?></button>

            <?php if (!eps_is_demo()) { ?>
              <a class="btn-remove-account btn btn-secondary"
                href="<?php echo osc_base_url(true) . '?page=user&action=delete&id=' . osc_user_id() . '&secret=' . $user['s_secret']; ?>"
                onclick="return confirm('<?php echo osc_esc_js(__('Are you sure you want to delete your account? This action cannot be undone', 'epsilon')); ?>?')"><span><?php _e('Delete account', 'epsilon'); ?></span></a>
            <?php } ?>
          </div>
        </form>
      </div>

      <!-- <div class="profile-box alt change-mail">
        <h2><?php _e('Change your email', 'epsilon'); ?></h2>

        <form action="<?php echo osc_base_url(true); ?>" method="post" id="user_email_change" class="user-change">
          <?php if (!eps_is_demo()) { ?>
            <input type="hidden" name="page" value="user" />
            <input type="hidden" name="action" value="change_email_post" />
          <?php } ?>

          <div class="row">
            <label for="email"><?php _e('Current e-mail', 'epsilon'); ?></label>
            <div class="input-box"><input type="text" disabled
                value="<?php echo osc_esc_html(osc_logged_user_email()); ?>" /></div>
          </div>

          <div class="row">
            <label for="new_email"><?php _e('New e-mail', 'epsilon'); ?> <span class="req">*</span></label>
            <div class="input-box"><input type="text" name="new_email" id="new_email" value="" /></div>
          </div>

          <div class="row user-buttons">
            <?php if (eps_is_demo()) { ?>
              <a class="btn mbBg disabled" onclick="return false;"
                title="<?php echo osc_esc_html(__('You cannot do this on demo site', 'epsilon')); ?>"><?php _e('Submit', 'epsilon'); ?></a>
            <?php } else { ?>
              <button type="submit" class="btn mbBg" disabled><?php _e('Submit', 'epsilon'); ?></button>
            <?php } ?>
          </div>
        </form>
      </div> -->

      <div class="profile-box alt change-pass">
        <h2><?php _e('Change your password', 'epsilon'); ?></h2>

        <form action="<?php echo osc_base_url(true); ?>" method="post" id="user_password_change" class="user-change">
          <?php if (!eps_is_demo()) { ?>
            <input type="hidden" name="page" value="user" />
            <input type="hidden" name="action" value="change_password_post" />
          <?php } ?>

          <div class="row">
            <label for="password"><?php _e('Current password', 'epsilon'); ?> <span class="req">*</span></label>
            <div class="input-box"><input type="password" name="password" id="password" value="" /></div>
          </div>

          <div class="row">
            <label for="new_password"><?php _e('New password', 'epsilon'); ?> <span class="req">*</span></label>
            <div class="input-box">
              <input type="password" name="new_password" id="new_password" value="" />
              <a href="#" class="toggle-pass"
                title="<?php echo osc_esc_html(__('Show/hide password', 'epsilon')); ?>"><i
                  class="fa fa-eye-slash"></i></a>
            </div>
          </div>

          <div class="row">
            <label for="new_password2"><?php _e('Repeat new password', 'epsilon'); ?> <span class="req">*</span></label>
            <div class="input-box">
              <input type="password" name="new_password2" id="new_password2" value="" />
              <a href="#" class="toggle-pass"
                title="<?php echo osc_esc_html(__('Show/hide password', 'epsilon')); ?>"><i
                  class="fa fa-eye-slash"></i></a>
            </div>
          </div>

          <div class="row user-buttons">
            <?php if (eps_is_demo()) { ?>
              <a class="btn mbBg disabled" onclick="return false;"
                title="<?php echo osc_esc_html(__('You cannot do this on demo site', 'epsilon')); ?>"><?php _e('Submit', 'epsilon'); ?></a>
            <?php } else { ?>
              <button type="submit" class="btn mbBg" disabled><?php _e('Submit', 'epsilon'); ?></button>
            <?php } ?>
          </div>
        </form>
      </div>

    </div>
  </div>

  <?php
  $locale = osc_get_current_user_locale();
  $locale_code = $locale['pk_c_code'];
  $locale_name = $locale['s_name'];
  ?>

  <script>


    $(document).ready(function () {
      // Unify selected locale in all tabs
      function delUserLocCheck() {
        if ($('.tabbernav li').length) {
          var localeText = "<?php echo trim(osc_esc_html($locale_name)); ?>";
          $('.tabbernav > li > a:contains("' + localeText + '")').click();
          clearInterval(checkTimer);
          return;
        }
      }

      var checkTimer = setInterval(delUserLocCheck, 150);

      <?php if (!eps_is_demo()) { ?>
        // Enable submit buttons
        $('input#new_email').on('keyup', function () {
          if ($(this).val() != '') {
            $(this).closest('.profile-box').find('button').attr('disabled', false);
          } else {
            $(this).closest('.profile-box').find('button').attr('disabled', true);
          }
        });

        $('input#password, input#new_password, input#new_password2').on('keyup', function () {
          if ($(this).val() != '') {
            $(this).closest('.profile-box').find('button').attr('disabled', false);
          } else {
            $(this).closest('.profile-box').find('button').attr('disabled', true);
          }
        });
      <?php } ?>
    });

    document.addEventListener('DOMContentLoaded', function () {
      const phoneInput = document.querySelector('#s_phone_mobile'); // Replace with your input field ID

      if (phoneInput) {
        // Format the initial value immediately on page load
        formatInitialPhoneValue(phoneInput);

        // Use a setTimeout to check for the value again after a short delay
        setTimeout(() => {
          formatInitialPhoneValue(phoneInput);
        }, 500); // Check again after 500ms

        // Use MutationObserver to watch for changes to the input's value
        const observer = new MutationObserver(function (mutations) {
          mutations.forEach(function (mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
              formatInitialPhoneValue(phoneInput);
            }
          });
        });

        // Start observing the input for attribute changes
        observer.observe(phoneInput, {
          attributes: true, // Watch for attribute changes
        });

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

      function formatInitialPhoneValue(input) {
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

          // Format the initial value for Ethiopian numbers
          if (localNumber.length === 9 && localNumber.startsWith('9')) {
            input.value = '+251 ' + localNumber; // Add a space after the country code
          } else {
            // If the local number is invalid, reset to just the country code
            input.value = '+251';
          }
        } else {
          // For other international numbers, leave unchanged
          input.value = phoneValue;
        }
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
  </script>
  <style>
    .row.p1 {
      width: 100%;
    }

    .hide-email {
      display: none;
    }

    .input-box {
      margin: 5px;
    }

    .radio-group {
      display: flex;
      flex-direction: column;
      /* Stack radio options vertically */
      gap: 8px;
      /* Small gap between items */

    }

    .radio-group label[for="show_on_profile"] {
      margin-bottom: 8px;
      /* Add spacing between main label and options */
    }

    .radio-option {
      display: flex;
      align-items: center;
      margin: 0;
    }


    .radio-label {
      display: flex;
      align-items: center;
      /* Ensure alignment of radio button and text */
    }

    .radio-label input[type="radio"] {
      margin: 0;
      /* Remove default margins for perfect alignment */
      line-height: 0;
      /* Remove any additional spacing inside the radio button */
    }


    .radio-text {
      margin-right: 4px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
      padding-top: 0.5px;
    }


    .radio-group p {
      margin-bottom: 10px;
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

    /* Style for the input field */
    .account-input {
      width: 300px;
      /* Adjust width as needed */
      padding: 8px;
      margin-top: 10px;
      /* Space above the input */
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
      display: none;
      margin-top: 20px;
      width: 100%;
    }


    /* Align radio buttons with labels and reduce size */
    .radio-label {
      display: flex !important;
      align-items: center !important;
      /* Align radio button and text vertically */
      margin-bottom: 8px;
      /* Adjust spacing as needed */
    }

    .radio-label .radio-text {
      margin-left: 5px;
      display: flex;
      align-items: center;
    }


    /* Hide the default radio button */
    .radio-label input[type="radio"] {
      width: 11px;
      height: 11px;
      margin-left: 0px;
    }

    /* Style the custom radio button when checked */
    .radio-label input[type="radio"]:checked+.custom-radio::after {
      content: '';
      display: block;
      width: 6px;
      /* Inner circle size */
      height: 6px;
      /* Inner circle size */
      background-color: #0178d6;
      /* Inner circle color */
      border-radius: 50%;
      /* Make it circular */
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    .radio-group .custom-radio {
      margin: 0px;
    }

    /* Italicize the bottom text */
    small em {
      font-style: italic;
    }

    .checkbox-container {
      margin-top: 8px;
      display: flex;
      flex-direction: row;
      /* Arrange items in a row */
      align-items: center;
      /* Vertically center items */
      gap: 0;
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

    #addRemoveButtonContainer button {
      font-size: 13px !important;
      font-family: "Comfortaa", sans-serif;
    }

    #s_name {
      max-width: 100% !important;
    }

    #socialNetworkLabel{
      color: #0178d6;
    }


    /* Style for the additional account field */

  </style>

  <?php
  if (function_exists('profile_picture_upload') && !osc_profile_img_users_enabled()) {
    profile_picture_upload();
  }
  ?>

  <?php osc_current_web_theme_path('footer.php'); ?>
</body>

</html>