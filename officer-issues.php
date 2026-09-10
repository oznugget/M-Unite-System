<?php
session_start();
require "dbConnection.php";
require "log_helper.php";

if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}
$username = $_SESSION['username'];

$updateMessage = "";

// Handle deleting an issue
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM current_issues WHERE issue_id = ?");
    $stmt->bind_param("i", $delete_id);
    $deleted = $stmt->execute();
    $stmt->close();

    if ($deleted) {
        log_activity($conn, $username, ACTION_ISSUE_DELETED);
    }
    header("Location: officer-issues.php");
    exit;
}

// Handle featuring an issue (exclusive - only one issue can be featured,
// since the resident page does WHERE is_featured = 1 LIMIT 1)
if (isset($_GET['feature_id'])) {
    $feature_id = intval($_GET['feature_id']);

    $conn->begin_transaction();
    try {
        $conn->query("UPDATE current_issues SET is_featured = 0 WHERE is_featured = 1");

        $stmt = $conn->prepare("UPDATE current_issues SET is_featured = 1 WHERE issue_id = ?");
        $stmt->bind_param("i", $feature_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        log_activity($conn, $username, ACTION_ISSUE_FEATURED);
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
    }
    header("Location: officer-issues.php");
    exit;
}

// Handle un-featuring (so it's possible to have zero featured issues too)
if (isset($_GET['unfeature_id'])) {
    $unfeature_id = intval($_GET['unfeature_id']);
    $stmt = $conn->prepare("UPDATE current_issues SET is_featured = 0 WHERE issue_id = ?");
    $stmt->bind_param("i", $unfeature_id);
    $stmt->execute();
    $stmt->close();
    header("Location: officer-issues.php");
    exit;
}

// Handle creating a new issue
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title_up   = isset($_POST["issue-title"]) ? trim($_POST["issue-title"]) : '';
    $content_up = isset($_POST["issue-content"]) ? trim($_POST["issue-content"]) : '';

    if ($title_up === '' || $content_up === '') {
        $updateMessage = "<p class=\"error\">Title and content are required.</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO current_issues (title, content, is_featured, posted_by, created_at) VALUES (?, ?, 0, ?, NOW())");
        $stmt->bind_param("sss", $title_up, $content_up, $username);
        $insert_result = $stmt->execute();
        $stmt->close();

        if ($insert_result) {
            $updateMessage = "<p class=\"success\">Issue posted! Use \"Feature\" below to make it show on the community home page.</p>";
            log_activity($conn, $username, ACTION_ISSUE_CREATED);
        } else {
            $updateMessage = "<p class=\"error\">Unable to post the issue!</p>";
        }
    }
}

// List all issues
$issues_result = $conn->query("SELECT issue_id, title, content, is_featured, posted_by, created_at FROM current_issues ORDER BY created_at DESC");
if ($issues_result === FALSE) {
    die("<p class=\"error\">Unable to retrieve current issues!</p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Current Issues - M-Unite Officer</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <div class="logo-box">M</div>
  <nav>
    <a href="officer-home.php">&#127968; Home</a>
    <a href="officer-notices.php">&#128226; Notices</a>
    <a href="officer-tickets.php">&#127915; Tickets</a>
    <a href="officer-stats.php">&#128200; Statistics</a>
    <a href="officer-events.php">&#127881; Events</a>
    <a href="officer-issues.php" class="active">&#128680; Current Issues</a>
  </nav>
  <span class="account-btn"><a href="officer-account.php">&#128100; My Account</a></span>
</header>

<main class="wide">
  <h1>Current Issues</h1>
  <p class="page-intro">One featured issue shows on the community home page at a time.</p>

  <section>
    <h2>&#128221; Post a New Issue</h2>
    <?php echo $updateMessage; ?>
    <form method="POST" action="officer-issues.php">
      <label>Title:
        <input type="text" name="issue-title" maxlength="150" required>
      </label>
      <label>Content:
        <textarea name="issue-content" rows="4" required></textarea>
      </label>
      <button type="submit">Post Issue</button>
    </form>
  </section>

  <section>
    <h2>&#128203; All Issues</h2>
    <ul class="item-list">
      <?php if ($issues_result->num_rows === 0): ?>
        <p class="empty-msg">No current issues posted yet.</p>
      <?php endif; ?>
      <?php while ($i = $issues_result->fetch_assoc()): ?>
        <li>
          <div class="item-title">
            <?php echo htmlspecialchars($i['title']); ?>
            <?php if ($i['is_featured'] == 1): ?>
              <span class="badge badge-approved">Featured - live on community home page</span>
            <?php endif; ?>
          </div>
          <div class="item-meta">Posted by <?php echo htmlspecialchars($i['posted_by']); ?> on <?php echo $i['created_at']; ?></div>
          <p><?php echo nl2br(htmlspecialchars($i['content'])); ?></p>

          <?php if ($i['is_featured'] == 1): ?>
            <a href="officer-issues.php?unfeature_id=<?php echo $i['issue_id']; ?>"><button type="button" class="secondary">Unfeature</button></a>
          <?php else: ?>
            <a href="officer-issues.php?feature_id=<?php echo $i['issue_id']; ?>"><button type="button">Feature this</button></a>
          <?php endif; ?>
          <a href="officer-issues.php?delete_id=<?php echo $i['issue_id']; ?>" onclick="return confirm('Delete this issue?');"><button type="button" class="danger">Delete</button></a>
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
