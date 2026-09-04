<?php


require 'db-connect.php'; // gives us $pdo, our database connection



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'brown@gmail.com';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method.');
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
    die('Submission failed: ' . implode(' ', $errors));
}




$categoryStmt = $pdo->prepare('SELECT category_id FROM service_categories WHERE category_name = :name');
$categoryStmt->execute(['name' => $faultType]);
$categoryRow = $categoryStmt->fetch();

if (!$categoryRow) {
    
    die('Submission failed: invalid fault type selected.');
}

$categoryId = $categoryRow['category_id'];




$wardStmt = $pdo->prepare('SELECT ward_id FROM wards WHERE ward_name = :name');
$wardStmt->execute(['name' => 'Ward ' . $wardNumber]);
$wardRow = $wardStmt->fetch();

if (!$wardRow) {
    die('Submission failed: could not match the pinned location to a known ward.');
}

$wardId = $wardRow['ward_id'];


/* ----- HANDLE THE IMAGE UPLOAD (OPTIONAL) ----- */

$imageUrl = null; // stays null if no image was uploaded — image_url is a nullable column

if (isset($_FILES['fault-image']) && $_FILES['fault-image']['error'] === UPLOAD_ERR_OK) {

    $uploadedFile = $_FILES['fault-image'];



    $imageInfo = @getimagesize($uploadedFile['tmp_name']);
    $detectedType = $imageInfo ? $imageInfo['mime'] : '';

    $allowedTypes = ['image/jpeg', 'image/png'];
    $maxSizeInBytes = 128 * 1024 * 1024; // 128MB, matching the JS validation rule

    if (!in_array($detectedType, $allowedTypes)) {
        die('Submission failed: uploaded file is not a valid .jpg, .jpeg, or .png image.');
    }

    if ($uploadedFile['size'] > $maxSizeInBytes) {
        die('Submission failed: uploaded image exceeds the 128MB size limit.');
    }

    
    $extension = ($detectedType === 'image/png') ? 'png' : 'jpg';
    $newFilename = uniqid('report_', true) . '.' . $extension;

    
    $uploadDirectory = __DIR__ . '/uploads/';
    $destinationPath = $uploadDirectory . $newFilename;

    if (move_uploaded_file($uploadedFile['tmp_name'], $destinationPath)) {
        
        $imageUrl = 'uploads/' . $newFilename;
    } else {
        die('Submission failed: could not save the uploaded image.');
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



header('Location: CommReports.html?submitted=1');
exit;