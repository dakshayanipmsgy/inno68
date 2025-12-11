<?php
session_start();
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
$consumerName = $_SESSION['name'] ?? $_SESSION['user_name'] ?? 'Consumer';

if (!$userId || strcasecmp($role, 'Consumer') !== 0) {
    redirect('dashboard.php');
}

$projects = readJSON('projects.json');
$users = readJSON('users.json');
$bills = readJSON('bills.json');

$vendorNames = [];
foreach ($users as $user) {
    if (($user['role'] ?? '') === 'Vendor' && isset($user['id'])) {
        $vendorNames[$user['id']] = $user['name'] ?? 'Vendor';
    }
}

$myProjects = array_values(array_filter($projects, function ($project) use ($userId) {
    return isset($project['consumer_id']) && (string)$project['consumer_id'] === (string)$userId;
}));

$myBills = array_values(array_filter($bills, function ($bill) use ($userId) {
    return isset($bill['consumer_id']) && (string)$bill['consumer_id'] === (string)$userId && strcasecmp($bill['status'] ?? '', 'UNPAID') === 0;
}));

function statusBadge(string $status): string
{
    $status = strtoupper($status);
    $map = [
        'PENDING_CONSUMER_APPROVAL' => 'info',
        'PENDING_DISCOM_APPROVAL' => 'warning',
        'LIVE' => 'success',
        'DRAFT' => 'secondary',
    ];

    return 'bg-' . ($map[$status] ?? 'secondary');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consumer Dashboard | Digital RESCO Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f8fb; }
        .page-hero {
            background: linear-gradient(135deg, #1d4ed8, #0f766e);
            color: #fff;
            border-radius: 18px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }
        .table thead { background: #e5f3ff; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-start mb-4 page-hero">
        <div>
            <p class="mb-1">Consumer Workspace</p>
            <h1 class="h3 fw-bold">Welcome, <?= htmlspecialchars($consumerName, ENT_QUOTES, 'UTF-8') ?>!</h1>
            <p class="mb-0">Review vendor proposals, track live output, and pay your bills.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark mb-2">Role: Consumer</span>
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

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Live System Performance</p>
                    <h4 class="fw-bold text-success">Current Output: 2.5 kW</h4>
                    <p class="small text-muted mb-0">Mock telemetry feed to demonstrate real-time visibility.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-semibold">Pending Bills</h5>
                        <small class="text-muted">Auto-generated after each billing cycle.</small>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="text-uppercase small">
                            <tr>
                                <th>Units (kWh)</th>
                                <th>Amount (INR)</th>
                                <th>EMI (INR)</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($myBills)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No unpaid bills right now.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($myBills as $bill): ?>
                                <tr>
                                    <td><?= htmlspecialchars(number_format((float)($bill['generation_units'] ?? 0), 0), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>₹<?= htmlspecialchars(number_format((float)($bill['bill_amount'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>₹<?= htmlspecialchars(number_format((float)($bill['emi_due'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-end">
                                        <form method="post" action="pay_bill.php" class="d-inline">
                                            <input type="hidden" name="bill_id" value="<?= htmlspecialchars($bill['id'] ?? '') ?>">
                                            <button type="submit" class="btn btn-success btn-sm">Pay Bill</button>
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
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-semibold">My Solar Projects</h5>
                <small class="text-muted">Track proposals shared by your vendor.</small>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="text-uppercase small">
                    <tr>
                        <th>Vendor Name</th>
                        <th>Capacity (kW)</th>
                        <th>Cost (INR)</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($myProjects)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No projects assigned yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($myProjects as $project): ?>
                        <?php $status = $project['status'] ?? 'DRAFT'; ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($vendorNames[$project['vendor_id'] ?? ''] ?? 'Vendor', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(number_format((float)($project['capacity_kw'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>₹<?= htmlspecialchars(number_format((float)($project['total_cost'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge <?= statusBadge($status); ?>"><?= htmlspecialchars(str_replace('_', ' ', $status), ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td class="text-end">
                                <?php if (strcasecmp($status, 'PENDING_CONSUMER_APPROVAL') === 0): ?>
                                    <a href="view_ppa.php?project_id=<?= urlencode($project['id'] ?? '') ?>" class="btn btn-primary btn-sm">Review &amp; Sign PPA</a>
                                <?php elseif (strcasecmp($status, 'LIVE') === 0): ?>
                                    <a href="#" class="btn btn-outline-success btn-sm">View Dashboard</a>
                                <?php else: ?>
                                    <span class="text-muted small">No actions available</span>
                                <?php endif; ?>
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
