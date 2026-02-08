<div class="sidebar">
    <?php
    $pending_request_count = 0;
    if (isAdmin() && isset($pdo)) {
        $pending_request_count = (int) $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn();
    }
    ?>
    <div class="brand-logo">
        <i class="fas fa-heartbeat"></i> BBMS
    </div>

    <div class="nav-label">Main Menu</div>
    <ul class="nav flex-column mb-4">
        <?php if (isAdmin()): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php' && (!isset($_GET['page']) || $_GET['page'] == 'dashboard')) ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>admin/dashboard.php">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'users') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>admin/dashboard.php?page=users">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'donations') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>admin/dashboard.php?page=donations">
                    <i class="fas fa-hand-holding-heart"></i> Donations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'requests') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>admin/dashboard.php?page=requests">
                    <span><i class="fas fa-procedures"></i> Requests</span>
                    <?php if ($pending_request_count > 0): ?>
                        <span class="menu-badge"><?php echo $pending_request_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <div class="nav-label mt-3">Inventory</div>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'blood_stock') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>admin/dashboard.php?page=blood_stock">
                    <i class="fas fa-warehouse"></i> Blood Stock
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'blood_groups') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>admin/dashboard.php?page=blood_groups">
                    <i class="fas fa-tint"></i> Blood Groups
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'reports') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>admin/dashboard.php?page=reports">
                    <i class="fas fa-chart-line"></i> Reports
                </a>
            </li>

            <div class="nav-label mt-3">Account</div>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'profile') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>admin/dashboard.php?page=profile">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
            </li>
        <?php else: ?>
            <!-- Donor Sidebar -->
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>donor/dashboard.php">
                    <i class="fas fa-home"></i> My Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'donate_create.php') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>donor/donate_create.php">
                    <i class="fas fa-plus-circle"></i> Donate Now
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'request_create.php') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>donor/request_create.php">
                    <i class="fas fa-ambulance"></i> Request Blood
                </a>
            </li>

            <div class="nav-label mt-3">History</div>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'donations.php') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>donor/donations.php">
                    <i class="fas fa-history"></i> Donations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'requests.php') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>donor/requests.php">
                    <i class="fas fa-file-medical-alt"></i> Requests
                </a>
            </li>
            <div class="nav-label mt-3">Account</div>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>donor/profile.php">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <div class="nav-label">System</div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>auth/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>
