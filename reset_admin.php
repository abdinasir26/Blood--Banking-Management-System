<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$pass = 'Admin@123';
$hash = password_hash($pass, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$hash]);
    echo "Admin password reset successfully to: $pass\n";
    echo "Hash: $hash\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>