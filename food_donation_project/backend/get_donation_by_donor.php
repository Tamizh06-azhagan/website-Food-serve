<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require "db_connection.php";

$donor_id = $_GET['donor_id'] ?? null;

if (!$donor_id) {
    echo json_encode([
        "status" => "error",
        "message" => "donor_id missing"
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, food_name, quantity, location, contact, prepared_time, status, accepted_by 
    FROM donations 
    WHERE donor_id = ?
    ORDER BY id DESC
");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "donations" => $data   // ✅ FIXED KEY NAME
]);
?>
