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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['child_id']) || empty($_POST['child_id'])) {
        echo json_encode(['error' => 'Child ID is required.', 'status' => 'error']);
        exit;
    }

    $child_id = $_POST['child_id'];

    // Query to POST vaccines for the child
    $query = "SELECT v.id, v.vaccine_name, v.vaccine_date, 
                     c.name as child_name, c.birth_date, c.user_id, c.doctor_id
              FROM vaccinations v 
              JOIN children c ON c.id = v.child_id 
              WHERE v.child_id = ? 
              ORDER BY v.vaccine_date ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $child_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $vaccines = [];
    while ($row = $result->fetch_assoc()) {
        $vaccines[] = $row;
    }

    if (empty($vaccines)) {
        echo json_encode(['message' => 'No vaccines found for this child', 'status' => 'success', 'vaccines' => []]);
    } else {
        echo json_encode(['vaccines' => $vaccines, 'status' => 'success']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request method.', 'status' => 'error']);
}