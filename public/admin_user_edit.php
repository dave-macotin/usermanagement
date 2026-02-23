<?php
// Edit User page: admin edits an existing user account
require_once '../components/auth.php';
require_once '../components/pdo.php';
require_once '../components/layout.php';

requireAdmin();

$db  = getDB();
$id  = (int)($_GET['id'] ?? 0);
$errors = [];

// Validate user ID
if ($id <= 0) redirectWith('admin_users.php','danger','Invalid user ID.');

// Fetch user data
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) redirectWith('admin_users.php','danger','User not found.');

$data = $user;

// Handle form submission for editing user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id'             => $id,
        'username'       => trim($_POST['username'] ?? ''),
        'email'          => trim($_POST['email'] ?? ''),
        'role'           => in_array($_POST['role']??'',['admin','user']) ? $_POST['role'] : 'user',
        'firstname'      => trim($_POST['firstname'] ?? ''),
        'lastname'       => trim($_POST['lastname'] ?? ''),
        'gender'         => in_array($_POST['gender']??'',['male','female','other']) ? $_POST['gender'] : null,
        'nationality'    => trim($_POST['nationality'] ?? ''),
        'contact_number' => trim($_POST['contact_number'] ?? ''),
        'new_password'   => $_POST['new_password'] ?? '',
        'confirm'        => $_POST['confirm'] ?? '',
    ];

    // Validate input
    if ($data['username'] === '') $errors[] = 'Username is required.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if ($data['firstname'] === '') $errors[] = 'First name is required.';
    if ($data['lastname'] === '')  $errors[] = 'Last name is required.';

    $changePass = $data['new_password'] !== '';
    if ($changePass) {
        if (strlen($data['new_password']) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($data['new_password'] !== $data['confirm']) $errors[] = 'Passwords do not match.';
    }

    // Check for duplicate username/email
    if (empty($errors)) {
        $stmt = $db->prepare('SELECT id FROM users WHERE (username=? OR email=?) AND id!=?');
        $stmt->execute([$data['username'],$data['email'],$id]);
        if ($stmt->fetch()) $errors[] = 'Username or email already taken by another user.';
    }

    // Update user if no errors
    if (empty($errors)) {
        if ($changePass) {
            $hashed = password_hash($data['new_password'], PASSWORD_DEFAULT);
            $stmt   = $db->prepare('UPDATE users SET username=?,email=?,password_hash=?,role=?,firstname=?,lastname=?,gender=?,nationality=?,contact_number=? WHERE id=?');
            $stmt->execute([$data['username'],$data['email'],$hashed,$data['role'],$data['firstname'],$data['lastname'],$data['gender']?:null,$data['nationality']?:null,$data['contact_number']?:null,$id]);
        } else {
            $stmt = $db->prepare('UPDATE users SET username=?,email=?,role=?,firstname=?,lastname=?,gender=?,nationality=?,contact_number=? WHERE id=?');
            $stmt->execute([$data['username'],$data['email'],$data['role'],$data['firstname'],$data['lastname'],$data['gender']?:null,$data['nationality']?:null,$data['contact_number']?:null,$id]);
        }
        redirectWith('admin_users.php','success',"User '{$data['username']}' updated successfully!");
    }
}

renderHead('Edit User');
renderTopBar($_SESSION['username'], $_SESSION['role']);
?>
<div class="d-flex">
<?php renderSidebar('admin_users.php', $_SESSION['role']); ?>
<div class="main-content w-100">

    <div class="page-title"><i class="bi bi-pencil-square me-2"></i>Edit User</div>
    <div class="page-subtitle">Editing: <strong><?= e($user['username']) ?></strong> (ID: <?= $id ?>)</div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Fix the following:</strong>
            <ul class="mb-0 mt-1"><?php foreach ($errors as $err) echo "<li>".htmlspecialchars($err)."</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header" style="background:#343a40; color:white;">
            <i class="bi bi-person-fill me-2"></i>Edit Account Details
        </div>
        <div class="card-body">
            <form method="POST">
                <p class="fw-bold text-uppercase text-muted" style="font-size:11px; letter-spacing:1px; border-bottom:1px solid #dee2e6; padding-bottom:6px;">Account Information</p>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" value="<?= e($data['username']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= e($data['email']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Role</label>
                        <?php if ($id === (int)$_SESSION['user_id']): ?>
                            <input type="text" class="form-control" value="<?= ucfirst($data['role']) ?>" readonly>
                            <input type="hidden" name="role" value="<?= e($data['role']) ?>">
                            <div class="form-text text-warning">You cannot change your own role.</div>
                        <?php else: ?>
                            <select name="role" class="form-select">
                                <option value="user"  <?= $data['role']==='user'  ?'selected':'' ?>>User</option>
                                <option value="admin" <?= $data['role']==='admin' ?'selected':'' ?>>Admin</option>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <p class="fw-bold text-uppercase text-muted mt-3" style="font-size:11px; letter-spacing:1px; border-bottom:1px solid #dee2e6; padding-bottom:6px;">Personal Information</p>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="firstname" class="form-control" value="<?= e($data['firstname']??'') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="lastname" class="form-control" value="<?= e($data['lastname']??'') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="male"   <?= ($data['gender']??'')==='male'   ?'selected':'' ?>>Male</option>
                            <option value="female" <?= ($data['gender']??'')==='female' ?'selected':'' ?>>Female</option>
                            <option value="other"  <?= ($data['gender']??'')==='other'  ?'selected':'' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nationality</label>
                        <input type="text" name="nationality" class="form-control" value="<?= e($data['nationality']??'') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="<?= e($data['contact_number']??'') ?>">
                    </div>
                </div>

                <p class="fw-bold text-uppercase text-muted mt-3" style="font-size:11px; letter-spacing:1px; border-bottom:1px solid #dee2e6; padding-bottom:6px;">
                    Reset Password <span class="text-muted fw-normal">(leave blank to keep current password)</span>
                </p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep unchanged">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" name="confirm" class="form-control">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Changes</button>
                    <a href="admin_users.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<?php renderScripts(); ?>
</body>
</html>
