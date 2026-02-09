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

include 'db_connection.php'; // الاتصال بقاعدة البيانات

$parent_id = $_POST['parent_id'];
$doctor_id = $_POST['doctor_id'];

$response = [];

$parent_query = mysqli_query($conn, "SELECT name FROM users WHERE id = '$parent_id'");
$doctor_query = mysqli_query($conn, "SELECT name FROM users WHERE id = '$doctor_id'");

if ($parent_row = mysqli_fetch_assoc($parent_query)) {
    $response['parent_name'] = $parent_row['name'];
}

if ($doctor_row = mysqli_fetch_assoc($doctor_query)) {
    $response['doctor_name'] = $doctor_row['name'];
}

$response['status'] = 'success';

echo json_encode($response);
?>
