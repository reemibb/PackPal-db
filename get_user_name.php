<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");


$conn = new mysqli("localhost", "root", "", "final_asp");


if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}


if (!isset($_GET['user_id'])) {
    echo json_encode(["error" => "User ID is required"]);
    exit;
}

$user_id = intval($_GET['user_id']); 


$stmt = $conn->prepare("SELECT firstname FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    echo json_encode(["firstname" => $row['firstname']]);
} else {
    echo json_encode(["error" => "User not found"]);
}

$stmt->close();
$conn->close();
?>
