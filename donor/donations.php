<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
$uid = $_SESSION['user_id'];
$donations = $pdo->prepare("SELECT d.*, bg.group_name FROM donations d JOIN blood_groups bg ON d.blood_group_id = bg.id WHERE d.donor_id = ? ORDER BY d.created_at DESC");
$donations->execute([$uid]);
$donations = $donations->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Donations - BBMS</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/client_style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        .table-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
    </style>
</head>

<body>

    <?php require_once 'layout/navbar.php'; ?>

    <div class="container py-5">
        <div class="page-header">
            <h2 class="page-title">My Donation History</h2>
            <p class="text-muted">Track the status of your blood donations.</p>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">ID</th>
                            <th>Blood Group</th>
                            <th>Units</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($donations) > 0): ?>
                            <?php foreach ($donations as $d): ?>
                                <tr>
                                    <td class="ps-4 text-muted">#<?php echo $d['id']; ?></td>
                                    <td><span
                                            class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3"><?php echo htmlspecialchars($d['group_name']); ?></span>
                                    </td>
                                    <td class="fw-bold"><?php echo $d['amount']; ?></td>
                                    <td>
                                        <?php if ($d['status'] == 'approved'): ?>
                                            <span class="badge bg-success rounded-pill px-3">Approved</span>
                                        <?php elseif ($d['status'] == 'rejected'): ?>
                                            <span class="badge bg-danger rounded-pill px-3">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark rounded-pill px-3">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted"><?php echo date('M d, Y', strtotime($d['created_at'])); ?></td>
                                    <td class="text-end pe-4">
                                        <?php if ($d['status'] == 'approved'): ?>
                                            <a href="donation_certificate.php?id=<?php echo (int) $d['id']; ?>"
                                                class="btn btn-sm btn-outline-primary" target="_blank">
                                                Download
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Available after approval</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">You haven't made any donations yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
