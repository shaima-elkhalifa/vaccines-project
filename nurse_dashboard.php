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

$nurse_id = $_POST['nurse_id'] ?? '';

if (empty($nurse_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing nurse_id']);
    exit;
}

$today = date('Y-m-d');

// 1. عدد الأطفال الذين تم تطعيمهم من قبل الممرض
$stmt1 = $conn->prepare("SELECT COUNT(*) FROM vaccinations WHERE vaccined_by = ?");
$stmt1->bind_param("s", $nurse_id);
$stmt1->execute();
$stmt1->bind_result($vaccinated_count);
$stmt1->fetch();
$stmt1->close();

// 2. عدد التطعيمات القادمة اليوم
$stmt2 = $conn->prepare("SELECT COUNT(*) FROM vaccinations WHERE vaccine_date = ? AND is_vaccinated = 0");
$stmt2->bind_param("s", $today);
$stmt2->execute();
$stmt2->bind_result($upcoming_today);
$stmt2->fetch();
$stmt2->close();

// 3. عدد الأطفال الذين تم تطعيمهم اليوم
$stmt3 = $conn->prepare("SELECT COUNT(*) FROM vaccinations WHERE vaccined_by = ? AND vaccine_date = ?");

$stmt3->bind_param("ss", $nurse_id, $today);
$stmt3->execute();
$stmt3->bind_result($vaccinated_today);
$stmt3->fetch();
$stmt3->close();

// 4. جلب كل التطعيمات القادمة ولم يتم تطعيمهم بعد
$vaccinations = [];
$query = "
    SELECT v.*, c.name as child_name, c.gender, c.birth_date, c.child_weight, 
           c.record_number, c.head_circumference, c.user_id
    FROM vaccinations v
    JOIN children c ON c.id = v.child_id
    WHERE v.vaccine_date >= CURDATE() AND v.is_vaccinated = 0
    ORDER BY v.vaccine_date ASC
";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $vaccinations[] = [
        'id' => $row['id'],
        'vaccine_name' => $row['vaccine_name'],
        'vaccine_date' => $row['vaccine_date'],
        'is_vaccinated' => $row['is_vaccinated'],
        'child' => [
            'name' => $row['child_name'],
            'gender' => $row['gender'],
            'birth_date' => $row['birth_date'],
            'child_weight' => $row['child_weight'],
            'record_number' => $row['record_number'],
            'head_circumference' => $row['head_circumference'],
            'parent_id' => $row['user_id'],
        ]
    ];
}

echo json_encode([
    'status' => 'success',
    'vaccinated_count' => $vaccinated_count,
    'upcoming_today' => $upcoming_today,
    'vaccinated_today' => $vaccinated_today,
    'vaccinations' => $vaccinations,
]);

$conn->close();
?>
