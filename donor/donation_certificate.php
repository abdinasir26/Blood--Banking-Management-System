<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
if (isAdmin()) {
    redirect('admin/dashboard.php');
}

$donation_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$uid = $_SESSION['user_id'];

if ($donation_id <= 0) {
    redirect('donor/donations.php');
}

$stmt = $pdo->prepare("
    SELECT d.id, d.amount, d.created_at, d.approved_at, bg.group_name, u.first_name, u.last_name
    FROM donations d
    JOIN blood_groups bg ON d.blood_group_id = bg.id
    JOIN users u ON d.donor_id = u.id
    WHERE d.id = ? AND d.donor_id = ? AND d.status = 'approved'
    LIMIT 1
");
$stmt->execute([$donation_id, $uid]);
$donation = $stmt->fetch();

if (!$donation) {
    redirect('donor/donations.php');
}

$donor_name = trim($donation['first_name'] . ' ' . $donation['last_name']);
$approved_date = $donation['approved_at'] ?: $donation['created_at'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Certificate #<?php echo (int) $donation['id']; ?></title>
    <style>
        :root {
            --primary: #b42323;
            --border: #8f8f8f;
            --text: #222;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #f3f4f6;
            color: var(--text);
            font-family: Georgia, "Times New Roman", serif;
        }

        .actions {
            text-align: right;
            margin-bottom: 16px;
        }

        .actions button {
            border: 0;
            background: var(--primary);
            color: #fff;
            padding: 10px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .certificate {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border: 8px solid #c5a35a;
            outline: 2px solid var(--border);
            outline-offset: -18px;
            padding: 48px 42px;
        }

        .title {
            text-align: center;
            font-size: 36px;
            margin: 10px 0 4px;
            letter-spacing: 0.5px;
            color: var(--primary);
        }

        .subtitle {
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 13px;
            margin-bottom: 28px;
        }

        .line {
            text-align: center;
            font-size: 22px;
            margin: 10px 0;
        }

        .line strong {
            color: var(--primary);
        }

        .details {
            margin-top: 30px;
            width: 100%;
            border-collapse: collapse;
        }

        .details td {
            border: 1px solid #d1d5db;
            padding: 10px 12px;
            font-size: 15px;
        }

        .details td:first-child {
            width: 35%;
            font-weight: 700;
            background: #fafafa;
        }

        .signature-wrap {
            margin-top: 44px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .signature {
            width: 48%;
            text-align: center;
            font-size: 14px;
        }

        .signature .sign-line {
            border-top: 1px solid #444;
            margin-bottom: 10px;
            padding-top: 6px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .actions {
                display: none;
            }

            .certificate {
                border-width: 6px;
                outline: none;
                max-width: none;
                margin: 0;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="actions">
        <button onclick="window.print()">Print / Save as PDF</button>
    </div>
    <main class="certificate">
        <h1 class="title">Donation Certificate</h1>
        <p class="subtitle">Blood Banking Management System</p>

        <p class="line">This certifies that</p>
        <p class="line"><strong><?php echo htmlspecialchars($donor_name); ?></strong></p>
        <p class="line">has successfully donated</p>
        <p class="line"><strong><?php echo (int) $donation['amount']; ?> unit(s)</strong> of blood group
            <strong><?php echo htmlspecialchars($donation['group_name']); ?></strong>
        </p>
        <p class="line">for the humanitarian mission of BBMS.</p>

        <table class="details">
            <tr>
                <td>Certificate No.</td>
                <td>BBMS-DC-<?php echo str_pad((string) $donation['id'], 6, '0', STR_PAD_LEFT); ?></td>
            </tr>
            <tr>
                <td>Donation ID</td>
                <td>#<?php echo (int) $donation['id']; ?></td>
            </tr>
            <tr>
                <td>Issued Date</td>
                <td><?php echo date('F d, Y', strtotime($approved_date)); ?></td>
            </tr>
        </table>

        <div class="signature-wrap">
            <div class="signature">
                <div class="sign-line">Donor Signature</div>
                <?php echo htmlspecialchars($donor_name); ?>
            </div>
            <div class="signature">
                <div class="sign-line">Authorized By</div>
                BBMS Administration
            </div>
        </div>
    </main>
</body>

</html>
