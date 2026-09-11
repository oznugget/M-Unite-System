<?php
// Shared alert-banner query.
// $username = null  -> public scope: only general (town-wide) alerts, no login required
// $username = 'x'   -> personal scope: general + the user's own ward alerts

function get_active_alerts($conn, $username = null) {
    $alerts = [];

    if ($username === null) {
        $sql = "SELECT n.notice_id, n.title, n.content, n.notif_type, n.created_at
                FROM notices n
                WHERE n.is_alert = 1
                  AND (n.expires_at IS NULL OR n.expires_at > NOW())
                  AND n.resolved_at IS NULL
                  AND n.notif_type = 'general'
                ORDER BY n.created_at DESC";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            return $alerts;
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }
        $stmt->close();
    } else {
        $sql = "SELECT n.notice_id, n.title, n.content, n.notif_type, n.created_at
                FROM notices n
                WHERE n.is_alert = 1
                  AND (n.expires_at IS NULL OR n.expires_at > NOW())
                  AND n.resolved_at IS NULL
                  AND (
                        n.notif_type = 'general'
                     OR (n.notif_type = 'ward' AND n.ward_id = (
                            SELECT cm.ward_id FROM community_member cm WHERE cm.username = ?
                        ))
                  )
                ORDER BY n.created_at DESC";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            return $alerts;
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }
        $stmt->close();
    }

    return $alerts;
}

function format_alert_time($timestamp) {
    $diff = time() - strtotime($timestamp);
    if ($diff < 60) return "Just now";
    if ($diff < 3600) return floor($diff / 60) . " min ago";
    if ($diff < 86400) {
        $hrs = floor($diff / 3600);
        return $hrs . " hour" . ($hrs > 1 ? "s" : "") . " ago";
    }
    return date('d F Y', strtotime($timestamp));
}