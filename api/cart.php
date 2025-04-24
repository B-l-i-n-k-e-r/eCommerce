<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) { // Changed to user_id for consistency
    echo json_encode(["error" => "Not authenticated"]);
    exit;
}

require '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'], $data['name'], $data['price'], $data['quantity']) && is_numeric($data['id']) && is_numeric($data['price']) && is_numeric($data['quantity']) && $data['quantity'] > 0) {
            $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, product_name, price, quantity)
            VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
            $stmt->execute([
                $_SESSION['user_id'], $data['id'], $data['name'], $data['price'], $data['quantity']
            ]);
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["error" => "Invalid cart item data."]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id']) && is_numeric($data['id'])) {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $data['id']]);
            echo json_encode(["success" => true, "deleted" => $stmt->rowCount()]); // Indicate if deletion occurred
        } else {
            echo json_encode(["error" => "Invalid product ID for removal."]);
        }
        break;

    case 'GET': // Optional: To get cart items (you might have a separate endpoint for this)
        $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $cartItems = $stmt->fetchAll();
        echo json_encode($cartItems);
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["error" => "Method not allowed."]);
        break;
}
?>