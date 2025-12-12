<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';

if (!$userId || strcasecmp($role, 'Vendor') !== 0) {
    redirect('dashboard.php');
}

$errors = [];
$users = readJSON('users.json');
$consumers = array_filter($users, function ($user) {
    return isset($user['role']) && strcasecmp($user['role'], 'Consumer') === 0;
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $capacity = $_POST['capacity_kw'] ?? '';
    $totalCost = $_POST['total_cost'] ?? '';
    $financing = $_POST['financing_required'] ?? '';
    $consumerId = $_POST['consumer_id'] ?? '';

    if ($title === '') {
        $errors[] = 'Project title is required.';
    }

    if (!is_numeric($capacity) || (float)$capacity <= 0) {
        $errors[] = 'Capacity must be a positive number.';
    }

    if (!is_numeric($totalCost) || (float)$totalCost <= 0) {
        $errors[] = 'Total project cost must be a positive number.';
    }

    if (!is_numeric($financing) || (float)$financing <= 0) {
        $errors[] = 'Financing required must be a positive number.';
    }

    if ($consumerId === '') {
        $errors[] = 'Please select a consumer for this project.';
    }

    if (empty($errors)) {
        $projects = readJSON('projects.json');

        $projects[] = [
            'project_id' => 'PRJ-' . time(),
            'vendor_id' => $userId,
            'consumer_id' => $consumerId,
            'title' => $title,
            'capacity_kw' => (float)$capacity,
            'total_cost' => (float)$totalCost,
            'financing_required' => (float)$financing,
            'status' => 'PENDING_CONSUMER_APPROVAL',
            'created_at' => date('c'),
        ];

        writeJSON('projects.json', $projects);

        redirect('dashboard.php?success=1');
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Project | Digital RESCO Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f9fafb; }
        .card { border: none; border-radius: 18px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); }
        .page-header {
            background: linear-gradient(135deg, #0ea5e9, #0f766e);
            color: #fff;
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 12px 30px rgba(14,165,233,0.25);
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <p class="mb-1">Vendor Proposal</p>
            <h1 class="h3 fw-bold mb-0">Create a New Project Proposal</h1>
            <small>Tag your consumer and submit financing requirements.</small>
        </div>
        <a href="dashboard.php" class="btn btn-light">Back to Dashboard</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
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

                <form method="post" novalidate>
                    <div class="mb-3">
                        <label for="title" class="form-label">Project Title</label>
                        <input type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="capacity_kw" class="form-label">Capacity (kW)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="capacity_kw" name="capacity_kw" required value="<?= htmlspecialchars($_POST['capacity_kw'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="total_cost" class="form-label">Total Project Cost (INR)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="total_cost" name="total_cost" required value="<?= htmlspecialchars($_POST['total_cost'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="financing_required" class="form-label">Financing Required (INR)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="financing_required" name="financing_required" required value="<?= htmlspecialchars($_POST['financing_required'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="mt-3 mb-4">
                        <label for="consumer_id" class="form-label">Assign Consumer</label>
                        <select class="form-select" id="consumer_id" name="consumer_id" required>
                            <option value="">Select Consumer</option>
                            <?php foreach ($consumers as $consumer): ?>
                                <option value="<?= htmlspecialchars($consumer['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= (($_POST['consumer_id'] ?? '') === ($consumer['id'] ?? '')) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(($consumer['name'] ?? 'Consumer') . ' (' . ($consumer['email'] ?? 'N/A') . ')', ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Save Proposal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
