<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VetHelp</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="styles/header_style.css" rel="stylesheet">
    <style>
        .navbar-light .navbar-nav .nav-link {
            color: #ffffff; 
        }

        .navbar-light .navbar-nav .nav-link:hover {
            color: #87CEFA;
        }
        .user-name {
            color: #FFFF00 !important; /* Цвет текста имени пользователя */
            margin-right: 15px; /* Отступ справа для выравнивания с кнопкой "Выйти" */
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-info">
        <div class="container">
            <a class="navbar-brand text-white" href="main.php">
                <img src="static/images/logo.png" width="50px" alt="VetHelp Logo" class="img-fluid">
                VetHelp
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a href="main_doctor.php" class="nav-link">Главная</a></li>
                    <li class="nav-item"><a href="message_doctor.php" class="nav-link">Сообщения</a></li>
                    <?php
                // Проверяем, установлена ли сессия пользователя (врача)
                if (isset($_SESSION['doctor_name'])) {
                    echo '<li class="nav-item"><p class="nav-link user-name">Здравствуйте,  ' . $_SESSION['doctor_name'] . '!</p></li>';
                    echo '<li class="nav-item"><a href="logout.php" class="nav-link btn btn-outline-light">Выйти</a></li>';
                } else {
                    echo '<li class="nav-item"><a href="enter.php" class="nav-link btn btn-outline-light">Вход</a></li>';
                }
                ?>
                </ul>
            </div>
        </div>
    </nav>

    <script src="https://code.jquery.com/jquery-3.6.2.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            var isNavOpen = false;

            $('.navbar-toggler').on('click', function () {
                if (isNavOpen) {
                    $('.navbar-collapse').collapse('hide');
                } else {
                    $('.navbar-collapse').collapse('show');
                }
                isNavOpen = !isNavOpen;
            });

            $('.navbar-nav a').on('click', function () {
                $('.navbar-collapse').collapse('hide');
                isNavOpen = false;
            });
        });
    </script>

</body>

</html>
