<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Monitoring - M-Unite Admin</title>
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
    </aside>

<main class="wide">
  <h1>System Monitoring</h1>
  <p class="page-intro">Recent account activity and login events.</p>

  <div class="stats-grid">
    <div class="stat-card"><div class="num" id="stat-total">0</div><div class="label">Total Events</div></div>
    <div class="stat-card"><div class="num" id="stat-logins">0</div><div class="label">Logins Today</div></div>
    <div class="stat-card"><div class="num" id="stat-deletes">0</div><div class="label">Delete Actions</div></div>
  </div>

  <section>
    <div class="toolbar">
      <input type="text" id="search-input" placeholder="Search by username...">
      <select id="action-filter">
        <option value="all">All actions</option>
        <option value="Login">Login</option>
        <option value="Logout">Logout</option>
        <option value="Delete">Delete</option>
      </select>
    </div>
    <table>
      <thead>
        <tr>
          <th onclick="sortBy('user')">User &#8597;</th>
          <th onclick="sortBy('action')">Action &#8597;</th>
          <th onclick="sortBy('time')">Time &#8597;</th>
        </tr>
      </thead>
      <tbody id="log-body"></tbody>
    </table>
    <p class="empty-msg" id="empty-msg" style="display:none;">No events match your search.</p>
  </section>
</main>

<footer>&copy; M-Unite 2026 - System Administrator Portal</footer>

<script src="nav.js"></script>
<script src="admin-monitor.js"></script>
</body>
</html>
