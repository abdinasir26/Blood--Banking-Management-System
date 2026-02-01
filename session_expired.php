<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired - BBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="auth-container">
        <div class="text-center">
            <div class="display-1 text-primary-custom mb-3">
                <i class="fas fa-clock"></i>
            </div>
            <h2 class="fw-bold mb-3">Session Expired</h2>
            <p class="text-muted mb-4">You have been logged out due to inactivity for more than 5 minutes.<br>Please
                login again to continue.</p>
            <a href="auth/login.php" class="btn btn-primary btn-lg px-5">Login</a>
        </div>
    </div>
</body>

</html>