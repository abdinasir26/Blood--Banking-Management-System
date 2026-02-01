<?php
// One-off script to update admin email and password_hash using existing config/db files.
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// New credentials (must match what was updated in bbms.sql)
$new_email = 'admin@changed.local';
$new_hash = '$2b$12$/MGmr2nt97scfoksS2LsV.XkOV07dwugzBo/90lxCyPiOFikMECQu';
$username = 'admin';

try {
    $stmt = $pdo->prepare("UPDATE users SET email = ?, password_hash = ? WHERE username = ?");
    $stmt->execute([$new_email, $new_hash, $username]);
    $count = $stmt->rowCount();
    echo "Updated $count row(s).\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Verify
try {
    $v = $pdo->prepare("SELECT id, username, email FROM users WHERE username = ?");
    $v->execute([$username]);
    $user = $v->fetch();
    if ($user) {
        echo "User: " . $user['username'] . " (id=" . $user['id'] . ") email=" . $user['email'] . "\n";
    } else {
        echo "User not found.\n";
    }
} catch (Exception $e) {
    echo "Verify error: " . $e->getMessage() . "\n";
    exit(1);
}

?>