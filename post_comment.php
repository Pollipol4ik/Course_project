<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: registration.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentText = $_POST['comment'];
    $userId = $_SESSION['user_id'];
    $userName = $_SESSION['user_name'];

    require('db_connection.php');

    $clinic_id = isset($_GET['clinic_id']) ? $_GET['clinic_id'] : null;

    // Insert new comment
    $stmtInsertComment = $mysqli->prepare("INSERT INTO comments (clinic_id, user_id, user_name, comment_text, rating) VALUES (?, ?, ?, ?, ?)");
    $stmtInsertComment->bind_param("iissi", $clinic_id, $userId, $userName, $commentText, $_POST['rating_value']);

    if (!$stmtInsertComment->execute()) {
        echo "Error: " . $stmtInsertComment->error;
        exit();
    }

    $stmtInsertComment->close();

    // Update the average rating and reviews count in the veterinary clinic table
    $stmtUpdateClinicRating = $mysqli->prepare("UPDATE veterinary_clinic SET clinic_rating = (SELECT AVG(rating) FROM comments WHERE clinic_id = ?), reviews_count = (SELECT COUNT(*) FROM comments WHERE clinic_id = ?) WHERE clinic_id = ?");
    $stmtUpdateClinicRating->bind_param("iii", $clinic_id, $clinic_id, $clinic_id);

    if (!$stmtUpdateClinicRating->execute()) {
        echo "Error: " . $stmtUpdateClinicRating->error;
        exit();
    }

    $stmtUpdateClinicRating->close();

    $mysqli->close();

    header("Location: clinic_details.php?clinic_id=$clinic_id");
    exit();
} else {
    header("Location: main.php");
    exit();
}
?>
