<?php
session_start();
require_once __DIR__ . '/functions.php';

// Step 1: Get ID.
if (!isset($_GET['project_id'])) {
    die("<h1>Error: Project ID is missing from the URL.</h1>");
}

$projectId = (string)$_GET['project_id'];

// Step 2: Load Data.
$projects = readJSON('projects.json');
$project = null;
foreach ($projects as $item) {
    if (isset($item['id']) && (string)$item['id'] === $projectId) {
        $project = $item;
        break;
    }
}

if ($project === null) {
    die("<h1>Error: Project not found in database.</h1>");
}

// Step 3: Auth Check (The Critical Part).
$u_id = (string)($_SESSION['user_id'] ?? '');
$consumerId = (string)($project['consumer_id'] ?? '');

if ($u_id !== $consumerId && ($_SESSION['role'] ?? '') !== 'Vendor') {
    echo "<h1>STOP: Auth Failed</h1>";
    echo "<p>Logged in User: " . htmlspecialchars($u_id, ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<p>Project Owner: " . htmlspecialchars($consumerId, ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<p>Roles: " . htmlspecialchars($_SESSION['role'] ?? '', ENT_QUOTES, 'UTF-8') . "</p>";
    exit();
}

// Step 4: Display.
$title = $project['title'] ?? 'Power Purchase Agreement';
$vendorName = $project['vendor_name'] ?? 'Vendor';
$consumerName = $project['consumer_name'] ?? 'Consumer';
$capacity = $project['capacity_kw'] ?? 'N/A';
$totalCost = $project['total_cost'] ?? 'N/A';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View PPA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="mb-4">
        <h1 class="h3 mb-1"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="text-muted mb-0">Agreement between <?php echo htmlspecialchars($vendorName, ENT_QUOTES, 'UTF-8'); ?> and <?php echo htmlspecialchars($consumerName, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5">Project Details</h2>
            <ul class="list-unstyled mb-0">
                <li><strong>Project ID:</strong> <?php echo htmlspecialchars($projectId, ENT_QUOTES, 'UTF-8'); ?></li>
                <li><strong>Capacity (kW):</strong> <?php echo htmlspecialchars((string)$capacity, ENT_QUOTES, 'UTF-8'); ?></li>
                <li><strong>Total Cost:</strong> <?php echo htmlspecialchars((string)$totalCost, ENT_QUOTES, 'UTF-8'); ?></li>
            </ul>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5">Terms &amp; Conditions</h2>
            <p class="text-muted">Standard terms for the Power Purchase Agreement.</p>
            <ol class="mb-0">
                <li>Scope of Work: Installation and maintenance of the solar power system.</li>
                <li>Tariff &amp; Payment: Payments as per agreed schedule and tariffs.</li>
                <li>Grid Interconnection: Compliance with local regulations for net-metering.</li>
                <li>Tenure &amp; Termination: Agreement tenure and termination clauses apply.</li>
                <li>Maintenance: Vendor responsible for system upkeep.</li>
                <li>Dispute Resolution: Disputes resolved as per governing law.</li>
            </ol>
        </div>
    </div>

    <form method="post" action="sign_ppa_action.php" class="card">
        <div class="card-body">
            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($projectId, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="accept_terms" name="accept_terms" required>
                <label class="form-check-label" for="accept_terms">
                    I, <?php echo htmlspecialchars($consumerName, ENT_QUOTES, 'UTF-8'); ?>, accept the terms and conditions of this Power Purchase Agreement.
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Sign PPA</button>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
