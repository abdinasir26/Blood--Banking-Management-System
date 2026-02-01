<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();

// Fetch blood groups
$groups = $pdo->query("SELECT * FROM blood_groups")->fetchAll();

// Handle Form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $donor_id = $_SESSION['user_id'];
    $blood_group_id = $_POST['blood_group_id'];
    $amount = (int) $_POST['amount'];
    $disease = cleanInput($_POST['disease_notes']);

    if ($amount > 0 && !empty($blood_group_id)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO donations (donor_id, blood_group_id, amount, disease_notes, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$donor_id, $blood_group_id, $amount, $disease]);
            setFlash('success', 'Thank you! Your donation request has been submitted for approval.');
            redirect('donor/dashboard.php');
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Blood - BBMS</title>
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
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="form-card animate-fade-up">
                    <div class="text-center mb-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle p-3 mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="fas fa-hand-holding-heart fa-3x"></i>
                        </div>
                        <h2 class="fw-bold text-dark">Donate Blood</h2>
                        <p class="text-muted">Fill out the form below to schedule a donation.</p>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger rounded-3"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="blood_group_id" name="blood_group_id" required>
                                        <option value="" selected disabled>Select Group</option>
                                        <?php foreach ($groups as $g): ?>
                                            <option value="<?php echo $g['id']; ?>"><?php echo $g['group_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="blood_group_id">Blood Group</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="amount" name="amount"
                                        placeholder="Units" min="1" required>
                                    <label for="amount">Units to Donate</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Any diseases or notes"
                                        id="disease_notes" name="disease_notes" style="height: 120px"></textarea>
                                    <label for="disease_notes">Previous Diseases / Medical Notes (Optional)</label>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary-custom w-100 py-3 fs-5">
                                    Submit Donation Request
                                </button>
                            </div>
                            <div class="col-12 text-center mt-3">
                                <a href="dashboard.php" class="text-muted text-decoration-none">Cancel</a>
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