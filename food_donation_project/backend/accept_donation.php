<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require "db_connection.php";

$input = json_decode(file_get_contents("php://input"), true);
if (!$input) $input = $_POST;

$donation_id = intval($input["donation_id"] ?? 0);
$accepted_by = intval($input["accepted_by"] ?? 0);

if (!$donation_id || !$accepted_by) {
    echo json_encode(["status" => "error", "message" => "Missing donation_id or accepted_by"]);
    exit;
}

// Check if donation exists
$check = $conn->prepare("SELECT status FROM donations WHERE id = ?");
$check->bind_param("i", $donation_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Donation not found"]);
    exit;
}

$row = $res->fetch_assoc();

if ($row['status'] === "accepted") {
    echo json_encode(["status" => "error", "message" => "Already accepted"]);
    exit;
}

// Update status
$update = $conn->prepare("UPDATE donations SET status = 'accepted', accepted_by = ? WHERE id = ?");
$update->bind_param("ii", $accepted_by, $donation_id);

if ($update->execute()) {
    echo json_encode([
        "status" => "success",
        "donation_id" => $donation_id,
        "accepted_by" => $accepted_by
    ]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>
