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

// Fetch Account Details via Prepared Statement
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Account | M-Unite</title>
    <link rel="stylesheet" href="signincss.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&family=TikTok+Sans:opsz,wght@12..36,300..900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>

<body>

    <!-- HEADER -->
    <header class="site-header">
        <div class="logo-box">
            <img src="logo1.png" alt="M-Unite Logo" class="logo-image">
            <a href="index.html" class="logo-link"></a>
        </div>

        <nav class="navbar">
            <a href="home.php" class="nav-item">Home</a>
            <a href="reports.html" class="nav-item">Reports</a>
            <a href="notification.html" class="nav-item">Notices</a>
            <a href="map.html" class="nav-item">Map</a>
            <a href="about_us.html" class="nav-item">About Us</a>
        </nav>

        <div class="header-right">
            <a href="account.php" class="sign-in-btn active">
                <?php echo htmlspecialchars($userData['name'] ?? 'Account'); ?> <i class="fa-regular fa-circle-user"></i>
            </a>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <div class="registration">
        <h2>My Profile</h2>

        <?php if (!empty($error)): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px;">
                Account details updated successfully!
            </div>
        <?php endif; ?>

        <?php if ($userData): ?>
            <div class="reg-form">
                <div class="form-group">
                    <label>Username / Email</label>
                    <input type="text" value="<?php echo htmlspecialchars($userData['username']); ?>" disabled />
                </div>

                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($userData['name']); ?>" disabled />
                </div>

                <div class="form-group">
                    <label>Surname</label>
                    <input type="text" value="<?php echo htmlspecialchars($userData['surname']); ?>" disabled />
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" value="<?php echo htmlspecialchars($userData['phone_number']); ?>" disabled />
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <input type="text" value="<?php echo htmlspecialchars($userData['role']); ?>" disabled />
                </div>

                <div class="form-group">
                    <label>Account Status</label>
                    <input type="text" value="<?php echo ($userData['active_status'] == 1) ? 'Active' : 'Inactive'; ?>" disabled />
                </div>

                <div class="form-actions" style="display: flex; gap: 10px;">
                    <a href="update_account.php" style="width: 100%;">
                        <button type="button" class="btn btn-primary" style="width: 100%;">Update Information</button>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <footer class="site-footer">
        <div class="footer-top">
            <div class="footer-col footer-about">
                <div class="footer-logo-box">
                    <img src="logo1.png" alt="M-Unite Logo" class="logo-image" />
                </div>
                <p>Connecting residents of Makhanda and the Municipality, enabling you to share and report municipal issues.</p>
            </div>

            <div class="footer-col">
                <h4>Pages</h4>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="reports.html">Reports</a></li>
                    <li><a href="notification.html">Notices</a></li>
                    <li><a href="map.html">Map</a></li>
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