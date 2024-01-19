<?php
session_start();
require('db_connection.php');
include('db_functions.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctorId = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : null;
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($doctorId !== null && $userId !== null) {
        try {
           
            $result = updateChatStatus($mysqli, $userId, $doctorId, 'open');

           
            echo $result;
        } catch (Exception $e) {
           
            error_log("Error updating chat status: " . $e->getMessage(), 0);

           
            echo "error";
        }
    } else {
        
        echo "error";
    }
}

$mysqli->close();
?>
