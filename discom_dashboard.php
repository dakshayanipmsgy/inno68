<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
$discomName = $_SESSION['name'] ?? $_SESSION['user_name'] ?? 'DISCOM Official';

if (!$userId || strcasecmp($role, 'DISCOM') !== 0) {
    redirect('dashboard.php');
}

$projects = readJSON('projects.json');
$users = readJSON('users.json');

$consumerNames = [];
$vendorNames = [];
foreach ($users as $user) {
    if (!isset($user['id'])) {
        continue;
    }

    $userRole = $user['role'] ?? '';
    if (strcasecmp($userRole, 'Consumer') === 0) {
        $consumerNames[$user['id']] = $user['name'] ?? 'Consumer';
    } elseif (strcasecmp($userRole, 'Vendor') === 0) {
        $vendorNames[$user['id']] = $user['name'] ?? 'Vendor';
    }
}

$pendingProjects = array_values(array_filter($projects, function ($project) {
    return strcasecmp($project['status'] ?? '', 'PENDING_DISCOM_APPROVAL') === 0;
}));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DISCOM Dashboard | Digital RESCO Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f8fb; }
        .page-hero {
            background: linear-gradient(135deg, #0f52ba, #22c55e);
            color: #fff;
            border-radius: 18px;
            padding: 2.5rem;
            box-shadow: 0 12px 30px rgba(15, 82, 186, 0.2);
        }
        .card-header { background: #f1f5f9; }
        .badge-status { text-transform: uppercase; letter-spacing: 0.4px; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-start mb-4 page-hero">
        <div>
            <p class="mb-1">DISCOM Operations</p>
            <h1 class="h3 fw-bold">Welcome, <?= htmlspecialchars($discomName, ENT_QUOTES, 'UTF-8') ?>!</h1>
            <p class="mb-0">Review grid connectivity requests and approve net metering.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark mb-2">Role: DISCOM</span>
            <div>
                <a href="logout.php" class="btn btn-light text-primary fw-semibold shadow-sm">Logout</a>
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
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-semibold">Grid Connectivity Requests</h5>
                <small class="text-muted">Projects awaiting DISCOM approval for net metering.</small>
            </div>
            <span class="badge bg-warning text-dark badge-status">Pending Approval</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th>Project ID</th>
                        <th>Consumer Name</th>
                        <th>Vendor Name</th>
                        <th class="text-end">Capacity (kW)</th>
                        <th>PPA Signed Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($pendingProjects)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No pending grid approvals.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendingProjects as $project): ?>
                        <tr>
                            <td class="fw-semibold">#<?= htmlspecialchars($project['id'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($consumerNames[$project['consumer_id'] ?? ''] ?? 'Consumer', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($vendorNames[$project['vendor_id'] ?? ''] ?? 'Vendor', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end"><?= htmlspecialchars(number_format((float)($project['capacity_kw'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($project['ppa_signed_date'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end">
                                <form action="approve_grid.php" method="post" class="d-inline">
                                    <input type="hidden" name="project_id" value="<?= htmlspecialchars($project['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn btn-success btn-sm fw-semibold">Approve Net Metering</button>
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
