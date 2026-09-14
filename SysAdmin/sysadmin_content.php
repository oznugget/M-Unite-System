<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Content Approval - M-Unite Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@500;700;800&family=TikTok+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="sysadmin_style.css">
<link rel="stylesheet" href="sysadmin_content.css">
</head>
<body class="admin-shell">

<div class="app-shell">

  <!-- ============ Sidebar ============ -->
  <aside class="sidebar">
    <div class="brand">
      <a href="sysadmin_home.php" class="logo-link">
        <img src="images/logo_1.png" alt="M-Unite Logo" class="brand-logo-image">
      </a>
    </div>

    <ul class="side-nav">
      <li><a href="sysadmin_home.php">Dashboard</a></li>
      <li><a href="sysadmin_users.php">User Management</a></li>
      <li><a href="sysadmin_content.php" class="active">Content Management</a></li>
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
        <div class="avatar">SR</div>
        <div>
          <div class="who-name">Sipho Radebe</div>
          <div class="who-role">SYSTEM ADMINISTRATOR</div>
        </div>
      </div>
    </header>

    <main class="content">
      <h1>Content Approval</h1>

      <div class="content-layout">

        <!-- Left: list -->
        <div class="content-list-col">

          <!-- Tabs + search -->
          <div class="content-toolbar">
            <div class="content-tabs">
              <button class="tab-btn active">Pending <span class="tab-count">4</span></button>
              <button class="tab-btn">Approved <span class="tab-count">2</span></button>
              <button class="tab-btn">Rejected <span class="tab-count">1</span></button>
            </div>
            <div class="search-input">
              <span class="search-icon">&#128269;</span>
              <input type="text" placeholder="Search notices or authors...">
            </div>
          </div>

          <!-- Notice cards -->
          <div class="notice-card">
            <div class="notice-card-top">
              <div>
                <div class="notice-title">Water interruption: Hlalani &amp; Extension 6, 14&ndash;15 September</div>
                <div class="notice-meta">Nomvula Mabhena &middot; Ward Councillor &middot; Ward 3 &mdash; Hlalani &middot; 10 Sep 2026</div>
              </div>
            </div>
            <p class="notice-excerpt">Residents of Hlalani and Extension 6 are advised that the Waainek Water Treatment Works will be shut down for emergency pipeline repairs from 06:00 on Monday 14 September until approximately 18:00&hellip;</p>
            <div class="notice-actions">
              <button class="btn-outline">&#128065; Preview</button>
              <button class="btn-approve-pill">&check; Approve</button>
              <button class="btn-reject-pill">&#10005; Reject</button>
            </div>
          </div>

          <div class="notice-card">
            <div class="notice-card-top">
              <div>
                <div class="notice-title">Refuse collection schedule change for Heritage Day week</div>
                <div class="notice-meta">Lerato Phiri &middot;  Municipal Officer  &middot; 10 Sep 2026</div>
              </div>
            </div>
            <p class="notice-excerpt">Due to the Heritage Day public holiday on Thursday 24 September, refuse collection for all wards will move forward by one day for that week. Please place bins on the kerb by 06:00. Normal schedules&hellip;</p>
            <div class="notice-actions">
              <button class="btn-outline">&#128065; Preview</button>
              <button class="btn-approve-pill">&check; Approve</button>
              <button class="btn-reject-pill">&#10005; Reject</button>
            </div>
          </div>

          <div class="notice-card">
            <div class="notice-card-top">
              <div>
                <div class="notice-title">Ward 1 community meeting: Joza street lighting upgrade</div>
                <div class="notice-meta">Andile Ngcaba &middot;  Ward Councillor &middot; Ward 1 &mdash; Joza &middot; 09 Sep 2026</div>
              </div>
            </div>
            <p class="notice-excerpt">A community meeting will be held at Joza Indoor Sports Centre on Saturday 19 September at 10:00 to discuss the high-mast lighting upgrade and the proposed phasing of installations along Extension 9.</p>
            <div class="notice-actions">
              <button class="btn-outline">&#128065; Preview</button>
              <button class="btn-approve-pill">&check; Approve</button>
              <button class="btn-reject-pill">&#10005; Reject</button>
            </div>
          </div>

          <div class="notice-card">
            <div class="notice-card-top">
              <div>
                <div class="notice-title">Pothole repair programme: Currie Street and African Street</div>
                <div class="notice-meta">Sibusiso Ndlovu &middot; Municipal Office &middot;  Ward 8 &mdash; CBD &amp; Currie Street &middot; 08 Sep 2026</div>
              </div>
            </div>
            <p class="notice-excerpt">The roads maintenance team will begin resurfacing sections of Currie Street and African Street from 16 September. Expect single-lane closures between 08:00 and 16:00 on weekdays.</p>
            <div class="notice-actions">
              <button class="btn-outline">&#128065; Preview</button>
              <button class="btn-approve-pill">&check; Approve</button>
              <button class="btn-reject-pill">&#10005; Reject</button>
            </div>
          </div>

        </div>

        <!-- Right: preview panel -->
        <aside class="notice-preview-col">
          <section class="panel">
            <h2>Notice preview</h2>
          
            <div class="preview-title">Refuse collection schedule change for Heritage Day week</div>
            <div class="preview-meta">Lerato Phiri &middot; Municipal Officer &middot; Community-wide</div>

            <p class="preview-body">Due to the Heritage Day public holiday on Thursday 24 September, refuse collection for all wards will move forward by one day for that week. Please place bins on the kerb by 06:00. Normal schedules resume Monday 28 September.</p>

            <div class="preview-footer">
              <div>
                <div class="preview-footer-label">SUBMITTED</div>
                <div class="preview-footer-value">10 Sep 2026</div>
              </div>
            </div>
          </section>
        </aside>

      </div>
    </main>
  </div>
</div>

<script src="nav.js"></script>
</body>
</html>