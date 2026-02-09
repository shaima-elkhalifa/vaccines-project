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
$receiverId = $_POST['receiver_id'] ?? '';
$messageText = $_POST['message'] ?? '';

if (empty($receiverId) || empty($messageText)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing receiver_id or message']);
    exit;
}

// 1. الحصول على FCM token من قاعدة البيانات

$stmt = $conn->prepare("SELECT fcm_token FROM users WHERE id = ?");
$stmt->bind_param("s", $receiverId);
$stmt->execute();
$stmt->bind_result($fcmToken);
$stmt->fetch();
$stmt->close();

if (!$fcmToken) {
    echo json_encode(['error' => 'FCM token not found']);
    exit;
}

// 2. تحميل بيانات ملف الخدمة
$serviceAccount = json_decode(file_get_contents('firebase_credentials.json'), true);
$projectId = $serviceAccount['project_id'];
$clientEmail = $serviceAccount['client_email'];
$privateKey = $serviceAccount['private_key'];

// 3. إنشاء JWT token
$now = time();
$jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
$jwtClaimSet = base64_encode(json_encode([
    'iss' => $clientEmail,
    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
    'aud' => 'https://oauth2.googleapis.com/token',
    'iat' => $now,
    'exp' => $now + 3600,
]));

$signatureInput = $jwtHeader . '.' . $jwtClaimSet;
openssl_sign($signatureInput, $signature, $privateKey, 'sha256WithRSAEncryption');
$jwtAssertion = $signatureInput . '.' . base64_encode($signature);

// 4. طلب access token من Google
$tokenResponse = sendHttpRequest('https://oauth2.googleapis.com/token', [
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'assertion' => $jwtAssertion,
]);

if (!isset($tokenResponse['access_token'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get access token']);
    exit;
}

$accessToken = $tokenResponse['access_token'];

// 5. إرسال الإشعار باستخدام access token
$fcmUrl = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

$fcmPayload = [
    'message' => [
        'token' => $fcmToken,
        'notification' => [
            'title' => 'رسالة جديدة',
            'body' => $messageText
        ],
    ]
];

$fcmResponse = sendHttpRequest($fcmUrl, $fcmPayload, $accessToken);

echo json_encode(['success' => true, 'response' => $fcmResponse]);


// ========== دعم الطلبات باستخدام cURL ==========
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
