<?php
// Generate Report Data
// 1. Stock Report
$stock = $pdo->query("SELECT bs.available_units, bg.group_name FROM blood_store bs JOIN blood_groups bg ON bs.blood_group_id = bg.id")->fetchAll();

// 2. Donation Stats
$donations_total = $pdo->query("SELECT COUNT(*) FROM donations WHERE status='approved'")->fetchColumn();
$donations_today = $pdo->query("SELECT COUNT(*) FROM donations WHERE status='approved' AND DATE(approved_at) = CURDATE()")->fetchColumn();

// 3. User Stats
$users_total = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$donors_total = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type='donor'")->fetchColumn();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark">System Reports</h4>
    <button onclick="window.print();" class="btn btn-secondary"><i class="fas fa-print me-2"></i> Print
        Report</button>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card p-4 border-0 shadow-sm text-center card-custom">
            <h3 class="fw-bold text-primary-custom">
                <?php echo defined('APP_NAME') ? APP_NAME : 'BBMS'; ?>
            </h3>
            <p class="text-muted mb-0">Report Generated on:
                <?php echo date('Y-m-d H:i:s'); ?>
            </p>
        </div>
    </div>
</div>

<!-- Summary -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm text-center card-custom h-100">
            <h6 class="text-muted text-uppercase mb-2" style="font-size:0.75rem; letter-spacing:1px;">Total
                Users</h6>
            <h2 class="fw-bold text-dark mb-0">
                <?php echo $users_total; ?>
            </h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm text-center card-custom h-100">
            <h6 class="text-muted text-uppercase mb-2" style="font-size:0.75rem; letter-spacing:1px;">Total
                Donors</h6>
            <h2 class="fw-bold text-dark mb-0">
                <?php echo $donors_total; ?>
            </h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm text-center card-custom h-100">
            <h6 class="text-muted text-uppercase mb-2" style="font-size:0.75rem; letter-spacing:1px;">Total
                Donations</h6>
            <h2 class="fw-bold text-dark mb-0">
                <?php echo $donations_total; ?>
            </h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm text-center card-custom h-100">
            <h6 class="text-muted text-uppercase mb-2" style="font-size:0.75rem; letter-spacing:1px;">Today's
                Donations</h6>
            <h2 class="fw-bold text-dark mb-0">
                <?php echo $donations_today; ?>
            </h2>
        </div>
    </div>
</div>

<!-- Stock Table -->
<div class="card card-custom">
    <div class="card-header card-header-custom border-0">
        <i class="fas fa-flask me-2"></i> Current Blood Stock
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
                    <?php foreach ($stock as $s): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-danger fs-6">
                                    <?php echo htmlspecialchars($s['group_name']); ?>
                                </span>
                            </td>
                            <td class="fw-bold text-dark">
                                <?php echo $s['available_units']; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>