<?php
session_start();
require "dbConnection.php";
require "log_helper.php";

if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}

$username = $_SESSION['username'];
$error = "";
$passwordError = "";
$passwordSuccess = "";

// Fetch Current Account Data
$sql = "SELECT * FROM accounts WHERE username = ?";
$stmt = $conn->prepare($sql);
$userData = null;
if ($stmt) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch Current Physical Address / Ward Data
$addressSql = "SELECT * FROM community_member WHERE username = ?";
$stmtAddr = $conn->prepare($addressSql);
$addressData = null;
if ($stmtAddr) {
    $stmtAddr->bind_param("s", $username);
    $stmtAddr->execute();
    $addressResult = $stmtAddr->get_result();
    if ($addressResult->num_rows > 0) {
        $addressData = $addressResult->fetch_assoc();
    }
    $stmtAddr->close();
}

// Handle the profile form being submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name  = isset($_POST['name']) ? trim($_POST['name']) : '';
    $surname = isset($_POST['surname']) ? trim($_POST['surname']) : '';
    $phone = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';

    $streetNumber = isset($_POST['street_number']) ? trim($_POST['street_number']) : '';
    $streetName   = isset($_POST['street_name']) ? trim($_POST['street_name']) : '';
    $suburb       = isset($_POST['suburb']) ? trim($_POST['suburb']) : '';
    $town         = isset($_POST['town']) ? trim($_POST['town']) : '';
    $postalCode   = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';

    if (empty($name) || empty($surname) || empty($phone)) {
        $error = "Name, Surname, and Phone Number are required.";
    } elseif (empty($streetNumber) || empty($streetName) || empty($suburb) || empty($town) || empty($postalCode)) {
        $error = "Please complete all physical address fields.";
    } else {
        $updateSql = "UPDATE accounts SET name = ?, surname = ?, phone_number = ? WHERE username = ?";
        $stmtUpdate = $conn->prepare($updateSql);

        $updateAddressSql = "UPDATE community_member SET street_number = ?, street_name = ?, suburb = ?, town = ?, postal_code = ? WHERE username = ?";
        $stmtAddress = $conn->prepare($updateAddressSql);

        if ($stmtUpdate && $stmtAddress) {
            $stmtUpdate->bind_param("ssss", $name, $surname, $phone, $username);
            $stmtAddress->bind_param("ssssss", $streetNumber, $streetName, $suburb, $town, $postalCode, $username);

            if ($stmtUpdate->execute() && $stmtAddress->execute()) {
                $_SESSION['firstname'] = $name;
                header("Location: account.php?update=success");
                exit();
            } else {
                $error = "Failed to update information. Please try again.";
            }
            $stmtUpdate->close();
            $stmtAddress->close();
        } else {
            $error = "Database statement error.";
        }
    }

    // if we got here, validation failed - keep showing what they typed
    $userData['name'] = $name;
    $userData['surname'] = $surname;
    $userData['phone_number'] = $phone;
    if ($addressData) {
        $addressData['street_number'] = $streetNumber;
        $addressData['street_name'] = $streetName;
        $addressData['suburb'] = $suburb;
        $addressData['town'] = $town;
        $addressData['postal_code'] = $postalCode;
    }
}

// Handle the password form being submitted - kept separate from the profile
// form so a mistake in one can never touch the other
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password      = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password  = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    $pwStmt = $conn->prepare("SELECT password FROM accounts WHERE username = ?");
    $pwStmt->bind_param("s", $username);
    $pwStmt->execute();
    $currentHash = $pwStmt->get_result()->fetch_assoc()['password'] ?? '';
    $pwStmt->close();

    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
        $passwordError = "All three password fields are required.";
    } elseif (!password_verify($current_password, $currentHash)) {
        $passwordError = "Current password is incorrect.";
    } elseif (strlen($new_password) < 8
        || !preg_match('/[A-Z]/', $new_password)
        || !preg_match('/[a-z]/', $new_password)
        || !preg_match('/[0-9]/', $new_password)) {
        $passwordError = "New password must be at least 8 characters and include an uppercase letter, a lowercase letter, and a number.";
    } elseif ($new_password !== $confirm_password) {
        $passwordError = "New password and confirmation don't match.";
    } elseif ($current_password === $new_password) {
        $passwordError = "New password must be different from your current password.";
    } else {
        $newHash = password_hash($new_password, PASSWORD_DEFAULT);
        $pwUpdateStmt = $conn->prepare("UPDATE accounts SET password = ? WHERE username = ?");
        $pwUpdateStmt->bind_param("ss", $newHash, $username);

        if ($pwUpdateStmt->execute()) {
            log_activity($conn, $username, ACTION_PASSWORD_UPDATED);
            $passwordSuccess = "Password updated successfully.";
        } else {
            $passwordError = "Unable to update your password. Please try again.";
        }
        $pwUpdateStmt->close();
    }
}

$firstname = htmlspecialchars($_SESSION['firstname'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Account | M-Unite</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="header_footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="update-account.css">
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
      <h1>Update My Information</h1>

      <section>
        <?php if (!empty($error)): ?>
          <p class="error-text" style="display:block;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="" method="post">
          <input type="hidden" name="update_profile" value="1">

          <label>First Name:
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userData['name'] ?? ''); ?>" required />
          </label>

          <label>Surname:
            <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($userData['surname'] ?? ''); ?>" required />
          </label>

          <label>Phone Number:
            <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($userData['phone_number'] ?? ''); ?>" required />
          </label>
          <ul id="phone-requirements" class="password-requirements">
            <li id="req-phone-digits"><span class="req-icon">&#10007;</span> Exactly 10 digits</li>
          </ul>

          <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;" />

          <label>Street Number:
            <input type="text" id="street_number" name="street_number" value="<?php echo htmlspecialchars($addressData['street_number'] ?? ''); ?>" required />
          </label>

          <label>Street Name:
            <input type="text" id="street_name" name="street_name" value="<?php echo htmlspecialchars($addressData['street_name'] ?? ''); ?>" required />
          </label>

          <label>Suburb:
            <input type="text" id="suburb" name="suburb" value="<?php echo htmlspecialchars($addressData['suburb'] ?? ''); ?>" required />
          </label>

          <label>Town:
            <input type="text" id="town" name="town" value="<?php echo htmlspecialchars($addressData['town'] ?? ''); ?>" required />
          </label>

          <label>Postal Code:
            <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($addressData['postal_code'] ?? ''); ?>" required />
          </label>

          <label>Ward (Read-Only):
            <input type="text" value="<?php echo htmlspecialchars($addressData['ward_id'] ?? ''); ?>" disabled />
          </label>

          <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;" />

          <label>Username / Email (Read-Only):
            <input type="text" value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>" disabled />
          </label>

          <label>Role (Read-Only):
            <input type="text" value="<?php echo htmlspecialchars($userData['role'] ?? ''); ?>" disabled />
          </label>

          <label>Status (Read-Only):
            <input type="text" value="<?php echo (($userData['active_status'] ?? 0) == 1) ? 'Active' : 'Inactive'; ?>" disabled />
          </label>

          <button type="submit">Save Changes</button>
          <a href="account.php"><button type="button" class="secondary">Cancel</button></a>
        </form>
      </section>

      <section>
        <h2>Update Password</h2>

        <?php if (!empty($passwordError)): ?>
          <p class="error-text" style="display:block;"><?php echo htmlspecialchars($passwordError); ?></p>
        <?php endif; ?>
        <?php if (!empty($passwordSuccess)): ?>
          <p style="color: var(--green-fg); font-weight: bold; margin-bottom: 12px;"><?php echo htmlspecialchars($passwordSuccess); ?></p>
        <?php endif; ?>

        <form method="POST" action="update-account.php">
          <input type="hidden" name="update_password" value="1">

          <label>Current password:
            <input type="password" id="current_password" name="current_password" required />
          </label>

          <label>New password:
            <input type="password" id="new_password" name="new_password" required minlength="8" />
          </label>
          <ul id="password-requirements" class="password-requirements">
            <li id="req-length"><span class="req-icon">&#10007;</span> At least 8 characters</li>
            <li id="req-upper"><span class="req-icon">&#10007;</span> One uppercase letter (A-Z)</li>
            <li id="req-lower"><span class="req-icon">&#10007;</span> One lowercase letter (a-z)</li>
            <li id="req-number"><span class="req-icon">&#10007;</span> One number (0-9)</li>
          </ul>

          <label>Confirm new password:
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8" />
          </label>

          <button type="submit">Update Password</button>
        </form>
      </section>
    </main>

    <script src="update-account.js"></script>

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
