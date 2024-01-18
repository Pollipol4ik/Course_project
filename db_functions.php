<?php

function closeChat($mysqli, $userId, $doctorId) {
    try {
        // Проверяем, закрыт ли чат
        if (!isChatOpen($mysqli, $userId, $doctorId)) {
            throw new Exception("Чат уже закрыт.");
        }

        // Обновляем статус чата на 'закрыт' и указываем, кто его закрыл
        $updateSql = "UPDATE user_chats SET status = 'closed', closed_by = ? WHERE user_id = ? AND doctor_id = ?";
        $stmt = $mysqli->prepare($updateSql);
        $closedBy = isset($_SESSION['user_id']) ? 'user' : 'admin';
        $stmt->bind_param("sss", $closedBy, $userId, $doctorId);

        if ($stmt->execute()) {
            return "success";
        } else {
            throw new Exception("Ошибка при закрытии чата.");
        }
    } catch (Exception $e) {
        return "error: " . $e->getMessage();
    }
}

function isChatOpen($mysqli, $userId, $doctorId) {
    try {
        // Не закрываем соединение здесь
        $checkChatSql = "SELECT * FROM user_chats WHERE user_id = ? AND doctor_id = ? AND status = 'open'";
        $stmt = $mysqli->prepare($checkChatSql);
        $stmt->bind_param("ss", $userId, $doctorId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $stmt->close(); // Закрываем здесь после получения результата
            return ($result->num_rows > 0);
        } else {
            throw new Exception("Ошибка при проверке статуса чата.");
        }
    } catch (Exception $e) {
        return false;
    }
}

function updateChatStatus($mysqli, $userId, $doctorId, $status) {
    try {
        // Предположим, что у вас есть соответствующий столбец в таблице user_chats для статуса чата
        $updateSql = "UPDATE user_chats SET status = ? WHERE user_id = ? AND doctor_id = ?";
        $stmt = $mysqli->prepare($updateSql);
        $stmt->bind_param("sss", $status, $userId, $doctorId);

        if ($stmt->execute()) {
            return "success";
        } else {
            throw new Exception("Ошибка при обновлении статуса чата.");
        }
    } catch (Exception $e) {
        return "error: " . $e->getMessage();
    }
}

function getChatStatus($mysqli, $doctorId, $userId) {
    try {
        $checkChatSql = "SELECT status FROM user_chats WHERE doctor_id = ? AND user_id = ?";
        $stmt = $mysqli->prepare($checkChatSql);
        $stmt->bind_param("ii", $doctorId, $userId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['status'];
            } else {
                return 'closed'; // Возвращайте 'closed', если чата нет
            }
        } else {
            throw new Exception("Ошибка при получении статуса чата.");
        }
    } catch (Exception $e) {
        return "error: " . $e->getMessage();
    }
}

?>
