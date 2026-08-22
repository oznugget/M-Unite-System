<?php
    session_start();
    include 'dbConnection.php';

    // TEMPORARY fallback until login/sessions are built.
    $username = $_SESSION['user_id'] ?? 'funi@gmail.com';

    //Implemeting unread notices
    $unread_count = 0;

    $sql = "SELECT COUNT(*) AS unread_count FROM notices WHERE username = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
    } else {
        $stmt->bind_param("s", $username); 
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $unread_count = $row['unread_count'];
        $stmt->close();
    }

    //implementing  filteiring tabs
   
    $notices = [];
    $sql2 = "SELECT notice_id, title, content, notif_type, is_read, is_dismissed FROM notices WHERE username = ? AND is_dismissed = 0
            ORDER BY timestamp DESC";

    $stmt2 = $conn->prepare($sql2);
    if ($stmt2 === false) {
        error_log("Prepare failed: " . $conn->error);
    } else {
        $stmt2->bind_param("s", $username);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        while ($row = $result2->fetch_assoc()) {
            $notices[] = $row;
        }
        $stmt2->close();
    }



?>