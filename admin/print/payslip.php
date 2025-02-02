<?php
session_start();
include '../../database/connection.php';

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

// Get user_id and dates from URL parameters
$user_id = $_GET['user_id'];
$from_date = $_GET['from_date'];
$to_date = $_GET['to_date'];

// Fetch user details
$get_user = "SELECT * FROM users WHERE user_id = :user_id";
$stmt_user = $conn->prepare($get_user);
$stmt_user->execute([':user_id' => $user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Fetch timekeeping records between the selected dates
$query_timekeeping = "SELECT * FROM timekeeping WHERE user_id = :user_id AND log_date BETWEEN :from_date AND :to_date";
$stmt_timekeeping = $conn->prepare($query_timekeeping);
$stmt_timekeeping->execute([':user_id' => $user_id, ':from_date' => $from_date, ':to_date' => $to_date]);
$timekeeping = $stmt_timekeeping->fetchAll(PDO::FETCH_ASSOC);

// Fetch deductions
$query_deductions = "SELECT * FROM deductions WHERE user_id = :user_id";
$stmt_deductions = $conn->prepare($query_deductions);
$stmt_deductions->execute([':user_id' => $user_id]);
$deductions = $stmt_deductions->fetch(PDO::FETCH_ASSOC);

// Calculate deductions
$sss = isset($deductions['sss']) ? $deductions['sss'] : 0;
$pagibig = isset($deductions['pagibig']) ? $deductions['pagibig'] : 0;
$philhealth = isset($deductions['philhealth']) ? $deductions['philhealth'] : 0;
$tax = isset($deductions['tax']) ? $deductions['tax'] : 0;

// Calculate total deductions and net salary
$total_deductions = $sss + $pagibig + $philhealth + $tax;
$net_salary = $user['basic_salary'] - $total_deductions;

// Initialize counters for presents and absents
$total_present = 0;
$total_absent = 0;

// Count presents and absents
foreach ($timekeeping as $log) {
    $total_present += $log['present'];
    $total_absent += $log['absent'];
}

// Fetch absences
$query_absences = "SELECT * FROM absences WHERE user_id = :user_id AND absence_date BETWEEN :from_date AND :to_date";
$stmt_absences = $conn->prepare($query_absences);
$stmt_absences->execute([':user_id' => $user_id, ':from_date' => $from_date, ':to_date' => $to_date]);
$absences = $stmt_absences->fetchAll(PDO::FETCH_ASSOC);

// Initialize the reason string for display
$absence_reasons = "";
if (!empty($absences)) {
    foreach ($absences as $absence) {
        $absence_reasons .= $absence['absence_date'] . " - " . $absence['reason'] . "<br>";
    }
} else {
    $absence_reasons = "No reason";
}

// Fetch work schedule
$query_schedule = "SELECT shift_start, shift_end, work_days FROM schedules WHERE user_id = :user_id";
$stmt_schedule = $conn->prepare($query_schedule);
$stmt_schedule->execute([':user_id' => $user_id]);
$schedule = $stmt_schedule->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Payslip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }

        .invoice-header {
            text-align: center;
        }

        .invoice-header h2 {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        table th,
        table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .total {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="invoice-header">
            <h2>Payslip for <?php echo $user['name']; ?></h2>
            <p><small>From: <?php echo $from_date; ?> To: <?php echo $to_date; ?></small></p>
        </div>

        <div class="employee-info">
            <table>
                <tr>
                    <th>Name</th>
                    <td><?php echo $user['name']; ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo $user['email']; ?></td>
                </tr>
                <tr>
                    <th>Position</th>
                    <td><?php echo $user['position']; ?></td>
                </tr>
                <tr>
                    <th>Basic Salary</th>
                    <td>₱<?php echo number_format($user['basic_salary'], 2); ?></td>
                </tr>
            </table>
        </div>

        <div class="attendance">
            <table>
                <tr>
                    <th>Present Days</th>
                    <td><?php echo $total_present; ?></td>
                </tr>
                <tr>
                    <th>Absent Days</th>
                    <td><?php echo $total_absent; ?></td>
                </tr>
                <tr>
                    <th>Absence Details</th>
                    <td><?php echo $absence_reasons; ?></td>
                </tr>
            </table>
        </div>

        <div class="schedule">
            <table>
                <?php if ($schedule): ?>
                    <tr>
                        <th>Work Days</th>
                        <td><?php echo $schedule['work_days']; ?></td>
                    </tr>
                    <tr>
                        <th>Shift Start</th>
                        <td><?php echo $schedule['shift_start']; ?></td>
                    </tr>
                    <tr>
                        <th>Shift End</th>
                        <td><?php echo $schedule['shift_end']; ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th colspan="2">No schedule set</th>
                    </tr>
                <?php endif; ?>
            </table>
        </div>


        <div class="deductions">
            <table>
                <tr>
                    <th>SSS</th>
                    <td>₱<?php echo number_format($sss, 2); ?></td>
                </tr>
                <tr>
                    <th>PAG-IBIG</th>
                    <td>₱<?php echo number_format($pagibig, 2); ?></td>
                </tr>
                <tr>
                    <th>PhilHealth</th>
                    <td>₱<?php echo number_format($philhealth, 2); ?></td>
                </tr>
                <tr>
                    <th>Tax</th>
                    <td>₱<?php echo number_format($tax, 2); ?></td>
                </tr>
                <tr>
                    <th>Total Deductions</th>
                    <td><strong>₱<?php echo number_format($total_deductions, 2); ?></strong></td>
                </tr>
            </table>
        </div>



        <div class="total">
            <p><strong>Net Salary: ₱<?php echo number_format($net_salary, 2); ?></strong></p>
        </div>

        <div class="footer">
            <p>Thank you for your hard work!</p>
        </div>
    </div>

    <script>
        window.print();
    </script>
</body>

</html>