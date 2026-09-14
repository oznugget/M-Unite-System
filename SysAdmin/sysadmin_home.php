<?php
require "sysadmin_home_data.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - M-Unite Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@500;700;800&family=TikTok+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="sysadmin_style.css">
</head>
<body class="admin-shell">

<div class="app-shell">

  <!-- ============ Sidebar ============ -->
  <aside class="sidebar">
   <div class="brand">
      <a href="sysadmin_home.php" class="logo-link">
        <img src="imaages/logo_1.png" alt="M-Unite Logo" class="brand-logo-image">
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

  <!--Main column-->
  <div>
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

    <main class="content">
            <h1>Good morning, <?php echo $firstName; ?></h1>

      <div class="stat-strip">
        <div class="stat-card">
          <div class="stat-label">ACTIVE USERS</div>
          <div class="stat-num"><?php echo $activeUsers; ?></div>
          <div class="stat-meta">out of <?php echo $totalUsers; ?> currently</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">PENDING APPROVALS</div>
          <div class="stat-num"><?php echo $pendingApprovals; ?></div>
          <div class="stat-meta">Oldest waiting <?php echo $oldestWaitingDays; ?> days</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">PENDING NOTICES</div>
          <div class="stat-num"><?php echo $pendingNotices; ?></div>
          <div class="stat-meta"><?php echo htmlspecialchars($noticeBreakdownText); ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">LOCKED ACCOUNTS</div>
          <div class="stat-num"><?php echo $lockedAccounts; ?></div>
          <div class="stat-meta">After 5 failed attempts</div>
        </div>
      </div>

      <!-- Panel grid -->
      <div class="panel-grid">
        <!-- Pending Users -->
        <section class="panel">
            <h2>Pending Users</h2>
            <p class="panel-sub">Most recent registrations awaiting approval</p>

            <?php if (empty($pendingUsersList)): ?>
                <p class="panel-sub">No pending users right now.</p>
            <?php else: ?>
                <?php foreach ($pendingUsersList as $user): ?>
                    <?php
                        $initials = strtoupper(substr($user['name'], 0, 1) . substr($user['surname'], 0, 1));
                        $daysAgo  = floor((time() - strtotime($user['date_registered'])) / 86400);
                        $dateText = ($daysAgo <= 0) ? 'Today' : $daysAgo . ' day' . ($daysAgo > 1 ? 's' : '') . ' ago';

                        if (in_array($user['role'], ['Ward Councillor', 'Ward councillor', '2'])) {
                            $roleLabel = 'Ward Councillor';
                            $scopeLabel = $user['ward_name'];
                        } elseif (in_array($user['role'], ['Municipal Officer', '3'])) {
                            $roleLabel = 'Municipal Officer';
                            $scopeLabel = 'Community-wide';
                        } else {
                            $roleLabel = 'System Admin';
                            $scopeLabel = 'Community-wide';
                        }
                    ?>
                    <div class="row">
                        <div class="row-avatar"><?php echo $initials; ?></div>
                        <div class="row-info">
                            <div class="row-title"><?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?></div>
                            <div class="row-meta">
                                <?php echo htmlspecialchars($roleLabel); ?> &middot;
                                <?php echo htmlspecialchars($scopeLabel); ?> &middot;
                                <?php echo $dateText; ?>
                            </div>
                        </div>
                        <div class="row-actions">
                            <button class="btn btn-approve" data-username="<?php echo htmlspecialchars($user['username']); ?>">Approve</button>
                            <button class="btn btn-reject" data-username="<?php echo htmlspecialchars($user['username']); ?>">Reject</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="panel-footer"><a href="sysadmin_users.php?status=pending">View all pending users &rarr;</a></div>
        </section>

        <!-- Locked / Suspicious Accounts -->
        <section class="panel">
          <h2>Locked / Suspicious Accounts</h2>
          <p class="panel-sub">Locked automatically after 5 failed login attempts</p>

          <div class="row flagged">
            <div class="row-avatar">!</div>
            <div class="row-info">
              <div class="row-title">pieter.vanwyk</div>
              <div class="row-meta">5 failed attempts &middot; locked 10 Sep 2026, 21:14</div>
            </div>
            <button class="btn btn-unlock">Unlock account</button>
          </div>

          <div class="row flagged">
            <div class="row-avatar">!</div>
            <div class="row-info">
              <div class="row-title">fatima.peters</div>
              <div class="row-meta">5 failed attempts &middot; locked 10 Sep 2026, 22:47</div>
            </div>
            <button class="btn btn-unlock">Unlock account</button>
          </div>

          <div class="panel-footer"><a href="admin-monitor.html?filter=locked">View security events &rarr;</a></div>
        </section>

        <!-- Pending Notices -->
        <section class="panel">
          <h2>Pending Notices</h2>
          <p class="panel-sub">Submitted by ward councillors and municipal officers</p>

          <div class="row">
            <div class="row-avatar">&#128196;</div>
            <div class="row-info">
              <div class="row-title">Water interruption: Hlalani &amp; Extension 6, 14&ndash;15 September</div>
              <div class="row-meta">Nomvula Mabhena (Ward Councillor) &middot; Ward 3 &mdash; Hlalani &middot; 10 Sep 2026</div>
            </div>
            <div class="row-actions">
              <button class="btn btn-approve">Approve</button>
              <button class="btn btn-reject">Reject</button>
            </div>
          </div>

          <div class="row">
            <div class="row-avatar">&#128196;</div>
            <div class="row-info">
              <div class="row-title">Refuse collection schedule change for Heritage Day week</div>
              <div class="row-meta">Lerato Phiri (Municipal Officer) &middot; Community-wide &middot; 10 Sep 2026</div>
            </div>
            <div class="row-actions">
              <button class="btn btn-approve">Approve</button>
              <button class="btn btn-reject">Reject</button>
            </div>
          </div>

          <div class="panel-footer"><a href="admin-content.html?status=pending">View all notices &rarr;</a></div>
        </section>

        <!-- Recent System Activity -->
        <section class="panel">
          <h2>Recent System Activity</h2>
          <p class="panel-sub">Last 10 authentication and account events</p>

          <div class="row">
            <div class="row-avatar">&#8594;</div>
            <div class="row-info">
              <div class="row-title">sipho.radebe administrator signed in</div>
              <div class="row-meta">11 Sep 2026, 08:55</div>
            </div>
          </div>

          <div class="row flagged">
            <div class="row-avatar">!</div>
            <div class="row-info">
              <div class="row-title">j.gxoyiya locked after 7 failed attempts</div>
              <div class="row-meta">11 Sep 2026, 05:02</div>
            </div>
          </div>

          <div class="panel-footer"><a href="admin-monitor.html">View full activity log &rarr;</a></div>
        </section>

      </div>
    </main>
  </div>
</div>

<script src="nav.js"></script>
</body>
</html>
