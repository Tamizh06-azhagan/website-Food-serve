<?php
$host = "sql312.infinityfree.com";
$user = "if0_40434151";
$pass = "YOUR_VPANEL_PASSWORD";
$dbname = "if0_40434151_food_orphanage";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
