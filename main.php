<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "vet_help";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка соединения: " . $conn->connect_error);
}

$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : '';

if (!$sortOrder) {
    $sortOrder = 'desc';
}

$sql = "SELECT clinic_id, clinic_name, address, phone_number, clinic_rating, reviews_count, latitude, longitude FROM veterinary_clinic ORDER BY clinic_rating $sortOrder";
$result = $conn->query($sql);

// Получение всех строк в виде массива
$clinics = [];
while ($row = $result->fetch_assoc()) {
    $clinics[] = $row;
}

// Определение типа пользователя на основе информации о сеансе
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'user';

// Функция для проверки, добавлена ли клиника в избранное
function checkFavorite($conn, $clinic_id, $user_id) {
    $check_sql = "SELECT * FROM user_favorites WHERE user_id = $user_id AND clinic_id = $clinic_id";
    $check_result = $conn->query($check_sql);
    return $check_result->num_rows > 0;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ветеринарные клиники</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/main.css">
    <style>
        .ya_map {
            font-family: arial;
            font-size: 12px;
            color: #454545;
        }

        #map {
            width: 100%;
            height: 490px;
            z-index: 1;
        }
    </style>

    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=82cdccb4-9063-4998-a32d-a21e21da55a7" type="text/javascript"></script>
</head>
<body>

<?php
// Включение соответствующего заголовка в зависимости от типа пользователя
if ($userType === 'doctor') {
    include('header_doctor.html');
} else {
    include('header.html');
}
?>

<div class="container">
    <h1 class="mt-5 mb-4">Круглосуточные ветеринарные клиники</h1>

    <!-- Форма сортировки -->
    <div class="mb-3">
        <form method="get">
            <label for="sort">Сортировать по:</label>
            <select id="sort" name="sort" class="form-control" onchange="this.form.submit()">
                <option value="" <?php if($sortOrder == '') echo 'selected'; ?>>По умолчанию</option>
                <option value="asc" <?php if($sortOrder == 'asc') echo 'selected'; ?>>По возрастанию</option>
                <option value="desc" <?php if($sortOrder == 'desc') echo 'selected'; ?>>По убыванию</option>
            </select>
        </form>
    </div>

    <div class="row">
        <!-- Колонка с картами клиник -->
        <div id="clinicContainer" class="col-md-6">
            <?php
            // Отображение карточек клиник
            foreach ($clinics as $clinic) {
                $isFavorite = isset($_SESSION['user_id']) && $userType !== 'doctor' ? checkFavorite($conn, $clinic['clinic_id'], $_SESSION['user_id']) : false;
                ?>
                <div class="clinic-card">
                    <div class="card mb-4 shadow-sm">
                        <!-- Ссылка на страницу с деталями клиники с параметром clinic_id -->
                        <a href="clinic_details.php?clinic_id=<?php echo $clinic['clinic_id']; ?>" class="clinic-link">
                            <div class="text-center mt-3">
                                <h2><?php echo $clinic['clinic_name']; ?></h2>
                                <p class="mb-0">Рейтинг: <?php echo $clinic['clinic_rating']; ?>★</p>
                            </div>
                        </a>
                        <!-- Отображение иконки для добавления в избранное, только если пользователь не доктор -->
                        <div class="text-right mt-2">
                            <?php
                            if ($userType !== 'doctor') {
                                if (isset($_SESSION['user_id'])) {
                                    $heartIcon = $isFavorite ? '❤️' : '🤍';
                                    $toggleFavoriteUrl = "favourite.php?clinic_id=" . $clinic['clinic_id'];
                                    echo "<a href=\"$toggleFavoriteUrl\">$heartIcon </a>";
                                } else {
                                    echo '<a href="registration.php">❤️ Зарегистрируйтесь, чтобы добавить в избранное</a>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- Контейнер карты -->
        <div class="col-md-6">
            <div id="map"></div>
        </div>
    </div>
</div>

<!-- Скрипты Bootstrap и jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Скрипт Яндекс.Карты -->
<script type="text/javascript">
    ymaps.ready(init);
    var myMap;

    function init() {
        myMap = new ymaps.Map("map", {
            center: [55.7558, 37.6176], // Координаты центра карты
            zoom: 13 // Масштаб карты
        });

        myMap.controls.add(
            new ymaps.control.ZoomControl() // Добавление элемента управления картой
        );

        <?php
        // Добавление кода JavaScript для создания маркеров для каждой клиники
        foreach ($clinics as $clinic) {
            echo "var placemark" . $clinic['clinic_id'] . " = new ymaps.Placemark([" . $clinic['latitude'] . ", " . $clinic['longitude'] . "], {
                balloonContent: '<div class=\"ya_map\"><a href=\"clinic_details.php?clinic_id=" . $clinic['clinic_id'] . "\">" . $clinic['clinic_name'] . "</a></div>'
            }, {
                preset: 'twirl#redDotIcon'
            });";

            echo "placemark" . $clinic['clinic_id'] . ".events.add('click', function () {
                // Переход на страницу с деталями клиники
                window.location.href = 'clinic_details.php?clinic_id=" . $clinic['clinic_id'] . "';
            });";

            echo "myMap.geoObjects.add(placemark" . $clinic['clinic_id'] . ");";
        }
        ?>
    }
</script>

<?php include('footer.html'); ?>
</body>
</html>
