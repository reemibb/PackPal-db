<?php
require_once 'db_connect.php';

// Get item ID
$item_id = isset($_GET['item_id']) ? $conn->real_escape_string($_GET['item_id']) : '';

if (empty($item_id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Item ID is required'
    ]);
    exit;
}

// Delete the itinerary item
$sql = "DELETE FROM itinerary_items WHERE id = '$item_id'";

if ($conn->query($sql)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Itinerary item deleted successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error deleting itinerary item: ' . $conn->error
    ]);
}

$conn->close();
?>