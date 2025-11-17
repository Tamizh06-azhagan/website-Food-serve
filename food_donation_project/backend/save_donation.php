<?php
header("Content-Type: application/json");
include("db_connection.php");

// Decode JSON body if sent
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Check if JSON received (from fetch) or fallback to POST
$food_name = $data["foodName"] ?? $_POST["food_name"] ?? '';
$quantity = $data["quantity"] ?? $_POST["quantity"] ?? '';
$location = $data["location"] ?? $_POST["location"] ?? '';
$contact = $data["contact"] ?? $_POST["contact"] ?? '';
$prepared_time = $data["preparedTime"] ?? $_POST["prepared_time"] ?? '';
$donor_id = $data["donor_id"] ?? $_POST["donor_id"] ?? null;

if (empty($food_name) || empty($quantity) || empty($location) || empty($contact)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO donations (donor_id, food_name, quantity, location, contact, prepared_time, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("isssss", $donor_id, $food_name, $quantity, $location, $contact, $prepared_time);

if ($stmt->execute()) {
    $donation_id = $conn->insert_id;
    echo json_encode([
        "status" => "success",
        "message" => "Donation saved successfully.",
        "donation_id" => $donation_id
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
