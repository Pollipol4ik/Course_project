<?php
session_start();
require('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION['doctor_id'])) {
    $doctorId = $_SESSION['doctor_id'];
    $messageId = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
    $messageText = isset($_POST['reply']) ? htmlspecialchars($_POST['reply']) : '';

    try {
        $stmt = $mysqli->prepare("SELECT user_id FROM messages WHERE message_id = ?");
        $stmt->bind_param("i", $messageId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userId);
            $stmt->fetch();

            $sqlInsert = "INSERT INTO messages (user_id, doctor_id, message_text, is_doctor_response) 
                          VALUES (?, ?, ?, 1)";
            $stmtInsert = $mysqli->prepare($sqlInsert);
            $stmtInsert->bind_param("iss", $userId, $doctorId, $messageText);
            
            if ($stmtInsert->execute()) {
                echo "success";
            } else {
                echo "Ошибка: " . $stmtInsert->error;
            }

            $stmtInsert->close();
        } else {
            echo "Ошибка: Сообщение не найдено.";
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "Ошибка: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}

$mysqli->close();
?>
