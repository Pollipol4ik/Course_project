<?php
session_start();
require('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    $username = mysqli_real_escape_string($mysqli, $input_username);

    // Проверка входа для пользователя
    $sqlUser = "SELECT id, username, password FROM users WHERE username='$username'";
    $resultUser = $mysqli->query($sqlUser);

    if ($resultUser->num_rows == 1) {
        $rowUser = $resultUser->fetch_assoc();
        $stored_password = $rowUser['password'];

        if (password_verify($input_password, $stored_password)) {
            $_SESSION['user_id'] = $rowUser['id'];
            $_SESSION['user_name'] = $rowUser['username'];
            $_SESSION['user_type'] = 'user'; // Установим тип пользователя
            header("Location: main.php"); 
            exit();
        } else {
            echo "Неверный пароль";
        }
    } else {
        // Проверка входа для доктора
        $sqlDoctor = "SELECT doctor_id, full_name, password FROM doctors WHERE username='$username'";
        $resultDoctor = $mysqli->query($sqlDoctor);

        if ($resultDoctor->num_rows == 1) {
            $rowDoctor = $resultDoctor->fetch_assoc();
            $stored_password_doctor = $rowDoctor['password'];

            if ($input_password === $stored_password_doctor) {
                $_SESSION['doctor_id'] = $rowDoctor['doctor_id'];
                $_SESSION['doctor_name'] = $rowDoctor['full_name'];
                $_SESSION['user_type'] = 'doctor'; // Установим тип пользователя
                header("Location: main_doctor.php");
                exit();
            } else {
                echo "Неверный пароль для доктора";
            }
        } else {
            echo "Пользователь или доктор не найден";
        }
    }
}

$mysqli->close();
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include('header_user.php'); ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Вход</h2>
                    </div>
                    <div class="card-body">
                        <form id="loginForm" method="post" action="">
                            <div class="form-group">
                                <label for="username">Имя пользователя:</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Пароль:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-info btn-block">Войти</button>
                        </form>
                        <p class="mt-3 text-center">Нет аккаунта? <a href="registration.php" class="register-link">Зарегистрируйтесь</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.2.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <?php include('footer.html');?>
</body>
</html>
