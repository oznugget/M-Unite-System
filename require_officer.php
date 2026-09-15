<?php
// Access guard for Municipal Officer pages, mirroring require_councillor.php.
//
// ASSUMPTION FLAGGED: I don't have your actual require_councillor.php, so I
// don't know the exact session keys / role-check your auth layer uses. This
// guesses $_SESSION['username'] + $_SESSION['userrole'] === 'municipal_officer'
// based on the patterns visible in ticket.php/tickets.php (which read
// $_SESSION['firstname'], $_SESSION['ward_id'], $_SESSION['username']) and
// the "userrole string/numeric mismatches" note from your own team history.
// Swap the role check below for whatever require_councillor.php actually does.

session_start();
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['username']) || ($_SESSION['userrole'] ?? '') !== 'municipal_officer') {
    http_response_code(403);
    echo "You don't have access to this page.";
    exit;
}

// Look up the officer's division fresh on every request (rather than trusting
// a stale session value) so a change to municipal_officers.division takes
// effect without waiting for the officer to log out and back in.
$stmt = $conn->prepare("SELECT division FROM municipal_officers WHERE username = ?");
$stmt->bind_param('s', $_SESSION['username']);
$stmt->execute();
$officer_row = $stmt->get_result()->fetch_assoc();

if (!$officer_row) {
    http_response_code(403);
    echo "No division is assigned to this account.";
    exit;
}

$_SESSION['division'] = $officer_row['division'];
