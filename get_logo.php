<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "final_asp");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

$result = $conn->query("SELECT url FROM images WHERE name = 'logo' LIMIT 1");

if ($row = $result->fetch_assoc()) {
    echo json_encode(["url" => $row['url']]);
} else {
    echo json_encode(["url" => null]);
}

$conn->close();
?>
