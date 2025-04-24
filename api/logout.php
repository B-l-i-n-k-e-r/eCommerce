<?php
session_start();
session_unset();
session_destroy();
header("Content-Type: application/json"); // Ensure JSON response
echo json_encode(["success" => true, "message" => "You have been logged out."]);
?>