<?php
    session_start();
    include 'db_connect.php';

    // Fallback to a test user ID until login is built
    $user_id = $_SESSION['user_id'] ?? 1; // TEMPORARY fallback

    $unread_count = 0;

    $sql = "SELECT COUNT(*) AS unread_count FROM notices WHERE user_id = ? AND is_read = 0";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $unread_count = $row['unread_count'];
        mysqli_stmt_close($stmt);
    }
?>