<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    include 'db_connection.php';

    // Create user_tokens table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS user_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        fcm_token TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id)
    )";
    
    if (!$conn->query($createTable)) {
        throw new Exception("Failed to create table: " . $conn->error);
    }

$user_id = $_POST['user_id'] ?? null;
$fcm_token = $_POST['fcm_token'] ?? null;

$user_id = $input['user_id'] ?? null;
$fcm_token = $input['fcm_token'] ?? null;
    if (!$user_id || !$fcm_token) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
        exit;
    }

    // Check if token already exists for this user
    $stmt = $conn->prepare("SELECT id FROM user_tokens WHERE user_id = ? AND fcm_token = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("is", $user_id, $fcm_token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        // Insert new token
        $stmt->close();
        $insert = $conn->prepare("INSERT INTO user_tokens (user_id, fcm_token) VALUES (?, ?)");
        if (!$insert) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $insert->bind_param("is", $user_id, $fcm_token);
        if (!$insert->execute()) {
            throw new Exception("Execute failed: " . $insert->error);
        }
        $insert->close();
    } else {
        $stmt->close();
    }

    echo json_encode(['status' => 'success', 'message' => 'Token saved successfully']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
