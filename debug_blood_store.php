<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

echo "Checking for duplicates in blood_store...\n";

$sql = "SELECT blood_group_id, COUNT(*) as count, SUM(available_units) as total_units FROM blood_store GROUP BY blood_group_id HAVING count > 1";
$duplicates = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

if (empty($duplicates)) {
    echo "No duplicates found based on blood_group_id group count.\n";
    // Check all rows just in case
    $all = $pdo->query("SELECT * FROM blood_store")->fetchAll(PDO::FETCH_ASSOC);
    echo "Total rows: " . count($all) . "\n";
    print_r($all);
} else {
    echo "Duplicates found:\n";
    foreach ($duplicates as $dup) {
        echo "Blood Group ID: " . $dup['blood_group_id'] . " - Count: " . $dup['count'] . " - Total Units: " . $dup['total_units'] . "\n";
    }
}
?>