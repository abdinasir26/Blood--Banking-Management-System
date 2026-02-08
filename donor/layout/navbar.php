<nav class="navbar navbar-expand-lg client-navbar">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-heartbeat"></i> BBMS
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#clientNav"
            aria-controls="clientNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="clientNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>"
                        href="dashboard.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'donate_create.php') ? 'active' : ''; ?>"
                        href="donate_create.php">Donate Blood</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'request_create.php') ? 'active' : ''; ?>"
                        href="request_create.php">Request Blood</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['donations.php', 'requests.php'])) ? 'active' : ''; ?>"
                        href="#" id="historyDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        History
                    </a>
                    <ul class="dropdown-menu border-0 shadow-lg p-2 rounded-3" aria-labelledby="historyDropdown">
                        <li><a class="dropdown-item rounded-2 py-2" href="donations.php">My Donations</a></li>
                        <li><a class="dropdown-item rounded-2 py-2" href="requests.php">My Requests</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>"
                        href="profile.php">Profile</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <?php if (!empty($_SESSION['profile_picture'])): ?>
                    <img src="<?php echo '../' . htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profile picture"
                        class="rounded-circle d-none d-lg-inline"
                        style="width: 34px; height: 34px; object-fit: cover; border: 2px solid rgba(0,0,0,0.06);">
                <?php endif; ?>
                <span class="d-none d-lg-block fw-bold text-muted small me-2">
                    Hello,
                    <?php echo isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name']) : 'Friend'; ?>
                </span>
                <a href="../auth/logout.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<?php
// Show flash messages on donor pages (success/danger/warning/info).
if (function_exists('getFlash')) {
    $flash = getFlash();
    if ($flash):
        ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?> alert-dismissible fade show" role="alert">
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php
    endif;
}
?>
