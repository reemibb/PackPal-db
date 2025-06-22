<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");


$host = "localhost";
$user = "root";
$pass = "";
$dbname = "final_asp";

$conn = new mysqli($host, $user, $pass, $dbname);


if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}


$data = json_decode(file_get_contents("php://input"), true);
$firstname = trim($data['firstname'] ?? '');
$lastname  = trim($data['lastname'] ?? '');
$email     = trim($data['email'] ?? '');
$password  = trim($data['password'] ?? '');


if (empty($email) || empty($password) || empty($firstname) || empty($lastname)) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}


$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email already registered"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();


$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $firstname, $lastname, $email, $hashed_password);

if ($stmt->execute()) {
    // Get the newly inserted user's ID
    $user_id = $conn->insert_id;
    
    // Return success with user information
    echo json_encode([
        "success" => true, 
        "message" => "User registered successfully",
        "user" => [
            "id" => $user_id,
            "firstname" => $firstname,
            "lastname" => $lastname,
            "email" => $email
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Registration failed"]);
}

$stmt->close();
$conn->close();
?>