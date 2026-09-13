<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require "dbConnection.php";

// 1. Must be logged in
if (!isset($_SESSION['username'])) {
    header("Location: signIN.php");
    exit();
}

// 2. Must be a System Admin
$allowedRoles = ['System Admin', '4'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
    header("Location: signIN.php");
    exit();
}

$username = $_SESSION['username'];

// ---- Fetch this admin's account details ----
$stmt = $conn->prepare("SELECT username, name, surname, phone_number FROM accounts WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header("Location: signIN.php");
    exit();
}

$admin = $result->fetch_assoc();
$stmt->close();

$profileMessage  = "";
$passwordMessage = "";

// ---- Handle Profile form submission ----
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {

    $name_up    = trim($_POST["name"] ?? '');
    $surname_up = trim($_POST["surname"] ?? '');
    $phone_up   = trim($_POST["phone"] ?? '');

    if ($name_up === '' || $surname_up === '' || $phone_up === '') {
        $profileMessage = "<p class=\"error\">Name, surname, and phone number are required.</p>";
    } else {
        $updateStmt = $conn->prepare("UPDATE accounts SET name = ?, surname = ?, phone_number = ? WHERE username = ?");
        $updateStmt->bind_param("ssss", $name_up, $surname_up, $phone_up, $username);

        if ($updateStmt->execute()) {
            $profileMessage = "<p class=\"success\">Profile updated successfully!</p>";
            $admin['name']         = $name_up;
            $admin['surname']      = $surname_up;
            $admin['phone_number'] = $phone_up;
        } else {
            $profileMessage = "<p class=\"error\">Unable to update the record. Please try again.</p>";
        }
        $updateStmt->close();
    }
}

// ---- Handle Password form submission ----
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_password'])) {

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $passwordMessage = "<p class=\"error\">All password fields are required.</p>";
    } elseif ($newPassword !== $confirmPassword) {
        $passwordMessage = "<p class=\"error\">New password and confirmation do not match.</p>";
    } elseif (strlen($newPassword) < 8) {
        $passwordMessage = "<p class=\"error\">New password must be at least 8 characters.</p>";
    } else {
        // Verify current password is correct before allowing a change
        $pwStmt = $conn->prepare("SELECT password FROM accounts WHERE username = ?");
        $pwStmt->bind_param("s", $username);
        $pwStmt->execute();
        $pwRow = $pwStmt->get_result()->fetch_assoc();
        $pwStmt->close();

        if (!password_verify($currentPassword, $pwRow['password'])) {
            $passwordMessage = "<p class=\"error\">Current password is incorrect.</p>";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $pwUpdateStmt = $conn->prepare("UPDATE accounts SET password = ? WHERE username = ?");
            $pwUpdateStmt->bind_param("ss", $hashedPassword, $username);

            if ($pwUpdateStmt->execute()) {
                $passwordMessage = "<p class=\"success\">Password updated successfully!</p>";
            } else {
                $passwordMessage = "<p class=\"error\">Unable to update password. Please try again.</p>";
            }
            $pwUpdateStmt->close();
        }
    }
}

$fullName = htmlspecialchars($admin['name'] . ' ' . $admin['surname']);
$initials = strtoupper(substr($admin['name'], 0, 1) . substr($admin['surname'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Account - M-Unite Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@500;700;800&family=TikTok+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="sysadmin_style.css">
<link rel="stylesheet" href="sysadmin_account.css">
</head>
<body class="admin-shell">

<div class="app-shell">

  <!-- ============ Sidebar ============ -->
  <aside class="sidebar">
        <div class="brand">
      <a href="sysadmin_home.php" class="logo-link">
        <img src="images/logo_1.png" alt="M-Unite Logo" class="brand-logo-image">
      </a>
    </div>

    <ul class="side-nav">
      <li><a href="sysadmin_home.php">Dashboard</a></li>
      <li><a href="sysadmin_users.php">User Management</a></li>
      <li><a href="sysadmin_content.php">Content Management</a></li>
      <li><a href="sysadmin_activity_logs.php">Activity Log</a></li>
      <li><a href="sysadmin_reports.php">Reports</a></li>
      <li><a href="sysadmin_account.php" class="active">My Account</a></li>
    </ul>

    <a href="#" class="logout-link" id="logout-btn">&#8630; Log Out</a>
  </aside>

  <!--Main column-->
  <div>
    <header class="topbar">
      <div class="search-box"> Search users, notices, activity...</div>
      <div class="topbar-right">
        <div class="bell">&#128276;</div>
        <div class="avatar"><?php echo $initials; ?></div>
        <div>
          <div class="who-name"><?php echo $fullName; ?></div>
          <div class="who-role">SYSTEM ADMINISTRATOR</div>
        </div>
      </div>
    </header>

    <main class="content">
      <div class="account-header">
        <div>
          <h1>My Account</h1>
          <p class="page-sub">Your administrator profile and credentials.</p>
        </div>
      </div>

      <div class="account-grid">

        <!-- Profile -->
        <section class="panel">
          <h2>Profile</h2>
          <p class="panel-sub">Displayed to other staff in the activity log.</p>

          <?php echo $profileMessage; ?>

          <div class="account-identity">
            <div class="account-avatar"><?php echo $initials; ?></div>
            <div>
              <div class="account-name"><?php echo $fullName; ?></div>
            </div>
          </div>

          <form method="post" action="">
            <div class="account-form-row">
              <div class="account-field">
                <label>First Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
              </div>
              <div class="account-field">
                <label>Surname</label>
                <input type="text" name="surname" value="<?php echo htmlspecialchars($admin['surname']); ?>" required>
              </div>
            </div>

            <div class="account-form-row">
              <div class="account-field">
                <label>Work Email</label>
                <input type="email" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled>
              </div>
              <div class="account-field">
                <label>Mobile Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($admin['phone_number']); ?>" required>
              </div>
            </div>

            <button type="submit" name="update_profile" class="btn-save">Save changes</button>
          </form>
        </section>

        <!-- Security -->
        <section class="panel">
          <h2>Security</h2>
          <p class="panel-sub">Update your login credentials.</p>

          <?php echo $passwordMessage; ?>

          <form method="post" action="">
            <div class="account-field">
              <label>Current Password</label>
              <input type="password" name="current_password" required>
            </div>

            <div class="account-form-row">
              <div class="account-field">
                <label>New Password</label>
                <input type="password" name="new_password" required>
              </div>
              <div class="account-field">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
              </div>
            </div>

            <button type="submit" name="update_password" class="btn-update-password">Update password</button>
          </form>
        </section>

      </div>
    </main>
  </div>
</div>

<script src="nav.js"></script>
</body>
</html>