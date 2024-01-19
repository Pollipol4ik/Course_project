<?php
session_start();
require('db_connection.php');
include('db_functions.php');

$database = Database::getInstance();
$mysqli = $database->getConnection();

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


$sqlDoctors = "SELECT * FROM doctors";
$resultDoctors = $mysqli->query($sqlDoctors);


if ($userId) {
    $sqlMessages = "SELECT uc.*, d.full_name AS doctor_name 
                    FROM user_chats uc
                    JOIN doctors d ON uc.doctor_id = d.doctor_id
                    WHERE uc.user_id = ?";
    $stmt = $mysqli->prepare($sqlMessages);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $resultMessages = $stmt->get_result();
    $stmt->close();
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Врачи и Сообщения</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
            margin: 20px;
        }

        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-img-top {
            object-fit: contain;
            max-height: 200px;
            width: 100%;
            margin-top: 10px;
        }

        .card-body {
            text-align: center;
        }

        .btn-message {
            background-color: #fff;
            color: #fff;
            border: 3px solid #63c1ff !important;
            border-radius: 30px;
            padding: 8px 16px;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
        }

        .btn-message:hover {
            background-color: #0056b3;
            color: #fff;
            border-color: #63c1ff;
        }

        .message-container {
            overflow-y: scroll;
            max-height: 300px;
            padding: 10px;
        }

        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            overflow: hidden;
        }

        .user-message,
        .doctor-message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            max-width: 70%;
        }

        .user-message {
            background-color: rgba(0, 0, 255, 0.3);
            float: right;
            clear: both;
        }

        .doctor-message {
            background-color: rgba(0, 255, 0, 0.3);
            float: left;
            clear: both;
        }

        .closed-chat {
            font-weight: bold;
            color: red;
        }
        #chatModal .modal-dialog {
        max-width: 800px; 
    }

    #chatModal .modal-body {
        max-height: 700px; 
        overflow-y: auto; 
    }
    </style>
</head>
<body>
    <?php include('header_user.php'); ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h2>Врачи и Сообщения</h2>
                <p>
                    Добро пожаловать на страницу "Врачи и Сообщения". Здесь вы можете ознакомиться с профилями врачей
                    и начать общение с ними. Выберите врача, и если вы авторизованы,
                    вы сможете отправить врачу сообщение прямо с этой страницы.
                </p>
                <p>
                    Чтобы воспользоваться функцией отправки сообщений, пожалуйста, авторизуйтесь на сайте.
                </p>
            </div>

            <?php
            if ($resultDoctors->num_rows > 0) {
                while ($rowDoctor = $resultDoctors->fetch_assoc()) {
                    $doctorId = $rowDoctor['doctor_id'];
                    $chatStatus = isChatOpen($mysqli, $userId, $doctorId) ? 'Открыт' : 'Закрыт';
                    $buttonClass = 'btn btn-message btn-sm';
                    $chatClosedBy = '';

                    if ($chatStatus === 'Закрыт') {
                        $getClosedBySql = "SELECT closed_by FROM user_chats WHERE user_id = ? AND doctor_id = ? AND status = 'closed'";
                        $stmtClosedBy = $mysqli->prepare($getClosedBySql);

                        $stmtClosedBy->bind_param("ss", $userId, $doctorId);
                        $stmtClosedBy->execute();
                        $closedByResult = $stmtClosedBy->get_result();

                        if ($closedByResult->num_rows > 0) {
                            $closedByRow = $closedByResult->fetch_assoc();
                            $chatClosedBy = 'Чат закрыт ' ;
                        }
                        $stmtClosedBy->close();
                    }
                    ?>
                    <div class="col-md-4">
                        <div class="card">
                            <img src="<?php echo $rowDoctor['photo_url']; ?>" class="card-img-top" alt="Изображение врача">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $rowDoctor['full_name']; ?></h5>
                                <p class="card-text">Телефон: <?php echo $rowDoctor['phone_number']; ?></p>
                                <p class="card-text">Специализации: <?php echo $rowDoctor['specialization']; ?></p>
                                <p class="card-text">Статус чата: <?php echo $chatStatus; ?></p>
                                <?php
                                if (isset($_SESSION['user_id'])) {
                                    if ($chatStatus === 'Открыт') {
                                        ?>
                                        <button class="<?php echo $buttonClass; ?>" data-doctor-id="<?php echo $doctorId; ?>">Написать сообщение</button>
                                        <?php
                                    } else {
                                        ?>
                                        <button class="<?php echo $buttonClass; ?>" data-doctor-id="<?php echo $doctorId; ?>" onclick="resumeChat(<?php echo $doctorId; ?>)">Возобновить чат</button>
                                        <?php
                                    }
                                } else {
                                    echo '<p class="text-danger">Только авторизованные пользователи могут отправлять сообщения.</p>';
                                }
                                ?>
                                <p class="closed-chat"><?php echo $chatClosedBy; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "Нет данных о врачах.";
            }
            ?>
        </div>
    </div>

    <div class="modal" id="chatModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Чат с врачом</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="message-container" id="chatMessages">
                        <!-- Сюда будут загружаться сообщения из чата -->
                    </div>
                </div>
                <div class="modal-footer">
                    <form id="messageForm">
                        <textarea id="messageInput" rows="4" cols="110" class="form-control" placeholder="Введите ваше сообщение..."></textarea>
                        <button type="submit" class="btn btn-info mt-2">Отправить</button>
                        <button type="button" class="btn btn-danger mt-2" id="closeChatBtn">Закрыть чат</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.html'); ?>

    <script src="https://code.jquery.com/jquery-3.6.2.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.btn-message').click(function () {
                var doctorId = $(this).data('doctor-id');
                loadChat(doctorId);
            });

            $('#messageForm').submit(function (event) {
                event.preventDefault(); 
                var message = $('#messageInput').val();
                var doctorId = $('#chatModal').data('doctor-id');
                sendMessage(doctorId, message);
            });

            $('#closeChatBtn').click(function () {
                var doctorId = $('#chatModal').data('doctor-id');
                closeChat(doctorId);
            });
        });

        function loadChat(doctorId) {
            $('#chatModal').data('doctor-id', doctorId);
            $('#chatMessages').empty();
            $.ajax({
                url: 'load_chat.php',
                method: 'POST',
                data: { doctorId: doctorId },
                success: function (response) {
                    $('#chatMessages').html(response);
                    $('#chatModal').modal('show');
                },
                error: function () {
                    alert('Ошибка загрузки чата.');
                }
            });
        }

        function sendMessage(doctorId, message) {
            $.ajax({
                url: 'send_message.php',
                method: 'POST',
                data: { doctorId: doctorId, message: message },
                success: function (response) {
                    if (response === "success") {
                       
                        loadChat(doctorId);

                        
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

        function closeChat(doctorId) {
            $.ajax({
                url: 'close_chat.php',
                method: 'POST',
                data: { doctor_id: doctorId },
                success: function (response) {
                    if (response === "success") {
                        alert('Чат успешно закрыт.');
                        $('#chatModal').modal('hide');
                    } else {
                        alert('Ошибка при закрытии чата.');
                    }
                },
                error: function () {
                    alert('Ошибка при закрытии чата.');
                }
            });
        }

        function resumeChat(doctorId) {
            $.ajax({
                url: 'update_chat_status.php',
                method: 'POST',
                data: { doctor_id: doctorId },
                success: function (response) {
                    console.log(response); 
                    if (response === "success") {
                        alert('Чат успешно возобновлен.');
                        loadChat(doctorId);
                    } else {
                        alert('Ошибка при возобновлении чата.');
                    }
                },
                error: function () {
                    alert('Ошибка при возобновлении чата.');
                }
            });
        }
    </script>
</body>
</html>
<?php $mysqli->close();?>

