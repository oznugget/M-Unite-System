<?php
session_start();
require_once __DIR__ . '/db_connect.php';

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
$stmt = $conn->prepare("SELECT 
                            report_id, category_id, description,
                            CONCAT(street_number, ' ', street_name, ', ', surburb) AS address,
                            current_status, timestamp
                         FROM reports WHERE ticket_id = ? ORDER BY timestamp DESC");
$stmt->bind_param('i', $ticket_id);
$stmt->execute();
$linked_reports = $stmt->get_result();

// For the "add reports" panel: unassigned reports, same ward, defaulting to same category
$type_filter = $ticket['category_id'];

if ($type_filter !== null) {
    $stmt2 = $conn->prepare("SELECT 
                                report_id, category_id, description,
                                CONCAT(street_number, ' ', street_name, ', ', surburb) AS address,
                                timestamp
                              FROM reports 
                              WHERE ticket_id IS NULL AND category_id = ? AND ward_id = ?
                              ORDER BY timestamp DESC");
    $stmt2->bind_param('ii', $type_filter, $session_ward);
} else {
    $stmt2 = $conn->prepare("SELECT 
                                report_id, category_id, description,
                                CONCAT(street_number, ' ', street_name, ', ', surburb) AS address,
                                timestamp
                              FROM reports 
                              WHERE ticket_id IS NULL AND ward_id = ?
                              ORDER BY timestamp DESC");
    $stmt2->bind_param('i', $session_ward);
}
$stmt2->execute();
$candidate_reports = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($ticket['title']) ?> — Ticket #<?= $ticket['ticket_id'] ?></title>
<link rel="stylesheet" href="tickets.css">
</head>
<body>

<header class="topbar">
    <h1>Ticket #<?= $ticket['ticket_id'] ?></h1>
    <nav>
        <a href="reports.php">Reports</a>
        <a href="tickets.php" class="active">Tickets</a>
    </nav>
</header>

<div class="ticket-detail">
    <div class="ticket-detail-header">
        <span class="badge badge-<?= strtolower(str_replace(' ', '-', $ticket['current_status'])) ?>"><?= htmlspecialchars($ticket['current_status']) ?></span>
        <h2><?= htmlspecialchars($ticket['title']) ?></h2>
        <p><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
        <div class="ticket-meta">
            <span>Type: <?= htmlspecialchars($ticket['category_id'] ?? 'Mixed') ?></span>
            <span>Created: <?= $ticket['date_created'] ? date('d M Y, H:i', strtotime($ticket['date_created'])) : '—' ?></span>
        </div>
    </div>

    <h3>Linked Reports (<?= $linked_reports->num_rows ?>)</h3>
    <div class="report-list" id="linked-report-list">
        <?php while ($row = $linked_reports->fetch_assoc()): ?>
            <div class="report-row" data-id="<?= $row['report_id'] ?>">
                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['current_status'])) ?>"><?= htmlspecialchars($row['current_status']) ?></span>
                <span class="report-type"><?= htmlspecialchars($row['category_id']) ?></span>
                <span class="report-desc" title="<?= htmlspecialchars($row['description']) ?>"><?= htmlspecialchars(mb_strimwidth($row['description'], 0, 70, '…')) ?></span>
                <span class="report-address"><?= htmlspecialchars($row['address']) ?></span>
                <span class="report-time"><?= date('d M, H:i', strtotime($row['timestamp'])) ?></span>
                <span class="report-id">#<?= $row['report_id'] ?></span>
            </div>
        <?php endwhile; ?>
    </div>

    <button id="add-reports-btn" class="btn-primary">+ Add Reports to This Ticket</button>

    <div id="add-reports-panel" class="add-reports-panel hidden">
        <h3>Unassigned reports<?= $type_filter !== null ? ' — ' . htmlspecialchars($type_filter) : '' ?></h3>
        <div class="report-list" id="candidate-report-list">
            <?php if ($candidate_reports->num_rows > 0): ?>
                <?php while ($row = $candidate_reports->fetch_assoc()): ?>
                    <div class="report-row" data-id="<?= $row['report_id'] ?>">
                        <input type="checkbox" class="candidate-checkbox" value="<?= $row['report_id'] ?>">
                        <span class="report-type"><?= htmlspecialchars($row['category_id']) ?></span>
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
</div>

<script>
const TICKET_ID = <?= $ticket_id ?>;
</script>
<script src="ticket_detail.js"></script>
</body>
</html>
