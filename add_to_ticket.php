<?php
session_start();
header('Content-Type: text/plain');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/categories.php';

$ticket_id = (int)($_POST['ticket_id'] ?? 0);
$report_ids = $_POST['report_ids'] ?? [];
$report_ids = array_values(array_filter(array_map('intval', $report_ids)));

if ($ticket_id <= 0 || empty($report_ids)) {
    http_response_code(400);
    echo 'A ticket and at least one report are required.';
    exit;
}

$ward_id = $_SESSION['ward_id'] ?? null;
if (!$ward_id) {
    http_response_code(403);
    echo 'No ward set for this session. Please log in again.';
    exit;
}

// Confirm the ticket exists AND belongs to this councillor's ward, and
// grab its category so we can enforce that every report we add matches
// the ticket's type (a ticket can only ever hold one type of report).
$check = $conn->prepare("SELECT ticket_id, category_id FROM tickets WHERE ticket_id = ? AND ward_id = ?");
$check->bind_param('ii', $ticket_id, $ward_id);
$check->execute();
$ticket = $check->get_result()->fetch_assoc();
if (!$ticket) {
    http_response_code(404);
    echo 'Ticket not found.';
    exit;
}

$ticket_category_id = $ticket['category_id'];
if ($ticket_category_id === null) {
    // Legacy/mixed ticket with no single category — nothing to match against.
    http_response_code(400);
    echo 'This ticket has no single report type, so reports cannot be added to it automatically.';
    exit;
}

$placeholders = implode(',', array_fill(0, count($report_ids), '?'));

// Reject up front if any selected report doesn't match the ticket's type,
// so the user gets a clear message instead of a silent partial link.
$mismatch_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM reports WHERE report_id IN ($placeholders) AND category_id != ?");
$mismatch_types = str_repeat('i', count($report_ids)) . 'i';
$mismatch_params = array_merge($report_ids, [$ticket_category_id]);
$mismatch_stmt->bind_param($mismatch_types, ...$mismatch_params);
$mismatch_stmt->execute();
$mismatch = $mismatch_stmt->get_result()->fetch_assoc()['cnt'];
if ($mismatch > 0) {
    http_response_code(400);
    echo 'Selected reports must match this ticket\'s type (' . category_name($ticket_category_id) . ').';
    exit;
}

// Only allow linking reports that are unassigned, in the same ward, AND the same type as the ticket
$stmt = $conn->prepare("UPDATE reports SET ticket_id = ? WHERE report_id IN ($placeholders) AND ticket_id IS NULL AND ward_id = ? AND category_id = ?");
$types = 'i' . str_repeat('i', count($report_ids)) . 'ii';
$params = array_merge([$ticket_id], $report_ids, [$ward_id, $ticket_category_id]);
$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) {
    http_response_code(500);
    echo 'Execute failed (link reports): ' . $stmt->error;
    exit;
}

echo "SUCCESS:" . $stmt->affected_rows;
