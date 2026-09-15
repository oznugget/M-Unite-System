<?php


require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/categories.php';
require_once __DIR__ . '/require_councillor.php';

$isLoggedIn = true; // guaranteed by the guard
$firstname  = htmlspecialchars($_SESSION['firstname'] ?? '');
$ward_id    = $_SESSION['ward_id'] ?? null;

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT 
                            ticket_id, title, description,
                            category_id, ward_id,
                            current_status, username, date_created
                         FROM tickets WHERE ticket_id = ?");
$stmt->bind_param('i', $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    http_response_code(404);
    echo "Ticket not found.";
    exit;
}

// Restrict to the logged-in councillor's own ward
$session_ward = $_SESSION['ward_id'] ?? null;
if ($session_ward === null || (int)$ticket['ward_id'] !== (int)$session_ward) {
    http_response_code(403);
    echo "You don't have access to this ticket.";
    exit;
}

// Reports already linked to this ticket — report_id is each report's own
// unique id, distinct from ticket_id (the parent ticket this page is for).
// Split into active vs. completed (Resolved/Closed) so completed ones can
// be shown in their own section at the bottom.
$stmt = $conn->prepare("SELECT 
                            report_id, category_id, description, image_url,
                            CONCAT(street_number, ' ', street_name, ', ', surburb) AS address,
                            current_status, timestamp
                         FROM reports WHERE ticket_id = ? ORDER BY timestamp DESC");
$stmt->bind_param('i', $ticket_id);
$stmt->execute();
$all_linked = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$active_linked = array_filter($all_linked, fn($r) => !in_array($r['current_status'], ['Resolved', 'Closed'], true));
$completed_linked = array_filter($all_linked, fn($r) => in_array($r['current_status'], ['Resolved', 'Closed'], true));

// For the "add reports" panel: unassigned reports, same ward, defaulting to same category
$type_filter = $ticket['category_id'];

if ($type_filter !== null) {
    $stmt2 = $conn->prepare("SELECT 
                                report_id, category_id, description, image_url,
                                CONCAT(street_number, ' ', street_name, ', ', surburb) AS address,
                                timestamp
                              FROM reports 
                              WHERE ticket_id IS NULL AND category_id = ? AND ward_id = ?
                                AND current_status NOT IN ('Resolved', 'Closed')
                              ORDER BY timestamp DESC");
    $stmt2->bind_param('ii', $type_filter, $session_ward);
} else {
    $stmt2 = $conn->prepare("SELECT 
                                report_id, category_id, description, image_url,
                                CONCAT(street_number, ' ', street_name, ', ', surburb) AS address,
                                timestamp
                              FROM reports 
                              WHERE ticket_id IS NULL AND ward_id = ?
                                AND current_status NOT IN ('Resolved', 'Closed')
                              ORDER BY timestamp DESC");
    $stmt2->bind_param('i', $session_ward);
}
$stmt2->execute();
$candidate_reports = $stmt2->get_result();

// Comments already posted on this ticket
$stmt3 = $conn->prepare("SELECT comment_id, username, comment_text, created_at
                          FROM comments WHERE ticket_id = ? ORDER BY created_at DESC");
$stmt3->bind_param('i', $ticket_id);
$stmt3->execute();
$comments = $stmt3->get_result();

$current_username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($ticket['title']) ?> — Ticket #<?= $ticket['ticket_id'] ?></title>
<link rel="stylesheet" href="tickets.css">
<link rel="stylesheet" href="header_footer.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&family=TikTok+Sans:opsz,wght@12..36,300..900&display=swap" rel="stylesheet">
</head>
<body>

<header class="site-header">

    <div class="logo-box">
      <img src="images/logo_1.png" alt="M-Unite Logo" class="logo-image">
      <a href="ward_councillor_home.php" class="logo-link"></a>
    </div>

    <nav class="navbar">
      <a href="ward_councillor_home.php" class="nav-item">Dashboard</a>
      <a href="reports.php" class="nav-item">Incoming Reports</a>
      <a href="WC_make_reports.php" class="nav-item">Make Report</a>
      <a href="tickets.php" class="nav-item-active">Tickets</a>
      <a href="public_notices.php" class="nav-item">Notices</a>
      <a href="map.php" class="nav-item">Map</a>
    </nav>

    
           
        <div class="header-right" id="header-right">
      <?php if ($isLoggedIn): ?>
        <a href="account.php" class="sign-in-btn">
          <?php echo $firstname ?> <i class="fa-regular fa-circle-user"></i>
        </a>
      <?php else: ?>
        <a href="signin.php" class="sign-in-btn">
          Sign In <i class="fa-regular fa-circle-user"></i>
        </a>
      <?php endif; ?>
            
        </div>
    </header>

<div class="ticket-detail">
    <div class="ticket-detail-header">
        <div class="status-row">
            <span class="badge badge-<?= strtolower(str_replace(' ', '-', $ticket['current_status'])) ?>" id="current-status-badge"><?= htmlspecialchars($ticket['current_status']) ?></span>
            <div class="status-control">
                <select id="status-select">
                    <?php foreach (['Pending', 'In Progress', 'Resolved', 'Closed'] as $status): ?>
                        <option value="<?= $status ?>" <?= $status === $ticket['current_status'] ? 'selected' : '' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
                <button id="update-status-btn" class="btn-primary" disabled>Update Status</button>
            </div>
        </div>
        <h2><?= htmlspecialchars($ticket['title']) ?></h2>
        <p><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
        <div class="ticket-meta">
            <span>Type: <span class="report-type <?= category_class($ticket['category_id']) ?>"><?= htmlspecialchars(category_name($ticket['category_id'])) ?></span></span>
            <span>Created: <?= $ticket['date_created'] ? date('d M Y, H:i', strtotime($ticket['date_created'])) : '—' ?></span>
        </div>
    </div>

    <h3>Linked Reports (<?= count($active_linked) ?>)</h3>
    <div class="report-list" id="linked-report-list">
        <?php if (count($active_linked) > 0): ?>
            <?php foreach ($active_linked as $row): ?>
                <div class="report-row no-checkbox"
                     data-id="<?= $row['report_id'] ?>"
                     data-type="<?= htmlspecialchars($row['category_id']) ?>"
                     data-status="<?= htmlspecialchars($row['current_status']) ?>"
                     data-address="<?= htmlspecialchars($row['address']) ?>"
                     data-time="<?= date('d M Y, H:i', strtotime($row['timestamp'])) ?>"
                     data-image="<?= htmlspecialchars($row['image_url'] ?? '') ?>">
                    <?php if (!empty($row['image_url'])): ?>
                        <img class="report-thumb" src="<?= htmlspecialchars($row['image_url']) ?>" alt="Report photo">
                    <?php else: ?>
                        <span class="report-thumb report-thumb-empty" aria-hidden="true"></span>
                    <?php endif; ?>
                    <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['current_status'])) ?>"><?= htmlspecialchars($row['current_status']) ?></span>
                    <span class="report-type <?= category_class($row['category_id']) ?>"><?= htmlspecialchars(category_name($row['category_id'])) ?></span>
                    <span class="report-desc" title="<?= htmlspecialchars($row['description']) ?>"><?= htmlspecialchars(mb_strimwidth($row['description'], 0, 70, '…')) ?></span>
                    <span class="report-address"><?= htmlspecialchars($row['address']) ?></span>
                    <span class="report-time"><?= date('d M, H:i', strtotime($row['timestamp'])) ?></span>
                    <span class="report-id">#<?= $row['report_id'] ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-state">No active reports linked yet.</p>
        <?php endif; ?>
    </div>

    <?php if (count($completed_linked) > 0): ?>
        <h3 class="section-heading">Completed Reports (<?= count($completed_linked) ?>)</h3>
        <div class="report-list completed-section" id="completed-linked-report-list">
            <?php foreach ($completed_linked as $row): ?>
                <div class="report-row no-checkbox"
                     data-id="<?= $row['report_id'] ?>"
                     data-type="<?= htmlspecialchars($row['category_id']) ?>"
                     data-status="<?= htmlspecialchars($row['current_status']) ?>"
                     data-address="<?= htmlspecialchars($row['address']) ?>"
                     data-time="<?= date('d M Y, H:i', strtotime($row['timestamp'])) ?>"
                     data-image="<?= htmlspecialchars($row['image_url'] ?? '') ?>">
                    <?php if (!empty($row['image_url'])): ?>
                        <img class="report-thumb" src="<?= htmlspecialchars($row['image_url']) ?>" alt="Report photo">
                    <?php else: ?>
                        <span class="report-thumb report-thumb-empty" aria-hidden="true"></span>
                    <?php endif; ?>
                    <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['current_status'])) ?>"><?= htmlspecialchars($row['current_status']) ?></span>
                    <span class="report-type <?= category_class($row['category_id']) ?>"><?= htmlspecialchars(category_name($row['category_id'])) ?></span>
                    <span class="report-desc" title="<?= htmlspecialchars($row['description']) ?>"><?= htmlspecialchars(mb_strimwidth($row['description'], 0, 70, '…')) ?></span>
                    <span class="report-address"><?= htmlspecialchars($row['address']) ?></span>
                    <span class="report-time"><?= date('d M, H:i', strtotime($row['timestamp'])) ?></span>
                    <span class="report-id">#<?= $row['report_id'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <button id="add-reports-btn" class="btn-primary">+ Add Reports to This Ticket</button>

    <div id="add-reports-panel" class="add-reports-panel hidden">
        <h3>Unassigned reports<?= $type_filter !== null ? ' — ' . htmlspecialchars(category_name($type_filter)) : '' ?></h3>
        <div class="report-list" id="candidate-report-list">
            <?php if ($candidate_reports->num_rows > 0): ?>
                <?php while ($row = $candidate_reports->fetch_assoc()): ?>
                    <div class="report-row candidate-row"
                         data-id="<?= $row['report_id'] ?>"
                         data-type="<?= htmlspecialchars($row['category_id']) ?>"
                         data-status=""
                         data-address="<?= htmlspecialchars($row['address']) ?>"
                         data-time="<?= date('d M Y, H:i', strtotime($row['timestamp'])) ?>"
                         data-image="<?= htmlspecialchars($row['image_url'] ?? '') ?>">
                        <input type="checkbox" class="candidate-checkbox" value="<?= $row['report_id'] ?>">
                        <?php if (!empty($row['image_url'])): ?>
                            <img class="report-thumb" src="<?= htmlspecialchars($row['image_url']) ?>" alt="Report photo">
                        <?php else: ?>
                            <span class="report-thumb report-thumb-empty" aria-hidden="true"></span>
                        <?php endif; ?>
                        <span class="report-type <?= category_class($row['category_id']) ?>"><?= htmlspecialchars(category_name($row['category_id'])) ?></span>
                        <span class="report-desc" title="<?= htmlspecialchars($row['description']) ?>"><?= htmlspecialchars(mb_strimwidth($row['description'], 0, 70, '…')) ?></span>
                        <span class="report-address"><?= htmlspecialchars($row['address']) ?></span>
                        <span class="report-time"><?= date('d M, H:i', strtotime($row['timestamp'])) ?></span>
                        <span class="report-id">#<?= $row['report_id'] ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-state">No unassigned reports available.</p>
            <?php endif; ?>
        </div>
        <button id="confirm-add-btn" class="btn-primary" disabled>Add Selected Reports</button>
    </div>

    <h3>Comments (<?= $comments->num_rows ?>)</h3>
    <div class="comment-list" id="comment-list">
        <?php if ($comments->num_rows > 0): ?>
            <?php while ($row = $comments->fetch_assoc()): ?>
                <div class="comment-row">
                    <div class="comment-meta">
                        <span class="comment-username"><?= htmlspecialchars($row['username']) ?></span>
                        <span class="comment-time"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></span>
                    </div>
                    <p class="comment-text"><?= nl2br(htmlspecialchars($row['comment_text'])) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-state" id="comment-empty-state">No comments yet.</p>
        <?php endif; ?>
    </div>

    <form id="add-comment-form" class="add-comment-form">
        <textarea id="comment-text" name="comment_text" rows="3" placeholder="Add a comment..." required></textarea>
        <button id="comment-submit-btn" type="submit" class="btn-primary">Post Comment</button>
    </form>
</div>

<script>
const TICKET_ID = <?= $ticket_id ?>;
const CURRENT_STATUS = <?= json_encode($ticket['current_status']) ?>;
const CURRENT_USERNAME = <?= json_encode($current_username) ?>;
</script>
<script src="report-common.js"></script>
<script src="ticket_detail.js"></script>
</body>
</html>
