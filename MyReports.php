<?php


require 'db-connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$username = $_SESSION['username'] ?? 'brown@gmail.com';



$stmt = $pdo->prepare('
    SELECT
        r.report_id,
        r.image_url,
        r.street_number,
        r.street_name,
        r.surburb,
        r.description,
        r.timestamp,
        r.current_status,
        c.category_name,
        w.ward_name
    FROM reports r
    JOIN service_categories c ON r.category_id = c.category_id
    JOIN wards w ON r.ward_id = w.ward_id
    WHERE r.username = :username
    ORDER BY r.timestamp DESC
');

$stmt->execute(['username' => $username]);
$reports = $stmt->fetchAll();




function buildStatusTimeline($currentStatus) {

    $statuses = ['Pending', 'In Progress', 'Resolved', 'Closed'];
    $currentIndex = array_search($currentStatus, $statuses);

   
    if ($currentIndex === false) {
        $currentIndex = 0;
    }

    $html = '<div class="status-timeline" aria-label="Report status: ' . htmlspecialchars($currentStatus) . '">';

    foreach ($statuses as $index => $label) {

        $classes = 'status-step';

        if ($index < $currentIndex) {
            $classes .= ' is-complete';
        }

        if ($index === $currentIndex) {
            $classes .= ' is-current';
        }

        
        if ($index > 0 && $index <= $currentIndex) {
            $classes .= ' segment-filled';
        }

        $html .= '<div class="' . $classes . '">';
        $html .= '<span class="status-dot"></span>';
        $html .= '<span class="status-label">' . htmlspecialchars($label) . '</span>';
        $html .= '</div>';

    }

    $html .= '</div>';

    return $html;

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports | M-Unite</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@400;700&family=TikTok+Sans:wght@400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="ReportStyle.css">
</head>

<body>
    <header class="site-header">
        <a href="index.html" class="logo">
            <img src="pictures/logo.png" alt="M-Unite logo">
        </a>

        <nav class="main-nav" aria-label="Main navigation">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="CommReports.html">Reports</a></li>
                <li><a href="notices.html">Notices</a></li>
                <li><a href="map.html">Map</a></li>
                <li><a href="about.html">About Us</a></li>
            </ul>
        </nav>

        <button class="account-icon" aria-label="My Account">
            <img src="pictures/account-icon.svg" alt="">
        </button>
    </header>

    <main class="reports-page">

        <p class="map-hint"><a href="CommReports.html">&larr; Back to report a fault</a></p>

        <section class="past-reports">
            <div class="past-reports-header">
                <h2>My Reports</h2>
                <div class="filter-wrapper">
                    <button type="button" class="filter-btn" aria-haspopup="true" aria-expanded="false">
                        Filter By: <span class="filter-icon" aria-hidden="true"></span>
                    </button>
                    <div id="filter-panel" class="filter-panel" hidden>
                        <label>
                            <input type="checkbox" class="filter-checkbox" value="Pending" checked>
                            Pending
                        </label>
                        <label>
                            <input type="checkbox" class="filter-checkbox" value="In Progress" checked>
                            In Progress
                        </label>
                        <label>
                            <input type="checkbox" class="filter-checkbox" value="Resolved" checked>
                            Resolved
                        </label>
                        <label>
                            <input type="checkbox" class="filter-checkbox" value="Closed" checked>
                            Closed
                        </label>
                    </div>
                </div>
            </div>

            <ul class="report-cards-list">

                <?php if (empty($reports)): ?>

                    
                    <p>You haven't submitted any reports yet.</p>

                <?php else: ?>

                    <?php foreach ($reports as $report): ?>

                        <li class="report-card" data-status="<?php echo htmlspecialchars($report['current_status']); ?>">

                            <h3 class="report-card-title"><?php echo htmlspecialchars($report['category_name']); ?> Report</h3>

                            <?php echo buildStatusTimeline($report['current_status']); ?>

                            <div class="report-card-image">
                                <?php if (!empty($report['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($report['image_url']); ?>" alt="Photo submitted with this report">
                                <?php else: ?>
                                    <img src="" alt="" hidden>
                                    <p class="no-image-text">No image</p>
                                <?php endif; ?>
                            </div>

                            <div class="report-card-meta">
                                <span class="report-date">Date: <?php echo date('d/m/y', strtotime($report['timestamp'])); ?></span>
                                <span class="report-status">Status: <?php echo htmlspecialchars($report['current_status']); ?></span>
                            </div>
                            <span class="report-id">ID: <?php echo str_pad($report['report_id'], 7, '0', STR_PAD_LEFT); ?></span>

                            <p class="report-description">
                                <strong>Description:</strong> <?php echo htmlspecialchars($report['description']); ?>
                            </p>

                            <p class="report-location">
                                <strong>Location:</strong>
                                <?php
                                   
                                    $locationParts = [];
                                    if (!empty($report['street_number'])) {
                                        $locationParts[] = trim($report['street_number']);
                                    }
                                    $locationParts[] = trim($report['street_name']);
                                    $locationParts[] = trim($report['surburb']);
                                    echo htmlspecialchars(implode(', ', array_filter($locationParts)));
                                ?>
                            </p>

                            <span class="report-time">Time of report: <?php echo date('H:i', strtotime($report['timestamp'])); ?></span>


                        </li>

                    <?php endforeach; ?>

                <?php endif; ?>

            </ul>
        </section>

    </main>

    <div class="ai-chat-widget">
        <img src="makbot_chat.jpeg" alt="matbok, M-Unite chatbot" class="ai-chat-mascot">
        <p class="ai-chat-prompt">Want to chat with Makbot, our AI assistant?</p>

        <button type="button" class="ai-chat-icon" aria-haspopup="dialog" aria-expanded="false" aria-controls="ai-chat-popup" aria-label="Chat with Makbot, our AI assistant">
            ?
        </button>

        <div id="ai-chat-popup" class="ai-chat-popup" role="dialog" aria-label="Makbot AI assistant chat" hidden>
            <div class="ai-chat-popup-header">
                <h2>Makbot</h2>
                <button type="button" class="ai-chat-close-btn" aria-label="Close chat">&times;</button>
            </div>

            <div class="ai-chat-messages"></div>

            <form class="ai-chat-input-row">
                <label for="ai-chat-input" class="visually-hidden">Type your message</label>
                <input type="text" id="ai-chat-input" name="ai-chat-input" placeholder="Ask a question...">
                <button type="submit">Send</button>
            </form>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-brand">
            <img src="assets/logo.svg" alt="M-Unite logo">
            <p>Connecting residents of Makhanda and the Municipality, enabling you to share and report municipal issues.</p>
        </div>

        <nav class="footer-col" aria-label="Pages">
            <h3>Pages</h3>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="CommReports.html">Reports</a></li>
                <li><a href="notices.html">Notices</a></li>
                <li><a href="map.html">Map</a></li>
                <li><a href="about.html">About Us</a></li>
            </ul>
        </nav>

        <nav class="footer-col" aria-label="Connect">
            <h3>Connect</h3>
            <ul>
                <li><a href="report-bug.html">Report Website Bugs</a></li>
                <li><a href="volunteer.html">Volunteer</a></li>
                <li><a href="mailto:info@munite.co.za">info@munite.co.za</a></li>
                <li><a href="tel:+27300300300">+27 300300300</a></li>
            </ul>
        </nav>

        <nav class="footer-col" aria-label="Resources">
            <h3>Resources</h3>
            <ul>
                <li><a href="privacy-policy.html">Privacy Policy</a></li>
                <li><a href="documentation.html">Documentation</a></li>
                <li><a href="terms-of-use.html">Terms Of Use</a></li>
                <li><a href="copyright-notice.html">Copyright Notice</a></li>
            </ul>
        </nav>

        <nav class="footer-col" aria-label="Socials">
            <h3>Socials</h3>
            <ul class="social-links">
                <li><a href="https://instagram.com/munite" aria-label="M-Unite on Instagram"><img src="assets/instagram-icon.svg" alt=""></a></li>
                <li><a href="https://github.com/munite" aria-label="M-Unite on GitHub"><img src="assets/github-icon.svg" alt=""></a></li>
                <li><a href="https://linkedin.com/company/munite" aria-label="M-Unite on LinkedIn"><img src="assets/linkedin-icon.svg" alt=""></a></li>
            </ul>
        </nav>

        <p class="copyright">&copy; M-Unite 2026</p>
    </footer>

    <script src="MyPastReportsScript.js"></script>

</body>
</html>