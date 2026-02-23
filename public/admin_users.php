<?php
// Users Management page: list, search, and filter users
require_once '../components/auth.php';
require_once '../components/pdo.php';
require_once '../components/layout.php';

requireAdmin();

$db     = getDB();
$search = trim($_GET['q'] ?? '');
$filter = $_GET['role'] ?? '';

// Build query for user search and filtering
$sql    = 'SELECT * FROM users WHERE 1=1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (username LIKE ? OR email LIKE ?)';
    $like = "%$search%";
    $params[] = $like; $params[] = $like;
}
if ($filter === 'admin' || $filter === 'user') {
    $sql .= ' AND role = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY id ASC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

renderHead('Users Management');
renderTopBar($_SESSION['username'], $_SESSION['role']);
?>
<div class="d-flex">
<?php renderSidebar('admin_users.php', $_SESSION['role']); ?>
<div class="main-content w-100">

    <div class="page-title"><i class="bi bi-people-fill me-2"></i>Users Management</div>
    <div class="page-subtitle">View and manage all registered users in the system.</div>

    <?php renderFlash(); ?>

    <!-- Search and filter form -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Search username or email..." style="min-width:220px;">
                    </div>
                </div>
                <div class="col-auto">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="admin" <?= $filter==='admin'?'selected':'' ?>>Admin</option>
                        <option value="user"  <?= $filter==='user' ?'selected':'' ?>>User</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-dark"><i class="bi bi-search me-1"></i>Search</button>
                    <?php if ($search || $filter): ?>
                        <a href="admin_users.php" class="btn btn-outline-secondary ms-1"><i class="bi bi-x-circle me-1"></i>Clear</a>
                    <?php endif; ?>
                </div>
                <div class="col-auto ms-auto">
                    <a href="admin_user_create.php" class="btn btn-success">
                        <i class="bi bi-person-plus-fill me-1"></i>Add New User
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center" style="background:#343a40; color:white;">
            <span><i class="bi bi-table me-2"></i>All Users</span>
            <span class="badge bg-secondary"><?= count($users) ?> record(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Full Name</th>
                            <th>Gender</th>
                            <th>Nationality</th>
                            <th>Contact</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="10" class="text-center text-muted py-4">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="text-muted"><?= $u['id'] ?></td>
                            <td><strong><?= e($u['username']) ?></strong></td>
                            <td><?= e($u['email']) ?></td>
                            <td>
                                <span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>
                            </td>
                            <td><?= e(trim($u['firstname'].' '.$u['lastname'])) ?></td>
                            <td><?= e($u['gender'] ?? '—') ?></td>
                            <td><?= e($u['nationality'] ?? '—') ?></td>
                            <td><?= e($u['contact_number'] ?? '—') ?></td>
                            <td style="font-size:12px;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="admin_user_edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                                        <a href="admin_user_delete.php?id=<?= $u['id'] ?>"
                                           class="btn btn-sm btn-danger"
                                           title="Delete"
                                           onclick="return confirm('Delete user <?= e($u['username']) ?>? This cannot be undone.')">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Cannot delete yourself">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
<?php renderScripts(); ?>
</body>
</html>