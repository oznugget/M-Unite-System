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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Account - M-Unite Officer</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <div class="logo-box">M</div>
  <nav>
    <a href="officer-home.php">&#127968; Home</a>
    <a href="officer-notices.php">&#128226; Notices</a>
    <a href="officer-tickets.php">&#127915; Tickets</a>
    <a href="officer-stats.php">&#128200; Statistics</a>
    <a href="officer-events.php">&#127881; Events</a>
    <a href="officer-issues.php">&#128680; Current Issues</a>
  </nav>
  <span class="account-btn"><a href="officer-account.php" class="active">&#128100; My Account</a></span>
</header>

<main class="wide">
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
      <p>Email: <span><?php echo htmlspecialchars($userData['username']); ?></span> <span class="item-meta">(fixed by system admin)</span></p>
      <p>Phone: <span><?php echo htmlspecialchars($userData['phone_number']); ?></span></p>
      <p>Role: <?php echo htmlspecialchars($userData['role']); ?></p>
      <p>Account Status: <?php echo ($userData['active_status'] == 1) ? 'Active' : 'Inactive'; ?></p>
    </section>

    <section>
      <a href="officer-update-account.php"><button type="button">Update Information</button></a>
      <a href="logout.php"><button type="button" class="secondary">Log Out</button></a>
    </section>
  <?php endif; ?>

</main>

<footer>&copy; M-Unite 2026 - Municipal Officer Portal</footer>

<script src="nav.js"></script>
</body>
</html>
