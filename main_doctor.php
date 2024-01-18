<?php
session_start();
require('db_connection.php');

// Проверка существования сессии врача
if (!isset($_SESSION['doctor_id'])) {
    header("Location: enter.php");
    exit();
}

$doctorId = $_SESSION['doctor_id'];

// Определение параметра сортировки
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'default';

// SQL-запрос с учетом сортировки
$sql = "SELECT DATE(created_at) AS date, COUNT(*) AS messages_count
        FROM messages
        WHERE doctor_id = ?
        GROUP BY DATE(created_at)
        ORDER BY " .
        ($sortOrder === 'default' ? 'DATE(created_at) ASC' : 'messages_count ' . ($sortOrder === 'desc' ? 'DESC' : 'ASC') . ', DATE(created_at) ASC');

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $doctorId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Формирование данных для графика
$dates = [];
$messageCounts = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['date'];
    $messageCounts[] = $row['messages_count'];
}

// Получение имени врача для обращения
$sqlDoctorName = "SELECT full_name FROM doctors WHERE doctor_id = ?";
$stmtDoctorName = $mysqli->prepare($sqlDoctorName);
$stmtDoctorName->bind_param("i", $doctorId);
$stmtDoctorName->execute();
$resultDoctorName = $stmtDoctorName->get_result();
$stmtDoctorName->close();
$doctorName = ($resultDoctorName->num_rows > 0) ? $resultDoctorName->fetch_assoc()['full_name'] : 'Уважаемый врач';

// Закрытие соединения с базой данных
$mysqli->close();

// Вычисление характеристик
$averageMessages = array_sum($messageCounts) / count($messageCounts);
$maxMessages = max($messageCounts);
$minMessages = min($messageCounts);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Активность врача: <?= htmlspecialchars($doctorName) ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
    <style>
        .badge-danger {
            background-color: #dc3545;
        }

        .badge-warning {
            background-color: #ffc107;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-primary {
            background-color: #007bff;
        }

        .color-mark {
            width: 20px;
            height: 20px;
            display: inline-block;
            margin-left: 5px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include('header_doctor.php'); ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4"><?= htmlspecialchars($doctorName) ?>, статистика по времени</h2>
                <p>
                    На этой странице представлена статистика вашей активности за последний месяц. 
                    График отображает количество ответов, предоставленных вами пациентам, за каждый день.
                    Вы можете оценить динамику вашей врачебной активности и получить общее представление о вашей занятости.
                </p>
                <div class="form-group">
                    <label for="sortSelect">Сортировка:</label>
                    <select id="sortSelect" class="form-control" onchange="changeSort()">
                        <option value="default" <?= ($sortOrder === 'default') ? 'selected' : '' ?>>По умолчанию</option>
                        <option value="asc" <?= ($sortOrder === 'asc') ? 'selected' : '' ?>>По возрастанию</option>
                        <option value="desc" <?= ($sortOrder === 'desc') ? 'selected' : '' ?>>По убыванию</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <canvas id="activityChart" style="height: 300px;"></canvas>
            </div>
            <div class="col-md-4">
                <h4 class="mb-3">Статистика за месяц:</h4>
                <!-- Обозначения цветов -->
                <div class="color-mark" style="background-color: #dc3545 !important;"></div> <span class="font-weight-bold">Сильная недоработка</span><br>
                <div class="color-mark" style="background-color: #ffc107 !important;"></div> <span class="font-weight-bold">Незначительная недоработка</span><br>
                <div class="color-mark" style="background-color: #28a745 !important;"></div> <span class="font-weight-bold">Норма</span><br>
                <div class="color-mark" style="background-color: #007bff !important"></div> <span class="font-weight-bold">Переработка</span><br>

        <ul class="list-group">
            <?php for ($i = 0; $i < count($dates); $i++): ?>
                <?php
                $messageCount = $messageCounts[$i];
                $badgeClass = '';
                $badgeDescription = '';

                if ($messageCount < 10) {
                    $badgeClass = 'badge-danger'; // Красный
                    $badgeDescription = 'Низкое количество сообщений';
                } elseif ($messageCount >= 10 && $messageCount < 15) {
                    $badgeClass = 'badge-warning'; // Желтый
                    $badgeDescription = 'Среднее количество сообщений';
                } elseif ($messageCount >= 15 && $messageCount < 20) {
                    $badgeClass = 'badge-success'; // Зеленый
                    $badgeDescription = 'Высокое количество сообщений';
                } else {
                    $badgeClass = 'badge-primary'; // Синий
                    $badgeDescription = 'Очень высокое количество сообщений';
                }
                ?>

                <li class="list-group-item d-flex justify-content-between align-items-center mt-3">
                    <span><?= $dates[$i] ?></span>
                    <span class="badge <?= $badgeClass ?> badge-pill color-mark" title="<?= $badgeDescription ?>"></span>
                    <span class="badge <?= $badgeClass ?> badge-pill"><?= $messageCount ?></span>
                </li>
            <?php endfor; ?>
        </ul>

        <h4 class="mt-4">Характеристики:</h4>
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Среднее количество сообщений:
                <span class="badge badge-info badge-pill"><?= round($averageMessages, 2) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Максимальное количество сообщений в день:
                <span class="badge badge-info badge-pill"><?= $maxMessages ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Минимальное количество сообщений в день:
                <span class="badge badge-info badge-pill"><?= $minMessages ?></span>
            </li>
        </ul>
    </div>
</div>

    <script>
        function changeSort() {
            var sortSelect = document.getElementById('sortSelect');
            var sortOrder = sortSelect.options[sortSelect.selectedIndex].value;
            window.location.href = '?sort=' + sortOrder;
        }

        var ctx = document.getElementById('activityChart').getContext('2d');
        var activityChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($dates) ?>,
                datasets: [{
                    label: 'Количество сообщений',
                    data: <?= json_encode($messageCounts) ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    fill: false,
                }]
            },
            options: {
                scales: {
                    x: [{
                        type: 'time',
                        time: {
                            unit: 'day',
                            displayFormats: {
                                day: 'D MMM'
                            },
                            tooltipFormat: 'D MMM'
                        },
                        title: {
                            display: true,
                            text: 'Дата'
                        }
                    }],
                    y: {
                        title: {
                            display: true,
                            text: 'Количество сообщений'
                        }
                    }
                }
            }
        });
    </script>

    

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

    <?php include('footer.html'); ?>
</body>
</html>
