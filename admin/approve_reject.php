<?php
session_start();
include '../database/connection.php';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $absence_id = $_GET['id'];

    if ($action == 'approve') {
        $status = 'Approved';

        $query = "SELECT user_id, absence_date FROM absences WHERE absence_id = :absence_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':absence_id' => $absence_id]);
        $absence = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($absence) {
            $user_id = $absence['user_id'];
            $absence_date = $absence['absence_date'];
            $insertQuery = "INSERT INTO timekeeping (user_id, log_date, attendance_date, present, absent, log_in, log_out)
                            VALUES (:user_id, :log_date, :attendance_date, 0, 1, NULL, NULL)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->execute([
                ':user_id' => $user_id,
                ':log_date' => $absence_date,
                ':attendance_date' => $absence_date
            ]);
        }
    } elseif ($action == 'reject') {
        $status = 'Rejected';
        $query = "SELECT user_id, reason FROM absences WHERE absence_id = :absence_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':absence_id' => $absence_id]);
        $absence = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($absence) {
            $user_id = $absence['user_id'];
            $leave_reason = $absence['reason'];

            if ($leave_reason == 'SICK LEAVE') {
                $updateQuery = "UPDATE users SET sl_credits = sl_credits + 1 WHERE user_id = :user_id";
            } elseif ($leave_reason == 'VACATION LEAVE') {
                $updateQuery = "UPDATE users SET vl_credits = vl_credits + 1 WHERE user_id = :user_id";
            }

            if (isset($updateQuery)) {
                $stmt = $conn->prepare($updateQuery);
                $stmt->execute([':user_id' => $user_id]);
            }
        }
    }

    $query = "UPDATE absences SET status = :status WHERE absence_id = :absence_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':status' => $status, ':absence_id' => $absence_id]);

    $_SESSION['success'] = "Marked successfully.";
    header('Location: leave_request.php');
    exit();
} else {
    $_SESSION['error'] = "Invalid action or absence ID.";
    header('Location: leave_request.php');
    exit();
}
