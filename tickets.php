<?php

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/categories.php';
require_once __DIR__ . '/require_councillor.php';

$isLoggedIn = true; // guaranteed by the guard
$firstname  = htmlspecialchars($_SESSION['firstname'] ?? '');
$ward_id    = $_SESSION['ward_id'] ?? null;

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
      <a href="ward_councillor_home.php" class="nav-item">Home</a>
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
            <span class="report-type <?= category_class($row['fault_type']) ?>"><?= htmlspecialchars(category_name($row['fault_type'])) ?></span>
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
