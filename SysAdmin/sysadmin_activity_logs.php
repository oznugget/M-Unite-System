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
      <li><a href="sysadmin_home.php">Dashboard</a></li>
      <li><a href="sysadmin_users.php">User Management</a></li>
      <li><a href="sysadmin_content.php">Content Management</a></li>
      <li><a href="sysadmin_activity_logs.php" class="active">Activity Log</a></li>
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
        <div class="avatar">SR</div>
        <div>
          <div class="who-name">Sipho Radebe</div>
          <div class="who-role">SYSTEM ADMINISTRATOR</div>
        </div>
      </div>
    </header>

    <main class="content">
      <h1>Activity Log</h1>
      <p class="page-sub">14 events recorded in the selected period &middot; 4 flagged security events.</p>

      <!-- Toolbar -->
      <div class="log-toolbar">
        <div class="search-input">
          <span class="search-icon">&#128269;</span>
          <input type="text" placeholder="Search by username...">
        </div>

        <div class="filter-group">
          <select>
            <option>All actions</option>
            <option>Login</option>
            <option>Logout</option>
            <option>Failed Attempt</option>
            <option>Account Locked</option>
            <option>Notice Approved</option>
          </select>
        </div>

        <div class="filter-group date-group">
          <label>From</label>
          <input type="date" value="2026-09-08">
        </div>

        <div class="filter-group date-group">
          <label>To</label>
          <input type="date" value="2026-09-11">
        </div>

        <button class="btn-sort">&#8645; Newest first</button>
      </div>

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
            <tr>
              <td class="log-user">sipho.radebe</td>
              <td><span class="action-tag login">Login</span></td>
              <td>Administrator signed in</td>
              <td>11 Sep 2026, 08:55</td>
              <td>
                <div class="ip-main">196.21.44.10</div>
    
              </td>
            </tr>

            <tr class="log-flagged">
              <td class="log-user">j.gxoyiya</td>
              <td><span class="action-tag locked">Account Locked</span></td>
              <td>Locked after 7 failed attempts</td>
              <td>11 Sep 2026, 05:02</td>
              <td>
                <div class="ip-main">105.4.223.18</div>
                
              </td>
            </tr>

            <tr class="log-flagged">
              <td class="log-user">j.gxoyiya</td>
              <td><span class="action-tag failed">Failed Attempt</span></td>
              <td>Incorrect password (attempt 7)</td>
              <td>11 Sep 2026, 05:01</td>
              <td>
                <div class="ip-main">105.4.223.18</div>
                
              </td>
            </tr>

            <tr>
              <td class="log-user">a.mtshali</td>
              <td><span class="action-tag login">Login</span></td>
              <td>Ward councillor signed in</td>
              <td>11 Sep 2026, 08:12</td>
              <td>
                <div class="ip-main">41.13.88.9</div>
                
              </td>
            </tr>

            <tr>
              <td class="log-user">lerato.phiri</td>
              <td><span class="action-tag notice">Notice Approved</span></td>
              <td>Notice N-314 published community-wide</td>
              <td>11 Sep 2026, 07:30</td>
              <td>
                <div class="ip-main">196.21.44.12</div>
                
              </td>
            </tr>

            <tr>
              <td class="log-user">andile.ngcaba</td>
              <td><span class="action-tag login"> Login</span></td>
              <td>Ward councillor signed in</td>
              <td>11 Sep 2026, 07:42</td>
              <td>
                <div class="ip-main">197.98.201.4</div>
                
              </td>
            </tr>

            <tr class="log-flagged">
              <td class="log-user">fatima.peters</td>
              <td><span class="action-tag locked"> Account Locked</span></td>
              <td>Locked after 5 failed attempts</td>
              <td>10 Sep 2026, 22:47</td>
              <td>
                <div class="ip-main">41.121.9.77</div>
                <div class="ip-sub"></div>
              </td>
            </tr>

            <tr class="log-flagged">
              <td class="log-user">pieter.vanwyk</td>
              <td><span class="action-tag failed">Failed Attempt</span></td>
              <td>Incorrect password (attempt 5)</td>
              <td>10 Sep 2026, 21:14</td>
              <td>
                <div class="ip-main">197.88.14.203</div>
                
              </td>
            </tr>

            <tr>
              <td class="log-user">bongiwe.sithole</td>
              <td><span class="action-tag logout">Logout</span></td>
              <td>Session ended</td>
              <td>10 Sep 2026, 19:40</td>
              <td>
                <div class="ip-main">102.65.7.221</div>
                
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination">
        <a href="#" class="page-btn page-prev disabled">Prev</a>
        <a href="#" class="page-btn active">1</a>
        <a href="#" class="page-btn">2</a>
        <a href="#" class="page-btn page-next">Next</a>
      </div>
    </main>
  </div>
</div>

<script src="nav.js"></script>
</body>
</html>