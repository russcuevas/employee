<?php
session_start();
include '../database/connection.php';

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $delete_sql = "DELETE FROM deductions WHERE user_id = ?";
    $stmt_delete = $conn->prepare($delete_sql);
    $stmt_delete->execute([$user_id]);

    $_SESSION['success'] = "Deductions deleted successfully!";
    header('Location: employee_management.php');
    exit();
} else {
    header('Location: employee_management.php');
    exit();
}
