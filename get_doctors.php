<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'db_connection.php';

$result = $conn->query("SELECT id, name FROM users WHERE user_del=0 and  role = 'doctor'");
$doctors = [];

while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

echo json_encode(['status' => 'success', 'doctors' => $doctors]);
$conn->close();
