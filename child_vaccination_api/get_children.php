<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'db_connection.php';

$uid = $_POST['user_id'] ?? '';

if (empty($uid)) {
    echo json_encode(['status' => false, 'message' => 'user_id is required']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM children WHERE child_del=0 and  user_id = ?");
$stmt->bind_param("s", $uid);
$stmt->execute();

$result = $stmt->get_result();
$children = [];

while ($row = $result->fetch_assoc()) {
    $children[] = $row;
}

echo json_encode([
    'status' => 'success',
    'children' => $children
]);
