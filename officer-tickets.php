<?php
session_start();
include 'dbConnection.php';
require 'log_helper.php';

if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}
$username = $_SESSION['username'];

// Handle status updates if submitted via form
if (isset($_POST['update_status']) && isset($_POST['ticket_id'])) {
    $new_status = $_POST['status'];
    $ticket_id = intval($_POST['ticket_id']);

    $valid_statuses = ['Pending', 'In Progress', 'Resolved', 'Closed'];
    if (in_array($new_status, $valid_statuses, true)) {

        if ($new_status === 'Resolved') {
            $stmt = $conn->prepare("UPDATE tickets SET current_status = ?, date_resolved = NOW() WHERE ticket_id = ?");
        } elseif ($new_status === 'Closed') {
            $stmt = $conn->prepare("UPDATE tickets SET current_status = ?, date_closed = NOW() WHERE ticket_id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE tickets SET current_status = ? WHERE ticket_id = ?");
        }
        $stmt->bind_param("si", $new_status, $ticket_id);
        $stmt->execute();
        $stmt->close();

        // Log the change to ticket_status_history (existing behaviour)
        $log_stmt = $conn->prepare("INSERT INTO ticket_status_history (ticket_id, status, username, changed_at) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $ticket_id, $new_status, $username);
        $log_stmt->execute();
        $log_stmt->close();

        // Also write to the system-wide audit log
        log_activity($conn, $username, ACTION_TICKET_STATUS_UPDATED);
    }

    header("Location: officer-tickets.php");
    exit;
}

// Fetch tickets from the database
$result = $conn->query("SELECT ticket_id, ward_id, category_id, description, current_status FROM tickets");
if ($result === FALSE) {
    die("<p class=\"error\">Unable to retrieve tickets!</p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tickets - M-Unite Officer</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <div class="logo-box">M</div>
  <nav>
    <a href="officer-home.php">&#127968; Home</a>
    <a href="officer-notices.php">&#128226; Notices</a>
    <a href="officer-tickets.php" class="active">&#127915; Tickets</a>
    <a href="officer-stats.php">&#128200; Statistics</a>
    <a href="officer-events.php">&#127881; Events</a>
    <a href="officer-issues.php">&#128680; Current Issues</a>
  </nav>
  <span class="account-btn"><a href="officer-account.php">&#128100; My Account</a></span>
</header>

<main class="wide">
  <h1>Issued Tickets</h1>
  <p class="page-intro">Official tickets forwarded by ward councillors.</p>

  <section>
    <ul class="item-list">
      <?php if ($result->num_rows === 0): ?>
        <p class="empty-msg">No tickets found.</p>
      <?php endif; ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <li>
          <div class="item-title">Category #<?php echo $row['category_id']; ?>
            <span class="badge badge-<?php echo str_replace(' ', '', $row['current_status']); ?>"><?php echo htmlspecialchars($row['current_status']); ?></span>
          </div>
          <div class="item-meta">Ward ID: <?php echo $row['ward_id']; ?></div>
          <p><?php echo htmlspecialchars($row['description']); ?></p>

          <form method="POST" style="margin-top: 10px;">
            <input type="hidden" name="ticket_id" value="<?php echo $row['ticket_id']; ?>">
            <label>Update status:
              <select name="status" onchange="this.form.submit()">
                <option value="Pending" <?php if($row['current_status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                <option value="In Progress" <?php if($row['current_status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                <option value="Resolved" <?php if($row['current_status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
                <option value="Closed" <?php if($row['current_status'] == 'Closed') echo 'selected'; ?>>Closed</option>
              </select>
            </label>
            <input type="hidden" name="update_status" value="1">
          </form>
        </li>
      <?php endwhile; ?>
    </ul>
  </section>
</main>

<footer>&copy; M-Unite 2026 - Municipal Officer Portal</footer>

<script src="nav.js"></script>
<?php $conn->close(); ?>
</body>
</html>
