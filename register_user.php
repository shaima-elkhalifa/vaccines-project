<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");
include 'db_connection.php';

// جلب البيانات
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'parent';

if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "All fields are required."]);
    exit;
}

// التحقق من وجود المستخدم مسبقًا
$checkQuery = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkQuery->bind_param("s", $email);
$checkQuery->execute();
$checkQuery->store_result();

if ($checkQuery->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email already exists."]);
    $checkQuery->close();
    $conn->close();
    exit;
}
$checkQuery->close();

// ⚠️ التشفير الأفضل لكلمة المرور
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// إضافة المستخدم
$insertQuery = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$insertQuery->bind_param("ssss", $name, $email, $hashedPassword, $role);

if ($insertQuery->execute()) {
    echo json_encode(["success" => true, "message" => "User registered successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Registration failed: " . $insertQuery->error]);
}

$insertQuery->close();
$conn->close();
?>
