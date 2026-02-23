<?php
// Authentication and authorization helpers

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require the user to be logged in.
 * Redirects to login.php if not authenticated.
 */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ../public/login.php');
        exit;
    }
}

/**
 * Require the user to be an admin.
 * Redirects to profile.php with an error if not admin.
 */
function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: profile.php?error=access_denied');
        exit;
    }
}

/**
 * Check if the currently logged-in user is an admin.
 */
function isAdmin(): bool {
    return !empty($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get the currently logged-in user's ID.
 */
function currentUserId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

/**
 * Sanitize output for HTML display.
 */
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect with a flash message stored in session.
 */
function redirectWith(string $url, string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    header("Location: $url");
    exit;
}

/**
 * Get and clear the flash message from session.
 */
function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
