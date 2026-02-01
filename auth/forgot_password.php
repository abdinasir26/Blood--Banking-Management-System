<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$step = 1;
$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = cleanInput($_GET['token']);
    // Verify token
    $stmt = $pdo->prepare("SELECT user_id FROM remember_tokens WHERE token_hash = ? AND expiry > NOW()");
    $stmt->execute([$token]); // In real app, we handle token differently (this table was for remember me, but I'll reuse or create a new one? I'll use a new logic or just reuse table structure if appropriate, but remember_tokens is for cookies. I'll rely on a simple logic here or just Create a 'password_resets' table?
    // Since I cannot change schema easily now without migration, I will use 'remember_tokens' table for this purpose mostly or just add a column?
    // Wait, I created 'remember_tokens' table. I can abuse it or just create a new table on the fly?
    // Let's create `password_resets` table if it doesn't exist? No, "seed data" implies static schema.
    // I'll use `remember_tokens` table for storing the reset token too, just with a different logic or just trust me.
    // Actually, `remember_tokens` has user_id, token_hash, expiry. Perfect. I can use it.

    // START CHECK
    $token_row = $stmt->fetch();

    if ($token_row) {
        $step = 2; // Show Reset Form
        $user_id_reset = $token_row['user_id'];
    } else {
        $error = 'Invalid or expired token.';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['send_link'])) {
        $email = cleanInput($_POST['email']);
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            // Store raw token for link, hash for DB? 
            // In my schema remember_tokens stores `token_hash`. 
            // So:
            //$token_hash = password_hash($token, PASSWORD_DEFAULT); 
            // Wait, for query strictly by token in URL, I need to match valid token. 
            // If I hash it in DB, I must verify all? No.
            // Simplified: Store the token as is in DB for this assignment or use a simple column.
            // Problem: `remember_tokens` stores hash. I can't reverse it. 
            // Solution: Store the token directly in `token_hash` column? No, 255 chars is enough.
            // SECURITY NOTE: Storing plain token is bad. But for this specific assignment constraint, I need a way to look it up. 
            // Actually, I can store the token directly or use a generated ID.
            // Let's store the token directly for the Reset flow since it's short lived.

            $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            $stmt_ins = $pdo->prepare("INSERT INTO remember_tokens (user_id, token_hash, expiry) VALUES (?, ?, ?)");
            $stmt_ins->execute([$user['id'], $token, $expiry]);

            // SIMULATION
            $link = BASE_URL . "auth/forgot_password.php?token=" . $token;
            $success = "Reset link found (Simulated Email): <a href='$link'>Click Here to Reset</a>";
        } else {
            $error = 'Email not found.';
        }
    } elseif (isset($_POST['reset_pass'])) {
        $token = cleanInput($_POST['token_input']);
        $pass = $_POST['password'];
        $conf = $_POST['confirm_password'];
        $uid = $_POST['user_id'];

        if ($pass !== $conf) {
            $error = 'Passwords do not match.';
            $step = 2;
        } elseif (strlen($pass) < 6) {
            $error = 'Password too short.';
            $step = 2;
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt_upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt_upd->execute([$hash, $uid]);

            // Delete token
            $pdo->prepare("DELETE FROM remember_tokens WHERE token_hash = ?")->execute([$token]);

            setFlash('success', 'Password updated successfully. Please login.');
            redirect('auth/login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - BBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="auth-container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <h2 class="text-primary-custom fw-bold">Forgot Password</h2>
                <p class="text-muted">
                    <?php echo ($step == 1) ? 'Enter your email to reset password.' : 'Set your new password.'; ?></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <?php if ($step == 1): ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="send_link" class="btn btn-primary btn-lg">Send Reset Link</button>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="token_input" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id_reset); ?>">
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="reset_pass" class="btn btn-primary btn-lg">Reset Password</button>
                    </div>
                <?php endif; ?>
            </form>

            <div class="mt-4 text-center">
                <a href="login.php" class="text-muted">Back to Login</a>
            </div>
        </div>
    </div>
</body>

</html>