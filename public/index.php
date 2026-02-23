<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect to dashboard if logged in, otherwise to login
if (!empty($_SESSION['user_id'])) {
    $target = ($_SESSION['role'] === 'admin') ? 'admin_users.php' : 'profile.php';
    header("Location: $target");
} else {
    header('Location: login.php');
}
exit;

