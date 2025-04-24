<?php
session_start();
header("Content-Type: application/json"); // Ensure JSON response

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not authenticated"]);
    exit;
}

require '../config/db.php';

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction(); // Start transaction for atomicity

    // Fetch cart items
    $cartStmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ?");
    $cartStmt->execute([$user_id]);
    $items = $cartStmt->fetchAll();

    if (!$items) {
        echo json_encode(["error" => "Cart is empty"]);
        $pdo->rollBack();
        exit;
    }

    // Calculate total
    $total = array_reduce($items, fn($sum, $item) => $sum + ($item['price'] * $item['quantity']), 0);

    // Insert order
    $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
    $orderStmt->execute([$user_id, $total]);
    $order_id = $pdo->lastInsertId();

    // Insert order items
    $orderItemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity)
                                  VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $orderItemStmt->execute([$order_id, $item['product_id'], $item['product_name'], $item['price'], $item['quantity']]);
    }

    // Clear the cart
    $clearCartStmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $clearCartStmt->execute([$user_id]);

    $pdo->commit(); // Commit transaction on success

    echo json_encode(["success" => true, "order_id" => $order_id]);

} catch (PDOException $e) {
    $pdo->rollBack(); // Rollback transaction on error
    // Log the error
    error_log("Database error in place_order.php: " . $e->getMessage());
    echo json_encode(["error" => "Failed to place order. Please try again."]);
}
?>