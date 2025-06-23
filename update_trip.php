<?php
require_once 'db_connect.php';

// Get PUT data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['id']) || !isset($data['title']) || !isset($data['destination']) || 
    !isset($data['startDate']) || !isset($data['endDate'])) {
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Escape inputs
$trip_id = $conn->real_escape_string($data['id']);
$title = $conn->real_escape_string($data['title']);
$destination = $conn->real_escape_string($data['destination']);
$start_date = $conn->real_escape_string($data['startDate']);
$end_date = $conn->real_escape_string($data['endDate']);

// Update the trip
$sql = "UPDATE trips SET title = '$title', destination = '$destination', 
        start_date = '$start_date', end_date = '$end_date' WHERE id = '$trip_id'";

if ($conn->query($sql)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Trip updated successfully',
        'id' => (int)$trip_id,
        'title' => $title,
        'destination' => $destination,
        'startDate' => $start_date,
        'endDate' => $end_date
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error updating trip: ' . $conn->error
    ]);
}

$conn->close();
?>