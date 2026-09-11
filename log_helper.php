<?php
/**
 * Shared helper for writing to the audit log.
 *
 * UPDATE: confirmed against the latest schema screenshot - the real table
 * is `system_activities` (normal spelling). Earlier versions of this file
 * pointed at `systems_acitivies` (a typo'd name from an older screenshot),
 * which meant every log_activity() call was silently failing since
 * $conn->prepare() on a bad table name just returns false and gets
 * skipped by the `if ($stmt)` check below - no error, no log entry.
 *
 * signin.php still logs to a table called `logtrails` separately - that's
 * a different, still-unresolved naming mismatch your team should settle
 * (rename logtrails to system_activities, or vice versa, so there's ONE
 * audit log table instead of two).
 *
 * ACTION TYPE IDs USED BELOW - these must exist as rows in your
 * `action_types` table or the action_type_id will point at nothing.
 * Confirm with your team, or run:
 *
 *   INSERT INTO action_types (action_code, category, description) VALUES
 *   ('TICKET_STATUS_UPDATED', 'Ticket', 'Officer updated a ticket status'),
 *   ('NOTICE_CREATED', 'Notice', 'Officer posted a new notice'),
 *   ('NOTICE_DELETED', 'Notice', 'Officer deleted a notice'),
 *   ('PROFILE_UPDATED', 'Account', 'Officer updated their profile'),
 *   ('ISSUE_CREATED', 'Current Issue', 'Officer created a current issue'),
 *   ('ISSUE_FEATURED', 'Current Issue', 'Officer marked an issue as featured'),
 *   ('ISSUE_DELETED', 'Current Issue', 'Officer deleted a current issue');
 *
 * Then check the actual action_type_id values MySQL assigned and update
 * the constants below to match.
 */

define('ACTION_SUCCESSFUL_LOGIN', 1);   // already used by signin.php
define('ACTION_FAILED_LOGIN', 2);       // already used by signin.php
define('ACTION_TICKET_STATUS_UPDATED', 3);
define('ACTION_NOTICE_CREATED', 4);
define('ACTION_NOTICE_DELETED', 5);
define('ACTION_PROFILE_UPDATED', 6);
define('ACTION_ISSUE_CREATED', 8);
define('ACTION_ISSUE_FEATURED', 9);
define('ACTION_ISSUE_DELETED', 10);

function log_activity($conn, $username, $action_type_id) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $timestamp  = date('Y-m-d H:i:s');
    $is_auth    = 1;

    $stmt = $conn->prepare("INSERT INTO system_activities (username, action_type_id, ip_address, start_session, end_session, is_authenticated) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sisssi", $username, $action_type_id, $ip_address, $timestamp, $timestamp, $is_auth);
        $stmt->execute();
        $stmt->close();
    }
}
?>
