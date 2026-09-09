<?php
/**
 * log_helper.php
 *
 * Writes one row to system_activities for a given action, looking up the
 * action_type_id from the action_types lookup table by its action_code
 * (e.g. 'TICKET_CREATE', 'COMMENT_ADD' — see action_types for the full list).
 *
 * Basic usage (most actions — one-off events like creating a ticket or
 * adding a comment):
 *
 *   require_once __DIR__ . '/log_helper.php';
 *   log_activity($conn, $_SESSION['username'] ?? null, 'COMMENT_ADD');
 *
 * For login/logout-style events that span a session, pass an explicit
 * end_session timestamp via $opts:
 *
 *   log_activity($conn, $username, 'AUTH_LOGOUT', [
 *       'end_session' => date('Y-m-d H:i:s'),
 *   ]);
 *
 * $opts supports:
 *   'ip_address'       — override the detected REMOTE_ADDR
 *   'end_session'      — datetime string, only set for session-ending events
 *   'is_authenticated' — override the default (1 if $username is set, else 0)
 */

function log_activity(mysqli $conn, ?string $username, string $action_code, array $opts = []) {
    static $action_type_cache = [];

    if (!isset($action_type_cache[$action_code])) {
        $stmt = $conn->prepare("SELECT action_type_id FROM action_types WHERE action_code = ? LIMIT 1");
        $stmt->bind_param('s', $action_code);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            error_log("log_activity: unknown action_code '{$action_code}' — not logged.");
            return false;
        }
        $action_type_cache[$action_code] = (int)$row['action_type_id'];
    }
    $action_type_id = $action_type_cache[$action_code];

    $ip_address       = $opts['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? null);
    $end_session      = $opts['end_session'] ?? null;
    $is_authenticated = array_key_exists('is_authenticated', $opts)
        ? (int)$opts['is_authenticated']
        : ($username !== null ? 1 : 0);

    $stmt = $conn->prepare("INSERT INTO system_activities
            (username, action_type_id, ip_address, start_session, end_session, is_authenticated)
        VALUES (?, ?, ?, NOW(), ?, ?)");
    $stmt->bind_param('sissi', $username, $action_type_id, $ip_address, $end_session, $is_authenticated);

    if (!$stmt->execute()) {
        error_log("log_activity: insert failed — " . $stmt->error);
        return false;
    }
    return $conn->insert_id;
}
