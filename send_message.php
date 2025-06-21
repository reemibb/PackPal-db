<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=utf-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Log the start
file_put_contents('php_errors.log', date('Y-m-d H:i:s') . " - Script started\n", FILE_APPEND);

try {
    // Get and validate input data
    $raw_input = file_get_contents("php://input");
    file_put_contents('php_errors.log', date('Y-m-d H:i:s') . " - Raw input: " . $raw_input . "\n", FILE_APPEND);
    
    $data = json_decode($raw_input, true);
    
    if (!$data) {
        throw new Exception("Invalid JSON data received");
    }
    
    // Validate required fields
    $required_fields = ['user_id', 'name', 'email', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            throw new Exception("Missing or empty field: $field");
        }
    }
    
    $userId = intval($data["user_id"]);
    $name = trim($data["name"]);
    $email = trim($data["email"]);
    $subject = trim($data["subject"]);
    $message = trim($data["message"]);
    
    file_put_contents('php_errors.log', date('Y-m-d H:i:s') . " - Parsed data: userId=$userId, name=$name, email=$email\n", FILE_APPEND);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }
    
    // Validate user_id
    if ($userId <= 0) {
        throw new Exception("Invalid user ID");
    }
    
    // Connect to database
    $conn = new mysqli("localhost", "root", "", "final_asp");
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Check if user exists (optional - you might want to allow messages from non-users)
    $checkUser = $conn->prepare("SELECT id FROM users WHERE id = ?");
    if (!$checkUser) {
        throw new Exception("Prepare user check failed: " . $conn->error);
    }
    
    $checkUser->bind_param("i", $userId);
    $checkUser->execute();
    $userResult = $checkUser->get_result();
    
    if ($userResult->num_rows === 0) {
        file_put_contents('php_errors.log', date('Y-m-d H:i:s') . " - User ID $userId not found\n", FILE_APPEND);
        // You might want to comment this out if you allow messages from non-registered users
        // throw new Exception("User not found");
    }
    $checkUser->close();
    
    // Insert message
    $sql = "INSERT INTO messages (user_id, name, email, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare insert failed: " . $conn->error);
    }
    
    $stmt->bind_param("issss", $userId, $name, $email, $subject, $message);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $messageId = $conn->insert_id;
    file_put_contents('php_errors.log', date('Y-m-d H:i:s') . " - Message inserted successfully with ID: $messageId\n", FILE_APPEND);
    
    echo json_encode([
        "success" => true,
        "message" => "Message sent successfully!",
        "message_id" => $messageId
    ]);
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    file_put_contents('php_errors.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
    
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>