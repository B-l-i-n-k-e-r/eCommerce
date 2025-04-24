<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized. Please log in to view your orders.']);
    exit;
}

// DB connection
require_once('../config/db.php');

$user_id = $_SESSION['user_id'];

$sql = "SELECT order_id, order_date, total_amount, status
        FROM orders
        WHERE user_id = ?
        ORDER BY order_date DESC";

$stmt = $pdo->prepare($sql); // Using $pdo from db.php, not $conn
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

echo json_encode($orders);

// No need to close $stmt and $pdo here as $pdo is likely used in other scripts
// and will be closed when the script ends. If you have other database interactions
// in this script, ensure proper closing.
?>