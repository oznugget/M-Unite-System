<?php
session_start();
header('Content-Type: text/plain');
require_once __DIR__ . '/db_connect.php';

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

// Confirm the ticket exists AND belongs to this councillor's ward
$check = $conn->prepare("SELECT ticket_id FROM tickets WHERE ticket_id = ? AND ward_id = ?");
$check->bind_param('ii', $ticket_id, $ward_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    http_response_code(404);
    echo 'Ticket not found.';
    exit;
}

$placeholders = implode(',', array_fill(0, count($report_ids), '?'));

// Only allow linking reports that are unassigned AND in the same ward
$stmt = $conn->prepare("UPDATE reports SET ticket_id = ? WHERE report_id IN ($placeholders) AND ticket_id IS NULL AND ward_id = ?");
$types = 'i' . str_repeat('i', count($report_ids)) . 'i';
$params = array_merge([$ticket_id], $report_ids, [$ward_id]);
$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) {
    http_response_code(500);
    echo 'Execute failed (link reports): ' . $stmt->error;
    exit;
}

echo "SUCCESS:" . $stmt->affected_rows;
