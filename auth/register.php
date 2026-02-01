<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

// Initialize variables
$first_name = $last_name = $username = $email = $phone = $sex = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = cleanInput($_POST['first_name']);
    $last_name = cleanInput($_POST['last_name']);
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $phone = cleanInput($_POST['phone']);
    $sex = cleanInput($_POST['sex']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($phone) || empty($password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check uniqueness
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $error = 'Username or Email already exists.';
        } else {
            // Register User
            $blood_group_id = cleanInput($_POST['blood_group_id']);
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (first_name, last_name, sex, blood_group_id, username, email, phone, password_hash, user_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'donor', 'active')";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$first_name, $last_name, $sex, $blood_group_id, $username, $email, $phone, $password_hash])) {
                setFlash('success', 'Registration successful! Please login.');
                redirect('auth/login.php');
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BBMS</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        /* Specific overrides for registration page to handle content height */
        .register-page .login-card {
            max-width: 1100px;
            overflow: visible;
            /* Allow content to flow */
        }

        .register-page .login-right {
            padding: 40px 50px;
        }

        /* Ensure specific form styling */
        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Fix for potential vertical overflow in flexbox for tall form */
        .register-page.login-bg {
            height: auto;
            min-height: 100vh;
            align-items: center;
            /* Center by default */
        }

        @media (max-height: 900px) {
            .register-page.login-bg {
                /* On shorter screens, align top to allow scrolling */
                align-items: flex-start;
                padding-top: 40px;
                padding-bottom: 40px;
            }
        }
    </style>
</head>

<body class="login-bg register-page">
    <div class="login-card">
        <!-- Left Side: Image -->
        <div class="login-left">
            <div class="h-100 w-100 d-flex flex-column justify-content-end p-5 text-white"
                style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                <h1 class="fw-bold display-4 mb-2">Join Our Community</h1>
                <p class="fs-5 opacity-75">Become a donor and help save lives today. Your contribution matters.</p>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="login-right">
            <div class="mb-4">
                <h2 class="fw-bold text-dark mb-1">Create Account</h2>
                <p class="text-muted">Enter your details to register</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 fs-6 rounded-3 mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control form-control-line" name="first_name"
                            value="<?php echo htmlspecialchars($first_name); ?>" required placeholder="John">
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control form-control-line" name="last_name"
                            value="<?php echo htmlspecialchars($last_name); ?>" required placeholder="Doe">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="sex" class="form-label">Sex</label>
                        <select class="form-select form-control-line" name="sex" required>
                            <option value="Male" <?php echo ($sex == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($sex == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($sex == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="blood_group_id" class="form-label">Blood Group</label>
                        <select class="form-select form-control-line" name="blood_group_id" required>
                            <option value="">Select Group</option>
                            <?php
                            $groups = $pdo->query("SELECT * FROM blood_groups")->fetchAll();
                            foreach ($groups as $group) {
                                echo '<option value="' . $group['id'] . '">' . $group['group_name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control form-control-line" name="username"
                        value="<?php echo htmlspecialchars($username); ?>" required placeholder="johndoe123">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control form-control-line" name="email"
                            value="<?php echo htmlspecialchars($email); ?>" required placeholder="john@example.com">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control form-control-line" name="phone"
                            value="<?php echo htmlspecialchars($phone); ?>" required pattern="[0-9]{10,15}"
                            title="Phone number must be numeric" placeholder="+1234567890">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control form-control-line" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirm</label>
                        <input type="password" class="form-control form-control-line" name="confirm_password" required>
                    </div>
                </div>

                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-purple btn-lg shadow-sm">
                        Create Account
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-muted mb-0">Already have an account?
                        <a href="login.php" class="text-decoration-none fw-bold"
                            style="color: var(--primary-color);">Login here</a>
                    </p>
                    <p class="mt-2 text-small">
                        <a href="../index.php" class="text-muted text-decoration-none small"><i
                                class="fas fa-arrow-left me-1"></i> Back to Home</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>