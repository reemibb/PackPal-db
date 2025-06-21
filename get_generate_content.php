<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "final_asp");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$sql = "SELECT name, content FROM generate_content";
$result = $conn->query($sql);

$title = '';
$types = [];
$activities = [];
$packs = [];

while ($row = $result->fetch_assoc()) {
    if ($row['name'] === 'title') {
        $title = $row['content'];
    } elseif (str_starts_with($row['name'], 'type')) {
        $types[] = $row['content'];
    } elseif (str_starts_with($row['name'], 'activity')) {
        $activities[] = $row['content'];
    } elseif (str_starts_with($row['name'], 'pack')) {
        $packs[] = $row['content'];
    }
}

echo json_encode([
    "success" => true,
    "data" => [
        "title" => $title,
        "types" => $types,
        "activities" => $activities,
        "packs" => $packs
    ]
]);
$conn->close();
