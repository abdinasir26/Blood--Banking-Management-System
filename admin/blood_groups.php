<?php
require_once '../includes/config.php';
// Redirect to the new Single Page Application style dashboard
header("Location: " . BASE_URL . "admin/dashboard.php?page=blood_groups");
exit;
?>