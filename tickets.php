<?php
session_start();
require_once __DIR__ . '/db_connect.php';

$sql = "SELECT t.id, t.title, t.description, t.fault_type, t.status, t.created_at,
               COUNT(r.id) AS report_count
        FROM tickets t
        LEFT JOIN reports r ON r.ticket_id = t.id
        GROUP BY t.id
        ORDER BY t.created_at DESC";
$result = $conn->query($sql);
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
                    <span><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                </div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="empty-state">No tickets yet. Aggregate reports from the Reports page to create one.</p>
    <?php endif; ?>
</div>

</body>
</html>
