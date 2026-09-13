<?php 
session_start();


require "dbConnection.php"; 

$error = "";
$isLoggedIn = isset($_SESSION['username']);
$firstname  = $isLoggedIn ? htmlspecialchars($_SESSION['firstname']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pword = isset($_POST['pword']) ? trim($_POST['pword']) : '';

    if (empty($email) || empty($pword)) {
        $error = "Email address and password are required.";
    } else {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            $timestamp  = date('Y-m-d H:i:s');
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

            $sql = "SELECT a.*, wc.ward_id
                    FROM accounts a
                    LEFT JOIN ward_councillors wc ON wc.username = a.username
                    WHERE a.username = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    $stmtLog = $conn->prepare("INSERT INTO logtrails (username, action_type_id, ip_address, start_session, end_session, is_authenticated) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($stmtLog) {
                        $action_type_id = 2; // Failed Login
                        $is_auth = 0;
                        $stmtLog->bind_param("sisssi", $email, $action_type_id, $ip_address, $timestamp, $timestamp, $is_auth);
                        $stmtLog->execute();
                        $stmtLog->close();
                    }
                    $error = "Invalid email or password.";
                } else {
                    $row = $result->fetch_assoc();

                    if (password_verify($pword, $row['password'])) {
                        session_regenerate_id(true);
                        $_SESSION['username']  = $row['username'];
                        $_SESSION['firstname'] = $row['name'];
                        $_SESSION['role']      = $row['role'];
                        $_SESSION['ward']    = null;
                        $_SESSION['ward_id'] = null;
                        $_SESSION['division'] = null;
                        $_SESSION['ward_id']   = isset($row['ward_id']) ? (int)$row['ward_id'] : null;
                        $stmtLog = $conn->prepare("INSERT INTO logtrails (username, action_type_id, ip_address, start_session, end_session, is_authenticated) VALUES (?, ?, ?, ?, ?, ?)");
                        if ($stmtLog) {
                            $action_type_id = 1; // Successful Login
                            $is_auth = 1;
                            $stmtLog->bind_param("sisssi", $email, $action_type_id, $ip_address, $timestamp, $timestamp, $is_auth);
                            $stmtLog->execute();
                            $stmtLog->close();
                        }

                        switch ($row['role']) {
                            case "Community Member":
                            case "1":
                                  $stmtC = $conn->prepare("SELECT ward FROM community_members WHERE username = ? LIMIT 1");
                                    if ($stmtC) {
                                        $stmtC->bind_param("s", $row['username']);
                                        $stmtC->execute();
                                        $cm = $stmtC->get_result()->fetch_assoc();
                                        $stmtC->close();
                                        if ($cm && !empty($cm['ward'])) {
                                            $_SESSION['ward'] = $cm['ward'];
                                        }
                                    }
                                header("Location: home.php?login=success");
                                exit();

                            case "Ward councillor":
                            case "2":
                                $stmtW = $conn->prepare("SELECT ward_id FROM ward_councillors WHERE username = ? LIMIT 1");
                                if ($stmtW) {
                                    $stmtW->bind_param("s", $row['username']);
                                    $stmtW->execute();
                                    $wardRow = $stmtW->get_result()->fetch_assoc();
                                    $stmtW->close();

                                    if ($wardRow) {
                                        $_SESSION['ward_id'] = $wardRow['ward_id'];
                                    }
                                }
                                header("Location: ward_councillor_home.php?login=success");
                                exit();

                            case "Municipal Officer":
                            case "3":
                                 $stmtM = $conn->prepare("SELECT division FROM municipal_officers WHERE username = ? LIMIT 1");
                                if ($stmtM) {
                                    $stmtM->bind_param("s", $row['username']);
                                    $stmtM->execute();
                                    $div = $stmtM->get_result()->fetch_assoc();
                                    $stmtM->close();

                                    if ($div) {
                                        $_SESSION['division'] = $div['division'];
                                    }
                                }
                                header("Location: officer-home.php?login=success");
                                exit();

                            case "System Admin":
                            case "4":
                                header("Location: admin-home.php?login=success");
                                exit();

                            default:
                                $error = "Access level not recognized. Please contact administrator.";
                                break;
                        }
                    } else {
                        $stmtLog = $conn->prepare("INSERT INTO logtrails (username, action_type_id, ip_address, start_session, end_session, is_authenticated) VALUES (?, ?, ?, ?, ?, ?)");
                        if ($stmtLog) {
                            $action_type_id = 2; // Failed Login
                            $is_auth = 0;
                            $stmtLog->bind_param("sisssi", $email, $action_type_id, $ip_address, $timestamp, $timestamp, $is_auth);
                            $stmtLog->execute();
                            $stmtLog->close();
                        }
                        $error = "Invalid email or password.";
                    }
                }
                $stmt->close();
            } else {
                $error = "Database query prepare failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In | M-Unite</title>
    <link rel="stylesheet" href="forms.css">
    <link rel="stylesheet" href="signincss.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <script src="signin.js" defer></script>
    <link rel="stylesheet" href="header_footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&family=TikTok+Sans:opsz,wght@12..36,300..900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    
</head>

<body>

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




    <!-- SIGN-IN FORM -->
    <div class="registration">
        <h2>Sign In</h2>

                <?php if (!empty($error)): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>


        <form class="reg-form" action="" method="post">
         
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required />
            </div>

           <div class="form-group">
                <label for="pword">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="pword" name="pword" required>
                    <button type="button" class="toggle-btn"
                            onclick="togglePassword('pword', 'eyeIconSignin')"
                            aria-label="Toggle password visibility">
                        <svg id="eyeIconSignin" xmlns="http://www.w3.org/2000/svg"
                            width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="#0E2841" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>

            <div class="register-link">
                Don't have an account? <a href="createacc.php">Register Now</a> <br>
                Forgot your password? <a href="forgot_password.php">Reset It</a>
            </div>
        </form>
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