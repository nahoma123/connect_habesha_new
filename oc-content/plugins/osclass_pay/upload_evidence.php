<?php
// upload_evidence.php - Corrected Model Function Name

// Define ABS_PATH if not already defined
if(!defined('ABS_PATH')) {
    // Adjust path if upload_evidence.php is in a different location relative to root
    define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/');
}
require_once ABS_PATH . 'oc-load.php';

// --- Initialize Variables ---
$transaction_id = Params::getParam('transaction_id');
$final_url = Params::getParam('final_url'); // Get the final destination URL passed from the form
$upload_success = false;
$error_message = '';

// --- Default Fallback URL if not provided ---
if (empty($final_url)) {
    // Log this issue if possible
    // error_log("OSP Upload Evidence: Missing final_url for transaction ID: " . $transaction_id);
    $final_url = osc_is_web_user_logged_in() ? osc_user_dashboard_url() : osc_base_url();
    osc_add_flash_warning_message(__('Could not determine the final page, redirecting to default.', 'osclass_pay'));
}

// --- Authentication & Authorization ---
if (!osc_is_web_user_logged_in()) {
    $error_message = __('You need to log in to upload evidence.', 'osclass_pay');
    osc_add_flash_error_message($error_message);
    osp_redirect(osc_user_login_url() ?: osc_base_url());
    exit;
}

if (empty($transaction_id)) {
     $error_message = __('Missing transaction ID for upload.', 'osclass_pay');
     osc_add_flash_error_message($error_message);
     osp_redirect($final_url); // Or dashboard?
     exit;
}

// --- Fetch the bank transfer details using the CORRECT function name ---
$transfer = ModelOSP::newInstance()->getBankTransferByTransactionId($transaction_id); // <<<< CORRECTED FUNCTION NAME

// --- Verify transaction and ownership ---
$userIdColumn = 'i_user_id'; // *** VERIFY THIS COLUMN NAME IN YOUR DB ***
$id = osc_logged_user_id();

if (!$transfer || !isset($transfer[$userIdColumn]) || $transfer[$userIdColumn] != osc_logged_user_id()) {
    $error_message = __('Invalid transaction or you are not authorized for this action.', 'osclass_pay');
    osc_add_flash_error_message($error_message);
    osp_redirect($final_url); // Or user dashboard
    exit;
}


// --- File Upload Processing ---
if (!isset($_FILES['evidence_image'])) {
     $error_message = __('No file data received.', 'osclass_pay');
} elseif ($_FILES['evidence_image']['error'] !== UPLOAD_ERR_OK) {
    // Handle specific PHP upload errors
    switch ($_FILES['evidence_image']['error']) {
        // ... (error handling cases as before) ...
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $error_message = __('File is too large.', 'osclass_pay');
            break;
        case UPLOAD_ERR_NO_FILE:
            $error_message = __('No file was selected for upload.', 'osclass_pay');
            break;
        default:
            $error_message = __('An unexpected error occurred during file upload.', 'osclass_pay');
            break;
    }
} else {
    // --- File received OK, proceed with validation ---
    $file = $_FILES['evidence_image'];

    // Validate file type (MIME)
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $file_mime_type = $finfo->file($file['tmp_name']);

    if (!in_array($file_mime_type, $allowed_mime_types)) {
        $error_message = __('Invalid file type. Allowed types: JPG, PNG, GIF, PDF.', 'osclass_pay');
    }

    // Validate file size
    elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB
        $error_message = __('File is too large. Maximum size is 5MB.', 'osclass_pay');
    } else {
        // --- Validation Passed - Move File ---
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        // Double check extension as well
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'pdf'])){
             $error_message = __('Invalid file extension detected.', 'osclass_pay');
        } else {
             $filename = 'evidence_' . $transaction_id . '_' . time() . '.' . $ext;
             $upload_dir = osc_content_path() . 'uploads/osp/evidence/'; // From original code

             // Ensure directory exists and is writable
             if (!file_exists($upload_dir)) {
                 if (!mkdir($upload_dir, 0755, true)) {
                      $error_message = __('Error creating upload directory.', 'osclass_pay');
                 }
             } elseif (!is_writable($upload_dir)) {
                  $error_message = __('Upload directory is not writable.', 'osclass_pay');
             }

             if (empty($error_message)) {
                $upload_path = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // --- Upload Succeeded ---
                    $upload_success = true;
                    $image_path = 'oc-content/uploads/osp/evidence/' . $filename;

                    // Update the database using the function from original code
                    $db_update_success = ModelOSP::newInstance()->updateBankTransferEvidence($transaction_id, $image_path);

                    if(!$db_update_success) {
                         osc_add_flash_warning_message(__('Evidence saved, but failed to update record. Please contact support.', 'osclass_pay'));
                    } else {
                         osc_add_flash_ok_message(__('Transfer completed', 'osclass_pay'));
                    }

                } else {
                    $error_message = __('Failed to save the uploaded file.', 'osclass_pay');
                }
             }
        }
    }
}

// --- Perform Redirect Based on Outcome ---
if ($upload_success) {
    // SUCCESS: Redirect to the FINAL destination URL
    osp_redirect($final_url);
    exit;
} else {
    // FAILURE: Redirect BACK to the details page
    if(empty($error_message)) { $error_message = __('An unknown upload error occurred.', 'osclass_pay'); }
    osc_add_flash_error_message($error_message);

    // Construct the URL for the details page
    $details_page_route = 'osp-bank-transfer-details'; // *** VERIFY THIS ROUTE NAME ***
    if (osc_route_url($details_page_route)) {
        $details_page_base_url = osc_route_url($details_page_route);
        $details_page_url = $details_page_base_url
                            . '?transaction_id=' . urlencode($transaction_id)
                            . '&final_url=' . urlencode($final_url); // Pass params back
    } else {
        // Fallback if route doesn't exist
        $details_page_url = osc_is_web_user_logged_in() ? osc_user_dashboard_url() : osc_base_url();
        osc_add_flash_warning_message(__('Could not return to details page, returning to dashboard/home instead.', 'osclass_pay'));
    }

    osp_redirect($details_page_url);
    exit;
}
?>