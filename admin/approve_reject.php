<?php
include '../database/connection.php';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $absence_id = $_GET['id'];

    if ($action == 'approve') {
        $status = 'Approved';

        // Fetch the absence_date and user_id from the absences table
        $query = "SELECT user_id, absence_date FROM absences WHERE absence_id = :absence_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':absence_id' => $absence_id]);
        $absence = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($absence) {
            $user_id = $absence['user_id'];
            $absence_date = $absence['absence_date'];

            // Insert data into the timekeeping table with absence details
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

        // Fetch the leave reason and user_id from absences table
        $query = "SELECT user_id, reason FROM absences WHERE absence_id = :absence_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':absence_id' => $absence_id]);
        $absence = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($absence) {
            $user_id = $absence['user_id'];
            $leave_reason = $absence['reason'];

            // Check if the reason is SICK LEAVE or VACATION LEAVE
            if ($leave_reason == 'SICK LEAVE') {
                // Increase the sick leave credits by 1
                $updateQuery = "UPDATE users SET sl_credits = sl_credits + 1 WHERE user_id = :user_id";
            } elseif ($leave_reason == 'VACATION LEAVE') {
                // Increase the vacation leave credits by 1
                $updateQuery = "UPDATE users SET vl_credits = vl_credits + 1 WHERE user_id = :user_id";
            }

            if (isset($updateQuery)) {
                // Execute the update to increment the leave credits
                $stmt = $conn->prepare($updateQuery);
                $stmt->execute([':user_id' => $user_id]);
            }
        }
    }

    // Update the status of the absence to Approved or Rejected
    $query = "UPDATE absences SET status = :status WHERE absence_id = :absence_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':status' => $status, ':absence_id' => $absence_id]);

    // Redirect back to the leave request page
    header('Location: leave_request.php');
    exit();
} else {
    echo "Invalid action or absence ID.";
}
