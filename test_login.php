<?php
// TEMPORARY — for testing only. Delete this file once the real
// login/account-creation page is built.
session_start();

$_SESSION['username'] = 'brown@gmail.com';
$_SESSION['user_id']  = 10;      // adjust to a real row in your users table if you have one
$_SESSION['ward_id']  = 5;      // adjust to whichever ward you're testing with

echo "Test session set:<br>";
echo "username: " . $_SESSION['username'] . "<br>";
echo "user_id: " . $_SESSION['user_id'] . "<br>";
echo "ward_id: " . $_SESSION['ward_id'] . "<br><br>";

echo '<a href="reports.php">Go to Reports</a>';
