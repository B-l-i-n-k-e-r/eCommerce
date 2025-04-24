<?php
session_start(); // Start the session
if (!isset($_SESSION['user_id'])) { // Changed to user_id for consistency
    // Redirect to login page if not logged in
    header("Location: login.html");
    exit;
}

// Assuming you stored more user data in the session upon login
$username = $_SESSION['username'] ?? 'Guest'; // Get username, default to 'Guest'

// Display content for logged-in user
echo "Welcome, " . htmlspecialchars($username);
?>