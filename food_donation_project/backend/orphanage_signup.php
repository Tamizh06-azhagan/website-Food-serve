<?php
header("Content-Type: application/json");
include("db_connection.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $orphanage_name = $_POST["orphanage_name"] ?? '';
    $license_no = $_POST["license_no"] ?? '';
    $owner_name = $_POST["owner_name"] ?? '';
    $owner_age = $_POST["owner_age"] ?? '';
    $contact = $_POST["contact"] ?? '';
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';
    $location = $_POST["location"] ?? '';

    if (empty($orphanage_name) || empty($license_no) || empty($owner_name) || empty($owner_age) || empty($contact) || empty($email) || empty($password) || empty($location)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM orphanages WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered."]);
        $check->close();
        exit;
    }
    $check->close();

    // Handle orphanage photo upload
    $photoPath = null;
    if (isset($_FILES["orphanage_photo"]) && $_FILES["orphanage_photo"]["error"] === UPLOAD_ERR_OK) {
        $targetDir = "../uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = uniqid() . "_" . basename($_FILES["orphanage_photo"]["name"]);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["orphanage_photo"]["tmp_name"], $targetFile)) {
            $photoPath = "uploads/" . $fileName;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to upload photo."]);
            exit;
        }
    }

    // Insert new orphanage
    $stmt = $conn->prepare("INSERT INTO orphanages (orphanage_name, license_no, owner_name, owner_age, contact, email, password, location, orphanage_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $orphanage_name, $license_no, $owner_name, $owner_age, $contact, $email, $hashedPassword, $location, $photoPath);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Orphanage registered successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
