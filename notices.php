<?php
session_start();
include 'dbConnection.php';

$username = $_SESSION['user_id'] ?? 'funi@gmail.com';

if (isset($_GET['mark_read'])) {
    $notice_id = $_GET['mark_read'];
    $sql1 = "UPDATE notices SET is_read = 1 WHERE notice_id = ? AND username = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("is", $notice_id, $username);
    $stmt1->execute();
    $stmt1->close();

    header("Location: notifications.php");
    exit;

}

if (isset($_GET['mark_all_read'])) {
    $sql = "UPDATE notices SET is_read = 1 WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();

    header("Location: notifications.php");
    exit;

}

// Unread count (unchanged)
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

// Fetch notices — now including timestamp, category, status, author
$notices = [];
$sql2 = "SELECT n.notice_id, n.title, n.content, n.notif_type, n.ward_id, wc.username, n.is_read, n.is_dismissed,n.created_at,
         sc.category_name as category ,ac.name AS councillor_name, ac.surname AS councillor_surname
         FROM notices n 
         LEFT JOIN service_categories sc on n.category_id = sc.category_id
         LEFT JOIN wards w ON n.ward_id = w.ward_id
         LEFT JOIN ward_councillors wc ON n.ward_id = wc.ward_id
         LEFT JOIN accounts ac on ac.username =wc.username
         WHERE n.username = ? AND is_dismissed = 0
         ORDER BY n.created_at DESC";
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

// Split into Today / Yesterday / Earlier
$today_notices = [];
$yesterday_notices = [];
$earlier_notices = [];

$today_str = date('Y-m-d');
$yesterday_str = date('Y-m-d', strtotime('-1 day'));

foreach ($notices as $n) {
    $noticeDate = date('Y-m-d', strtotime($n['created_at']));
    if ($noticeDate === $today_str) {
        $today_notices[] = $n;
    } elseif ($noticeDate === $yesterday_str) {
        $yesterday_notices[] = $n;
    } else {
        $earlier_notices[] = $n;
    }
}

// "12 min ago" / "1 hour ago" / date fallback
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

// Icon per category — match keys to your actual category values
function get_notice_icon($category) {
    $icons = [
        'water'       => 'water_drop',
        'electricity' => 'bolt',
        'roads'       => 'construction',
        'waste'       => 'delete',
        'general'     => 'info',
    ];
    return $icons[$category] ?? 'notifications';
}

// Outputs one <article class="notif-card"> for a given row
function render_notice_card($n) {
    $unreadClass = $n['is_read'] == 0 ? 'unread' : '';
    $icon = get_notice_icon($n['category']);
    $time = format_notice_time($n['created_at']);
    $councillorFullName = trim(($n['councillor_name'] ?? '') . ' ' . ($n['councillor_surname'] ?? ''));
    ?>
    <article class="notif-card <?php echo $unreadClass; ?>"
             data-notification-id="<?php echo $n['notice_id']; ?>"
             data-notif-type="<?php echo htmlspecialchars($n['notif_type']); ?>"
             data-category="<?php echo htmlspecialchars($n['category']); ?>"
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
                <?php if (!empty($n['username'])): ?>
                    <span class="notif-separator">·</span>
                    <span class="notif-author"><?php echo htmlspecialchars($councillorFullName); ?></span>
                <?php endif; ?>
            </footer>
        </div>

        <button class="dismiss-btn" type="button" aria-label="Dismiss notification">
            <span class="material-symbols-outlined">close</span>
        </button>
    </article>
    <?php
}