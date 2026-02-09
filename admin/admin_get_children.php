<?php
header("Access-Control-Allow-Origin: http://localhost:5661");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include './../db_connection.php'; // غيّري الاسم لو ملفك مختلف

header('Content-Type: application/json; charset=utf-8');

// ندعم GET و POST
$userId = $_POST['user_id'] ?? $_GET['user_id'] ?? null;
$role   = $_POST['role'] ?? $_GET['role'] ?? null;

if (!$userId) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Missing user ID'
    ]);
    exit();
}

$userId = (int)$userId;

// نحدد الاستعلام حسب الـ role
if ($role === 'parent') {
    // أطفال هذا الأب/الأم
    $stmt = $conn->prepare(
        "SELECT id, name, birth_date
         FROM children
         WHERE user_id = ?"
    );
} elseif ($role === 'doctor') {
    // الأطفال المتابعين مع هذا الدكتور
    $stmt = $conn->prepare(
        "SELECT DISTINCT id, name, birth_date
         FROM children
         WHERE doctor_id = ?"
    );
} elseif ($role === 'nurse') {
    // الأطفال المتابعين مع هذه النيرس من جدول التطعيمات
    $stmt = $conn->prepare(
        "SELECT DISTINCT c.id, c.name, c.birth_date
         FROM children c
         JOIN vaccinations v ON c.id = v.child_id
         WHERE v.nurse_id = ?"
    );
} else {
    // افتراضي: أطفال الـ user_id
    $stmt = $conn->prepare(
        "SELECT id, name, birth_date
         FROM children
         WHERE user_id = ?"
    );
}

if (!$stmt) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database error: ' . $conn->error
    ]);
    exit();
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$children = [];
while ($row = $result->fetch_assoc()) {
    $children[] = [
        'id'   => (int)$row['id'],
        'name' => $row['name'],
        'dob'  => $row['birth_date'],
    ];
}

echo json_encode([
    'status'   => 'success',
    'children' => $children
]);
?>
