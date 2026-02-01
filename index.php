<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// If user is already logged in, redirect to respective dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('donor/dashboard.php');
    }
} else {
    // If not logged in, always go to login
    redirect('auth/login.php');
}
?>