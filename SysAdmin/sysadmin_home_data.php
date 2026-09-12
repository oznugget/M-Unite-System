<?php
session_start();
require "db.php";

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

// Pull fresh name/surname for this admin
$stmt = $conn->prepare("SELECT name, surname FROM accounts WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$adminResult = $stmt->get_result();

if ($adminResult->num_rows === 0) {
    session_destroy();
    header("Location: signIN.php");
    exit();
}

$admin = $adminResult->fetch_assoc();
$stmt->close();

$firstName    = htmlspecialchars($admin['name']);
$fullInitials = strtoupper(substr($admin['name'], 0, 1) . substr($admin['surname'], 0, 1));

// STAT CARDS 

// Active users out of total
$totalUsers  = $conn->query("SELECT COUNT(*) AS cnt FROM accounts")->fetch_assoc()['cnt'];
$activeUsers = $conn->query("SELECT COUNT(*) AS cnt FROM accounts WHERE active_status = 1")->fetch_assoc()['cnt'];

// Pending approvals (unregistered/unapproved accounts)
$pendingApprovals = $conn->query("SELECT COUNT(*) AS cnt FROM accounts WHERE is_registered = 0")->fetch_assoc()['cnt'];

// Pending notices (everything except 'report' type notices, which aren't approved here)
$pendingNoticesTotal = 0;
$noticeBreakdownParts = [];

$typeResult = $conn->query(" SELECT notif_type, COUNT(*) AS cnt FROM notices
                            WHERE status = 'pending' AND notif_type != 'report' GROUP BY notif_type");

// Friendly labels for each enum value
$typeLabels = [
    'ward'           => 'ward',
    'general'        => 'general',
    'events'         => 'events',
    'current_issues' => 'current issues'
];

while ($row = $typeResult->fetch_assoc()) {
    $pendingNoticesTotal += $row['cnt'];
    $label = $typeLabels[$row['notif_type']] ?? $row['notif_type'];
    $noticeBreakdownParts[] = $row['cnt'] . ' ' . $label;
}

$pendingNotices = $pendingNoticesTotal;
//if the notices aren't empty join each park using . , otherwise None Pending
$noticeBreakdownText = !empty($noticeBreakdownParts) ? implode(' · ', $noticeBreakdownParts) : 'None pending';

// Pending approvals (unregistered accounts), plus how long the oldest one has waited
$pendingApprovalRow = $conn->query("
    SELECT COUNT(*) AS cnt,
           DATEDIFF(NOW(), MIN(date_registered)) AS oldest_days
    FROM accounts
    WHERE is_registered = 0
")->fetch_assoc();

$pendingApprovals  = $pendingApprovalRow['cnt'];
$oldestWaitingDays = $pendingApprovalRow['oldest_days'] ?? 0;

// Locked accounts: 5+ failed logins in a row since their last successful login
$lockedSql = "SELECT COUNT(*) AS cnt FROM ( SELECT username, COUNT(*) AS fail_streak
                FROM system_activities sa WHERE action_type_id = 2
                AND timestamp > COALESCE(( SELECT MAX(timestamp) FROM system_activities sa2
                WHERE sa2.username = sa.username AND sa2.action_type_id = 1), '1970-01-01')
                GROUP BY username HAVING fail_streak >= 5) AS locked";
$lockedAccounts = $conn->query($lockedSql)->fetch_assoc()['cnt'];

// Pending users for the "Pending Users" panel (most recent 2)
// Only Ward Councillors, Municipal Officers, and System Admins require approval
$pendingUsersResult = $conn->query("SELECT a.username, a.name, a.surname, a.role, a.date_registered,w.ward_name,mo.division
                        FROM accounts a LEFT JOIN ward_councillors wc ON a.username = wc.username
                        LEFT JOIN wards w ON wc.ward_id = w.ward_id 
                        LEFT JOIN municipal_officers mo ON a.username = mo.username
                        WHERE a.is_registered = 0 AND a.role IN ('Ward Councillor', 'Ward councillor', '2',
                      'Municipal Officer', '3',
                      'System Admin', '4') ORDER BY a.date_registered DESC LIMIT 2");

$pendingUsersList = [];
while ($row = $pendingUsersResult->fetch_assoc()) {
    $pendingUsersList[] = $row;
}
?>