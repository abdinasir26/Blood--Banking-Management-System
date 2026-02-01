<?php
// Handle Actions (Logic moved here or kept in dashboard.php? Ideally dashboard handles routing, but simplest is to keep logic here for now or include it)
// Ideally, logic should be at the top of the controller, but for this refactor, we can keep it here if included before output.
// However, since this view is included INSIDE the HTML body, we must ensure no header redirects happen here without buffering.
// The existing users.php had redirects. We should change those to redirects to dashboard.php?page=users.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['toggle_status'])) {
        $id = $_POST['user_id'];
        $current_status = $_POST['current_status'];
        $new_status = ($current_status == 'active') ? 'not_active' : 'active';

        $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$new_status, $id]);
        setFlash('success', 'User status updated.');
    } elseif (isset($_POST['delete_user'])) {
        $id = $_POST['user_id'];
        if ($id != $_SESSION['user_id']) { // Prevent self-delete
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            setFlash('success', 'User deleted.');
        } else {
            setFlash('danger', 'Cannot delete yourself.');
        }
    } elseif (isset($_POST['add_user'])) {
        $u_user = cleanInput($_POST['username']);
        $u_email = cleanInput($_POST['email']);
        $u_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $u_type = $_POST['user_type'];
        $u_fname = cleanInput($_POST['first_name']);
        $u_lname = cleanInput($_POST['last_name']);
        $u_sex = $_POST['sex'];
        $u_phone = cleanInput($_POST['phone']);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, sex, username, password_hash, phone, email, user_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$u_fname, $u_lname, $u_sex, $u_user, $u_pass, $u_phone, $u_email, $u_type]);
            setFlash('success', 'User created successfully.');
        } catch (Exception $e) {
            setFlash('danger', 'Error creating user. Username or Email may already exist.');
        }
    }
    // Redirect to the same page view
    echo "<script>window.location.href='dashboard.php?page=users';</script>";
    exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark">Manage Users</h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-user-plus me-2"></i> Add User
    </button>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-3 bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px;">
                                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">
                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                        </div>
                                        <div class="text-muted small">
                                            @
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge <?php echo ($user['user_type'] == 'admin') ? 'bg-dark' : 'bg-info bg-opacity-10 text-info'; ?>">
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
                                <span
                                    class="badge <?php echo ($user['status'] == 'active') ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $user['status']; ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-warning"
                                            title="Toggle Status">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                    <form action="" method="POST" class="d-inline ms-1"
                                        onsubmit="return confirm('Delete this user?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger"
                                            title="Delete" <?php echo ($user['id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sex</label>
                            <select class="form-select" name="sex">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="user_type">
                                <option value="donor">Donor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_user" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>