<?php
require_once 'db_connect.php';

// Get user ID
$user_id = isset($_GET['user_id']) ? $conn->real_escape_string($_GET['user_id']) : '';

if (empty($user_id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User ID is required'
    ]);
    exit;
}

// Get all trips for this user
$sql = "SELECT * FROM trips WHERE user_id = '$user_id' ORDER BY start_date DESC";
$result = $conn->query($sql);

$trips = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $trip_id = $row['id'];
        
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
        
        $trips[] = [
            'id' => (int)$row['id'],
            'user_id' => (int)$row['user_id'],
            'title' => $row['title'],
            'destination' => $row['destination'],
            'startDate' => $row['start_date'],
            'endDate' => $row['end_date'],
            'itinerary' => $itinerary_items
        ];
    }
}

echo json_encode([
    'status' => 'success',
    'trips' => $trips
]);

$conn->close();
?>