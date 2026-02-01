<?php
// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_group'])) {
        $name = cleanInput($_POST['group_name']);
        if (!empty($name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO blood_groups (group_name) VALUES (?)");
                $stmt->execute([$name]);
                // Also initialize store
                $id = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO blood_store (blood_group_id, available_units) VALUES (?, 0)")->execute([$id]);

                setFlash('success', 'Blood Group added successfully.');
            } catch (Exception $e) {
                setFlash('danger', 'Error adding group. It may already exist.');
            }
        }
    } elseif (isset($_POST['delete_group'])) {
        $id = $_POST['group_id'];
        $pdo->prepare("DELETE FROM blood_groups WHERE id = ?")->execute([$id]);
        setFlash('success', 'Blood Group deleted.');
    }
    echo "<script>window.location.href='dashboard.php?page=blood_groups';</script>";
    exit;
}

$groups = $pdo->query("SELECT * FROM blood_groups ORDER BY group_name")->fetchAll();
?>

<h4 class="fw-bold text-dark mb-4">Manage Blood Groups</h4>

<div class="card card-custom mb-4">
    <div class="card-header card-header-custom border-0">
        <i class="fas fa-plus me-2"></i> Add New Blood Group
    </div>
    <div class="card-body">
        <form action="" method="POST" class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="group_name" class="visually-hidden">Group Name</label>
                <input type="text" class="form-control" name="group_name" placeholder="E.g. A2+" required>
            </div>
            <div class="col-auto">
                <button type="submit" name="add_group" class="btn btn-primary">Add Group</button>
            </div>
        </form>
    </div>
</div>

<div class="card card-custom">
    <div class="card-header card-header-custom border-0">
        <i class="fas fa-list me-2"></i> Existing Groups
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Group Name</th>
                        <th>Created At</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groups as $group): ?>
                        <tr>
                            <td class="ps-4">
                                <?php echo $group['id']; ?>
                            </td>
                            <td><span class="badge bg-danger fs-6">
                                    <?php echo htmlspecialchars($group['group_name']); ?>
                                </span>
                            </td>
                            <td class="text-muted">
                                <?php echo $group['created_at']; ?>
                            </td>
                            <td class="text-end pe-4">
                                <form action="" method="POST"
                                    onsubmit="return confirm('Are you sure? This will delete related stock/history.');">
                                    <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                                    <button type="submit" name="delete_group" class="btn btn-sm btn-outline-danger"><i
                                            class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>