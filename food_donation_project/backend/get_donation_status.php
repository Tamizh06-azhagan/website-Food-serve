<?php
header("Content-Type: application/json");
include("db_connection.php");

$id = $_GET["id"] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "Missing donation ID"]);
    exit;
}

$sql = "SELECT accepted_by FROM donations WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["status" => "success", "accepted_by" => $row["accepted_by"]]);
} else {
    echo json_encode(["status" => "error", "message" => "Donation not found"]);
}

$stmt->close();
$conn->close();
?>
