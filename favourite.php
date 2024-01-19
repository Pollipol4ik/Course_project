<?php
session_start();
require('db_connection.php');

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: main.php"); 
    exit();
}

$database = Database::getInstance();
$mysqli = $database->getConnection();

if (isset($_GET['clinic_id'])) {
    $clinic_id = mysqli_real_escape_string($mysqli, $_GET['clinic_id']);
    $user_id = $_SESSION['user_id'];

    // Проверяем, не находится ли клиника уже в избранном
    $check_sql = "SELECT * FROM user_favorites WHERE user_id = $user_id AND clinic_id = $clinic_id";
    $check_result = $mysqli->query($check_sql);

    if ($check_result->num_rows == 0) {
        // Добавляем клинику в избранное
        $insert_sql = "INSERT INTO user_favorites (user_id, clinic_id) VALUES ($user_id, $clinic_id)";
        $mysqli->query($insert_sql);
    } else {
        // Удаляем клинику из избранного
        $delete_sql = "DELETE FROM user_favorites WHERE user_id = $user_id AND clinic_id = $clinic_id";
        $mysqli->query($delete_sql);
    }
}

// Получаем избранные клиники для текущего пользователя
$user_id = $_SESSION['user_id'];
$sql = "SELECT vc.clinic_id, vc.clinic_name, vc.address, vc.phone_number, vc.reviews_count, vc.clinic_rating
        FROM veterinary_clinic vc
        INNER JOIN user_favorites uf ON vc.clinic_id = uf.clinic_id
        WHERE uf.user_id = $user_id";
$result = $mysqli->query($sql);

$mysqli->close();
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Избранные клиники</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/main.css">
        <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=82cdccb4-9063-4998-a32d-a21e21da55a7" type="text/javascript"></script>
        <style>
        clinic-card {
            height: 300px; 
            width: 100%; 
        }

        .clinic-card .card {
            height: 100%;
        }

        .clinic-card .card-body {
            overflow: hidden;
        }
    </style>
</head>
<body>

<?php

   include('header_user.php');
?>

<div class="container">
    <h1 class="mt-5 mb-4">Избранные клиники</h1>
    <p>
        Добро пожаловать на страницу "Избранные клиники". Здесь вы можете просмотреть список клиник, которые вы добавили в избранное.
        Вы можете легко перейти к деталям каждой клиники, увидеть ее рейтинг, адрес и другую важную информацию. 
        Воспользуйтесь удобством нашего сервиса, чтобы быстро находить и управлять вашим списком избранных клиник.
    </p>

    <!-- Карточки избранных клиник -->
    <div id="favoriteContainer" class="row">
        <?php
        while ($row = $result->fetch_assoc()) {
        ?>
            <div class="col-md-4 clinic-card">
                <div class="card mb-4 shadow-sm">
                    <a href="clinic_details.php?clinic_id=<?php echo $row['clinic_id']; ?>">
                        <h2 class="text-center mt-3"><?php echo $row['clinic_name']; ?></h2>
                    </a>
                    <p class="text-center">Рейтинг: <?php echo $row['clinic_rating']; ?>★</p>
                    <p class="text-center">Адрес: <?php echo $row['address']; ?></p>
                    <div class="text-center mt-2">
                        <a href="favourite.php?clinic_id=<?php echo $row['clinic_id']; ?>">
                            <?php echo '❤️'; ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.2.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<?php include('footer.html'); ?>
</body>
</html>
