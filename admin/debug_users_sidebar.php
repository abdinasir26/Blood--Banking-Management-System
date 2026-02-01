<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 d-none d-md-block sidebar px-0 border-end">
            <?php require_once '../includes/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            Content
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>