<?php
// Change Password page: user updates their password
require_once '../components/auth.php';
require_once '../components/pdo.php';
require_once '../components/layout.php';

requireLogin();

$db      = getDB();
$uid     = currentUserId();
$errors  = [];
$success = false;

// Fetch current user
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch();
if (!$user) { session_destroy(); header('Location: login.php'); exit; }

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validate input
    if ($current === '')         $errors[] = 'Current password is required.';
    if (strlen($new) < 6)       $errors[] = 'New password must be at least 6 characters.';
    if ($new !== $confirm)       $errors[] = 'New passwords do not match.';

    // Verify current password
    if (empty($errors) && !password_verify($current, $user['password_hash'])) {
        $errors[] = 'Current password is incorrect.';
    }

    // Update password if no errors
    if (empty($errors)) {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt   = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hashed, $uid]);
        $success = true;
    }
}

renderHead('Change Password');
renderTopBar($_SESSION['username'], $_SESSION['role']);
?>
<div class="d-flex">
<?php renderSidebar('change_password.php', $_SESSION['role']); ?>
<div class="main-content w-100">

    <div class="page-title"><i class="bi bi-lock-fill me-2"></i>Change Password</div>
    <div class="page-subtitle">Update your account password. You'll need your current password.</div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Error:</strong>
            <ul class="mb-0 mt-1"><?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>Password updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 480px;">
        <div class="card-header" style="background:#343a40;color:white;">
            <i class="bi bi-shield-lock me-2"></i>Update Password
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
                    <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required autocomplete="current-password">
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                    <input type="password" name="new_password" class="form-control" id="newPass" placeholder="Minimum 6 characters" required autocomplete="new-password">
                    <div class="form-text">At least 6 characters.</div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" id="confirmPass" placeholder="Repeat new password" required autocomplete="new-password">
                    <div id="matchMsg" class="form-text"></div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Update Password</button>
                    <a href="profile.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Profile</a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<?php renderScripts(); ?>
<script>
    document.getElementById('confirmPass').addEventListener('input', function() {
        var msg = document.getElementById('matchMsg');
        if (this.value === document.getElementById('newPass').value) {
            msg.textContent = '✓ Passwords match';
            msg.style.color = 'green';
        } else {
            msg.textContent = '✗ Passwords do not match';
            msg.style.color = 'red';
        }
    });
</script>
</body>
</html>
