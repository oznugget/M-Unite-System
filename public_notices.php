<?php
session_start(); 

// Ensure database connection is loaded first
include_once 'dbConnection.php';
include 'global_badge.php';

include 'public_notices_data.php'; 
include 'alert_banner_data.php';
include 'alert_banner.php';

$active_alerts = get_active_alerts($conn); // public scope, no username

$isLoggedIn = isset($_SESSION['username']);
$firstname  = $isLoggedIn ? htmlspecialchars($_SESSION['firstname']) : '';
$username   = $isLoggedIn ? $_SESSION['username'] : null;

// Get the badge count using our centralized function
$global_unread_count = get_global_unread_count($conn);
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
                <a href="CommReports.html" class="nav-item">Reports</a>
                <a href="public_notices.php" class="nav-item-active">Notices</a>
                <a href="map.php" class="nav-item">Map</a>
                <a href="about_us.html" class="nav-item">About Us</a>
            </nav>
            <div class="header-right">
                    <!-- Active Bell Icon for Personal Notifications -->
                   <a href="check_notifications.php" class="notif-bell-btn active-bell" aria-label="Personal Notifications">
                    <span class="material-symbols-outlined">notifications</span>
                    <?php if (isset($global_unread_count) && $global_unread_count > 0): ?>
                        <span class="bell-badge"><?php echo $global_unread_count; ?></span>
                    <?php endif; ?>
                </a>

                    <!-- Account Button -->
                    <a href="account.html" class="sign-in-btn">
                        <?php echo $firstname?><span class="material-symbols-outlined">account_circle</span>
                    </a>
                </div>
        </header>

        <?php render_alert_banner($active_alerts); ?>
        <main>
            <section class="public-notices-intro">
                <h1>Town notices</h1>
                <p>See what's happening around town.</p>
            </section>
            
                    <!--Town Notices Sections-->
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

            <section class="notice-group events-group">
            <h2 class="group-title">Events</h2>
            <?php if (!empty($event_notices)): ?>
                <div class="time-bucket-cards">
                    <?php foreach ($event_notices as $n) { render_public_notice_card($n); } ?>
                </div>
            <?php else: ?>
                <p class="group-empty-message">No upcoming events right now.</p>
            <?php endif; ?>
        </section>

        <!-- NEW: CURRENT ISSUES SECTION -->
        <section class="notice-group issues-group">
            <h2 class="group-title" id = ci>Current Issues</h2>
            <?php if (!empty($current_issues)): ?>
                <div class="time-bucket-cards">
                    <?php foreach ($current_issues as $n) { render_public_notice_card($n); } ?>
                </div>
            <?php else: ?>
                <p class="group-empty-message">No active current issues reported.</p>
            <?php endif; ?>
        </section>
        </main>

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
                   <!-- FOOTER -->
            <footer class="site-footer">

                <img src="images/footerimgresponsive1.png" alt="Makhanda skyline" class="footer-skyline-mobile">
                <img src = "images/footer_img.png" alt = "Makhanda skyline" id = "footerimg">


                <div class="footer-top">
                <!-- Left Info -->
                <div class="footer-col footer-about">
                    <div class="footer-logo-box">
                        <img src="images\logo_1.png" alt="M-Unite Logo" class="footer-logo">
                    </div>
                    <p>Connecting residents of Makhanda and the Municipality, enabling you to share and report municipal issues.</p>
                </div>

                <!-- Pages Column -->
                <div class="footer-col">
                    <h4>Pages</h4>
                    <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="reports.html">Reports</a></li>
                    <li><a href="notices.html">Notices</a></li>
                    <li><a href="map.html">Map</a></li>
                    <li><a href="about.html">About Us</a></li>
                    </ul>
                </div>

                <!-- Connect Column -->
                <div class="footer-col">
                    <h4>Connect</h4>
                    <ul>
                    <li><a href="#">Report Website Bugs</a></li>
                    <li><a href="#">Volunteer</a></li>
                    <li><a href="mailto:info@munite.co.za">info@munite.co.za</a></li>
                    <li><a href="tel:+27000000000">+27 000000000</a></li>
                    </ul>
                </div>

                <!-- Resources Column -->
                <div class="footer-col">
                    <h4>Resources</h4>
                    <ul>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Documentation</a></li>
                    <li><a href="#">Terms Of Use</a></li>
                    <li><a href="#">Copyright Notice</a></li>
                    </ul>
                </div>

                <div class="footer-col footer-socials">
                    <h4>Socials</h4>
                    <div class="social-icons-vertical">
                    <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://github.com" target="_blank" aria-label="GitHub"><i class="fa-brands fa-github"></i></a>
                    <a href="https://linkedin.com" target="_blank" aria-label="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
                    </div>
                </div>
                </div>

            
                <div class="footer-bottom">
                <p>&copy; M-Unite 2026</p>
                </div>
            </footer>
    </body>
</html>