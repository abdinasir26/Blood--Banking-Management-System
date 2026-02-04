<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

// --- Data Fetching for Dashboard (Only if page is dashboard or empty) ---
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Initialize variables to avoid undefined variable errors on home view
$total_users = 0;
$pending_donations = 0;
$pending_requests = 0;
$total_stock = 0;
$stock_labels = '[]';
$stock_values = '[]';
$donor_labels = '[]';
$donor_values = '[]';
$recent_donations = [];
$pending_donation_rows = [];
$pending_request_rows = [];
$patients_served = 0;

if ($page == 'dashboard') {
    // 1. Stats Cards
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type != 'admin'")->fetchColumn();
    $pending_donations = $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'pending'")->fetchColumn();
    $pending_requests = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn();
    $total_stock = $pdo->query("SELECT SUM(available_units) FROM blood_store")->fetchColumn() ?: 0;

    // Patients served (approved requests)
    $patients_served = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'approved'")->fetchColumn();

    // 2. Blood Stock Distribution (For Donut/Pie Chart)
    $stock_data = $pdo->query("SELECT bg.group_name, bs.available_units FROM blood_store bs JOIN blood_groups bg ON bs.blood_group_id = bg.id")->fetchAll(PDO::FETCH_KEY_PAIR);
    $stock_labels = json_encode(array_keys($stock_data));
    $stock_values = json_encode(array_values($stock_data));

    // 3. User Distribution by Blood Group (For Bar Chart)
    $donor_dist_sql = "SELECT bg.group_name, COUNT(u.id) as count FROM blood_groups bg LEFT JOIN users u ON u.blood_group_id = bg.id AND u.user_type = 'donor' GROUP BY bg.id ORDER BY bg.id";
    $donor_dist = $pdo->query($donor_dist_sql)->fetchAll(PDO::FETCH_ASSOC);
    $donor_labels = json_encode(array_column($donor_dist, 'group_name'));
    $donor_values = json_encode(array_column($donor_dist, 'count'));

    // 4. Recent Activity (Last 5 Donations)
    $recent_donations = $pdo->query("SELECT d.*, u.first_name, u.last_name, bg.group_name FROM donations d JOIN users u ON d.donor_id = u.id JOIN blood_groups bg ON d.blood_group_id = bg.id ORDER BY d.created_at DESC LIMIT 5")->fetchAll();

    // 5. Pending items for quick approval
    $pending_donation_rows = $pdo->query("SELECT d.*, u.first_name, u.last_name, bg.group_name FROM donations d JOIN users u ON d.donor_id = u.id JOIN blood_groups bg ON d.blood_group_id = bg.id WHERE d.status = 'pending' ORDER BY d.created_at ASC LIMIT 5")->fetchAll();
    $pending_request_rows = $pdo->query("SELECT r.*, u.first_name, u.last_name, bg.group_name FROM requests r JOIN users u ON r.requester_id = u.id JOIN blood_groups bg ON r.blood_group_id = bg.id WHERE r.status = 'pending' ORDER BY r.urgency DESC, r.created_at ASC LIMIT 5")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BBMS</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link href="../assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>

<body>

    <div class="dashboard-container">
        <?php require_once '../includes/sidebar.php'; ?>

        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <form class="search-bar" action="dashboard.php" method="GET">
                    <i class="fas fa-search text-muted"></i>
                    <input type="hidden" name="page" value="search">
                    <input type="text" name="q" placeholder="Search anything..."
                        value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                </form>

                <div class="user-profile">
                    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
                        <i class="fas fa-moon"></i>
                    </button>
                    <div class="icon-btn">
                        <i class="fas fa-bell"></i>
                        <span class="badge-dot"></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-btn bg-dark text-white">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-dark">
                                <?php
                                $admin_name = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
                                echo $admin_name !== '' ? htmlspecialchars($admin_name) : 'Admin User';
                                ?>
                            </div>
                            <div class="text-muted" style="font-size: 0.75rem;">Administrator</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            // Dynamic View Loading
            switch ($page) {
                case 'users':
                    require_once 'views/users_view.php';
                    break;
                case 'donations':
                    require_once 'views/donations_view.php';
                    break;
                case 'requests':
                    require_once 'views/requests_view.php';
                    break;
                case 'blood_stock':
                    require_once 'views/blood_stock_view.php';
                    break;
                case 'blood_groups':
                    require_once 'views/blood_groups_view.php';
                    break;
                case 'reports':
                    require_once 'views/reports_view.php';
                    break;
                case 'search':
                    require_once 'views/search_view.php';
                    break;
                case 'dashboard':
                default:
                    require_once 'views/home_view.php';
                    break;
            }
            ?>

        </div>
    </div>

    <script>
        (function () {
            const root = document.documentElement;
            const toggle = document.getElementById('themeToggle');
            if (!toggle) return;

            const saved = localStorage.getItem('bbms_theme');
            if (saved === 'dark' || saved === 'light') {
                root.setAttribute('data-theme', saved);
            }

            const updateIcon = () => {
                const isDark = root.getAttribute('data-theme') === 'dark';
                toggle.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            };

            updateIcon();

            toggle.addEventListener('click', () => {
                const isDark = root.getAttribute('data-theme') === 'dark';
                const next = isDark ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                localStorage.setItem('bbms_theme', next);
                updateIcon();
            });
        })();
    </script>
</body>

</html>
