<?php
require_once '../includes/config.php';
// We don't include functions.php to avoid auto-session_start if possible,
// but we need it for redirect().
// However, to be safe, let's manually start session if not started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Unset all session values
$_SESSION = array();

// 2. Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Destroy the session
session_destroy();

// 4. Delete Remember Me cookies manually
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// 5. Manual Redirect to avoid include issues
header("Location: " . BASE_URL . "auth/login.php?logged_out=1");
exit;
?>