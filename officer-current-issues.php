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
    $delStmt = $conn->prepare("DELETE FROM current_issues WHERE issue_id = ?");
    $delStmt->bind_param("i", $delete_id);
    if ($delStmt->execute()) {
        log_activity($conn, $username, ACTION_ISSUE_DELETED);
    }
    $delStmt->close();

    header("Location: officer-current-issues.php");
    exit;
}

// Handle featuring an issue - this is what controls what shows on the resident
// home page. Only one issue can be featured at a time, so unfeature everything
// else first, then feature the one that was clicked.
if (isset($_GET['feature_id'])) {
    $feature_id = intval($_GET['feature_id']);

    $conn->query("UPDATE current_issues SET is_featured = 0");

    $featStmt = $conn->prepare("UPDATE current_issues SET is_featured = 1 WHERE issue_id = ?");
    $featStmt->bind_param("i", $feature_id);
    $featStmt->execute();
    $featStmt->close();

    header("Location: officer-current-issues.php");
    exit;
}

// Handle posting a new issue OR saving edits to an existing one
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    $title   = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';

    if ($title === '' || $content === '') {
        $updateMessage = "<p class=\"error\">Title and content are required.</p>";
    } elseif ($edit_id > 0) {
        $stmt = $conn->prepare("UPDATE current_issues SET title = ?, content = ? WHERE issue_id = ?");
        $stmt->bind_param("ssi", $title, $content, $edit_id);
        if ($stmt->execute()) {
            $updateMessage = "<p class=\"success\">Issue updated!</p>";
            log_activity($conn, $username, ACTION_ISSUE_UPDATED);
        } else {
            $updateMessage = "<p class=\"error\">Unable to update the issue.</p>";
        }
        $stmt->close();
    } else {
        // The very first issue ever posted is automatically featured, since
        // otherwise the home page would have nothing to show until an officer
        // remembers to click Feature
        $isFirstEver = $conn->query("SELECT COUNT(*) as total FROM current_issues")->fetch_assoc()['total'] == 0;
        $featuredFlag = $isFirstEver ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO current_issues (title, content, is_featured, posted_by, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssis", $title, $content, $featuredFlag, $username);
        if ($stmt->execute()) {
            $updateMessage = "<p class=\"success\">Issue posted!</p>";
            log_activity($conn, $username, ACTION_ISSUE_CREATED);
        } else {
            $updateMessage = "<p class=\"error\">Unable to post the issue.</p>";
        }
        $stmt->close();
    }
}

// If we're editing, fetch that issue's current data to prefill the form
$editingIssue = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $editStmt = $conn->prepare("SELECT * FROM current_issues WHERE issue_id = ?");
    $editStmt->bind_param("i", $edit_id);
    $editStmt->execute();
    $editResult = $editStmt->get_result();
    if ($editResult->num_rows > 0) {
        $editingIssue = $editResult->fetch_assoc();
    }
    $editStmt->close();
}

// Stats for the cards
$total_result = $conn->query("SELECT COUNT(*) as total FROM current_issues");
$total_issues = $total_result->fetch_assoc()['total'];

$featured_result = $conn->query("SELECT title FROM current_issues WHERE is_featured = 1 LIMIT 1");
$featured_title = $featured_result->num_rows > 0 ? $featured_result->fetch_assoc()['title'] : 'None yet';

// List of all issues, newest first
$issues_result = $conn->query("SELECT * FROM current_issues ORDER BY created_at DESC");
if ($issues_result === FALSE) {
    die("<p class=\"error\">Unable to retrieve issues!</p>");
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
    <a href="officer-current-issues.php" class="active">&#128240; Current Issues</a>
  </nav>
  <span class="account-btn"><a href="officer-account.php">&#128100; My Account</a></span>
</header>

<main class="wide">
  <h1>Current Issues</h1>
  <p class="page-intro">Manage the issue featured on the resident home page.</p>

  <div class="stats-grid">
    <div class="stat-card"><div class="num" id="stat-total"><?php echo $total_issues; ?></div><div class="label">Total Issues</div></div>
    <div class="stat-card"><div class="num" style="font-size: 1.1rem;" id="stat-featured"><?php echo htmlspecialchars($featured_title); ?></div><div class="label">Currently Featured</div></div>
  </div>

  <section>
    <h2><?php echo $editingIssue ? '&#9999;&#65039; Edit Issue' : '&#128221; Post a New Issue'; ?></h2>
    <?php echo $updateMessage; ?>
    <form id="issue-form" method="POST" action="officer-current-issues.php">
      <?php if ($editingIssue): ?>
        <input type="hidden" name="edit_id" value="<?php echo (int)$editingIssue['issue_id']; ?>">
      <?php endif; ?>

      <label>Title:
        <input type="text" name="title" maxlength="150" required
               value="<?php echo $editingIssue ? htmlspecialchars($editingIssue['title']) : ''; ?>">
      </label>

      <label>Content:
        <textarea name="content" rows="5" required><?php echo $editingIssue ? htmlspecialchars($editingIssue['content']) : ''; ?></textarea>
        <span class="field-note">This is what residents see. The home page shows the first part, with a "Read more" link to the rest.</span>
      </label>

      <button type="submit"><?php echo $editingIssue ? 'Save Changes' : 'Post Issue'; ?></button>
      <?php if ($editingIssue): ?>
        <a href="officer-current-issues.php"><button type="button" class="secondary">Cancel</button></a>
      <?php endif; ?>
    </form>
  </section>

  <section>
    <h2>&#128240; Posted Issues</h2>
    <ul id="issue-list" class="item-list">
      <?php if ($issues_result->num_rows === 0): ?>
        <p class="empty-msg">No issues posted yet.</p>
      <?php endif; ?>
      <?php while ($i = $issues_result->fetch_assoc()): ?>
        <li>
          <div class="item-title">
            <?php echo htmlspecialchars($i['title']); ?>
            <?php if ($i['is_featured']): ?>
              <span class="badge badge-approved">Shown on home page</span>
            <?php endif; ?>
          </div>
          <div class="item-meta">Posted <?php echo $i['created_at']; ?></div>
          <p><?php echo nl2br(htmlspecialchars($i['content'])); ?></p>
          <a href="officer-current-issues.php?edit_id=<?php echo $i['issue_id']; ?>"><button type="button" class="secondary">Edit</button></a>
          <?php if (!$i['is_featured']): ?>
            <a href="officer-current-issues.php?feature_id=<?php echo $i['issue_id']; ?>"><button type="button" class="secondary">Feature on home page</button></a>
          <?php endif; ?>
          <a href="officer-current-issues.php?delete_id=<?php echo $i['issue_id']; ?>" onclick="return confirm('Delete this issue?');"><button type="button" class="danger">Delete</button></a>
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
