
if (session_status() === PHP_SESSION_NONE) session_start();
// Log out user and destroy session
session_unset();
session_destroy();

header('Location: login.php');
exit;

