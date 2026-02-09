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

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['child_id']) && !empty($data['vaccine_date'])) {
    $stmt = $pdo->prepare("INSERT INTO vaccinations (child_id, vaccine_date, vaccine_name, notes) 
                           VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([
        $data['child_id'],
        $data['vaccine_date'],
        $data['vaccine_name'],
        $data['notes']
    ]);

    if ($result) {
        echo json_encode(["success" => true, "message" => "Vaccination added"]);
    } else {
        echo json_encode(["success" => false, "message" => "Insert failed"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
}
?>
