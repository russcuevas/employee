<?php
session_start();
include '../database/connection.php';

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_GET['user_id'])) {
    $employee_id = $_GET['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$employee_id]);

    $_SESSION['success'] = "Employee deleted successfully!";
    header('Location: employee_management.php');
    exit();
} else {
    $_SESSION['error'] = "No employee ID provided!";
    header('Location: employee_management.php');
    exit();
}
