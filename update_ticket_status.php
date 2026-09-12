<?php
session_start();
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/log_helper.php';

header('Content-Type: text/plain');

const ALLOWED_STATUSES = ['Pending', 'In Progress', 'Resolved', 'Closed'];

$username     = $_SESSION['username'] ?? null;
$session_ward = $_SESSION['ward_id'] ?? null;

if (!$username || $session_ward === null) {
    http_response_code(403);
    echo 'You must be logged in to update a ticket.';
    exit;
}

$ticket_id  = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
$new_status = trim($_POST['status'] ?? '');

if ($ticket_id <= 0 || !in_array($new_status, ALLOWED_STATUSES, true)) {
    http_response_code(400);
    echo 'Invalid ticket id or status.';
    exit;
}

// Confirm the ticket exists and belongs to this councillor's ward
$stmt = $conn->prepare("SELECT ward_id, current_status FROM tickets WHERE ticket_id = ?");
$stmt->bind_param('i', $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    http_response_code(404);
    echo 'Ticket not found.';
    exit;
}
if ((int)$ticket['ward_id'] !== (int)$session_ward) {
    http_response_code(403);
    echo "You don't have access to this ticket.";
    exit;
}

if ($ticket['current_status'] === $new_status) {
    // Nothing changed — treat it as a no-op success rather than an error.
    echo 'SUCCESS:' . $ticket_id;
    exit;
}

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("UPDATE tickets SET current_status = ? WHERE ticket_id = ?");
    $stmt->bind_param('si', $new_status, $ticket_id);
    if (!$stmt->execute()) {
        throw new Exception('Could not update the ticket: ' . $stmt->error);
    }

    // Cascade: every report linked to this ticket takes on the ticket's new status.
    $stmt = $conn->prepare("UPDATE reports SET current_status = ? WHERE ticket_id = ?");
    $stmt->bind_param('si', $new_status, $ticket_id);
    if (!$stmt->execute()) {
        throw new Exception('Could not update the linked reports: ' . $stmt->error);
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo 'Could not update ticket status. ' . $e->getMessage(); // remove the detail once fixed, for security
    exit;
}

log_activity($conn, $username, 'TICKET_STATUS_UPDATE');
if ($new_status === 'Closed') {
    log_activity($conn, $username, 'TICKET_CLOSE');
}

echo 'SUCCESS:' . $ticket_id;
