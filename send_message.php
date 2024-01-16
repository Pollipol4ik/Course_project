<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "vet_help";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

if (isset($_SESSION['user_id']) && isset($_POST['doctorId']) && isset($_POST['message'])) {
    $userId = $_SESSION['user_id'];
    $doctorId = $_POST['doctorId'];
    $message = $_POST['message'];

    // Добавление сообщения в базу данных
    $sql = "INSERT INTO messages (user_id, doctor_id, message_text, is_doctor_response) VALUES ($userId, $doctorId, '$message', FALSE)";
    $result = $conn->query($sql);

    if ($result) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "invalid parameters";
}

$conn->close();
?>
