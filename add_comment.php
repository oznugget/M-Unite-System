<?php
session_start();
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/log_helper.php';

header('Content-Type: text/plain');

$username     = $_SESSION['username'] ?? null;
$session_ward = $_SESSION['ward_id'] ?? null;

if (!$username || $session_ward === null) {
    http_response_code(403);
    echo 'You must be logged in to comment.';
    exit;
}

$ticket_id    = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
$comment_text = trim($_POST['comment_text'] ?? '');

if ($ticket_id <= 0 || $comment_text === '') {
    http_response_code(400);
    echo 'Missing ticket id or comment text.';
    exit;
}

// Confirm the ticket exists and belongs to this councillor's ward before
// allowing the comment, same restriction ticket.php applies for viewing.
$stmt = $conn->prepare("SELECT ward_id FROM tickets WHERE ticket_id = ?");
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

$stmt = $conn->prepare("INSERT INTO comments (ticket_id, username, comment_text, created_at)
                         VALUES (?, ?, ?, NOW())");
$stmt->bind_param('iss', $ticket_id, $username, $comment_text);

if ($stmt->execute()) {
    log_activity($conn, $username, 'COMMENT_ADD');
    echo 'SUCCESS:' . $conn->insert_id;
} else {
    http_response_code(500);
    echo 'Could not save comment. ' . $stmt->error; // remove the detail once fixed, for security
}
