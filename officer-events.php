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

// Where flyers get saved on disk. Created automatically if it doesn't exist yet.
$uploadDir = "uploads/events/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Handle deleting an event (same $_GET?delete_id=9 pattern as officer-notices.php)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Remove the flyer file from disk too, not just the DB row
    $fetchStmt = $conn->prepare("SELECT flyer_path FROM events WHERE event_id = ?");
    $fetchStmt->bind_param("i", $delete_id);
    $fetchStmt->execute();
    $flyerRow = $fetchStmt->get_result()->fetch_assoc();
    $fetchStmt->close();

    if ($flyerRow && !empty($flyerRow['flyer_path']) && file_exists($flyerRow['flyer_path'])) {
        unlink($flyerRow['flyer_path']);
    }

    $delStmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    $delStmt->bind_param("i", $delete_id);
    if ($delStmt->execute()) {
        log_activity($conn, $username, ACTION_EVENT_DELETED);
    }
    $delStmt->close();

    header("Location: officer-events.php");
    exit;
}

// Handle posting a new event OR saving edits to an existing one
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $edit_id     = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    $title       = isset($_POST['title']) ? trim($_POST['title']) : '';
    $event_date  = isset($_POST['event_date']) ? trim($_POST['event_date']) : '';
    $location    = isset($_POST['location']) ? trim($_POST['location']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if ($title === '' || $event_date === '') {
        $updateMessage = "<p class=\"error\">Title and date are required.</p>";
    } else {
        // Flyer upload is optional - only touch flyer_path if a new file was chosen
        $flyer_path = null;
        $flyerUploaded = isset($_FILES['flyer']) && $_FILES['flyer']['error'] === UPLOAD_ERR_OK;

        if ($flyerUploaded) {
            $fileType = $_FILES['flyer']['type'];
            $fileSize = $_FILES['flyer']['size'];

            if (!in_array($fileType, $allowedTypes, true)) {
                $updateMessage = "<p class=\"error\">Flyer must be a JPG, PNG, GIF, or WEBP image.</p>";
                $flyerUploaded = false;
            } elseif ($fileSize > $maxFileSize) {
                $updateMessage = "<p class=\"error\">Flyer must be smaller than 5MB.</p>";
                $flyerUploaded = false;
            } else {
                $ext = pathinfo($_FILES['flyer']['name'], PATHINFO_EXTENSION);
                $safeName = uniqid('flyer_', true) . '.' . $ext;
                $destination = $uploadDir . $safeName;

                if (move_uploaded_file($_FILES['flyer']['tmp_name'], $destination)) {
                    $flyer_path = $destination;
                } else {
                    $updateMessage = "<p class=\"error\">Something went wrong saving the flyer. The rest of the event was not saved.</p>";
                    $flyerUploaded = false;
                }
            }
        }

        if ($updateMessage === "") {
            if ($edit_id > 0) {
                // Editing an existing event
                if ($flyer_path !== null) {
                    $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, flyer_path = ? WHERE event_id = ?");
                    $stmt->bind_param("sssssi", $title, $description, $event_date, $location, $flyer_path, $edit_id);
                } else {
                    $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ? WHERE event_id = ?");
                    $stmt->bind_param("ssssi", $title, $description, $event_date, $location, $edit_id);
                }

                if ($stmt->execute()) {
                    $updateMessage = "<p class=\"success\">Event updated!</p>";
                    log_activity($conn, $username, ACTION_EVENT_UPDATED);
                } else {
                    $updateMessage = "<p class=\"error\">Unable to update the event.</p>";
                }
                $stmt->close();
            } else {
                // Posting a brand new event
                $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, location, flyer_path, posted_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssssss", $title, $description, $event_date, $location, $flyer_path, $username);

                if ($stmt->execute()) {
                    $updateMessage = "<p class=\"success\">Event posted!</p>";
                    log_activity($conn, $username, ACTION_EVENT_CREATED);
                } else {
                    $updateMessage = "<p class=\"error\">Unable to post the event.</p>";
                }
                $stmt->close();
            }
        }
    }
}

// If we're editing, fetch that event's current data to prefill the form
$editingEvent = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $editStmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
    $editStmt->bind_param("i", $edit_id);
    $editStmt->execute();
    $editResult = $editStmt->get_result();
    if ($editResult->num_rows > 0) {
        $editingEvent = $editResult->fetch_assoc();
    }
    $editStmt->close();
}

// Stats for the cards
$total_result = $conn->query("SELECT COUNT(*) as total FROM events");
$total_events = $total_result->fetch_assoc()['total'];

$upcoming_result = $conn->query("SELECT COUNT(*) as total FROM events WHERE event_date >= CURDATE()");
$upcoming_events = $upcoming_result->fetch_assoc()['total'];

$past_events = $total_events - $upcoming_events;

// List of all events, soonest first
$events_result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
if ($events_result === FALSE) {
    die("<p class=\"error\">Unable to retrieve events!</p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Events - M-Unite Officer</title>
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
    <a href="officer-events.php" class="active">&#127881; Events</a>
    <a href="officer-current-issues.php">&#128240; Current Issues</a>
  </nav>
  <span class="account-btn"><a href="officer-account.php">&#128100; My Account</a></span>
</header>

<main class="wide">
  <h1>Community Events</h1>
  <p class="page-intro">Post upcoming events and manage what's currently listed.</p>

  <div class="stats-grid">
    <div class="stat-card"><div class="num" id="stat-total"><?php echo $total_events; ?></div><div class="label">Total Events</div></div>
    <div class="stat-card"><div class="num" id="stat-upcoming"><?php echo $upcoming_events; ?></div><div class="label">Upcoming</div></div>
    <div class="stat-card"><div class="num" id="stat-past"><?php echo $past_events; ?></div><div class="label">Past</div></div>
  </div>

  <section>
    <h2><?php echo $editingEvent ? '&#9999;&#65039; Edit Event' : '&#128221; Post a New Event'; ?></h2>
    <?php echo $updateMessage; ?>
    <form id="event-form" method="POST" action="officer-events.php" enctype="multipart/form-data">
      <?php if ($editingEvent): ?>
        <input type="hidden" name="edit_id" value="<?php echo (int)$editingEvent['event_id']; ?>">
      <?php endif; ?>

      <label>Title:
        <input type="text" name="title" maxlength="150" required
               value="<?php echo $editingEvent ? htmlspecialchars($editingEvent['title']) : ''; ?>">
      </label>

      <label>Date:
        <input type="date" name="event_date" required
               value="<?php echo $editingEvent ? htmlspecialchars($editingEvent['event_date']) : ''; ?>">
      </label>

      <label>Location:
        <input type="text" name="location" maxlength="150"
               value="<?php echo $editingEvent ? htmlspecialchars($editingEvent['location']) : ''; ?>">
      </label>

      <label>Description:
        <textarea name="description" rows="3" maxlength="500"><?php echo $editingEvent ? htmlspecialchars($editingEvent['description']) : ''; ?></textarea>
      </label>

      <label>Flyer / image<?php echo $editingEvent ? ' (leave blank to keep the current one)' : ''; ?>:
        <input type="file" name="flyer" accept="image/*">
        <span class="field-note">JPG, PNG, GIF, or WEBP. Max 5MB.</span>
      </label>

      <?php if ($editingEvent && !empty($editingEvent['flyer_path'])): ?>
        <div style="margin-bottom: 12px;">
          <img src="<?php echo htmlspecialchars($editingEvent['flyer_path']); ?>" alt="Current flyer" class="flyer-preview">
        </div>
      <?php endif; ?>

      <button type="submit"><?php echo $editingEvent ? 'Save Changes' : 'Post Event'; ?></button>
      <?php if ($editingEvent): ?>
        <a href="officer-events.php"><button type="button" class="secondary">Cancel</button></a>
      <?php endif; ?>
    </form>
  </section>

  <section>
    <h2>&#128197; Posted Events</h2>
    <ul id="event-list" class="item-list">
      <?php if ($events_result->num_rows === 0): ?>
        <p class="empty-msg">No events posted yet.</p>
      <?php endif; ?>
      <?php while ($e = $events_result->fetch_assoc()):
          $isPast = strtotime($e['event_date']) < strtotime(date('Y-m-d'));
      ?>
        <li style="display: flex; gap: 14px; align-items: flex-start;">
          <?php if (!empty($e['flyer_path'])): ?>
            <img src="<?php echo htmlspecialchars($e['flyer_path']); ?>" alt="" class="flyer-thumb">
          <?php else: ?>
            <div class="flyer-thumb flyer-thumb-empty">&#128247;</div>
          <?php endif; ?>
          <div style="flex: 1;">
            <div class="item-title">
              <?php echo htmlspecialchars($e['title']); ?>
              <span class="badge <?php echo $isPast ? 'badge-closed' : 'badge-approved'; ?>"><?php echo $isPast ? 'Past' : 'Upcoming'; ?></span>
            </div>
            <div class="item-meta"><?php echo htmlspecialchars($e['event_date']); ?><?php echo $e['location'] ? ' &middot; ' . htmlspecialchars($e['location']) : ''; ?></div>
            <p><?php echo nl2br(htmlspecialchars($e['description'])); ?></p>
            <a href="officer-events.php?edit_id=<?php echo $e['event_id']; ?>"><button type="button" class="secondary">Edit</button></a>
            <a href="officer-events.php?delete_id=<?php echo $e['event_id']; ?>" onclick="return confirm('Delete this event?');"><button type="button" class="danger">Delete</button></a>
          </div>
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
