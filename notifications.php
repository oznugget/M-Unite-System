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

        <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@400;500;600;700&family=TikTok+Sans:opsz,
            wght@12..36,400;12..36,500;12..36,600;12..36,700&display=swap" rel="stylesheet">
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
            <!--Notifications headers-->
            <section class="filtering-tabs">
                <h1>My notifications</h1><span id="unread-count" name="unread-msgs"> <?php echo $unread_count; ?> unread</span>
                <p>See what you've missed out on.</p>
                <a href="notifications.php?mark_all_read=1" class="mark-all-read">Mark all read</a>

              <div class="notif-type-tab">

                <!-- TOP ROW -->
                <div class="filter-top-row">

                    <!-- Existing tabs -->
                    <nav aria-label="Notification filters">
                        <button type="button" aria-pressed="true" data-filter="all">All</button>
                        <button type="button" aria-pressed="false" data-filter="report">My Reports</button>
                        <button type="button" aria-pressed="false" data-filter="ward">My Ward</button>
                        <button type="button" aria-pressed="false" data-filter="general"> General</button>
                    </nav>

                    <!-- Unread toggle -->
                    <label class="unread-toggle">
                        <input type="checkbox">
                        <span class="toggle-slider"></span>
                        <span class="toggle-text">Unread only</span>
                    </label>

                </div>

                <!-- BOTTOM ROW -->
                <div class="filter-bottom-row">

                    <!-- Search -->
                    <div class="notification-search">
                        <span class="search-icon">⌕</span>
                        <input type="search" placeholder="Search notifications..." aria-label="Search notifications">
                    </div>

                    <!-- Category -->
                    <select class="category-filter" aria-label="Filter by category">
                        <option value="">All categories</option>
                        <option value="electricity">Electricity</option>
                        <option value="water">Water & Sanitation</option>
                        <option value="roads">Roads</option>
                        <option value="waste">Waste Management</option>
                        <option value="general">General</option>
                    </select>

                </div>

            </div>
            </section>

            <!--Today messages sections-->
            <?php if (!empty($today_notices)): ?>
            <section class="today-notices" aria-labelledby="today-heading">
                <h2 id="today-heading">Today</h2>
                <?php foreach ($today_notices as $n) { render_notice_card($n); } ?>
            </section>
            <?php endif; ?>

            <!-- YESTERDAY SECTION -->
            <?php if (!empty($yesterday_notices)): ?>
            <section class="yesterday-notices">
                <h2 id="yesterday-heading">Yesterday</h2>
                <?php foreach ($yesterday_notices as $n) { render_notice_card($n); } ?>
            </section>
            <?php endif; ?>

            <!-- EARLIER SECTION -->
            <?php if (!empty($earlier_notices)): ?>
            <section class="earlier-notices" aria-labelledby="earlier-heading">
                <h2 id="earlier-heading">Earlier</h2>
                <?php foreach ($earlier_notices as $n) { render_notice_card($n); } ?>
            </section>
            <?php endif; ?>

            <!--This part should appear when there are no notices-->
            <p class="no-notifications" <?php echo empty($notices) ? '' : 'hidden'; ?>>No new messages</p>

            <!--This part should appear only when there are unread notices-->
            <p class="no-unread-notifications" hidden>No unread notifications</p>

        </main>
        <footer>
            <p>&copy; 2026 M-Unite</p>
        </footer>
        <script src="notifications.js"></script>
    </body>
</html>