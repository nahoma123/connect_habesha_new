<?php
/**
 * bank_transfer_confirmation.php - ENHANCED VERSION
 * Improved UI/UX with responsive design, better visual appeal, and enhanced functionality
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
$display_status_class = 'pending';

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
    
    // Status text and class based on status
    if (isset($transfer_data['i_status']) && $transfer_data['i_status'] == 1) {
        $display_status_text = __('Verified', 'osclass_pay');
        $display_status_class = 'verified';
    } elseif (isset($transfer_data['i_status']) && $transfer_data['i_status'] == 2) {
        $display_status_text = __('Rejected', 'osclass_pay');
        $display_status_class = 'rejected';
    } else {
        $display_status_text = __('Pending Verification', 'osclass_pay');
        $display_status_class = 'pending';
    }

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

// --- Define progress steps based on status ---
$progress_steps = [
    [
        'label' => __('Payment Submitted', 'osclass_pay'),
        'icon' => '✓',
        'status' => 'completed' // Always completed since we're on the confirmation page
    ],
    [
        'label' => __('Verification', 'osclass_pay'),
        'icon' => '⌛',
        'status' => $display_status_class == 'verified' ? 'completed' : ($display_status_class == 'rejected' ? 'rejected' : 'active')
    ],
    [
        'label' => __('Activation', 'osclass_pay'),
        'icon' => '★',
        'status' => $display_status_class == 'verified' ? 'active' : ''
    ]
];

// --- START HTML ---
?>
<!DOCTYPE html>
<html lang="<?php echo osc_current_user_locale(); ?>" data-na-text="<?php echo osc_esc_html(__('N/A', 'osclass_pay')); ?>" data-error-text="<?php echo osc_esc_html(__('Error loading', 'osclass_pay')); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Bank Transfer Summary', 'osclass_pay'); ?></title>
    <link rel="stylesheet" href="<?php echo osc_base_url(); ?>oc-content/plugins/osclass_pay/css/bank_transfer_confirmation.css">
    <style>
        /* Animation styles */
        .osp-fade-in {
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
        }
        
        .osp-item-visible {
            opacity: 0;
            transform: translateY(10px);
            animation: slideIn 0.3s ease-in-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .osp-support-form {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .osp-support-form-visible {
            max-height: 300px;
        }
    </style>
</head>
<body>
    <div class="osp-container osp-fade-in">

        <?php osc_show_flash_message(); ?>

        <div class="osp-header">
            <h2 class="osp-title"><?php _e('Bank Transfer Summary', 'osclass_pay'); ?></h2>
            
            <!-- Progress Tracker -->
            <div class="osp-progress-tracker">
                <?php foreach ($progress_steps as $step): ?>
                <div class="osp-progress-step <?php echo $step['status']; ?>">
                    <div class="osp-progress-step-icon"><?php echo $step['icon']; ?></div>
                    <div class="osp-progress-step-label"><?php echo $step['label']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="osp-summary-box">
            <h3><?php _e('Transaction Details', 'osclass_pay'); ?></h3>
            <ul class="osp-summary-list">
                <li><strong><?php _e('Transaction ID:', 'osclass_pay'); ?></strong> <span id="transaction-id"><?php echo osc_esc_html($transaction_id); ?></span></li>
                <li><strong><?php _e('Amount Transferred:', 'osclass_pay'); ?></strong> <span><?php echo isset($transfer_data['f_price']) ? $transfer_data['f_price'] : $display_amount; ?></span></li>
                <li><strong><?php _e('Date Submitted:', 'osclass_pay'); ?></strong> <span><?php echo $display_date; ?></span></li>
                <li><strong><?php _e('Status:', 'osclass_pay'); ?></strong> <span class="osp-status <?php echo $display_status_class; ?>"><?php echo osc_esc_html($display_status_text); ?></span></li>
                <li><strong><?php _e('Original Description:', 'osclass_pay'); ?></strong> <span id="display-original-desc"><?php echo isset($transfer_data['s_description']) ? $transfer_data['s_description'] : __('Loading...', 'osclass_pay'); ?></span></li>
                <li><strong><?php _e('Product Code:', 'osclass_pay'); ?></strong> <span id="display-original-product"><?php echo osc_esc_html(__('Loading...', 'osclass_pay')); ?></span></li>
                <li><strong><?php _e('Reference / Variable Symbol:', 'osclass_pay'); ?></strong> <span><?php echo isset($transfer_data['s_variable']) ? $transfer_data['s_variable'] : $display_variable_symbol; ?></span></li>
                <li><strong><?php _e('User:', 'osclass_pay'); ?></strong> <span id="display-extra-user"><?php echo osc_esc_html(__('Loading...', 'osclass_pay')); ?></span></li>
                <li><strong><?php _e('Product Name:', 'osclass_pay'); ?></strong> <span id="display-extra-product-name"><?php echo osc_esc_html(__('Loading...', 'osclass_pay')); ?></span></li>
            </ul>
        </div>

        <div class="osp-note">
            <p><strong><?php _e('Next Steps:', 'osclass_pay'); ?></strong></p>
            
            <?php if ($display_status_class == 'verified'): ?>
            <p><?php _e('Your payment has been verified and your purchase has been activated.', 'osclass_pay'); ?></p>
            <p><?php _e('You can now enjoy all the benefits of your purchase. Thank you for your business!', 'osclass_pay'); ?></p>
            <?php elseif ($display_status_class == 'rejected'): ?>
            <p><?php _e('Unfortunately, your payment could not be verified. This could be due to missing or incorrect information.', 'osclass_pay'); ?></p>
            <p><?php _e('Please contact our support team for assistance with resolving this issue.', 'osclass_pay'); ?></p>
            <?php else: ?>
            <p><?php _e('Your proof of payment has been received and is now awaiting manual verification by our team.', 'osclass_pay'); ?></p>
            <p><?php _e('Once confirmed, the status will be updated, and any associated services will be activated. This may take 1-2 business days.', 'osclass_pay'); ?></p>
            <p><?php _e('You can check the status later in your account dashboard.', 'osclass_pay'); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Support Section -->
        <div class="osp-support-section">
            <h4 class="osp-support-title"><?php _e('Need Help?', 'osclass_pay'); ?></h4>
            <p class="osp-support-text"><?php _e('If you have any questions about this transaction, our support team is here to help.', 'osclass_pay'); ?></p>
            <a href="#" class="osp-support-btn"><?php _e('Contact Support', 'osclass_pay'); ?></a>
            
            <div class="osp-support-form">
                <p><?php _e('Please contact us at:', 'osclass_pay'); ?> <strong>support@example.com</strong></p>
                <p><?php _e('Include your Transaction ID:', 'osclass_pay'); ?> <strong><?php echo osc_esc_html($transaction_id); ?></strong></p>
            </div>
        </div>

        <div class="osp-center-button">
            <a href="<?php echo osc_esc_html($continue_url); ?>" class="osp-continue-btn">
                <?php echo osc_esc_html($continue_text); ?>
            </a>
        </div>
    </div>

    <script src="<?php echo osc_base_url(); ?>oc-content/plugins/osclass_pay/js/bank_transfer_confirmation.js"></script>
</body>
</html>