<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
$uid = $_SESSION['user_id'];

// Update Profile Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = cleanInput($_POST['first_name']);
    $last_name = cleanInput($_POST['last_name']);
    $medical_info = cleanInput($_POST['medical_info']);

    // Check if password change
    $password_sql = "";
    $params = [$first_name, $last_name, $medical_info, $uid];

    if (!empty($_POST['new_password'])) {
        $password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $password_sql = ", password_hash = ?";
        // Insert password hash before uid in params
        array_splice($params, 3, 0, $password_hash);
    }

    $sql = "UPDATE users SET first_name = ?, last_name = ?, medical_info = ?" . $password_sql . " WHERE id = ?";
    $pdo->prepare($sql)->execute($params);

    $_SESSION['first_name'] = $first_name; // Update session
    setFlash('success', 'Profile updated successfully.');
    redirect('donor/profile.php');
}

$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$uid]);
$user = $user->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - BBMS</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/client_style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>

<body>

    <?php require_once 'layout/navbar.php'; ?>

    <div class="container py-5">
        <div class="row g-4">
            <!-- Left Column: User Card -->
            <div class="col-md-4">
                <div class="form-card text-center h-100">
                    <div class="position-relative d-inline-block mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto"
                            style="width: 100px; height: 100px; font-size: 2.5rem; background: var(--primary-red) !important;">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                        </div>
                    </div>
                    <h4 class="fw-bold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                    </h4>
                    <p class="text-muted mb-1"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="badge bg-secondary rounded-pill px-3"><?php echo ucfirst($user['user_type']); ?></span>

                    <hr class="my-4">
                    <div class="text-start">
                        <h6 class="text-uppercase text-muted fs-7 ls-1">Member Since</h6>
                        <p class="fw-bold"><?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Edit Form -->
            <div class="col-md-8">
                <div class="form-card h-100">
                    <h3 class="fw-bold mb-4">Edit Profile</h3>

                    <form action="" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">FIRST NAME</label>
                                <input type="text" class="form-control form-control-lg" name="first_name"
                                    value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">LAST NAME</label>
                                <input type="text" class="form-control form-control-lg" name="last_name"
                                    value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label text-muted small fw-bold">MEDICAL INFO (BLOOD TYPE /
                                    ISSUES)</label>
                                <textarea class="form-control" name="medical_info"
                                    rows="3"><?php echo htmlspecialchars($user['medical_info']); ?></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <hr>
                                <h6 class="fw-bold mb-3 text-danger"><i class="fas fa-lock me-2"></i>Change Password
                                    <span class="text-muted fw-normal small ms-2">(Leave blank to keep current)</span>
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">NEW PASSWORD</label>
                                <input type="password" class="form-control form-control-lg" name="new_password">
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary-custom px-5">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>