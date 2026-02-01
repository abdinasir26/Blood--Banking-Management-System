<?php
// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['donation_action'])) {
    $donation_id = $_POST['donation_id'];
    $action = $_POST['action']; // 'approve' or 'reject'

    // Get donation details
    $stmt = $pdo->prepare("SELECT * FROM donations WHERE id = ?");
    $stmt->execute([$donation_id]);
    $donation = $stmt->fetch();

    if ($donation && $donation['status'] == 'pending') {
        if ($action == 'approve') {
            // Transaction
            $pdo->beginTransaction();
            try {
                // Update donation status
                $pdo->prepare("UPDATE donations SET status = 'approved', approved_at = NOW() WHERE id = ?")->execute([$donation_id]);

                // Add to blood stock
                // Check if stock entry exists (it should, as per add group logic)
                $stock = $pdo->prepare("SELECT * FROM blood_store WHERE blood_group_id = ?");
                $stock->execute([$donation['blood_group_id']]);
                $stock_entry = $stock->fetch();

                if ($stock_entry) {
                    $new_amount = $stock_entry['available_units'] + $donation['amount'];
                    $pdo->prepare("UPDATE blood_store SET available_units = ? WHERE id = ?")->execute([$new_amount, $stock_entry['id']]);
                } else {
                    // Create if not exists (failsafe)
                    $pdo->prepare("INSERT INTO blood_store (blood_group_id, available_units) VALUES (?, ?)")->execute([$donation['blood_group_id'], $donation['amount']]);
                }

                $pdo->commit();
                setFlash('success', 'Donation approved and stock updated.');
            } catch (Exception $e) {
                $pdo->rollBack();
                setFlash('danger', 'Error processing approval.');
            }
        } elseif ($action == 'reject') {
            $pdo->prepare("UPDATE donations SET status = 'rejected' WHERE id = ?")->execute([$donation_id]);
            setFlash('warning', 'Donation rejected.');
        }
    }
    // Redirect to same view
    echo "<script>window.location.href='dashboard.php?page=donations';</script>";
    exit;
}

$pending = $pdo->query("SELECT d.*, u.first_name, u.last_name, bg.group_name FROM donations d JOIN users u ON d.donor_id = u.id JOIN blood_groups bg ON d.blood_group_id = bg.id WHERE d.status = 'pending' ORDER BY d.created_at ASC")->fetchAll();

$history = $pdo->query("SELECT d.*, u.first_name, u.last_name, bg.group_name FROM donations d JOIN users u ON d.donor_id = u.id JOIN blood_groups bg ON d.blood_group_id = bg.id WHERE d.status != 'pending' ORDER BY d.created_at DESC LIMIT 50")->fetchAll();
?>

<h4 class="fw-bold text-dark mb-4">Manage Donations</h4>

<!-- Pending Donations -->
<div class="card card-custom mb-4 border-start border-4 border-warning">
    <div class="card-header card-header-custom bg-warning bg-opacity-10 text-warning">
        <i class="fas fa-clock me-2"></i> Pending Approvals
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Donor</th>
                        <th>Group</th>
                        <th>Units</th>
                        <th>Disease/Notes</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pending) > 0): ?>
                        <?php foreach ($pending as $d): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    <?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?>
                                </td>
                                <td><span class="badge bg-danger bg-opacity-10 text-danger">
                                        <?php echo htmlspecialchars($d['group_name']); ?>
                                    </span>
                                </td>
                                <td class="fw-bold">
                                    <?php echo $d['amount']; ?>
                                </td>
                                <td><small class="text-muted">
                                        <?php echo htmlspecialchars($d['disease_notes'] ?: '-'); ?>
                                    </small>
                                </td>
                                <td class="text-muted">
                                    <?php echo date('M d, Y', strtotime($d['created_at'])); ?>
                                </td>
                                <td class="text-end pe-4">
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="donation_id" value="<?php echo $d['id']; ?>">
                                        <button type="submit" name="donation_action" value="approve"
                                            class="btn btn-sm btn-success me-1">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <input type="hidden" name="action" value="approve">
                                    </form>
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="donation_id" value="<?php echo $d['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" name="donation_action" value="reject"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Reject this donation?');">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">No pending donations.</td>
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
        <i class="fas fa-history me-2"></i> Donation History
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Donor</th>
                        <th>Group</th>
                        <th>Units</th>
                        <th>Status</th>
                        <th>Processed Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark">
                                <?php echo htmlspecialchars($h['first_name'] . ' ' . $h['last_name']); ?>
                            </td>
                            <td><span class="badge bg-danger bg-opacity-10 text-danger">
                                    <?php echo htmlspecialchars($h['group_name']); ?>
                                </span>
                            </td>
                            <td class="fw-bold">
                                <?php echo $h['amount']; ?>
                            </td>
                            <td>
                                <span
                                    class="badge <?php echo ($h['status'] == 'approved') ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger'; ?>">
                                    <?php echo ucfirst($h['status']); ?>
                                </span>
                            </td>
                            <td class="text-muted">
                                <?php echo ($h['approved_at']) ? date('M d, Y', strtotime($h['approved_at'])) : date('M d, Y', strtotime($h['updated_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>