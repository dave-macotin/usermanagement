<?php
// Login page: handles user authentication
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect logged-in users to their dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin_users.php' : 'profile.php'));
    exit;
}

require_once '../components/pdo.php';

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        // Verify user credentials
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['lastname']  = $user['lastname'];

            header('Location: ' . ($user['role'] === 'admin' ? 'admin_users.php' : 'profile.php'));
            exit;
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Macotin UMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .login-header {
            background-color: #343a40;
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 25px;
            text-align: center;
        }
        .login-header h4 {
            margin: 0;
            font-weight: bold;
        }
        .login-header p {
            margin: 5px 0 0;
            font-size: 13px;
            opacity: 0.7;
        }
        .login-body {
            padding: 30px;
            background: white;
            border-radius: 0 0 10px 10px;
        }
        .btn-login {
            background-color: #343a40;
            color: white;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 15px;
        }
        .btn-login:hover {
            background-color: #495057;
            color: white;
        }
        .demo-box {
            background-color: #e9ecef;
            border-radius: 5px;
            padding: 12px 15px;
            font-size: 13px;
            margin-top: 15px;
            color: #495057;
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-people-fill" style="font-size: 36px;"></i>
            <h4 class="mt-2">User Management System</h4>
        </div>
        <div class="login-body">
            <h5 class="mb-4" style="font-weight:bold;">Sign In</h5>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php
            // Show flash (from registration)
            if (!empty($_SESSION['flash'])) {
                $f = $_SESSION['flash']; unset($_SESSION['flash']);
                $t = $f['type'] === 'success' ? 'success' : 'danger';
                echo "<div class='alert alert-{$t}'><i class='bi bi-check-circle-fill me-2'></i>" . htmlspecialchars($f['message']) . "</div>";
            }
            ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="login" class="form-control"
                               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                               placeholder="Enter username or email" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </form>

            <div class="text-center mt-3" style="font-size:13px;">
                Don't have an account? <a href="register.php" style="color:#0d6efd;">Register here</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // show/hide password toggle
</script>
</body>
</html>