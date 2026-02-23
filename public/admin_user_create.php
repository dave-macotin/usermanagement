<?php
// Add User page: admin creates a new user account
require_once '../components/auth.php';
require_once '../components/pdo.php';
require_once '../components/layout.php';

requireAdmin();

$db     = getDB();
$errors = [];
$data   = ['username'=>'','email'=>'','role'=>'user','firstname'=>'','lastname'=>'','gender'=>'','nationality'=>'','contact_number'=>''];

// Handle form submission for new user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username'       => trim($_POST['username'] ?? ''),
        'email'          => trim($_POST['email'] ?? ''),
        'password'       => $_POST['password'] ?? '',
        'confirm'        => $_POST['confirm'] ?? '',
        'role'           => in_array($_POST['role']??'',['admin','user']) ? $_POST['role'] : 'user',
        'firstname'      => trim($_POST['firstname'] ?? ''),
        'lastname'       => trim($_POST['lastname'] ?? ''),
        'gender'         => in_array($_POST['gender']??'',['male','female','other']) ? $_POST['gender'] : '',
        'nationality'    => trim($_POST['nationality'] ?? ''),
        'contact_number' => trim($_POST['contact_number'] ?? ''),
    ];

    // Validate input
    if ($data['username'] === '') $errors[] = 'Username is required.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($data['firstname'] === '') $errors[] = 'First name is required.';
    if ($data['lastname'] === '')  $errors[] = 'Last name is required.';
    if (strlen($data['password']) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($data['password'] !== $data['confirm']) $errors[] = 'Passwords do not match.';

    // Check for duplicate username/email
    if (empty($errors)) {
        $stmt = $db->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$data['username'], $data['email']]);
        if ($stmt->fetch()) $errors[] = 'Username or email already exists.';
    }

    // Insert new user if no errors
    if (empty($errors)) {
        $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt   = $db->prepare('INSERT INTO users (username,email,password_hash,role,firstname,lastname,gender,nationality,contact_number) VALUES (?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$data['username'],$data['email'],$hashed,$data['role'],$data['firstname'],$data['lastname'],$data['gender']?:null,$data['nationality']?:null,$data['contact_number']?:null]);
        redirectWith('admin_users.php','success',"User '{$data['username']}' created successfully!");
    }
}

renderHead('Add User');
renderTopBar($_SESSION['username'], $_SESSION['role']);
?>
<div class="d-flex">
<?php renderSidebar('admin_user_create.php', $_SESSION['role']); ?>
<div class="main-content w-100">

    <div class="page-title"><i class="bi bi-person-plus-fill me-2"></i>Add New User</div>
    <div class="page-subtitle">Create a new user account in the system.</div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Please fix the errors below:</strong>
            <ul class="mb-0 mt-1"><?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header" style="background:#343a40; color:white;">
            <i class="bi bi-person-fill me-2"></i>User Details
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
                        <select name="role" class="form-select">
                            <option value="user"  <?= $data['role']==='user'  ?'selected':'' ?>>User</option>
                            <option value="admin" <?= $data['role']==='admin' ?'selected':'' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="confirm" class="form-control" required>
                    </div>
                </div>

                <p class="fw-bold text-uppercase text-muted mt-4" style="font-size:11px; letter-spacing:1px; border-bottom:1px solid #dee2e6; padding-bottom:6px;">Personal Information</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="firstname" class="form-control" value="<?= e($data['firstname']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="lastname" class="form-control" value="<?= e($data['lastname']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="male"   <?= $data['gender']==='male'   ?'selected':'' ?>>Male</option>
                            <option value="female" <?= $data['gender']==='female' ?'selected':'' ?>>Female</option>
                            <option value="other"  <?= $data['gender']==='other'  ?'selected':'' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nationality</label>
                        <input type="text" name="nationality" class="form-control" value="<?= e($data['nationality']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="<?= e($data['contact_number']) ?>">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Create User</button>
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
