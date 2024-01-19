<?php
session_start();
require('db_connection.php');

if (isset($_SESSION['user_id']) && isset($_POST['doctorId'])) {
    $userId = $_SESSION['user_id'];
    $doctorId = $_POST['doctorId'];

    
    $sql = "SELECT m.*, d.full_name AS doctor_name FROM messages m
            JOIN doctors d ON m.doctor_id = d.doctor_id
            WHERE (m.user_id = $userId AND m.doctor_id = $doctorId) OR (m.user_id = $doctorId AND m.doctor_id = $userId)
            ORDER BY m.created_at";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $messageClass = ($row['is_doctor_response'] == 1) ? 'doctor-message' : 'user-message';

            echo '<div class="message ' . $messageClass . '">';
            echo '<p>' . $row['message_text'] . '</p>';
            echo '<p>' . (($row['is_doctor_response'] == 1) ? 'От доктора: ' : 'Отправлено: ') . $row['created_at'] . ' от ' . (($row['is_doctor_response'] == 1) ? 'Доктор ' . $row['doctor_name'] : 'Пользователь') . '</p>';
            echo '</div>';
        }
    } else {
        echo '<p>Нет сообщений.</p>';
    }
} else {
    echo '<p>Неверные параметры запроса.</p>';
}

$mysqli->close();
?>
