<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$vendorName = $_SESSION['name'] ?? $_SESSION['user_name'] ?? 'Vendor';
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';

if (!$userId || strcasecmp($role, 'Vendor') !== 0) {
    redirect('dashboard.php');
}

$projects = readJSON('projects.json');
$users = readJSON('users.json');
$transactions = readJSON('transactions.json');

// Map consumer IDs to names for easy lookup.
$consumerNames = [];
foreach ($users as $user) {
    if (($user['role'] ?? '') === 'Consumer' && isset($user['id'])) {
        $consumerNames[$user['id']] = $user['name'] ?? 'Consumer';
    }
}

// Filter projects for the logged-in vendor.
$myProjects = array_values(array_filter($projects, function ($project) use ($userId) {
    return isset($project['vendor_id']) && (string)$project['vendor_id'] === (string)$userId;
}));

$vendorEarnings = 0.0;
foreach ($transactions as $txn) {
    if (($txn['to'] ?? '') === 'Vendor' && isset($txn['vendor_id']) && (string)$txn['vendor_id'] === (string)$userId) {
        $vendorEarnings += (float)($txn['amount'] ?? 0);
    }
}

// Pipeline and impact metrics
$appliedStatuses = ['DRAFT', 'PENDING_CONSUMER_APPROVAL', 'SUBMITTED'];
$approvedStatuses = ['APPROVED', 'FUNDING_REQUESTED'];
$liveStatuses = ['LIVE'];

$pipelineCounts = [
    'Applied' => 0,
    'Approved' => 0,
    'Live' => 0,
];

$installedCapacity = 0.0;

foreach ($myProjects as $project) {
    $status = strtoupper($project['status'] ?? 'DRAFT');
    if (in_array($status, $appliedStatuses, true)) {
        $pipelineCounts['Applied']++;
    } elseif (in_array($status, $approvedStatuses, true)) {
        $pipelineCounts['Approved']++;
    } elseif (in_array($status, $liveStatuses, true)) {
        $pipelineCounts['Live']++;
    }

    if (in_array($status, $liveStatuses, true)) {
        $installedCapacity += (float)($project['capacity_kw'] ?? 0);
    }
}

$co2Offset = $installedCapacity * 0.8;

// Status badge helper
function statusBadgeClass(string $status): string
{
    $map = [
        'DRAFT' => 'warning',
        'PENDING_CONSUMER_APPROVAL' => 'info',
        'SUBMITTED' => 'primary',
        'APPROVED' => 'success',
        'REJECTED' => 'danger',
    ];

    return 'bg-' . ($map[strtoupper($status)] ?? 'secondary');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vendor Dashboard | Digital RESCO Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f8fafc; }
        .page-header {
            background: linear-gradient(120deg, #0f766e, #1d4ed8);
            color: #fff;
            border-radius: 18px;
            padding: 2.5rem;
            box-shadow: 0 12px 30px rgba(15, 118, 110, 0.2);
        }
        .table thead { background: #e5f3ff; }
        .badge { text-transform: uppercase; letter-spacing: 0.4px; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-start mb-4 page-header">
        <div>
            <p class="mb-1">Vendor Workspace</p>
            <h1 class="h3 fw-bold">Welcome back, <?= htmlspecialchars($vendorName, ENT_QUOTES, 'UTF-8') ?>!</h1>
            <p class="mb-0">Manage your RESCO proposals and collaborate with consumers.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark mb-2">Role: Vendor</span>
            <div class="d-flex gap-2 justify-content-end">
                <a href="create_project.php" class="btn btn-light text-primary fw-semibold shadow-sm">+ Create New Project</a>
                <a href="logout.php" class="btn btn-outline-light fw-semibold shadow-sm text-dark">Logout</a>
            </div>
        </div>
    </div>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Project proposal created successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Financial Overview</p>
                    <h4 class="fw-bold text-success">₹<?= htmlspecialchars(number_format($vendorEarnings, 2), ENT_QUOTES, 'UTF-8') ?></h4>
                    <p class="small text-muted mb-0">Total earnings from paid consumer bills.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Capacity Installed (kW)</p>
                    <h4 class="fw-bold"><?= htmlspecialchars(number_format($installedCapacity, 2), ENT_QUOTES, 'UTF-8') ?></h4>
                    <p class="small text-muted mb-0">Across live RESCO deployments.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">CO2 Offset (Tons)</p>
                    <h4 class="fw-bold text-primary"><?= htmlspecialchars(number_format($co2Offset, 2), ENT_QUOTES, 'UTF-8') ?></h4>
                    <p class="small text-muted mb-0">Estimated using 0.8 ton per kW.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-semibold">Pipeline Overview</h5>
                <small class="text-muted">Applied vs. Approved vs. Live</small>
            </div>
        </div>
        <div class="card-body">
            <canvas id="pipelineChart" height="120"></canvas>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-semibold">My Projects</h5>
                <small class="text-muted">Track proposals and their approval status.</small>
            </div>
            <a href="create_project.php" class="btn btn-primary">Create New Project</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="text-uppercase small">
                    <tr>
                        <th>Project Title</th>
                        <th>Assigned Consumer</th>
                        <th class="text-end">Capacity (kW)</th>
                        <th class="text-end">Total Cost (INR)</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($myProjects)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No projects yet. Start by creating your first proposal.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($myProjects as $project): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($project['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($consumerNames[$project['consumer_id'] ?? ''] ?? 'Unassigned', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end"><?= htmlspecialchars(number_format((float)($project['capacity_kw'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end">₹<?= htmlspecialchars(number_format((float)($project['total_cost'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php $status = $project['status'] ?? 'DRAFT'; ?>
                                <span class="badge <?= statusBadgeClass($status); ?>"><?= htmlspecialchars(str_replace('_', ' ', $status), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="#" class="btn btn-outline-primary btn-sm">View</a>
                                    <a href="#" class="btn btn-outline-secondary btn-sm">Edit</a>
                                </div>
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
<script>
    const pipelineCtx = document.getElementById('pipelineChart');
    if (pipelineCtx) {
        new Chart(pipelineCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($pipelineCounts)); ?>,
                datasets: [{
                    label: 'Projects',
                    data: <?= json_encode(array_values($pipelineCounts)); ?>,
                    backgroundColor: ['#0f766e', '#f59e0b', '#1d4ed8'],
                    borderRadius: 8,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    }
</script>
</body>
</html>
