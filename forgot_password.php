<?php
session_start();
require "dbConnection.php";

$message = "";
$msgType = "error";
$token   = $_GET['token']  ?? '';
$email   = $_GET['email']  ?? '';

// ---------- 1. User submitted their email → generate token + send mail ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT name FROM accounts WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Always show success so attackers can't tell if the email exists
    $message = "If that email is registered, we've sent a reset link.";
    $msgType = "ok";

    if ($user) {
        $raw   = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $raw);
        $exp   = date('Y-m-d H:i:s', time() + 3600);   // 1 hour

        $upd = $conn->prepare("UPDATE accounts SET reset_token = ?, reset_expires = ? WHERE username = ?");
        $upd->bind_param("sss", $hash, $exp, $email);
        $upd->execute();
        $upd->close();

        $link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'])
              . "/forgot_password.php?token={$raw}&email=" . urlencode($email);

        $subject = "Reset your M-Unite password";
        $body    = "Hi {$user['name']},\n\n"
                 . "Click the link below to reset your password (expires in 1 hour):\n\n"
                 . $link . "\n\n"
                 . "If you didn't request this, ignore this email.";
        $headers = "From: no-reply@munite.co.za\r\nReply-To: info@munite.co.za\r\n";

        @mail($email, $subject, $body, $headers);
    }
}

// ---------- 2. User submitted a new password ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pword'])) {
    $token = $_POST['token'];
    $email = $_POST['email'];
    $pwd   = $_POST['pword'];
    $cfm   = $_POST['confirm_pwd'];

    // Basic password rules
    if (strlen($pwd) < 8 || !preg_match('/[A-Z]/', $pwd) || !preg_match('/[a-z]/', $pwd) || !preg_match('/[0-9]/', $pwd)) {
        $message = "Password must be 8+ characters with upper, lower, and a number.";
    } elseif ($pwd !== $cfm) {
        $message = "Passwords do not match.";
    } else {
        $hash = hash('sha256', $token);
        $stmt = $conn->prepare("SELECT username FROM accounts WHERE username = ? AND reset_token = ? AND reset_expires > NOW() LIMIT 1");
        $stmt->bind_param("ss", $email, $hash);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $message = "This reset link is invalid or has expired.";
        } else {
            $newHash = password_hash($pwd, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE accounts SET password = ?, reset_token = NULL, reset_expires = NULL WHERE username = ?");
            $upd->bind_param("ss", $newHash, $email);
            $upd->execute();
            $upd->close();

            $message = "Password updated. You can now sign in.";
            $msgType = "ok";
            $token = '';   // hide the reset form
        }
    }
}

// ---------- 3. Decide which form to show ----------
$showResetForm = false;
if ($token && $email && !isset($_POST['pword'])) {
    $hash = hash('sha256', $token);
    $stmt = $conn->prepare("SELECT 1 FROM accounts WHERE username = ? AND reset_token = ? AND reset_expires > NOW() LIMIT 1");
    $stmt->bind_param("ss", $email, $hash);
    $stmt->execute();
    $showResetForm = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();

    if (!$showResetForm && $message === '') {
        $message = "This reset link is invalid or has expired.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | M-Unite</title>
    <link rel="stylesheet" href="signincss.css">
    <link rel="stylesheet" href="header_footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
            <a href="CommReports.php" class="nav-item">Reports</a>
            <a href="public_notices.php" class="nav-item">Notices</a>
            <a href="map.php" class="nav-item">Map</a>
            <a href="about_us.html" class="nav-item">About Us</a>
        </nav>
        <div class="header-right">
            <a href="signin.php" class="sign-in-btn">Sign In <i class="fa-regular fa-circle-user"></i></a>
        </div>
    </header>

    <div class="registration">
        <h2><?php echo $showResetForm ? 'Set a New Password' : 'Forgot Password'; ?></h2>

        <?php if ($message): ?>
            <div style="background:<?php echo $msgType === 'ok' ? '#d4edda' : '#f8d7da'; ?>;
                        color:<?php echo $msgType === 'ok' ? '#155724' : '#721c24'; ?>;
                        padding:10px;border-radius:4px;margin-bottom:15px;text-align:center;font-size:14px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($showResetForm): ?>
            <form class="reg-form" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                <div class="form-group">
                    <label for="pword">New Password</label>
                    <input type="password" id="pword" name="pword" required>
                </div>
                <div class="form-group">
                    <label for="confirm_pwd">Confirm Password</label>
                    <input type="password" id="confirm_pwd" name="confirm_pwd" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>

        <?php elseif ($msgType !== 'ok' || !isset($_POST['email'])): ?>
            <form class="reg-form" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                </div>
                <div class="register-link">
                    Remembered it? <a href="signin.php">Back to sign in</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>