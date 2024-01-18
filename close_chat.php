<?php
session_start();
require('db_connection.php');
include('db_functions.php');

// Используйте метод getConnection() для получения соединения
$mysqli = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctorId = $_POST['doctor_id'];
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    closeChat($mysqli, $userId, $doctorId);

    echo "success";
} else {
    echo "error";
}

$mysqli->close();
?>
