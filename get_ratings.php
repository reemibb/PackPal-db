<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "final_asp");

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}


$query = "SELECT r.id, r.rating, r.feedback, r.user_id,
          u.firstname, u.lastname 
          FROM ratings r 
          LEFT JOIN users u ON r.user_id = u.id 
          ORDER BY r.id DESC 
          LIMIT 2";

$result = $conn->query($query);

if (!$result) {
    echo json_encode(["error" => "Query failed: " . $conn->error]);
    exit;
}

$ratings = [];
$count = 0;

while ($row = $result->fetch_assoc()) {
    $count++;
    $ratings[] = [
        'id' => $row['id'],
        'rating' => $row['rating'],
        'feedback' => $row['feedback'] ?: "Great service!",  
        'user_id' => $row['user_id'],
        'firstname' => $row['firstname'] ?: "User",  
        'lastname' => $row['lastname'] ? substr($row['lastname'], 0, 1) . '.' : '',
    ];
}


$response = [
    'ratings' => $ratings,
    'debug' => [
        'count' => $count,
        'query' => $query,
        'success' => true
    ]
];


$tables = $conn->query("SHOW TABLES LIKE 'ratings'");
$tableExists = $tables->num_rows > 0;

if ($tableExists) {
    $countQuery = $conn->query("SELECT COUNT(*) as total FROM ratings");
    $countRow = $countQuery->fetch_assoc();
    $response['debug']['table_exists'] = true;
    $response['debug']['total_ratings'] = $countRow['total'];
} else {
    $response['debug']['table_exists'] = false;
}


echo json_encode($ratings);  

$conn->close();
?>