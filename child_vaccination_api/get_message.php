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

include 'db_connection.php'; // الاتصال بقاعدة البيانات

$chatId = $_POST['chat_id'];

$query = "SELECT * FROM messages WHERE chat_id = ? ORDER BY timestamp ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $chatId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode(['messages' => $messages]);
