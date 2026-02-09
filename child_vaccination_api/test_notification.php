<?php
$token = 'edoLDZn0S36zzv1t5_qGfN:APA91bG_80uT8F4EPwOZ6ROQs4IJdwq2I9uMQD3Jjgd5k_Ya7XvkLiUXKbDBNvDlL0Q8eadxoqR0Lc6UQzhJZqhoFWcwQ7XI97OyQK_Ds1_xk-GOqtiiqiA';
$title = 'تجربة إشعار';
$body = 'هذا إشعار اختبار من FCM';

sendFCMNotification($token, $title, $body);

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

    // Get access token
    $tokenResponse = httpPost("https://oauth2.googleapis.com/token", [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt,
    ]);

    $accessToken = $tokenResponse['access_token'] ?? null;
    if (!$accessToken) {
        echo "❌ Failed to obtain access token.\n";
        return false;
    }

    // Prepare payload
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

    echo "✅ FCM Response:\n";
    print_r($response);
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
    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
    }
    curl_close($ch);
    return json_decode($response, true);
}

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
?>
