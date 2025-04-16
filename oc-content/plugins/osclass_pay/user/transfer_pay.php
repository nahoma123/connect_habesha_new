<?php
// --- START OSCLASS BOOTSTRAP ---
if(!defined('ABS_PATH')) {
    define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/');
}
require_once ABS_PATH . 'oc-load.php';
// --- END OSCLASS BOOTSTRAP ---

// --- PHP Logic for Page 1 (Bank Selection) ---

// Retrieve and validate initial data
$url = '';
$data = osp_get_custom(urldecode(Params::getParam('extra')));
$user_id = $data['user'] ?? null;
$item_id = $data['itemid'] ?? null; // This might be the user_id again if from cart (901x1xUSER_ID)
$email = $data['email'] ?? null;
$product_type_string = $data['product'] ?? ''; // e.g., '901x1x123' or '901x2x456'
$product_type = explode('x', $product_type_string);
$amount = round($data['amount'] ?? 0, 2);
$min = osp_param('bt_min') > 0 ? osp_param('bt_min') : 0;
$checksum = osp_create_checksum($item_id, $user_id, $email, $amount);
$original_checksum = $data['checksum'] ?? '';
$extra_param_encoded = urlencode(urldecode(Params::getParam('extra')));
$concept = $data['concept'] ?? ($data['description'] ?? ''); // Get concept or description

// --- Determine the FINAL REDIRECT URL (for errors / next page) ---
$final_error_redirect_url = osp_pay_url_redirect($product_type);
if (empty($final_error_redirect_url)) {
    $final_error_redirect_url = osc_user_dashboard_url() ?: osc_base_url(); // Fallback
}

// --- Determine the LOGICAL BACK URL ---
$back_url = '';
$ptype_code = $product_type[0] ?? null;
$psubtype_code = $product_type[1] ?? null; // 1 for cart, 2 for item based on cart.php example
$pitem_id = $product_type[2] ?? $item_id; // ID is 3rd part, fallback to item_id from data


// Define OSP constants (ensure these are correct or available)
if (!defined('OSP_TYPE_MULTIPLE')) define('OSP_TYPE_MULTIPLE', 901);

// *** MORE PRECISE BACK URL LOGIC ***
if ($ptype_code == OSP_TYPE_MULTIPLE) {
    if ($psubtype_code == 1 && function_exists('osc_route_url')) { // Subtype 1 indicates CART payment
        $back_url = osc_route_url('osp-cart');
    } elseif ($psubtype_code == 2 && $pitem_id > 0 && function_exists('osc_item_url')) { // Subtype 2 indicates ITEM payment
        $back_url = osc_item_url($pitem_id);
        // Fallback if osc_item_url is empty
        if (empty($back_url)) {
             $item = Item::newInstance()->findByPrimaryKey($pitem_id);
             if ($item) { $back_url = osc_item_url_from_item($item); }
        }
    }
}

// Fallback if the specific logic above didn't yield a URL
if (empty($back_url)) {
    // Try original item page logic just in case product code wasn't 901
    if ($item_id > 0 && function_exists('osc_item_url')) {
         $back_url = osc_item_url($item_id);
         if (empty($back_url)) {
             $item = Item::newInstance()->findByPrimaryKey($item_id);
             if ($item) { $back_url = osc_item_url_from_item($item); }
         }
    }
}

// Final, final fallback
if (empty($back_url)) {
    $back_url = $final_error_redirect_url; // Use the error redirect URL as last resort
}


// --- Initial Validations (Redirect to $final_error_redirect_url on error) ---
if ($checksum != $original_checksum) {
    osc_add_flash_error_message(__('Data checksum has failed, payment was cancelled.', 'osclass_pay'));
    osp_redirect($final_error_redirect_url); exit;
}
if ($user_id > 0 && osc_is_web_user_logged_in() && osc_logged_user_id() != $user_id) {
    osc_add_flash_error_message(__('Bank transfer data are related to different user, payment was cancelled.', 'osclass_pay'));
    osp_redirect($final_error_redirect_url); exit;
}
// Use the specific product code check from cart.php if appropriate (901x1x or 901x2x)
$is_valid_product = ($ptype_code == OSP_TYPE_MULTIPLE && ($psubtype_code == 1 || $psubtype_code == 2));
if (!$is_valid_product || $amount < $min) {
     osc_add_flash_error_message(__('Product type invalid or minimum amount not met.', 'osclass_pay'));
     osp_redirect($final_error_redirect_url); exit;
}
// --- End Validations ---

// --- Define Bank Data & Order ---
$banks = [ /* ... bank data ... */
    'cbe' => ['name' => 'CBE', 'account' => osp_param('bt_iban_cbe') ?: 'CBE_TEST_IBAN'],
    'awash' => ['name' => 'Awash Bank', 'account' => osp_param('bt_iban_awash') ?: 'AWASH_TEST_IBAN'],
    'mpesa' => ['name' => 'M-Pesa', 'account' => osp_param('bt_account_mpesa') ?: 'MPESA_TEST_ACCOUNT'],
    'telebirr' => ['name' => 'Telebirr', 'account' => osp_param('bt_account_telebirr') ?: 'TELEBIRR_TEST_ACCOUNT'],
    'abyssinia' => ['name' => 'Abyssinia Bank', 'account' => osp_param('bt_iban_abyssinia') ?: 'ABYSSINIA_TEST_IBAN']
];
$base_image_url = osc_base_url() . 'oc-content/themes/epsilon/images/';
$image_map = [ /* ... image map ... */
    'abyssinia' => $base_image_url . 'abyssinia_logo.png',
    'awash'     => $base_image_url . 'awash_bank_logo.webp',
    'cbe'       => $base_image_url . 'cbe_logo.webp',
    'mpesa'     => $base_image_url . 'm_pesa_logo.png',
    'telebirr'  => $base_image_url . 'telebirr_logo.png'
];
// Use the requested order
$ordered_bank_keys = ['cbe', 'awash', 'abyssinia', 'telebirr', 'mpesa'];

// --- Define the URL for Page 2 ---
$details_page_url = osc_route_url('osp-bank-transfer-details');

?>
<!DOCTYPE html>
<html lang="<?php echo osc_current_user_locale(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Select Bank for Transfer', 'osclass_pay'); ?></title>
    <style>
        /* CSS from previous version */
        body { font-family: sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f4f4f4; color: #333; }
        .osp-container { max-width: 800px; margin: 30px auto; padding: 25px 30px; background: #fff; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .osp-title { font-size: 24px; margin-bottom: 15px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px;}
        .osp-container p.subtitle { margin-bottom: 25px; color: #555; font-size: 1.1em; }
        .osp-bank-selection { display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-bottom: 25px; }
        .osp-bank-selection label { display: block; border: 2px solid #ccc; border-radius: 8px; padding: 10px; cursor: pointer; transition: border-color 0.2s, box-shadow 0.2s; text-align: center; background-color: #fff; width: 120px; height: 100px; box-sizing: border-box; position: relative; overflow: hidden; }
        .osp-bank-selection input[type="radio"] { opacity: 0; position: absolute; width: 0; height: 0; }
        .osp-bank-selection img { max-width: 100%; max-height: 70%; object-fit: contain; display: block; margin: 0 auto 5px auto; }
        .osp-bank-selection span.bank-name { display: block; font-size: 0.85em; color: #333; line-height: 1.2; height: 2.4em; overflow: hidden; }
        .osp-bank-selection label.selected { border-color: #007bff; box-shadow: 0 0 8px rgba(0, 123, 255, 0.5); }
        .osp-bank-selection input[type="radio"]:focus-visible + img { outline: 2px dashed #007bff; outline-offset: 2px; }
        .osp-form-actions { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap;}
        .osp-submit-button { flex-grow: 1; padding: 12px 20px; background: #007bff; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; text-align: center; transition: background-color 0.2s; }
        .osp-submit-button:hover { background: #0056b3; }
        .osp-back-button { padding: 10px 18px; background: #6c757d; color: white; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; text-align: center; text-decoration: none; transition: background-color 0.2s; display: inline-block; }
        .osp-back-button:hover { background: #5a6268; }
        .osp-error-message { color: #d9534f; background-color: #f2dede; border: 1px solid #ebccd1; padding: 10px 15px; border-radius: 4px; margin-top: 15px; display: none; }
        @media (max-width: 480px) { .osp-form-actions { flex-direction: column-reverse; align-items: stretch; } .osp-submit-button, .osp-back-button { width: 100%; } }
    </style>
</head>
<body>
    <div class="osp-container">
        <h2 class="osp-title"><?php _e('Select Your Bank', 'osclass_pay'); ?></h2>
        <p class="subtitle"><?php _e('Please select one of the following payment methods to finalize your transaction.', 'osclass_pay'); ?></p>

        <form id="bank-select-form" action="<?php echo osc_esc_html($details_page_url); ?>" method="post">
            <?php /* CSRF removed */ ?>

            <!-- Hidden fields -->
            <input type="hidden" name="item_id" value="<?php echo osc_esc_html($item_id); ?>">
            <input type="hidden" name="user_id" value="<?php echo osc_esc_html($user_id); ?>">
            <input type="hidden" name="email" value="<?php echo osc_esc_html($email); ?>">
            <input type="hidden" name="amount" value="<?php echo osc_esc_html($amount); ?>">
            <input type="hidden" name="checksum" value="<?php echo osc_esc_html($checksum); ?>">
            <input type="hidden" name="extra" value="<?php echo osc_esc_html($extra_param_encoded); ?>">
            <input type="hidden" name="concept" value="<?php echo osc_esc_html($concept); ?>">
            <input type="hidden" name="product" value="<?php echo osc_esc_html($product_type_string); ?>">
            <!-- Use $final_error_redirect_url here -->
            <input type="hidden" name="redirect_url" value="<?php echo osc_esc_html($final_error_redirect_url); ?>">

            <!-- Image-based Bank Selection (Corrected Order) -->
            <div class="osp-bank-selection">
                <?php
                $found_banks = false;
                foreach ($ordered_bank_keys as $key): // Use corrected order
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
                if (!$found_banks): echo '<p>' . __('No bank transfer options configured.', 'osclass_pay') . '</p>'; endif;
                ?>
            </div>
            <div id="selection-error" class="osp-error-message"><?php _e('Please select a payment option.', 'osclass_pay'); ?></div>

            <!-- Actions: Back Button and Proceed Button -->
            <div class="osp-form-actions">
                 <?php if ($found_banks): ?>
                    <!-- Back Button - Links to the more precisely determined $back_url -->
                    <a href="<?php echo osc_esc_html($back_url); ?>" class="osp-back-button"><?php _e('Go Back', 'osclass_pay'); ?></a>
                    <!-- Proceed Button -->
                    <button type="submit" class="osp-submit-button"><?php _e('Proceed to Payment Details', 'osclass_pay'); ?></button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script>
        // JS from previous version - no changes needed
        document.addEventListener('DOMContentLoaded', function() {
             const form = document.getElementById('bank-select-form'); /* ... rest of JS */
             const errorDiv = document.getElementById('selection-error');
             const radioButtons = document.querySelectorAll('input[name="selected_bank"]');
             const labels = document.querySelectorAll('.osp-bank-selection label');
             if (form) { form.addEventListener('submit', function(event) { const selectedBank = document.querySelector('input[name="selected_bank"]:checked'); if (!selectedBank && radioButtons.length > 0) { if(errorDiv) errorDiv.style.display = 'block'; event.preventDefault(); } else { if(errorDiv) errorDiv.style.display = 'none'; } }); }
             radioButtons.forEach(radio => { radio.addEventListener('change', function() { labels.forEach(label => { label.classList.remove('selected'); }); if (this.checked) { let parentLabel = this.closest('label'); if(parentLabel) { parentLabel.classList.add('selected'); } if(errorDiv) errorDiv.style.display = 'none'; } }); });
        });
    </script>
</body>
</html>