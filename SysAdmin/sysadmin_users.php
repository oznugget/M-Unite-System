<?php
require "sysadmin_user_actions.php";
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
      <link rel="stylesheet" href="manager_panel.css">
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
                <a href="sysadmin_home.php" class="logo-link">
                  <img src="images/logo_1.png" alt="M-Unite Logo" class="brand-logo-image">
                </a>
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

                      </td>
                      <td><?php echo htmlspecialchars($u['username']); ?></td>
                      <td><?php echo htmlspecialchars($u['role_label']); ?></td>
                      <td><?php echo htmlspecialchars($u['ward_label']); ?></td>
                      <td><span class="status-pill <?php echo $u['status_class']; ?>">&#9679; <?php echo $u['status_label']; ?></span></td>
                      <td><?php echo date('d M Y', strtotime($u['date_registered'])); ?></td>
                      <td><a href="?<?php echo http_build_query(array_merge($_GET, ['username' => $u['username']])); ?>" class="manage-link">Manage &rarr;</a></td>
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
    </div>
                <!-- Manage User Side Panel -->
      <?php if ($selectedUser): ?>
      <div class="panel-overlay open" id="managePanelOverlay">
        <aside class="manage-panel" id="managePanel">
          <div class="manage-panel-header">
            <div class="manage-panel-avatar"><?php echo htmlspecialchars($selectedUser['initials']); ?></div>
            <div class="manage-panel-heading">
              <div class="manage-panel-name"><?php echo htmlspecialchars($selectedUser['name'] . ' ' . $selectedUser['surname']); ?></div>
              <div class="manage-panel-sub"><?php echo htmlspecialchars($selectedUser['role_label']); ?></div>
            </div>
          <!-- PHP-generated link used to close a panel while keeping the other URL parameters intact-->
            <a href="?<?php echo http_build_query(array_diff_key($_GET, ['username' => ''])); ?>" class="manage-panel-close" id="managePanelClose">&times;</a>
          </div>

          <div class="manage-panel-body">
            <span class="status-pill <?php echo $selectedUser['status_class']; ?>">&#9679; <?php echo htmlspecialchars($selectedUser['status_label']); ?></span>

            <div class="manage-detail-row">
              <span class="manage-detail-icon">&#9993;</span>
              <div>
                <div class="manage-detail-label">Email</div>
                <div class="manage-detail-value"><?php echo htmlspecialchars($selectedUser['username']); ?></div>
              </div>
            </div>

            <div class="manage-detail-row">
              <span class="manage-detail-icon">&#9742;</span>
              <div>
                <div class="manage-detail-label">Phone</div>
                <div class="manage-detail-value"><?php echo htmlspecialchars($selectedUser['phone_display'] ?: '—'); ?></div>
              </div>
            </div>

            <div class="manage-detail-row">
              <span class="manage-detail-icon">&#128100;</span>
              <div>
                <div class="manage-detail-label">Ward</div>
                <div class="manage-detail-value"><?php echo htmlspecialchars($selectedUser['ward_label']); ?></div>
              </div>
            </div>

            <div class="manage-detail-row">
              <span class="manage-detail-icon">&#128337;</span>
              <div>
                <div class="manage-detail-label">Registered</div>
                <div class="manage-detail-value"><?php echo date('d M Y', strtotime($selectedUser['date_registered'])); ?></div>
              </div>
            </div>

            <div class="manage-detail-row">
              <span class="manage-detail-icon">&#128337;</span>
              <div>
                <div class="manage-detail-label">Last login</div>
                <div class="manage-detail-value"><?php echo $selectedUser['last_login'] ? date('d M Y, H:i', strtotime($selectedUser['last_login'])) : '—'; ?></div>
              </div>
            </div>


      <!--Actions performed by sysadmin, approve registration, lock, suspend, actibvate and remove-->
            <div class="manage-actions-label">ADMIN ACTIONS</div>

              <?php if ($selectedUser['is_registered'] == 0): ?>
                <form method="post" action="sysadmin_users.php">
                  <input type="hidden" name="action" value="approve">
                  <input type="hidden" name="username" value="<?php echo htmlspecialchars($selectedUser['username']); ?>">
                  <input type="hidden" name="return_search" value="<?php echo htmlspecialchars($search); ?>">
                  <input type="hidden" name="return_role" value="<?php echo htmlspecialchars($roleFilter); ?>">
                  <input type="hidden" name="return_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                  <input type="hidden" name="return_page" value="<?php echo (int) $page; ?>">
                  <button type="submit" class="btn-approve-full">&check; Approve registration</button>
                </form>
              <?php endif; ?>

              <form method="post" action="sysadmin_users.php" style="margin-bottom:12px;">
                <input type="hidden" name="action" value="assign_role">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($selectedUser['username']); ?>">
                <input type="hidden" name="return_search" value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="return_role" value="<?php echo htmlspecialchars($roleFilter); ?>">
                <input type="hidden" name="return_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                <input type="hidden" name="return_page" value="<?php echo (int) $page; ?>">
                <div class="manage-detail-label" style="margin-bottom:6px;">Assign role</div>
                <select name="new_role" style="width:100%; padding:8px; border-radius:8px; border:1px solid #e5e7eb; margin-bottom:8px;">
                  <?php foreach (['Community Member', 'Ward Councillor', 'Municipal Officer', 'System Admin'] as $r): ?>
                    <option value="<?php echo htmlspecialchars($r); ?>" <?php echo ($selectedUser['role_label'] === $r) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($r); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-action-outline" style="width:100%;">&#128100; Assign role</button>
              </form>

    <div class="manage-actions-grid">
      <form method="post" action="sysadmin_users.php">
        <input type="hidden" name="action" value="unlock">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($selectedUser['username']); ?>">
        <input type="hidden" name="return_search" value="<?php echo htmlspecialchars($search); ?>">
        <input type="hidden" name="return_role" value="<?php echo htmlspecialchars($roleFilter); ?>">
        <input type="hidden" name="return_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
        <input type="hidden" name="return_page" value="<?php echo (int) $page; ?>">
        <button type="submit" class="btn-action-outline" style="width:100%;">&#128274; Unlock</button>
      </form>

        <?php if ($selectedUser['active_status'] == 1): ?>
        <form method="post" action="sysadmin_users.php" onsubmit="return confirm('Suspend this account?');">
          <input type="hidden" name="action" value="suspend">
          <input type="hidden" name="username" value="<?php echo htmlspecialchars($selectedUser['username']); ?>">
          <input type="hidden" name="return_search" value="<?php echo htmlspecialchars($search); ?>">
          <input type="hidden" name="return_role" value="<?php echo htmlspecialchars($roleFilter); ?>">
          <input type="hidden" name="return_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
          <input type="hidden" name="return_page" value="<?php echo (int) $page; ?>">
          <button type="submit" class="btn-action-outline" style="width:100%;">&#9201; Suspend</button>
        </form>
      <?php else: ?>
        <form method="post" action="sysadmin_users.php">
          <input type="hidden" name="action" value="activate">
          <input type="hidden" name="username" value="<?php echo htmlspecialchars($selectedUser['username']); ?>">
          <input type="hidden" name="return_search" value="<?php echo htmlspecialchars($search); ?>">
          <input type="hidden" name="return_role" value="<?php echo htmlspecialchars($roleFilter); ?>">
          <input type="hidden" name="return_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
          <input type="hidden" name="return_page" value="<?php echo (int) $page; ?>">
          <button type="submit" class="btn-action-outline" style="width:100%;">&#9989; Activate</button>
        </form>
      <?php endif; ?>

      <form method="post" action="sysadmin_users.php" onsubmit="return confirm('Remove this user? This deactivates their account and hides it from the list, but does not permanently delete their record.');" style="grid-column: 1 / -1;">
        <input type="hidden" name="action" value="remove">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($selectedUser['username']); ?>">
        <input type="hidden" name="return_search" value="<?php echo htmlspecialchars($search); ?>">
        <input type="hidden" name="return_role" value="<?php echo htmlspecialchars($roleFilter); ?>">
        <input type="hidden" name="return_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
        <input type="hidden" name="return_page" value="<?php echo (int) $page; ?>">
        <button type="submit" class="btn-action-outline danger" style="width:100%;">&#128465; Remove</button>
      </form>
    </div>
      <?php endif; ?>
  </body>
</html>