<?php
require_once 'db_connect.php';

// Get PUT data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['id']) || !isset($data['day']) || !isset($data['time']) || !isset($data['activity'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Escape inputs
$item_id = $conn->real_escape_string($data['id']);
$day = $conn->real_escape_string($data['day']);
$time = $conn->real_escape_string($data['time']);
$activity = $conn->real_escape_string($data['activity']);
$location = isset($data['location']) ? $conn->real_escape_string($data['location']) : '';
$notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
$completed = isset($data['completed']) ? ($data['completed'] ? 1 : 0) : 0;

// Update the itinerary item
$sql = "UPDATE itinerary_items SET day = '$day', time = '$time', activity = '$activity', 
        location = '$location', notes = '$notes', completed = '$completed' WHERE id = '$item_id'";

if ($conn->query($sql)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Itinerary item updated successfully',
        'id' => (int)$item_id,
        'trip_id' => (int)$data['trip_id'],
        'day' => (int)$day,
        'time' => $time,
        'activity' => $activity,
        'location' => $location,
        'notes' => $notes,
        'completed' => (bool)$completed
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error updating itinerary item: ' . $conn->error
    ]);
}

$conn->close();
?>