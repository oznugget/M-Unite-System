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

// Fetch this officer's current details (accounts + municipal_officers)
$sql = "SELECT a.username, a.name, a.surname, a.phone_number, a.role, a.active_status, mo.division
        FROM accounts a
        JOIN municipal_officers mo ON mo.username = a.username
        WHERE a.username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<p class=\"error\">Officer account details not found.</p>");
}
$userData = $result->fetch_assoc();
$stmt->close();

// Handle the form being submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name_up  = isset($_POST["name"]) ? trim($_POST["name"]) : '';
    $surname_up = isset($_POST["surname"]) ? trim($_POST["surname"]) : '';
    $phone_up = isset($_POST["phone"]) ? trim($_POST["phone"]) : '';

    if ($name_up === '' || $surname_up === '') {
        $error = "Name and surname are required.";
    } elseif (!preg_match('/^\d{10}$/', $phone_up)) {
        $error = "Phone number must be 10 digits.";
    } else {
        $sql_update = "UPDATE accounts SET name = ?, surname = ?, phone_number = ? WHERE username = ?";
        $update_stmt = $conn->prepare($sql_update);
        $update_stmt->bind_param("ssss", $name_up, $surname_up, $phone_up, $username);
        $update_result = $update_stmt->execute();
        $update_stmt->close();

        if ($update_result) {
            log_activity($conn, $username, ACTION_PROFILE_UPDATED);
            header("Location: officer-account.php?update=success");
            exit();
        } else {
            $error = "Unable to update the record! Please try again.";
        }
    }

    // if we got here, validation failed - keep showing what they typed
    $userData['name'] = $name_up;
    $userData['surname'] = $surname_up;
    $userData['phone_number'] = $phone_up;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Account - M-Unite Officer</title>
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
  <h1>Update My Information</h1>

  <section>
    <?php if (!empty($error)): ?>
      <p class="error-text" style="display:block;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="officer-update-account.php">
      <label>Name:
        <input type="text" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" required>
      </label>
      <label>Surname:
        <input type="text" name="surname" value="<?php echo htmlspecialchars($userData['surname']); ?>" required>
      </label>
      <label>Phone number:
        <input type="text" name="phone" maxlength="10" value="<?php echo htmlspecialchars($userData['phone_number']); ?>" required>
        <span class="field-note">Must be 10 digits.</span>
      </label>

      <!-- Read-only fields - shown for reference, can't be edited here.
           Email/username: primary key everything links to.
           Role/Status: administrative, not self-service.
           Division: administrative for now - officers don't reassign
           their own division without approval. -->
      <label>Email / Username:
        <input type="text" value="<?php echo htmlspecialchars($userData['username']); ?>" disabled>
      </label>
      <label>Division:
        <input type="text" value="<?php echo htmlspecialchars($userData['division']); ?>" disabled>
      </label>
      <label>Role:
        <input type="text" value="<?php echo htmlspecialchars($userData['role']); ?>" disabled>
      </label>
      <label>Account Status:
        <input type="text" value="<?php echo ($userData['active_status'] == 1) ? 'Active' : 'Inactive'; ?>" disabled>
      </label>

      <button type="submit">Save Changes</button>
      <a href="officer-account.php"><button type="button" class="secondary">Cancel</button></a>
    </form>
  </section>
</main>

<footer>&copy; M-Unite 2026 - Municipal Officer Portal</footer>

<script src="nav.js"></script>
</body>
</html>
