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

// ---- Read filters/pagination from the URL ----
$search     = isset($_GET['search']) ? trim($_GET['search']) : '';
$roleFilter = isset($_GET['role']) ? trim($_GET['role']) : 'All roles';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'All statuses';

$perPage = 20;
$page    = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset  = ($page - 1) * $perPage;

// Map the dropdown label to the actual role values stored in accounts.role
$roleMap = [
    'Community Member'  => ['Community Member', '1'],
    'Ward Councillor'   => ['Ward Councillor', 'Ward councillor', '2'],
    'Municipal Officer' => ['Municipal Officer', '3'],
    'System Admin'      => ['System Admin', '4'],
];

// ---- Base query: one row per account, with role-specific ward/division joined in ----
// fail_streak = failed logins since their last successful login (same logic as the dashboard)
$baseSql = "
    SELECT t.* FROM (
        SELECT a.username, a.name, a.surname, a.role, a.is_registered, a.active_status, a.date_registered,
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
               ) AS fail_streak
        FROM accounts a
        LEFT JOIN ward_councillors wc ON a.username = wc.username
        LEFT JOIN wards wc_w ON wc.ward_id = wc_w.ward_id
        LEFT JOIN community_member cm ON a.username = cm.username
        LEFT JOIN wards cm_w ON cm.ward_id = cm_w.ward_id
        LEFT JOIN municipal_officers mo ON a.username = mo.username
    ) AS t
    WHERE 1=1
";

$conditions = [];
$params = [];
$types  = "";

// Search: name, surname, or username
if ($search !== '') {
    $conditions[] = "(t.name LIKE ? OR t.surname LIKE ? OR t.username LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}

// Role filter
if ($roleFilter !== 'All roles' && isset($roleMap[$roleFilter])) {
    $variants = $roleMap[$roleFilter];
    $placeholders = implode(',', array_fill(0, count($variants), '?'));
    $conditions[] = "t.role IN ($placeholders)";
    foreach ($variants as $v) {
        $params[] = $v;
        $types .= "s";
    }
}

// Status filter (locked takes priority, matching the dashboard's definition)
if ($statusFilter === 'Pending') {
    $conditions[] = "t.is_registered = 0 AND t.fail_streak < 5";
} elseif ($statusFilter === 'Locked') {
    $conditions[] = "t.fail_streak >= 5";
} elseif ($statusFilter === 'Active') {
    $conditions[] = "t.is_registered = 1 AND t.active_status = 1 AND t.fail_streak < 5";
}

if (!empty($conditions)) {
    $baseSql .= " AND " . implode(" AND ", $conditions);
}

// ---- Count total matching rows (for pagination) ----
$countSql = "SELECT COUNT(*) AS cnt FROM ($baseSql) AS counted";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalUsers = $countStmt->get_result()->fetch_assoc()['cnt'];
$countStmt->close();

$totalPages = max(1, ceil($totalUsers / $perPage));
$page = min($page, $totalPages); // clamp if someone requests a page past the end

// ---- Fetch the actual page of results ----
$dataSql = $baseSql . " ORDER BY t.date_registered DESC LIMIT ? OFFSET ?";
$dataStmt = $conn->prepare($dataSql);

$allParams = $params;
$allParams[] = $perPage;
$allParams[] = $offset;
$allTypes = $types . "ii";

$dataStmt->bind_param($allTypes, ...$allParams);
$dataStmt->execute();
$result = $dataStmt->get_result();

$usersList = [];
while ($row = $result->fetch_assoc()) {
    // Resolve display role, ward, and status per row
    if (in_array($row['role'], ['Ward Councillor', 'Ward councillor', '2'])) {
        $row['role_label'] = 'Ward Councillor';
        $row['ward_label'] = $row['councillor_ward'] ?? '—';
    } elseif (in_array($row['role'], ['Municipal Officer', '3'])) {
        $row['role_label'] = 'Municipal Officer';
        $row['ward_label'] = 'Community-wide';
    } elseif (in_array($row['role'], ['System Admin', '4'])) {
        $row['role_label'] = 'System Admin';
        $row['ward_label'] = 'Community-wide';
    } else {
        $row['role_label'] = 'Citizen';
        $row['ward_label'] = $row['citizen_ward'] ?? '—';
    }

    if ($row['fail_streak'] >= 5) {
        $row['status_label'] = 'Locked';
        $row['status_class'] = 'locked';
    } elseif ($row['is_registered'] == 0) {
        $row['status_label'] = 'Pending';
        $row['status_class'] = 'pending';
    } elseif ($row['active_status'] == 1) {
        $row['status_label'] = 'Active';
        $row['status_class'] = 'active';
    } else {
        $row['status_label'] = 'Inactive';
        $row['status_class'] = 'inactive';
    }

    $usersList[] = $row;
}
$dataStmt->close();
?>