<?php
function get_global_unread_count($conn) {
    $unread_count = 0;
    if (isset($_SESSION['username']) && $conn) {
        $user = $_SESSION['username'];
        $badge_sql = "SELECT COUNT(*) AS cnt FROM notice_status WHERE username = ? AND is_read = 0";
        $badge_stmt = $conn->prepare($badge_sql);
        if ($badge_stmt) {
            $badge_stmt->bind_param("s", $user);
            $badge_stmt->execute();
            $badge_res = $badge_stmt->get_result();
            if ($badge_row = $badge_res->fetch_assoc()) {
                $unread_count = $badge_row['cnt'];
            }
            $badge_stmt->close();
        }
    }
    return $unread_count;
}
?>