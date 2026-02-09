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

require 'db_connection.php';

$doctorId = $_POST['doctor_id'] ?? '';

$response = [
    'status' => false,
    'children_count' => 0,
    'vaccine_count' => 0,
    'upcoming' => [],
    'message' => 'Invalid doctor ID'
];

if ($doctorId) {
    // Get children count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM children WHERE child_del=0 and  doctor_id = ?");
    $stmt->bind_param("s", $doctorId);
    $stmt->execute();
    $stmt->bind_result($response['children_count']);
    $stmt->fetch();
    $stmt->close();

    // Get vaccine count
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM vaccinations v 
        JOIN children c ON v.child_id = c.id 
        WHERE c.child_del=0 and  c.doctor_id = ?
    ");
    $stmt->bind_param("s", $doctorId);
    $stmt->execute();
    $stmt->bind_result($response['vaccine_count']);
    $stmt->fetch();
    $stmt->close();

    // Get upcoming vaccinations (today or future)
    $stmt = $conn->prepare("
        SELECT c.name, v.vaccine_date AS date 
        FROM vaccinations v 
        JOIN children c ON v.child_id = c.id 
        WHERE c.doctor_id = ? AND v.vaccine_date >= CURDATE()
        ORDER BY v.vaccine_date ASC
        LIMIT 10
    ");
    $stmt->bind_param("s", $doctorId);
    $stmt->execute();
    $result = $stmt->get_result();

    $upcoming = [];
    while ($row = $result->fetch_assoc()) {
        $upcoming[] = $row;
    }

    $response['status'] = true;
    $response['upcoming'] = $upcoming;
    $response['message'] = 'Report generated successfully';

    $stmt->close();
}

echo json_encode($response);
$conn->close();
