<?php


require 'db-connect.php';
require 'log_activity.php'; // gives us $pdo, our database connection

function respond($success, $message) {
 
    header('Content-Type: application/json');
 
    echo json_encode([
        'success' => $success,
        'message' => $message,
    ]);
 
    exit; // stops the script immediately — same role "die()" used to play
 
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'brown@gmail.com';

$isAuthenticated = isset($_SESSION['username']) ? 1 : 0;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   respond(false, 'Invalid request method.');
}




$faultType = trim($_POST['fault-type'] ?? '');
$description = trim($_POST['fault-description'] ?? '');
$streetNumber = trim($_POST['house-number'] ?? '');
$streetName = trim($_POST['road-name'] ?? '');
$suburb = trim($_POST['suburb'] ?? '');
$wardNumber = trim($_POST['ward-number'] ?? '');



$errors = [];

if ($description === '') {
    $errors[] = 'Fault description is required.';
}

if ($streetName === '') {
    $errors[] = 'A street/road could not be determined for this location — please drop a pin on a valid road.';
}

if ($suburb === '') {
    $errors[] = 'A suburb could not be determined for this location.';
}

if ($wardNumber === '' || !ctype_digit($wardNumber) || (int)$wardNumber < 1 || (int)$wardNumber > 14) {
    
    $errors[] = 'A valid ward (1–14) could not be determined for this location — please drop a pin on a street with known ward data.';
}


if (!empty($errors)) {
   respond(false, implode(' ', $errors));
}




$categoryStmt = $pdo->prepare('SELECT category_id FROM service_categories WHERE category_name = :name');
$categoryStmt->execute(['name' => $faultType]);
$categoryRow = $categoryStmt->fetch();

if (!$categoryRow) {
    
    respond(false, 'Invalid fault type selected.');
}

$categoryId = $categoryRow['category_id'];




$wardStmt = $pdo->prepare('SELECT ward_id FROM wards WHERE ward_name = :name');
$wardStmt->execute(['name' => 'Ward ' . $wardNumber]);
$wardRow = $wardStmt->fetch();

if (!$wardRow) {
    respond(false, 'Could not match the pinned location to a known ward.');
}

$wardId = $wardRow['ward_id'];


/* ----- HANDLE THE IMAGE UPLOAD (OPTIONAL) ----- */

$imageUrl = null; // stays null if no image was uploaded — image_url is a nullable column

if (isset($_FILES['fault-image']) && $_FILES['fault-image']['error'] === UPLOAD_ERR_OK) {

    $uploadedFile = $_FILES['fault-image'];



    $imageInfo = @getimagesize($uploadedFile['tmp_name']);
    $detectedType = $imageInfo ? $imageInfo['mime'] : '';

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSizeInBytes = 128 * 1024 * 1024; // 128MB, matching the JS validation rule

    if (!in_array($detectedType, $allowedTypes)) {
       respond(false, 'Uploaded file is not a valid .jpg, .jpeg, .png, or .webp image.');
    }

    if ($uploadedFile['size'] > $maxSizeInBytes) {
        respond(false, 'Uploaded image exceeds the 128MB size limit.');
    }

    
    if ($detectedType === 'image/png') {
        $extension = 'png';
    } elseif ($detectedType === 'image/webp') {
        $extension = 'webp';
    } else {
        $extension = 'jpg';
    }
    $newFilename = uniqid('report_', true) . '.' . $extension;

    
    $uploadDirectory = __DIR__ . '/uploads/';
    $destinationPath = $uploadDirectory . $newFilename;

    if (move_uploaded_file($uploadedFile['tmp_name'], $destinationPath)) {
        
        $imageUrl = 'uploads/' . $newFilename;
    } else {
        respond(false, 'Could not save the uploaded image.');
    }

}




$insertStmt = $pdo->prepare('
    INSERT INTO reports
        (username, category_id, ward_id, image_url, street_number, street_name, surburb, description, timestamp)
    VALUES
        (:username, :categoryId, :wardId, :imageUrl, :streetNumber, :streetName, :surburb, :description, :timestamp)
');

$insertStmt->execute([
    'username' => $username,
    'categoryId' => $categoryId,
    'wardId' => $wardId,
    'imageUrl' => $imageUrl,
    'streetNumber' => $streetNumber !== '' ? $streetNumber : null, // nullable column — empty string becomes a real NULL, not a stored blank
    'streetName' => $streetName,
    'surburb' => $suburb,
    'description' => $description,
    'timestamp' => date('Y-m-d H:i:s'), // current date/time, formatted the way MySQL's DATETIME column expects
]);

logActivity($pdo, $username, 'REPORT_CREATE', $isAuthenticated);

if ($imageUrl !== null) {
    logActivity($pdo, $username, 'REPORT_ATTACH_MEDIA', $isAuthenticated);
}

Respond(true, 'Report submitted successfully.');