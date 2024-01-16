<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "vet_help";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка соединения: " . $conn->connect_error);
}

$clinic_id = isset($_GET['clinic_id']) ? $_GET['clinic_id'] : null;

$sqlClinic = "SELECT clinic_name, address, phone_number, clinic_rating, reviews_count, latitude, longitude FROM veterinary_clinic WHERE clinic_id = ?";
$stmtClinic = $conn->prepare($sqlClinic);
$stmtClinic->bind_param("i", $clinic_id);
$stmtClinic->execute();
$resultClinic = $stmtClinic->get_result();

if ($resultClinic && $resultClinic->num_rows > 0) {
    $clinicDetails = $resultClinic->fetch_assoc();
} else {
    header("Location: main.php");
    exit();
}

$sqlComments = "SELECT user_name, comment_text, created_at, rating FROM comments WHERE clinic_id = ?";
$stmtComments = $conn->prepare($sqlComments);
$stmtComments->bind_param("i", $clinic_id);
$stmtComments->execute();
$resultComments = $stmtComments->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали клиники</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/main.css">
    <script src="https://code.jquery.com/jquery-3.6.2.min.js"></script>
    <style>
    .clinic-details-card {
        padding: 20px; 
    }

    #map {
        width: 100%;
        height: 300px;
        margin-bottom: 20px;
    }

    .rating {
        text-align: center;
    }

    .rating > input {
        display: none;
    }

    .rating > label {
        display: inline-block;
        padding: 0 5px; /* Чуть больше отступов для звезд */
        font-size: 24px;
        line-height: 1.2;
        cursor: pointer;
    }

    .rating > label:before {
        content: '★';
    }

    .rating > label.star {
        display: inline-block;
        color: #ddd; /* Цвет незакрашенной звезды */
    }

    .rating > input:checked ~ label.star {
        color: #ffcc00; /* Цвет закрашенной звезды */
    }
</style>

</head>
<body>
<?php include('header.html'); ?>
    <div class="container">
        <h1 class="mt-5 mb-4">Детали клиники</h1>

        <!-- Карта с одной отметкой -->
        <div id="map"></div>

        <div class="card mb-4 shadow-sm clinic-details-card">
            <h2 class="text-center mt-3"><?php echo $clinicDetails['clinic_name']; ?></h2>
            <p class="text-center">Рейтинг: <span class="rating"><?php echo $clinicDetails['clinic_rating']; ?></span> (<?php echo $clinicDetails['reviews_count']; ?> отзывов)</p>
            <p class="text-center">Адрес: <?php echo $clinicDetails['address']; ?></p>
            <p class="text-center">Телефон: <?php echo $clinicDetails['phone_number']; ?></p>
        </div>

        <div class="card mt-4 mb-4 shadow-sm">
            <h3 class="text-center mt-3">Комментарии</h3>

            <?php
            if ($resultComments && $resultComments->num_rows > 0) {
                foreach ($resultComments as $comment) {
                    echo '<div class="card-body">';
                    echo '<div class="card">';
                    echo '<div class="card-header">' . $comment['user_name'] . ' - ' . $comment['created_at'] . ' - Рейтинг: ' . $comment['rating'] . ' ★</div>';
                    echo '<div class="card-body">';
                    echo '<p class="card-text">' . $comment['comment_text'] . '</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>

            <?php
            $userLoggedIn = isset($_SESSION['user_id']);

            if ($userLoggedIn) {
                $userName = $_SESSION['user_name'];

                echo '<form action="post_comment.php?clinic_id=' . $clinic_id . '" method="post">';
                echo '<label for="rating">Рейтинг:</label>';
                echo '<div class="rating">';
                for ($i = 5; $i >= 1; $i--) {
                    echo '<input type="radio" id="star' . $i . '" name="rating" value="' . $i . '" /><label for="star' . $i . '" class="star"></label>';
                }
                echo '</div>';
                echo '<label for="comment">Написать комментарий:</label>';
                echo '<textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>';
                echo '<input type="hidden" id="rating_value" name="rating_value" value="">
                ';
                echo '<button type="submit" class="btn btn-primary mt-3">Оставить комментарий</button>';
                echo '</form>';

                echo '</div>';
            } else {
                echo '<div class="card-body">';
                echo '<p class="text-center">Чтобы оставить комментарий, вам нужно <a href="enter.php">войти</a>.</p>';
                echo '</div>';
            }
            ?>
        </div>

        <a href="main.php">Вернуться к списку клиник</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Скрипт Яндекс Карты -->
    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=82cdccb4-9063-4998-a32d-a21e21da55a7" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.rating input').on('change', function () {
                var ratingValue = $(this).val();
                $('#rating_value').val(ratingValue);
            });

            ymaps.ready(init);
            var myMap;

            function init() {
                myMap = new ymaps.Map("map", {
                    center: [<?php echo $clinicDetails['latitude']; ?>, <?php echo $clinicDetails['longitude']; ?>],
                    zoom: 17
                });

                myMap.controls.add(new ymaps.control.ZoomControl());

                var placemark = new ymaps.Placemark([<?php echo $clinicDetails['latitude']; ?>, <?php echo $clinicDetails['longitude']; ?>], {
                    balloonContent: '<div><?php echo $clinicDetails['clinic_name']; ?></div>'
                }, {
                    preset: 'twirl#redDotIcon'
                });

                myMap.geoObjects.add(placemark);
            }
        });
    </script>

    <?php include('footer.html');?>
</body>
</html>
