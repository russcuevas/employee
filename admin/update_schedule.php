<?php
session_start();
include '../database/connection.php';

if (!isset($_GET['user_id'])) {
    $_SESSION['error'] = "User ID is required.";
    header("Location: employee_schedule.php");
    exit();
}

$user_id = $_GET['user_id'];
$query = $conn->prepare("SELECT * FROM schedules WHERE user_id = ?");
$query->execute([$user_id]);
$schedule = $query->fetch(PDO::FETCH_ASSOC);

if (!$schedule) {
    $_SESSION['error'] = "Schedule not found.";
    header("Location: employee_schedule.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shift_start = $_POST['shift_start'];
    $shift_end = $_POST['shift_end'];
    $work_days = $_POST['work_days'];

    $updateQuery = $conn->prepare("UPDATE schedules SET shift_start = ?, shift_end = ?, work_days = ? WHERE user_id = ?");
    if ($updateQuery->execute([$shift_start, $shift_end, $work_days, $user_id])) {
        $_SESSION['success'] = "Schedule updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update schedule.";
    }
    header("Location: employee_schedule.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Management System</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css?v=3.2.0">
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
            <a href="index3.html" class="brand-link">
                <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                <span class="brand-text font-weight-light">System Name</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="info">
                        <a href="#" class="d-block">Admin Panel</a>
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
                            <a href="employee_schedule.php" class="nav-link active">
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
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-cash-register"></i>
                                <p>
                                    Payroll Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="payroll_management.php" class="nav-link">
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
                            <h1>Add Schedule</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="employee_schedule.php">Employee Schedule</a></li>
                                <li class="breadcrumb-item active">Add Schedule</li>
                            </ol>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <!-- Main content -->
            <section class="content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; ?>
                        <?php unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_SESSION['error']; ?>
                        <?php unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="container-fluid">
                    <form id="quickForm" method="POST">
                        <div class="form-group">
                            <label>Shift Start:</label>
                            <input type="time" class="form-control" name="shift_start" value="<?php echo $schedule['shift_start']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Shift End:</label>
                            <input type="time" class="form-control" name="shift_end" value="<?php echo $schedule['shift_end']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Work Days - <span style="color: red;">(e.g., Monday to Friday or M-F)</span></label>
                            <input type="text" class="form-control" name="work_days" value="<?php echo $schedule['work_days']; ?>" required><br>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Schedule</button>
                    </form>

                </div>
            </section>


            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2025 <a href="https://adminlte.io">System Name</a>.</strong> All rights reserved.
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- jquery-validation -->
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="plugins/jquery-validation/additional-methods.min.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js?v=3.2.0"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js"></script>
    <!-- Page specific script -->
    <script>
        $(function() {
            $('#quickForm').validate({
                rules: {
                    email: {
                        required: true,
                        email: true,
                    },
                    password: {
                        required: true,
                        minlength: 5
                    },
                    terms: {
                        required: true
                    },
                },
                messages: {
                    email: {
                        required: "Please enter a email address",
                        email: "Please enter a valid email address"
                    },
                    password: {
                        required: "Please provide a password",
                        minlength: "Your password must be at least 5 characters long"
                    },
                    terms: "Please accept our terms"
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
</body>

</html>