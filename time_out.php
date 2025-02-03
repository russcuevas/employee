<?php
session_start();
include 'database/connection.php';

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $log_date = $_POST['log_date'];
    $log_out = $_POST['log_out'];

    $stmt = $conn->prepare("SELECT user_id, log_out FROM timekeeping WHERE user_id = ? AND log_date = ?");
    $stmt->execute([$user_id, $log_date]);
    $attendance = $stmt->fetch();

    if ($attendance) {
        if ($attendance['log_out'] !== NULL) {
            $_SESSION['error'] = "You have already timed out for today.";
            header('Location: time_out.php');
            exit();
        } else {
            $update_stmt = $conn->prepare("UPDATE timekeeping SET log_out = ? WHERE user_id = ? AND log_date = ?");
            if ($update_stmt->execute([$log_out, $user_id, $log_date])) {
                $_SESSION['success'] = "Time Out Recorded Successfully!";
                header('Location: time_out.php');
                exit();
            } else {
                $_SESSION['error'] = "Failed to record time out!";
                header('Location: time_out.php');
                exit();
            }
        }
    } else {
        $_SESSION['error'] = "No time-in record found for this user today!";
        header('Location: time_out.php');
        exit();
    }
}


$user_stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();
$user_name = $user['name'];

$date_today = date('Y-m-d');

$timekeeping_stmt = $conn->prepare("SELECT t.*, u.name FROM timekeeping t JOIN users u ON t.user_id = u.user_id WHERE t.log_date = ?");
$timekeeping_stmt->execute([$date_today]);
$timekeeping_records = $timekeeping_stmt->fetchAll();


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
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="assets/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
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
                <img src="assets/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
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
                            <a href="leave_request.php" class="nav-link">
                                <i class="nav-icon fas fa-file"></i>
                                <p>
                                    Request a Leave
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="time_in.php" class="nav-link">
                                <i class="nav-icon fas fa-user-clock"></i>
                                <p>
                                    Time In
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="time_out.php" class="nav-link active">
                                <i class="nav-icon fas fa-user-clock"></i>
                                <p>
                                    Time Out
                                </p>
                            </a>
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
                            <h1>Time Out</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item active">Time Out</li>
                            </ol>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <!-- Main content -->
            <section class="content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; ?>
                        <?php unset($_SESSION['success']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; ?>
                        <?php unset($_SESSION['error']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="row">

                    <!-- Right Column (Employee Image and Basic Information) -->
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <!-- Image Section -->
                                <div class="mb-4">
                                    <img class="img-fluid rounded shadow-sm" style="height: auto; width: 100%; object-fit: cover;" src="https://tse1.mm.bing.net/th?id=OIP.srNFFzORAaERcWvhwgPzVAHaHa&pid=Api&P=0&h=180" alt="Employee Image">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Left Column (Time Out Form) -->
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Time Out</h5>
                            </div>
                            <div class="card-body">
                                <form id="quickForm" action="" method="POST">
                                    <div class="form-group">
                                        <label for="log_date">Date Today</label>
                                        <h3 class="text-center" id="currentTime"><?php echo date('l, F j, Y'); ?></h3>
                                        <input type="hidden" class="form-control" name="log_date" value="<?php echo date('Y-m-d'); ?>" required readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="user_id">Employee:</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_name); ?>" disabled />
                                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>" />
                                    </div>

                                    <div class="form-group">
                                        <label for="log_out">Time Out:</label>
                                        <input class="form-control" type="time" name="log_out" id="log_out" required readonly>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">TIME OUT</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>


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
    <!-- Select2 -->
    <script src="assets/plugins/select2/js/select2.full.min.js"></script>
    <!-- jquery-validation -->
    <script src="assets/plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="assets/plugins/jquery-validation/additional-methods.min.js"></script>
    <!-- DataTables -->
    <script src="assets/plugins/datatables/jquery.dataTables.js"></script>
    <script src="assets/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
    <!-- AdminLTE App -->
    <script src="assets/dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="assets/dist/js/demo.js"></script>
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

            let formattedTime = month + ' ' + day + ', ' + year + ' - ' + hours + ':' + minutes + ':' + seconds + ' ' + ampm;
            document.getElementById("currentTime").innerText = formattedTime;
            const hours24 = String(now.getHours()).padStart(2, '0');
            const minutes24 = String(now.getMinutes()).padStart(2, '0');

            document.getElementById('log_out').value = hours24 + ':' + minutes24;
        }

        window.onload = updateTime;

        setInterval(updateTime, 1000);
    </script>

    <!-- page script -->
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