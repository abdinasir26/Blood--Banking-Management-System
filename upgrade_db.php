<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    echo "Checking users table columns...\n";

    // blood_group_id
    $col_check = $pdo->query("SHOW COLUMNS FROM users LIKE 'blood_group_id'");
    if ($col_check->rowCount() == 0) {
        $sql = "ALTER TABLE users ADD COLUMN blood_group_id INT(11) NULL AFTER email";
        $pdo->exec($sql);
        echo "Column blood_group_id added successfully.\n";
    } else {
        echo "Column blood_group_id already exists.\n";
    }

    // medical_info
    $col_check = $pdo->query("SHOW COLUMNS FROM users LIKE 'medical_info'");
    if ($col_check->rowCount() == 0) {
        $sql = "ALTER TABLE users ADD COLUMN medical_info TEXT NULL AFTER profile_picture";
        $pdo->exec($sql);
        echo "Column medical_info added successfully.\n";
    } else {
        echo "Column medical_info already exists.\n";
    }

    // foreign key for blood_group_id if missing
    $fk_check = $pdo->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'blood_group_id' AND REFERENCED_TABLE_NAME = 'blood_groups'");
    if ($fk_check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD CONSTRAINT fk_user_blood_group FOREIGN KEY (blood_group_id) REFERENCES blood_groups(id) ON DELETE SET NULL");
        echo "Foreign key fk_user_blood_group added successfully.\n";
    } else {
        echo "Foreign key fk_user_blood_group already exists.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
