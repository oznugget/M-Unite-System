<?php
session_start();
require_once("dbConnection.php");
require_once("log_helper.php");

if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}
$username = $_SESSION['username'];

$updateMessage = "";

// Handle deleting a notice (passed via the URL, same technique as delete.php?id=9 from the slides)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql_delete = "DELETE FROM notices WHERE notice_id = $delete_id";
    $delete_result = $conn->query($sql_delete);

    if ($delete_result) {
        log_activity($conn, $username, ACTION_NOTICE_DELETED);
    }

    header("Location: officer-notices.php");
    exit;
}

// Handle posting a new notice
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title_up   = isset($_POST["notice-title"]) ? trim($_POST["notice-title"]) : '';
    $target_up  = isset($_POST["notice-target"]) ? trim($_POST["notice-target"]) : '';
    $content_up = isset($_POST["notice-content"]) ? trim($_POST["notice-content"]) : '';

    if ($title_up !== '' && $content_up !== '') {

        // Work out the ward_id for the chosen target ("General" or a ward name)
        $ward_id_sql = "NULL";
        $notif_type = "general";

        if ($target_up !== "General") {
            $ward_result = $conn->query("SELECT ward_id FROM wards WHERE ward_name = '$target_up'");
            if ($ward_result && $ward_result->num_rows > 0) {
                $ward_row = $ward_result->fetch_assoc();
                $ward_id_sql = $ward_row['ward_id'];
                $notif_type = "ward";
            }
        }

        // posted_by now uses the real logged-in officer instead of a hardcoded value
        $sql_insert = "INSERT INTO notices (ward_id, title, content, notif_type, posted_by, created_at)
                        VALUES ($ward_id_sql, '$title_up', '$content_up', '$notif_type', '$username', NOW())";
        $insert_result = $conn->query($sql_insert);

        if ($insert_result === FALSE) {
            $updateMessage = "<p class=\"error\">Unable to post the notice!</p>";
        } else {
            $updateMessage = "<p class=\"success\">Notice successfully posted!</p>";
            log_activity($conn, $username, ACTION_NOTICE_CREATED);
        }
    } else {
        $updateMessage = "<p class=\"error\">Title and content are required.</p>";
    }
}

// Stats for the cards
$total_result = $conn->query("SELECT COUNT(*) as total FROM notices");
if ($total_result === FALSE) {
    die("<p class=\"error\">Unable to retrieve notice totals!</p>");
}
$total_notices = $total_result->fetch_assoc()['total'];

$general_result = $conn->query("SELECT COUNT(*) as total FROM notices WHERE ward_id IS NULL");
$general_notices = $general_result->fetch_assoc()['total'];

$ward_notices = $total_notices - $general_notices;

// List of ward names for the dropdown
$wards_result = $conn->query("SELECT ward_id, ward_name FROM wards ORDER BY ward_name");

// List of posted notices
$notices_result = $conn->query("SELECT n.notice_id, n.title, n.content, n.created_at,
                                        COALESCE(w.ward_name, 'General') as target
                                 FROM notices n
                                 LEFT JOIN wards w ON w.ward_id = n.ward_id
                                 ORDER BY n.created_at DESC");
if ($notices_result === FALSE) {
    die("<p class=\"error\">Unable to retrieve notices!</p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notices - M-Unite Officer</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <div class="logo-box">M</div>
  <nav>
    <a href="officer-home.php">&#127968; Home</a>
    <a href="officer-notices.php" class="active">&#128226; Notices</a>
    <a href="officer-tickets.php">&#127915; Tickets</a>
    <a href="officer-stats.php">&#128200; Statistics</a>
    <a href="officer-events.php">&#127881; Events</a>
    <a href="officer-issues.php">&#128680; Current Issues</a>
  </nav>
  <span class="account-btn"><a href="officer-account.php">&#128100; My Account</a></span>
</header>

<main class="wide">
  <h1>Municipal Notices</h1>
  <p class="page-intro">Post updates for residents and manage what's currently live.</p>

  <div class="stats-grid">
    <div class="stat-card"><div class="num" id="stat-total"><?php echo $total_notices; ?></div><div class="label">Total Notices</div></div>
    <div class="stat-card"><div class="num" id="stat-general"><?php echo $general_notices; ?></div><div class="label">General</div></div>
    <div class="stat-card"><div class="num" id="stat-ward"><?php echo $ward_notices; ?></div><div class="label">Ward-Specific</div></div>
  </div>

  <section>
    <h2>&#128221; Post a New Notice</h2>
    <?php echo $updateMessage; ?>
    <form id="notice-form" method="POST" action="officer-notices.php">
      <label>Title:
        <input type="text" id="notice-title" name="notice-title" maxlength="60" required>
      </label>
      <label>Applies to:
        <select id="notice-target" name="notice-target">
          <option value="General">General (all residents)</option>
          <?php while ($w = $wards_result->fetch_assoc()): ?>
            <option value="<?php echo htmlspecialchars($w['ward_name']); ?>"><?php echo htmlspecialchars($w['ward_name']); ?> only</option>
          <?php endwhile; ?>
        </select>
      </label>
      <label>Content:
        <textarea id="notice-content" name="notice-content" rows="3" maxlength="300" required></textarea>
      </label>
      <button type="submit" id="submit-btn">Post Notice</button>
    </form>
  </section>

  <section>
    <h2>&#128203; Posted Notices</h2>
    <ul id="notice-list" class="item-list">
      <?php if ($notices_result->num_rows === 0): ?>
        <p class="empty-msg" id="empty-msg">No notices posted yet.</p>
      <?php endif; ?>
      <?php while ($n = $notices_result->fetch_assoc()): ?>
        <li>
          <div class="item-title"><?php echo htmlspecialchars($n['title']); ?> <span class="badge badge-progress"><?php echo htmlspecialchars($n['target']); ?></span></div>
          <div class="item-meta">Posted <?php echo $n['created_at']; ?></div>
          <p><?php echo htmlspecialchars($n['content']); ?></p>
          <a href="officer-notices.php?delete_id=<?php echo $n['notice_id']; ?>" onclick="return confirm('Delete this notice?');"><button type="button" class="danger">Delete</button></a>
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
