<?php
/**
 * bank_transfer_details.php - REVISED for LocalStorage Debugging
 */

// --- START OSCLASS BOOTSTRAP ---
if(!defined('ABS_PATH')) {
     define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
}
require_once ABS_PATH . 'oc-load.php';
// --- END OSCLASS BOOTSTRAP ---

// --- Configuration & Initialization ---
$transaction_id = null;
$final_url = osc_base_url();
$selected_bank_key = null;
$display_bank_name = __('N/A', 'osclass_pay');
$display_account = __('N/A', 'osclass_pay');
$display_amount = '';
$display_variable_symbol = 'N/A';
$display_transaction_id = 'N/A';

// Variables to hold initial params securely for JS
$initial_desc_param = '';
$initial_extra_param = '';
$initial_product_param = '';

// --- Determine Load Type & Process ---
$is_initial_load = (Params::existParam('checksum') && Params::existParam('selected_bank'));

// ==============================================
// === PATH 1: Initial Load (CHECK URL PARAMS) ===
// ==============================================
if ($is_initial_load) {
    // --- Get Data from POST (or GET if it came via link) ---
    // CAPTURE PARAMS FOR LOCALSTORAGE IMMEDIATELY
    $initial_desc_param = Params::getParam('desc');
    $initial_extra_param = Params::getParam('extra'); // Raw, URL-encoded version
    $initial_product_param = Params::getParam('product');

    // Optional: Log captured values to PHP error log for debugging
    error_log("OSP Transfer Debug (Initial Load): Captured desc='{$initial_desc_param}', extra='{$initial_extra_param}', product='{$initial_product_param}'");

    // --- Continue processing other params ---
    $selected_bank_key = Params::getParam('selected_bank');
    $item_id = Params::getParam('item_id'); // May come from 'extra' or direct param - adjust if needed
    $user_id = Params::getParam('user_id'); // May come from 'extra' or direct param - adjust if needed
    $email = Params::getParam('email');     // May come from 'extra' or direct param - adjust if needed
    $amount = round(Params::getParam('amount', 0, false, false), 2); // May come from 'extra' or direct param 'a' - adjust if needed
    $received_checksum = Params::getParam('checksum'); // May come from 'extra' or direct param - adjust if needed
    $extra_param_encoded = $initial_extra_param; // Use the captured one
    $concept = Params::getParam('concept'); // May come from 'extra' or direct param 'desc' - adjust if needed
    $product_string = $initial_product_param; // Use the captured one
    $redirect_url_from_post = Params::getParam('redirect_url');

    // --- If core data (user_id, email, amount, item_id, checksum, concept) comes *only* from 'extra', parse it here ---
    // Example parsing (adjust based on your actual needs for validation/DB insert):
    $extra_parsed_temp = [];
    if(!empty($extra_param_encoded)) {
        parse_str(str_replace('|', '&', urldecode($extra_param_encoded)), $extra_parsed_temp);
        // Override params if they exist in 'extra' and are needed for validation/DB
        $user_id = $extra_parsed_temp['user'] ?? $user_id;
        $item_id = $extra_parsed_temp['itemid'] ?? $item_id;
        $email = $extra_parsed_temp['email'] ?? $email;
        $amount = $extra_parsed_temp['amount'] ?? $amount; // Be careful with data types here
        $received_checksum = $extra_parsed_temp['checksum'] ?? $received_checksum;
        $concept = $extra_parsed_temp['concept'] ?? $concept;
         // Ensure $amount is correctly formatted number
         $amount = round(floatval($amount), 2);
    }
     error_log("OSP Transfer Debug (Initial Load): Using amount={$amount}, user_id={$user_id}, email={$email}, concept='{$concept}' for validation/DB.");


    // Use the passed URL for errors and the final button link
    if (!empty($redirect_url_from_post)) {
        $final_url = $redirect_url_from_post;
    }

    // --- Validate Checksum ---
    // Make sure osp_create_checksum uses the correct values (potentially parsed from 'extra')
    $expected_checksum = osp_create_checksum($item_id, $user_id, $email, $amount);
    if ($expected_checksum != $received_checksum) {
        error_log("OSP Transfer Debug: Checksum mismatch. Expected: {$expected_checksum}, Received: {$received_checksum}");
        osc_add_flash_error_message(__('Data integrity check failed. Payment cancelled.', 'osclass_pay'));
        osp_redirect($final_url);
        exit;
    }

    // --- Bank Selection Validation ---
     $banks_check = [ 'cbe' => 1, 'awash' => 1, 'mpesa' => 1, 'telebirr' => 1, 'abyssinia' => 1 ];
     if (empty($selected_bank_key) || !isset($banks_check[$selected_bank_key])) {
        osc_add_flash_error_message(__('Invalid bank selection. Please try again.', 'osclass_pay'));
        osp_redirect($final_url);
        exit;
    }

    // --- Create Bank Transfer Record ---
    try {
        $variable_symbol = mb_generate_rand_int(8);
        $product_type = explode('x', $product_string ?? ''); // Use $product_string captured earlier
        $cart_string = osp_create_cart_string($product_type[1] ?? '', $user_id, $item_id);
        $extra_decoded = !empty($extra_param_encoded) ? urldecode($extra_param_encoded) : ''; // $extra_param_encoded captured earlier

        // Ensure correct values are passed to createBankTransfer
        $transaction_id = ModelOSP::newInstance()->createBankTransfer(
            $variable_symbol, $cart_string, $concept, $amount, $user_id, $extra_decoded
        );

        if (!$transaction_id) { throw new Exception(__('Failed to create bank transfer record.', 'osclass_pay')); }

        $display_variable_symbol = $variable_symbol;
        $display_transaction_id = $transaction_id; // This ID is crucial for localStorage key
        $display_amount = osp_format_price($amount); // Use the validated/parsed amount

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
    // NO localStorage storage should happen on reload
    $transaction_id = Params::getParam('transaction_id');

    if (empty($transaction_id)) {
        osc_add_flash_error_message(__('Transaction information missing on page reload.', 'osclass_pay'));
        osp_redirect(osc_base_url());
        exit;
    }
    $final_url = Params::getParam('final_url') ?: (osc_is_web_user_logged_in() ? osc_user_dashboard_url() : osc_base_url());

    $transfer_data = ModelOSP::newInstance()->getBankTransferById($transaction_id);
    if ($transfer_data) {
         $display_variable_symbol = $transfer_data['s_variable_symbol'] ?? 'N/A';
         $display_transaction_id = $transaction_id;
         $display_amount = osp_format_price($transfer_data['f_amount'] ?? 0);
    } else {
        osc_add_flash_warning_message(__('Could not reload transaction details.', 'osclass_pay'));
        $display_transaction_id = $transaction_id;
    }
    $selected_bank_key = null;
    $display_bank_name = __('N/A (Reloaded)', 'osclass_pay');
    $display_account = __('N/A (Reloaded)', 'osclass_pay');
}

// ================================================================
// === COMMON PATH: Prepare Final Display Vars & URLs ===
// ================================================================

// Get Bank Details if initial load
if ($is_initial_load && $selected_bank_key) {
    $banks = [
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

if (empty($final_url)) { $final_url = osc_base_url(); }
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
        /* --- Styles --- */
        .osp-complete-btn[disabled] { background-color: #cccccc !important; color: #666666 !important; cursor: not-allowed !important; opacity: 0.7 !important; }
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
        <?php osc_show_flash_message(); ?>

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
        <form action="<?php echo osc_esc_html($upload_evidence_url); ?>"
              method="post"
              enctype="multipart/form-data"
              id="completion-form"
              class="osp-completion-form">

            <?php /* CSRF removed */ ?>
            <input type="hidden" name="transaction_id" value="<?php echo osc_esc_html($display_transaction_id); ?>">
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

    <?php
    // --- Add JavaScript to Store Data in LocalStorage ---
    // This block only runs if it was the initial load AND a transaction ID was successfully created
    if ($is_initial_load && !empty($display_transaction_id)) {
        // USE THE PHP VARIABLES CAPTURED EARLIER
        $data_to_store = [
            'desc' => $initial_desc_param,
            // IMPORTANT: Store the RAW URL-encoded 'extra' param.
            // The confirmation page's JS expects to decode it.
            'extra' => $initial_extra_param,
            'product' => $initial_product_param
        ];
        // Encode the PHP array into a JSON string
        $json_data_to_store_string = json_encode($data_to_store);

        // Check if JSON encoding was successful
        if ($json_data_to_store_string === false) {
             error_log("OSP Transfer Debug: Failed to JSON encode data for localStorage. Data: " . print_r($data_to_store, true));
             // Create a valid JSON 'null' string to prevent JS errors if encoding fails
             $js_string_literal_for_storage = "'null'"; // Use single quotes for JS string literal
        } else {
            // IMPORTANT: Encode the JSON *string* AGAIN using json_encode.
            // This ensures it's output as a valid JavaScript STRING literal,
            // correctly quoted and escaped.
            // e.g., '{"desc":"..."}' becomes '"{\"desc\":\"...\"}"' in the JS source
            $js_string_literal_for_storage = json_encode($json_data_to_store_string);
        }
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- JavaScript to Store Original Data in LocalStorage ---
            try {
                if (typeof(Storage) !== "undefined") {
                    const transactionId = <?php echo json_encode($display_transaction_id); ?>;
                    // Assign the correctly formatted JavaScript string literal
                    const dataJsonStringForStorage = <?php echo $js_string_literal_for_storage; ?>;

                    // Log what's being attempted
                    console.log('LocalStorage Store Attempt: TID=', transactionId);
                    console.log('LocalStorage Store Attempt: Raw JS String Literal=', <?php echo var_export($js_string_literal_for_storage, true); ?>); // Shows exactly what PHP generated
                    console.log('LocalStorage Store Attempt: Final String Value=', dataJsonStringForStorage); // Shows the value assigned in JS

                    if (transactionId && dataJsonStringForStorage && dataJsonStringForStorage !== 'null') {
                        const storageKey = `osp_transfer_data_${transactionId}`;
                        // Store the valid JSON string
                        localStorage.setItem(storageKey, dataJsonStringForStorage);
                        // Verify what was actually stored
                        console.log(`SUCCESS: Stored transfer data in localStorage. Key: ${storageKey}, Value: ${localStorage.getItem(storageKey)}`);
                    } else {
                        console.warn('LocalStorage Store Skipped: Missing transaction ID or data was invalid/null (check PHP JSON encoding).');
                    }
                } else {
                    console.error("LocalStorage Store Failed: Not supported by this browser.");
                }
            } catch (e) {
                console.error("LocalStorage Store Failed: JavaScript error.", e);
            }
        });
    </script>
    <?php
    } // End of the $is_initial_load check for JS data prep
    ?>

    <script>
        // --- JavaScript for UI Enhancements (Button enabling/disabling) ---
        // (Your existing UI script remains here, unchanged)
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('evidence_image');
            const completeButton = document.getElementById('complete-button');
            const completeMessage = document.getElementById('complete-message');
            const completionForm = document.getElementById('completion-form');

            function updateButtonStates() {
                if (fileInput.files && fileInput.files.length > 0) {
                    completeButton.disabled = false;
                    if (completeMessage) {
                         completeMessage.textContent = '<?php echo osc_esc_js(__('File selected. Click below to upload and complete.', 'osclass_pay')); ?>';
                    }
                } else {
                    completeButton.disabled = true;
                     if (completeMessage) {
                        completeMessage.textContent = '<?php echo osc_esc_js(__('Please select your proof of payment file to enable completion.', 'osclass_pay')); ?>';
                    }
                }
            }

             if(fileInput && completeButton) { // Ensure elements exist
                 updateButtonStates();
                 fileInput.addEventListener('change', updateButtonStates);
             } else {
                 console.warn("Could not find file input or complete button for UI enhancement script.");
             }

            if (completionForm) {
                completionForm.addEventListener('reset', function() {
                    setTimeout(updateButtonStates, 50);
                });
            }
        });
    </script>

</body>
<style>
.osp-container {
    margin: 0px;
}
</style>

</html>