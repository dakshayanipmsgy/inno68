<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';

if (!$userId || strcasecmp($role, 'Financier') !== 0) {
    redirect('dashboard.php?error=' . urlencode('Unauthorized access. Financier role required.'));
}

$projectId = $_POST['project_id'] ?? '';
if ($projectId === '') {
    redirect('financier_dashboard.php?error=' . urlencode('Project not specified.'));
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
    redirect('financier_dashboard.php?error=' . urlencode('Project not found.'));
}

$currentStatus = $projects[$projectIndex]['status'] ?? '';
if (strcasecmp($currentStatus, 'FUNDING_REQUESTED') !== 0) {
    redirect('financier_dashboard.php?error=' . urlencode('This project is not awaiting financing.'));
}

$totalCost = (float)($projects[$projectIndex]['total_cost'] ?? 0);
$emiAmount = round($totalCost * 0.01, 2);

$projects[$projectIndex]['status'] = 'LIVE';
$projects[$projectIndex]['loan_amount'] = $totalCost;
$projects[$projectIndex]['emi_amount'] = $emiAmount;
$projects[$projectIndex]['financed_date'] = date('c');

writeJSON('projects.json', $projects);

// Notify vendor on fund disbursal.
$vendorId = $projects[$projectIndex]['vendor_id'] ?? null;
if ($vendorId) {
    sendNotification((string)$vendorId, 'Funds Disbursed. Your project is live.', 'success');
}

redirect('financier_dashboard.php?success=' . urlencode('Loan Disbursed. Project is LIVE.'));
