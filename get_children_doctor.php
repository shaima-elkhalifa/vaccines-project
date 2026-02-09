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

include 'db_connection.php'; // اتصال قاعدة البيانات

$doctorId = $_GET['doctor_id'] ?? '';

if (!$doctorId) {
    echo json_encode(['status' => 'error', 'message' => 'Doctor ID is required']);
    exit;
}

$sql = "SELECT c.id AS child_id, c.name AS child_name, c.user_id, p.name AS parent_name
        FROM children c
        JOIN users p ON c.user_id = p.id
        WHERE  c.child_del=0 and c.doctor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $doctorId);
$stmt->execute();
$result = $stmt->get_result();

$children = [];
while ($row = $result->fetch_assoc()) {
    $children[] = $row;
}

echo json_encode(['status' => 'success', 'children' => $children]);
?>
