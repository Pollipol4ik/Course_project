<?php
session_start();
require('db_connection.php');

// Получение поискового запроса из GET-параметра
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT clinic_id, clinic_name FROM veterinary_clinic WHERE clinic_name LIKE '%$searchQuery%'";
$result = $mysqli->query($sql);


if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clinic_id = $row['clinic_id'];
        $clinic_name = $row['clinic_name'];
        echo "<a href='clinic_details.php?clinic_id=$clinic_id'>$clinic_name</a><br>";
    }
} else {
    echo "Ничего не найдено";
}

$mysqli->close();
?>
