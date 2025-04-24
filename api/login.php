<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db.php';
header("Content-Type: application/json"); // Ensure JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true); // Decode as associative array

    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');

    if (empty($email) || empty($password)) {
        echo json_encode(["error" => "Email and password are required."]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, username, password, is_admin FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username']; // Store username
        $_SESSION['is_admin'] = (int)($user['is_admin'] ?? 0); // Ensure it's an integer

        echo json_encode([
            "success" => "Login successful.",
            "user" => [ // Send back user details if needed
                "id" => $user['id'],
                "username" => $user['username'],
                "is_admin" => $_SESSION['is_admin']
            ]
        ]);
    } else {
        // Consider adding a slight delay here to mitigate brute-force attacks
        echo json_encode(["error" => "Invalid credentials."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
}
?>