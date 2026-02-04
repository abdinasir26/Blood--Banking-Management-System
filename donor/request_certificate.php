<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireLogin();
if (isAdmin()) {
    redirect('admin/dashboard.php');
}

$request_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$uid = $_SESSION['user_id'];

if ($request_id <= 0) {
    redirect('donor/requests.php');
}

$stmt = $pdo->prepare("
    SELECT r.id, r.amount, r.hospital_name, r.urgency, r.created_at, r.approved_at, bg.group_name, u.first_name, u.last_name
    FROM requests r
    JOIN blood_groups bg ON r.blood_group_id = bg.id
    JOIN users u ON r.requester_id = u.id
    WHERE r.id = ? AND r.requester_id = ? AND r.status = 'approved'
    LIMIT 1
");
$stmt->execute([$request_id, $uid]);
$request = $stmt->fetch();

if (!$request) {
    redirect('donor/requests.php');
}

$requester_name = trim($request['first_name'] . ' ' . $request['last_name']);
$approved_date = $request['approved_at'] ?: $request['created_at'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Certificate #<?php echo (int) $request['id']; ?></title>
    <style>
        :root {
            --primary: #0d4fbb;
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
            border: 8px solid #7ea9ea;
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
        <h1 class="title">Request Certificate</h1>
        <p class="subtitle">Blood Banking Management System</p>

        <p class="line">This certifies that the blood request by</p>
        <p class="line"><strong><?php echo htmlspecialchars($requester_name); ?></strong></p>
        <p class="line">for <strong><?php echo (int) $request['amount']; ?> unit(s)</strong> of
            <strong><?php echo htmlspecialchars($request['group_name']); ?></strong>
            has been approved for
            <strong><?php echo htmlspecialchars($request['hospital_name']); ?></strong>.
        </p>

        <table class="details">
            <tr>
                <td>Certificate No.</td>
                <td>BBMS-RC-<?php echo str_pad((string) $request['id'], 6, '0', STR_PAD_LEFT); ?></td>
            </tr>
            <tr>
                <td>Request ID</td>
                <td>#<?php echo (int) $request['id']; ?></td>
            </tr>
            <tr>
                <td>Urgency</td>
                <td><?php echo ucfirst(htmlspecialchars($request['urgency'])); ?></td>
            </tr>
            <tr>
                <td>Issued Date</td>
                <td><?php echo date('F d, Y', strtotime($approved_date)); ?></td>
            </tr>
        </table>

        <div class="signature-wrap">
            <div class="signature">
                <div class="sign-line">Requester Signature</div>
                <?php echo htmlspecialchars($requester_name); ?>
            </div>
            <div class="signature">
                <div class="sign-line">Authorized By</div>
                BBMS Administration
            </div>
        </div>
    </main>
</body>

</html>
