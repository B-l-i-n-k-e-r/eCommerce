<?php
require '../config/db.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate inputs
if (!isset($data['username'], $data['email'], $data['password'], $data['confirm_password'])) {
    echo json_encode(["success" => false, "error" => "All fields are required."]);
    exit;
}

$username = trim($data['username']);
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$password = trim($data['password']);
$confirmPassword = trim($data['confirm_password']);

if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
    echo json_encode(["success" => false, "error" => "Please fill in all fields."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "error" => "Invalid email format."]);
    exit;
}

if ($password !== $confirmPassword) {
    echo json_encode(["success" => false, "error" => "Passwords do not match."]);
    exit;
}

if (strlen($password) < 8) { // Example password strength check
    echo json_encode(["success" => false, "error" => "Password must be at least 8 characters long."]);
    exit;
}

// Hash password and insert into DB
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(["success" => false, "error" => "Email already registered."]);
        exit;
    }

    // Check if username already exists (optional)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(["success" => false, "error" => "Username already taken."]);
        exit;
    }

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword]);

    echo json_encode(["success" => "User registered successfully."]);

} catch (PDOException $e) {
    // Log the error
    error_log("Database error in signup.php: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Database error occurred during registration."]);
}
?>