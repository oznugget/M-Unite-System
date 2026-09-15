<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require "dbConnection.php";

// 1. Must be logged in
if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}

// 2. Must be a System Admin
$allowedRoles = ['System Admin', '4'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
    header("Location: signIN.php");
    exit();
}

// Fetch current logged-in admin details for the topbar
$admin_stmt = $conn->prepare("SELECT name, surname FROM accounts WHERE username = ?");
$admin_stmt->bind_param("s", $_SESSION['username']);
$admin_stmt->execute();
$admin_res = $admin_stmt->get_result();
$admin_row = $admin_res->fetch_assoc();
$admin_stmt->close();

$admin_name = $admin_row ? ($admin_row['name'] . ' ' . $admin_row['surname']) : $_SESSION['username'];
$admin_initials = $admin_row ? strtoupper(substr($admin_row['name'], 0, 1) . substr($admin_row['surname'], 0, 1)) : 'SA';

// Read filters from URL
$search_user     = isset($_GET['search_user']) ? trim($_GET['search_user']) : '';
$action_category = isset($_GET['action_category']) ? trim($_GET['action_category']) : 'All actions';
$date_from       = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to         = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$sort_order      = (isset($_GET['sort']) && strtolower($_GET['sort']) === 'asc') ? 'ASC' : 'DESC';

// Base SQL query using explicit joins
$baseSql = "
    SELECT 
        sa.activity_id,
        sa.username,
        at.action_code,
        at.category,
        at.description AS detail,
        sa.timestamp,
        sa.ip_address
    FROM system_activities sa
    JOIN action_types at ON sa.action_type_id = at.action_type_id
    WHERE 1=1
";

$conditions = [];
$params = [];
$types = "";

if ($search_user !== '') {
    $conditions[] = "sa.username LIKE ?";
    $params[] = '%' . $search_user . '%';
    $types .= "s";
}

if ($action_category !== '' && $action_category !== 'All actions') {
    $conditions[] = "at.category = ?";
    $params[] = $action_category;
    $types .= "s";
}

if ($date_from !== '') {
    $conditions[] = "DATE(sa.timestamp) >= ?";
    $params[] = $date_from;
    $types .= "s";
}
if ($date_to !== '') {
    $conditions[] = "DATE(sa.timestamp) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

if (!empty($conditions)) {
    $baseSql .= " AND " . implode(" AND ", $conditions);
}

$baseSql .= " ORDER BY sa.timestamp $sort_order LIMIT 100";

// Execute prepared statement to populate $logs
$stmt = $conn->prepare($baseSql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$logs = [];
while ($row = $result->fetch_assoc()) {
    $code = $row['action_code'];
    $row['is_flagged'] = (strpos($code, 'FAILED') !== false || 
                          strpos($code, 'LOCKED') !== false || 
                          strpos($code, 'DEACTIVATE') !== false ||
                          strpos($code, 'SUSPENDED') !== false ||
                          strpos($code, 'REMOVED') !== false);
    
    if (strpos($code, 'LOGIN_SUCCESS') !== false) {
        $row['tag_class'] = 'login';
    } elseif (strpos($code, 'LOGOUT') !== false) {
        $row['tag_class'] = 'logout';
    } elseif (strpos($code, 'FAILED') !== false) {
        $row['tag_class'] = 'failed';
    } elseif (strpos($code, 'LOCKED') !== false || strpos($code, 'DEACTIVATE') !== false || strpos($code, 'SUSPENDED') !== false || strpos($code, 'REMOVED') !== false) {
        $row['tag_class'] = 'locked';
    } else {
        $row['tag_class'] = 'notice';
    }

    $logs[] = $row;
}
$stmt->close();

$total_events = count($logs);
$flagged_count = array_reduce($logs, function($carry, $item) {
    return $carry + ($item['is_flagged'] ? 1 : 0); 
}, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Activity Log - M-Unite Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@500;700;800&family=TikTok+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="sysadmin_style.css">
<link rel="stylesheet" href="sysadmin_users.css">
<link rel="stylesheet" href="sysadmin_activity_logs.css">
</head>
<body class="admin-shell">

<div class="app-shell">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-mark">M</div>
      <div>
        <div class="brand-name">M-Unite</div>
        <div class="brand-sub">Makhanda &middot; Makana Local Municipality</div>
      </div>
    </div>
    <ul class="side-nav">
      <li><a href="sysadmin_home.php">Dashboard</a></li>
      <li><a href="sysadmin_users.php">User Management</a></li>
      <li><a href="sysadmin_content.php">Content Management</a></li>
      <li><a href="sysadmin_activity_logs.php" class="active">Activity Log</a></li>
      <li><a href="sysadmin_reports.php">Reports</a></li>
      <li><a href="sysadmin_account.php">My Account</a></li>
    </ul>
    <a href="#" class="logout-link" id="logout-btn">&#8630; Log Out</a>
  </aside>

  <!-- Main column -->
  <div>
    <header class="topbar">
      <div class="search-box"> Search users, notices, activity...</div>
      <div class="topbar-right">
        <div class="bell">&#128276;</div>
        <div class="avatar"><?= htmlspecialchars($admin_initials) ?></div>
        <div>
          <div class="who-name"><?= htmlspecialchars($admin_name) ?></div>
          <div class="who-role">SYSTEM ADMINISTRATOR</div>
        </div>
      </div>
    </header>

    <main class="content">
      <h1>Activity Log</h1>
      <p class="page-sub"><?= $total_events ?> events recorded in the selected period &middot; <?= $flagged_count ?> flagged security events.</p>

      <!-- Toolbar Form -->
      <form method="GET" action="sysadmin_activity_logs.php" class="log-toolbar">
        <div class="search-input">
          <span class="search-icon">&#128269;</span>
          <input type="text" name="search_user" value="<?= htmlspecialchars($search_user) ?>" placeholder="Search by username...">
        </div>

        <div class="filter-group">
          <select name="action_category" onchange="this.form.submit()">
            <option value="All actions">All actions</option>
            <option value="AUTH" <?= $action_category === 'AUTH' ? 'selected' : '' ?>>Auth</option>
            <option value="USER" <?= $action_category === 'USER' ? 'selected' : '' ?>>User</option>
            <option value="REPORT" <?= $action_category === 'REPORT' ? 'selected' : '' ?>>Report</option>
            <option value="TICKET" <?= $action_category === 'TICKET' ? 'selected' : '' ?>>Ticket</option>
            <option value="NOTICE" <?= $action_category === 'NOTICE' ? 'selected' : '' ?>>Notice</option>
            <option value="COMMENT" <?= $action_category === 'COMMENT' ? 'selected' : '' ?>>Comment</option>
          </select>
        </div>

        <div class="filter-group date-group">
          <label>From</label>
          <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
        </div>

        <div class="filter-group date-group">
          <label>To</label>
          <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
        </div>

        <input type="hidden" name="sort" value="<?= $sort_order === 'DESC' ? 'asc' : 'desc' ?>">
        <button type="submit" class="btn-sort">&#8645; <?= $sort_order === 'DESC' ? 'Newest first' : 'Oldest first' ?></button>
      </form>

      <!-- Log table -->
      <div class="table-wrap">
        <table class="log-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Action</th>
              <th>Detail</th>
              <th>Timestamp</th>
              <th>IP / Device</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($logs)): ?>
              <tr><td colspan="5" style="text-align: center; color: #6b7280; padding: 20px;">No activity logs found matching your criteria. (Try clearing the date filters).</td></tr>
            <?php else: ?>
              <?php foreach ($logs as $log): ?>
                <tr class="<?= $log['is_flagged'] ? 'log-flagged' : '' ?>">
                  <td class="log-user"><?= htmlspecialchars($log['username']) ?></td>
                  <td><span class="action-tag <?= $log['tag_class'] ?>"><?= htmlspecialchars(ucwords(strtolower(str_replace('_', ' ', str_replace('AUTH_', '', $log['action_code']))))) ?></span></td>
                  <td><?= htmlspecialchars($log['detail']) ?></td>
                  <td><?= date('d M Y, H:i', strtotime($log['timestamp'])) ?></td>
                  <td>
                    <div class="ip-main"><?= htmlspecialchars($log['ip_address']) ?></div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </main>
  </div>
</div>

<script src="nav.js"></script>
</body>
</html>