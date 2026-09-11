<?php
session_start();
require "dbConnection.php";

if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}

$username = $_SESSION['username'];
$error = "";
$userData = null;
$addressData = null;

// Fetch Account Details
$sql = "SELECT * FROM accounts WHERE username = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    } else {
        $error = "Account details not found.";
    }
    $stmt->close();
} else {
    $error = "Database query preparation failed.";
}

// Fetch Address / Ward Details
$addrStmt = $conn->prepare("SELECT * FROM community_member WHERE username = ?");
if ($addrStmt) {
    $addrStmt->bind_param("s", $username);
    $addrStmt->execute();
    $addrResult = $addrStmt->get_result();
    if ($addrResult->num_rows > 0) {
        $addressData = $addrResult->fetch_assoc();
    }
    $addrStmt->close();
}

$firstname = htmlspecialchars($_SESSION['firstname'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | M-Unite</title>

    <link rel="stylesheet" href="style.css">
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
        <a href="account.php" class="sign-in-btn active">
          <?php echo $firstname; ?> <i class="fa-regular fa-circle-user"></i>
        </a>
      </div>
    </header>

    <main>
      <h1>My Account</h1>

      <?php if (!empty($error)): ?>
        <section>
          <p class="error-text" style="display:block;"><?php echo htmlspecialchars($error); ?></p>
        </section>
      <?php endif; ?>

      <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
        <section>
          <p style="color:#1e7a3c; font-weight:700;">&#10003; Account details updated successfully!</p>
        </section>
      <?php endif; ?>

      <?php if ($userData): ?>
        <section>
          <h2>&#128100; Profile</h2>
          <p>Name: <strong><?php echo htmlspecialchars($userData['name']); ?> <?php echo htmlspecialchars($userData['surname']); ?></strong></p>
          <p>Email: <?php echo htmlspecialchars($userData['username']); ?></p>
          <p>Phone: <?php echo htmlspecialchars($userData['phone_number']); ?></p>
          <?php if ($addressData): ?>
            <p>Address:
              <?php echo htmlspecialchars($addressData['street_number'] . ' ' . $addressData['street_name'] . ', ' . $addressData['suburb'] . ', ' . $addressData['town'] . ' ' . $addressData['postal_code']); ?>
            </p>
            <p>Ward: <?php echo htmlspecialchars($addressData['ward_id']); ?></p>
          <?php endif; ?>
          <p>Role: <?php echo htmlspecialchars($userData['role']); ?></p>
          <p>Account Status: <?php echo ($userData['active_status'] == 1) ? 'Active' : 'Inactive'; ?></p>
        </section>

        <section>
          <a href="update-account.php"><button type="button">Update Information</button></a>
          <a href="logout.php"><button type="button" class="secondary">Log Out</button></a>
        </section>
      <?php endif; ?>
    </main>

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
