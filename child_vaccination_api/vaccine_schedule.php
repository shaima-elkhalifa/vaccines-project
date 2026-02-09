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

include 'db_connection.php';  // تأكد من ملف الاتصال بقاعدة البيانات

try {
    $sql = "SELECT id, vaccine_name, due_days, description FROM vaccine_schedule ORDER BY due_days ASC";
    $result = $conn->query($sql);

    $vaccines = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $vaccines[] = $row;
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => $vaccines
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
