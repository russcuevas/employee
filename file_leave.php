<?php
session_start();
include 'database/connection.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $absence_date = $_POST['absence_date'];
    $reason = $_POST['reason'];

    if (empty($absence_date) || empty($reason)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header('location: leave_request.php');
        exit();
    }

    try {
        $sql = "SELECT sl_credits, vl_credits FROM users WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $sl_credits = $user['sl_credits'];
            $vl_credits = $user['vl_credits'];

            if ($reason == 'SICK LEAVE' && $sl_credits > 0) {
                $new_sl_credits = $sl_credits - 1;
                $update_sql = "UPDATE users SET sl_credits = :new_sl_credits WHERE user_id = :user_id";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bindParam(':new_sl_credits', $new_sl_credits, PDO::PARAM_INT);
                $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $update_stmt->execute();
            } elseif ($reason == 'VACATION LEAVE' && $vl_credits > 0) {
                $new_vl_credits = $vl_credits - 1;
                $update_sql = "UPDATE users SET vl_credits = :new_vl_credits WHERE user_id = :user_id";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bindParam(':new_vl_credits', $new_vl_credits, PDO::PARAM_INT);
                $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $update_stmt->execute();
            } else {
                $_SESSION['error'] = "Not enough leave credits.";
                header('location: leave_request.php');
                exit();
            }

            $sql_insert = "INSERT INTO absences (user_id, absence_date, reason, status) 
                           VALUES (:user_id, :absence_date, :reason, 'Pending')";
            $insert_stmt = $conn->prepare($sql_insert);
            $insert_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $insert_stmt->bindParam(':absence_date', $absence_date, PDO::PARAM_STR);
            $insert_stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
            $insert_stmt->execute();

            $_SESSION['success'] = "Leave request submitted successfully!";
            header('location: leave_request.php');
            exit();
        } else {
            $_SESSION['error'] = "User not found.";
            header('location: leave_request.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header('location: leave_request.php');
        exit();
    }

    header('location: leave_request.php');
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
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css?v=3.2.0">
    <link rel="stylesheet" href="assets/dist/css/bootstrap.min.css">

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
                <img src="assets/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                <span class="brand-text font-weight-light">EMS</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="info">
                        <a href="#" style="text-decoration: none !important;" class="d-block">Employee Panel</a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="information.php" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Information
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="leave_request.php" class="nav-link active">
                                <i class="nav-icon fas fa-file"></i>
                                <p>
                                    Request a Leave
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
                            <h1>File a Leave</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="leave_request.php">Leave Request</a></li>
                                <li class="breadcrumb-item active">File a leave</li>
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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">File a Leave Request</h3>
                                    </div>
                                    <div class="card-body">
                                        <!-- Absence Date -->
                                        <div class="form-group">
                                            <label for="absence_date">Leave Date</label>
                                            <input type="date" name="absence_date" class="form-control" id="absence_date" required>
                                        </div>

                                        <!-- Reason for Leave -->
                                        <div class="form-group">
                                            <label for="reason">Reason for Leave</label>
                                            <select name="reason" class="form-control" id="reason" required>
                                                <option value="">Select Reason</option>
                                                <option value="SICK LEAVE">Sick Leave</option>
                                                <option value="VACATION LEAVE">Vacation Leave</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-md-12">
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary">Submit Leave Request</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </section>


            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->


        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- jquery-validation -->
    <script src="assets/plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="assets/plugins/jquery-validation/additional-methods.min.js"></script>
    <!-- AdminLTE App -->
    <script src="assets/dist/js/adminlte.min.js?v=3.2.0"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="assets/dist/js/demo.js"></script>
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