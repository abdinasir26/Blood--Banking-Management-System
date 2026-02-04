<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
$uid = $_SESSION['user_id'];
$requests = $pdo->prepare("SELECT r.*, bg.group_name FROM requests r JOIN blood_groups bg ON r.blood_group_id = bg.id WHERE r.requester_id = ? ORDER BY r.created_at DESC");
$requests->execute([$uid]);
$requests = $requests->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - BBMS</title>
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
            <h2 class="page-title">My Blood Requests</h2>
            <p class="text-muted">Status of your requests for blood.</p>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">Hospital</th>
                            <th>Group</th>
                            <th>Units</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($requests) > 0): ?>
                            <?php foreach ($requests as $r): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?php echo htmlspecialchars($r['hospital_name']); ?></td>
                                    <td><span
                                            class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3"><?php echo htmlspecialchars($r['group_name']); ?></span>
                                    </td>
                                    <td class="fw-bold"><?php echo $r['amount']; ?></td>
                                    <td>
                                        <span
                                            class="badge <?php echo ($r['urgency'] == 'urgent' || $r['urgency'] == 'critical') ? 'bg-danger' : 'bg-primary'; ?> rounded-pill px-2">
                                            <?php echo ucfirst($r['urgency']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($r['status'] == 'approved'): ?>
                                            <span class="badge bg-success rounded-pill px-3">Approved</span>
                                        <?php elseif ($r['status'] == 'rejected'): ?>
                                            <span class="badge bg-secondary rounded-pill px-3">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark rounded-pill px-3">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                                    <td class="text-end pe-4">
                                        <?php if ($r['status'] == 'approved'): ?>
                                            <a href="request_certificate.php?id=<?php echo (int) $r['id']; ?>"
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
                                <td colspan="7" class="text-center py-5 text-muted">No requests found.</td>
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
