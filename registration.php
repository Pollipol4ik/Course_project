<?php
    // Подключение к базе данных
    $servername = "localhost";
    $username_db = "root";
    $password_db = "root";
    $dbname = "vet_help";

    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Обработка формы при отправке
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Хеширование пароля

        // SQL запрос для вставки данных в таблицу
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

        if ($conn->query($sql) === TRUE) {
            echo "Регистрация успешна!";
            // Переход на страницу входа
            header("Location: enter.php", true, 303);
            exit();
        } else {
            echo "Ошибка: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Регистрация</title>
</head>
<body>
    <?php include('header.html'); ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Регистрация</h2>
                    </div>
                    <div class="card-body">
                        <form id="registrationForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <!-- Добавленный атрибут "method" и "action" -->
                            <div class="form-group">
                                <label for="username">Имя пользователя:</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Пароль:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Зарегистрироваться</button>
                        </form>
                        <p class="mt-3 text-center">Уже есть аккаунт? <a href="#" class="login-link">Войти</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <?php include('footer.html');?>
    <script>
        document.querySelector('.login-link').addEventListener('click', function() {
            window.location.href = 'enter.php'; 
        });
    </script>
</body>
</html>
