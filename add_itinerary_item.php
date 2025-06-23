<?php
require_once 'db_connect.php';

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['tripId']) || !isset($data['item']) || 
    !isset($data['item']['day']) || !isset($data['item']['time']) || !isset($data['item']['activity'])) {
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Escape inputs
$trip_id = $conn->real_escape_string($data['tripId']);
$day = $conn->real_escape_string($data['item']['day']);
$time = $conn->real_escape_string($data['item']['time']);
$activity = $conn->real_escape_string($data['item']['activity']);
$location = isset($data['item']['location']) ? $conn->real_escape_string($data['item']['location']) : '';
$notes = isset($data['item']['notes']) ? $conn->real_escape_string($data['item']['notes']) : '';

// Create the itinerary item
$sql = "INSERT INTO itinerary_items (trip_id, day, time, activity, location, notes) 
        VALUES ('$trip_id', '$day', '$time', '$activity', '$location', '$notes')";

if ($conn->query($sql)) {
    $item_id = $conn->insert_id;
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Itinerary item added successfully',
        'id' => (int)$item_id,
        'trip_id' => (int)$trip_id,
        'day' => (int)$day,
        'time' => $time,
        'activity' => $activity,
        'location' => $location,
        'notes' => $notes,
        'completed' => false
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error adding itinerary item: ' . $conn->error
    ]);
}

$conn->close();
?>