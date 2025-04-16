<?php
/**
 * bank_transfer_confirmation.php - REVISED for LocalStorage Debugging & Product Name
 */

// --- START OSCLASS BOOTSTRAP ---
if(!defined('ABS_PATH')) {
     define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
}
require_once ABS_PATH . 'oc-load.php';
// --- END OSCLASS BOOTSTRAP ---

// --- Initialize Variables ---
$transaction_id = Params::getParam('osp_confirm_tid');
$return_url = Params::getParam('return_url');
$transfer_data = null;
$display_amount = __('N/A', 'osclass_pay');
$display_variable_symbol = __('N/A', 'osclass_pay');
$display_date = __('N/A', 'osclass_pay');
$display_status_text = __('Pending Verification', 'osclass_pay');

// --- Validate Transaction ID ---
if (empty($transaction_id)) {
    osc_add_flash_error_message(__('Invalid confirmation link: Missing transaction ID.', 'osclass_pay'));
    osp_redirect(osc_is_web_user_logged_in() ? osc_user_dashboard_url() : osc_base_url());
    exit;
}

// --- Fetch Transaction Details from Database ---
try {
    $transfer_data = ModelOSP::newInstance()->getBankTransferByTransactionId($transaction_id);
    if (!$transfer_data) {
        throw new Exception(__('Transaction record not found.', 'osclass_pay'));
    }
    // --- Security Check ---
    if (osc_is_web_user_logged_in()) {
        $userIdColumn = 'i_user_id';
        if (!isset($transfer_data[$userIdColumn]) || $transfer_data[$userIdColumn] != osc_logged_user_id()) {
             osc_add_flash_error_message(__('You are not authorized to view this confirmation.', 'osclass_pay'));
             osp_redirect(osc_user_dashboard_url());
             exit;
        }
    } elseif (isset($transfer_data['i_user_id']) && $transfer_data['i_user_id'] > 0) {
        osc_add_flash_error_message(__('Please log in to view your transaction confirmation.', 'osclass_pay'));
        osp_redirect(osc_user_login_url());
        exit;
    }
    // --- End Security Check ---

    // Prepare display data from DB
    $display_amount = osp_format_price($transfer_data['f_amount'] ?? 0);
    $display_variable_symbol = $transfer_data['s_variable_symbol'] ?? __('N/A', 'osclass_pay');
    $date_to_format = $transfer_data['dt_evidence_uploaded'] ?? $transfer_data['dt_date'] ?? null;
    if ($date_to_format) {
        $display_date = osc_format_date($date_to_format, osc_get_preference('date_format'));
    }
    // Status text (can be enhanced later based on DB status)
    // $display_status_text = ...

} catch (Exception $e) {
    osc_add_flash_error_message(__('Error loading transaction details: ', 'osclass_pay') . $e->getMessage());
    osp_redirect(osc_is_web_user_logged_in() ? osc_user_dashboard_url() : osc_base_url());
    exit;
}

// --- Determine Continue Button URL and Text ---
if (!empty($return_url) && filter_var($return_url, FILTER_VALIDATE_URL)) {
    $continue_url = $return_url;
    // Determine text based on URL content
    if (strpos($return_url, 'user/dashboard') !== false) $continue_text = __('Go to Dashboard', 'osclass_pay');
    elseif (strpos($return_url, 'item&id=') !== false) $continue_text = __('Return to Item', 'osclass_pay');
    else $continue_text = __('Continue', 'osclass_pay');
} else {
    // Fallback
    $continue_url = osc_is_web_user_logged_in() ? osc_user_dashboard_url() : osc_base_url();
    $continue_text = osc_is_web_user_logged_in() ? __('Go to Dashboard', 'osclass_pay') : __('Return to Homepage', 'osclass_pay');
}

// --- START HTML ---
?>
<!DOCTYPE html>
<html lang="<?php echo osc_current_user_locale(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Bank Transfer Summary', 'osclass_pay'); ?></title>
    <style>
        /* --- Styles (keep existing styles) --- */
         body { font-family: sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f4f4f4; color: #333; }
        .osp-container { max-width: 750px; margin: 30px auto; padding: 25px 30px; background: #fff; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .osp-title { font-size: 24px; margin-bottom: 20px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 15px; text-align: center; }
        .osp-summary-box { padding: 20px; background: #f9f9f9; border: 1px solid #e7e7e7; border-radius: 4px; margin-bottom: 25px; }
        .osp-summary-list { list-style: none; padding: 0; margin: 0; }
        .osp-summary-list li { margin-bottom: 10px; font-size: 1.05em; padding: 8px 0; border-bottom: 1px dotted #eee; display: flex; flex-wrap: wrap; }
        .osp-summary-list li:last-child { border-bottom: none; }
        .osp-summary-list strong { display: inline-block; width: 190px; color: #444; font-weight: 600; flex-shrink: 0; padding-right: 10px;}
        /* Ensure spans holding dynamic data exist and can wrap */
        .osp-summary-list span { flex-grow: 1; font-weight: 500; word-break: break-word; }
        .osp-status { font-weight: bold; }
        .osp-status.pending { color: #ff9800; }
        .osp-status.verified { color: #28a745; }
        .osp-note { font-style: italic; color: #666; margin-top: 15px; padding: 15px; background-color: #f0f0f0; border-left: 4px solid #ccc; border-radius: 3px; text-align: left; }
        .osp-continue-btn { display: inline-block; box-sizing: border-box; padding: 12px 30px; background: #007bff; color: white; text-decoration: none; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; transition: background-color 0.2s; cursor: pointer; text-align: center; margin-top: 20px; }
        .osp-continue-btn:hover { background: #0056b3; }
        .osp-center-button { text-align: center; }
        /* Flash message styles */
         .flashmessage { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
         .flashmessage-error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
         .flashmessage-warning { color: #856404; background-color: #fff3cd; border-color: #ffeeba; }
         .flashmessage-ok { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
         .flashmessage-info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }
    </style>
</head>
<body>
    <div class="osp-container">

        <?php osc_show_flash_message(); ?>

        <h2 class="osp-title"><?php _e('Bank Transfer Summary', 'osclass_pay'); ?></h2>

        <div class="osp-summary-box">
             <h3 style="margin-top:0; margin-bottom: 15px;"><?php _e('Transaction Details', 'osclass_pay'); ?></h3>
            <ul class="osp-summary-list">
                <li><strong><?php _e('Transaction ID:', 'osclass_pay'); ?></strong> <span><?php echo osc_esc_html($transaction_id); ?></span></li>
                <li><strong><?php _e('Amount Transferred:', 'osclass_pay'); ?></strong> <span><?php echo $transfer_data['f_price'] ?></span></li>
                <li><strong><?php _e('Date Submitted:', 'osclass_pay'); ?></strong> <span><?php echo $display_date; ?></span></li>
                <li><strong><?php _e('Status:', 'osclass_pay'); ?></strong> <span class="osp-status pending"><?php echo osc_esc_html($display_status_text); ?></span></li>
                <?php /* --- Placeholders for LocalStorage Data --- */ ?>
                <li><strong><?php _e('Original Description:', 'osclass_pay'); ?></strong> <?php echo $transfer_data['s_description'] ?></span></li>
                <li><strong><?php _e('Product Code:', 'osclass_pay'); ?></strong> <span id="display-original-product"><?php echo osc_esc_html(__('Loading...', 'osclass_pay')); ?></span></li>
                <li><strong><?php _e('Reference / Variable Symbol:', 'osclass_pay'); ?></strong> <span><?php echo $transfer_data['s_variable'] ?></span></li>
                <li><strong><?php _e('User :', 'osclass_pay'); ?></strong> <span id="display-extra-user"><?php echo osc_esc_html(__('Loading...', 'osclass_pay')); ?></span></li>
                <?php /* ---> ADDED PLACEHOLDER FOR PRODUCT NAME <--- */ ?>
                <li><strong><?php _e('Product Name :', 'osclass_pay'); ?></strong> <span id="display-extra-product-name"><?php echo osc_esc_html(__('Loading...', 'osclass_pay')); ?></span></li>
                 <?php /* Add more placeholders if needed */ ?>
            </ul>
        </div>

        <div class="osp-note">
            <p><strong><?php _e('Next Steps:', 'osclass_pay'); ?></strong></p>
            <p><?php _e('Your proof of payment has been received and is now awaiting manual verification by our team.', 'osclass_pay'); ?></p>
            <p><?php _e('Once confirmed, the status will be updated, and any associated services will be activated. This may take some time.', 'osclass_pay'); ?></p>
            <p><?php _e('You can check the status later in your account dashboard (if applicable).', 'osclass_pay'); ?></p>
        </div>

        <div class="osp-center-button">
            <a href="<?php echo osc_esc_html($continue_url); ?>" class="osp-continue-btn">
                <?php echo osc_esc_html($continue_text); ?>
            </a>
        </div>
    </div>

    <script>
        
        function parseCustomExtraString(extraString) {
            const result = {};
            if (!extraString) return result;
            try {
                // 1. Decode percent encodings first (e.g., %2C -> ',', %27 -> ')
                let decoded = decodeURIComponent(extraString);

                // 2. *** ADDED: Replace '+' signs with spaces ***
                //    The 'g' flag ensures ALL occurrences are replaced.
                decoded = decoded.replace(/\+/g, ' ');

                // 3. Now split into pairs and process
                const pairs = decoded.split('|');
                pairs.forEach(pair => {
                    const parts = pair.split(',', 2); // Split into max 2 parts
                    if (parts.length === 2) {
                        const key = parts[0].trim();
                        const value = parts[1].trim(); // Value should now have spaces instead of '+'
                        if (key) { // Ensure key is not empty
                           result[key] = value;
                        }
                    }
                });
            } catch (e) {
                console.error("Error parsing custom extra string:", extraString, e);
            }
            return result;
        }



        document.addEventListener('DOMContentLoaded', function() {
            const na_text = '<?php echo osc_esc_js(__('N/A', 'osclass_pay')); ?>';
            const error_text = '<?php echo osc_esc_js(__('Error loading', 'osclass_pay')); ?>';

            // --- Retrieve, Display, and Clear Data from LocalStorage ---
            try {
                const transactionId = <?php echo json_encode($transaction_id); ?>;

                if (!transactionId) {
                     console.warn('LocalStorage Retrieve Failed: Transaction ID missing in PHP.');
                     // Set all placeholders to N/A if transaction ID is missing
                     document.getElementById('display-original-desc').textContent = na_text;
                     document.getElementById('display-original-product').textContent = na_text;
                     document.getElementById('display-extra-user').textContent = na_text;
                     // ---> ADDED: Set new placeholder to N/A <---
                     const prodNameEl = document.getElementById('display-extra-product-name'); if (prodNameEl) prodNameEl.textContent = na_text;
                     return; // Stop processing
                }

                if (typeof(Storage) !== "undefined") {
                    const storageKey = `osp_transfer_data_${transactionId}`;
                    const storedDataJson = localStorage.getItem(storageKey);

                    console.log(`LocalStorage Retrieve Attempt: Key='${storageKey}', Raw Data='${storedDataJson}'`);

                    let storedData = null;
                    if (storedDataJson) {
                        try {
                            storedData = JSON.parse(storedDataJson);
                            console.log("LocalStorage Retrieve Success: Parsed Data=", storedData);
                        } catch (jsonError) {
                             console.error("LocalStorage Retrieve Failed: Could not parse JSON.", jsonError, "Raw Data:", storedDataJson);
                             storedData = null; // Ensure storedData is null if parsing fails
                        }
                    } else {
                        console.warn("LocalStorage Retrieve: No data found for key:", storageKey);
                    }

                    // --- Get Element References (Check they exist!) ---
                    const descElement = document.getElementById('display-original-desc');
                    const productElement = document.getElementById('display-original-product');
                    const userElement = document.getElementById('display-extra-user');
                    // ---> ADDED: Get reference to new element <---
                    const productNameElement = document.getElementById('display-extra-product-name');

                    if (!descElement) console.error("HTML Element 'display-original-desc' not found!");
                    if (!productElement) console.error("HTML Element 'display-original-product' not found!");
                    if (!userElement) console.error("HTML Element 'display-extra-user' not found!");
                    // ---> ADDED: Check new element <---
                    if (!productNameElement) console.error("HTML Element 'display-extra-product-name' not found!");


                    // --- Populate Placeholders (if data exists and is valid) ---
                    if (storedData) {
                        // Description
                        if (descElement) {
                            if (storedData.hasOwnProperty('desc')) {
                                descElement.textContent = storedData.desc !== null && storedData.desc !== undefined ? storedData.desc : na_text;
                                console.log("Setting desc:", descElement.textContent);
                            } else {
                                descElement.textContent = na_text;
                                console.log("Setting desc: N/A (property missing)");
                            }
                        }

                        // Product Code
                        if (productElement) {
                             if (storedData.hasOwnProperty('product') && storedData.product !== null && storedData.product !== undefined) {
                                productElement.textContent = storedData.product;
                                console.log("Setting product:", productElement.textContent);
                            } else {
                                productElement.textContent = na_text;
                                console.log("Setting product: N/A (property missing or invalid)");
                            }
                        }

                        // Extra Data (User Name and Product Name)
                        if (storedData.hasOwnProperty('extra') && storedData.extra) {
                            const extraData = parseCustomExtraString(storedData.extra);
                            console.log("Parsed Extra Data:", extraData);

                            // User Name
                            if (userElement) {
                                if (extraData.hasOwnProperty('name') && extraData.name !== null && extraData.name !== undefined) {
                                    userElement.textContent = extraData.name;
                                    console.log("Setting user (from extra):", userElement.textContent);
                                } else {
                                    userElement.textContent = na_text + ' (name not found)';
                                    console.log("Setting user: N/A (name key missing or invalid in parsed extra)");
                                }
                            }

                            // ---> ADDED: Product Name Logic <---
                            if (productNameElement) { // Check if element exists
                                if (extraData.hasOwnProperty('product_name') && extraData.product_name !== null && extraData.product_name !== undefined) {
                                    // Decode might be needed if the name itself contains special chars like '+' that were part of the value
                                    // However, parseCustomExtraString already did decodeURIComponent on the whole string.
                                    productNameElement.textContent = extraData.product_name;
                                    console.log("Setting product name (from extra):", productNameElement.textContent);
                                } else {
                                    productNameElement.textContent = na_text + ' (product_name not found)';
                                    console.log("Setting product name: N/A (product_name key missing or invalid in parsed extra)");
                                }
                            }
                            // ---> END ADDED <---

                        } else {
                            // Handle case where 'extra' property itself is missing
                            if(userElement) {
                                userElement.textContent = na_text + ' (extra missing)';
                                console.log("Setting user: N/A (extra property missing or empty)");
                            }
                             // ---> ADDED: Handle missing 'extra' for product name <---
                            if(productNameElement) {
                                productNameElement.textContent = na_text + ' (extra missing)';
                                console.log("Setting product name: N/A (extra property missing or empty)");
                            }
                        }


                        // --- !!! IMPORTANT: Clean up LocalStorage !!! ---
                        // Consider moving this *outside* the if(storedData) block
                        // if you want to remove the item even if parsing failed earlier.
                        // Keeping it here removes only if data was successfully retrieved and parsed.
                        localStorage.removeItem(storageKey);
                        console.log('LocalStorage Cleanup: Removed data for key:', storageKey);

                    } else {
                        // Handle case where no data was found or JSON parsing failed
                        console.log("Setting all localStorage fields to N/A because no valid data was retrieved.");
                        if(descElement) descElement.textContent = na_text;
                        if(productElement) productElement.textContent = na_text;
                        if(userElement) userElement.textContent = na_text;
                         // ---> ADDED: Set new placeholder to N/A <---
                        if(productNameElement) productNameElement.textContent = na_text;
                    }

                } else {
                    console.error("LocalStorage Retrieve Failed: Not supported by this browser.");
                     // Set all placeholders to Error text
                     if(descElement) descElement.textContent = error_text;
                     if(productElement) productElement.textContent = error_text;
                     if(userElement) userElement.textContent = error_text;
                     // ---> ADDED: Set new placeholder to Error <---
                     const prodNameEl = document.getElementById('display-extra-product-name'); if (prodNameEl) prodNameEl.textContent = error_text;
                }
            } catch (e) {
                console.error("LocalStorage Retrieve Failed: Unhandled JavaScript error.", e);
                 // Set all placeholders to Error text on general failure
                 const descEl = document.getElementById('display-original-desc'); if (descEl) descEl.textContent = error_text;
                 const prodEl = document.getElementById('display-original-product'); if (prodEl) prodEl.textContent = error_text;
                 const userEl = document.getElementById('display-extra-user'); if (userEl) userEl.textContent = error_text;
                 // ---> ADDED: Set new placeholder to Error <---
                 const prodNameEl = document.getElementById('display-extra-product-name'); if (prodNameEl) prodNameEl.textContent = error_text;
            }
        });
        
    </script>

</body>
</html>