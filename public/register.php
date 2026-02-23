<?php
// Register page: user creates a new account
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect logged-in users to their dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin_users.php' : 'profile.php'));
    exit;
}

require_once '../components/pdo.php';

$errors = [];
$data   = ['username'=>'','email'=>'','firstname'=>'','lastname'=>'','gender'=>'','nationality'=>'','contact_number'=>'','role'=>'user'];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username'       => trim($_POST['username'] ?? ''),
        'email'          => trim($_POST['email'] ?? ''),
        'firstname'      => trim($_POST['firstname'] ?? ''),
        'lastname'       => trim($_POST['lastname'] ?? ''),
        'gender'         => in_array($_POST['gender'] ?? '', ['male','female','other']) ? $_POST['gender'] : '',
        'nationality'    => trim($_POST['nationality'] ?? ''),
        'contact_number' => trim($_POST['contact_number'] ?? ''),
        'password'       => $_POST['password'] ?? '',
        'confirm'        => $_POST['confirm'] ?? '',
        'role'           => in_array($_POST['role'] ?? '', ['admin','user']) ? $_POST['role'] : 'user',
    ];

    // Validate input
    if ($data['username'] === '')                                  $errors[] = 'Username is required.';
    elseif (strlen($data['username']) < 3)                        $errors[] = 'Username must be at least 3 characters.';
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username']))  $errors[] = 'Username can only have letters, numbers, underscores.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))       $errors[] = 'Please enter a valid email address.';
    if ($data['firstname'] === '') $errors[] = 'First name is required.';
    if ($data['lastname'] === '')  $errors[] = 'Last name is required.';
    if (strlen($data['password']) < 6)                            $errors[] = 'Password must be at least 6 characters.';
    if ($data['password'] !== $data['confirm'])                   $errors[] = 'Passwords do not match.';

    // Check for duplicate username/email
    if (empty($errors)) {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$data['username'], $data['email']]);
        if ($stmt->fetch()) $errors[] = 'Username or email is already taken.';
    }

    // Insert new user if no errors
    if (empty($errors)) {
        $db     = getDB();
        $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt   = $db->prepare('INSERT INTO users (username, email, password_hash, role, firstname, lastname, gender, nationality, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['username'], $data['email'], $hashed, $data['role'],
            $data['firstname'], $data['lastname'],
            $data['gender'] ?: null,
            $data['nationality'] ?: null,
            $data['contact_number'] ?: null,
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => "Account created successfully! Welcome, {$data['firstname']}. Please log in."];
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Macotin UMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: Arial, sans-serif; }
        .register-card {
            max-width: 580px;
            margin: 40px auto;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .register-header {
            background-color: #343a40;
            color: white;
            padding: 22px 30px;
        }
        .register-header h4 { margin: 0; font-weight: bold; }
        .register-header p  { margin: 4px 0 0; font-size: 13px; opacity: 0.65; }
        .register-body { background: white; padding: 30px; }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 6px;
            margin-bottom: 16px;
        }
        .role-card {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 14px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }
        .role-card:hover { border-color: #adb5bd; background: #f8f9fa; }
        .role-card.selected { border-color: #0d6efd; background: #e7f1ff; }
        .role-card i { font-size: 28px; display: block; margin-bottom: 6px; }
        .role-card span { font-size: 13px; font-weight: bold; display: block; }
        .role-card small { color: #6c757d; font-size: 11px; }
    </style>
</head>
<body>
<div class="container">
    <div class="register-card">
        <div class="register-header">
            <h4><i class="bi bi-person-plus-fill me-2"></i>Create an Account</h4>
            <p>Macotin User Management System</p>
        </div>
        <div class="register-body">

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-1">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" id="registerForm">

                <!-- Role -->
                <div class="section-title">Register As</div>
                <div class="row mb-4 g-3">
                    <div class="col-6">
                        <div class="role-card <?= $data['role'] === 'user' ? 'selected' : '' ?>" onclick="selectRole('user')">
                            <i class="bi bi-person-fill text-primary"></i>
                            <span>User</span>
                            <small>Manage own account only</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="role-card <?= $data['role'] === 'admin' ? 'selected' : '' ?>" onclick="selectRole('admin')">
                            <i class="bi bi-shield-fill-check text-success"></i>
                            <span>Admin</span>
                            <small>Manage all users</small>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($data['role']) ?>">

                <!-- Personal Info -->
                <div class="section-title">Personal Information</div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="firstname" class="form-control"
                               value="<?= htmlspecialchars($data['firstname']) ?>" placeholder="e.g. Anna" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="lastname" class="form-control"
                               value="<?= htmlspecialchars($data['lastname']) ?>" placeholder="e.g. Ramos" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="male"   <?= $data['gender']==='male'   ? 'selected':'' ?>>Male</option>
                            <option value="female" <?= $data['gender']==='female' ? 'selected':'' ?>>Female</option>
                            <option value="other"  <?= $data['gender']==='other'  ? 'selected':'' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nationality</label>
                        <input type="text" name="nationality" class="form-control"
                               value="<?= htmlspecialchars($data['nationality']) ?>" placeholder="e.g. Filipino">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control"
                               value="<?= htmlspecialchars($data['contact_number']) ?>" placeholder="e.g. 09xxxxxxxxx">
                    </div>
                </div>

                <!-- Account Details -->
                <div class="section-title">Account Details</div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-at"></i></span>
                            <input type="text" name="username" class="form-control"
                                   value="<?= htmlspecialchars($data['username']) ?>" placeholder="e.g. aramos" required>
                        </div>
                        <div class="form-text">Letters, numbers, underscores only.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($data['email']) ?>" placeholder="anna@example.com" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" id="pass1" placeholder="Minimum 6 characters" required>
                        <div class="form-text">At least 6 characters.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="confirm" class="form-control" id="pass2" placeholder="Repeat your password" required>
                        <div id="passMsg" class="form-text"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <a href="login.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Login
                    </a>
                    <button type="submit" class="btn btn-dark px-4">
                        <i class="bi bi-person-check-fill me-2"></i>Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function selectRole(role) {
        document.getElementById('roleInput').value = role;
        document.querySelectorAll('.role-card').forEach(function(card) {
            card.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
    }

    // live password match check
    document.getElementById('pass2').addEventListener('input', function() {
        var p1  = document.getElementById('pass1').value;
        var msg = document.getElementById('passMsg');
        if (this.value === '') {
            msg.textContent = '';
        } else if (this.value === p1) {
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
