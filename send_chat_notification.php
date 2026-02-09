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

// ==== أدوات المساعدة (ضعها في الأعلى) ====
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
    curl_close($ch);
    return json_decode($result, true);
}

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

    $tokenResponse = sendHttpRequest("https://oauth2.googleapis.com/token", [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt,
    ]);

    $accessToken = $tokenResponse['access_token'] ?? null;
    if (!$accessToken) return false;

    $payload = [
        'message' => [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => [
                'type' => 'chat',
            ],
        ],
    ];

    $response = sendHttpRequest(
        "https://fcm.googleapis.com/v1/projects/$projectId/messages:send",
        $payload,
        $accessToken
    );

    return isset($response['name']);
}

// ===== تنفيذ عملية الإرسال =====

$receiverId = $_POST['receiver_id'] ?? '';
$title = $_POST['title'] ?? 'new Chat';
$body = $_POST['body'] ?? '';

if (!$receiverId || !$body) {
    echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
    exit;
}

// جلب كل fcm_token الخاصة بالمستخدم
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
    echo json_encode(['status' => 'error', 'message' => 'No tokens found for this user']);
    exit;
}

// إرسال الإشعار
$sentCount = 0;
foreach ($tokens as $token) {
    if (sendFCMNotification($token, $title, $body)) {
        $sentCount++;
    }
}

echo json_encode(['status' => 'success', 'message' => "Notification sent to $sentCount device(s)"]);
?>
