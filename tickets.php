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
$result = $stmt->get_result();
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

<div class="ticket-grid">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
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
        <?php endwhile; ?>
    <?php else: ?>
        <p class="empty-state">No tickets yet. Aggregate reports from the Reports page to create one.</p>
    <?php endif; ?>
</div>

</body>
</html>
