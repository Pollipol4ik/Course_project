<?php
session_start();
require('db_connection.php');

if (isset($_SESSION['user_id']) && isset($_POST['doctorId']) && isset($_POST['message'])) {
    $userId = $_SESSION['user_id'];
    $doctorId = $_POST['doctorId'];
    $message = $_POST['message'];

    $sql = "INSERT INTO messages (user_id, doctor_id, message_text, is_doctor_response) VALUES ($userId, $doctorId, '$message', FALSE)";
    $result = $mysqli->query($sql);

    if ($result) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "invalid parameters";
}

$mysqli->close();
?>
