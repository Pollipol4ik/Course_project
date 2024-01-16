<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION['doctor_id'])) {
    $doctorId = $_SESSION['doctor_id'];
    $messageId = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0; // Добавлено получение user_id

    // Database connection parameters (replace with your values)
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "vet_help";

    try {
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            throw new Exception("Ошибка подключения к базе данных: " . $conn->connect_error);
        }

        $sqlChat = "SELECT * FROM messages 
                    WHERE (user_id = ? AND doctor_id = ?) 
                    OR (user_id = ? AND doctor_id = ?)
                    ORDER BY created_at ASC";

        $stmtChat = $conn->prepare($sqlChat);
        $stmtChat->bind_param("iiii", $userId, $doctorId, $doctorId, $userId);
        $stmtChat->execute();
        $resultChat = $stmtChat->get_result();

        while ($rowMessage = $resultChat->fetch_assoc()) {
            if (isset($rowMessage['created_at'], $rowMessage['message_text'])) {
                echo '<div class="message">';
                echo '<p>' . htmlspecialchars($rowMessage['created_at']) . ': ' . htmlspecialchars($rowMessage['message_text']) . '</p>';
                echo '</div>';
            }
        }

        $stmtChat->close();
        $conn->close();
    } catch (Exception $e) {
        echo '<p>Ошибка: ' . $e->getMessage() . '</p>';
    }
} else {
    echo '<p>Invalid request.</p>';
}
?>
