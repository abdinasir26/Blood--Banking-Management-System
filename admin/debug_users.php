<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();
echo "Header/Auth OK";

$users = $pdo->query("SELECT * FROM users")->fetchAll();
echo " | DB Query OK";
?>