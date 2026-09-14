<?php
 

require_once __DIR__ . '/db_connect.php'; // expects $conn (mysqli)
require_once __DIR__ . '/categories.php';
require_once __DIR__ . '/require_councillor.php';
$isLoggedIn = true; // guaranteed by the guard
$firstname  = htmlspecialchars($_SESSION['firstname'] ?? '');
$ward_id    = $_SESSION['ward_id'] ?? null;

// TODO: role-gate this page to ward councillor accounts, matching
// your existing role-gated display pattern.

// Only unassigned reports show on this dashboard — once linked to a
// ticket, ticket_id is set and the report drops off this list.
//
// Split into "active" (still needs work) and "completed" (Resolved or
// Closed but never got linked to a ticket) so completed ones can be
// rendered in their own section at the bottom of the page.
//
// NOTE: image_url is assumed to be the column on `reports` that holds
// the photo's path/URL. Rename it below if your schema uses something
// else (e.g. photo_url).
$ward_id = $_SESSION['ward_id'] ?? null;
if (!$ward_id) {
    die('No ward set for this session.'); // or redirect to a login page
}

$isLoggedIn = isset($_SESSION['username']);
$firstname  = $isLoggedIn ? htmlspecialchars($_SESSION['firstname'] ?? '') : '';

$sql = "SELECT report_id AS id, category_id, description, street_name, image_url,
        CONCAT(street_number, ' ', street_name, ', ', surburb) AS address,
        timestamp, current_status AS status
    FROM reports
    WHERE ticket_id IS NULL AND ward_id = ? AND current_status NOT IN ('Resolved', 'Closed')
    ORDER BY timestamp DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $ward_id);
$stmt->execute();
$result = $stmt->get_result();

$completed_sql = "SELECT report_id AS id, category_id, description, street_name, image_url,
        CONCAT(street_number, ' ', street_name, ', ', surburb) AS address,
        timestamp, current_status AS status
    FROM reports
    WHERE ticket_id IS NULL AND ward_id = ? AND current_status IN ('Resolved', 'Closed')
    ORDER BY timestamp DESC";

$completed_stmt = $conn->prepare($completed_sql);
$completed_stmt->bind_param('i', $ward_id);
$completed_stmt->execute();
$completed_result = $completed_stmt->get_result();

// Open tickets in this ward, for the "Add to Existing Ticket" dropdown
$open_tickets_stmt = $conn->prepare(
    "SELECT ticket_id, title, category_id FROM tickets
     WHERE ward_id = ? AND current_status != 'Closed'
     ORDER BY date_created DESC"
);
$open_tickets_stmt->bind_param('i', $ward_id);
$open_tickets_stmt->execute();
$open_tickets = $open_tickets_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Incoming Reports — M-Unite Councillor View</title>

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
      <a href="reports.php" class="nav-item-active">Incoming Reports</a>
      <a href="WC_make_reports.php" class="nav-item">Make Report</a>
      <a href="tickets.php" class="nav-item">Tickets</a>
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

<div class="toolbar">
    <div class="toolbar-left">
        <label class="select-all-wrap">
            <input type="checkbox" id="select-all"> Select all
        </label>
        <span id="selection-count">0 selected</span>
    </div>
    <div class="toolbar-right">
        <select id="group-by-filter">
            <option value="">No grouping</option>
            <option value="street">Group by street</option>
            <option value="category">Group by category</option>
        </select>
        <select id="type-filter">
            <option value="">All types</option>
            <?php
            $types = $conn->query("SELECT DISTINCT category_id FROM reports WHERE ticket_id IS NULL ORDER BY category_id");
            while ($t = $types->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($t['category_id']) . '">' . htmlspecialchars(category_name($t['category_id'])) . '</option>';
            }
            ?>
        </select>
        <button id="add-existing-btn" class="btn-secondary" disabled>Add to Existing Ticket</button>
        <button id="create-ticket-btn" disabled>Create Ticket from Selected</button>
    </div>
</div>
<p id="mixed-type-warning" class="mixed-type-warning hidden">
    Selected reports are of different types — a ticket can only be created from reports of one type.
</p>

<div class="report-list" id="report-list">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="report-row"
                 data-type="<?= htmlspecialchars($row['category_id']) ?>"
                 data-street="<?= htmlspecialchars($row['street_name']) ?>"
                 data-id="<?= $row['id'] ?>"
                 data-status="<?= htmlspecialchars($row['status']) ?>"
                 data-address="<?= htmlspecialchars($row['address']) ?>"
                 data-time="<?= date('d M Y, H:i', strtotime($row['timestamp'])) ?>"
                 data-image="<?= htmlspecialchars($row['image_url'] ?? '') ?>">
                <input type="checkbox" class="report-checkbox" value="<?= $row['id'] ?>">
               
                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>"><?= htmlspecialchars($row['status']) ?></span>
                <span class="report-type <?= category_class($row['category_id']) ?>"><?= htmlspecialchars(category_name($row['category_id'])) ?></span>
                <span class="report-desc" title="<?= htmlspecialchars($row['description']) ?>"><?= htmlspecialchars(mb_strimwidth($row['description'], 0, 70, '…')) ?></span>
                 <?php if (!empty($row['image_url'])): ?>
                    <img class="report-thumb" src="<?= htmlspecialchars($row['image_url']) ?>" alt="Report photo">
                <?php else: ?>
                    <span class="report-thumb report-thumb-empty" aria-hidden="true"></span>
                <?php endif; ?>
                <span class="report-address"><?= htmlspecialchars($row['address']) ?></span>
                <span class="report-time"><?= date('d M, H:i', strtotime($row['timestamp'])) ?></span>
                <span class="report-id">#<?= $row['id'] ?></span>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="empty-state">No unassigned reports right now.</p>
    <?php endif; ?>
</div>

<h3 class="section-heading">Completed Reports (<?= $completed_result->num_rows ?>)</h3>
<div class="report-list completed-section" id="completed-report-list">
    <?php if ($completed_result && $completed_result->num_rows > 0): ?>
        <?php while ($row = $completed_result->fetch_assoc()): ?>
            <div class="report-row no-checkbox"
                 data-type="<?= htmlspecialchars($row['category_id']) ?>"
                 data-street="<?= htmlspecialchars($row['street_name']) ?>"
                 data-id="<?= $row['id'] ?>"
                 data-status="<?= htmlspecialchars($row['status']) ?>"
                 data-address="<?= htmlspecialchars($row['address']) ?>"
                 data-time="<?= date('d M Y, H:i', strtotime($row['timestamp'])) ?>"
                 data-image="<?= htmlspecialchars($row['image_url'] ?? '') ?>">
                <?php if (!empty($row['image_url'])): ?>
                    <img class="report-thumb" src="<?= htmlspecialchars($row['image_url']) ?>" alt="Report photo">
                <?php else: ?>
                    <span class="report-thumb report-thumb-empty" aria-hidden="true"></span>
                <?php endif; ?>
                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>"><?= htmlspecialchars($row['status']) ?></span>
                <span class="report-type <?= category_class($row['category_id']) ?>"><?= htmlspecialchars(category_name($row['category_id'])) ?></span>
                <span class="report-desc" title="<?= htmlspecialchars($row['description']) ?>"><?= htmlspecialchars(mb_strimwidth($row['description'], 0, 70, '…')) ?></span>
                <span class="report-address"><?= htmlspecialchars($row['address']) ?></span>
                <span class="report-time"><?= date('d M, H:i', strtotime($row['timestamp'])) ?></span>
                <span class="report-id">#<?= $row['id'] ?></span>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="empty-state">No completed reports yet.</p>
    <?php endif; ?>
</div>

<!-- Create Ticket Modal -->
<div id="ticket-modal" class="modal-overlay hidden">
    <div class="modal">
        <h2>Create Ticket</h2>
        <p id="modal-report-count"></p>
        <label for="ticket-title">Title</label>
        <input type="text" id="ticket-title" placeholder="e.g. Pothole cluster — High Street">

        <label for="ticket-desc">Description</label>
        <textarea id="ticket-desc" rows="4" placeholder="Summarize the issue for this ticket..."></textarea>

        <div class="modal-actions">
            <button id="modal-cancel" class="btn-secondary">Cancel</button>
            <button id="modal-submit" class="btn-primary">Create Ticket</button>
        </div>
    </div>
</div>

<!-- Add to Existing Ticket Modal -->
<div id="existing-ticket-modal" class="modal-overlay hidden">
    <div class="modal">
        <h2>Add to Existing Ticket</h2>
        <p id="existing-modal-report-count"></p>
        <label for="existing-ticket-select">Ticket</label>
        <select id="existing-ticket-select">
            <?php if ($open_tickets && $open_tickets->num_rows > 0): ?>
                <?php while ($t = $open_tickets->fetch_assoc()): ?>
                    <option value="<?= $t['ticket_id'] ?>" data-category="<?= htmlspecialchars($t['category_id'] ?? '') ?>">
                        #<?= $t['ticket_id'] ?> — <?= htmlspecialchars($t['title']) ?> (<?= htmlspecialchars(category_name($t['category_id'])) ?>)
                    </option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="" disabled selected>No open tickets in your ward yet</option>
            <?php endif; ?>
        </select>

        <div class="modal-actions">
            <button id="existing-modal-cancel" class="btn-secondary">Cancel</button>
            <button id="existing-modal-submit" class="btn-primary" <?= (!$open_tickets || $open_tickets->num_rows === 0) ? 'disabled' : '' ?>>Add Reports</button>
        </div>
    </div>
</div>

<script src="report-common.js"></script>
<script src="reports.js"></script>
</body>
</html>
