<?php
session_start();
require('db_connection.php');

$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : '';
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$selectedAdmArea = isset($_GET['admArea']) ? $_GET['admArea'] : '';
$selectedDistrict = isset($_GET['district']) ? $_GET['district'] : '';

if (!$sortOrder) {
    $sortOrder = 'desc';
}

$mysqli = $database->getConnection();

$admAreasQuery = "SELECT DISTINCT AdmArea FROM veterinary_clinic";
$admAreasResult = $mysqli->query($admAreasQuery);
$admAreas = [];
while ($row = $admAreasResult->fetch_assoc()) {
    $admAreas[] = $row['AdmArea'];
}

$districtsQuery = "SELECT DISTINCT District FROM veterinary_clinic";
$districtsResult = $mysqli->query($districtsQuery);
$districts = [];
while ($row = $districtsResult->fetch_assoc()) {
    $districts[] = $row['District'];
}

$sql = "SELECT clinic_id, clinic_name, address, phone_number, clinic_rating, reviews_count, latitude, longitude FROM veterinary_clinic";

// Добавляем условия для фильтрации по округам и районам
if ($searchQuery || $selectedAdmArea || $selectedDistrict) {
    $sql .= " WHERE";

    if ($searchQuery) {
        $sql .= " clinic_name LIKE '%$searchQuery%' AND";
    }

    if ($selectedAdmArea) {
        $sql .= " AdmArea = '$selectedAdmArea' AND";
    }

    if ($selectedDistrict) {
        $sql .= " District = '$selectedDistrict' AND";
    }

    
    $sql = rtrim($sql, ' AND');
}

$sql .= " ORDER BY clinic_rating $sortOrder";
$result = $mysqli->query($sql);

$clinics = [];
while ($row = $result->fetch_assoc()) {
    $clinics[] = $row;
}


$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'user';

function checkFavorite($mysqli, $clinic_id, $user_id) {
    $check_sql = "SELECT * FROM user_favorites WHERE user_id = $user_id AND clinic_id = $clinic_id";
    $check_result = $mysqli->query($check_sql);
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

        #searchResults {
            position: absolute;
            background: white;
            z-index: 2;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            display: none;
        }

        #searchResults a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #ddd;
        }

        #searchResults a:hover {
            background-color: #f9f9f9;
        }
    </style>

    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=d272ae8d-e320-465e-8470-c7fcce88863c" type="text/javascript"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<?php

if ($userType === 'doctor') {
    include('header_doctor.php');
} else {
    include('header_user.php');
}
?>

<div class="container">
    <h1 class="mt-5 mb-4">Ветеринарные клиники в г.Москва</h1>
    <p>
        Добро пожаловать на страницу "Ветеринарные клиники". Здесь вы можете найти информацию о ветеринарных клиниках в городе Москва.
        Выберите врачебное учреждение из списка ниже, чтобы узнать подробности, включая рейтинг, адрес, и отзывы о клинике.
        Если вы зарегистрированы на сайте, вы также можете добавлять клиники в избранное для более удобного доступа.
    </p>

    <!-- Форма поиска -->
    <div class="mb-3 d-flex">
        <form class="flex-grow-1 mr-2" method="get">
            <label for="search">Поиск по названию:</label>
            <div class="input-group">
                <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">Искать</button>
                </div>
            </div>
        </form>
        <div id="searchResults"></div>
    </div>

    <!-- Форма сортировки и фильтрации -->
    <div class="mb-3">
        <form method="get">
            <label for="sort">Сортировать по рейтингу:</label>
            <select id="sort" name="sort" class="form-control" onchange="this.form.submit()">
                <option value="" <?php if($sortOrder == '') echo 'selected'; ?>>По умолчанию</option>
                <option value="asc" <?php if($sortOrder == 'asc') echo 'selected'; ?>>По возрастанию</option>
                <option value="desc" <?php if($sortOrder == 'desc') echo 'selected'; ?>>По убыванию</option>
            </select>

            <!-- Выпадающий список для фильтрации по округам -->
            <label for="admArea" class="ml-3">Фильтровать по округу:</label>
            <select id="admArea" name="admArea" class="form-control" onchange="this.form.submit()">
                <option value="" <?php if (!$selectedAdmArea) echo 'selected'; ?>>Все округа</option>
                <?php foreach ($admAreas as $admAreaOption) : ?>
                    <option value="<?php echo $admAreaOption; ?>" <?php if ($selectedAdmArea == $admAreaOption) echo 'selected'; ?>>
                        <?php echo $admAreaOption; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Выпадающий список для фильтрации по районам -->
            <label for="district" class="ml-3">Фильтровать по району:</label>
            <select id="district" name="district" class="form-control" onchange="this.form.submit()">
                <option value="" <?php if (!$selectedDistrict) echo 'selected'; ?>>Все районы</option>
                <?php foreach ($districts as $districtOption) : ?>
                    <option value="<?php echo $districtOption; ?>" <?php if ($selectedDistrict == $districtOption) echo 'selected'; ?>>
                        <?php echo $districtOption; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="row">
        <!-- Колонка с картами клиник -->
        <div id="clinicContainer" class="col-md-6">
            <?php
            foreach ($clinics as $clinic) {
                $isFavorite = isset($_SESSION['user_id']) && $userType !== 'doctor' ? checkFavorite($mysqli, $clinic['clinic_id'], $_SESSION['user_id']) : false;
                ?>
                <div class="clinic-card">
                    <div class="card mb-4 shadow-sm">
                        <a href="clinic_details.php?clinic_id=<?php echo $clinic['clinic_id']; ?>" class="clinic-link">
                            <div class="text-center mt-3">
                                <h2><?php echo $clinic['clinic_name']; ?></h2>
                                <p class="mb-0">Рейтинг: <?php echo $clinic['clinic_rating']; ?>★</p>
                            </div>
                        </a>
                        <div class="text-right mt-2">
                            <?php
                            if ($userType !== 'doctor') {
                                if (isset($_SESSION['user_id'])) {
                                    $heartIcon = $isFavorite ? '❤️' : '🤍';

                                    $toggleFavoriteUrl = "favourite.php?clinic_id=" . $clinic['clinic_id'];
                                    echo "<a href=\"$toggleFavoriteUrl\">$heartIcon </a>";
                                } else {
                                    echo '<a href="registration.php">Необходимо войти или зарегистрироваться   🤍</a>';
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

<script src="https://code.jquery.com/jquery-3.6.2.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script type="text/javascript">
    ymaps.ready(init);
    var myMap;

    function init() {
        myMap = new ymaps.Map("map", {
            center: [55.7558, 37.6176], 
            zoom: 12 
        });

        myMap.controls.add(
            new ymaps.control.ZoomControl() // Добавление элемента управления картой
        );

        <?php
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

        $('#search').on('input', function() {
            var searchQuery = $(this).val();
            if (searchQuery.length >= 3) {
                $.ajax({
                    url: 'ajax_search.php', 
                    type: 'GET',
                    data: { search: searchQuery },
                    success: function(response) {
                        $('#searchResults').html(response);
                        $('#searchResults').show();
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            } else {
                $('#searchResults').hide();
            }
        });
    }
</script>

<?php include('footer.html'); ?>
</body>
</html>
