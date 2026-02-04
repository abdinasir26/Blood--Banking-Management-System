<?php
$today = date('Y-m-d');
$default_from = date('Y-m-d', strtotime('-29 days'));
$from = isset($_GET['from']) ? $_GET['from'] : $default_from;
$to = isset($_GET['to']) ? $_GET['to'] : $today;

$is_valid_date = function ($date) {
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt && $dt->format('Y-m-d') === $date;
};

if (!$is_valid_date($from) || !$is_valid_date($to) || $from > $to) {
    $from = $default_from;
    $to = $today;
}

$days_range = (int) ((strtotime($to) - strtotime($from)) / 86400) + 1;
if ($days_range > 366) {
    $from = date('Y-m-d', strtotime($to . ' -365 days'));
    $days_range = 366;
}

$summary_stmt = $pdo->prepare("
    SELECT
        (SELECT COUNT(*) FROM users WHERE user_type = 'donor') AS total_donors,
        (SELECT COUNT(*) FROM users WHERE status = 'active' AND user_type = 'donor') AS active_donors,
        (SELECT COUNT(*) FROM donations WHERE status = 'approved' AND DATE(approved_at) BETWEEN ? AND ?) AS approved_donations,
        (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'approved' AND DATE(approved_at) BETWEEN ? AND ?) AS donated_units,
        (SELECT COUNT(*) FROM requests WHERE status = 'approved' AND DATE(approved_at) BETWEEN ? AND ?) AS approved_requests,
        (SELECT COALESCE(SUM(amount), 0) FROM requests WHERE status = 'approved' AND DATE(approved_at) BETWEEN ? AND ?) AS fulfilled_units,
        (SELECT COUNT(*) FROM donations WHERE status = 'pending') AS pending_donations,
        (SELECT COUNT(*) FROM requests WHERE status = 'pending') AS pending_requests
");
$summary_stmt->execute([$from, $to, $from, $to, $from, $to, $from, $to]);
$summary = $summary_stmt->fetch(PDO::FETCH_ASSOC);

$stock = $pdo->query("
    SELECT bg.group_name, bs.available_units
    FROM blood_store bs
    JOIN blood_groups bg ON bg.id = bs.blood_group_id
    ORDER BY bg.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

$donations_by_group_stmt = $pdo->prepare("
    SELECT bg.group_name, COUNT(d.id) AS donation_count, COALESCE(SUM(d.amount), 0) AS donated_units
    FROM blood_groups bg
    LEFT JOIN donations d ON d.blood_group_id = bg.id
        AND d.status = 'approved'
        AND DATE(d.approved_at) BETWEEN ? AND ?
    GROUP BY bg.id, bg.group_name
    ORDER BY donated_units DESC, donation_count DESC
");
$donations_by_group_stmt->execute([$from, $to]);
$donations_by_group = $donations_by_group_stmt->fetchAll(PDO::FETCH_ASSOC);

$requests_status_stmt = $pdo->prepare("
    SELECT status, COUNT(*) AS count_requests, COALESCE(SUM(amount), 0) AS units
    FROM requests
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY status
");
$requests_status_stmt->execute([$from, $to]);
$requests_status = $requests_status_stmt->fetchAll(PDO::FETCH_ASSOC);

$top_donors_stmt = $pdo->prepare("
    SELECT CONCAT(u.first_name, ' ', u.last_name) AS donor_name, COUNT(d.id) AS approved_count, COALESCE(SUM(d.amount), 0) AS units
    FROM donations d
    JOIN users u ON u.id = d.donor_id
    WHERE d.status = 'approved' AND DATE(d.approved_at) BETWEEN ? AND ?
    GROUP BY d.donor_id, donor_name
    ORDER BY units DESC, approved_count DESC
    LIMIT 5
");
$top_donors_stmt->execute([$from, $to]);
$top_donors = $top_donors_stmt->fetchAll(PDO::FETCH_ASSOC);

$top_hospitals_stmt = $pdo->prepare("
    SELECT hospital_name, COUNT(id) AS approved_count, COALESCE(SUM(amount), 0) AS units
    FROM requests
    WHERE status = 'approved' AND DATE(approved_at) BETWEEN ? AND ?
    GROUP BY hospital_name
    ORDER BY units DESC, approved_count DESC
    LIMIT 5
");
$top_hospitals_stmt->execute([$from, $to]);
$top_hospitals = $top_hospitals_stmt->fetchAll(PDO::FETCH_ASSOC);

$daily_donations_stmt = $pdo->prepare("
    SELECT DATE(approved_at) AS report_date, COUNT(*) AS approved_count
    FROM donations
    WHERE status = 'approved' AND DATE(approved_at) BETWEEN ? AND ?
    GROUP BY DATE(approved_at)
");
$daily_donations_stmt->execute([$from, $to]);
$daily_donations_rows = $daily_donations_stmt->fetchAll(PDO::FETCH_ASSOC);
$daily_donations = [];
foreach ($daily_donations_rows as $row) {
    $daily_donations[$row['report_date']] = (int) $row['approved_count'];
}

$daily_requests_stmt = $pdo->prepare("
    SELECT DATE(approved_at) AS report_date, COUNT(*) AS approved_count
    FROM requests
    WHERE status = 'approved' AND DATE(approved_at) BETWEEN ? AND ?
    GROUP BY DATE(approved_at)
");
$daily_requests_stmt->execute([$from, $to]);
$daily_requests_rows = $daily_requests_stmt->fetchAll(PDO::FETCH_ASSOC);
$daily_requests = [];
foreach ($daily_requests_rows as $row) {
    $daily_requests[$row['report_date']] = (int) $row['approved_count'];
}
?>

<style>
    .report-toolbar .form-control {
        min-width: 160px;
    }

    @media print {
        .report-toolbar {
            display: none !important;
        }

        .card {
            break-inside: avoid;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold text-dark mb-0">Advanced Reports</h4>
</div>

<div class="card card-custom mb-4 report-toolbar">
    <div class="card-body">
        <form method="GET" action="dashboard.php" class="d-flex flex-wrap gap-2 align-items-end">
            <input type="hidden" name="page" value="reports">
            <div>
                <label class="form-label mb-1">From</label>
                <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>" class="form-control">
            </div>
            <div>
                <label class="form-label mb-1">To</label>
                <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>" class="form-control">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Apply</button>
                <a href="dashboard.php?page=reports" class="btn btn-outline-secondary">Reset</a>
                <button type="button" onclick="window.print();" class="btn btn-outline-dark"><i
                        class="fas fa-print me-1"></i> Print</button>
            </div>
        </form>
        <div class="text-muted small mt-2">
            Date range: <strong><?php echo htmlspecialchars($from); ?></strong> to <strong><?php echo htmlspecialchars($to); ?></strong>
            (<?php echo $days_range; ?> day<?php echo $days_range > 1 ? 's' : ''; ?>)
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card p-3 card-custom h-100">
            <div class="text-muted small text-uppercase">Approved Donations</div>
            <div class="fw-bold fs-3"><?php echo (int) $summary['approved_donations']; ?></div>
            <div class="small text-muted"><?php echo (int) $summary['donated_units']; ?> units collected</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 card-custom h-100">
            <div class="text-muted small text-uppercase">Approved Requests</div>
            <div class="fw-bold fs-3"><?php echo (int) $summary['approved_requests']; ?></div>
            <div class="small text-muted"><?php echo (int) $summary['fulfilled_units']; ?> units fulfilled</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 card-custom h-100">
            <div class="text-muted small text-uppercase">Active Donors</div>
            <div class="fw-bold fs-3"><?php echo (int) $summary['active_donors']; ?></div>
            <div class="small text-muted">of <?php echo (int) $summary['total_donors']; ?> registered donors</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 card-custom h-100">
            <div class="text-muted small text-uppercase">Pending Workload</div>
            <div class="fw-bold fs-3"><?php echo (int) $summary['pending_donations'] + (int) $summary['pending_requests']; ?>
            </div>
            <div class="small text-muted">
                <?php echo (int) $summary['pending_donations']; ?> donations, <?php echo (int) $summary['pending_requests']; ?> requests
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header card-header-custom border-0">Collection By Blood Group</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Group</th>
                                <th>Approved Donations</th>
                                <th>Units Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donations_by_group as $row): ?>
                                <tr>
                                    <td class="ps-4"><span class="badge bg-danger"><?php echo htmlspecialchars($row['group_name']); ?></span></td>
                                    <td><?php echo (int) $row['donation_count']; ?></td>
                                    <td class="fw-bold"><?php echo (int) $row['donated_units']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header card-header-custom border-0">Request Intake By Status</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Status</th>
                                <th>Requests</th>
                                <th>Units</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($requests_status) > 0): ?>
                                <?php foreach ($requests_status as $row): ?>
                                    <tr>
                                        <td class="ps-4"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></td>
                                        <td><?php echo (int) $row['count_requests']; ?></td>
                                        <td class="fw-bold"><?php echo (int) $row['units']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No requests in this period.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header card-header-custom border-0">Top Donors (By Units)</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Donor</th>
                                <th>Approved Donations</th>
                                <th>Units</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($top_donors) > 0): ?>
                                <?php foreach ($top_donors as $row): ?>
                                    <tr>
                                        <td class="ps-4"><?php echo htmlspecialchars($row['donor_name']); ?></td>
                                        <td><?php echo (int) $row['approved_count']; ?></td>
                                        <td class="fw-bold"><?php echo (int) $row['units']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No approved donations in this period.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header card-header-custom border-0">Top Hospitals Served</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Hospital</th>
                                <th>Approved Requests</th>
                                <th>Units Fulfilled</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($top_hospitals) > 0): ?>
                                <?php foreach ($top_hospitals as $row): ?>
                                    <tr>
                                        <td class="ps-4"><?php echo htmlspecialchars($row['hospital_name']); ?></td>
                                        <td><?php echo (int) $row['approved_count']; ?></td>
                                        <td class="fw-bold"><?php echo (int) $row['units']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No approved requests in this period.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header card-header-custom border-0">Current Blood Stock</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Blood Group</th>
                                <th>Available Units</th>
                                <th>Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stock as $row): ?>
                                <?php $is_low = ((int) $row['available_units'] < 5); ?>
                                <tr>
                                    <td class="ps-4"><span class="badge bg-danger"><?php echo htmlspecialchars($row['group_name']); ?></span></td>
                                    <td class="fw-bold"><?php echo (int) $row['available_units']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $is_low ? 'bg-warning text-dark' : 'bg-success'; ?>">
                                            <?php echo $is_low ? 'Low' : 'Stable'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header card-header-custom border-0">Daily Throughput</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Approved Donations</th>
                                <th>Approved Requests</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < $days_range; $i++): ?>
                                <?php
                                $day = date('Y-m-d', strtotime($from . " +$i days"));
                                $don_count = isset($daily_donations[$day]) ? $daily_donations[$day] : 0;
                                $req_count = isset($daily_requests[$day]) ? $daily_requests[$day] : 0;
                                ?>
                                <tr>
                                    <td class="ps-4"><?php echo htmlspecialchars($day); ?></td>
                                    <td><?php echo (int) $don_count; ?></td>
                                    <td><?php echo (int) $req_count; ?></td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
