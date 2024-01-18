<?php
session_start();
require('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION['doctor_id'])) {
    $doctorId = $_SESSION['doctor_id'];
    $messageId = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $chatStatus = isset($_POST['chat_status']) ? $_POST['chat_status'] : 'open';

    try {
        $sqlChat = "SELECT * FROM messages 
                    WHERE (user_id = ? AND doctor_id = ?) 
                    OR (user_id = ? AND doctor_id = ?)
                    ORDER BY created_at ASC";

        $stmtChat = $mysqli->prepare($sqlChat);
        $stmtChat->bind_param("iiii", $userId, $doctorId, $doctorId, $userId);
        $stmtChat->execute();
        $resultChat = $stmtChat->get_result();

        while ($rowMessage = $resultChat->fetch_assoc()) {
            if (isset($rowMessage['created_at'], $rowMessage['message_text'], $rowMessage['is_doctor_response'])) {
                $messageClass = ($rowMessage['is_doctor_response'] == 1) ? 'doctor-message' : 'user-message';
        
                echo '<div class="message ' . $messageClass . '">';
                echo '<p>' . htmlspecialchars($rowMessage['message_text']) . '</p>';
                echo '<p>' . (($rowMessage['is_doctor_response'] == 1) ? 'От доктора: ' : 'Отправлено: ') . htmlspecialchars($rowMessage['created_at']) . '</p>';
                echo '</div>';
            }
        }
        
        $stmtChat->close();
    } catch (Exception $e) {
        echo '<p>Ошибка: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} else {
    echo '<p>Invalid request.</p>';
}
?>
