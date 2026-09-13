<?php
session_start();
$isLoggedIn = isset($_SESSION['username']);
$firstname  = $isLoggedIn ? htmlspecialchars($_SESSION['firstname']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation | M-Unite</title>

    <link rel="stylesheet" href="header_footer.css">
    <link rel="stylesheet" href="documentation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&family=TikTok+Sans:opsz,wght@12..36,300..900&display=swap" rel="stylesheet">
</head>

<body>

    <!-- HEADER (same as your other pages) -->
    <header class="site-header">
        <div class="logo-box">
            <a href="home.php" class="logo-link">
                <img src="images/logo_1.png" alt="M-Unite Logo" class="logo-image">
            </a>
        </div>

        <div class="hamburger" id="hamburger-menu">
            <i class="fa-solid fa-bars"></i>
        </div>

        <nav class="navbar" id="nav-menu">
            <a href="home.php" class="nav-item">Home</a>
            <a href="CommReports.php" class="nav-item">Reports</a>
            <a href="public_notices.php" class="nav-item">Notices</a>
            <a href="map.php" class="nav-item">Map</a>
            <a href="about_us.html" class="nav-item">About Us</a>
        </nav>

        <div class="header-right">
            <?php if ($isLoggedIn): ?>
                <a href="account.php" class="sign-in-btn">
                    <?php echo $firstname ?> <i class="fa-regular fa-circle-user"></i>
                </a>
            <?php else: ?>
                <a href="signin.php" class="sign-in-btn">
                    Sign In <i class="fa-regular fa-circle-user"></i>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- DOCUMENTATION CONTENT -->
    <main class="doc-content">

        <h1>M-Unite Documentation</h1>
        <p class="doc-intro">
            M-Unite connects residents of Makhanda with the municipality. This guide explains
            how to create an account, report issues, view notices, and use the volunteer system.
        </p>

        <section>
            <h2>1. Getting Started</h2>
            <ul>
                <li><strong>Create an account:</strong> Click <em>Register Now</em> on the sign-in page and complete the form with your email address.</li>
                <li><strong>Sign in:</strong> Use the email and password you registered with. You'll be redirected to a home page based on your role.</li>
                <li><strong>Forgot password:</strong> Use the <em>Reset It</em> link on the sign-in page to receive a reset email.</li>
            </ul>
        </section>

        <section>
            <h2>2. User Roles</h2>
            <ul>
                <li><strong>Community Member</strong> — report municipal issues, view ward notices, and volunteer for local initiatives.</li>
                <li><strong>Ward Councillor</strong> — view reports for your ward, post ward notices, and manage ward information.</li>
                <li><strong>Municipal Officer</strong> — manage division-specific reports and update their statuses.</li>
                <li><strong>System Admin</strong> — full access to all reports, users, notices, and system settings.</li>
            </ul>
        </section>

        <section>
            <h2>3. Reporting an Issue</h2>
            <ol>
                <li>Sign in and click <em>Make Report</em> on the home page, or go to <em>Reports</em>.</li>
                <li>Choose a category (water, electricity, roads, waste, etc.).</li>
                <li>Describe the issue and add a location.</li>
                <li>Optionally attach a photo.</li>
                <li>Submit. Your report is visible to the relevant ward councillor and municipal officer.</li>
            </ol>
        </section>

        <section>
            <h2>4. Notices &amp; Alerts</h2>
            <p>
                Notices are public announcements from the municipality. When you're signed in,
                you'll also see alerts that are specific to your ward. Alerts appear as a banner
                at the top of the home page.
            </p>
        </section>

        <section>
            <h2>5. Map</h2>
            <p>
                The map shows reported issues in Makhanda. You can filter by category and, if
                you're signed in, by your ward. Click a marker to see details and the current status.
            </p>
        </section>

        <section>
            <h2>6. Volunteering</h2>
            <p>
                On the home page, scroll to <em>Where You Come In</em>. Signed-in users can select
                initiatives (clean-ups, neighbourhood watch, soup kitchens, disaster management,
                youth mentoring) and click <em>Confirm sign up</em> to be added to the relevant
                mailing list.
            </p>
            <p>
                If you're not signed in, you'll see a prompt to
                <a href="signin.php" class="signin-here">sign in here</a> first.
            </p>
        </section>

        <section>
            <h2>7. Ward Information</h2>
            <p>
                Your ward is linked to your account when you register. Councillors see reports
                and notices for their ward only. Community members see public notices and alerts
                for their own ward.
            </p>
        </section>

        <section>
            <h2>8. Troubleshooting</h2>
            <ul>
                <li><strong>Can't sign in?</strong> Confirm your email address is verified and use the password reset link.</li>
                <li><strong>Not seeing your ward?</strong> Log out and sign in again, or contact support with your registered email.</li>
                <li><strong>Report not submitting?</strong> Check your internet connection and ensure a photo isn't larger than the upload limit shown on the form.</li>
            </ul>
        </section>

        <section>
            <h2>9. Contact</h2>
            <p>
                Email <a href="mailto:info@munite.co.za">info@munite.co.za</a> or call
                <a href="tel:+27000000000">+27 000000000</a>. For website bugs, use the
                <em>Report Website Bugs</em> link in the footer.
            </p>
        </section>

    </main>

    <!-- FOOTER (same as your other pages) -->
    <footer class="site-footer">
        <img src="images/footerimgresponsive1.png" alt="Makhanda skyline" class="footer-skyline-mobile">
        <img src="images/footer_img.png" alt="Makhanda skyline" id="footerimg">

        <div class="footer-top">
            <div class="footer-col footer-about">
                <div class="footer-logo-box">
                    <img src="images/logo_1.png" alt="M-Unite Logo" class="footer-logo">
                </div>
                <p>Connecting residents of Makhanda and the Municipality, enabling you to share and report municipal issues.</p>
            </div>

            <div class="footer-col">
                <h4>Pages</h4>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="CommReports.php">Reports</a></li>
                    <li><a href="public_notices.php">Notices</a></li>
                    <li><a href="map.php">Map</a></li>
                    <li><a href="about_us.html">About Us</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Connect</h4>
                <ul>
                    <li><a href="#">Report Website Bugs</a></li>
                    <li><a href="#">Volunteer</a></li>
                    <li><a href="mailto:info@munite.co.za">info@munite.co.za</a></li>
                    <li><a href="tel:+27000000000">+27 000000000</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Resources</h4>
                <ul>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="documentation.php">Documentation</a></li>
                    <li><a href="Terms_of_use.php">Terms Of Use</a></li>
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