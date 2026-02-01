<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
if (isAdmin())
    redirect('admin/dashboard.php');

$uid = $_SESSION['user_id'];
$user_name = $_SESSION['first_name'] ?? 'Friend';

// Stats
$stats = [];
$stats['donations_approved'] = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE donor_id = ? AND status='approved'");
$stats['donations_approved']->execute([$uid]);
$stats['donations_approved'] = $stats['donations_approved']->fetchColumn();

$stats['donations_pending'] = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE donor_id = ? AND status='pending'");
$stats['donations_pending']->execute([$uid]);
$stats['donations_pending'] = $stats['donations_pending']->fetchColumn();

$stats['requests_total'] = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE requester_id = ?");
$stats['requests_total']->execute([$uid]);
$stats['requests_total'] = $stats['requests_total']->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard - BBMS</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/client_style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>

<body>

    <!-- Top Navbar -->
    <?php require_once 'layout/navbar.php'; ?>

    <!-- Hero Section -->
    <header class="hero-section text-center">
        <div class="container">
            <h1 class="hero-title display-4">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p class="hero-subtitle mx-auto mb-4">Every drop counts. Thank you for being a part of our life-saving
                mission.</p>
        </div>
    </header>

    <!-- Stats & Quick Actions -->
    <div class="container stats-container">
        <!-- Stats Row -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-box">
                    <div class="stat-icon-circle bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <div class="stat-val text-success"><?php echo $stats['donations_approved']; ?></div>
                        <div class="stat-label">Lives Impacted</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <div class="stat-icon-circle bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <div>
                        <div class="stat-val text-warning"><?php echo $stats['donations_pending']; ?></div>
                        <div class="stat-label">Pending Donations</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <div class="stat-icon-circle bg-info bg-opacity-10 text-info p-3 rounded-circle">
                        <i class="fas fa-hand-holding-medical fa-2x"></i>
                    </div>
                    <div>
                        <div class="stat-val text-info"><?php echo $stats['requests_total']; ?></div>
                        <div class="stat-label">My Requests</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Actions -->
        <div class="row g-4">
            <div class="col-md-6 mb-4">
                <a href="donate_create.php"
                    class="action-card primary d-flex flex-column align-items-center text-center p-5">
                    <div class="action-card-icon red mb-4">
                        <i class="fas fa-tint"></i>
                    </div>
                    <h3 class="action-card-title display-6 mb-3">Donate Blood</h3>
                    <p class="action-card-desc fs-5">Schedule a new donation and help save a life today.</p>
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="request_create.php" class="action-card d-flex flex-column align-items-center text-center p-5">
                    <div class="action-card-icon blue mb-4">
                        <i class="fas fa-ambulance"></i>
                    </div>
                    <h3 class="action-card-title display-6 mb-3">Request Blood</h3>
                    <p class="action-card-desc fs-5">Need blood for a medical emergency? Request it here.</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center text-muted py-4">
        <div class="container">
            <small>&copy; <?php echo date('Y'); ?> BBMS. All rights reserved.</small>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>