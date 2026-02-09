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

$child_id = $_POST['child_id'] ?? '';
$birth_date = $_POST['birth_date'] ?? '';
$fcm_token = $_POST['fcm_token'] ?? '';

if (empty($child_id) || empty($birth_date)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing child_id or birth_date']);
    exit;
}

// تحقق من وجود جدول تطعيم لهذا الطفل مسبقًا
$checkQuery = "SELECT COUNT(*) as total FROM vaccinations WHERE child_id = '$child_id'";
$checkResult = $conn->query($checkQuery);
$checkData = $checkResult->fetch_assoc();

if ($checkData['total'] > 0) {
    echo json_encode(['status' => 'exists', 'message' => 'Vaccination schedule already exists for this child.']);
    exit;
}

// جلب جدول مواعيد اللقاحات
$scheduleQuery = "SELECT * FROM vaccine_schedule ORDER BY due_days ASC";
$scheduleResult = $conn->query($scheduleQuery);

if (!$scheduleResult || $scheduleResult->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No vaccine schedule found']);
    exit;
}

$birth = new DateTime($birth_date);
$nearestDate = null;
$nearestVaccine = null;

while ($row = $scheduleResult->fetch_assoc()) {
    $vaccine_name = $row['vaccine_name'];
    $due_days = (int)$row['due_days'];

    $appointment_date = (clone $birth)->modify("+$due_days days")->format('Y-m-d');

    $insertQuery = "INSERT INTO vaccinations (child_id, vaccine_date, vaccine_name, notes)
                    VALUES ('$child_id', '$appointment_date', '$vaccine_name', '')";
    $conn->query($insertQuery);

    if (!$nearestDate && $appointment_date >= date('Y-m-d')) {
        $nearestDate = $appointment_date;
        $nearestVaccine = $vaccine_name;
    }
}

if (!empty($fcm_token) && $nearestDate) {
    sendFCMNotification($fcm_token, "موعد تطعيم قادم", "اللقاح القادم: $nearestVaccine في $nearestDate");
}

echo json_encode([
    'status' => 'success',
    'message' => 'Vaccines scheduled successfully.',
    'notified' => !empty($fcm_token),
    'next_vaccine' => $nearestVaccine,
    'next_date' => $nearestDate
]);

$conn->close();

// ========== دالة إرسال الإشعار ==========
function sendFCMNotification($token, $title, $body) {
    $serviceAccount = json_decode(file_get_contents("firebase_credentials.json"), true);
    $projectId = $serviceAccount['project_id'];
    $clientEmail = $serviceAccount['client_email'];
    $privateKey = $serviceAccount['private_key'];

    $now = time();
    $header = base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $claims = base64UrlEncode(json_encode([
        'iss' => $clientEmail,
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600,
    ]));
    $data = $header . '.' . $claims;
    openssl_sign($data, $signature, $privateKey, 'sha256WithRSAEncryption');
    $jwt = $data . '.' . base64UrlEncode($signature);

    $tokenResponse = httpPost("https://oauth2.googleapis.com/token", [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt,
    ]);
    $accessToken = $tokenResponse['access_token'] ?? null;
    if (!$accessToken) return;

    $payload = [
        'message' => [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ],
    ];

    httpPost("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", $payload, $accessToken);
}

// أدوات المساعدة
function httpPost($url, $data, $accessToken = null) {
    $headers = ['Content-Type: application/json'];
    if ($accessToken) {
        $headers[] = 'Authorization: Bearer ' . $accessToken;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
?>
