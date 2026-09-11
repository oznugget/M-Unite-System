<?php
session_start();
include 'dbConnection.php';

if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}
$username = $_SESSION['username'];

// NOTE: no log_activity() call in this file - nothing here writes to the
// database yet (dam levels are still client-side only, see officer-home.js).
// Once dam levels are wired to a real table, that's where a log call belongs.

// 1. Fetch ticket counts by status for the stats grid and donut chart
$status_counts = [
    "Pending" => 0,
    "In Progress" => 0,
    "Resolved" => 0,
    "Closed" => 0
];

$result = $conn->query("SELECT current_status, COUNT(*) as count FROM tickets GROUP BY current_status");
if ($result === FALSE) {
    die("<p class=\"error\">Unable to retrieve ticket counts!</p>");
}
while ($row = $result->fetch_assoc()) {
    if (array_key_exists($row['current_status'], $status_counts)) {
        $status_counts[$row['current_status']] = (int)$row['count'];
    }
}

$open_tickets_count = $status_counts['Pending'] + $status_counts['In Progress'];
$resolved_month_count = $status_counts['Resolved'];

// 2. Count total notices posted
$notices_result = $conn->query("SELECT COUNT(*) as total FROM notices");
if ($notices_result === FALSE) {
    die("<p class=\"error\">Unable to retrieve notice count!</p>");
}
$notices_row = $notices_result->fetch_assoc();
$total_notices = $notices_row['total'];

// 3. Fetch tickets that need attention (Pending or In Progress)
$priority_query = "SELECT category_id, ward_id, current_status, date_created FROM tickets WHERE current_status IN ('Pending', 'In Progress') ORDER BY date_created ASC LIMIT 5";
$priority_result = $conn->query($priority_query);
if ($priority_result === FALSE) {
    die("<p class=\"error\">Unable to retrieve priority tickets!</p>");
}

// 4. Recent activity - real data from ticket_status_history instead of static placeholders
$activity_query = "SELECT ticket_id, status, changed_at FROM ticket_status_history ORDER BY changed_at DESC LIMIT 5";
$activity_result = $conn->query($activity_query);
if ($activity_result === FALSE) {
    die("<p class=\"error\">Unable to retrieve recent activity!</p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - M-Unite Officer</title>
<link rel="stylesheet" href="style.css">
<style>
  .dam-card { position: relative; padding-right: 40px; }
  .dam-edit-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--bg, #DCE6F2);
    border: 1px solid var(--navy, #0E2841);
    color: var(--navy, #0E2841);
    font-size: 0.85rem;
    line-height: 1;
    padding: 0;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
  }
  .dam-edit-icon:hover { background: var(--navy, #0E2841); color: white; }
  .dam-name-input {
    font-weight: 700;
    color: var(--navy, #0E2841);
    border: none;
    border-bottom: 1px dashed var(--navy, #0E2841);
    background: transparent;
    width: 100%;
    margin-bottom: 6px;
    padding: 2px 0;
  }
  .dam-percent-input { width: 70px; font-weight: 800; font-size: 1.05rem; }
</style>
</head>
<body>

<header>
  <div class="logo-box">M</div>
  <nav>
    <a href="officer-home.php" class="active">&#127968; Home</a>
    <a href="officer-notices.php">&#128226; Notices</a>
    <a href="officer-tickets.php">&#127915; Tickets</a>
    <a href="officer-stats.php">&#128200; Statistics</a>
    <a href="officer-events.php">&#127881; Events</a>
    <a href="officer-issues.php">&#128680; Current Issues</a>
  </nav>
  <span class="account-btn"><a href="officer-account.php">&#128100; My Account</a></span>
</header>

<main class="wide">
      <h1>Officer Dashboard</h1>
      <p class="page-intro">Your tickets, notices, and ward activity at a glance.</p>

      <div class="stats-grid">
        <div class="stat-card"><span class="num" id="stat-open"><?php echo $open_tickets_count; ?></span><div class="label">Open Tickets</div></div>
        <div class="stat-card"><span class="num" id="stat-resolved"><?php echo $resolved_month_count; ?></span><div class="label">Resolved This Month</div></div>
        <div class="stat-card"><span class="num" id="stat-notices"><?php echo $total_notices; ?></span><div class="label">Notices Posted</div></div>
        <div class="stat-card"><span class="num" id="stat-unread">2</span><div class="label">Unread Alerts</div></div>
      </div>
      <!-- NOTE: Unread Alerts is still hardcoded to 2 - wiring this to notice_status
           needs a logged-in officer's username, which isn't available until
           there's a sign-in page setting a session. -->

      <div class="dash-row">
        <section>
          <h2>&#127915; Tickets by Status</h2>
          <div class="donut" id="status-donut"><div class="donut-hole" id="status-donut-total"></div></div>
          <div class="donut-key" id="status-key"></div>
        </section>

        <section>
          <h2>&#128276; Recent Activity</h2>
          <ul class="feed-list" id="activity-feed">
            <?php if ($activity_result->num_rows === 0): ?>
              <li>No recent activity.</li>
            <?php endif; ?>
            <?php while ($a = $activity_result->fetch_assoc()): ?>
              <li>Ticket #<?php echo $a['ticket_id']; ?> marked <?php echo htmlspecialchars($a['status']); ?><div class="item-meta"><?php echo $a['changed_at']; ?></div></li>
            <?php endwhile; ?>
          </ul>
        </section>
      </div>

      <section>
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
          <h2 style="margin:0;">&#9888;&#65039; Needs Your Attention</h2>
          <a href="officer-tickets.php"><button type="button">View All Tickets &#8594;</button></a>
        </div>
        <table id="priority-table">
          <tr><th>Category ID</th><th>Ward ID</th><th>Status</th><th>Reported Date</th></tr>
          <?php if ($priority_result->num_rows > 0): ?>
            <?php while ($row = $priority_result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['category_id']); ?></td>
                <td><?php echo htmlspecialchars($row['ward_id']); ?></td>
                <td><span class="badge badge-<?php echo str_replace(' ', '', $row['current_status']); ?>"><?php echo htmlspecialchars($row['current_status']); ?></span></td>
                <td><?php echo htmlspecialchars($row['date_created']); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" style="text-align:center;">Nothing urgent right now.</td></tr>
          <?php endif; ?>
        </table>
      </section>

      <section>
        <h2>&#128167; Dam Levels</h2>
        <p class="page-intro">Water supply dams serving Makhanda.</p>
        <div class="status-row">

          <div class="status-card dam-card">
            <button type="button" class="dam-edit-icon" id="dam-btn-1" onclick="toggleDamEdit(1)" title="Edit">&#9998;</button>
            <div class="status-title" id="dam-name-view-1">Howieson's Poort Dam</div>
            <input type="text" class="dam-name-input" id="dam-name-input-1" value="Howieson's Poort Dam" style="display:none;">
            <div class="status-value" id="dam-view-1">29%</div>
            <input type="number" class="dam-percent-input" step="0.1" min="0" max="100" id="dam-input-1" value="29" style="display:none;">
            <div class="status-meta">Supplies Waainek Water Treatment Works (west)</div>
          </div>

          <div class="status-card dam-card">
            <button type="button" class="dam-edit-icon" id="dam-btn-2" onclick="toggleDamEdit(2)" title="Edit">&#9998;</button>
            <div class="status-title" id="dam-name-view-2">Settlers Dam</div>
            <input type="text" class="dam-name-input" id="dam-name-input-2" value="Settlers Dam" style="display:none;">
            <div class="status-value fail" id="dam-view-2">8%</div>
            <input type="number" class="dam-percent-input" step="0.1" min="0" max="100" id="dam-input-2" value="8" style="display:none;">
            <div class="status-meta">Supplies Waainek Water Treatment Works (west)</div>
          </div>

          <div class="status-card dam-card">
            <button type="button" class="dam-edit-icon" id="dam-btn-3" onclick="toggleDamEdit(3)" title="Edit">&#9998;</button>
            <div class="status-title" id="dam-name-view-3">Glen Melville Dam</div>
            <input type="text" class="dam-name-input" id="dam-name-input-3" value="Glen Melville Dam" style="display:none;">
            <div class="status-value ok" id="dam-view-3">45%</div>
            <input type="number" class="dam-percent-input" step="0.1" min="0" max="100" id="dam-input-3" value="45" style="display:none;">
            <div class="status-meta">Supplies James Kleynhans Water Treatment Works (east)</div>
          </div>

        </div>
      </section>
    </main>

<footer>&copy; M-Unite 2026 - Municipal Officer Portal</footer>

<!-- Pass PHP counts directly to JavaScript to keep your chart rendering function working -->
<script>
const ticketsByStatus = [
  { status: "Pending", count: <?php echo $status_counts['Pending']; ?>, color: "#8a5a00", bg: "#fdecd2" },
  { status: "In Progress", count: <?php echo $status_counts['In Progress']; ?>, color: "#0E2841" },
  { status: "Resolved", count: <?php echo $status_counts['Resolved']; ?>, color: "#1e7a3c" },
  { status: "Closed", count: <?php echo $status_counts['Closed']; ?>, color: "#999999" }
];

// Dam levels editing - client-side only, no database involved
function toggleDamEdit(id) {
  const nameView = document.getElementById("dam-name-view-" + id);
  const nameInput = document.getElementById("dam-name-input-" + id);
  const percentView = document.getElementById("dam-view-" + id);
  const percentInput = document.getElementById("dam-input-" + id);
  const btn = document.getElementById("dam-btn-" + id);

  const isEditing = nameInput.style.display !== "none";

  if (!isEditing) {
    // switch to edit mode
    nameInput.value = nameView.textContent;
    percentInput.value = parseFloat(percentView.textContent);

    nameView.style.display = "none";
    percentView.style.display = "none";
    nameInput.style.display = "block";
    percentInput.style.display = "inline-block";

    btn.innerHTML = "&#10003;"; // checkmark
    btn.title = "Save";
  } else {
    // save and switch back to view mode
    const newName = nameInput.value.trim();
    const newPercent = parseFloat(percentInput.value);

    if (newName !== "") {
      nameView.textContent = newName;
    }
    if (!isNaN(newPercent)) {
      percentView.textContent = newPercent + "%";
      percentView.classList.remove("ok", "fail");
      if (newPercent < 15) {
        percentView.classList.add("fail");
      } else if (newPercent >= 40) {
        percentView.classList.add("ok");
      }
    }

    nameInput.style.display = "none";
    percentInput.style.display = "none";
    nameView.style.display = "block";
    percentView.style.display = "block";

    btn.innerHTML = "&#9998;"; // pencil
    btn.title = "Edit";
  }
}
</script>
<script src="nav.js"></script>
<script src="officer-home.js"></script>
<?php $conn->close(); ?>
</body>
</html>
