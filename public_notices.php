<?php include 'public_notices_data.php'; 

include 'alert_banner_data.php';
include 'alert_banner.php';

$active_alerts = get_active_alerts($conn); // public scope, no username
?>

<!Doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Public Notices</title>
        <link rel="stylesheet" href="public_notices.css">
        <link rel="stylesheet" href="alert_banner.css">
        <link rel="stylesheet" href="header_footer.css">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@400;500;600;700&family=TikTok+Sans:opsz,wght@12..36,400;12..36,500;12..36,600;12..36,700&display=swap" rel="stylesheet">
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
                <a href="public_notices.php" class="nav-item-active" aria-current="page">Notices</a>
                <a href="map.php" class="nav-item">Map</a>
                <a href="about_us.html" class="nav-item">About Us</a>
            </nav>
            <div class="header-right">
                <!-- Notification Bell -->
                <a href="notifications.php" class="notif-bell-btn" aria-label="Personal Notifications">
                    <span class="material-symbols-outlined">notifications</span>
                </a>

                <!-- Account Button -->
                <a href="account.html" class="sign-in-btn">
                    Account <span class="material-symbols-outlined">account_circle</span>
                </a>
            </div>
        </header>

        <?php render_alert_banner($active_alerts); ?>
        <main>
            <section class="public-notices-intro">
                <h1>Town notices</h1>
                <p>See what's happening around town.</p>
            </section>

            <section class="notice-group">
                <?php if (!empty($public_notices_grouped['today'])): ?>
                <div class="time-bucket today-notices">
                    <h3>Today</h3>
                    <?php foreach ($public_notices_grouped['today'] as $n) { render_public_notice_card($n); } ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($public_notices_grouped['yesterday'])): ?>
                <div class="time-bucket yesterday-notices">
                    <h3>Yesterday</h3>
                    <?php foreach ($public_notices_grouped['yesterday'] as $n) { render_public_notice_card($n); } ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($public_notices_grouped['earlier'])): ?>
                <div class="time-bucket earlier-notices">
                    <h3>Earlier</h3>
                    <?php foreach ($public_notices_grouped['earlier'] as $n) { render_public_notice_card($n); } ?>
                </div>
                <?php endif; ?>
            </section>

            <p class="no-notices" <?php echo empty($public_notices) ? '' : 'hidden'; ?>>No notices right now</p>
        </main>

        <footer>
            <p>&copy; 2026 M-Unite</p>
        </footer>
        <script src="alert_banner.js"></script>
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