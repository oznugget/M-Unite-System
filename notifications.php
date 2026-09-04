<?php include 'notices.php'; ?>

<!Doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Notices</title>
        <link rel="stylesheet" href="notificationstyle.css">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@400;500;600;700&family=TikTok+Sans:opsz,wght@12..36,400;12..36,500;12..36,600;12..36,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
    </head>
    <body>
        <header id="notif-header">
            <p>Logo M-Unite</p>
            <nav>
                <a href="">Home</a>
                <a href="">Reports</a>
                <a href="" aria-current="page">Notices</a>
                <a href="">Map</a>
                <a href="">About Us</a>
            </nav>
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
                            <option value="Water & Sanitation">Water & Sanitation</option>
                            <option value="Roads">Roads</option>
                            <option value="Waste Management">Waste Management</option>
                            <option value="Animals">Animals</option>
                            <option value="Vandalism">Vandalism</option>
                            <option value="Transport">Transport</option>
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

            <!-- Empty State Fallbacks -->
            <p class="no-notifications" <?php echo empty($notices) ? '' : 'hidden'; ?>>No new messages</p>
            <p class="no-unread-notifications" hidden>No unread notifications</p>
        </main>
        <footer>
            <p>&copy; 2026 M-Unite</p>
        </footer>
        <script src="notifications.js"></script>
    </body>
</html>