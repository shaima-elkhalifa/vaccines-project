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

// Get input - support both POST and JSON
$input = json_decode(file_get_contents('php://input'), true);

if ($input) {
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $role = $input['role'] ?? '';
} else {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
}

// تأمين البيانات
$email = mysqli_real_escape_string($conn, $email);
$role = mysqli_real_escape_string($conn, $role);

// استعلام للبحث عن المستخدم مع التحقق من البريد والدور
$sql = "SELECT * FROM users WHERE email = '$email' AND role = '$role'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $dbPassword = $row['password'];
    $passwordValid = false;

    // Check if password matches using different methods
    // Method 1: Check if it's a bcrypt hash (starts with $2y$)
    if (strpos($dbPassword, '$2y$') === 0) {
        $passwordValid = password_verify($password, $dbPassword);
    } 
    // Method 2: Check MD5
    else if ($dbPassword === md5($password)) {
        $passwordValid = true;
    }
    // Method 3: Check plain text (for legacy accounts)
    else if ($dbPassword === $password) {
        $passwordValid = true;
    }

    // التحقق من كلمة المرور
    if ($passwordValid) {
        echo json_encode([
            'status' => 'success',
            'uid' => (string)$row['id'],
            'name' => $row['name'],
            'role' => $row['role'],
            'message' => 'login success'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Incorrect password'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not found or role mismatch'
    ]);
}

$conn->close();
?>
