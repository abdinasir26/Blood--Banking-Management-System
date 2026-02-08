<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
$uid = $_SESSION['user_id'];

// Load current user (needed for showing/deleting old profile picture)
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$uid]);
$user = $userStmt->fetch();

// Detect optional columns (for older DBs)
$has_medical_info = false;
// profile_picture exists in current schema, but keep this for older DBs.
$has_profile_picture = false;
try {
    $col_check = $pdo->query("SHOW COLUMNS FROM users LIKE 'medical_info'");
    $has_medical_info = $col_check->rowCount() > 0;
} catch (Exception $e) {
    $has_medical_info = false;
}
try {
    $col_check = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    $has_profile_picture = $col_check->rowCount() > 0;
} catch (Exception $e) {
    $has_profile_picture = false;
}

// Update Profile Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = cleanInput($_POST['first_name']);
    $last_name = cleanInput($_POST['last_name']);
    $medical_info = cleanInput($_POST['medical_info']);

    $fields = ["first_name = ?", "last_name = ?"];
    $params = [$first_name, $last_name];
    $session_profile_picture = $_SESSION['profile_picture'] ?? ($user['profile_picture'] ?? null);

    if ($has_medical_info) {
        $fields[] = "medical_info = ?";
        $params[] = $medical_info;
    }

    // Optional profile picture upload
    if ($has_profile_picture && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['profile_picture'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $error = "Failed to upload profile picture.";
        } elseif ($f['size'] > 2 * 1024 * 1024) {
            $error = "Profile picture must be 2MB or smaller.";
        } else {
            $imgInfo = @getimagesize($f['tmp_name']);
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];
            $mime = $imgInfo['mime'] ?? '';
            if (!$imgInfo || !isset($allowed[$mime])) {
                $error = "Please upload a valid image (JPG/PNG/WEBP/GIF).";
            } else {
                $ext = $allowed[$mime];
                $root = dirname(__DIR__);
                $relDir = "uploads/profile_pictures";
                $absDir = $root . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "profile_pictures";

                if (!is_dir($absDir)) {
                    @mkdir($absDir, 0755, true);
                }

                $name = "u" . $uid . "_" . date("Ymd_His") . "_" . bin2hex(random_bytes(6)) . "." . $ext;
                $absPath = $absDir . DIRECTORY_SEPARATOR . $name;
                $relPath = $relDir . "/" . $name;

                if (!move_uploaded_file($f['tmp_name'], $absPath)) {
                    $error = "Could not save uploaded profile picture.";
                } else {
                    // Delete old uploaded image (only if it looks like one of ours)
                    $old = $user['profile_picture'] ?? '';
                    if ($old && str_starts_with($old, $relDir . "/")) {
                        $oldAbs = $root . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $old);
                        if (is_file($oldAbs)) {
                            @unlink($oldAbs);
                        }
                    }

                    $fields[] = "profile_picture = ?";
                    $params[] = $relPath;
                    $session_profile_picture = $relPath;
                }
            }
        }
    }

    // Optional password change
    if (!empty($_POST['new_password'])) {
        $password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $fields[] = "password_hash = ?";
        $params[] = $password_hash;
    }

    if (!isset($error)) {
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        $params[] = $uid;
        $pdo->prepare($sql)->execute($params);

        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['profile_picture'] = $session_profile_picture;
        setFlash('success', 'Profile updated successfully.');
        redirect('donor/profile.php');
    }
}
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
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo '../' . htmlspecialchars($user['profile_picture']); ?>"
                                alt="Profile picture" class="rounded-circle"
                                style="width: 100px; height: 100px; object-fit: cover; border: 3px solid rgba(0,0,0,0.06);">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                style="width: 100px; height: 100px; font-size: 2.5rem; background: var(--primary-red) !important;">
                                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
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

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger rounded-3"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
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
                                    rows="3"><?php echo htmlspecialchars($user['medical_info'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label text-muted small fw-bold">PROFILE PICTURE</label>
                                <input type="file" class="form-control" name="profile_picture" accept="image/*">
                                <div class="form-text text-muted">JPG/PNG/WEBP/GIF, max 2MB.</div>
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
