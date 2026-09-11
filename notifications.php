<?php include 'notices_data.php'; ?>

<!Doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Notifications</title>
        
        <link rel="stylesheet" href="notificationstyle.css">
        <link rel="stylesheet" href="header_footer.css">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&family=TikTok+Sans:opsz,wght@12..36,300..900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
    </head>
    <body>
        <header class="site-header">
            <div class="logo-box">
                <img src="logo_1.png" alt="M-Unite Logo" class="logo-image">
                <a href="home.php" class="logo-link"></a>
            </div>

            <nav class="navbar">
                <a href="home.php" class="nav-item">Home</a>
                <a href="reports.html" class="nav-item">Reports</a>
                <a href="public_notices.php" class="nav-item-active">Notices</a>
                <a href="map.php" class="nav-item">Map</a>
                <a href="about_us.html" class="nav-item">About Us</a>
            </nav>
            <div class="header-right">
                    <!-- Active Bell Icon for Personal Notifications -->
                    <a href="notifications.php" class="notif-bell-btn active-bell" aria-label="Personal Notifications">
                        <span class="material-symbols-outlined">notifications</span>
                        <?php if (isset($unread_count) && $unread_count > 0): ?>
                            <span class="bell-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- Account Button -->
                    <a href="account.html" class="sign-in-btn">
                        Account <span class="material-symbols-outlined">account_circle</span>
                    </a>
                </div>
        </header>

        <main>
            <!-- Filter Section -->
            <section class="filtering-tabs">
                <h1>My notifications</h1><span id="unread-count" name="unread-msgs"><?php echo $unread_count; ?> unread</span>
                <p>See what you've missed out on.</p>
                <a href="notifications.php?mark_all_read=1" class="mark-all-read">Mark all read</a>

                <div class="notif-type-tab">
                    <div class="filter-top-row">
                        <nav aria-label="Notification filters">
                            <button type="button" aria-pressed="true" data-filter="all">All</button>
                            <button type="button" aria-pressed="false" data-filter="report">My Reports</button>
                            <button type="button" aria-pressed="false" data-filter="ward">My Ward</button>
                            <button type="button" aria-pressed="false" data-filter="general">General</button>
                        </nav>

                        <label class="unread-toggle">
                            <input type="checkbox">
                            <span class="toggle-slider"></span>
                            <span class="toggle-text">Unread only</span>
                        </label>
                    </div>

                    <div class="filter-bottom-row">
                        <div class="notification-search">
                            <span class="search-icon">⌕</span>
                            <input type="search" placeholder="Search notifications..." aria-label="Search notifications">
                        </div>

                        <select class="category-filter" aria-label="Filter by category">
                            <option value="">All categories</option>
                            <option value="Electricity">Electricity</option>
                            <option value="Water & Sanitation">Water</option>
                            <option value="Roads">Roads</option>
                            <option value="Waste Management">Waste Management</option>
                            <option value="Water & Sanitation">Sanitation</option>
                            <option value="Animals">Animals</option>
                            <option value="Vandalism">Vandalism</option>
                            <option value="Transport">Enviromental Issues</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- 1. PRIORITY SAFETY ALERTS -->
            <?php if (!empty($safety_alerts)): ?>
            <section class="notice-group alert-group" data-group="alerts">
                <h2 class="group-title priority-title">Priority Safety Alerts</h2>
                <div class="time-bucket-cards">
                    <?php foreach ($safety_alerts as $n) { render_notice_card($n); } ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- 2. PERSONAL NOTICES -->
            <section class="notice-group personal-group" data-group="personal">
                <h2 class="group-title">Personal Updates</h2>

                <p class="group-empty-message" <?php echo empty($personal_notices) ? '' : 'hidden'; ?>>No new messages</p>

                <?php if (!empty($personal_grouped['today'])): ?>
                <div class="time-bucket today-notices">
                    <h3>Today</h3>
                    <?php foreach ($personal_grouped['today'] as $n) { render_notice_card($n); } ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($personal_grouped['yesterday'])): ?>
                <div class="time-bucket yesterday-notices">
                    <h3>Yesterday</h3>
                    <?php foreach ($personal_grouped['yesterday'] as $n) { render_notice_card($n); } ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($personal_grouped['earlier'])): ?>
                <div class="time-bucket earlier-notices">
                    <h3>Earlier</h3>
                    <?php foreach ($personal_grouped['earlier'] as $n) { render_notice_card($n); } ?>
                </div>
                <?php endif; ?>
            </section>

            <!-- 3. TOWN-WIDE NOTICES -->
            <section class="notice-group townwide-group" data-group="townwide">
                <h2 class="group-title">Town-Wide Notices</h2>

                <p class="group-empty-message" <?php echo empty($townwide_notices) ? '' : 'hidden'; ?>>No new messages</p>

                <?php if (!empty($townwide_grouped['today'])): ?>
                <div class="time-bucket today-notices">
                    <h3>Today</h3>
                    <?php foreach ($townwide_grouped['today'] as $n) { render_notice_card($n); } ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($townwide_grouped['yesterday'])): ?>
                <div class="time-bucket yesterday-notices">
                    <h3>Yesterday</h3>
                    <?php foreach ($townwide_grouped['yesterday'] as $n) { render_notice_card($n); } ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($townwide_grouped['earlier'])): ?>
                <div class="time-bucket earlier-notices">
                    <h3>Earlier</h3>
                    <?php foreach ($townwide_grouped['earlier'] as $n) { render_notice_card($n); } ?>
                </div>
                <?php endif; ?>
            </section>
            
            <p class="single-tab-empty-message" hidden>No new messages</p>
        </main>

        <footer class="site-footer">
            <div class="footer-bottom">
                <p>&copy; M-Unite 2026</p>
            </div>
        </footer>

        <script src="notifications.js"></script>

        <!-- Notice Modal Overlay -->
        <div id="notice-modal-overlay" class="modal-overlay" aria-hidden="true">
            <div class="modal-card" role="dialog" aria-modal="true">
                <button type="button" class="modal-close-btn" onclick="closeNoticeModal()" aria-label="Close notice">
                    <span class="material-symbols-outlined">close</span>
                </button>
                <div class="modal-header">
                    <div class="notif-icon">
                        <span class="material-symbols-outlined" id="modal-icon">notifications</span>
                    </div>
                    <div class="modal-header-info">
                        <h2 id="modal-title" class="notif-title"></h2>
                        <span id="modal-time" class="time"></span>
                    </div>
                </div>
                <div class="modal-body">
                    <p id="modal-content"></p>
                </div>
                <footer class="notif-footer" id="modal-footer">
                    <span id="modal-category" class="notif-category"></span>
                    <span id="modal-author-wrapper">
                        <span class="notif-separator">·</span>
                        <span id="modal-author" class="notif-author"></span>
                    </span>
                </footer>
            </div>
        </div>
    </body>
</html>