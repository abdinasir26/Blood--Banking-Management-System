<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

echo "Starting blood_store cleanup...\n";

// 1. Get total units for each blood group
$sql = "SELECT blood_group_id, SUM(available_units) as total_units FROM blood_store GROUP BY blood_group_id";
$aggregated_stock = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

if (empty($aggregated_stock)) {
    echo "No stock found to fix.\n";
    exit;
}

echo "Found " . count($aggregated_stock) . " blood groups with stock data.\n";

$pdo->beginTransaction();

try {
    // 2. Clear the table
    $pdo->exec("DELETE FROM blood_store");

    // 3. Re-insert aggregated data
    $stmt = $pdo->prepare("INSERT INTO blood_store (blood_group_id, available_units, updated_at) VALUES (?, ?, NOW())");

    foreach ($aggregated_stock as $item) {
        $stmt->execute([$item['blood_group_id'], $item['total_units']]);
        echo "Restored Group " . $item['blood_group_id'] . " with " . $item['total_units'] . " units.\n";
    }

    $pdo->commit();
    echo "Cleanup successful! Duplicates removed and units consolidated.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>