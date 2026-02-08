<?php
// Queue filter
$queue_filter = isset($_GET['queue']) ? $_GET['queue'] : 'all';
if (!in_array($queue_filter, ['all', 'high'], true)) {
    $queue_filter = 'all';
}

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
                // Provide a clearer message for admins: available vs requested.
                $stockInfoStmt = $pdo->prepare("
                    SELECT
                        COALESCE(bs.available_units, 0) AS available_units,
                        bg.group_name
                    FROM blood_groups bg
                    LEFT JOIN blood_store bs ON bs.blood_group_id = bg.id
                    WHERE bg.id = ?
                    LIMIT 1
                ");
                $stockInfoStmt->execute([$req['blood_group_id']]);
                $stockInfo = $stockInfoStmt->fetch();

                $available_units = $stockInfo ? (int) $stockInfo['available_units'] : 0;
                $group_name = $stockInfo && isset($stockInfo['group_name']) ? $stockInfo['group_name'] : 'Selected group';

                setFlash(
                    'danger',
                    "Not enough units available for " . htmlspecialchars($group_name) .
                        ". Available: " . $available_units . " unit(s). Requested: " . (int) $req['amount'] . " unit(s)."
                );
            }
        } elseif ($action == 'reject') {
            $pdo->prepare("UPDATE requests SET status = 'rejected' WHERE id = ?")->execute([$request_id]);
            setFlash('warning', 'Request rejected.');
        }
    }
    // Redirect
    $redirect_filter = ($queue_filter === 'high') ? '&queue=high' : '';
    echo "<script>window.location.href='dashboard.php?page=requests" . $redirect_filter . "';</script>";
    exit;
}

$pending_sql = "
    SELECT
        r.*,
        u.first_name,
        u.last_name,
        bg.group_name,
        CASE
            WHEN r.urgency = 'critical' THEN 3
            WHEN r.urgency = 'urgent' THEN 2
            ELSE 1
        END AS priority_score
    FROM requests r
    JOIN users u ON r.requester_id = u.id
    JOIN blood_groups bg ON r.blood_group_id = bg.id
    WHERE r.status = 'pending'
";

if ($queue_filter === 'high') {
    $pending_sql .= " AND r.urgency IN ('critical', 'urgent')";
}

$pending_sql .= "
    ORDER BY priority_score DESC, r.created_at ASC
";

$pending_stmt = $pdo->query($pending_sql);
$pending = $pending_stmt->fetchAll();

$history = $pdo->query("SELECT r.*, u.first_name, u.last_name, bg.group_name FROM requests r JOIN users u ON r.requester_id = u.id JOIN blood_groups bg ON r.blood_group_id = bg.id WHERE r.status != 'pending' ORDER BY r.created_at DESC LIMIT 50")->fetchAll();
?>

<h4 class="fw-bold text-dark mb-4">Manage Requests</h4>

<div class="d-flex gap-2 flex-wrap mb-3">
    <a href="dashboard.php?page=requests"
        class="btn btn-sm <?php echo ($queue_filter === 'all') ? 'btn-danger' : 'btn-outline-danger'; ?>">
        All Pending
    </a>
    <a href="dashboard.php?page=requests&queue=high"
        class="btn btn-sm <?php echo ($queue_filter === 'high') ? 'btn-danger' : 'btn-outline-danger'; ?>">
        Critical + Urgent Only
    </a>
</div>

<!-- Pending -->
<div class="card card-custom mb-4 border-start border-4 border-danger">
    <div class="card-header card-header-custom bg-danger bg-opacity-10 text-danger">
        <i class="fas fa-exclamation-circle me-2"></i> Pending Requests (Priority Queue)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Queue</th>
                        <th class="ps-4">Patient/Hospital</th>
                        <th>Requester</th>
                        <th>Group</th>
                        <th>Units</th>
                        <th>Urgency</th>
                        <th>Priority</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pending) > 0): ?>
                        <?php foreach ($pending as $index => $r): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark">#<?php echo $index + 1; ?></td>
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
                                        class="badge <?php echo ($r['urgency'] == 'urgent' || $r['urgency'] == 'critical') ? 'bg-danger' : 'bg-primary bg-opacity-10 text-primary'; ?>">
                                        <?php echo ucfirst($r['urgency']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-dark bg-opacity-10 text-dark">
                                        P<?php echo (int) $r['priority_score']; ?>
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
                            <td colspan="9" class="text-center py-3 text-muted">No pending requests.</td>
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
                                    class="badge <?php echo ($h['urgency'] == 'urgent' || $h['urgency'] == 'critical') ? 'bg-danger' : 'bg-primary bg-opacity-10 text-primary'; ?>">
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
