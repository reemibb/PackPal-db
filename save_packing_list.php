<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Set UTF-8 encoding for PHP
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

$conn = new mysqli("localhost", "root", "", "final_asp");

// Set charset to utf8mb4 for full Unicode support
if (!$conn->set_charset("utf8mb4")) {
    echo json_encode(["success" => false, "message" => "Error setting charset: " . $conn->error]);
    exit;
}

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection error"]);
    exit;
}

// Get raw input and ensure proper encoding
$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true);

// Check if JSON decode was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "Invalid JSON data"]);
    exit;
}

// Validate required fields
if (!isset($data['user_id']) || !isset($data['destination'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$user_id = intval($data['user_id']);
$destination = $data['destination'];
$start_date = $data['start_date'];
$end_date = $data['end_date'];
$trip_type = $data['trip_type'];

// Properly encode arrays with UTF-8 support
$activities = json_encode($data['activities'], JSON_UNESCAPED_UNICODE);
$packing_pref = $data['packing_pref'];
$weather = isset($data['weather']) ? json_encode($data['weather'], JSON_UNESCAPED_UNICODE) : null;
$items = json_encode($data['items'], JSON_UNESCAPED_UNICODE);

$stmt = $conn->prepare("REPLACE INTO packing_lists (user_id, destination, start_date, end_date, trip_type, activities, packing_pref, weather_details, packing_items) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("issssssss", $user_id, $destination, $start_date, $end_date, $trip_type, $activities, $packing_pref, $weather, $items);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Packing list saved successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Execute failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>