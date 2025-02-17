<?php
session_start();
include '../database/connection.php';

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $generated_at = date('Y-m-d H:i:s');

    $insert_query = "INSERT INTO payroll (user_id, deduction_id, period_start, period_end, basic_salary, gross_salary, net_salary, generated_at) 
                     VALUES (:user_id, :deduction_id, :period_start, :period_end, :basic_salary, :gross_salary, :net_salary, :generated_at)";
    $stmt = $conn->prepare($insert_query);
    $stmt->execute([
        ':user_id' => $user_id,
        ':deduction_id' => $deductions['deduction_id'] ?? null,
        ':period_start' => $from_date,
        ':period_end' => $to_date,
        ':basic_salary' => $basic_salary,
        ':gross_salary' => $gross_salary,
        ':net_salary' => $net_salary,
        ':generated_at' => $generated_at
    ]);

    $_SESSION['success'] = "Payroll report generated successfully.";
    header('Location: payroll_management.php');
    exit;
}

?>



<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Management System</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">

</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="index.php" class="brand-link">
                <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                <span class="brand-text font-weight-light">EMS</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="info">
                        <a href="#" class="d-block" style="text-decoration: none;">Admin Panel</a>

                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="employee_management.php" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>
                                    Employee Management
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="employee_schedule.php" class="nav-link">
                                <i class="nav-icon fas fa-calendar-day"></i>
                                <p>
                                    Employee Schedule
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="leave_request.php" class="nav-link">
                                <i class="nav-icon fas fa-file"></i>
                                <p>
                                    Leave Request
                                </p>
                            </a>
                        </li>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-user-clock"></i>
                                <p>
                                    Time Records
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="time_records.php" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p> Records</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="time_in.php" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p> Time In</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="time_out.php" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p> Time Out</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-treeview menu-open">
                            <a href="#" class="nav-link active">
                                <i class="nav-icon fas fa-cash-register"></i>
                                <p>
                                    Payroll Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="payroll_management.php" class="nav-link active">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            Create Payslip
                                        </p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="reports.php" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p> Reports</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>
                                    Logout
                                </p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Payslip</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item active">Payslip</li>
                            </ol>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="invoice p-3 mb-3">
                                <div class="row">
                                    <div class="col-12">
                                        <h4>
                                            <i class="fas fa-credit-card"></i> Payslip for <?php echo $user['name']; ?>
                                            <small class="float-right"><?php echo date("F j, Y", strtotime($from_date)); ?> -- <?php echo date("F j, Y", strtotime($to_date)); ?></small>
                                        </h4>
                                    </div>
                                </div>
                                <br>
                                <br>
                                <div class="row invoice-info">
                                    <div class="col-sm-4">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th colspan="2" style="text-align: left; font-size: 18px;"><strong>Employee Information</strong></th>
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

                                    </div>

                                    <div class="col-sm-4">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th colspan="2" style="text-align: left; font-size: 18px;"><strong>Attendance</strong></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Present Days:</strong></td>
                                                    <td><?php echo $total_present; ?> - <span style="color: red; font-weight: 900;">₱<?php echo number_format($gross_salary, 2); ?></span></td>
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
                                    </div>

                                    <div class="col-sm-4">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th colspan="2" style="text-align: left; font-size: 18px;"><strong>Deductions</strong></th>
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
                                                    <td><strong>Gross Salary</strong></td>
                                                    <td><strong>₱<?php echo number_format($gross_salary, 2); ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <td><strong style="color: red;">Deductions:</strong></td>
                                                    <td><strong style="color: red;">₱<?php echo number_format($total_deductions, 2); ?></strong></td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>


                                    <div class="row">
                                        <div class="col-12">
                                            <h3 style="text-align: right;"><strong> = NET SALARY: ₱<?php echo number_format($net_salary, 2); ?></strong></h3>
                                            <form action="" method="POST">
                                                <!-- INPUT USER ID user_id -->
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <!-- INPUT DEDUCTION ID deduction_id -->
                                                <input type="hidden" name="deduction_id" value="<?php echo $deductions['deduction_id']; ?>">
                                                <!-- INPUT PERIOD START period_start -->
                                                <input type="hidden" name="period_start" value="<?php echo $from_date; ?>">
                                                <!-- INPUT PERIOD END period_end -->
                                                <input type="hidden" name="period_end" value="<?php echo $to_date; ?>">
                                                <input type="hidden" name="basic_salary" value="<?php echo number_format($basic_salary, 2); ?>">

                                                <!-- INPUT GROSS SALARY gross_salary -->
                                                <input type="hidden" name="gross_salary" value="<?php echo number_format($gross_salary, 2); ?>">
                                                <!-- INPUT NET SALARY net_salary -->
                                                <input type="hidden" name="net_salary" value="<?php echo $net_salary; ?>">
                                                <!-- INPUT GENERATED AT generated_at -->
                                                <input type="hidden" name="generated_at" value="<?php echo date('Y-m-d H:i:s'); ?>">

                                                <button class="btn btn-success float-right" type="submit">Add to reports</button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="row no-print">
                                        <div class="col-12">
                                            <a href="payroll_management.php" class="btn btn-default"><i class="fas fa-arrow-left"></i> Back</a>
                                            <a href="print/payslip.php?user_id=<?php echo $user['user_id']; ?>&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>" class="btn btn-info float-right mt-2" target="_blank">
                                                <i class="fas fa-print"></i> Print copy
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </section>

            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js"></script>
</body>

</html>