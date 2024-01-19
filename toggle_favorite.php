
<?php
session_start();
require('db_connection.php');

if (isset($_POST['clinicId']) && isset($_POST['isFavorite'])) {
    $clinicId = $_POST['clinicId'];
    $userId = $_SESSION['user_id'];
    $isFavorite = $_POST['isFavorite'];

    if ($isFavorite) {
        // Удаление из избранного
        $delete_sql = "DELETE FROM user_favorites WHERE user_id = ? AND clinic_id = ?";
        $delete_stmt = $mysqli->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $userId, $clinicId);
        $delete_stmt->execute();

        echo 'removed';
    } else {
        // Добавление в избранное
        $insert_sql = "INSERT INTO user_favorites (user_id, clinic_id) VALUES (?, ?)";
        $insert_stmt = $mysqli->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $userId, $clinicId);
        $insert_stmt->execute();

        echo 'added';
    }
}

$mysqli->close();
?>
