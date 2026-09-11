<?php
session_start();
require_once __DIR__ . '/db_connect.php';

$sql = "SELECT t.ticket_id AS id, t.title, t.description, t.category_id AS fault_type,
        t.current_status AS status, t.date_created AS created_at, COUNT(r.report_id) AS report_count
    FROM tickets t
    LEFT JOIN reports r ON r.ticket_id = t.ticket_id
    WHERE t.ward_id = ?
    GROUP BY t.ticket_id
    ORDER BY t.date_created DESC";
$ward_id = $_SESSION['ward_id'] ?? null;
if (!$ward_id) {
    die('No ward set for this session.'); // or redirect to a login page
}
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $ward_id);
$stmt->execute();
$all_tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$active_tickets = array_filter($all_tickets, fn($t) => !in_array($t['status'], ['Resolved', 'Closed'], true));
$completed_tickets = array_filter($all_tickets, fn($t) => in_array($t['status'], ['Resolved', 'Closed'], true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tickets — M-Unite Councillor View</title>
<link rel="stylesheet" href="tickets.css">
</head>
<body>

<header class="topbar">
    <h1>Tickets</h1>
    <nav>
        <a href="reports.php">Reports</a>
        <a href="tickets.php" class="active">Tickets</a>
    </nav>
</header>

<?php
// Renders one ticket card — kept as a tiny local helper so the active
// and completed sections below don't duplicate the markup.
function render_ticket_card(array $row): void {
    ?>
    <a class="ticket-card" href="ticket.php?id=<?= $row['id'] ?>">
        <div class="ticket-card-top">
            <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>"><?= htmlspecialchars($row['status']) ?></span>
            <span class="ticket-count"><?= $row['report_count'] ?> report<?= $row['report_count'] == 1 ? '' : 's' ?></span>
        </div>
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <p><?= htmlspecialchars(mb_strimwidth($row['description'], 0, 120, '…')) ?></p>
        <div class="ticket-card-bottom">
            <span><?= htmlspecialchars($row['fault_type'] ?? 'Mixed') ?></span>
            <span><?= $row['created_at'] ? date('d M Y', strtotime($row['created_at'])) : '—' ?></span>
        </div>
    </a>
    <?php
}
?>

<h3 class="section-heading">Active Tickets (<?= count($active_tickets) ?>)</h3>
<div class="ticket-grid">
    <?php if (count($active_tickets) > 0): ?>
        <?php foreach ($active_tickets as $row): render_ticket_card($row); endforeach; ?>
    <?php else: ?>
        <p class="empty-state">No tickets yet. Aggregate reports from the Reports page to create one.</p>
    <?php endif; ?>
</div>

<?php if (count($completed_tickets) > 0): ?>
    <h3 class="section-heading">Completed Tickets (<?= count($completed_tickets) ?>)</h3>
    <div class="ticket-grid completed-section">
        <?php foreach ($completed_tickets as $row): render_ticket_card($row); endforeach; ?>
    </div>
<?php endif; ?>

</body>
</html>
