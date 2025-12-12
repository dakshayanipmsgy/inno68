<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';

if (!$userId || strcasecmp($role, 'MNRE_Admin') !== 0) {
    redirect('dashboard.php?error=' . urlencode('MNRE Admin access required.'));
}

$projects = readJSON('projects.json');

$totalCapacityKw = 0.0;
$totalCapital = 0.0;

foreach ($projects as $project) {
    if (isset($project['status']) && strcasecmp($project['status'], 'LIVE') === 0) {
        $totalCapacityKw += (float)($project['capacity_kw'] ?? 0);
    }

    $totalCapital += (float)($project['loan_amount'] ?? 0);
}

$totalCapacityMw = $totalCapacityKw / 1000;
$carbonAvoidedTons = $totalCapacityMw * 0.82 * 1000; // Simplified annualized factor

$totalBudget = 500000000; // Example MNRE/NISE envelope
$utilizationPercent = $totalBudget > 0 ? min(100, ($totalCapital / $totalBudget) * 100) : 0;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MNRE Oversight | Digital RESCO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #eef2f7; }
        .hero { background: linear-gradient(135deg, #0f766e, #1d4ed8); color: #fff; border-radius: 18px; padding: 2rem; }
    </style>
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>
<div class="container py-4">
    <div class="hero mb-4 d-flex justify-content-between align-items-start">
        <div>
            <p class="mb-1">MNRE / Super Admin</p>
            <h1 class="h3 fw-bold">National Impact Dashboard</h1>
            <p class="mb-0">Monitoring progress toward the 40 GW distributed solar ambition.</p>
        </div>
        <span class="badge bg-light text-dark">Weather Factor: <?= htmlspecialchars(getWeatherFactor(), ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Capacity Deployed (MW)</p>
                    <h3 class="fw-bold"><?= htmlspecialchars(number_format($totalCapacityMw, 2), ENT_QUOTES, 'UTF-8') ?></h3>
                    <small class="text-muted">LIVE projects only</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Carbon Emissions Avoided (tons/yr)</p>
                    <h3 class="fw-bold text-success"><?= htmlspecialchars(number_format($carbonAvoidedTons, 2), ENT_QUOTES, 'UTF-8') ?></h3>
                    <small class="text-muted">Capacity × 0.82 tons/year logic</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Capital Mobilized (INR)</p>
                    <h3 class="fw-bold text-primary">₹<?= htmlspecialchars(number_format($totalCapital, 2), ENT_QUOTES, 'UTF-8') ?></h3>
                    <small class="text-muted">Loan amounts across financed projects</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-0">Deployment Map</h5>
                            <small class="text-muted">Projects by State (simulated)</small>
                        </div>
                    </div>
                    <canvas id="stateChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3">Subsidy Utilization</h5>
                    <p class="text-muted">Funds Disbursed vs. Total Budget</p>
                    <div class="progress mb-2" style="height: 20px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= htmlspecialchars($utilizationPercent, ENT_QUOTES, 'UTF-8') ?>%;" aria-valuenow="<?= htmlspecialchars($utilizationPercent, ENT_QUOTES, 'UTF-8') ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">₹<?= htmlspecialchars(number_format($totalCapital, 2), ENT_QUOTES, 'UTF-8') ?> disbursed</span>
                        <span class="text-muted">Budget: ₹<?= htmlspecialchars(number_format($totalBudget, 0), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const ctx = document.getElementById('stateChart');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jharkhand', 'Odisha', 'Chhattisgarh'],
            datasets: [{
                label: 'Projects',
                data: [12, 5, 3],
                backgroundColor: ['#0f766e', '#1d4ed8', '#f59e0b']
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
