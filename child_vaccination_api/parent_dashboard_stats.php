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

// Support both POST and JSON input
$input = json_decode(file_get_contents('php://input'), true);
$userId = '';

if ($input) {
    $userId = $input['user_id'] ?? '';
} else {
    $userId = $_POST['user_id'] ?? '';
}

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing user_id']);
    exit;
}

// جلب جميع الأطفال لهذا المستخدم (using user_id not parent_id)
$childrenQuery = "SELECT id FROM children WHERE child_del = 0 AND user_id = ?";
$stmt = $conn->prepare($childrenQuery);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

$childIds = [];
while ($row = $result->fetch_assoc()) {
    $childIds[] = $row['id'];
}

if (empty($childIds)) {
    echo json_encode([
        'status' => 'success',
        'total_vaccines' => 0,
        'upcoming_vaccines' => 0
    ]);
    exit;
}

$idsPlaceholders = implode(',', array_fill(0, count($childIds), '?'));
$types = str_repeat('i', count($childIds));

// عدد كل التطعيمات
$totalQuery = "SELECT COUNT(*) AS total FROM vaccinations WHERE child_id IN ($idsPlaceholders)";
$stmt = $conn->prepare($totalQuery);
$stmt->bind_param($types, ...$childIds);
$stmt->execute();
$totalResult = $stmt->get_result()->fetch_assoc();
$totalVaccines = $totalResult['total'] ?? 0;

// عدد التطعيمات القادمة (لم يتم تطعيمها بعد) - using is_vaccinated column
$upcomingQuery = "SELECT COUNT(*) AS upcoming FROM vaccinations WHERE child_id IN ($idsPlaceholders) AND (is_vaccinated = 0 OR is_vaccinated IS NULL)";
$stmt = $conn->prepare($upcomingQuery);
$stmt->bind_param($types, ...$childIds);
$stmt->execute();
$upcomingResult = $stmt->get_result()->fetch_assoc();
$upcomingVaccines = $upcomingResult['upcoming'] ?? 0;

echo json_encode([
    'status' => 'success',
    'total_vaccines' => $totalVaccines,
    'upcoming_vaccines' => $upcomingVaccines,
]);
?>
