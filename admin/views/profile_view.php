<?php
requireAdmin();

$uid = $_SESSION['user_id'];

// Load current user
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$uid]);
$user = $userStmt->fetch();

// Detect optional columns (older DBs)
$has_medical_info = false;
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = cleanInput($_POST['first_name'] ?? '');
    $last_name = cleanInput($_POST['last_name'] ?? '');
    $medical_info = cleanInput($_POST['medical_info'] ?? '');

    $fields = ["first_name = ?", "last_name = ?"];
    $params = [$first_name, $last_name];

    if ($has_medical_info) {
        $fields[] = "medical_info = ?";
        $params[] = $medical_info;
    }

    // Optional profile picture upload
    $session_profile_picture = $_SESSION['profile_picture'] ?? ($user['profile_picture'] ?? null);
    if ($has_profile_picture && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['profile_picture'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            setFlash('danger', 'Failed to upload profile picture.');
            echo "<script>window.location.href='dashboard.php?page=profile';</script>";
            exit;
        }
        if ($f['size'] > 2 * 1024 * 1024) {
            setFlash('danger', 'Profile picture must be 2MB or smaller.');
            echo "<script>window.location.href='dashboard.php?page=profile';</script>";
            exit;
        }

        $imgInfo = @getimagesize($f['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        $mime = $imgInfo['mime'] ?? '';
        if (!$imgInfo || !isset($allowed[$mime])) {
            setFlash('danger', 'Please upload a valid image (JPG/PNG/WEBP/GIF).');
            echo "<script>window.location.href='dashboard.php?page=profile';</script>";
            exit;
        }

        $ext = $allowed[$mime];
        $root = dirname(__DIR__, 2); // project root (BBMS/)
        $relDir = "uploads/profile_pictures";
        $absDir = $root . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "profile_pictures";
        if (!is_dir($absDir)) {
            @mkdir($absDir, 0755, true);
        }

        $name = "u" . $uid . "_" . date("Ymd_His") . "_" . bin2hex(random_bytes(6)) . "." . $ext;
        $absPath = $absDir . DIRECTORY_SEPARATOR . $name;
        $relPath = $relDir . "/" . $name;

        if (!move_uploaded_file($f['tmp_name'], $absPath)) {
            setFlash('danger', 'Could not save uploaded profile picture.');
            echo "<script>window.location.href='dashboard.php?page=profile';</script>";
            exit;
        }

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

    // Optional password change
    if (!empty($_POST['new_password'])) {
        $password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $fields[] = "password_hash = ?";
        $params[] = $password_hash;
    }

    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $params[] = $uid;
    $pdo->prepare($sql)->execute($params);

    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;
    $_SESSION['profile_picture'] = $session_profile_picture;
    setFlash('success', 'Profile updated successfully.');
    echo "<script>window.location.href='dashboard.php?page=profile';</script>";
    exit;
}

?>

<div class="content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">My Profile</h4>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card card-custom">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo '../' . htmlspecialchars($user['profile_picture']); ?>" alt="Profile picture"
                                class="rounded-circle" style="width: 110px; height: 110px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                style="width: 110px; height: 110px; font-size: 2.6rem;">
                                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="fw-bold">
                        <?php echo htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?>
                    </div>
                    <div class="text-muted small"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                    <div class="mt-2">
                        <span class="badge bg-dark">Admin</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-custom">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Edit Profile</h5>
                    <form action="dashboard.php?page=profile" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">FIRST NAME</label>
                                <input type="text" class="form-control" name="first_name"
                                    value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">LAST NAME</label>
                                <input type="text" class="form-control" name="last_name"
                                    value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                            </div>

                            <?php if ($has_medical_info): ?>
                                <div class="col-12">
                                    <label class="form-label text-muted small fw-bold">MEDICAL INFO</label>
                                    <textarea class="form-control" name="medical_info"
                                        rows="3"><?php echo htmlspecialchars($user['medical_info'] ?? ''); ?></textarea>
                                </div>
                            <?php endif; ?>

                            <?php if ($has_profile_picture): ?>
                                <div class="col-12">
                                    <label class="form-label text-muted small fw-bold">PROFILE PICTURE</label>
                                    <input type="file" class="form-control" name="profile_picture" accept="image/*">
                                    <div class="form-text text-muted">JPG/PNG/WEBP/GIF, max 2MB.</div>
                                </div>
                            <?php endif; ?>

                            <div class="col-12 mt-2">
                                <hr>
                                <div class="fw-bold text-danger mb-2">
                                    <i class="fas fa-lock me-2"></i>Change Password
                                    <span class="text-muted fw-normal small ms-2">(Leave blank to keep current)</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">NEW PASSWORD</label>
                                <input type="password" class="form-control" name="new_password">
                            </div>

                            <div class="col-12 text-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
