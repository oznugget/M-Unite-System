<?php
require_once("dbConnection.php");

// 1. Totals for the stat cards
$total_result = $conn->query("SELECT COUNT(*) as total FROM reports");
if ($total_result === FALSE) {
    die("<p class=\"error\">Unable to retrieve report totals!</p>");
}
$total_reports = $total_result->fetch_assoc()['total'];

$resolved_result = $conn->query("SELECT COUNT(*) as total FROM reports WHERE current_status = 'Resolved'");
$resolved_reports = $resolved_result->fetch_assoc()['total'];

$open_reports = $total_reports - $resolved_reports;
$resolved_rate = ($total_reports > 0) ? round(($resolved_reports / $total_reports) * 100) : 0;

// 2. Reports grouped by status
$status_counts = [];
$status_result = $conn->query("SELECT current_status, COUNT(*) as count FROM reports GROUP BY current_status");
if ($status_result === FALSE) {
    die("<p class=\"error\">Unable to retrieve status breakdown!</p>");
}
while ($row = $status_result->fetch_assoc()) {
    $status_counts[$row['current_status']] = (int)$row['count'];
}

// 3. Reports grouped by ward
$ward_counts = [];
$ward_result = $conn->query("SELECT COALESCE(w.ward_name, 'Unassigned') as ward_name, COUNT(*) as count
                              FROM reports r
                              LEFT JOIN wards w ON w.ward_id = r.ward_id
                              GROUP BY w.ward_name");
if ($ward_result === FALSE) {
    die("<p class=\"error\">Unable to retrieve ward breakdown!</p>");
}
while ($row = $ward_result->fetch_assoc()) {
    $ward_counts[$row['ward_name']] = (int)$row['count'];
}

// 4. Build the bar rows as HTML (same technique as Displaying Data - echo built up in a loop)
function render_bars($counts) {
    if (count($counts) === 0) {
        echo "<p class=\"empty-msg\">No data yet.</p>";
        return;
    }
    $max = max($counts);
    foreach ($counts as $label => $count) {
        $pct = ($max > 0) ? round(($count / $max) * 100) : 0;
        echo "<div class=\"bar-row\">";
        echo "<div class=\"bar-label\">" . htmlspecialchars($label) . "</div>";
        echo "<div class=\"bar-track\"><div class=\"bar-fill\" style=\"width:" . $pct . "%\"></div></div>";
        echo "<div class=\"bar-value\">" . $count . "</div>";
        echo "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Statistics - M-Unite Officer</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <div class="logo-box">M</div>
  <nav>
    <a href="officer-home.php">&#127968; Home</a>
    <a href="officer-notices.php">&#128226; Notices</a>
    <a href="officer-tickets.php">&#127915; Tickets</a>
    <a href="officer-stats.php" class="active">&#128200; Statistics</a>
    <a href="officer-events.php">&#127881; Events</a>
    <a href="officer-issues.php">&#128680; Current Issues</a>
  </nav>
  <span class="account-btn"><a href="officer-account.php">&#128100; My Account</a></span>
</header>

<main class="wide">
  <h1>Fault Statistics</h1>
  <p class="page-intro">A live breakdown of reports across the municipality.</p>

  <div class="stats-grid">
    <div class="stat-card"><div class="num" id="stat-total"><?php echo $total_reports; ?></div><div class="label">Total Reports</div></div>
    <div class="stat-card"><div class="num" id="stat-resolved-rate"><?php echo $resolved_rate; ?>%</div><div class="label">Resolved Rate</div></div>
    <div class="stat-card"><div class="num" id="stat-open"><?php echo $open_reports; ?></div><div class="label">Still Open</div></div>
  </div>

  <section>
    <h2>&#128202; By Status</h2>
    <div class="bar-chart-wrap" id="status-bars"><?php render_bars($status_counts); ?></div>
  </section>

  <section>
    <h2>&#127968; By Ward</h2>
    <div class="bar-chart-wrap" id="ward-bars"><?php render_bars($ward_counts); ?></div>
  </section>
</main>

<footer>&copy; M-Unite 2026 - Municipal Officer Portal</footer>

<script src="nav.js"></script>
<?php $conn->close(); ?>
</body>
</html>
