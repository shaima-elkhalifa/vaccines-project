<?php
header("Access-Control-Allow-Origin: http://localhost:5661");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
include './../db_connection.php';
header('Content-Type: application/json');

$tokenId = $_POST['token_id'] ?? null;

if (!$tokenId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing token_id']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM user_tokens WHERE id = ?");
$stmt->bind_param("i", $tokenId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Token not found or already deleted']);
}
