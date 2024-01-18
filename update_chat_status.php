<?php
session_start();
require('db_connection.php');
include('db_functions.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctorId = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : null;
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($doctorId !== null && $userId !== null) {
        try {
            // Update chat status to 'open' when resuming the chat
            $result = updateChatStatus($mysqli, $userId, $doctorId, 'open');

            // Return the result
            echo $result;
        } catch (Exception $e) {
            // Log the error
            error_log("Error updating chat status: " . $e->getMessage(), 0);

            // Return an error
            echo "error";
        }
    } else {
        // Return an error if doctor_id or user_id is not set
        echo "error";
    }
}

$mysqli->close();
?>
