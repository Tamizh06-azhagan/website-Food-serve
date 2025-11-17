<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include("db_connection.php");

$input = json_decode(file_get_contents("php://input"), true);
if (!$input) { $input = $_POST; }

$email = $input["email"] ?? '';
$password = $input["password"] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Email and password are required."]);
    exit;
}

// Try Donor
$stmt = $conn->prepare("SELECT id, name, password FROM donors WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$donorResult = $stmt->get_result();
if ($donorResult && $donorResult->num_rows > 0) {
    $donor = $donorResult->fetch_assoc();
    if (password_verify($password, $donor["password"])) {
        echo json_encode([
            "status" => "success",
            "role" => "donor",
            "id" => $donor["id"],
            "message" => "Donor login successful",
            "redirect" => "donation.html"
        ]);
        exit;
    }
}

// Try Orphanage
$stmt = $conn->prepare("SELECT id, orphanage_name, password FROM orphanages WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$orphResult = $stmt->get_result();
if ($orphResult && $orphResult->num_rows > 0) {
    $orphanage = $orphResult->fetch_assoc();
    if (password_verify($password, $orphanage["password"])) {
        echo json_encode([
            "status" => "success",
            "role" => "orphanage",
            "id" => $orphanage["id"],
            "message" => "Orphanage login successful",
            "redirect" => "orphanage.html"
        ]);
        exit;
    }
}

// No Match
echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
$stmt->close();
$conn->close();
?>
