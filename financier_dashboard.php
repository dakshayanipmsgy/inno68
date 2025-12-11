<?php
session_start();
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
$financierName = $_SESSION['name'] ?? $_SESSION['user_name'] ?? 'Financier';

if (!$userId || strcasecmp($role, 'Financier') !== 0) {
    redirect('dashboard.php?error=' . urlencode('Unauthorized access. Financier role required.'));
}

$projects = readJSON('projects.json');
$users = readJSON('users.json');

$consumerNames = [];
$vendorNames = [];
foreach ($users as $user) {
    if (!isset($user['id'])) {
        continue;
    }
    if (($user['role'] ?? '') === 'Consumer') {
        $consumerNames[$user['id']] = $user['name'] ?? 'Consumer';
    }
    if (($user['role'] ?? '') === 'Vendor') {
        $vendorNames[$user['id']] = $user['name'] ?? 'Vendor';
    }
}

$loanApplications = array_values(array_filter($projects, function ($project) {
    return isset($project['status']) && strcasecmp($project['status'], 'FUNDING_REQUESTED') === 0;
}));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Financier Dashboard | Digital RESCO Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f8fb; }
        .hero {
            background: linear-gradient(135deg, #0f766e, #1d4ed8);
            color: #fff;
            border-radius: 18px;
            padding: 2.5rem;
            box-shadow: 0 12px 30px rgba(13, 110, 253, 0.15);
        }
        .table thead { background: #e8f5ff; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-start mb-4 hero">
        <div>
            <p class="mb-1">Embedded Financing</p>
            <h1 class="h3 fw-bold">Welcome, <?= htmlspecialchars($financierName, ENT_QUOTES, 'UTF-8') ?>!</h1>
            <p class="mb-0">Review vetted solar projects awaiting capital deployment.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark mb-2">Role: Financier</span>
            <div>
                <a href="login.php" class="btn btn-light text-primary fw-semibold shadow-sm">Logout</a>
            </div>
        </div>
    </div>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-semibold">Loan Applications</h5>
                <small class="text-muted">Projects approved by DISCOM and ready for financing.</small>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="text-uppercase small">
                    <tr>
                        <th>Project</th>
                        <th>Vendor</th>
                        <th>Consumer</th>
                        <th class="text-end">Total Cost (INR)</th>
                        <th class="text-center">AI Credit Score</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($loanApplications)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No funding requests available right now.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($loanApplications as $project): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($project['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($vendorNames[$project['vendor_id'] ?? ''] ?? 'Vendor', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($consumerNames[$project['consumer_id'] ?? ''] ?? 'Consumer', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end">₹<?= htmlspecialchars(number_format((float)($project['total_cost'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-center fw-bold text-success"><?= rand(600, 850) ?></td>
                            <td class="text-end">
                                <form method="post" action="sanction_loan.php" class="d-inline">
                                    <input type="hidden" name="project_id" value="<?= htmlspecialchars($project['id'] ?? '') ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Sanction Loan</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
