<?php
session_start();
require "dbConnection.php";

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "You must be signed in."]);
    exit();
}

$username = $_SESSION['username'];

$dam_id = isset($_POST['dam_id']) ? intval($_POST['dam_id']) : 0;
$dam_name = isset($_POST['dam_name']) ? trim($_POST['dam_name']) : '';
$level_percent = isset($_POST['level_percent']) ? filter_var($_POST['level_percent'], FILTER_VALIDATE_FLOAT) : false;

if ($dam_id <= 0 || $dam_name === '' || $level_percent === false || $level_percent < 0 || $level_percent > 100) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid dam name or level."]);
    exit();
}

$stmt = $conn->prepare("UPDATE dams_levels SET dam_name = ?, level_percent = ?, updated_at = NOW(), updated_by = ? WHERE dam_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database statement error."]);
    exit();
}

$stmt->bind_param("sdsi", $dam_name, $level_percent, $username, $dam_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Failed to save changes."]);
}

$stmt->close();
$conn->close();
