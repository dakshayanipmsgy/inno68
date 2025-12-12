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

$projectIndex = null;
foreach ($projects as $index => $item) {
    $itemId = $item['project_id'] ?? $item['id'] ?? null;
    if ($itemId !== null && (string)$itemId === (string)$projectId) {
        $projectIndex = $index;
        break;
    }
}

if ($projectIndex === null || (string)($projects[$projectIndex]['vendor_id'] ?? '') !== (string)$userId) {
    redirect('vendor_dashboard.php?error=' . urlencode('Project not found.'));
}

$project = $projects[$projectIndex];
$status = strtoupper($project['status'] ?? 'DRAFT');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($status !== 'DRAFT') {
        $errors[] = 'Cannot edit project once application is submitted.';
    }

    $title = trim($_POST['title'] ?? '');
    $capacity = $_POST['capacity_kw'] ?? '';
    $totalCost = $_POST['total_cost'] ?? '';

    if ($title === '') {
        $errors[] = 'Project title is required.';
    }

    if (!is_numeric($capacity) || (float)$capacity <= 0) {
        $errors[] = 'Capacity must be a positive number.';
    }

    if (!is_numeric($totalCost) || (float)$totalCost <= 0) {
        $errors[] = 'Total cost must be a positive number.';
    }

    if (empty($errors)) {
        $projects[$projectIndex]['title'] = $title;
        $projects[$projectIndex]['capacity_kw'] = (float)$capacity;
        $projects[$projectIndex]['total_cost'] = (float)$totalCost;

        writeJSON('projects.json', $projects);

        redirect('vendor_dashboard.php?success=' . urlencode('Project updated successfully.'));
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Project | Digital RESCO Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; }
        .page-header {
            background: linear-gradient(120deg, #0f766e, #1d4ed8);
            color: #fff;
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }
        .card {
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
            <p class="mb-1">Edit Proposal</p>
            <h1 class="h4 fw-bold mb-0">Update Project Details</h1>
            <small>Modify draft proposals before submitting to consumers.</small>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark mb-2">Status: <?= htmlspecialchars(str_replace('_', ' ', $status), ENT_QUOTES, 'UTF-8') ?></span>
            <div class="mt-1">
                <a href="vendor_dashboard.php" class="btn btn-light text-primary fw-semibold">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card p-4 bg-white">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($status !== 'DRAFT'): ?>
                    <div class="alert alert-warning mb-0">
                        Cannot edit project once application is submitted.
                    </div>
                <?php else: ?>
                    <form method="post" novalidate>
                        <div class="mb-3">
                            <label for="title" class="form-label">Project Title</label>
                            <input type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? ($project['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="capacity_kw" class="form-label">Capacity (kW)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="capacity_kw" name="capacity_kw" required value="<?= htmlspecialchars($_POST['capacity_kw'] ?? ($project['capacity_kw'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="total_cost" class="form-label">Total Project Cost (INR)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="total_cost" name="total_cost" required value="<?= htmlspecialchars($_POST['total_cost'] ?? ($project['total_cost'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
