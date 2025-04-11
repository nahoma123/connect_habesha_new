<?php
/**
 * bank_transfer_confirmation.php
 *
 * Confirmation and summary page shown after successful bank transfer evidence upload.
 * Displays details fetched from the database based on the transaction ID.
 */

// --- START OSCLASS BOOTSTRAP ---
if(!defined('ABS_PATH')) {
     define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/');
}
require_once ABS_PATH . 'oc-load.php';
// --- END OSCLASS BOOTSTRAP ---

// --- Initialize Variables ---
$transaction_id = Params::getParam('osp_confirm_tid'); // Get the transaction ID from the URL parameter
$return_url = Params::getParam('return_url');         // Get the original final URL
$transfer_data = null;
$display_amount = __('N/A', 'osclass_pay');
$display_variable_symbol = __('N/A', 'osclass_pay');
$display_date = __('N/A', 'osclass_pay');
$display_status_text = __('Pending Verification', 'osclass_pay'); // Default status text

// --- Validate Transaction ID ---
if (empty($transaction_id)) {
    osc_add_flash_error_message(__('Invalid confirmation link: Missing transaction ID.', 'osclass_pay'));
    // Redirect to a safe default if ID is missing
    osp_redirect(osc_is_web_user_logged_in() ? osc_user_dashboard_url() : osc_base_url());
    exit;
}

// --- Fetch Transaction Details from Database ---
try {
    // Use the correct function to get the transfer details by its *primary* ID or code
    // Assuming the transaction_id passed in the URL IS the primary key (pk_i_id)
    // If $transaction_id is the 'code' (like osp_tx_...), use getBankTransferByCode()
    // *** ADJUST ModelOSP function name if necessary ***

    $transfer_data = ModelOSP::newInstance()->getBankTransferByTransactionId($transaction_id); // Or getBankTransferByCode($transaction_id)

    if (!$transfer_data) {
        throw new Exception(__('Transaction record not found.', 'osclass_pay'));
    }

    // --- Security Check (Optional but Recommended) ---
    // Ensure the logged-in user owns this transaction
    if (osc_is_web_user_logged_in()) {
        $userIdColumn = 'i_user_id'; // *** VERIFY THIS COLUMN NAME IN YOUR t_osp_bank_transfer TABLE ***
        if (!isset($transfer_data[$userIdColumn]) || $transfer_data[$userIdColumn] != osc_logged_user_id()) {
             osc_add_flash_error_message(__('You are not authorized to view this confirmation.', 'osclass_pay'));
             osp_redirect(osc_user_dashboard_url()); // Redirect logged-in user to their dashboard
             exit;
        }
    } elseif (isset($transfer_data['i_user_id']) && $transfer_data['i_user_id'] > 0) {
        // If the transaction has a user ID but the current visitor isn't logged in
        osc_add_flash_error_message(__('Please log in to view your transaction confirmation.', 'osclass_pay'));
        osp_redirect(osc_user_login_url());
        exit;
    }
    // --- End Security Check ---


    // Prepare display data
    $display_amount = osp_format_price($transfer_data['f_amount'] ?? 0);
    $display_variable_symbol = $transfer_data['s_variable_symbol'] ?? __('N/A', 'osclass_pay');

    // Use the date the record was created or evidence uploaded date if available
    // *** Check your DB schema for date columns like 'dt_date', 'dt_evidence_uploaded' ***
    $date_to_format = $transfer_data['dt_evidence_uploaded'] ?? $transfer_data['dt_date'] ?? null;
    if ($date_to_format) {
        $display_date = osc_format_date($date_to_format, osc_get_preference('date_format')); // Use Osclass date format
    }

    // You could potentially refine status text based on `i_status` if you use it
    // Example: if ($transfer_data['i_status'] == OSP_STATUS_COMPLETED) { $display_status_text = __('Verified', 'osclass_pay'); }
    // For now, we assume it's always "Pending Verification" on this page.


} catch (Exception $e) {
    osc_add_flash_error_message(__('Error loading transaction details: ', 'osclass_pay') . $e->getMessage());
    // Redirect on error, as we can't show details
    osp_redirect(osc_is_web_user_logged_in() ? osc_user_dashboard_url() : osc_base_url());
    exit;
}


// --- Determine Continue Button URL and Text (Using logic from previous step) ---
if (!empty($return_url) && filter_var($return_url, FILTER_VALIDATE_URL)) {
    $continue_url = $return_url;
    if (strpos($return_url, 'user/dashboard') !== false) {
         $continue_text = __('Go to Dashboard', 'osclass_pay');
    } elseif (strpos($return_url, 'item&id=') !== false) {
        $continue_text = __('Return to Item', 'osclass_pay');
    } else {
         $continue_text = __('Continue', 'osclass_pay');
    }
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
        /* --- Reuse styles from previous confirmation page attempt or bank_transfer_details --- */
         body { font-family: sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f4f4f4; color: #333; }
        .osp-container { max-width: 750px; margin: 30px auto; padding: 25px 30px; background: #fff; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .osp-title { font-size: 24px; margin-bottom: 20px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 15px; text-align: center; }
        .osp-summary-box { padding: 20px; background: #f9f9f9; border: 1px solid #e7e7e7; border-radius: 4px; margin-bottom: 25px; }
        .osp-summary-list { list-style: none; padding: 0; margin: 0; }
        .osp-summary-list li { margin-bottom: 10px; font-size: 1.05em; padding: 8px 0; border-bottom: 1px dotted #eee; display: flex; flex-wrap: wrap; }
        .osp-summary-list li:last-child { border-bottom: none; }
        .osp-summary-list strong { display: inline-block; width: 190px; color: #444; font-weight: 600; flex-shrink: 0; padding-right: 10px;}
        .osp-summary-list span { flex-grow: 1; font-weight: 500; }
        .osp-status { font-weight: bold; }
        .osp-status.pending { color: #ff9800; } /* Orange for pending */
        .osp-status.verified { color: #28a745; } /* Green for verified */
        .osp-note { font-style: italic; color: #666; margin-top: 15px; padding: 15px; background-color: #f0f0f0; border-left: 4px solid #ccc; border-radius: 3px; text-align: left; }
        .osp-continue-btn { display: inline-block; box-sizing: border-box; padding: 12px 30px; background: #007bff; color: white; text-decoration: none; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; transition: background-color 0.2s; cursor: pointer; text-align: center; margin-top: 20px; }
        .osp-continue-btn:hover { background: #0056b3; }
        .osp-center-button { text-align: center; }
         /* Flash message styles (important to show success message) */
         .flashmessage { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
         .flashmessage-error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
         .flashmessage-warning { color: #856404; background-color: #fff3cd; border-color: #ffeeba; }
         .flashmessage-ok { color: #155724; background-color: #d4edda; border-color: #c3e6cb; } /* Style for the success message */
         .flashmessage-info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }
    </style>
</head>
<body>
    <div class="osp-container">

        <?php osc_show_flash_message(); // Display the "Transfer completed..." message set by upload_evidence.php ?>

        <h2 class="osp-title"><?php _e('Bank Transfer Summary', 'osclass_pay'); ?></h2>

        <div class="osp-summary-box">
             <h3 style="margin-top:0; margin-bottom: 15px;"><?php _e('Transaction Details', 'osclass_pay'); ?></h3>
            <ul class="osp-summary-list">
                <li><strong><?php _e('Transaction ID:', 'osclass_pay'); ?></strong> <span><?php echo osc_esc_html($transaction_id); ?></span></li>
                <li><strong><?php _e('Amount Transferred:', 'osclass_pay'); ?></strong> <span><?php echo $display_amount; ?></span></li>
                <li><strong><?php _e('Reference / Variable Symbol:', 'osclass_pay'); ?></strong> <span><?php echo osc_esc_html($display_variable_symbol); ?></span></li>
                <li><strong><?php _e('Date Submitted:', 'osclass_pay'); ?></strong> <span><?php echo $display_date; ?></span></li>
                <li><strong><?php _e('Status:', 'osclass_pay'); ?></strong> <span class="osp-status pending"><?php echo osc_esc_html($display_status_text); ?></span></li>
                 <?php /* You could add more details here if fetched, e.g., bank name if stored */ ?>
                 <?php /* if(isset($transfer_data['s_bank_name'])) { echo '<li><strong>Bank Used:</strong> <span>'.osc_esc_html($transfer_data['s_bank_name']).'</span></li>'; } */ ?>
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
</body>
</html>