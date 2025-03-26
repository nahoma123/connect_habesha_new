<?php
require_once ABS_PATH . 'oc-load.php';

// Check if the user is logged in
if (!osc_is_web_user_logged_in()) {
    osc_add_flash_error_message(__('You need to log in to upload evidence.', 'osclass_pay'));
    header('Location: ' . osc_base_url());
    exit;
}

// Retrieve the transaction ID from the POST data
$transaction_id = Params::getParam('transaction_id');

// Fetch the bank transfer details
$transfer = ModelOSP::newInstance()->getBankTransferByTransactionId($transaction_id);

// Verify the transaction exists and belongs to the logged-in user
if (!$transfer || $transfer['i_user_id'] != osc_logged_user_id()) {
    osc_add_flash_error_message(__('Invalid transaction or you are not authorized to upload evidence for this transaction.', 'osclass_pay'));
    header('Location: ' . osc_base_url());
    exit;
}

// Check if a file was uploaded successfully
if (isset($_FILES['evidence_image']) && $_FILES['evidence_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['evidence_image'];

    // Validate file type (only allow JPEG, PNG, GIF)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        osc_add_flash_error_message(__('Only image files (JPEG, PNG, GIF) are allowed.', 'osclass_pay'));
        header('Location: ' . osc_base_url());
        exit;
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        osc_add_flash_error_message(__('File is too large. Maximum size is 5MB.', 'osclass_pay'));
        header('Location: ' . osc_base_url());
        exit;
    }

    // Generate a unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'evidence_' . $transaction_id . '_' . time() . '.' . $ext;
    $upload_dir = osc_content_path() . 'uploads/osp/evidence/';

    // Create the directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $upload_path = $upload_dir . $filename;

    // Move the uploaded file to the target directory
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Store the relative path in the database
        $image_path =  'oc-content/uploads/osp/evidence/' . $filename;
        ModelOSP::newInstance()->updateBankTransferEvidence($transaction_id,  $image_path);
        osc_add_flash_ok_message(__('Evidence image uploaded successfully.', 'osclass_pay'));
    } else {
        osc_add_flash_error_message(__('Failed to save the file.', 'osclass_pay'));
    }
} else {
    osc_add_flash_error_message(__('No file was uploaded or an error occurred.', 'osclass_pay'));
}

// Redirect back to the base URL
header('Location: ' . osc_base_url());
exit;
?>