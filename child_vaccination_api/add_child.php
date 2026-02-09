<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Log the raw input for debugging
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Request received\n", FILE_APPEND);
file_put_contents('debug.log', "POST: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents('debug.log', "RAW: " . file_get_contents('php://input') . "\n", FILE_APPEND);

try {
    include 'db_connection.php';

    if (!isset($conn) || $conn->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    // Support both POST and JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // If not JSON, assume it's form data
        $input = $_POST;
    }

    $name = $input['name'] ?? '';
    $birth_date = $input['birth_date'] ?? '';
    $gender = $input['gender'] ?? '';
    $head_circumference = $input['head_circumference'] ?? '';
    $weight = $input['weight'] ?? '';
    $record_number = $input['record_number'] ?? '';
    $user_id = $input['user_id'] ?? '';
    $doctor_id = $input['doctor_id'] ?? '';

    if (
        empty($name) || empty($birth_date) || empty($gender) ||
        empty($record_number) || empty($user_id) || empty($doctor_id)
    ) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']);
        exit;
    }

    // تحقق من التكرار
    $checkQuery = "SELECT id FROM children WHERE record_number = ? AND user_id = ? AND child_del = 0";
    $checkStmt = $conn->prepare($checkQuery);
    
    if (!$checkStmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $checkStmt->bind_param("ss", $record_number, $user_id);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Child with same record number already exists']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // احصل على FCM Token من جدول user_tokens
    $fcm_token = '';
    $tokenQuery = "SELECT fcm_token FROM user_tokens WHERE user_id = ? LIMIT 1";
    $tokenStmt = $conn->prepare($tokenQuery);
    if ($tokenStmt) {
        $tokenStmt->bind_param("s", $user_id);
        $tokenStmt->execute();
        $tokenStmt->bind_result($fcm_token);
        $tokenStmt->fetch();
        $tokenStmt->close();
    }

    // إدخال الطفل
    $insert = "INSERT INTO children (name, birth_date, gender, head_circumference, child_weight, record_number, user_id, doctor_id, child_del) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($insert);
    
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("ssssssss", $name, $birth_date, $gender, $head_circumference, $weight, $record_number, $user_id, $doctor_id);

    if (!$stmt->execute()) {
        $error_message = 'Execute failed: ' . $stmt->error;
        file_put_contents('debug.log', date('Y-m-d H:i:s') . " - " . $error_message . "\n", FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => $error_message]);
        $stmt->close();
        exit;
    }

    $child_id = $stmt->insert_id;
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Child added successfully',
        'child_id' => (string)$child_id,
        'fcm_token' => $fcm_token
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()]);
}
?>
