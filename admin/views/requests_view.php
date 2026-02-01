<?php
// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action']; // 'approve' or 'reject'

    // Correct fetch
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $req = $stmt->fetch();

    if ($req && $req['status'] == 'pending') {
                if ($action == 'approve') {
            // Check stock
            $stockStmt = $pdo->prepare("SELECT * FROM blood_store WHERE blood_group_id = ?");
            $stockStmt->execute([$req['blood_group_id']]);
            $stock = $stockStmt->fetch();

            if ($stock && $stock['available_units'] >= $req['amount']) {
                $pdo->beginTransaction();
                try {
                    // Deduct Stock
                    $new_units = $stock['available_units'] - $req['amount'];
                    $pdo->prepare("UPDATE blood_store SET available_units = ? WHERE id = ?")->execute([$new_units, $stock['id']]);

                    // Approve Request (record approver and timestamp)
                    $approver = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                    $pdo->prepare("UPDATE requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?")->execute([$approver, $request_id]);

                    // Log activity
                    if ($approver) {
                        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'approve_request', ?)");
                        $logDetails = 'Approved request ID: ' . $request_id . ' for hospital: ' . $req['hospital_name'];
                        $logStmt->execute([$approver, $logDetails]);
                    }

                    $pdo->commit();
                    setFlash('success', 'Request approved. Stock deducted.');
                } catch (Exception $e) {
                    $pdo->rollBack();
                    setFlash('danger', 'Error processing request.');
                }
            } else {
                setFlash('danger', 'Insufficient stock to approve this request.');
            }
        } elseif ($action == 'reject') {
            $pdo->prepare("UPDATE requests SET status = 'rejected' WHERE id = ?")->execute([$request_id]);
            setFlash('warning', 'Request rejected.');
        }
    }
    // Redirect
    echo "<script>window.location.href='dashboard.php?page=requests';</script>";
    exit;
}

$pending = $pdo->query("SELECT r.*, u.first_name, u.last_name, bg.group_name FROM requests r JOIN users u ON r.requester_id = u.id JOIN blood_groups bg ON r.blood_group_id = bg.id WHERE r.status = 'pending' ORDER BY r.urgency DESC, r.created_at ASC")->fetchAll();

$history = $pdo->query("SELECT r.*, u.first_name, u.last_name, bg.group_name FROM requests r JOIN users u ON r.requester_id = u.id JOIN blood_groups bg ON r.blood_group_id = bg.id WHERE r.status != 'pending' ORDER BY r.created_at DESC LIMIT 50")->fetchAll();
?>

<h4 class="fw-bold text-dark mb-4">Manage Requests</h4>

<!-- Pending -->
<div class="card card-custom mb-4 border-start border-4 border-danger">
    <div class="card-header card-header-custom bg-danger bg-opacity-10 text-danger">
        <i class="fas fa-exclamation-circle me-2"></i> Pending Requests
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Patient/Hospital</th>
                        <th>Requester</th>
                        <th>Group</th>
                        <th>Units</th>
                        <th>Urgency</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pending) > 0): ?>
                        <?php foreach ($pending as $r): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    <?php echo htmlspecialchars($r['hospital_name']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?>
                                </td>
                                <td><span class="badge bg-danger bg-opacity-10 text-danger">
                                        <?php echo htmlspecialchars($r['group_name']); ?>
                                    </span></td>
                                <td class="fw-bold">
                                    <?php echo $r['amount']; ?>
                                </td>
                                <td>
                                    <span
                                        class="badge <?php echo ($r['urgency'] == 'urgent') ? 'bg-danger' : 'bg-primary bg-opacity-10 text-primary'; ?>">
                                        <?php echo ucfirst($r['urgency']); ?>
                                    </span>
                                </td>
                                <td class="text-muted">
                                    <?php echo date('M d, Y', strtotime($r['created_at'])); ?>
                                </td>
                                <td class="text-end pe-4">
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" name="request_action" class="btn btn-sm btn-success me-1">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" name="request_action" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Reject this request?');">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-3 text-muted">No pending requests.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- History -->
<div class="card card-custom">
    <div class="card-header card-header-custom border-0">
        <i class="fas fa-history me-2"></i> Request History
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Hospital</th>
                        <th>Group</th>
                        <th>Units</th>
                        <th>Urgency</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark">
                                <?php echo htmlspecialchars($h['hospital_name']); ?>
                            </td>
                            <td><span class="badge bg-danger bg-opacity-10 text-danger">
                                    <?php echo htmlspecialchars($h['group_name']); ?>
                                </span></td>
                            <td class="fw-bold">
                                <?php echo $h['amount']; ?>
                            </td>
                            <td>
                                <span
                                    class="badge <?php echo ($h['urgency'] == 'urgent') ? 'bg-danger' : 'bg-primary bg-opacity-10 text-primary'; ?>">
                                    <?php echo ucfirst($h['urgency']); ?>
                                </span>
                            </td>
                            <td>
                                <span
                                    class="badge <?php echo ($h['status'] == 'approved') ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger'; ?>">
                                    <?php echo ucfirst($h['status']); ?>
                                </span>
                            </td>
                            <td class="text-muted">
                                <?php echo date('M d, Y', strtotime($h['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>