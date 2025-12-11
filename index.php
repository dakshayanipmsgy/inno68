<?php
session_start();
require_once __DIR__ . '/functions.php';

$projects = readJSON('projects.json');
$totalProjects = count($projects);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital RESCO Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0ea5e9, #10b981);
            min-height: 100vh;
            color: #0b2537;
        }
        .hero-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            padding: 3rem;
        }
        .btn-primary {
            background-color: #0f766e;
            border-color: #0f766e;
        }
        .btn-outline-light {
            color: #0f172a;
            border-color: #0f172a;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row align-items-center">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <h1 class="display-4 fw-bold text-white mb-3">Digital RESCO Platform</h1>
            <p class="lead text-white-50">Democratizing Solar Financing for India by connecting vendors, consumers, financiers, and DISCOMs through a trusted digital marketplace.</p>
            <div class="d-flex gap-3 flex-wrap">
                <a href="login.php" class="btn btn-light btn-lg px-4">Login</a>
                <a href="register.php" class="btn btn-outline-light btn-lg px-4">Register</a>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="hero-card">
                <h2 class="fw-semibold mb-3 text-primary">Solar-first, Fintech-ready</h2>
                <p class="text-muted">Start building rooftop solar projects with instant access to financing partners, streamlined approvals, and transparent net-metering workflows.</p>
                <ul class="text-muted">
                    <li>Marketplace for vendors and consumers</li>
                    <li>Loan approvals with financiers</li>
                    <li>DISCOM-ready documentation and status tracking</li>
                </ul>
                <div class="d-flex gap-2">
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                    <a href="login.php" class="btn btn-outline-secondary">Existing user? Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4 p-lg-5 text-center">
                    <p class="text-muted mb-1">Live Platform Stats</p>
                    <h2 class="fw-bold">Real Deployment Momentum</h2>
                    <p class="text-muted">Investors can explore an actively growing pipeline backed by vetted participants.</p>
                    <div class="d-flex justify-content-center gap-4 flex-wrap mt-4">
                        <div>
                            <h1 class="display-5 fw-bold text-primary mb-0"><?= htmlspecialchars($totalProjects, ENT_QUOTES, 'UTF-8') ?></h1>
                            <p class="mb-0 text-muted">Total Projects</p>
                        </div>
                        <div>
                            <h1 class="display-6 fw-semibold text-success mb-0">Multi-party</h1>
                            <p class="mb-0 text-muted">Vendors • Consumers • Financiers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
