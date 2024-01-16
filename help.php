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

$sqlDoctors = "SELECT * FROM doctors";
$resultDoctors = $conn->query($sqlDoctors);

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    $sqlMessages = "SELECT m.*, d.full_name AS doctor_name FROM messages m
                    JOIN doctors d ON m.doctor_id = d.doctor_id
                    WHERE m.user_id = $userId";
    $resultMessages = $conn->query($sqlMessages);
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
            background-color: #007bff;
            color: #fff;
        }

        .btn-message:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include('header.html'); ?>
    <div class="container mt-5">
        <div class="row">
            <?php
            if ($resultDoctors->num_rows > 0) {
                while ($rowDoctor = $resultDoctors->fetch_assoc()) {
                    ?>
                    <div class="col-md-4">
                        <div class="card">
                            <img src="<?php echo $rowDoctor['photo_url']; ?>" class="card-img-top" alt="Изображение врача">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $rowDoctor['full_name']; ?></h5>
                                <p class="card-text">Телефон: <?php echo $rowDoctor['phone_number']; ?></p>
                                <p class="card-text">Специализации: <?php echo $rowDoctor['specialization']; ?></p>
                                <?php
                                if (isset($_SESSION['user_id'])) {
                                    ?>
                                    <button class="btn btn-message btn-sm" data-doctor-id="<?php echo $rowDoctor['doctor_id']; ?>">Написать сообщение</button>
                                    <?php
                                } else {
                                    echo '<p class="text-danger">Только авторизованные пользователи могут отправлять сообщения.</p>';
                                }
                                ?>
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
            $('.btn-message').click(function () {
                var doctorId = $(this).data('doctor-id');
                loadChat(doctorId);
            });

            $('#messageForm').submit(function (event) {
                event.preventDefault(); // Предотвращаем стандартное поведение формы
                var message = $('#messageInput').val();
                var doctorId = $('#chatModal').data('doctor-id');
                sendMessage(doctorId, message);
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
                        // Обновите чат после успешной отправки сообщения
                        loadChat(doctorId);
                        
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
