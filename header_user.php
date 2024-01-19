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
            color: #FFFF00 !important; 
            margin-right: 15px; 
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
                    <li class="nav-item"><a href="main.php" class="nav-link">Главная</a></li>
                    <li class="nav-item"><a href="about.php" class="nav-link">О нас</a></li>
                    <li class="nav-item"><a href="help.php" class="nav-link">Обратиться за помощью</a></li>
                    <li class="nav-item"><a href="favourite.php" class="nav-link"><span>&#10084;</span></a></li>
                    <?php
                    
                    if (isset($_SESSION['user_name'])) {
                        // Выводим имя пользователя перед кнопкой "Выйти"
                        echo '<li class="nav-item"><p class="nav-link user-name">Привет, ' . $_SESSION['user_name'] . '!</p></li>';
                        echo '<li class="nav-item"><a href="logout.php" class="nav-link btn btn-outline-light">Выйти</a></li>';
                    } else {
                        // Если сессия не установлена, выводим кнопку входа
                        echo '<li class="nav-item"><a href="enter.php" class="nav-link btn btn-outline-light">Войти</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
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
