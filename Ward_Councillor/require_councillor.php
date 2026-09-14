<?php
/**
 * require_councillor.php
 *
 * Include this at the very top of every ward-councillor-only page,
 * BEFORE any output, session usage, or db_connect.php include.
 *
 * - If not logged in          -> redirect to signin.php
 * - If logged in but not a WC -> HTTP 403 and stop
 * - Otherwise                 -> no-op, page continues
 *
 * Requires: session already started (this file will start it if not).
 * Expects:  accounts table has a `role` column whose value for
 *           ward councillors is exactly 'Ward Councillor'.
 *           Adjust ROLE_* constants below if your schema differs.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_connect.php'; // provides $conn (mysqli)

// ---- Tunables -------------------------------------------------------------
const WC_ROLE_VALUE = 'Ward Councillor'; // value stored in accounts.role
// ---------------------------------------------------------------------------

// 1) Must be logged in
if (empty($_SESSION['username'])) {
    // Preserve intended destination so you can bounce back after sign-in
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'ward_councillor_home.php';
    header('Location: signin.php');
    exit;
}

// 2) Re-check the role against the DB every request (do NOT trust the
//    session value alone — roles can be revoked, sessions can be stale).
$stmt = $conn->prepare(
    "SELECT role FROM accounts WHERE username = ? LIMIT 1"
);
$stmt->bind_param('s', $_SESSION['username']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || $row['role'] !== WC_ROLE_VALUE) {
    http_response_code(403);
    // Keep this page minimal — no nav, no styling — so nothing leaks.
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head><meta charset="UTF-8"><title>Access denied</title></head>
    <body style="font-family: sans-serif; max-width: 40rem; margin: 4rem auto;">
        <h1>403 — Access denied</h1>
        <p>This page is restricted to ward councillor accounts.</p>
        <p><a href="signin.php">Sign in</a> · <a href="home.php">Go home</a></p>
    </body>
    </html>
    <?php
    exit;
}