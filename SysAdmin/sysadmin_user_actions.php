<?php
// Must run before any HTML output, since it may send a Location header.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only do anything if a form on this page was actually submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // ---- Auth guard (same rule as sysadmin_users_data.php) ----
    if (!isset($_SESSION['username'])) {
        header("Location: signin.php");
        exit();
    }
    $allowedRoles = ['System Admin', '4'];
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        header("Location: signIN.php");
        exit();
    }

    require "dbConnection.php";

    $action   = trim($_POST['action']);
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';

    // Where to send the admin back to afterwards (keeps their filters/page,
    // and keeps the panel open on the same user via ?username=...)
    $returnParams = [
        'search'   => $_POST['return_search']   ?? '',
        'role'     => $_POST['return_role']     ?? 'All roles',
        'status'   => $_POST['return_status']   ?? 'All statuses',
        'page'     => $_POST['return_page']     ?? 1,
        'username' => $username,
    ];
    $returnUrl = 'sysadmin_users.php?' . http_build_query($returnParams);

    if ($username === '') {
        header("Location: $returnUrl");
        exit();
    }

    switch ($action) {

        case 'assign_role':
            $newRole = isset($_POST['new_role']) ? trim($_POST['new_role']) : '';
            $validRoles = ['Community Member', 'Ward Councillor', 'Municipal Officer', 'System Admin'];

            if (in_array($newRole, $validRoles)) {

                $stmt = $conn->prepare("UPDATE accounts SET role = ? WHERE username = ?");
                $stmt->bind_param("ss", $newRole, $username);
                $stmt->execute();
                $stmt->close();

                // Log the System Admin action
                $stmt = $conn->prepare(
                    "INSERT INTO system_activities (username, action_type_id, timestamp)
                    VALUES (?, 36, NOW())"
                );
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->close();
            }

            break;

            case 'unlock':
                // Log the System Admin unlocking the account
                $stmt = $conn->prepare(
                    "INSERT INTO system_activities (username, action_type_id, timestamp)
                    VALUES (?, 34, NOW())"
                );
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->close();
                break;

            case 'suspend':
                $stmt = $conn->prepare("UPDATE accounts SET active_status = 0 WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->close();

                // Log the System Admin action
                $stmt = $conn->prepare(
                    "INSERT INTO system_activities (username, action_type_id, timestamp)
                    VALUES (?, 31, NOW())"
                );
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->close();

                break;
            case 'activate':
                $stmt = $conn->prepare("UPDATE accounts SET active_status = 1 WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->close();

                // Log the System Admin action
                $stmt = $conn->prepare(
                    "INSERT INTO system_activities (username, action_type_id, timestamp)
                    VALUES (?, 32, NOW())"
                );
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->close();

                break;

        case 'activate':
            $stmt = $conn->prepare("UPDATE accounts SET active_status = 1 WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();

            // Log the System Admin action
            $stmt = $conn->prepare(
                "INSERT INTO system_activities (username, action_type_id, timestamp)
                VALUES (?, 32, NOW())"
            );
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();

            break;

        case 'remove':
            // Soft delete only — never a real DELETE.
            $stmt = $conn->prepare(
                "UPDATE accounts SET is_deleted = 1, active_status = 0 WHERE username = ?"
            );
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();

            // Log the System Admin action
            $stmt = $conn->prepare(
                "INSERT INTO system_activities (username, action_type_id, timestamp)
                VALUES (?, 33, NOW())"
            );
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();

            // Since removed users are filtered out of the list, send the admin
            // back without ?username= so the panel doesn't try to reopen it.
            unset($returnParams['username']);
            $returnUrl = 'sysadmin_users.php?' . http_build_query($returnParams);

            break;
    }

    header("Location: $returnUrl");
    exit();
}