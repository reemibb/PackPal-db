<?php
require_once 'db_connect.php';

// Get trip ID
$trip_id = isset($_GET['trip_id']) ? $conn->real_escape_string($_GET['trip_id']) : '';

if (empty($trip_id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Trip ID is required'
    ]);
    exit;
}

// Delete the trip (cascade will delete related itinerary items)
$sql = "DELETE FROM trips WHERE id = '$trip_id'";

if ($conn->query($sql)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Trip deleted successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error deleting trip: ' . $conn->error
    ]);
}

$conn->close();
?>