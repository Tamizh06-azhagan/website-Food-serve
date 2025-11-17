<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require "db_connection.php";

$query = "SELECT 
            id, 
            food_name, 
            quantity, 
            location, 
            contact, 
            DATE_FORMAT(prepared_time, '%Y-%m-%dT%H:%i:%s') AS prepared_time,
            status, 
            accepted_by 
          FROM donations 
          ORDER BY id DESC";

$result = $conn->query($query);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);
?>
