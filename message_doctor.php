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

if (isset($_SESSION['doctor_id'])) {
    $doctorId = $_SESSION['doctor_id'];

    $sqlUsers = "SELECT DISTINCT m.user_id, u.username AS user_name, MAX(m.message_id) AS message_id
                 FROM messages m
                 JOIN users u ON m.user_id = u.id
                 WHERE m.doctor_id = ?
                 GROUP BY m.user_id";
    $stmtUsers = $conn->prepare($sqlUsers);
    $stmtUsers->bind_param("i", $doctorId);
    $stmtUsers->execute();
    $resultUsers = $stmtUsers->get_result();
    $stmtUsers->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сообщения от пользователей</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('header_doctor.html'); ?>
    
    <div class="container mt-5">
        <?php if (isset($resultUsers) && $resultUsers->num_rows > 0): ?>
            <div class="row">
                <div class="col-12">
                    <h2>Сообщения от пользователей</h2>
                </div>
            </div>
            <div class="row">
                <?php while ($rowUser = $resultUsers->fetch_assoc()): ?>
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Пользователь: <?= $rowUser['user_name']; ?></h5>
                                
                                <!-- Интеграция чата в сообщения -->
                                <button class="btn btn-primary btn-open-chat" data-user-id="<?= $rowUser['user_id']; ?>" data-message-id="<?= $rowUser['message_id']; ?>">Открыть чат</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Нет сообщений от пользователей.</p>
        <?php endif; ?>
    </div>

    <!-- Модальное окно для отображения чата -->
    <div class="modal" id="chatModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Чат с пользователем</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="chatMessages">
                    <!-- Сюда будут загружаться сообщения из чата -->
                </div>
                <div class="modal-footer">
                    <form id="messageForm">
                        <textarea id="messageInput" rows="4" cols="50" placeholder="Введите ваше сообщение..."></textarea>
                        <button type="submit" class="btn btn-primary">Отправить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.html'); ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.btn-open-chat').click(function () {
                var userId = $(this).data('user-id');
                var messageId = $(this).data('message-id');
                loadChat(userId, messageId);
                $('#chatModal').modal('show');
            });

            $('#messageForm').submit(function (event) {
                event.preventDefault(); // Предотвращаем стандартное поведение формы
                var message = $('#messageInput').val();
                var userId = $('#chatModal').data('user-id');
                var messageId = $('#chatModal').data('message-id');
                sendMessage(userId, messageId, message);
            });
        });

        function loadChat(userId, messageId) {
            $('#chatModal').data('user-id', userId);
            $('#chatModal').data('message-id', messageId);
            $('#chatMessages').empty();
            $.ajax({
                url: 'chat.php',
                method: 'POST',
                data: { user_id: userId, message_id: messageId },
                success: function (response) {
                    $('#chatMessages').html(response);
                },
                error: function () {
                    alert('Ошибка загрузки чата.');
                }
            });
        }

        function sendMessage(userId, messageId, message) {
            $.ajax({
                url: 'send_reply.php',
                method: 'POST',
                data: { user_id: userId, message_id: messageId, reply: message },
                success: function (response) {
                    if (response === "success") {
                        // Обновите чат после успешной отправки сообщения
                        loadChat(userId, messageId);
                        
                        // Очистите поле ввода сообщения после успешной отправки
                        $('#messageInput').val('');
                    } else {
                        alert('Ошибка отправки сообщения.');
                    }
                },
                error: function () {
                    alert('Ошибка отправки сообщения.');
                }
            });
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
