<?php
$host = "localhost";
$db_name = "vaccination_db"; // Your database name
$username = "root";           // Database username
$password = "";               // Database password

$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
