<?php
// Profile page: user views and updates their personal information
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

$errorParam = $_GET['error'] ?? '';

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname      = trim($_POST['firstname'] ?? '');
    $lastname       = trim($_POST['lastname'] ?? '');
    $gender         = in_array($_POST['gender']??'',['male','female','other','']) ? ($_POST['gender']?:null) : null;
    $nationality    = trim($_POST['nationality'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    // Validate input
    if ($firstname === '') $errors[] = 'First name is required.';
    if ($lastname === '')  $errors[] = 'Last name is required.';

    // Update user profile if no errors
    if (empty($errors)) {
        $stmt = $db->prepare('UPDATE users SET firstname=?,lastname=?,gender=?,nationality=?,contact_number=? WHERE id=?');
        $stmt->execute([$firstname,$lastname,$gender,$nationality?:null,$contact_number?:null,$uid]);
        $_SESSION['firstname'] = $firstname;
        $_SESSION['lastname']  = $lastname;

        // Refresh user data
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$uid]);
        $user = $stmt->fetch();
        $success = true;
    }
}

renderHead('My Profile');
renderTopBar($_SESSION['username'], $_SESSION['role']);
?>
<div class="d-flex">
<?php renderSidebar('profile.php', $_SESSION['role']); ?>
<div class="main-content w-100">

    <div class="page-title"><i class="bi bi-person-circle me-2"></i>My Profile</div>
    <div class="page-subtitle">View and update your personal information.</div>

    <?php if ($errorParam === 'access_denied'): ?>
        <div class="alert alert-danger"><i class="bi bi-shield-exclamation me-2"></i>Access denied. You do not have permission to view that page.</div>
    <?php endif; ?>
    <?php renderFlash(); ?>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>Profile updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul></div>
    <?php endif; ?>

    <!-- Profile Card -->
    <div class="card mb-3">
        <div class="card-body d-flex align-items-center gap-3" style="padding: 20px;">
            <div style="width:60px;height:60px;border-radius:50%;background:#343a40;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:bold;color:white;flex-shrink:0;">
                <?= strtoupper(substr($user['firstname'] ?? $user['username'], 0, 1)) ?>
            </div>
            <div>
                <div style="font-size:18px;font-weight:bold;"><?= e(trim($user['firstname'].' '.$user['lastname'])) ?></div>
                <div style="font-size:13px;color:#6c757d;">
                    @<?= e($user['username']) ?> &nbsp;·&nbsp; <?= e($user['email']) ?> &nbsp;·&nbsp;
                    <span class="badge badge-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Read-only Info -->
    <div class="card mb-3">
        <div class="card-header" style="background:#343a40;color:white;">
            <i class="bi bi-info-circle me-2"></i>Account Information <small class="opacity-50">(read-only)</small>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" class="form-control" value="<?= e($user['username']) ?>" readonly style="background:#f8f9fa;">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="text" class="form-control" value="<?= e($user['email']) ?>" readonly style="background:#f8f9fa;">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Role</label>
                    <input type="text" class="form-control" value="<?= ucfirst($user['role']) ?>" readonly style="background:#f8f9fa;">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Member Since</label>
                    <input type="text" class="form-control" value="<?= date('M d, Y', strtotime($user['created_at'])) ?>" readonly style="background:#f8f9fa;">
                </div>
            </div>
        </div>
    </div>

    <!-- Editable Info -->
    <div class="card mb-3">
        <div class="card-header" style="background:#343a40;color:white;">
            <i class="bi bi-pencil-fill me-2"></i>Edit My Details
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="firstname" class="form-control" value="<?= e($user['firstname']??'') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="lastname" class="form-control" value="<?= e($user['lastname']??'') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="male"   <?= ($user['gender']??'')==='male'   ?'selected':'' ?>>Male</option>
                            <option value="female" <?= ($user['gender']??'')==='female' ?'selected':'' ?>>Female</option>
                            <option value="other"  <?= ($user['gender']??'')==='other'  ?'selected':'' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nationality</label>
                        <input type="text" name="nationality" class="form-control" value="<?= e($user['nationality']??'') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="<?= e($user['contact_number']??'') ?>">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="text-end">
        <a href="change_password.php" class="btn btn-outline-dark">
            <i class="bi bi-lock me-1"></i>Change Password
        </a>
    </div>
</div>
</div>
<?php renderScripts(); ?>
</body>
</html>
