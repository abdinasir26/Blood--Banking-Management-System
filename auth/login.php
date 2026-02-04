<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('donor/dashboard.php');
    }
}

$error = '';
$username = '';

if (isset($_GET['logged_out'])) {
    $error = 'You have been successfully logged out.';
    // Or use a success style:
    // $success_msg = '...';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = cleanInput($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $sql = "SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username' => $username, ':email' => $username]);
        $user = $stmt->fetch();

        $password_ok = false;
        if ($user) {
            $stored = $user['password_hash'];
            if (password_verify($password, $stored)) {
                $password_ok = true;
            } else {
                // Fallback: if the stored value is a plain password (not a bcrypt/hash), allow login
                // then upgrade to a bcrypt hash for future logins.
                if (strpos($stored, '$2') !== 0 && $password === $stored) {
                    $password_ok = true;
                    // Re-hash the plain password and update the DB
                    try {
                        $new_hash = password_hash($password, PASSWORD_BCRYPT);
                        $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                        $updateStmt->execute([$new_hash, $user['id']]);
                    } catch (Exception $e) {
                        // If updating fails, continue with login but do not block the user
                    }
                }
            }
        }

        if ($user && $password_ok) {
            if ($user['status'] == 'not_active') {
                $error = 'Your account is inactive. Please contact admin.';
            } else {
                // Set Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['last_activity'] = time();

                // Handle Remember Me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days

                    // Store token in DB (Assuming remember_tokens table exists as per schema)
                    $token_hash = password_hash($token, PASSWORD_DEFAULT);
                    $sql_token = "INSERT INTO remember_tokens (user_id, token_hash, expiry) VALUES (:user_id, :token_hash, :expiry)";
                    $stmt_token = $pdo->prepare($sql_token);
                    $stmt_token->execute([':user_id' => $user['id'], ':token_hash' => $token_hash, ':expiry' => $expiry]);

                    setcookie('remember_token', $token, time() + (86400 * 30), "/", "", false, true);
                    setcookie('remember_user', $user['id'], time() + (86400 * 30), "/", "", false, true);
                }

                setFlash('success', 'Welcome back, ' . $user['first_name'] . '!');

                if ($user['user_type'] == 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('donor/dashboard.php');
                }
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BBMS</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>

<body class="login-bg">
    <div class="login-card">
        <!-- Left Side: Image -->
        <div class="login-left"></div>

        <!-- Right Side: Form -->
        <div class="login-right">
            <!-- Removed headers as requested -->


            <?php if ($error): ?>
                <div class="alert alert-danger py-2 fs-6 rounded-3"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php $flash = getFlash();
            if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> py-2 fs-6 rounded-3"><?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-4">
                    <label for="username" class="form-label text-muted small fw-bold">USERNAME</label>
                    <input type="text" class="form-control form-control-line" id="username" name="username"
                        value="<?php echo $username; ?>" required placeholder="">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label text-muted small fw-bold">PASSWORD</label>
                    <input type="password" class="form-control form-control-line" id="password" name="password" required
                        placeholder="">
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label text-muted small" for="remember">Remember me</label>
                    </div>
                    <a href="forgot_password.php" class="text-decoration-none text-muted small">forget password?</a>
                </div>

                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-purple btn-lg">Login</button>
                </div>

                <div class="text-center">
                    <a href="register.php" class="text-decoration-none text-muted small">Create Account</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
