<?php
session_start();
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';

if (!$userId || strcasecmp($role, 'DISCOM') !== 0) {
    redirect('dashboard.php?error=' . urlencode('Unauthorized access. DISCOM role required.'));
}

$projectId = $_POST['project_id'] ?? $_GET['project_id'] ?? '';
if ($projectId === '') {
    redirect('dashboard.php?error=' . urlencode('Project not specified.'));
}

$projects = readJSON('projects.json');
$projectIndex = null;

foreach ($projects as $index => $project) {
    if (isset($project['id']) && (string)$project['id'] === (string)$projectId) {
        $projectIndex = $index;
        break;
    }
}

if ($projectIndex === null) {
    redirect('dashboard.php?error=' . urlencode('Project not found.'));
}

$currentStatus = $projects[$projectIndex]['status'] ?? '';
if (strcasecmp($currentStatus, 'PENDING_DISCOM_APPROVAL') !== 0) {
    redirect('dashboard.php?error=' . urlencode('This project is not awaiting DISCOM approval.'));
}

$projects[$projectIndex]['status'] = 'FUNDING_REQUESTED';
$projects[$projectIndex]['grid_approval_date'] = date('c');

writeJSON('projects.json', $projects);

redirect('dashboard.php?success=' . urlencode('Net Metering Approved. Project is now visible to Financiers.'));
