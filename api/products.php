<?php
header('Content-Type: application/json');

require '../config/db.php';

try {
    $stmt = $pdo->prepare("SELECT id, name, price, image FROM products"); // Select relevant columns
    $stmt->execute();
    $products = $stmt->fetchAll();

    echo json_encode(["success" => true, "products" => $products]);

} catch (PDOException $e) {
    // Log the error
    error_log("Database error in products.php: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Failed to retrieve products."]);
}
?>