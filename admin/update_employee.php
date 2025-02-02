<?php
session_start();
include '../database/connection.php';

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $employee = $stmt->fetch();


    if (!$employee) {
        $_SESSION['error'] = "Employee not found!";
        header('Location: employee_management.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $position = $_POST['position'];
        $basic_salary = $_POST['basic_salary'];
        $sss_number = $_POST['sss_number'];
        $pagibig_number = $_POST['pagibig_number'];
        $tin_number = $_POST['tin_number'];
        $philhealth_number = $_POST['philhealth_number'];
        $vl_credits = $_POST['vl_credits']; // Added field
        $sl_credits = $_POST['sl_credits']; // Added field

        $update_sql = "UPDATE users SET name = ?, username = ?, email = ?, position = ?, basic_salary = ?, sss_number = ?, pagibig_number = ?, tin_number = ?, philhealth_number = ?, vl_credits = ?, sl_credits = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->execute([
            $name,
            $username,
            $email,
            $position,
            $basic_salary,
            $sss_number,
            $pagibig_number,
            $tin_number,
            $philhealth_number,
            $vl_credits,
            $sl_credits,
            $user_id
        ]);

        $_SESSION['success'] = "Employee details updated successfully!";
        header('Location: employee_management.php');
        exit();
    }
} else {
    header('Location: employee_management.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Employee</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css?v=3.2.0">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">

</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
        </nav>

        <!-- Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="index.php" class="brand-link">
                <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">EMS</span>
            </a>
            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="info">
                        <a href="#" class="d-block">Admin Panel</a>
                    </div>
                </div>
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
                            <a href="employee_management.php" class="nav-link active">
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

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Update Employee</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="employee_management.php">Employee Management</a></li>
                                <li class="breadcrumb-item active">Update Employee</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main Content -->
            <section class="content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_SESSION['error']; ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="container-fluid">
                    <form action="" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $employee['user_id']; ?>">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Employee Account Details</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="name">Full Name</label>
                                            <input type="text" name="name" class="form-control" id="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" name="username" class="form-control" id="username" value="<?php echo htmlspecialchars($employee['username']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Email Address</label>
                                            <input type="email" name="email" class="form-control" id="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Employment Details</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="position">Position</label>
                                            <input type="text" name="position" class="form-control" id="position" value="<?php echo htmlspecialchars($employee['position']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="basic_salary">Basic Salary</label>
                                            <input type="number" name="basic_salary" class="form-control" id="basic_salary" value="<?php echo htmlspecialchars($employee['basic_salary']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="sss_number">SSS Number</label>
                                            <input type="text" name="sss_number" class="form-control" id="sss_number" value="<?php echo htmlspecialchars($employee['sss_number']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="pagibig_number">Pag-IBIG Number</label>
                                            <input type="text" name="pagibig_number" class="form-control" id="pagibig_number" value="<?php echo htmlspecialchars($employee['pagibig_number']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="tin_number">TIN Number</label>
                                            <input type="text" name="tin_number" class="form-control" id="tin_number" value="<?php echo htmlspecialchars($employee['tin_number']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="philhealth_number">PhilHealth Number</label>
                                            <input type="text" name="philhealth_number" class="form-control" id="philhealth_number" value="<?php echo htmlspecialchars($employee['philhealth_number']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="vl_credits">VL Credits</label>
                                            <input type="number" name="vl_credits" class="form-control" id="vl_credits" value="<?php echo htmlspecialchars($employee['vl_credits']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="sl_credits">SL Credits</label>
                                            <input type="number" name="sl_credits" class="form-control" id="sl_credits" value="<?php echo htmlspecialchars($employee['sl_credits']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary">Update Employee</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        <!-- Footer -->

    </div>

    <!-- jQuery -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js?v=3.2.0"></script>
</body>

</html>