<?php
require_once("db.php");

// No sign-in page yet, so for now the username is passed in the URL,
// e.g. account.php?username=amahle.mtshali@example.com
$username = isset($_REQUEST['username']) ? trim($_REQUEST['username']) : '';

if ($username === '') {
    die("<p class=\"error\">No username provided. Open this page as account.php?username=someone@example.com</p>");
}

// Retrieve the account + community member details
$sql = "SELECT a.username, a.name, a.surname, a.phone_number, a.active_status,
               cm.town, w.ward_name
        FROM accounts a
        JOIN community_member cm ON cm.username = a.username
        LEFT JOIN wards w ON w.ward_id = cm.ward_id
        WHERE a.username = '$username'";
$result = $conn->query($sql);

if ($result === FALSE) {
    die("<p class=\"error\">Unable to retrieve account details!</p>");
}
if ($result->num_rows === 0) {
    die("<p class=\"error\">No account found for username: $username</p>");
}

$row = $result->fetch_assoc();

// Handle the settings form being submitted
$updateMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name_up  = isset($_POST["name"]) ? trim($_POST["name"]) : '';
    $phone_up = isset($_POST["phone"]) ? trim($_POST["phone"]) : '';

    if ($name_up !== '' && $phone_up !== '') {

        $sql_update = "UPDATE accounts SET name = '$name_up', phone_number = '$phone_up' WHERE username = '$username'";
        $update_result = $conn->query($sql_update);

        if ($update_result === FALSE) {
            $updateMessage = "<p class=\"error\">Unable to update the record!</p>";
        } else {
            $updateMessage = "<p class=\"success\">Account successfully updated!</p>";
            // refresh the values so the page shows the change immediately
            $row['name'] = $name_up;
            $row['phone_number'] = $phone_up;
        }
    } else {
        $updateMessage = "<p class=\"error\">Name and phone number are required.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Account - M-Unite</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Top navigation bar, styled after the SDS wireframe: logo box,
     boxed nav links, and a "Sign In" icon button on the right -->
<header>
  <div class="logo-box">M</div>
  <nav>
    <a href="index.html">Home</a>
    <a href="reports.html">Reports</a>
    <a href="notices.html">Notices</a>
    <a href="map.html">Map</a>
    <a href="about.html">About Us</a>
  </nav>
  <span class="account-btn">&#128100; My Account</span>
</header>

<main id="main-content">
  <h1>My Account</h1>

  <!-- Profile information -->
  <section>
    <h2>Profile</h2>
    <p>Name: <span id="p-name"><?php echo $row['name'] . " " . $row['surname']; ?></span></p>
    <p>Email: <span id="p-email"><?php echo $row['username']; ?></span></p>
    <p>Phone: <span id="p-phone"><?php echo $row['phone_number']; ?></span></p>
    <p>Ward: <?php echo $row['ward_name'] . ", " . $row['town']; ?></p>
    <p>Role: Community Member</p>
    <p>Status: <?php echo ($row['active_status'] == 1) ? "Active" : "Inactive"; ?></p>
  </section>

  <!-- List of reports the user has submitted -->
  <section>
    <h2>My Reports</h2>
    <ul id="report-list">
      <?php
      $sql_reports = "SELECT r.description, r.current_status, r.timestamp, sc.category_name
                       FROM reports r
                       JOIN service_categories sc ON sc.category_id = r.category_id
                       WHERE r.username = '$username'
                       ORDER BY r.timestamp DESC";
      $result_reports = $conn->query($sql_reports);

      if ($result_reports === FALSE) {
          echo "<p class=\"error\">Unable to retrieve reports!</p>";
      } elseif ($result_reports->num_rows === 0) {
          echo "<li>You haven't submitted any reports yet.</li>";
      } else {
          while ($rrow = $result_reports->fetch_assoc()) {
              echo "<li>";
              echo "<strong>" . $rrow['category_name'] . "</strong> - ";
              echo "<span class=\"status\">" . $rrow['current_status'] . "</span><br>";
              echo $rrow['description'] . "<br>";
              echo "<small>" . $rrow['timestamp'] . "</small>";
              echo "</li>";
          }
      }
      ?>
    </ul>
  </section>

  <!-- Update account details -->
  <section>
    <h2>Settings</h2>
    <?php echo $updateMessage; ?>
    <p>Update your account details below.</p>
    <form action="account.php?username=<?php echo $username; ?>" method="POST">
      <label>Name:
        <input type="text" id="name" name="name" value="<?php echo $row['name']; ?>">
      </label>
      <label>Email:
        <input type="email" id="email" name="email" value="<?php echo $row['username']; ?>" readonly>
      </label>
      <label>Phone number:
        <input type="text" id="phone" name="phone" value="<?php echo $row['phone_number']; ?>">
      </label>
      <button type="submit">Save Changes</button>
    </form>
  </section>

  <button id="logout-btn">Log Out</button>
</main>

<!-- Site footer -->
<footer>
  <p>Connecting residents of Makhanda and the Municipality.</p>
  <nav>
    <a href="index.html">Home</a>
    <a href="reports.html">Reports</a>
    <a href="notices.html">Notices</a>
    <a href="map.html">Map</a>
    <a href="about.html">About Us</a>
  </nav>
  <p>&copy; M-Unite 2026</p>
</footer>

<script src="nav.js"></script>
<?php $conn->close(); ?>
</body>
</html>
