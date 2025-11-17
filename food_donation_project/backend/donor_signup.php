<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include("db_connection.php");

// Read raw JSON from fetch()
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON format"]);
    exit;
}

$name = $data["name"] ?? '';
$age = $data["age"] ?? '';
$contact = $data["contact"] ?? '';
$email = $data["email"] ?? '';
$password = $data["password"] ?? '';
$location = $data["location"] ?? '';

if (empty($name) || empty($age) || empty($contact) || empty($email) || empty($password) || empty($location)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Check if email already exists
$check = $conn->prepare("SELECT id FROM donors WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email already registered."]);
    $check->close();
    exit;
}

$check->close();

// Insert new donor
$stmt = $conn->prepare("INSERT INTO donors (name, age, contact, email, password, location) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sissss", $name, $age, $contact, $email, $hashedPassword, $location);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Donor registered successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
