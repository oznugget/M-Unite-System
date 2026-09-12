<?php
include 'dbConnection.php';

// Fetch all active, non-expired general (town-wide) notices.
// No username, no notice_status join — this page has no per-user state.
$public_notices = [];
$event_notices=[];
$current_issues=[];

$sql = "SELECT n.notice_id, n.title, n.content, n.notif_type, n.ward_id,
               n.is_alert, n.expires_at, n.resolved_at, n.created_at,
               sc.category_name AS category,
               ac.name AS councillor_name, ac.surname AS councillor_surname
        FROM notices n
        LEFT JOIN service_categories sc ON n.category_id = sc.category_id
        LEFT JOIN ward_councillors wc ON n.ward_id = wc.ward_id
        LEFT JOIN accounts ac ON ac.username = wc.username
        WHERE (n.expires_at IS NULL OR n.expires_at > NOW())
        ORDER BY n.created_at DESC";
        
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
} else {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Categorize into the appropriate arrays based on notif_type or category
        $type = strtolower($row['notif_type']);
        
        if ($type === 'general') {
            $public_notices[] = $row;  //fetch only general notices
        } elseif ($type === 'events') {
            $event_notices[] = $row;  //fetch only evemt nnotices
        } elseif ($type === 'current_issues') {
            $current_issues[] = $row;  //fetch pnly current issue notices
        }
    }
    $stmt->close();
}

// Partition into Today / Yesterday / Earlier buckets
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

$public_notices_grouped = partition_by_date($public_notices);

//works out the time
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

//dislays icons in relation to the category type (Need to add more later!)
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

// Public card has no unread statte, no dismiss button, not clickable-to-mark-read
function render_public_notice_card($n) {
    $icon = get_notice_icon($n['category']);
    $time = format_notice_time($n['created_at']);
    $councillorFullName = trim(($n['councillor_name'] ?? '') . ' ' . ($n['councillor_surname'] ?? ''));
    $isAlert = !empty($n['is_alert']) && $n['is_alert'] == 1;
    $alertClass = $isAlert ? 'alert-card' : '';

    // Text truncation logic
    $fullContent = $n['content'];
    $maxLength = 150;
    $isLongText = mb_strlen($fullContent) > $maxLength;
    $displayContent = $isLongText ? mb_substr($fullContent, 0, $maxLength) . '...' : $fullContent;
    ?>
    <article class="notif-card <?php echo $alertClass; ?>"
             id="notice-<?php echo $n['notice_id']; ?>"
             data-notification-id="<?php echo $n['notice_id']; ?>"
             data-category="<?php echo htmlspecialchars($n['category']); ?>"
             data-full-title="<?php echo htmlspecialchars($n['title']); ?>"
             data-full-content="<?php echo htmlspecialchars($fullContent); ?>"
             data-time="<?php echo htmlspecialchars($time); ?>"
             data-icon="<?php echo htmlspecialchars($icon); ?>"
             data-author="<?php echo htmlspecialchars($councillorFullName); ?>">

        <div class="notif-icon">
            <span class="material-symbols-outlined"><?php echo $icon; ?></span>
        </div>

        <div class="notif-content">
            <header class="notif-header">
                <div class="notif-title-row">
                    <h3 class="notif-title"><?php echo htmlspecialchars($n['title']); ?></h3>
                    <?php if ($isAlert): ?>
                        <span class="alert-badge">Alert</span>
                    <?php endif; ?>
                </div>
                <span class="time"><?php echo $time; ?></span>
            </header>

            <p class="notif-msg">
                <?php echo htmlspecialchars($displayContent); ?>
                <?php if ($isLongText): ?>
                    <button type="button" class="read-more-btn" onclick="openNoticeModal(this.closest('.notif-card'));">Read more</button>
                <?php endif; ?>
            </p>

            <footer class="notif-footer">
                <span class="notif-category"><?php echo htmlspecialchars(ucfirst($n['category'])); ?></span>
                <?php if (!empty($councillorFullName)): ?>
                    <span class="notif-separator">·</span>
                    <span class="notif-author"><?php echo htmlspecialchars($councillorFullName); ?></span>
                <?php endif; ?>
            </footer>
        </div>
    </article>
    <?php
}