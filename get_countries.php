<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "final_asp");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit;
}

$sql = "SELECT name FROM countries ORDER BY name ASC";
$result = $conn->query($sql);

$countries = [];
while ($row = $result->fetch_assoc()) {
    $countries[] = $row['name'];
}

echo json_encode([
    "success" => true,
    "data" => $countries
]);

$conn->close();
?>
