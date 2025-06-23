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

// Get trip details
$sql = "SELECT * FROM trips WHERE id = '$trip_id'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Trip not found'
    ]);
    exit;
}

$trip = $result->fetch_assoc();

// Get itinerary items for this trip
$itinerary_sql = "SELECT * FROM itinerary_items WHERE trip_id = '$trip_id' ORDER BY day, time";
$itinerary_result = $conn->query($itinerary_sql);

$itinerary_items = [];
if ($itinerary_result->num_rows > 0) {
    while ($item = $itinerary_result->fetch_assoc()) {
        $itinerary_items[] = [
            'id' => (int)$item['id'],
            'trip_id' => (int)$item['trip_id'],
            'day' => (int)$item['day'],
            'time' => $item['time'],
            'activity' => $item['activity'],
            'location' => $item['location'],
            'notes' => $item['notes'],
            'completed' => (bool)$item['completed']
        ];
    }
}

echo json_encode([
    'id' => (int)$trip['id'],
    'user_id' => (int)$trip['user_id'],
    'title' => $trip['title'],
    'destination' => $trip['destination'],
    'startDate' => $trip['start_date'],
    'endDate' => $trip['end_date'],
    'itinerary' => $itinerary_items
]);

$conn->close();
?>