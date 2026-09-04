<?php
session_start();
include 'dbConnection.php';

$username = $_SESSION['user_id'] ?? 'funi@gmail.com';

// Change notices to read
if (isset($_GET['mark_read'])) {
    $notice_id = $_GET['mark_read'];
    $sql1 = "UPDATE notice_status SET is_read = 1 WHERE notice_id = ? AND username = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("is", $notice_id, $username);
    $stmt1->execute();
    $stmt1->close();

    header("Location: notifications.php");
    exit;
}

// Change all unread notices to read
if (isset($_GET['mark_all_read'])) {
    $sql = "UPDATE notice_status SET is_read = 1 WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();

    header("Location: notifications.php");
    exit;
}

// Dismissal of notices
if (isset($_GET['dismiss'])) {
    $notice_id = $_GET['dismiss'];
    $sql3 = "UPDATE notice_status SET is_dismissed = 1 WHERE notice_id = ? AND username = ?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("is", $notice_id, $username);
    $stmt3->execute();
    $stmt3->close();

    header("Location: notifications.php");
    exit;
}

// Display count of all unread notices
$unread_count = 0;
$sql = "SELECT COUNT(*) AS unread_count FROM notice_status WHERE username = ? AND is_read = 0";
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

// Fetch all applicable notices
$notices = [];
$sql2 = "SELECT n.notice_id, n.title, n.content, n.notif_type, n.ward_id, 
                n.is_alert, n.expires_at, n.resolved_at, n.created_at,
                wc.username AS councillor_username,
                COALESCE(ns.is_read, 0) AS is_read,
                COALESCE(ns.is_dismissed, 0) AS is_dismissed,
                sc.category_name AS category,
                ac.name AS councillor_name, ac.surname AS councillor_surname
         FROM notices n
         LEFT JOIN service_categories sc ON n.category_id = sc.category_id
         LEFT JOIN wards w ON n.ward_id = w.ward_id
         LEFT JOIN ward_councillors wc ON n.ward_id = wc.ward_id
         LEFT JOIN accounts ac ON ac.username = wc.username
         LEFT JOIN notice_status ns ON ns.notice_id = n.notice_id AND ns.username = ?
         WHERE (
                (n.notif_type = 'report' AND n.username = ?)
             OR (n.notif_type = 'ward' AND n.ward_id = (
                    SELECT cm.ward_id FROM community_member cm WHERE cm.username = ?
                ))
             OR (n.notif_type = 'general')
         )
         AND COALESCE(ns.is_dismissed, 0) = 0
         ORDER BY n.created_at DESC";

$stmt2 = $conn->prepare($sql2);
if ($stmt2 === false) {
    error_log("Prepare failed: " . $conn->error);
} else {
    $stmt2->bind_param("sss", $username, $username, $username);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    while ($row = $result2->fetch_assoc()) {
        $notices[] = $row;
    }
    $stmt2->close();
}

// Categorization Collections
$safety_alerts = [];
$personal_notices = [];
$townwide_notices = [];

foreach ($notices as $n) {
    if (!empty($n['is_alert']) && $n['is_alert'] == 1) {
        $safety_alerts[] = $n;
    } elseif ($n['notif_type'] === 'report') {
        $personal_notices[] = $n;
    } else {
        $townwide_notices[] = $n;
    }
}

// Helper to partition arrays by time buckets (Today / Yesterday / Earlier)
function partition_by_date($notice_list) {
    $today = [];
    $yesterday = [];
    $earlier = [];
    $today_str = date('Y-m-d');
    $yesterday_str = date('Y-m-d', strtotime('-1 day'));

    foreach ($notice_list as $n) {
        $noticeDate = date('Y-m-d', strtotime($n['created_at']));
        if ($noticeDate === $today_str) {
            $today[] = $n;
        } elseif ($noticeDate === $yesterday_str) {
            $yesterday[] = $n;
        } else {
            $earlier[] = $n;
        }
    }

    return [
        'today' => $today,
        'yesterday' => $yesterday,
        'earlier' => $earlier
    ];
}

$personal_grouped = partition_by_date($personal_notices);
$townwide_grouped = partition_by_date($townwide_notices);

function format_notice_time($timestamp) {
    $diff = time() - strtotime($timestamp);
    if ($diff < 60) return "Just now";
    if ($diff < 3600) return floor($diff / 60) . " min ago";
    if ($diff < 86400) {
        $hrs = floor($diff / 3600);
        return $hrs . " hour" . ($hrs > 1 ? "s" : "") . " ago";
    }
    return date('d F Y', strtotime($timestamp));
}

function get_notice_icon($category) {
    $icons = [
        'water'       => 'water_drop',
        'electricity' => 'bolt',
        'roads'       => 'construction',
        'waste'       => 'delete',
        'general'     => 'info',
    ];
    return $icons[strtolower($category)] ?? 'notifications';
}

function render_notice_card($n) {
    $unreadClass = $n['is_read'] == 0 ? 'unread' : '';
    $alertClass = !empty($n['is_alert']) && $n['is_alert'] == 1 ? 'alert-card' : '';
    $icon = get_notice_icon($n['category']);
    $time = format_notice_time($n['created_at']);
    $councillorFullName = trim(($n['councillor_name'] ?? '') . ' ' . ($n['councillor_surname'] ?? ''));
    ?>
    <article class="notif-card <?php echo $unreadClass . ' ' . $alertClass; ?>"
             data-notification-id="<?php echo $n['notice_id']; ?>"
             data-notif-type="<?php echo htmlspecialchars($n['notif_type']); ?>"
             data-category="<?php echo htmlspecialchars($n['category']); ?>"
             data-is-alert="<?php echo $n['is_alert'] ?? 0; ?>"
             onclick="window.location.href='notifications.php?mark_read=<?php echo $n['notice_id']; ?>'">

        <div class="notif-icon">
            <span class="material-symbols-outlined"><?php echo $icon; ?></span>
        </div>

        <div class="notif-content">
            <header class="notif-header">
                <div class="notif-title-row">
                    <?php if ($n['is_read'] == 0): ?>
                        <span class="unread-dot"></span>
                    <?php endif; ?>
                    <h3 class="notif-title"><?php echo htmlspecialchars($n['title']); ?></h3>
                </div>
                <span class="time"><?php echo $time; ?></span>
            </header>

            <p class="notif-msg"><?php echo htmlspecialchars($n['content']); ?></p>

            <footer class="notif-footer">
                <span class="notif-category"><?php echo htmlspecialchars(ucfirst($n['category'])); ?></span>
                <?php if (!empty($councillorFullName)): ?>
                    <span class="notif-separator">·</span>
                    <span class="notif-author"><?php echo htmlspecialchars($councillorFullName); ?></span>
                <?php endif; ?>
            </footer>
        </div>

        <button class="dismiss-btn" type="button" aria-label="Dismiss notification"
            onclick="event.stopPropagation(); window.location.href='notifications.php?dismiss=<?php echo $n['notice_id']; ?>'">
            <span class="material-symbols-outlined">close</span>
        </button>
    </article>
    <?php
}