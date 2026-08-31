<?php
session_start();
require_once __DIR__ . '/db_connect.php';

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->bind_param('i', $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    http_response_code(404);
    echo "Ticket not found.";
    exit;
}

$stmt = $conn->prepare("SELECT id, fault_type, description, address, status, created_at
                         FROM reports WHERE ticket_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $ticket_id);
$stmt->execute();
$linked_reports = $stmt->get_result();

// For the "add reports" panel: unassigned reports, defaulting to same fault_type
$type_filter = $ticket['fault_type'];
$stmt2 = $conn->prepare("SELECT id, fault_type, description, address, created_at
                          FROM reports WHERE ticket_id IS NULL AND fault_type = ?
                          ORDER BY created_at DESC");
$stmt2->bind_param('s', $type_filter);
$stmt2->execute();
$candidate_reports = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($ticket['title']) ?> — Ticket #<?= $ticket['id'] ?></title>
<link rel="stylesheet" href="tickets.css">
</head>
<body>

<header class="topbar">
    <h1>Ticket #<?= $ticket['id'] ?></h1>
    <nav>
        <a href="reports.php">Reports</a>
        <a href="tickets.php" class="active">Tickets</a>
    </nav>
</header>

<div class="ticket-detail">
    <div class="ticket-detail-header">
        <span class="badge badge-<?= strtolower(str_replace(' ', '-', $ticket['status'])) ?>"><?= htmlspecialchars($ticket['status']) ?></span>
        <h2><?= htmlspecialchars($ticket['title']) ?></h2>
        <p><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
        <div class="ticket-meta">
            <span>Type: <?= htmlspecialchars($ticket['fault_type'] ?? 'Mixed') ?></span>
            <span>Created: <?= date('d M Y, H:i', strtotime($ticket['created_at'])) ?></span>
        </div>
    </div>

    <h3>Linked Reports (<?= $linked_reports->num_rows ?>)</h3>
    <div class="report-list" id="linked-report-list">
        <?php while ($row = $linked_reports->fetch_assoc()): ?>
            <div class="report-row" data-id="<?= $row['id'] ?>">
                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>"><?= htmlspecialchars($row['status']) ?></span>
                <span class="report-type"><?= htmlspecialchars($row['fault_type']) ?></span>
                <span class="report-desc" title="<?= htmlspecialchars($row['description']) ?>"><?= htmlspecialchars(mb_strimwidth($row['description'], 0, 70, '…')) ?></span>
                <span class="report-address"><?= htmlspecialchars($row['address']) ?></span>
                <span class="report-time"><?= date('d M, H:i', strtotime($row['created_at'])) ?></span>
                <span class="report-id">#<?= $row['id'] ?></span>
            </div>
        <?php endwhile; ?>
    </div>

    <button id="add-reports-btn" class="btn-primary">+ Add Reports to This Ticket</button>

    <div id="add-reports-panel" class="add-reports-panel hidden">
        <h3>Unassigned reports — <?= htmlspecialchars($type_filter ?? 'same type') ?></h3>
        <div class="report-list" id="candidate-report-list">
            <?php if ($candidate_reports->num_rows > 0): ?>
                <?php while ($row = $candidate_reports->fetch_assoc()): ?>
                    <div class="report-row" data-id="<?= $row['id'] ?>">
                        <input type="checkbox" class="candidate-checkbox" value="<?= $row['id'] ?>">
                        <span class="report-type"><?= htmlspecialchars($row['fault_type']) ?></span>
                        <span class="report-desc" title="<?= htmlspecialchars($row['description']) ?>"><?= htmlspecialchars(mb_strimwidth($row['description'], 0, 70, '…')) ?></span>
                        <span class="report-address"><?= htmlspecialchars($row['address']) ?></span>
                        <span class="report-time"><?= date('d M, H:i', strtotime($row['created_at'])) ?></span>
                        <span class="report-id">#<?= $row['id'] ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-state">No unassigned reports of this type.</p>
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
