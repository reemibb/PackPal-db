<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "final_asp");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Test 1: Check if ratings table exists
$tablesCheck = $conn->query("SHOW TABLES LIKE 'ratings'");
$tableExists = $tablesCheck->num_rows > 0;

// Test 2: If table exists, count rows
$ratingsCount = 0;
if ($tableExists) {
    $countQuery = $conn->query("SELECT COUNT(*) as total FROM ratings");
    if ($countQuery) {
        $countRow = $countQuery->fetch_assoc();
        $ratingsCount = $countRow['total'];
    }
}

// Test 3: Check table structure
$tableStructure = [];
if ($tableExists) {
    $structure = $conn->query("DESCRIBE ratings");
    while ($row = $structure->fetch_assoc()) {
        $tableStructure[] = $row;
    }
}

// Test 4: Try to get sample data
$sampleData = [];
if ($tableExists && $ratingsCount > 0) {
    $sampleQuery = $conn->query("SELECT * FROM ratings LIMIT 3");
    while ($row = $sampleQuery->fetch_assoc()) {
        $sampleData[] = $row;
    }
}

// Test 5: Try the exact query used in get_ratings.php
$joinQuery = "SELECT r.id, r.rating, r.feedback, r.user_id, r.created_at, 
              u.firstname, u.lastname 
              FROM ratings r 
              LEFT JOIN users u ON r.user_id = u.id 
              ORDER BY r.id DESC 
              LIMIT 2";
              
$joinResult = $conn->query($joinQuery);
$joinData = [];

if ($joinResult) {
    while ($row = $joinResult->fetch_assoc()) {
        $joinData[] = $row;
    }
}

// Compile test results
$results = [
    "status" => "success",
    "tests" => [
        "table_exists" => $tableExists,
        "ratings_count" => $ratingsCount,
        "table_structure" => $tableStructure,
        "sample_data" => $sampleData,
        "join_query" => [
            "query" => $joinQuery,
            "results" => $joinData
        ]
    ]
];

echo json_encode($results, JSON_PRETTY_PRINT);

$conn->close();
?>