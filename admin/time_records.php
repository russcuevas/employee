<?php
session_start();
include('../database/connection.php');

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

$today = date("Y-m-d");

$stmt = $conn->prepare("
    SELECT 
        u.user_id, 
        u.name AS employee_name, 
        t.log_in, 
        t.log_out, 
        t.present, 
        t.log_date, 
        t.absent,
        t.attendance_date
    FROM timekeeping t
    RIGHT JOIN users u ON t.user_id = u.user_id
    WHERE t.attendance_date = :today OR t.log_date = :today
");
$stmt->bindParam(':today', $today);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);




if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_date'])) {
    $attendance_date = $_POST['attendance_date'];
    $stmt = $conn->prepare("SELECT user_id FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $insertStmt = $conn->prepare("
        INSERT INTO timekeeping (user_id, log_in, log_out, attendance_date, present, absent, log_date)
        VALUES (:user_id, NULL, NULL, :attendance_date, 0, 0, NULL)
    ");

    foreach ($users as $user) {
        $checkStmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM timekeeping 
            WHERE user_id = :user_id AND (attendance_date = :attendance_date OR log_date = :attendance_date)
        ");
        $checkStmt->bindParam(':user_id', $user['user_id']);
        $checkStmt->bindParam(':attendance_date', $attendance_date);
        $checkStmt->execute();
        $existingRecord = $checkStmt->fetchColumn();

        if ($existingRecord == 0) {
            $insertStmt->bindParam(':user_id', $user['user_id']);
            $insertStmt->bindParam(':attendance_date', $attendance_date);
            $insertStmt->execute();
        }
    }

    $_SESSION['success'] = "Attendance for today added successfully";
    header('Location: time_records.php');
    exit();
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
    <!-- DataTables -->
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.css">
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
                        <li class="nav-item has-treeview menu-open">
                            <a href="#" class="nav-link active">
                                <i class="nav-icon fas fa-user-clock"></i>
                                <p>
                                    Time Records
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="time_records.php" class="nav-link active">
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
                            <h1>Time Records</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item active">Time Records</li>
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
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Time Record List</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#attendanceModal">
                                    Add Attendance Date +
                                </button>
                                <h3 id="currentTime"></h3>
                                <!-- Button to trigger modal -->

                                <table id="example2" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Time In</th>
                                            <th>Time Out</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($records as $record): ?>
                                            <tr>
                                                <td><?php echo isset($record['employee_name']) ? htmlspecialchars($record['employee_name']) : 'N/A'; ?></td>
                                                <td><?php echo $record['log_in'] ? date("h:i A", strtotime($record['log_in'])) : 'N/A'; ?></td>
                                                <td><?php echo $record['log_out'] ? date("h:i A", strtotime($record['log_out'])) : 'N/A'; ?></td>
                                                <td>
                                                    <?php
                                                    if ($record['present'] == 1) {
                                                        echo 'Present';
                                                    } else if ($record['present'] === NULL) {
                                                        echo 'No record';
                                                    } else if ($record['absent'] === 0 || $record['present'] === 0) {
                                                        echo 'No record';
                                                    } else {
                                                        echo 'Absent';
                                                    }
                                                    ?>

                                                </td>
                                                <td>
                                                    <?php echo $record['log_date'] ? date("F j, Y", strtotime($record['log_date'])) : 'N/A'; ?>
                                                </td>
                                                <td>
                                                    <?php if ($record['log_in'] == null && $record['absent'] != 1): ?>
                                                        <button type="button" class="btn btn-danger btn-sm" data-id="<?php echo $record['user_id']; ?>" onclick="markAsAbsent(this)">Mark as absent</button>
                                                    <?php elseif ($record['log_in'] != null): ?>
                                                        <p>Present</p>
                                                    <?php elseif ($record['absent'] == 1): ?>
                                                        <p>Absent</p>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->


        <!-- Modal -->
        <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="attendanceModalLabel">Add Attendance Date</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="attendance_date">Date Today</label>
                                <input type="text" class="form-control" id="attendance_date" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" readonly>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Attendance date</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



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
    <!-- DataTables -->
    <script src="plugins/datatables/jquery.dataTables.js"></script>
    <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js"></script>
    <!-- page script -->
    <script>
        function markAsAbsent(button) {
            var userId = button.getAttribute('data-id');

            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'mark_as_absent.php'; // Replace with the actual PHP script handling the request

            // Create a hidden input field for user_id
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_id';
            input.value = userId;
            form.appendChild(input);

            // Append the form to the body and submit
            document.body.appendChild(form);
            form.submit();
        }
    </script>
    <script>
        $(function() {
            $("#example1").DataTable();
            $('#example2').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": true,
                "autoWidth": false,
            });
        });
    </script>
    <script>
        function updateTime() {
            let now = new Date();

            let monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

            let day = now.getDate();
            let month = monthNames[now.getMonth()];
            let year = now.getFullYear();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            let ampm = hours >= 12 ? 'pm' : 'am';

            hours = hours % 12;
            hours = hours ? hours : 12;

            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            let formattedTime = month + ' ' + day + ', ' + year + ' - ' + hours + ':' + minutes + ':' + seconds + ampm;

            document.getElementById("currentTime").innerText = formattedTime;
        }

        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>

</html>