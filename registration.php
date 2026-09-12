<?php
session_start();

include "dbConnection.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function showError($message) {
    // Send the user back to the same registration form with the friendly
    // message attached, instead of dying on a separate page.
    header("Location: createacc.php?error=" . urlencode($message));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $firstname = isset($_POST["firstname"]) ? trim($_POST["firstname"]) : "";
    $surname = isset($_POST["surname"]) ? trim($_POST["surname"]) : "";
    $nameRegex = '/^[A-Za-z][A-Za-z\s\'-]*$/';

    if (!preg_match($nameRegex, $firstname) || strlen($firstname) < 1) {
        showError("Please input a valid first name.");
    }
    if (!preg_match($nameRegex, $surname) || strlen($surname) < 1) {
        showError("Please input a valid surname.");
    }

    $contact = isset($_POST["contact"]) ? trim($_POST["contact"]) : "";
    $physAdd = isset($_POST["addr"]) ? trim($_POST["addr"]) : "";
    $roleMap = ["1" => "Community Member", "2" => "Ward councillor", "3" => "Municipal Officer", "4" => "System Admin"];
    $role = isset($_POST["userrole"]) ? ($roleMap[$_POST["userrole"]] ?? "") : "";
    $password = isset($_POST["pword"]) ? trim($_POST["pword"]) : "";
    $hash_pword = password_hash($password, PASSWORD_DEFAULT);
    $division = !empty($_POST["division"]) ? trim($_POST["division"]) : null;
    $wcWard = !empty($_POST["wardCouncillorward"]) ? trim($_POST["wardCouncillorward"]) : null;

    // Defense-in-depth: strip any HTML/script content before storing.
    // (Prepared statements below already stop SQL injection — this stops
    // stored XSS from ever getting displayed unescaped elsewhere in the app.)
    $firstname = htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8');
    $surname   = htmlspecialchars($surname, ENT_QUOTES, 'UTF-8');
    $physAdd   = htmlspecialchars($physAdd, ENT_QUOTES, 'UTF-8');
    $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : "";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    showError("Please enter a valid email address.");
    }

    //for splitting the physical address into street number, street name and suburb
    $lat           = $_POST["lat"] ?? "";
    $lon           = $_POST["lon"] ?? "";
    $street_number = $_POST["street_number"] ?? "";
    $street_name   = $_POST["street_name"] ?? "";
    $suburb        = $_POST["suburb"] ?? "";
   
    // Require physical address  if user is a Community Member
    if ($role === "Community Member" && empty($physAdd)) {
        showError("Physical address is required for Community Members.");
    }

    if ($role === "Ward councillor" && empty($wcWard)) {
        showError("Please select your ward.");
    }

    if ($role === "Municipal Officer" && empty($division)) {
        showError("Please select a division.");
    }

    // Default to '0' rather than rejecting registration if no number was found
    if ($street_number === null || $street_number === "") {
        $street_number = "0";
    }

        if ($role !== "Community Member" && strtolower(substr(strrchr($email, "@") ?: "", 1)) !== "makana.gov.za") {
        showError("Cannot create an account of this role with the given email address.");
    }


       // Ward is now extracted client-side (from the same Nominatim response
    // used for address autocomplete) and submitted via the hidden ward_id
    // field — no separate server-side MapIt API call, so registration is
    // no longer blocked by a third-party API timing out or being unreachable.
    $ward_id = isset($_POST["ward_id"]) && ctype_digit($_POST["ward_id"]) ? (int)$_POST["ward_id"] : 1;

    if ($role === "Community Member" && empty($_POST["ward_id"])) {
        error_log("No ward_id submitted for {$email} (lat={$lat}, lon={$lon}). Defaulting to ward_id=1.");
    }

        // Pre-check for duplicate email and duplicate phone number before
    // attempting the insert, so we can give a precise, friendly message
    // without ever needing to inspect the raw SQL error text.
    $username = $email;
    $checkStmt = $conn->prepare("SELECT username, phone_number FROM accounts WHERE username = ? OR phone_number = ? LIMIT 1");
    $checkStmt->bind_param("ss", $username, $contact);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $existing = $result->fetch_assoc();
    $checkStmt->close();

    if ($existing) {
        if ($existing['username'] === $username) {
            showError("An account with this email address already exists. Please <a href='signin.php' style='color:#0E2841; font-weight:bold;'>sign in</a> instead, or use a different email.");
        } else {
            showError("An account with this contact number already exists. Please use a different number, or contact support if this is a mistake.");
        }
    }


  
    $is_registered = 1;
    $active_status = 1;

    // Begin Database Transaction
    $conn->begin_transaction();

    try {
        // 1. Insert into Accounts
        $stmt = $conn->prepare("INSERT INTO accounts (username, password, phone_number, name, surname, role, is_registered, active_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssii", $username, $hash_pword, $contact, $firstname, $surname, $role, $is_registered, $active_status);
        $stmt->execute();
        $stmt->close();


    //Filling in community member table
    if ($role === "Community Member") {
        $stmt2 = $conn->prepare("INSERT INTO community_member(username, ward_id, street_number, street_name, suburb) 
            VALUES (?,?,?,?,?)");
        $stmt2->bind_param("sisss", $username, $ward_id, $street_number, $street_name, $suburb);
        $stmt2->execute();
        $stmt2->close();
    

    //Filling in ward councillor table
    } else if ($role === "Ward councillor") {
        $stmt3 = $conn->prepare("INSERT INTO ward_councillors(username, ward_id) VALUES (?,?)");
        $stmt3->bind_param("si", $username, $wcWard);
        $stmt3->execute();
        $stmt3->close();
    

    //Filling in municipal officer table
    }else if ($role === "Municipal Officer") {
        $stmt4 = $conn->prepare("INSERT INTO municipal_officers(username, division) VALUES (?,?)");
        $stmt4->bind_param("ss", $username, $division);
        $stmt4->execute();
        $stmt4->close();

    } else if ($role === "System Admin") {
        $stmt5 = $conn->prepare("INSERT INTO system_admins(username) VALUES (?)");
        $stmt5->bind_param("s", $username);
        $stmt5->execute();
        $stmt5->close();
    }

        $action_type_id   = 7; // 7 = Account Creation Success
        $ip_address       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $timestamp        = date('Y-m-d H:i:s');
        $is_authenticated = 1;

        $stmtLog = $conn->prepare("INSERT INTO system_activities (username, action_type_id, ip_address, timestamp, is_authenticated) VALUES (?, ?, ?, ?, ?)");

        if ($stmtLog) {
            $stmtLog->bind_param("sissi", $username, $action_type_id, $ip_address, $timestamp, $is_authenticated);
            $stmtLog->execute();
            $stmtLog->close();
        }

        // Commit all changes if no exceptions occurred
        $conn->commit();

        // Redirect after successful commit
        header("Location: signin.php?registration=success");
        exit();

        } catch (mysqli_sql_exception $e) {
        // Rollback any database changes if a query fails
        $conn->rollback();

        // Always log the real technical detail for developers — this NEVER
        // reaches the user, only the server log.
        error_log("Registration DB error [" . $e->getCode() . "]: " . $e->getMessage());

        // MySQL error code 1062 = duplicate entry on a unique key.
        // The pre-check above catches this in the normal case; this only
        // fires on a rare race condition (two identical submissions at once).
        // We deliberately do NOT inspect $e->getMessage() here.
        if ($e->getCode() === 1062) {
            showError("An account with this email address or contact number already exists. Please use different details, or sign in if this is your account.");
        } else {
            // Every other DB failure gets one flat, generic message —
            // no error code, no SQL text, ever shown to the user.
            showError("Something went wrong while creating your account. Please try again, and contact support if the problem continues.");
            error_log("Registration DB error [" . $e->getCode() . "]: " . $e->getMessage());
        }
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        // TEMPORARY DEBUG: Displays the exact database error on your screen
        showError("SQL Error [" . $e->getCode() . "]: " . $e->getMessage());
    } catch (Exception $e) {
        $conn->rollback();
        showError("PHP Error: " . $e->getMessage());
    }
}

$conn->close();
?>   

