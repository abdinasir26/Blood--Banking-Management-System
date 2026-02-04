<?php
session_start();

/**
 * Redirect to a specific URL
 */
function redirect($url)
{
    header("Location: " . BASE_URL . $url);
    exit;
}

/**
 * Set a flash message
 */
function setFlash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Require login (Redirect if not logged in)
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        setFlash('danger', 'Please login to access this page.');
        redirect('auth/login.php');
    }
}

/**
 * Check if user is admin
 */
function isAdmin()
{
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Require admin access
 */
function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        setFlash('danger', 'Access denied. Admin only.');
        redirect('donor/dashboard.php'); // Redirect to donor dashboard or home
    }
}

/**
 * Check session inactivity (Auto-logout after 20 minutes)
 */
function checkSessionTimeout()
{
    $timeout_duration = 1200; // 20 minutes in seconds

    if (isset($_SESSION['last_activity'])) {
        $duration = time() - $_SESSION['last_activity'];
        if ($duration > $timeout_duration) {
            session_unset();
            session_destroy();
            redirect('session_expired.php');
        }
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Sanitize input
 */
function cleanInput($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Run timeout check on every page load if logged in
if (isLoggedIn()) {
    checkSessionTimeout();
}
?>
