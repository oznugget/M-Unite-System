<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$ticket_id = (int)($data['ticket_id'] ?? 0);
$report_ids = $data['report_ids'] ?? [];
$report_ids = array_values(array_filter(array_map('intval', $report_ids)));

if ($ticket_id <= 0 || empty($report_ids)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'A ticket and at least one report are required.']);
    exit;
}

// Confirm the ticket exists
$check = $conn->prepare("SELECT id FROM tickets WHERE id = ?");
$check->bind_param('i', $ticket_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Ticket not found.']);
    exit;
}

$placeholders = implode(',', array_fill(0, count($report_ids), '?'));
$stmt = $conn->prepare("UPDATE reports SET ticket_id = ? WHERE id IN ($placeholders) AND ticket_id IS NULL");
$types = 'i' . str_repeat('i', count($report_ids));
$params = array_merge([$ticket_id], $report_ids);
$stmt->bind_param($types, ...$params);
$stmt->execute();

echo json_encode(['success' => true, 'added' => $stmt->affected_rows]);
