<?php
// Include database connection file
include('../database/connection.php');

session_start();

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $stmt = $conn->prepare("
        UPDATE timekeeping
        SET present = 0, absent = 1, log_in = NULL, log_out = NULL, log_date = NOW()
        WHERE user_id = :user_id
    ");

    $stmt->bindParam(':user_id', $user_id);

    echo "Executing query: " . $stmt->queryString;
    echo " With parameters: user_id = $user_id";

    if ($stmt->execute()) {
        $_SESSION['success'] = "Marked as absent";
    } else {
        $_SESSION['error'] = "Error marking user as absent.";
    }

    header('Location: time_records.php');
    exit();
} else {
    $_SESSION['error'] = "User ID not provided.";
    header('Location: time_records.php');
    exit();
}
