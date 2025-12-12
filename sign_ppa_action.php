<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';

if (!$userId || strcasecmp($role, 'Consumer') !== 0) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$projectId = $_POST['project_id'] ?? '';
$accepted = !empty($_POST['accept_terms']);

if ($projectId === '' || !$accepted) {
    $message = $projectId === '' ? 'Project not specified.' : 'You must accept the terms to sign the PPA.';
    redirect('view_ppa.php?project_id=' . urlencode($projectId) . '&error=' . urlencode($message));
}

$projects = readJSON('projects.json');
$projectIndex = null;

foreach ($projects as $index => $project) {
    if (isset($project['id']) && (string)$project['id'] === (string)$projectId) {
        $projectIndex = $index;
        break;
    }
}

if ($projectIndex === null || (string)($projects[$projectIndex]['consumer_id'] ?? '') !== (string)$userId) {
    redirect('dashboard.php?error=' . urlencode('You are not authorized to sign this PPA.'));
}

$currentStatus = $projects[$projectIndex]['status'] ?? '';
if (strcasecmp($currentStatus, 'PENDING_CONSUMER_APPROVAL') !== 0) {
    redirect('dashboard.php?error=' . urlencode('This PPA cannot be signed at the current status.'));
}

$projects[$projectIndex]['status'] = 'PENDING_DISCOM_APPROVAL';
$projects[$projectIndex]['ppa_signed_date'] = date('c');

writeJSON('projects.json', $projects);

// Notify vendor and DISCOM stakeholders.
$vendorId = $projects[$projectIndex]['vendor_id'] ?? null;
if ($vendorId) {
    sendNotification((string)$vendorId, 'Consumer signed PPA for your project.', 'info');
}

$users = readJSON('users.json');
foreach ($users as $user) {
    if (isset($user['role'], $user['id']) && strcasecmp($user['role'], 'DISCOM') === 0) {
        sendNotification((string)$user['id'], 'New Grid Request ready for review.', 'warning');
    }
}

redirect('dashboard.php?success=' . urlencode('PPA Signed. Application sent to DISCOM for Grid Approval.'));
