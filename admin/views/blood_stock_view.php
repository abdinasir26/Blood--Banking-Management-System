<?php
// Handle Stock Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $id = $_POST['store_id'];
    $units = (int) $_POST['units'];
    if ($units < 0)
        $units = 0;

    $pdo->prepare("UPDATE blood_store SET available_units = ? WHERE id = ?")->execute([$units, $id]);
    setFlash('success', 'Stock updated successfully.');
    echo "<script>window.location.href='dashboard.php?page=blood_stock';</script>";
    exit;
}

$inventory = $pdo->query("SELECT bs.*, bg.group_name FROM blood_store bs JOIN blood_groups bg ON bs.blood_group_id = bg.id ORDER BY bg.id")->fetchAll();
?>

<h4 class="fw-bold text-dark mb-4">Blood Inventory</h4>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Blood Group</th>
                        <th>Available Units</th>
                        <th>Last Updated</th>
                        <th class="text-end pe-4">Update Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory as $item): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-danger fs-5">
                                    <?php echo htmlspecialchars($item['group_name']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold fs-5 text-dark">
                                    <?php echo $item['available_units']; ?>
                                </span>
                            </td>
                            <td class="text-muted">
                                <?php echo $item['updated_at']; ?>
                            </td>
                            <td class="text-end pe-4">
                                <form action="" method="POST" class="d-flex justify-content-end align-items-center">
                                    <input type="hidden" name="store_id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="units" class="form-control form-control-sm me-2"
                                        style="width: 80px;" value="<?php echo $item['available_units']; ?>" min="0">
                                    <button type="submit" name="update_stock" class="btn btn-sm btn-primary">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>