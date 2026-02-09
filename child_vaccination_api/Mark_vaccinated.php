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

$id = $_POST['vaccination_id'] ?? '';
$nurse_id = $_POST['nurse_id'] ?? '';

if (empty($id) || empty($nurse_id)) {
    echo json_encode(['status' => false, 'message' => 'Missing fields']);
    exit;
}

$update = $conn->prepare("UPDATE vaccinations SET is_vaccinated = 1, vaccined_by = ?, vaccinated_date = CURDATE() WHERE id = ?");
$update->bind_param("si", $nurse_id, $id);

if ($update->execute()) {
    echo json_encode(['status' => true, 'message' => 'Updated']);
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to update']);
}
?>
