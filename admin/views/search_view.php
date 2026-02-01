<?php
$query = trim($_GET['q'] ?? '');
$users = [];
$donations = [];
$requests = [];
$stock = [];

if ($query !== '') {
    $like = '%' . $query . '%';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$like, $like, $like, $like, $like]);
    $users = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT d.*, u.first_name, u.last_name, u.username, bg.group_name
        FROM donations d
        JOIN users u ON d.donor_id = u.id
        JOIN blood_groups bg ON d.blood_group_id = bg.id
        WHERE u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR bg.group_name LIKE ?
        ORDER BY d.created_at DESC
        LIMIT 50");
    $stmt->execute([$like, $like, $like, $like]);
    $donations = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name, u.username, bg.group_name
        FROM requests r
        JOIN users u ON r.requester_id = u.id
        JOIN blood_groups bg ON r.blood_group_id = bg.id
        WHERE r.hospital_name LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR bg.group_name LIKE ?
        ORDER BY r.created_at DESC
        LIMIT 50");
    $stmt->execute([$like, $like, $like, $like, $like]);
    $requests = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT bs.*, bg.group_name FROM blood_store bs JOIN blood_groups bg ON bs.blood_group_id = bg.id WHERE bg.group_name LIKE ? ORDER BY bg.id");
    $stmt->execute([$like]);
    $stock = $stmt->fetchAll();
}
?>

<h4 class="fw-bold text-dark mb-4">Search Results</h4>

<?php if ($query === ''): ?>
    <div class="alert alert-info">Type in the search box to find users, donations, requests, or stock.</div>
<?php else: ?>
    <div class="text-muted mb-3">Results for: <span class="fw-bold"><?php echo htmlspecialchars($query); ?></span></div>
<?php endif; ?>

<!-- Users -->
<div class="card card-custom mb-4">
    <div class="card-header card-header-custom border-0">
        <i class="fas fa-users me-2"></i> Users
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">
                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                    </div>
                                    <div class="text-muted small">@<?php echo htmlspecialchars($user['username']); ?></div>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($user['user_type'] == 'admin') ? 'bg-dark' : 'bg-info bg-opacity-10 text-info'; ?>">
                                        <?php echo ucfirst($user['user_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-muted"><i class="fas fa-envelope me-1"></i>
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </div>
                                    <div class="small text-muted"><i class="fas fa-phone me-1"></i>
                                        <?php echo htmlspecialchars($user['phone']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($user['status'] == 'active') ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-3 text-muted">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Donations -->
<div class="card card-custom mb-4">
    <div class="card-header card-header-custom border-0">
        <i class="fas fa-tint me-2"></i> Donations
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
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($donations) > 0): ?>
                        <?php foreach ($donations as $d): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    <?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?>
                                </td>
                                <td><span class="badge bg-danger bg-opacity-10 text-danger">
                                        <?php echo htmlspecialchars($d['group_name']); ?>
                                    </span>
                                </td>
                                <td class="fw-bold"><?php echo $d['amount']; ?></td>
                                <td>
                                    <span class="badge <?php echo ($d['status'] == 'approved') ? 'bg-success bg-opacity-10 text-success' : (($d['status'] == 'rejected') ? 'bg-danger bg-opacity-10 text-danger' : 'bg-warning bg-opacity-10 text-warning'); ?>">
                                        <?php echo ucfirst($d['status']); ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?php echo date('M d, Y', strtotime($d['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">No donations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Requests -->
<div class="card card-custom mb-4">
    <div class="card-header card-header-custom border-0">
        <i class="fas fa-notes-medical me-2"></i> Requests
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Hospital</th>
                        <th>Requester</th>
                        <th>Group</th>
                        <th>Units</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($requests) > 0): ?>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    <?php echo htmlspecialchars($r['hospital_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></td>
                                <td><span class="badge bg-danger bg-opacity-10 text-danger">
                                        <?php echo htmlspecialchars($r['group_name']); ?>
                                    </span>
                                </td>
                                <td class="fw-bold"><?php echo $r['amount']; ?></td>
                                <td>
                                    <span class="badge <?php echo ($r['status'] == 'approved') ? 'bg-success bg-opacity-10 text-success' : (($r['status'] == 'rejected') ? 'bg-danger bg-opacity-10 text-danger' : 'bg-warning bg-opacity-10 text-warning'); ?>">
                                        <?php echo ucfirst($r['status']); ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">No requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Stock -->
<div class="card card-custom">
    <div class="card-header card-header-custom border-0">
        <i class="fas fa-warehouse me-2"></i> Blood Stock
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Blood Group</th>
                        <th>Available Units</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($stock) > 0): ?>
                        <?php foreach ($stock as $s): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($s['group_name']); ?></td>
                                <td class="fw-bold"><?php echo $s['available_units']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center py-3 text-muted">No stock records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
