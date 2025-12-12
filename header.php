<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['name'] ?? $_SESSION['user_name'] ?? 'Guest';
$userRole = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
$unreadData = ['count' => 0, 'notifications' => []];

if ($userId) {
    $unreadData = getUnreadNotifications((string)$userId);
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="dashboard.php">Digital RESCO</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-lg-center">
                <?php if (strcasecmp($userRole, 'MNRE_Admin') === 0): ?>
                    <li class="nav-item"><a class="nav-link" href="mnre_dashboard.php">MNRE Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_simulation.php">Simulation Center</a></li>
                <?php endif; ?>
                <li class="nav-item dropdown mx-2">
                    <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="bi bi-bell-fill fs-5"></span>
                        <?php if ($unreadData['count'] > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= (int)$unreadData['count'] ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notificationDropdown" style="min-width: 320px;">
                        <li class="dropdown-header d-flex justify-content-between align-items-center">
                            <span>Notifications</span>
                            <span class="badge bg-primary">Unread: <?= (int)$unreadData['count'] ?></span>
                        </li>
                        <?php if (empty($unreadData['notifications'])): ?>
                            <li><span class="dropdown-item text-muted">No new alerts</span></li>
                        <?php else: ?>
                            <?php foreach (array_slice($unreadData['notifications'], 0, 5) as $note): ?>
                                <li>
                                    <div class="dropdown-item">
                                        <div class="small text-muted mb-1"><?= htmlspecialchars(date('M j, g:i a', strtotime($note['created_at'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="fw-semibold text-<?= htmlspecialchars($note['type'] ?? 'info', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($note['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><span class="dropdown-item-text text-muted">Role: <?= htmlspecialchars($userRole ?: 'Guest', ENT_QUOTES, 'UTF-8') ?></span></li>
                        <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
