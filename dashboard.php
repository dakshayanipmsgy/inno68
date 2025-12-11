<?php
session_start();
require_once __DIR__ . '/functions.php';

if (empty($_SESSION['user_id'])) {
    redirect('login.php');
}

// Normalize session keys to support both legacy and expected keys.
if (!isset($_SESSION['role']) && isset($_SESSION['user_role'])) {
    $_SESSION['role'] = $_SESSION['user_role'];
}
if (!isset($_SESSION['name']) && isset($_SESSION['user_name'])) {
    $_SESSION['name'] = $_SESSION['user_name'];
}

$name = $_SESSION['name'] ?? $_SESSION['user_name'] ?? 'User';
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? 'Member';

if (strcasecmp($role, 'Vendor') === 0) {
    require __DIR__ . '/vendor_dashboard.php';
    exit;
}

if (strcasecmp($role, 'Consumer') === 0) {
    require __DIR__ . '/consumer_dashboard.php';
    exit;
}

if (strcasecmp($role, 'DISCOM') === 0) {
    require __DIR__ . '/discom_dashboard.php';
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Digital RESCO Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f6f8fb;
        }
        .hero {
            background: linear-gradient(135deg, #22c55e, #0f52ba);
            color: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h1 class="fw-bold text-primary">Digital RESCO Platform</h1>
        <div>
            <span class="me-3 text-muted">Role: <?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></span>
            <a href="login.php" class="btn btn-outline-secondary btn-sm">Logout</a>
        </div>
    </div>

    <div class="hero mb-4">
        <h2 class="fw-semibold">Welcome, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>!</h2>
        <p class="lead mb-0">You are logged in as a <?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>. Dedicated dashboards and workflows are coming soon.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Next Steps</h5>
            <ul class="mb-0 text-muted">
                <li>Profile completion and KYC verification</li>
                <li>Project onboarding and financing applications</li>
                <li>Net-metering approvals and DISCOM integrations</li>
            </ul>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
