<?php
session_start();
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
$consumerName = $_SESSION['name'] ?? $_SESSION['user_name'] ?? 'Consumer';

if (!$userId || strcasecmp($role, 'Consumer') !== 0) {
    redirect('dashboard.php');
}

$projectId = $_GET['project_id'] ?? '';
if ($projectId === '') {
    redirect('dashboard.php?error=' . urlencode('Project not specified.'));
}

$projects = readJSON('projects.json');
$users = readJSON('users.json');

$project = null;
foreach ($projects as $item) {
    if (isset($item['id']) && (string)$item['id'] === (string)$projectId) {
        $project = $item;
        break;
    }
}

if (!$project || (string)($project['consumer_id'] ?? '') !== (string)$userId) {
    redirect('dashboard.php?error=' . urlencode('You are not authorized to view this project.'));
}

$vendorName = 'Vendor';
$consumerDisplayName = $consumerName;
foreach ($users as $user) {
    if (isset($project['vendor_id']) && isset($user['id']) && (string)$user['id'] === (string)$project['vendor_id']) {
        $vendorName = $user['name'] ?? $vendorName;
    }
    if (isset($user['id']) && (string)$user['id'] === (string)$userId) {
        $consumerDisplayName = $user['name'] ?? $consumerDisplayName;
    }
}

$status = $project['status'] ?? '';
if (strcasecmp($status, 'PENDING_CONSUMER_APPROVAL') !== 0) {
    redirect('dashboard.php?error=' . urlencode('PPA is not available for signing.'));
}

$capacity = number_format((float)($project['capacity_kw'] ?? 0), 2);
$cost = number_format((float)($project['total_cost'] ?? 0));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Power Purchase Agreement | Digital RESCO Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #eef2f7; }
        .agreement-card {
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.06);
        }
        .terms-box {
            max-height: 260px;
            overflow-y: auto;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 1rem;
            border-radius: 8px;
        }
        .clause-title { font-weight: 600; color: #0f172a; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-muted small mb-1">Digital Approval</p>
            <h1 class="h4 fw-bold">Power Purchase Agreement (Standardized RESCO Model)</h1>
            <p class="mb-0 text-secondary">Review and digitally sign to authorize net-metering submission.</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="agreement-card p-4">
        <div class="mb-4">
            <p class="text-muted mb-1">Agreement Parties</p>
            <h2 class="h5">This agreement is between <strong><?= htmlspecialchars($vendorName, ENT_QUOTES, 'UTF-8') ?></strong> and <strong><?= htmlspecialchars($consumerDisplayName, ENT_QUOTES, 'UTF-8') ?></strong> for a <strong><?= htmlspecialchars($capacity, ENT_QUOTES, 'UTF-8') ?>kW</strong> system at a cost of <strong>₹<?= htmlspecialchars($cost, ENT_QUOTES, 'UTF-8') ?></strong>.</h2>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 mb-0">Terms &amp; Conditions</h3>
                <span class="badge bg-light text-dark">Standardized PPA</span>
            </div>
            <div class="terms-box">
                <p class="clause-title">1. Scope of Work</p>
                <p class="text-secondary small">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sit amet pretium erat. Praesent vitae nisl sed leo pretium facilisis. Integer nec porttitor nisi. Proin vel tellus sit amet enim vestibulum suscipit.</p>
                <p class="clause-title">2. Tariff &amp; Payment Terms</p>
                <p class="text-secondary small">Aliquam erat volutpat. Curabitur aliquet, erat at feugiat scelerisque, lacus elit convallis urna, vitae suscipit sem neque at nibh. Suspendisse potenti. Sed at neque vel velit dignissim dignissim.</p>
                <p class="clause-title">3. Grid Interconnection &amp; Net-Metering</p>
                <p class="text-secondary small">Suspendisse dapibus nisi a augue egestas, in tristique neque egestas. Fusce accumsan, lacus id facilisis fringilla, nulla sapien dictum est, nec vulputate sapien ipsum sit amet orci.</p>
                <p class="clause-title">4. Tenure &amp; Termination</p>
                <p class="text-secondary small">Curabitur vel ultrices erat. Vestibulum fringilla efficitur nunc, id lobortis velit rhoncus non. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p>
                <p class="clause-title">5. Maintenance &amp; Performance</p>
                <p class="text-secondary small">Morbi varius nibh vel feugiat viverra. Proin iaculis erat vitae leo interdum, vitae porttitor nulla accumsan. Mauris lacinia tristique orci, ut elementum nunc convallis in.</p>
                <p class="clause-title">6. Dispute Resolution</p>
                <p class="text-secondary small">Quisque dignissim justo vitae risus feugiat, vitae interdum sapien porttitor. Donec nec sem sit amet leo vehicula consequat. Donec ac diam sit amet urna hendrerit fermentum non at orci.</p>
            </div>
        </div>

        <form method="post" action="sign_ppa_action.php">
            <input type="hidden" name="project_id" value="<?= htmlspecialchars($projectId, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="accept_terms" name="accept_terms" required>
                <label class="form-check-label" for="accept_terms">
                    I, <?= htmlspecialchars($consumerDisplayName, ENT_QUOTES, 'UTF-8') ?>, accept the terms and conditions and authorize the net-metering application.
                </label>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Digitally Sign &amp; Approve</button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
