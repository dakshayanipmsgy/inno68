<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
$vendorName = $_SESSION['name'] ?? $_SESSION['user_name'] ?? 'Vendor';

if (!$userId || strcasecmp($role, 'Vendor') !== 0) {
    redirect('dashboard.php');
}

$projectId = $_GET['project_id'] ?? '';
if ($projectId === '') {
    redirect('vendor_dashboard.php?error=' . urlencode('Project not specified.'));
}

$projects = readJSON('projects.json');
$users = readJSON('users.json');

$project = null;
foreach ($projects as $item) {
    $itemId = $item['project_id'] ?? $item['id'] ?? null;
    if ($itemId !== null && (string)$itemId === (string)$projectId) {
        $project = $item;
        break;
    }
}

if (!$project || (string)($project['vendor_id'] ?? '') !== (string)$userId) {
    redirect('vendor_dashboard.php?error=' . urlencode('Project not found.'));
}

$consumerName = 'Consumer';
foreach ($users as $user) {
    if (isset($user['id']) && (string)$user['id'] === (string)($project['consumer_id'] ?? '')) {
        $consumerName = $user['name'] ?? $consumerName;
        break;
    }
}

$status = $project['status'] ?? 'DRAFT';
$title = $project['title'] ?? 'Untitled Project';
$capacity = number_format((float)($project['capacity_kw'] ?? 0), 2);
$totalCost = number_format((float)($project['total_cost'] ?? 0));
$financing = number_format((float)($project['financing_required'] ?? 0));
$createdAt = $project['created_at'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Project | Digital RESCO Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6fb; }
        .page-header {
            background: linear-gradient(120deg, #0f766e, #1d4ed8);
            color: #fff;
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }
        .summary-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.06);
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-start mb-4 page-header">
        <div>
            <p class="mb-1">Project Overview</p>
            <h1 class="h4 fw-bold mb-0"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
            <small>Detailed view of your RESCO proposal.</small>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark mb-2">Status: <?= htmlspecialchars(str_replace('_', ' ', $status), ENT_QUOTES, 'UTF-8') ?></span>
            <div class="mt-1">
                <a href="vendor_dashboard.php" class="btn btn-light text-primary fw-semibold">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card summary-card p-4 h-100">
                <h5 class="fw-semibold mb-3">Project Details</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Customer</p>
                        <h6 class="mb-0"><?= htmlspecialchars($consumerName, ENT_QUOTES, 'UTF-8') ?></h6>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Created At</p>
                        <h6 class="mb-0"><?= htmlspecialchars($createdAt ?: 'Not available', ENT_QUOTES, 'UTF-8') ?></h6>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Capacity</p>
                        <h6 class="mb-0"><?= htmlspecialchars($capacity, ENT_QUOTES, 'UTF-8') ?> kW</h6>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Total Cost</p>
                        <h6 class="mb-0">₹<?= htmlspecialchars($totalCost, ENT_QUOTES, 'UTF-8') ?></h6>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Financing Required</p>
                        <h6 class="mb-0">₹<?= htmlspecialchars($financing, ENT_QUOTES, 'UTF-8') ?></h6>
                    </div>
                </div>
                <div>
                    <p class="text-muted mb-1">Status</p>
                    <span class="badge bg-secondary"><?= htmlspecialchars(str_replace('_', ' ', $status), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card summary-card p-4 h-100">
                <h5 class="fw-semibold mb-3">Financial Breakdown</h5>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Total Cost</span>
                        <strong>₹<?= htmlspecialchars($totalCost, ENT_QUOTES, 'UTF-8') ?></strong>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Financing Required</span>
                        <strong>₹<?= htmlspecialchars($financing, ENT_QUOTES, 'UTF-8') ?></strong>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Estimated Equity (Cost - Financing)</span>
                        <?php
                        $equity = max((float)($project['total_cost'] ?? 0) - (float)($project['financing_required'] ?? 0), 0);
                        ?>
                        <strong>₹<?= htmlspecialchars(number_format($equity), ENT_QUOTES, 'UTF-8') ?></strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
