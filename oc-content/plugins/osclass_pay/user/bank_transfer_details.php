<?php
/**
 * bank_transfer_details.php - SIMPLIFIED - COMBINED UPLOAD/COMPLETE
 *
 * Page 2 of the Bank Transfer process.
 * - Creates transfer record on initial load.
 * - Displays details.
 * - Form includes file input and hidden fields.
 * - "Complete" button submits the form (including file) to the upload handler.
 * - Upload handler redirects to final URL on success, or back here on failure.
 * - JS disables "Complete" button until file is selected.
 * - NO DB CHANGES required for this logic.
 */

// --- START OSCLASS BOOTSTRAP ---
if(!defined('ABS_PATH')) {
     define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
}
require_once ABS_PATH . 'oc-load.php';
// --- END OSCLASS BOOTSTRAP ---

// --- Configuration & Initialization ---
$transaction_id = null;
// ... (Initialize other variables as in the previous 'simplified' version) ...
$final_url = osc_base_url(); // Default fallback URL
$selected_bank_key = null;
$display_bank_name = __('N/A', 'osclass_pay');
$display_account = __('N/A', 'osclass_pay');
$display_amount = '';
$display_variable_symbol = 'N/A';
$display_transaction_id = 'N/A';

// --- Determine Load Type & Process ---
$is_initial_load = (Params::existParam('checksum') && Params::existParam('selected_bank'));

// ==============================================
// === PATH 1: Initial Load (POST from Page 1) ===
// ==============================================
if ($is_initial_load) {
    // --- Get Data from POST ---
    $selected_bank_key = Params::getParam('selected_bank');
    $item_id = Params::getParam('item_id');
    $user_id = Params::getParam('user_id');
    $email = Params::getParam('email');
    $amount = round(Params::getParam('amount', 0, false, false), 2);
    $received_checksum = Params::getParam('checksum');
    $extra_param_encoded = Params::getParam('extra');
    $concept = Params::getParam('concept');
    $product_string = Params::getParam('product');
    $redirect_url_from_post = Params::getParam('redirect_url');

    // Use the passed URL for errors and the final button link
    if (!empty($redirect_url_from_post)) {
        $final_url = $redirect_url_from_post;
    }

    // --- Validate Checksum ---
    $expected_checksum = osp_create_checksum($item_id, $user_id, $email, $amount);
    if ($expected_checksum != $received_checksum) {
        osc_add_flash_error_message(__('Data integrity check failed. Payment cancelled.', 'osclass_pay'));
        osp_redirect($final_url); // Use the determined final URL
        exit;
    }

    // --- Bank Selection Validation (Optional) ---
     $banks_check = [ 'cbe' => 1, 'awash' => 1, 'mpesa' => 1, 'telebirr' => 1, 'abyssinia' => 1 ];
     if (empty($selected_bank_key) || !isset($banks_check[$selected_bank_key])) {
        osc_add_flash_error_message(__('Invalid bank selection. Please try again.', 'osclass_pay'));
        osp_redirect($final_url);
        exit;
    }

    // --- Create Bank Transfer Record ---
    try {
        $variable_symbol = mb_generate_rand_int(8);
        $product_type = explode('x', $product_string ?? '');
        $cart_string = osp_create_cart_string($product_type[1] ?? '', $user_id, $item_id);
        $extra_decoded = !empty($extra_param_encoded) ? urldecode($extra_param_encoded) : '';

        $transaction_id = ModelOSP::newInstance()->createBankTransfer(
            $variable_symbol, $cart_string, $concept, $amount, $user_id, $extra_decoded
        );

        if (!$transaction_id) { throw new Exception(__('Failed to create bank transfer record.', 'osclass_pay')); }

        $display_variable_symbol = $variable_symbol;
        $display_transaction_id = $transaction_id;
        $display_amount = osp_format_price($amount);

        osp_email_new_bt($transaction_id);
        osp_cart_drop($user_id);

    } catch (Exception $e) {
        osc_add_flash_error_message(__('Error initiating bank transfer: ', 'osclass_pay') . $e->getMessage());
        osp_redirect($final_url);
        exit;
    }

// ==================================================
// === PATH 2: Reload (e.g., after Upload Failure) ===
// ==================================================
} else {
    $transaction_id = Params::getParam('transaction_id'); // Expect ID from redirect param

    if (empty($transaction_id)) {
        osc_add_flash_error_message(__('Transaction information missing on page reload.', 'osclass_pay'));
        osp_redirect(osc_base_url());
        exit;
    }

    // Attempt to get final URL passed back, else use default
    // The upload handler MUST pass this back on failure redirect
    $final_url = Params::getParam('final_url') ?: (osc_is_web_user_logged_in() ? osc_user_dashboard_url() : osc_base_url());

    // Fetch minimal data for display
    $transfer_data = ModelOSP::newInstance()->getBankTransferById($transaction_id);
    if ($transfer_data) {
         $display_variable_symbol = $transfer_data['s_variable_symbol'] ?? 'N/A';
         $display_transaction_id = $transaction_id;
         $display_amount = osp_format_price($transfer_data['f_amount'] ?? 0);
    } else {
        osc_add_flash_warning_message(__('Could not reload transaction details.', 'osclass_pay'));
        $display_transaction_id = $transaction_id; // Show ID at least
    }
    // Bank details not reliably known on reload
    $selected_bank_key = null;
    $display_bank_name = __('N/A (Reloaded)', 'osclass_pay');
    $display_account = __('N/A (Reloaded)', 'osclass_pay');
}

// ================================================================
// === COMMON PATH: Prepare Final Display Vars & URLs ===
// ================================================================

// Get Bank Details if initial load
if ($is_initial_load && $selected_bank_key) {
    $banks = [ /* .. Define banks array .. */
        'cbe' => ['name' => 'CBE', 'account' => osp_param('bt_iban_cbe') ?: 'CBE_TEST_IBAN'],
        'awash' => ['name' => 'Awash Bank', 'account' => osp_param('bt_iban_awash') ?: 'AWASH_TEST_IBAN'],
        'mpesa' => ['name' => 'M-Pesa', 'account' => osp_param('bt_account_mpesa') ?: 'MPESA_TEST_ACCOUNT'],
        'telebirr' => ['name' => 'Telebirr', 'account' => osp_param('bt_account_telebirr') ?: 'TELEBIRR_TEST_ACCOUNT'],
        'abyssinia' => ['name' => 'Abyssinia Bank', 'account' => osp_param('bt_iban_abyssinia') ?: 'ABYSSINIA_TEST_IBAN']
    ];
    $selected_bank_info = $banks[$selected_bank_key] ?? null;
    if ($selected_bank_info) {
        $display_bank_name = $selected_bank_info['name'];
        $display_account = $selected_bank_info['account'];
    }
}

// Ensure $final_url has a value
if (empty($final_url)) { $final_url = osc_base_url(); }

// Upload URL (Form action)
$upload_evidence_url = osc_route_url('osp-upload-evidence');

// --- START HTML ---
?>
<!DOCTYPE html>
<html lang="<?php echo osc_current_user_locale(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Bank Transfer Details & Completion', 'osclass_pay'); ?></title>
    <style>
        /* --- Styles for disabled button --- */
        .osp-complete-btn[disabled] { background-color: #cccccc !important; color: #666666 !important; cursor: not-allowed !important; opacity: 0.7 !important; }
        /* --- Other essential styles --- */
         body { font-family: sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f4f4f4; color: #333; }
        .osp-container { max-width: 750px; margin: 30px auto; padding: 25px 30px; background: #fff; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .osp-title { font-size: 24px; margin-bottom: 10px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px;}
        .osp-subtitle { font-size: 16px; margin-bottom: 25px; color: #555; }
        .osp-transfer-detail { padding: 25px; background: #f9f9f9; border: 1px solid #e7e7e7; border-radius: 4px; margin-bottom: 30px;}
        .osp-detail-list { list-style: none; padding: 0; margin: 0 0 20px 0; }
        .osp-detail-list li { margin-bottom: 12px; font-size: 1.05em; padding: 8px 0; border-bottom: 1px dotted #eee; display: flex; flex-wrap: wrap; }
        .osp-detail-list li:last-child { border-bottom: none; }
        .osp-detail-list strong { display: inline-block; width: 190px; color: #444; font-weight: 600; flex-shrink: 0; padding-right: 10px;}
        .osp-detail-list span { flex-grow: 1; }
        .osp-highlight { font-weight: bold; color: #000; background-color: #fffbdd; padding: 3px 6px; border-radius: 3px; display: inline-block; word-break: break-all; }
        .osp-warning { color: #c0392b; font-weight: bold;}
        .osp-note { font-style: italic; color: #666; margin-top: 15px; padding: 12px; background-color: #f0f0f0; border-left: 4px solid #ccc; border-radius: 3px; }
        .osp-completion-form label { display: block; margin-bottom: 8px; font-weight: bold; font-size: 1.1em; }
        .osp-completion-form input[type="file"] { display: block; margin-bottom: 15px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; width: calc(100% - 22px); background-color: #fff;}
        .osp-completion-form .info-text { margin-bottom: 15px; color: #555; }
        .osp-complete-btn { display: inline-block; width:100%; box-sizing: border-box; padding: 12px 30px; background: #28a745; color: white; text-decoration: none; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; transition: background-color 0.2s, opacity 0.2s; cursor: pointer; text-align: center; }
        .osp-complete-btn:hover:not(:disabled) { background: #218838; }
        hr.osp-separator { border: 0; height: 1px; background: #eee; margin: 30px 0; }
    </style>
</head>
<body>
    <div class="osp-container">
        <?php osc_show_flash_message(); // Shows success/error messages from upload handler ?>

        <h2 class="osp-title"><?php _e('Complete Your Bank Transfer', 'osclass_pay'); ?></h2>
        <p class="osp-subtitle"><?php _e('Use the details below for your transfer, then upload proof and complete.', 'osclass_pay'); ?></p>

        <!-- Transfer Details -->
        <div class="osp-transfer-detail">
             <h3 style="margin-top:0; margin-bottom: 20px;"><?php _e('Payment Instructions', 'osclass_pay'); ?></h3>
            <ul class="osp-detail-list">
                <li><strong><?php _e('Bank/Service Name:', 'osclass_pay'); ?></strong> <span><?php echo osc_esc_html($display_bank_name); ?></span></li>
                <li><strong><?php _e('Transfer Amount:', 'osclass_pay'); ?></strong> <span><span class="osp-highlight"><?php echo $display_amount; ?></span></span></li>
                <li><strong><?php _e('To Account/IBAN:', 'osclass_pay'); ?></strong> <span><span class="osp-highlight"><?php echo osc_esc_html($display_account); ?></span></span></li>
                <li><strong><?php _e('Reference / Variable Symbol:', 'osclass_pay'); ?></strong> <span><span class="osp-highlight"><?php echo $display_variable_symbol; ?></span></span></li>
                 <li><strong class="osp-warning"><?php _e('IMPORTANT:', 'osclass_pay'); ?></strong> <span><?php _e('You MUST include the Reference / Variable Symbol in your transfer details.', 'osclass_pay'); ?></span></li>
                <li><strong><?php _e('Internal Transaction ID:', 'osclass_pay'); ?></strong> <span><?php echo $display_transaction_id; ?> <small>(For support queries)</small></span></li>
            </ul>
            <p class="osp-note"><?php _e('Make your transfer, then select the proof file below and click Complete.', 'osclass_pay'); ?></p>
        </div>

        <hr class="osp-separator">

        <!-- Combined Upload and Completion Form -->
        <form action="<?php echo osc_esc_html($upload_evidence_url); // Submit to upload handler ?>"
              method="post"
              enctype="multipart/form-data"
              id="completion-form"
              class="osp-completion-form">

            <?php /* CSRF removed */ ?>
            <input type="hidden" name="transaction_id" value="<?php echo $display_transaction_id; ?>">
            <!-- Pass the FINAL destination URL to the upload handler -->
            <input type="hidden" name="final_url" value="<?php echo osc_esc_html($final_url); ?>">

            <label for="evidence_image"><?php _e('Upload Receipt:', 'osclass_pay'); ?></label>
            <input type="file" name="evidence_image" id="evidence_image" accept="image/*,application/pdf" required> <?php /* HTML5 required */ ?>

            <p class="info-text" id="complete-message">
                <?php _e('Please select your proof of payment file to enable completion.', 'osclass_pay'); ?>
            </p>

            <!-- This button SUBMITS the form -->
            <button type="submit"
                    id="complete-button"
                    class="osp-complete-btn"
                    disabled <?php /* Start disabled via attribute */ ?> >
                <?php _e('Complete Payment', 'osclass_pay'); ?>
            </button>

        </form>

    </div>

    <script>
        // --- JavaScript for UI Enhancements ---
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('evidence_image');
            const completeButton = document.getElementById('complete-button'); // Now a button
            const completeMessage = document.getElementById('complete-message');
            const completionForm = document.getElementById('completion-form');

            function updateButtonStates() {
                // Check if a file is selected
                if (fileInput.files && fileInput.files.length > 0) {
                    // File selected: ENABLE the Complete button
                    completeButton.disabled = false; // Enable the button
                    if (completeMessage) {
                         completeMessage.textContent = '<?php echo osc_esc_js(__('File selected. Click below to upload and complete.', 'osclass_pay')); ?>';
                    }
                } else {
                    // No file selected: DISABLE the Complete button
                    completeButton.disabled = true; // Disable the button
                     if (completeMessage) {
                        completeMessage.textContent = '<?php echo osc_esc_js(__('Please select your proof of payment file to enable completion.', 'osclass_pay')); ?>';
                    }
                }
            }

            // Initial state check
             updateButtonStates();

            // Add event listener to file input
            if (fileInput) {
                fileInput.addEventListener('change', updateButtonStates);
            }

            // Optional: Reset if form is reset
            if (completionForm) {
                completionForm.addEventListener('reset', function() {
                    setTimeout(updateButtonStates, 50); // Delay slightly
                });
            }
        });
    </script>

</body>
</html>