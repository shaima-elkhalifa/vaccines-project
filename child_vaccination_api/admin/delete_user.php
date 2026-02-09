<?php
header("Access-Control-Allow-Origin: http://localhost:5661");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
include '../db_connection.php';
$id = $_GET['id'];
$conn->query("DELETE FROM users WHERE id = $id"); 
$conn->query("DELETE FROM user_tokens WHERE user_id = $id"); 
header("Location: index.php");
?>
