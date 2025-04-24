<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "logged_in" => true,
        "user_id" => $_SESSION['user_id'],
        "username" => $_SESSION['username'] ?? null, // Include username
        "is_admin" => $_SESSION['is_admin'] ?? 0
    ]);
} else {
    echo json_encode([
        "logged_in" => false
    ]);
}
?>