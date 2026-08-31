<?php
session_start();
header('Content-Type: text/plain');
require_once 'db_connect.php';

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$report_ids = $_POST['report_ids'] ?? [];

if ($title === '' || $description === '' || empty($report_ids)) {
    http_response_code(400);
    echo 'Title, description, and at least one report are required.';
    exit;
}

$report_ids = array_values(array_filter(array_map('intval', $report_ids)));
if (empty($report_ids)) {
    http_response_code(400);
    echo 'Invalid report ids.';
    exit;
}



$conn->begin_transaction();
try {
    $placeholders = implode(',', array_fill(0, count($report_ids), '?'));

    $types_stmt = $conn->prepare("SELECT DISTINCT category_id FROM reports WHERE report_id IN ($placeholders)");
    if (!$types_stmt) {
        throw new Exception('Prepare failed (category lookup): ' . $conn->error);
    }
    $types_stmt->bind_param(str_repeat('i', count($report_ids)), ...$report_ids);
    if (!$types_stmt->execute()) {
        throw new Exception('Execute failed (category lookup): ' . $types_stmt->error);
    }
    $types_result = $types_stmt->get_result();

    $distinct_types = [];
    while ($row = $types_result->fetch_assoc()) {
        $distinct_types[] = $row['category_id'];
    }
    $category_id = count($distinct_types) === 1 ? $distinct_types[0] : null;

    $ward_id = $_SESSION['ward_id'] ?? null;
    if (!$ward_id) {
        throw new Exception('No ward set for this session. Please log in again.');
    }

    $check_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM reports WHERE report_id IN ($placeholders) AND ward_id != ?");
    $check_params = array_merge($report_ids, [$ward_id]);
    $check_types = str_repeat('i', count($report_ids)) . 'i';
    $check_stmt->bind_param($check_types, ...$check_params);
    if (!$check_stmt->execute()) {
        throw new Exception('Execute failed (ward check): ' . $check_stmt->error);
    }
    $check_result = $check_stmt->get_result();
    if (!$check_result) {
        throw new Exception('get_result failed (ward check): ' . $check_stmt->error);
    }
    $mismatch = $check_result->fetch_assoc()['cnt'];
    if ($mismatch > 0) {
        throw new Exception('One or more selected reports do not belong to your ward.');
    }

    $username = $_SESSION['username'] ?? $_SESSION['user_name'] ?? $_SESSION['email'] ?? 'system';
    $username = trim((string) $username);
    if ($username === '') {
        $username = 'system';
    }

   
    $ticket_stmt = $conn->prepare(
        "INSERT INTO tickets (title, description, category_id, ward_id, current_status, username, date_created)
         VALUES (?, ?, ?, ?, 'Pending', ?, NOW())"
    );
    if (!$ticket_stmt) {
        throw new Exception('Prepare failed (insert ticket): ' . $conn->error);
    }
    // types: title(s), description(s), category_id(i), current_status is literal, username(s)
    $ticket_stmt->bind_param('ssiis', $title, $description, $category_id, $ward_id, $username);
    if (!$ticket_stmt->execute()) {
        throw new Exception('Execute failed (insert ticket): ' . $ticket_stmt->error);
    }
    $ticket_id = $conn->insert_id;

    if ($ticket_id <= 0) {
        throw new Exception('Insert reported success but no ticket_id was returned.');
    }

    $link_stmt = $conn->prepare("UPDATE reports SET ticket_id = ? WHERE report_id IN ($placeholders)");
    if (!$link_stmt) {
        throw new Exception('Prepare failed (link reports): ' . $conn->error);
    }
    $link_types = 'i' . str_repeat('i', count($report_ids));
    $link_params = array_merge([$ticket_id], $report_ids);
    $link_stmt->bind_param($link_types, ...$link_params);
    if (!$link_stmt->execute()) {
        throw new Exception('Execute failed (link reports): ' . $link_stmt->error);
    }

    $conn->commit();

    echo "SUCCESS:" . $ticket_id;
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo 'Could not create ticket. ' . $e->getMessage(); // remove the detail once fixed, for security
}
