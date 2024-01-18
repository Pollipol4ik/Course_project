<?php
session_start();
require('db_connection.php');

if (isset($_SESSION['doctor_id'])) {
    $doctorId = $_SESSION['doctor_id'];

    // Добавлено условие для фильтрации по статусу чата
    $statusCondition = '';
    $filterStatus = 'all';

    if (isset($_GET['status']) && in_array($_GET['status'], ['open', 'closed'])) {
        $statusCondition = ' AND uc.status = ?';
        $filterStatus = $_GET['status'];
    }

    $sqlUsers = "SELECT DISTINCT uc.user_id, u.username AS user_name, MAX(m.message_id) AS message_id, uc.status,
    COUNT(m.message_id) AS message_count
    FROM user_chats uc
    LEFT JOIN messages m ON uc.user_id = m.user_id AND uc.doctor_id = m.doctor_id
    JOIN users u ON uc.user_id = u.id
    WHERE m.doctor_id = ?" . $statusCondition . "
    GROUP BY uc.user_id, uc.status
    ORDER BY FIELD(uc.status, 'open', 'closed')";

    $stmtUsers = $mysqli->prepare($sqlUsers);

    // Передача параметра статуса, если он установлен
    if (!empty($statusCondition)) {
        $stmtUsers->bind_param("is", $doctorId, $filterStatus);
    } else {
        $stmtUsers->bind_param("i", $doctorId);
    }

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
    <style>
        .message-container {
            overflow-y: auto;
            max-height: 300px;
            padding: 10px;
        }

        .message, .user-message, .doctor-message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            overflow: hidden;
        }

        .user-message, .doctor-message {
            max-width: 70%;
        }

        .user-message {
            background-color: rgba(0, 0, 255, 0.3);
            float: left;
            clear: both;
        }

        .doctor-message {
            background-color: rgba(0, 255, 0, 0.3);
            float: right;
            clear: both;
        }

        .closed-chat {
            background-color: #ccc; /* Цвет для закрытых чатов */
        }
    </style>
</head>
<body>
    <?php include('header_doctor.php'); ?>
    
    <div class="container mt-5">
        
        <?php if (isset($resultUsers) && $resultUsers->num_rows > 0): ?>
            <div class="row">
                <div class="col-12">
                    <h2>Сообщения от пользователей</h2>
                    <p>
                        Добро пожаловать на страницу "Сообщения от пользователей". Здесь вы можете просматривать сообщения, полученные от пользователей, 
                        и взаимодействовать с ними. Каждый пользователь представлен в виде карточки, содержащей информацию о последнем сообщении от этого пользователя.
                        Вы можете открывать чаты с пользователями, просматривать историю сообщений и отправлять ответы.
                    </p>
                </div>
            </div>
            <div class="row">
                <?php while ($rowUser = $resultUsers->fetch_assoc()): ?>
                    <div class="col-md-12 mb-4 <?= ($rowUser['status'] == 'closed') ? 'closed-chat' : ''; ?>">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Пользователь: <?= $rowUser['user_name']; ?></h5>
                                <p class="card-text">
                                    Статус чата: <?= ucfirst(($rowUser['status'] == 'closed') ? 'закрыт' : 'открыт'); ?>
                                    <?php if ($rowUser['message_count'] > 0): ?>
                                        | Сообщений: <?= $rowUser['message_count']; ?>
                                    <?php else: ?>
                                        | Еще нет сообщений
                                    <?php endif; ?>
                                </p>
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
                <div class="modal-body">
                    <div class="message-container" id="chatMessages">
                        <!-- Сюда будут загружаться сообщения из чата -->
                    </div>
                </div>
                <div class="modal-footer">
                    <form id="messageForm">
                        <textarea id="messageInput" rows="4" cols="60" class="form-control" placeholder="Введите ваше сообщение..."></textarea>
                        <button type="submit" class="btn btn-info mt-2">Отправить</button>
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
                var chatStatus = $(this).closest('.card').hasClass('closed-chat') ? 'closed' : 'open';
                
                loadChat(userId, messageId, chatStatus);
                autoUpdateChat(userId, messageId, chatStatus);
                $('#chatModal').modal('show');
            });

            $('#messageForm').submit(function (event) {
                event.preventDefault();
                var message = $('#messageInput').val();
                var userId = $('#chatModal').data('user-id');
                var messageId = $('#chatModal').data('message-id');
                sendMessage(userId, messageId, message);
            });

            // Очистка чата при закрытии модального окна
            $('#chatModal').on('hidden.bs.modal', function () {
                $('#chatMessages').empty();
            });
        });

        function loadChat(userId, messageId, chatStatus) {
            $('#chatModal').data('user-id', userId);
            $('#chatModal').data('message-id', messageId);
            $('#chatModal').data('chat-status', chatStatus);
            $.ajax({
                url: 'chat.php',
                method: 'POST',
                data: { user_id: userId, message_id: messageId, chat_status: chatStatus },
                success: function (response) {
                    $('#chatMessages').html(response);
                },
                error: function () {
                    alert('Ошибка загрузки чата.');
                }
            });
        }

        function autoUpdateChat(userId, messageId, chatStatus) {
            setInterval(function () {
                loadChat(userId, messageId, chatStatus);
            }, 5000); 
        }

        function sendMessage(userId, messageId, message) {
            $.ajax({
                url: 'send_reply.php',
                method: 'POST',
                data: { user_id: userId, message_id: messageId, reply: message },
                success: function (response) {
                    if (response === "success") {
                        loadChat(userId, messageId);
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

$mysqli->close();
?>
