<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (isset($_SESSION['username'])) {
    // Logged in: send to notifications page
    header("Location: notifications.php");
    exit;
} else {
    // Not logged in: send to login page
    header("Location: signin.php");
    exit;
}
?>