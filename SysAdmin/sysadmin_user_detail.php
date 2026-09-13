<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require "dbConnection.php";

header('Content-Type: application/json');

// Must be logged in as System Admin
$allowedRoles = ['System Admin', '4'];
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

$username = isset($_GET['username']) ? trim($_GET['username']) : '';

if ($username === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing username']);
    exit();
}

$sql = "
    SELECT a.username, a.name, a.surname, a.phone_number, a.role, a.is_registered, a.active_status, a.date_registered,
           wc_w.ward_name AS councillor_ward,
           cm_w.ward_name AS citizen_ward,
           mo.division,
           (
             SELECT COUNT(*) FROM system_activities sa
             WHERE sa.username = a.username AND sa.action_type_id = 2
               AND sa.timestamp > COALESCE(
                   (SELECT MAX(timestamp) FROM system_activities sa2
                    WHERE sa2.username = a.username AND sa2.action_type_id = 1),
                   '1970-01-01'
               )
           ) AS fail_streak,
           (
             SELECT MAX(timestamp) FROM system_activities sa3
             WHERE sa3.username = a.username AND sa3.action_type_id = 1
           ) AS last_login
    FROM accounts a
    LEFT JOIN ward_councillors wc ON a.username = wc.username
    LEFT JOIN wards wc_w ON wc.ward_id = wc_w.ward_id
    LEFT JOIN community_member cm ON a.username = cm.username
    LEFT JOIN wards cm_w ON cm.ward_id = cm_w.ward_id
    LEFT JOIN municipal_officers mo ON a.username = mo.username
    WHERE a.username = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();

// Resolve display role + ward, same logic as the table
if (in_array($row['role'], ['Ward Councillor', 'Ward councillor', '2'])) {
    $roleLabel = 'Ward Councillor';
    $wardLabel = $row['councillor_ward'] ?? '—';
} elseif (in_array($row['role'], ['Municipal Officer', '3'])) {
    $roleLabel = 'Municipal Officer';
    $wardLabel = 'Community-wide';
} elseif (in_array($row['role'], ['System Admin', '4'])) {
    $roleLabel = 'System Admin';
    $wardLabel = 'Community-wide';
} else {
    $roleLabel = 'Citizen';
    $wardLabel = $row['citizen_ward'] ?? '—';
}

if ($row['fail_streak'] >= 5) {
    $statusLabel = 'Locked';
    $statusClass = 'locked';
} elseif ($row['is_registered'] == 0) {
    $statusLabel = 'Pending';
    $statusClass = 'pending';
} elseif ($row['active_status'] == 1) {
    $statusLabel = 'Active';
    $statusClass = 'active';
} else {
    $statusLabel = 'Inactive';
    $statusClass = 'inactive';
}

$initials = strtoupper(substr($row['name'], 0, 1) . substr($row['surname'], 0, 1));

echo json_encode([
    'username'      => $row['username'],
    'name'          => $row['name'] . ' ' . $row['surname'],
    'initials'      => $initials,
    'role_label'    => $roleLabel,
    'ward_label'    => $wardLabel,
    'status_label'  => $statusLabel,
    'status_class'  => $statusClass,
    'email'         => $row['username'], // accounts.username is the email/login
    'phone'         => $row['phone_number'] ?: '—',
    'registered'    => date('d M Y', strtotime($row['date_registered'])),
    'last_login'    => $row['last_login'] ? date('d M Y, H:i', strtotime($row['last_login'])) : '—',
    'is_pending'    => $row['is_registered'] == 0,
]);