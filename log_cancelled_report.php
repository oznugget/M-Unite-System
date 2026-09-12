<?php

require 'db-connect.php';
require 'log_activity.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'brown@gmail.com';
$isAuthenticated = isset($_SESSION['username']) ? 1 : 0;

logActivity($pdo, $username, 'REPORT_DELETE', $isAuthenticated);

header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>