<h4 class="fw-bold text-dark mb-4">Dashboard Overview</h4>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value"><?php echo $total_users; ?></div>
            <div class="stat-label">Total Donors</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fas fa-tint"></i>
            </div>
            <div class="stat-value"><?php echo $total_stock; ?></div>
            <div class="stat-label">Total Units in Stock</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-hand-holding-heart"></i>
            </div>
            <div class="stat-value"><?php echo $pending_donations; ?></div>
            <div class="stat-label">Pending Donations</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-notes-medical"></i>
            </div>
            <div class="stat-value"><?php echo $patients_served; ?></div>
            <div class="stat-label">Patients Served</div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <!-- Bar Chart: Donors per Group -->
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Registered Donors by Blood Group</div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light" type="button"><i class="fas fa-ellipsis-h"></i></button>
                </div>
            </div>
            <canvas id="barChart" height="120"></canvas>
        </div>
    </div>

    <!-- Pie Chart: Stock Distribution -->
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Blood Stock Distribution</div>
            </div>
            <div style="position: relative; height: 250px;">
                <canvas id="doughnutChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Table -->
<div class="card card-custom">
    <div class="card-body">
        <h5 class="fw-bold mb-4">Recent Donations</h5>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="text-muted small text-uppercase">
                    <tr>
                        <th>Donor</th>
                        <th>Blood Group</th>
                        <th>Units</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent_donations) > 0): ?>
                        <?php foreach ($recent_donations as $d): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="icon-btn btn-sm bg-light text-primary">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <span
                                            class="fw-bold text-dark"><?php echo $d['first_name'] . ' ' . $d['last_name']; ?></span>
                                    </div>
                                </td>
                                <td><span
                                        class="badge bg-danger bg-opacity-10 text-danger px-3 py-2"><?php echo $d['group_name']; ?></span>
                                </td>
                                <td class="fw-bold"><?php echo $d['amount']; ?> Units</td>
                                <td>
                                    <?php if ($d['status'] == 'approved'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success">Approved</span>
                                    <?php elseif ($d['status'] == 'rejected'): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger">Rejected</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?php echo date('M d, Y', strtotime($d['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No recent donations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pending Approvals -->
<div class="row g-4 mt-4">
    <div class="col-lg-6">
        <div class="card card-custom">
            <div class="card-header card-header-custom bg-warning bg-opacity-10 text-warning d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clock me-2"></i>Pending Donations</span>
                <a href="dashboard.php?page=donations" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Donor</th>
                                <th>Group</th>
                                <th>Units</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pending_donation_rows) > 0): ?>
                                <?php foreach ($pending_donation_rows as $d): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            <?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?>
                                        </td>
                                        <td><span class="badge bg-danger bg-opacity-10 text-danger">
                                                <?php echo htmlspecialchars($d['group_name']); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold"><?php echo $d['amount']; ?></td>
                                        <td class="text-end pe-4">
                                            <form action="dashboard.php?page=donations" method="POST" class="d-inline">
                                                <input type="hidden" name="donation_id" value="<?php echo $d['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" name="donation_action" value="approve"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="dashboard.php?page=donations" method="POST" class="d-inline">
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
                                    <td colspan="4" class="text-center py-3 text-muted">No pending donations.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-custom">
            <div class="card-header card-header-custom bg-danger bg-opacity-10 text-danger d-flex justify-content-between align-items-center">
                <span><i class="fas fa-exclamation-circle me-2"></i>Pending Requests</span>
                <a href="dashboard.php?page=requests" class="btn btn-sm btn-outline-danger">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Hospital</th>
                                <th>Group</th>
                                <th>Units</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pending_request_rows) > 0): ?>
                                <?php foreach ($pending_request_rows as $r): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            <?php echo htmlspecialchars($r['hospital_name']); ?>
                                        </td>
                                        <td><span class="badge bg-danger bg-opacity-10 text-danger">
                                                <?php echo htmlspecialchars($r['group_name']); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold"><?php echo $r['amount']; ?></td>
                                        <td class="text-end pe-4">
                                            <form action="dashboard.php?page=requests" method="POST" class="d-inline">
                                                <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" name="request_action" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="dashboard.php?page=requests" method="POST" class="d-inline">
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
                                    <td colspan="4" class="text-center py-3 text-muted">No pending requests.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Data from PHP
    const donorLabels = <?php echo $donor_labels; ?>;
    const donorValues = <?php echo $donor_values; ?>;
    const stockLabels = <?php echo $stock_labels; ?>;
    const stockValues = <?php echo $stock_values; ?>;

    // Bar Chart Configuration
    const barCtx = document.getElementById('barChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: donorLabels,
            datasets: [{
                label: 'Donors',
                data: donorValues,
                backgroundColor: '#8B0000',
                borderRadius: 6,
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5] }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Doughnut Chart Configuration
    const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');
    new Chart(doughnutCtx, {
        type: 'doughnut',
        data: {
            labels: stockLabels,
            datasets: [{
                data: stockValues,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#E7E9ED', '#8B0000'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
