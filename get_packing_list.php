<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=utf-8");


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$conn = new mysqli("localhost", "root", "", "final_asp");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed: " . $conn->connect_error]);
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid user ID: " . $user_id]);
    exit;
}


$sql = "SELECT id, packing_items, created_at 
        FROM packing_lists 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    echo json_encode([
        "success" => false,
        "message" => "No packing list found for user $user_id",
        "items" => [],
        "packing_list_id" => null
    ]);
    exit;
}

$packing_list_id = $row['id'];
$created_at = $row['created_at'];
$items = json_decode($row['packing_items'], true);


if (json_last_error() !== JSON_ERROR_NONE || !is_array($items)) {
    echo json_encode([
        "success" => false,
        "message" => "JSON decode error: " . json_last_error_msg(),
        "raw_data" => $row['packing_items']
    ]);
    exit;
}


$packed_status = [];
$checklist_sql = "SELECT item_name, is_checked FROM checklist WHERE user_id = ? AND packing_list_id = ?";
$checklist_stmt = $conn->prepare($checklist_sql);
$checklist_stmt->bind_param("ii", $user_id, $packing_list_id);
$checklist_stmt->execute();
$checklist_result = $checklist_stmt->get_result();

while ($check = $checklist_result->fetch_assoc()) {
    $packed_status[$check['item_name']] = $check['is_checked'] == 1;
}


echo json_encode([
    "success" => true,
    "items" => $items,
    "packed_status" => $packed_status,
    "packing_list_id" => $packing_list_id,
    "created_at" => $created_at
]);

$stmt->close();
$checklist_stmt->close();
$conn->close();
?>
