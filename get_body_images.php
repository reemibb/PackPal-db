<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "final_asp");

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit;
}

$result = $conn->query("SELECT name, url FROM images WHERE name IN ('pack', 'reem', 'nour')");
$images = [];

while ($row = $result->fetch_assoc()) {
    $images[$row['name']] = $row['url'];
}

echo json_encode($images);
$conn->close();
?>
