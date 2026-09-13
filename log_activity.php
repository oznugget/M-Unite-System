<?php

/**
 * Records one row in system_activities, resolved against
 * action_types by its action_code (e.g. 'REPORT_CREATE').
 *
 * $pdo             - existing PDO connection (from db-connect.php)
 * $username        - the acting user's username/email
 * $actionCode      - must match an existing action_types.action_code
 * $isAuthenticated - 1 if this came from a real logged-in session, 0 if not
 */
function logActivity($pdo, $username, $actionCode, $isAuthenticated) {

    $actionTypeStmt = $pdo->prepare('SELECT action_type_id FROM action_types WHERE action_code = :code');
    $actionTypeStmt->execute(['code' => $actionCode]);
    $actionTypeRow = $actionTypeStmt->fetch();

    if (!$actionTypeRow) {
        // Don't let a bad/missing action_code break the real request —
        // just note it and move on.
        error_log("logActivity: unknown action_code '$actionCode'");
        return false;
    }

    $insertStmt = $pdo->prepare('
        INSERT INTO system_activities
            (username, action_type_id, ip_address, timestamp, is_authenticated)
        VALUES
            (:username, :actionTypeId, :ipAddress, :timestamp, :isAuthenticated)
    ');

    return $insertStmt->execute([
        'username'        => $username,
        'actionTypeId'    => $actionTypeRow['action_type_id'],
        'ipAddress'       => $_SERVER['REMOTE_ADDR'] ?? null,
        'timestamp'       => date('Y-m-d H:i:s'),
        'isAuthenticated' => $isAuthenticated,
    ]);

}
?>