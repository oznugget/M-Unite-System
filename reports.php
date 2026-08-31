<?php
session_start();
require_once __DIR__ . '/db_connect.php'; // expects $conn (mysqli)

// TODO: role-gate this page to ward councillor accounts, matching
// your existing role-gated display pattern.

// Only unassigned reports show on this dashboard — once linked to a
// ticket, ticket_id is set and the report drops off this list.
$sql = "SELECT report_id AS id, category_id, description,
        CONCAT(street_number, ' ', street_name, ', ', surburb) AS address,
        timestamp, current_status AS status
    FROM reports
    WHERE ticket_id IS NULL AND ward_id = ?
    ORDER BY timestamp DESC";

$ward_id = $_SESSION['ward_id'] ?? null;
if (!$ward_id) {
    die('No ward set for this session.'); // or redirect to a login page
}

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $ward_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Incoming Reports — M-Unite Councillor View</title>
<link rel="stylesheet" href="tickets.css">
</head>
<body>

<header class="topbar">
    <h1>Incoming Reports</h1>
    <nav>
        <a href="reports.php" class="active">Reports</a>
        <a href="tickets.php">Tickets</a>
    </nav>
</header>

<div class="toolbar">
    <div class="toolbar-left">
        <label class="select-all-wrap">
            <input type="checkbox" id="select-all"> Select all
        </label>
        <span id="selection-count">0 selected</span>
    </div>
    <div class="toolbar-right">
        <select id="type-filter">
            <option value="">All types</option>
            <?php
            $types = $conn->query("SELECT DISTINCT category_id FROM reports WHERE ticket_id IS NULL ORDER BY category_id");
            while ($t = $types->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($t['category_id']) . '">' . htmlspecialchars($t['category_id']) . '</option>';
            }
            ?>
        </select>
        <button id="create-ticket-btn" disabled>Create Ticket from Selected</button>
    </div>
</div>

<div class="report-list" id="report-list">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="report-row" data-type="<?= htmlspecialchars($row['category_id']) ?>" data-id="<?= $row['id'] ?>">
                <input type="checkbox" class="report-checkbox" value="<?= $row['id'] ?>">
                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>"><?= htmlspecialchars($row['status']) ?></span>
                <span class="report-type"><?= htmlspecialchars($row['category_id']) ?></span>
                <span class="report-desc" title="<?= htmlspecialchars($row['description']) ?>"><?= htmlspecialchars(mb_strimwidth($row['description'], 0, 70, '…')) ?></span>
                <span class="report-address"><?= htmlspecialchars($row['address']) ?></span>
                <span class="report-time"><?= date('d M, H:i', strtotime($row['timestamp'])) ?></span>
                <span class="report-id">#<?= $row['id'] ?></span>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="empty-state">No unassigned reports right now.</p>
    <?php endif; ?>
</div>

<!-- Create Ticket Modal -->
<div id="ticket-modal" class="modal-overlay hidden">
    <div class="modal">
        <h2>Create Ticket</h2>
        <p id="modal-report-count"></p>
        <label for="ticket-title">Title</label>
        <input type="text" id="ticket-title" placeholder="e.g. Pothole cluster — High Street">

        <label for="ticket-desc">Description</label>
        <textarea id="ticket-desc" rows="4" placeholder="Summarize the issue for this ticket..."></textarea>

        <div class="modal-actions">
            <button id="modal-cancel" class="btn-secondary">Cancel</button>
            <button id="modal-submit" class="btn-primary">Create Ticket</button>
        </div>
    </div>
</div>

<script src="reports.js"></script>
</body>
</html>
