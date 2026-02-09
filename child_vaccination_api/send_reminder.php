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

include "db_connection.php";

$parentId = $_POST['parent_id'];
$vaccineName = $_POST['vaccine_name'];
$vaccineDate = $_POST['vaccine_date'];

$title = "Vaccine Reminder";
$body = "Reminder: $vaccineName is scheduled on $vaccineDate.";

// جلب كل fcm_token الخاصة بالمستخدم
$query = "SELECT fcm_token FROM user_tokens WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $parentId);
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

// إرسال الإشعارات لكل توكن
foreach ($tokens as $token) {
    sendFCMNotification($token, $title, $body);
}
echo json_encode(['status' => 'success', 'message' => 'Reminder sent successfully.']);


// ======== دالة إرسال الإشعار ==========
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
            'data' => [
                'type' => 'reminder'
            ]
        ],
    ];

    httpPost("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", $payload, $accessToken);
}

// ======== أدوات مساعدة ==========
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
