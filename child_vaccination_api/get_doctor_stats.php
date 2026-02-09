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

include 'db_connect.php';

$doctorId = $_GET['doctor_id'];
$today = date('Y-m-d');

// 1. Get number of appointments today
$appointmentsQuery = "
    SELECT COUNT(*) AS total_appointments 
    FROM vaccinations v
    JOIN users u ON v.child_id = u.id
    WHERE v.vaccine_date = ? AND u.doctor_id = ?  and c.child_del=0 and u.user_del=0
";

$stmt1 = $conn->prepare($appointmentsQuery);
$stmt1->bind_param("si", $today, $doctorId);
$stmt1->execute();
$appointmentsResult = $stmt1->get_result()->fetch_assoc();

// 2. Get number of vaccinations given by doctor today
$vaccinationsQuery = "
    SELECT COUNT(*) AS total_vaccinations 
    FROM vaccinations 
    WHERE vaccinated_date = ? AND vaccined_by = ?
";

$stmt2 = $conn->prepare($vaccinationsQuery);
$stmt2->bind_param("ss", $today, $doctorId);
$stmt2->execute();
$vaccinationsResult = $stmt2->get_result()->fetch_assoc();

$response = [
    'appointments_today' => $appointmentsResult['total_appointments'],
    'vaccinations_given_today' => $vaccinationsResult['total_vaccinations']
];

echo json_encode($response);
?>
