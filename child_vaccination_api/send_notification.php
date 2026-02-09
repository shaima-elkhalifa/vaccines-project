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

$title = $_POST['title'] ?? 'New Vaccine';
$body = $_POST['body'] ?? 'A new vaccine has been added';
$receiverId = $_POST['receiver_id'] ?? '';

if (!$receiverId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing receiver_id']);
    exit;
}


$query = "SELECT fcm_token FROM user_tokens WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $receiverId);
$stmt->execute();
$result = $stmt->get_result();

$tokens = [];
while ($row = $result->fetch_assoc()) {
    if (!empty($row['fcm_token'])) {
        $tokens[] = $row['fcm_token'];
    }
}

if (empty($tokens)) {
    http_response_code(404);
    echo json_encode(['error' => 'No FCM tokens found for user']);
    exit;
}

// تحميل بيانات ملف الخدمة
$serviceAccount = json_decode(file_get_contents('firebase_credentials.json'), true);
$projectId = $serviceAccount['project_id'];
$clientEmail = $serviceAccount['client_email'];
$privateKey = $serviceAccount['private_key'];

// إنشاء JWT يدويًا
$now = time();
$jwtHeader = base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
$jwtClaim = base64UrlEncode(json_encode([
    'iss' => $clientEmail,
    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
    'aud' => 'https://oauth2.googleapis.com/token',
    'iat' => $now,
    'exp' => $now + 3600,
]));

$jwtData = $jwtHeader . '.' . $jwtClaim;
openssl_sign($jwtData, $signature, $privateKey, 'sha256WithRSAEncryption');
$jwt = $jwtData . '.' . base64UrlEncode($signature);

// طلب Access Token
$tokenResponse = sendHttpRequest('https://oauth2.googleapis.com/token', [
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'assertion' => $jwt,
]);

if (!isset($tokenResponse['access_token'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to obtain access token']);
    exit;
}

$accessToken = $tokenResponse['access_token'];

// إرسال إشعار لكل توكن
$results = [];
foreach ($tokens as $deviceToken) {
    $fcmUrl = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
    $fcmPayload = [
        'message' => [
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ],
    ];

    $fcmResponse = sendHttpRequest($fcmUrl, $fcmPayload, $accessToken);
    $results[] = [
        'token' => $deviceToken,
        'response' => $fcmResponse,
    ];
}

echo json_encode(['success' => true, 'results' => $results]);


// ======== دوال المساعدة ========
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function sendHttpRequest($url, $data, $accessToken = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    $headers = ['Content-Type: application/json'];
    if ($accessToken) {
        $headers[] = 'Authorization: Bearer ' . $accessToken;
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return ['error' => curl_error($ch)];
    }

    curl_close($ch);
    return json_decode($result, true);
}
?>
