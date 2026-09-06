<?php
session_start();
require_once __DIR__ . '/db_connect.php'; // expects $conn (mysqli)

// TODO: role-gate this page to ward councillor accounts, matching
// your existing role-gated display pattern.

// Only unassigned reports show on this dashboard — once linked to a
// ticket, ticket_id is set and the report drops off this list.
$sql = "SELECT report_id AS id, category_id, description, street_name,
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

// Open tickets in this ward, for the "Add to Existing Ticket" dropdown
$open_tickets_stmt = $conn->prepare(
    "SELECT ticket_id, title, category_id FROM tickets
     WHERE ward_id = ? AND current_status != 'Closed'
     ORDER BY date_created DESC"
);
$open_tickets_stmt->bind_param('i', $ward_id);
$open_tickets_stmt->execute();
$open_tickets = $open_tickets_stmt->get_result();
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
        <select id="group-by-filter">
            <option value="">No grouping</option>
            <option value="street">Group by street</option>
            <option value="category">Group by category</option>
        </select>
        <select id="type-filter">
            <option value="">All types</option>
            <?php
            $types = $conn->query("SELECT DISTINCT category_id FROM reports WHERE ticket_id IS NULL ORDER BY category_id");
            while ($t = $types->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($t['category_id']) . '">' . htmlspecialchars($t['category_id']) . '</option>';
            }
            ?>
        </select>
        <button id="add-existing-btn" class="btn-secondary" disabled>Add to Existing Ticket</button>
        <button id="create-ticket-btn" disabled>Create Ticket from Selected</button>
    </div>
</div>

<div class="report-list" id="report-list">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="report-row" data-type="<?= htmlspecialchars($row['category_id']) ?>" data-street="<?= htmlspecialchars($row['street_name']) ?>" data-id="<?= $row['id'] ?>">
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

<!-- Add to Existing Ticket Modal -->
<div id="existing-ticket-modal" class="modal-overlay hidden">
    <div class="modal">
        <h2>Add to Existing Ticket</h2>
        <p id="existing-modal-report-count"></p>
        <label for="existing-ticket-select">Ticket</label>
        <select id="existing-ticket-select">
            <?php if ($open_tickets && $open_tickets->num_rows > 0): ?>
                <?php while ($t = $open_tickets->fetch_assoc()): ?>
                    <option value="<?= $t['ticket_id'] ?>">
                        #<?= $t['ticket_id'] ?> — <?= htmlspecialchars($t['title']) ?> (<?= htmlspecialchars($t['category_id'] ?? 'Mixed') ?>)
                    </option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="" disabled selected>No open tickets in your ward yet</option>
            <?php endif; ?>
        </select>

        <div class="modal-actions">
            <button id="existing-modal-cancel" class="btn-secondary">Cancel</button>
            <button id="existing-modal-submit" class="btn-primary" <?= (!$open_tickets || $open_tickets->num_rows === 0) ? 'disabled' : '' ?>>Add Reports</button>
        </div>
    </div>
</div>

<script src="reports.js"></script>
</body>
</html>
