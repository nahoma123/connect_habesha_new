<?php
// --- START OSCLASS BOOTSTRAP ---
if(!defined('ABS_PATH')) {
    // Adjust level based on your file's location
    define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/');
}
require_once ABS_PATH . 'oc-load.php';
// --- END OSCLASS BOOTSTRAP ---

// --- PHP Logic for Page 1 (Bank Selection) ---

// Retrieve and validate initial data (Keep this logic)
$url = '';
$data = osp_get_custom(urldecode(Params::getParam('extra')));
$user_id = $data['user'] ?? null; // Assuming pk_i_id is in $data['user'] if it's an array, adjust if needed
$item_id = $data['itemid'] ?? null;
$email = $data['email'] ?? null; // Assuming s_email is in $data['email']
$product_type_string = $data['product'] ?? ''; // Keep original product string like '901x2x...'
$product_type = explode('x', $product_type_string);
$amount = round($data['amount'] ?? 0, 2);
$min = osp_param('bt_min') > 0 ? osp_param('bt_min') : 0;
$checksum = osp_create_checksum($item_id, $user_id, $email, $amount);
$original_checksum = $data['checksum'] ?? '';
$extra_param_encoded = urlencode(urldecode(Params::getParam('extra')));
$concept = $data['concept'] ?? ''; // Get concept if passed

// --- Determine the CORRECT final redirect URL ---
$url = osp_pay_url_redirect($product_type);
if (empty($url)) {
    // Define a sensible fallback if the function returns empty
    $url = osc_user_dashboard_url() ?: osc_base_url();
}

// --- Initial Validations ---
if ($checksum != $original_checksum) {
    osc_add_flash_error_message(__('Data checksum has failed, payment was cancelled.', 'osclass_pay'));
    osp_redirect($url); // Redirect to final destination on error
    exit;
}
if ($user_id > 0 && osc_is_web_user_logged_in() && osc_logged_user_id() != $user_id) {
    osc_add_flash_error_message(__('Bank transfer data are related to different user, payment was cancelled.', 'osclass_pay'));
    osp_redirect($url); // Redirect to final destination on error
    exit;
}
if (!($product_type[0] == OSP_TYPE_MULTIPLE && $amount >= $min)) { // Assuming OSP_TYPE_MULTIPLE is correct constant
     osc_add_flash_error_message(__('There was problem recognizing your product or minimum amount not met, please try bank transfer payment again.', 'osclass_pay'));
     osp_redirect($url); // Redirect to final destination on error
     exit;
}
// --- End Validations ---

// --- Define Bank Data (As before) ---
$banks = [
    'cbe' => ['name' => 'CBE', 'account' => osp_param('bt_iban_cbe') ?: 'CBE_TEST_IBAN'],
    'awash' => ['name' => 'Awash Bank', 'account' => osp_param('bt_iban_awash') ?: 'AWASH_TEST_IBAN'],
    'mpesa' => ['name' => 'M-Pesa', 'account' => osp_param('bt_account_mpesa') ?: 'MPESA_TEST_ACCOUNT'],
    'telebirr' => ['name' => 'Telebirr', 'account' => osp_param('bt_account_telebirr') ?: 'TELEBIRR_TEST_ACCOUNT'],
    'abyssinia' => ['name' => 'Abyssinia Bank', 'account' => osp_param('bt_iban_abyssinia') ?: 'ABYSSINIA_TEST_IBAN']
    // Add any others configured
];

// --- Define Image Paths and Order (Based on your example) ---
$base_image_url = osc_base_url() . 'oc-content/themes/epsilon/images/'; // Adjust theme name if needed

// Map image paths to the bank keys used in $banks array
$image_map = [
    'abyssinia' => $base_image_url . 'abyssinia_logo.png',
    'awash'     => $base_image_url . 'awash_bank_logo.webp',
    'cbe'       => $base_image_url . 'cbe_logo.webp',
    'mpesa'     => $base_image_url . 'm_pesa_logo.png',
    'telebirr'  => $base_image_url . 'telebirr_logo.png'
];

// Define the desired display order using the keys
$ordered_bank_keys = ['abyssinia', 'awash', 'cbe', 'mpesa', 'telebirr'];

// Filter out banks that aren't enabled or configured if necessary (optional)
// Example: $ordered_bank_keys = array_filter($ordered_bank_keys, fn($key) => isset($banks[$key]));

// --- Define the URL for the next page (Page 2) ---
$details_page_url = osc_route_url('osp-bank-transfer-details'); // Ensure this route exists

?>
<!DOCTYPE html>
<html lang="<?php echo osc_current_user_locale(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Select Bank for Transfer', 'osclass_pay'); ?></title>
    <style>
        /* Basic Styles */
        body { font-family: sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f4f4f4; color: #333; }
        .osp-container { max-width: 800px; margin: 30px auto; padding: 25px 30px; background: #fff; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .osp-title { font-size: 24px; margin-bottom: 15px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px;}
        .osp-container p.subtitle { margin-bottom: 25px; color: #555; font-size: 1.1em; }

        /* Image Selection Styles */
        .osp-bank-selection { display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-bottom: 25px; }
        .osp-bank-selection label {
            display: block;
            border: 2px solid #ccc;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s;
            text-align: center;
            background-color: #fff;
            width: 120px; /* Adjust width as needed */
            height: 100px; /* Adjust height */
            box-sizing: border-box;
            position: relative; /* For absolute positioning of radio */
            overflow: hidden;
        }
        .osp-bank-selection input[type="radio"] {
            /* Hide the radio button itself */
            opacity: 0;
            position: absolute;
            width: 0;
            height: 0;
        }
        .osp-bank-selection img {
            max-width: 100%;
            max-height: 70%; /* Adjust based on label height */
            object-fit: contain; /* Scale image nicely */
            display: block;
            margin: 0 auto 5px auto; /* Center image */
        }
         .osp-bank-selection span.bank-name {
             display: block;
             font-size: 0.85em;
             color: #333;
             line-height: 1.2;
             height: 2.4em; /* Limit to approx 2 lines */
             overflow: hidden;
         }

        /* Style for selected label */
        .osp-bank-selection input[type="radio"]:checked + img + span.bank-name {
             font-weight: bold; /* Optional: make name bold */
         }
         .osp-bank-selection label.selected { /* Class added by JS */
             border-color: #007bff;
             box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
         }
         /* Optional focus style for accessibility */
         .osp-bank-selection input[type="radio"]:focus + img {
             outline: 2px dashed #007bff; /* Or style the label */
             outline-offset: 2px;
         }


        /* Submit Button */
        .osp-submit-button { display: block; width: 100%; padding: 12px; margin-top: 25px; background: #007bff; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; text-align: center; transition: background-color 0.2s; }
        .osp-submit-button:hover { background: #0056b3; }
        /* Error Message */
        .osp-error-message { color: #d9534f; background-color: #f2dede; border: 1px solid #ebccd1; padding: 10px 15px; border-radius: 4px; margin-top: 15px; display: none; /* Hidden by default */ }
    </style>
</head>
<body>
    <div class="osp-container">
        <h2 class="osp-title"><?php _e('Select Your Bank', 'osclass_pay'); ?></h2>
        <p class="subtitle"><?php _e('Please choose the bank or payment service you intend to use.', 'osclass_pay'); ?></p>

        <form id="bank-select-form" action="<?php echo osc_esc_html($details_page_url); ?>" method="post">
            <?php /* CSRF removed */ ?>

            <!-- Hidden fields to pass payment data to the next page -->
            <input type="hidden" name="item_id" value="<?php echo osc_esc_html($item_id); ?>">
            <input type="hidden" name="user_id" value="<?php echo osc_esc_html($user_id); ?>">
            <input type="hidden" name="email" value="<?php echo osc_esc_html($email); ?>">
            <input type="hidden" name="amount" value="<?php echo osc_esc_html($amount); ?>">
            <input type="hidden" name="checksum" value="<?php echo osc_esc_html($checksum); ?>">
            <input type="hidden" name="extra" value="<?php echo osc_esc_html($extra_param_encoded); ?>">
            <input type="hidden" name="concept" value="<?php echo osc_esc_html($concept); ?>">
            <input type="hidden" name="product" value="<?php echo osc_esc_html($product_type_string); ?>">
            <!-- Pass the final redirect URL -->
            <input type="hidden" name="redirect_url" value="<?php echo osc_esc_html($url); ?>">

            <!-- Image-based Bank Selection -->
            <div class="osp-bank-selection">
                <?php
                $found_banks = false;
                foreach ($ordered_bank_keys as $key):
                    if (isset($banks[$key]) && isset($image_map[$key])):
                        $bank = $banks[$key];
                        $image_src = $image_map[$key];
                        $found_banks = true;
                ?>
                    <label for="bank_<?php echo $key; ?>">
                        <input type="radio" name="selected_bank" value="<?php echo $key; ?>" id="bank_<?php echo $key; ?>" required>
                        <img src="<?php echo osc_esc_html($image_src); ?>" alt="<?php echo osc_esc_html($bank['name']); ?>">
                         <span class="bank-name"><?php echo osc_esc_html($bank['name']); ?></span>
                    </label>
                <?php
                    endif;
                endforeach;

                if (!$found_banks):
                    echo '<p>' . __('No bank transfer options are currently configured with images.', 'osclass_pay') . '</p>';
                endif;
                ?>
            </div>
             <!-- End Image Selection -->

             <div id="selection-error" class="osp-error-message">
                <?php _e('Please select a payment option before proceeding.', 'osclass_pay'); ?>
            </div>

            <?php if ($found_banks): // Only show button if options exist ?>
                <button type="submit" class="osp-submit-button"><?php _e('Proceed to Payment Details', 'osclass_pay'); ?></button>
            <?php endif; ?>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('bank-select-form');
            const errorDiv = document.getElementById('selection-error');
            const radioButtons = document.querySelectorAll('input[name="selected_bank"]');
            const labels = document.querySelectorAll('.osp-bank-selection label');

            // Add simple validation feedback on submit
            if (form) {
                form.addEventListener('submit', function(event) {
                    const selectedBank = document.querySelector('input[name="selected_bank"]:checked');
                    if (!selectedBank && radioButtons.length > 0) { // Only validate if radios exist
                        if(errorDiv) errorDiv.style.display = 'block';
                        event.preventDefault(); // Stop form submission
                    } else {
                        if(errorDiv) errorDiv.style.display = 'none';
                    }
                });
            }

            // Add visual indication ('selected' class) for selected label
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove 'selected' class from all labels first
                    labels.forEach(label => {
                        label.classList.remove('selected');
                    });
                    // Add 'selected' class to the parent label of the checked radio
                    if (this.checked) {
                        // Find the parent label element
                        let parentLabel = this.closest('label');
                        if(parentLabel) {
                            parentLabel.classList.add('selected');
                        }
                        // Hide error message if shown
                        if(errorDiv) errorDiv.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>