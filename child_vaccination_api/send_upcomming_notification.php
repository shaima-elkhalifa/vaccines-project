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

$uid = $_POST['user_id'] ?? null;

if (!$uid) {
    echo json_encode(['status' => 'error', 'message' => 'Missing user_id']);
    exit;
}

// جلب مواعيد التطعيم غير المرسَل إشعار بها
$query = "
    SELECT v.id, v.child_id, v.vaccine_date, v.vaccine_name, c.name AS child_name
    FROM vaccinations v
    JOIN children c ON c.id = v.child_id
    JOIN users u ON u.id = c.user_id
    WHERE v.vaccine_date >= CURDATE()
      AND (v.notified IS NULL OR v.notified = 0)
      AND u.id = ?
    ORDER BY v.vaccine_date ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

$notificationsSent = [];

if ($result && $result->num_rows > 0) {
    // جلب جميع fcm_tokens الخاصة بالمستخدم
    $tokenStmt = $conn->prepare("SELECT fcm_token FROM user_tokens WHERE user_id = ?");
    $tokenStmt->bind_param("i", $uid);
    $tokenStmt->execute();
    $tokenResult = $tokenStmt->get_result();

    $tokens = [];
    while ($row = $tokenResult->fetch_assoc()) {
        if (!empty($row['fcm_token'])) {
            $tokens[] = $row['fcm_token'];
        }
    }
    $tokenStmt->close();

    // إذا لا توجد توكنات، نخرج
    if (empty($tokens)) {
        echo json_encode(['status' => 'no_tokens', 'message' => 'No FCM tokens found for this user']);
        exit;
    }

    // إرسال إشعار لكل موعد تطعيم
    while ($row = $result->fetch_assoc()) {
        $vaccineId   = (int) $row['id'];
        $childName   = $row['child_name'];
        $vaccineDate = $row['vaccine_date'];
        $vaccineName = $row['vaccine_name'];

        $title = "موعد تطعيم قادم";
        $body  = "الطفل $childName لديه تطعيم $vaccineName بتاريخ $vaccineDate";

        $successCount = 0;

        foreach ($tokens as $token) {
            if (sendFCMNotification($token, $title, $body)) {
                $successCount++;
            }
        }

        if ($successCount > 0) {
            $update = $conn->prepare("UPDATE vaccinations SET notified = 1 WHERE id = ?");
            $update->bind_param("i", $vaccineId);
            $update->execute();
            $update->close();

            $notificationsSent[] = $vaccineId;
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Notifications sent',
        'notified_vaccine_ids' => $notificationsSent
    ]);
} else {
    echo json_encode([
        'status' => 'empty',
        'message' => 'No upcoming vaccinations needing notification'
    ]);
}

$conn->close();


// ========== إرسال الإشعار ==========

function sendFCMNotification($token, $title, $body) {
    $serviceAccount = json_decode(file_get_contents("firebase_credentials.json"), true);

    $projectId   = $serviceAccount['project_id'];
    $clientEmail = $serviceAccount['client_email'];
    $privateKey  = $serviceAccount['private_key'];

    $now = time();
    $header = base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $claims = base64UrlEncode(json_encode([
        'iss'   => $clientEmail,
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'iat'   => $now,
        'exp'   => $now + 3600,
    ]));
    $data = $header . '.' . $claims;
    openssl_sign($data, $signature, $privateKey, 'sha256WithRSAEncryption');
    $jwt = $data . '.' . base64UrlEncode($signature);

    $tokenResponse = httpPost("https://oauth2.googleapis.com/token", [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt,
    ]);

    $accessToken = $tokenResponse['access_token'] ?? null;
    if (!$accessToken) return false;

    $payload = [
        'message' => [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
        ],
    ];

    $response = httpPost(
        "https://fcm.googleapis.com/v1/projects/$projectId/messages:send",
        $payload,
        $accessToken
    );

    return isset($response['name']);
}

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
