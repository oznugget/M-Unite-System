<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Content - M-Unite Admin</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ============ Sidebar ============ -->
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-mark">M</div>
      <div>
        <div class="brand-name">M-Unite</div>
        <div class="brand-sub">Makhanda &middot; Makana Local Municipality</div>
      </div>
    </div>

    <ul class="side-nav">
      <li><a href="sysadmin_home.php" class="active">Dashboard</a></li>
      <li><a href="sysadmin_users.php">User Management</a></li>
      <li><a href="sysadmin_content.php">Content Management</a></li>
      <li><a href="sysadmin_activity_logs.php">Activity Log</a></li>
      <li><a href="sysadmin_reports.php">Reports</a></li>
      <li><a href="sysadmin_account.php">My Account</a></li>
    </ul>


    <a href="#" class="logout-link" id="logout-btn">&#8630; Log Out</a>
  </aside>>

<main class="wide">
  <h1>Content Management</h1>
  <p class="page-intro">Review notices submitted by municipal officers before they go live.</p>

  <div class="stats-grid">
    <div class="stat-card"><div class="num" id="stat-total">0</div><div class="label">Total Items</div></div>
    <div class="stat-card"><div class="num" id="stat-pending">0</div><div class="label">Awaiting Review</div></div>
    <div class="stat-card"><div class="num" id="stat-approved">0</div><div class="label">Approved</div></div>
  </div>

  <section>
    <div class="toolbar">
      <input type="text" id="search-input" placeholder="Search by title...">
      <select id="status-filter">
        <option value="all">All statuses</option>
        <option value="Pending">Pending</option>
        <option value="Approved">Approved</option>
        <option value="Rejected">Rejected</option>
      </select>
    </div>
    <ul id="content-list" class="item-list"></ul>
    <p class="empty-msg" id="empty-msg" style="display:none;">No items match your search.</p>
  </section>
</main>

<footer>&copy; M-Unite 2026 - System Administrator Portal</footer>

<script src="nav.js"></script>
<script src="admin-content.js"></script>
</body>
</html>
