<?php
session_start();
header("Content-Type: application/json");
// Only allow admin users
if (!isset($_SESSION['user_id']) || !($_SESSION['is_admin'] ?? 0)) {
    echo json_encode(["error" => "Unauthorized access."]);
    exit;
}
require_once("../config/db.php");

// Validate required fields
if (
    !isset($_POST['name'], $_POST['description'], $_POST['price']) ||
    empty($_FILES['image']['name'])
) {
    echo json_encode(["error" => "All fields are required including an image."]);
    exit;
}

$name = trim($_POST['name']);
$description = trim($_POST['description']);
$price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

if ($price === false) {
    echo json_encode(["error" => "Invalid price format."]);
    exit;
}
$price = floatval($price);

// Handle file upload
$targetDir = "../uploads/";
$originalName = basename($_FILES["image"]["name"]);
$imageName = time() . "_" . preg_replace("/[^a-zA-Z0-9.-]/", "", $originalName); // Added underscore for better readability
$targetFile = $targetDir . $imageName;
$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
$allowedTypes = ["jpg", "jpeg", "png", "gif"];
$maxFileSize = 2 * 1024 * 1024; // 2MB limit

if (!in_array($imageFileType, $allowedTypes)) {
    echo json_encode(["error" => "Invalid image type. Allowed: JPG, JPEG, PNG, GIF."]);
    exit;
}

if ($_FILES["image"]["size"] > $maxFileSize) {
    echo json_encode(["error" => "Image size exceeds the limit (2MB)."]);
    exit;
}

if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
    echo json_encode(["error" => "Failed to upload image."]);
    exit;
}

// Save product in DB
try {
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $description, $price, $imageName]);
    echo json_encode(["success" => "Product added successfully."]);
} catch (PDOException $e) {
    // Log the error for debugging (not for direct user output in production)
    error_log("Database error in add_product.php: " . $e->getMessage());
    echo json_encode(["error" => "Database error occurred while adding product."]);
}
?>