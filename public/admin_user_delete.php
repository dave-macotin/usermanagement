
require_once '../components/auth.php';
require_once '../components/pdo.php';

requireAdmin();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

// Prevent deleting self
if ($id <= 0) {
    redirectWith('admin_users.php', 'danger', 'Invalid user ID.');
}
if ($id === currentUserId()) {
    redirectWith('admin_users.php', 'danger', 'You cannot delete your own account.');
}

// Check if user exists
$stmt = $db->prepare('SELECT username FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    redirectWith('admin_users.php', 'danger', 'User not found.');
}

// Delete user
$stmt = $db->prepare('DELETE FROM users WHERE id = ?');
$stmt->execute([$id]);

redirectWith('admin_users.php', 'success', "User '{$user['username']}' deleted successfully.");

