<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true);


if (!$data || !is_array($data)) {
    echo json_encode(["success" => false, "message" => "Invalid data received"]);
    exit;
}

if (empty($data)) {
    echo json_encode(["success" => false, "message" => "Empty data array"]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "final_asp");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}


$conn->begin_transaction();

try {
    $stmt = $conn->prepare("REPLACE INTO checklist (user_id, packing_list_id, item_name, is_checked) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $processed = 0;
    foreach ($data as $entry) {
        
        if (!isset($entry['user_id'], $entry['packing_list_id'], $entry['item_name'])) {
            error_log("Missing required fields in entry: " . json_encode($entry));
            continue;
        }

        $user_id = intval($entry['user_id']);
        $packing_list_id = intval($entry['packing_list_id']);
        $item_name = trim($entry['item_name']);
        $is_checked = isset($entry['is_checked']) ? ($entry['is_checked'] ? 1 : 0) : 0;

    
        if ($user_id <= 0 || $packing_list_id <= 0 || empty($item_name)) {
            error_log("Invalid entry values: user_id=$user_id, packing_list_id=$packing_list_id, item_name=$item_name");
            continue;
        }

        $stmt->bind_param("iisi", $user_id, $packing_list_id, $item_name, $is_checked);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed for item '$item_name': " . $stmt->error);
        }
        
        $processed++;
    }

    if ($processed === 0) {
        throw new Exception("No valid entries to process");
    }

    
    $conn->commit();
    echo json_encode([
        "success" => true, 
        "message" => "Checklist saved successfully",
        "processed_items" => $processed
    ]);

} catch (Exception $e) {
    
    $conn->rollback();
    echo json_encode([
        "success" => false, 
        "message" => $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>