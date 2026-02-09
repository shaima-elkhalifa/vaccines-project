<?php
session_start();
include '../db_connection.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, role, password FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Verify password
            if (password_verify($password, $user['password']) || $user['password'] === md5($password) || $user['password'] === $password) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error_message = "Invalid password.";
            }
        } else {
            $error_message = "No admin user found with that email.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f4f0ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 15px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            border: none;
        }
        .login-card .card-header {
            background: linear-gradient(135deg, #5e2ca5, #a47cf3);
            color: #fff;
            text-align: center;
            font-weight: 600;
            padding: 1.5rem;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .btn-purple {
            background-color: #6f3cc3;
            color: #fff;
            font-weight: 500;
        }
        .btn-purple:hover {
            background-color: #5e2ca5;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-header">
            <h4>Admin Panel Login</h4>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form action="login.php" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-purple w-100">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
