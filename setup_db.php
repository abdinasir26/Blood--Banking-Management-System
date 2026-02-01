<?php
require_once 'includes/config.php';

try {
    // Connect to MySQL server (no DB selected)
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "Database " . DB_NAME . " created or exists.\n";

    // Select DB
    $pdo->exec("USE " . DB_NAME);

    // Read SQL file
    $sql = file_get_contents('database/bbms.sql');

    // Execute SQL
    // We need to split by semicolon because PDO usually runs one statement at a time, 
    // BUT some drivers allow multiple. 
    // bbms.sql has transaction commit, so it might work as a block.
    // Let's try running it as a whole block first. If it fails, we split.

    try {
        $pdo->exec($sql);
        echo "Schema imported successfully.\n";
    } catch (PDOException $e) {
        // Fallback: Split by semicolon
        echo "Block execution failed. Trying statement by statement...\n";
        $statements = explode(';', $sql);
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (!empty($stmt)) {
                try {
                    $pdo->exec($stmt);
                } catch (Exception $ex) {
                    // Ignore empty/comment errors or partials, but show warnings
                    echo "Warning on: " . substr($stmt, 0, 50) . "... : " . $ex->getMessage() . "\n";
                }
            }
        }
        echo "Statement execution completed.\n";
    }

} catch (PDOException $e) {
    echo "DB Connection Failed: " . $e->getMessage() . "\n";
}
?>