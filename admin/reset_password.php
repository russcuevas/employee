<?php
include '../database/connection.php';
session_start();

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT admin_id FROM forgot_password WHERE token = ?");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $_SESSION['error'] = "Invalid or expired token.";
        header('location: admin_login.php');
        exit();
    }
} else {
    header('location: admin_login.php');
    exit();
}

if (isset($_POST['reset'])) {
    $new_password = $_POST['password'];
    $admin_id = $row['admin_id'];

    $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE admin_id = ?");
    $stmt->execute([$new_password, $admin_id]);

    $stmt = $conn->prepare("DELETE FROM forgot_password WHERE admin_id = ?");
    $stmt->execute([$admin_id]);

    $_SESSION['success'] = "Password successfully reset!";
    header('location: admin_login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }

        .reset-box {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            width: 100%;
            background: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="reset-box">
            <h4 class="text-center">Reset Password</h4>
            <p class="text-center text-muted">Enter your new password below.</p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password" required>
                </div>
                <button type="submit" name="reset" class="btn btn-primary">Reset Password</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>