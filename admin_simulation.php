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

$message = '';
$weatherFactor = getWeatherFactor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'sunny') {
        setWeatherFactor(1.0);
        $weatherFactor = 1.0;
        $message = 'Weather factor set to SUNNY (1.0).';
    } elseif ($action === 'cloudy') {
        setWeatherFactor(0.4);
        $weatherFactor = 0.4;
        $message = 'Weather factor set to CLOUDY (0.4).';
    } elseif ($action === 'run_billing') {
        $runCount = runBillingCycle($weatherFactor);
        $message = 'Billing cycle executed for ' . $runCount . ' live projects using factor ' . $weatherFactor . '.';
    } elseif ($action === 'reset_demo') {
        writeJSON('projects.json', []);
        writeJSON('bills.json', []);
        $message = 'Demo data has been reset.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simulation Control Center | Digital RESCO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6fb; }
        .control-card { border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .big-btn { padding: 1.25rem; font-size: 1.1rem; }
    </style>
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>
<div class="container py-5">
    <div class="mb-4">
        <p class="text-muted mb-1">Demo Orchestration</p>
        <h1 class="h3 fw-bold">Simulation Control Center</h1>
        <p class="text-secondary">Switch weather, fast-forward billing and reset the demo in one click.</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card control-card">
                <div class="card-body">
                    <h5 class="card-title">Weather Simulator</h5>
                    <p class="text-muted">Current factor: <strong><?= htmlspecialchars($weatherFactor, ENT_QUOTES, 'UTF-8') ?></strong></p>
                    <form method="post" class="d-flex gap-2 flex-wrap">
                        <input type="hidden" name="action" value="sunny">
                        <button type="submit" class="btn btn-success big-btn flex-fill">Simulate Weather: SUNNY</button>
                    </form>
                    <form method="post" class="d-flex gap-2 flex-wrap mt-2">
                        <input type="hidden" name="action" value="cloudy">
                        <button type="submit" class="btn btn-secondary big-btn flex-fill">Simulate Weather: CLOUDY</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card control-card">
                <div class="card-body">
                    <h5 class="card-title">Billing Accelerator</h5>
                    <p class="text-muted">Trigger the monthly billing workflow immediately.</p>
                    <form method="post" class="d-flex gap-2 flex-wrap">
                        <input type="hidden" name="action" value="run_billing">
                        <button type="submit" class="btn btn-primary big-btn flex-fill">Trigger Monthly Billing Cycle</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card control-card border-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger">Reset Demo Data</h5>
                    <p class="text-muted">Clear projects and bills to restart the storyline.</p>
                    <form method="post" onsubmit="return confirm('Reset all demo data?');">
                        <input type="hidden" name="action" value="reset_demo">
                        <button type="submit" class="btn btn-outline-danger big-btn">Reset Demo Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
