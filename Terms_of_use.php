<?php
$isLoggedIn = isset($_SESSION['username']);
$firstname  = $isLoggedIn ? htmlspecialchars($_SESSION['firstname']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Terms of Use | M-Unite</title>

  <!-- Same fonts as every other page, for visual consistency -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@400;700&family=TikTok+Sans:wght@400;500&display=swap" rel="stylesheet">

  <!-- No Leaflet needed here — this page has no map -->
  <link rel="stylesheet" href="header_footer.css">
  <link rel="stylesheet" href="ReportStyle.css">
  <link rel="stylesheet" href="Terms_of_use_Style.css">
</head>
<body>

  <!-- ===== HEADER / NAV ===== -->
  <!-- Identical to every other page's header, except NOTHING
       has aria-current="page" here since Terms of Use isn't
       one of the 5 main nav links -->
 <header class="site-header">

      <div class="logo-box">
      <a href="home.php" class="logo-link">
      <img src="images/logo_1.png" alt="M-Unite Logo" class="logo-image">
      </a>
    </div>

    <nav class="navbar">
      <a href="home.php" class="nav-item-active">Home</a>
      <a href="CommReports.html" class="nav-item">Reports</a>
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

  <!-- ===== MAIN CONTENT ===== -->
  <!-- Reusing the "reports-page" class purely for its existing
       padding rules — this page has nothing to do with reports,
       we're just borrowing the spacing so this page feels
       consistent with the rest of the site, rather than writing
       a whole new CSS class for one page's padding. -->
  <main class="reports-page legal-page">

    <h1>Terms of Use</h1>
    <p class="legal-updated">Last updated: [insert date]</p>

    <p>
      Welcome to M-Unite. These Terms of Use ("Terms") govern
      your access to and use of the M-Unite platform, a
      web-based service connecting residents of Makhanda with
      the Makana Local Municipality for reporting and tracking
      municipal service issues. By creating an account or using
      any part of this platform, you agree to be bound by these
      Terms.
    </p>

    <h2>1. Who Can Use M-Unite</h2>
    <p>
      M-Unite is intended for residents, ward councillors,
      municipal staff, and registered organisations within the
      Makana Local Municipality area. You must provide accurate
      information when registering an account, and you are
      responsible for keeping your login details secure.
    </p>

    <h2>2. Submitting Reports and Tickets</h2>
    <p>
      When you submit a fault report or ticket through M-Unite,
      you confirm that the information provided — including
      location, description, category, and any uploaded images
      — is accurate to the best of your knowledge. Submitting
      false, misleading, or malicious reports may result in
      account suspension.
    </p>
    <p>
      Reports and tickets submitted through this platform are
      shared with the relevant ward councillor and/or municipal
      department for review and action. M-Unite does not
      guarantee a specific response time or outcome for any
      individual report.
    </p>

    <h2>3. Acceptable Use</h2>
    <p>
      You agree not to use M-Unite to upload unlawful, abusive,
      or harmful content, impersonate another person or
      organisation, attempt to gain unauthorised access to any
      part of the platform or its underlying systems, or
      interfere with the platform's normal operation.
    </p>

    <h2>4. Location and Map Data</h2>
    <p>
      M-Unite uses third-party mapping and geolocation services
      (including OpenStreetMap contributors and Nominatim) to
      help identify report locations and municipal ward
      boundaries. While we aim for accuracy, this data is
      community-maintained and may occasionally be incomplete
      or outdated. Ward assignment based on this data may be
      corrected by a municipal officer where necessary.
    </p>

    <h2>5. Content You Upload</h2>
    <p>
      You retain ownership of any images or descriptions you
      submit, but you grant M-Unite and the Makana Local
      Municipality a licence to use, store, and display that
      content for the purpose of processing and resolving your
      report.
    </p>

    <h2>6. Account Suspension</h2>
    <p>
      M-Unite reserves the right to suspend or terminate
      accounts that violate these Terms, submit repeated false
      reports, or otherwise misuse the platform.
    </p>

    <h2>7. Limitation of Liability</h2>
    <p>
      M-Unite is provided on an "as is" basis. We do not
      guarantee uninterrupted availability of the platform, and
      we are not liable for delays or failures in municipal
      service delivery arising from reports submitted through
      this platform.
    </p>

    <h2>8. Changes to These Terms</h2>
    <p>
      These Terms may be updated from time to time. Continued
      use of M-Unite after changes are posted constitutes
      acceptance of the revised Terms.
    </p>

    <h2>9. Contact Us</h2>
    <p>
      Questions about these Terms can be sent to
      <a href="mailto:info@munite.co.za">info@munite.co.za</a>.
    </p>

    

  </main>

  <!-- ===== FOOTER ===== -->
  <!-- Identical to every other page's footer -->
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
          <li><a href="Terms_of_use.html">Terms Of Use</a></li>
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