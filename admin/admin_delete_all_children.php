<?php
header("Access-Control-Allow-Origin: http://localhost:5661");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}include './../db_connection.php';
header('Content-Type: application/json');

$userId = $_POST['user_id'] ?? null;

if (!$userId) {
  echo json_encode(['status' => 'error', 'message' => 'Missing user_id']);
  exit;
}

$stmt = $conn->prepare("DELETE FROM children WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$success = $stmt->execute();

if ($success) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Failed to delete all']);
}
