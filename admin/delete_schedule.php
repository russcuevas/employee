<?php
session_start();
include '../database/connection.php';

if (!isset($_GET['user_id'])) {
    $_SESSION['error'] = "User ID is required.";
    header("Location: employee_schedule.php");
    exit();
}

$user_id = $_GET['user_id'];

$query = $conn->prepare("DELETE FROM schedules WHERE user_id = ?");
if ($query->execute([$user_id])) {
    $_SESSION['success'] = "Schedule deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete schedule.";
}

header("Location: employee_schedule.php");
exit();
