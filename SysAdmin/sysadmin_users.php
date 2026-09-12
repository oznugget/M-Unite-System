<?php
require "sysadmin_home_data.php";
require "sysadmin_users_data.php";
?>

<html>
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Dashboard - M-Unite Admin</title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@500;700;800&family=TikTok+Sans:wght@400;500;600&display=swap" rel="stylesheet">

      <link rel="stylesheet" href="sysadmin_users.css">
      <link rel="stylesheet" href="sysadmin_style.css">
    </head>

    <body class="admin-shell">
          <header class="topbar">
            <div class="search-box"> Search users, notices, activity...</div>
            <div class="topbar-right">
              <div class="bell">&#128276;</div>
              <div class="avatar"><?php echo $fullInitials; ?></div>
              <div>
                <div class="who-name"><?php echo htmlspecialchars($admin['name'] . ' ' . $admin['surname']); ?></div>
                <div class="who-role">SYSTEM ADMINISTRATOR</div>
              </div>
          </header>
          
      <div class="app-shell">
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

        <main class="content">
          <h1>All Users</h1>
          <p class="page-sub">Every registered account on M-Unite. Click a row to open the full account record and admin actions.</p>

          <form method="get" class="users-toolbar">
            <div class="search-input">
              <span class="search-icon">&#128269;</span>
              <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="filter-group">
              <label>Role</label>
              <select name="role" onchange="this.form.submit()">
                <?php foreach (['All roles', 'Community Member', 'Ward Councillor', 'Municipal Officer', 'System Admin'] as $r): ?>
                  <option value="<?php echo htmlspecialchars($r); ?>" <?php echo ($roleFilter === $r) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($r); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="filter-group">
              <label>Status</label>
              <select name="status" onchange="this.form.submit()">
                <?php foreach (['All statuses', 'Active', 'Pending', 'Locked'] as $s): ?>
                  <option value="<?php echo htmlspecialchars($s); ?>" <?php echo ($statusFilter === $s) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($s); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <button type="submit" style="display:none;">Apply</button>
            <div class="results-count"><?php echo count($usersList); ?> of <?php echo $totalUsers; ?> accounts</div>
          </form>

          <div class="table-wrap">
            <table class="users-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Ward</th>
                  <th>Status</th>
                  <th>Registered</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($usersList)): ?>
                  <tr><td colspan="7" style="text-align:center; padding: 24px; color:#6b7280;">No matching users found.</td></tr>
                <?php else: ?>
                  <?php foreach ($usersList as $u): ?>
                    <tr>
                      <td>
                        <div class="user-name"><?php echo htmlspecialchars($u['name'] . ' ' . $u['surname']); ?></div>
                        <div class="user-id"><?php echo htmlspecialchars($u['username']); ?></div>
                      </td>
                      <td><?php echo htmlspecialchars($u['username']); ?></td>
                      <td><?php echo htmlspecialchars($u['role_label']); ?></td>
                      <td><?php echo htmlspecialchars($u['ward_label']); ?></td>
                      <td><span class="status-pill <?php echo $u['status_class']; ?>">&#9679; <?php echo $u['status_label']; ?></span></td>
                      <td><?php echo date('d M Y', strtotime($u['date_registered'])); ?></td>
                      <td><a href="#" class="manage-link" data-username="<?php echo htmlspecialchars($u['username']); ?>">Manage &rarr;</a></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php
            // Helper to build a page URL that keeps the current search/role/status filters
            function pageUrl($p, $search, $roleFilter, $statusFilter) {
                return '?' . http_build_query([
                    'page'   => $p,
                    'search' => $search,
                    'role'   => $roleFilter,
                    'status' => $statusFilter,
                ]);
            }
          ?>
          <?php if ($totalPages > 1): ?>
            <div class="pagination">
              <a href="<?php echo pageUrl(max(1, $page - 1), $search, $roleFilter, $statusFilter); ?>"
                class="page-btn page-prev <?php echo ($page <= 1) ? 'disabled' : ''; ?>">&larr; Prev</a>

              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == 1 || $i == $totalPages || abs($i - $page) <= 1): ?>
                  <a href="<?php echo pageUrl($i, $search, $roleFilter, $statusFilter); ?>"
                    class="page-btn <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php elseif (abs($i - $page) == 2): ?>
                  <span class="page-ellipsis">...</span>
                <?php endif; ?>
              <?php endfor; ?>

              <a href="<?php echo pageUrl(min($totalPages, $page + 1), $search, $roleFilter, $statusFilter); ?>"
                class="page-btn page-next <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">Next &rarr;</a>
            </div>
          <?php endif; ?>
        </main>
        </main>
    </div>
  </body>
</html>