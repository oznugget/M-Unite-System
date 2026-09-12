<?php
session_start();
require "dbConnection.php";

$isLoggedIn = isset($_SESSION['username']);
$firstname  = $isLoggedIn ? htmlspecialchars($_SESSION['firstname']) : '';

$issue_result = $conn->query("SELECT title, content, created_at FROM current_issues WHERE is_featured = 1 LIMIT 1");
$issue = ($issue_result && $issue_result->num_rows > 0) ? $issue_result->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $issue ? htmlspecialchars($issue['title']) : 'Current Issue'; ?> | M-Unite</title>

    <link rel="stylesheet" href="homecss.css">
    <link rel="stylesheet" href="header_footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&family=TikTok+Sans:opsz,wght@12..36,300..900&display=swap" rel="stylesheet">
</head>
<body>

    <header class="site-header">
      <div class="logo-box">
      <a href="home.php" class="logo-link">
      <img src="images/logo_1.png" alt="M-Unite Logo" class="logo-image">
      </a>
    </div>

    <nav class="navbar">
      <a href="home.php" class="nav-item">Home</a>
      <a href="reports.html" class="nav-item">Reports</a>
      <a href="notification.html" class="nav-item">Notices</a>
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

    <section class="localinfo">
      <?php if ($issue): ?>
        <h2 class="section-title"><?php echo htmlspecialchars($issue['title']); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($issue['content'])); ?></p>
        <p style="color:#767b80; font-size:0.85rem; margin-top:1rem;">Posted <?php echo htmlspecialchars($issue['created_at']); ?></p>
      <?php else: ?>
        <h2 class="section-title">No current issues posted right now.</h2>
      <?php endif; ?>
      <p style="margin-top:1.5rem;"><a href="home.php">&larr; Back to home</a></p>
    </section>

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
              <li><a href="reports.html">Reports</a></li>
              <li><a href="notices.html">Notices</a></li>
              <li><a href="map.html">Map</a></li>
              <li><a href="about.html">About Us</a></li>
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
