<?php
// Database connection details
define('DB_HOST', 'localhost');
define('DB_USER', 'u609444707_6txkv');
define('DB_PASSWORD', '[t5>:XctG');
define('DB_NAME', 'u609444707_NvDH4');
define('DB_TABLE_PREFIX', 'osxw_');

// Create a connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if phone number is provided
if (isset($_POST['phone'])) {
    $phone = trim($_POST['phone']);

    // Prepare the SQL query
    $stmt = $mysqli->prepare("SELECT s_email FROM " . DB_TABLE_PREFIX . "t_user WHERE s_phone_mobile = ?");
    $stmt->bind_param('s', $phone);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();

    if ($email) {
        echo json_encode(['email' => $email]);
    } else {
        echo json_encode(['email' => '']);
    }

    $stmt->close();
}

// Close the connection
$mysqli->close();
?>
