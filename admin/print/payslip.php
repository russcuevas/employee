<?php
session_start();
include '../../database/connection.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('location:admin_login.php');
    exit;
}

// Get user_id and date range from URL parameters
$user_id = $_GET['user_id'];
$from_date = $_GET['from_date'];
$to_date = $_GET['to_date'];

// Fetch user details
$get_user = "SELECT * FROM users WHERE user_id = :user_id";
$stmt_user = $conn->prepare($get_user);
$stmt_user->execute([':user_id' => $user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Function to count working days (Monday to Friday) in a given month
function countWorkingDaysInMonth($month, $year)
{
    $start_date = new DateTime("{$year}-{$month}-01");
    $end_date = new DateTime("{$year}-{$month}-01");
    $end_date->modify('last day of this month');

    $total_days = $end_date->format('d');

    $work_days = 0;

    for ($day = 1; $day <= $total_days; $day++) {
        $date = new DateTime("{$year}-{$month}-{$day}");

        if ($date->format('N') < 6) {
            $work_days++;
        }
    }

    return $work_days;
}


$current_year = date('Y');
$current_month = date('m');

$from_date_parts = explode('-', $from_date);
$to_date_parts = explode('-', $to_date);
$from_month = $from_date_parts[1];
$from_year = $from_date_parts[0];
$to_month = $to_date_parts[1];
$to_year = $to_date_parts[0];

if ($from_month == $to_month) {
    $total_working_days_in_range = countWorkingDaysInMonth($from_month, $from_year);
} else {
    $total_working_days_in_range = countWorkingDaysInMonth($from_month, $from_year);
}

$basic_salary = $user['basic_salary'];
$daily_salary = ($total_working_days_in_range > 0) ? $basic_salary / $total_working_days_in_range : 0;

$query_timekeeping = "SELECT SUM(present) AS total_present, SUM(absent) AS total_absent 
                      FROM timekeeping 
                      WHERE user_id = :user_id AND log_date BETWEEN :from_date AND :to_date";
$stmt_timekeeping = $conn->prepare($query_timekeeping);
$stmt_timekeeping->execute([':user_id' => $user_id, ':from_date' => $from_date, ':to_date' => $to_date]);
$timekeeping = $stmt_timekeeping->fetch(PDO::FETCH_ASSOC);

$total_present = $timekeeping['total_present'] ?? 0;
$total_absent = $timekeeping['total_absent'] ?? 0;

$query_deductions = "SELECT * FROM deductions WHERE user_id = :user_id";
$stmt_deductions = $conn->prepare($query_deductions);
$stmt_deductions->execute([':user_id' => $user_id]);
$deductions = $stmt_deductions->fetch(PDO::FETCH_ASSOC) ?: [];

$sss = $deductions['sss'] ?? 0;
$pagibig = $deductions['pagibig'] ?? 0;
$philhealth = $deductions['philhealth'] ?? 0;
$tax = $deductions['tax'] ?? 0;
$total_deductions = $sss + $pagibig + $philhealth + $tax;

$gross_salary = $daily_salary * $total_present;
$net_salary = $gross_salary - $total_deductions;

$query_absences = "SELECT absence_date, reason FROM absences 
                   WHERE user_id = :user_id AND status = 'Approved' 
                   AND absence_date BETWEEN :from_date AND :to_date";
$stmt_absences = $conn->prepare($query_absences);
$stmt_absences->execute([':user_id' => $user_id, ':from_date' => $from_date, ':to_date' => $to_date]);
$absences = $stmt_absences->fetchAll(PDO::FETCH_ASSOC);

$absence_reasons = !empty($absences) ? implode('<br>', array_map(function ($a) {
    return "{$a['absence_date']} - {$a['reason']}";
}, $absences)) : "No file leaves.";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../dist/css/bootstrap.min.css">
    <title>Payslip</title>
    <style>
        /* Add any custom styles for printing */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 10px;
        }

        h1 {
            text-align: center;
            font-size: 24px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .float-right {
            float: right;
        }
    </style>
</head>

<body onload="window.print();">
    <div class="container">
        <h1>Payslip for <?php echo $user['name']; ?></h1>
        <p><strong>Period:</strong> <?php echo date("F j, Y", strtotime($from_date)); ?> to <?php echo date("F j, Y", strtotime($to_date)); ?></p>

        <table class="table">
            <thead>
                <tr>
                    <th colspan="2">Employee Information</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Name:</strong></td>
                    <td><?php echo $user['name']; ?></td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><?php echo $user['email']; ?></td>
                </tr>
                <tr>
                    <td><strong>Position:</strong></td>
                    <td><?php echo $user['position']; ?></td>
                </tr>
                <tr>
                    <td><strong>Basic Salary:</strong></td>
                    <td>₱<?php echo number_format($basic_salary, 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Daily Salary:</strong></td>
                    <td>₱<?php echo number_format($daily_salary, 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Working Days:</strong></td>
                    <td><?php echo $total_working_days_in_range; ?> days</td>
                </tr>
            </tbody>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th colspan="2">Attendance</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Present Days:</strong></td>
                    <td><?php echo $total_present; ?> - <span>₱<?php echo number_format($gross_salary, 2); ?></span></td>
                </tr>
                <tr>
                    <td><strong>Absent Days:</strong></td>
                    <td><?php echo $total_absent; ?></td>
                </tr>
                <tr>
                    <td><strong>Reason:</strong></td>
                    <td><?php echo $absence_reasons; ?></td>
                </tr>
            </tbody>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th colspan="2">Deductions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>SSS:</strong></td>
                    <td>₱<?php echo number_format($sss, 2); ?></td>
                </tr>
                <tr>
                    <td><strong>PAG-IBIG:</strong></td>
                    <td>₱<?php echo number_format($pagibig, 2); ?></td>
                </tr>
                <tr>
                    <td><strong>PhilHealth:</strong></td>
                    <td>₱<?php echo number_format($philhealth, 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Tax:</strong></td>
                    <td>₱<?php echo number_format($tax, 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Gross Salary:</strong></td>
                    <td>₱<?php echo number_format($gross_salary, 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Deductions:</strong></td>
                    <td>₱<?php echo number_format($total_deductions, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <h3 class="float-right"><strong>= Net Salary: ₱<?php echo number_format($net_salary, 2); ?></strong></h3>
    </div>
</body>

</html>