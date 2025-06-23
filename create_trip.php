<?php
require_once 'db_connect.php';

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['user_id']) || !isset($data['title']) || !isset($data['destination']) || 
    !isset($data['startDate']) || !isset($data['endDate'])) {
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Escape inputs
$user_id = $conn->real_escape_string($data['user_id']);
$title = $conn->real_escape_string($data['title']);
$destination = $conn->real_escape_string($data['destination']);
$start_date = $conn->real_escape_string($data['startDate']);
$end_date = $conn->real_escape_string($data['endDate']);

// Create the trip
$sql = "INSERT INTO trips (user_id, title, destination, start_date, end_date) 
        VALUES ('$user_id', '$title', '$destination', '$start_date', '$end_date')";

if ($conn->query($sql)) {
    $trip_id = $conn->insert_id;
    
    $result = [
        'status' => 'success',
        'message' => 'Trip created successfully',
        'trip' => [
            'id' => $trip_id,
            'user_id' => $user_id,
            'title' => $title,
            'destination' => $destination,
            'startDate' => $start_date,
            'endDate' => $end_date,
            'itinerary' => []
        ]
    ];
    
    echo json_encode($result);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error creating trip: ' . $conn->error
    ]);
}

$conn->close();
?>