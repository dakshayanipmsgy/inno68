<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
require_once __DIR__ . '/functions.php';

if (empty($_SESSION['user_id'])) {
    redirect('login.php');
}

$projects = readJSON('projects.json');
$bills = readJSON('bills.json');

$liveProjects = array_values(array_filter($projects, function ($project) {
    return isset($project['status']) && strcasecmp($project['status'], 'LIVE') === 0;
}));

foreach ($liveProjects as $project) {
    if (empty($project['id'])) {
        continue;
    }

    $generationUnits = rand(200, 400);
    $billAmount = $generationUnits * 8;
    $emiDue = isset($project['emi_amount']) ? (float)$project['emi_amount'] : 0.0;

    $bills[] = [
        'id' => uniqid('bill_'),
        'project_id' => $project['id'],
        'consumer_id' => $project['consumer_id'] ?? null,
        'vendor_id' => $project['vendor_id'] ?? null,
        'generation_units' => $generationUnits,
        'bill_amount' => $billAmount,
        'emi_due' => $emiDue,
        'status' => 'UNPAID',
        'generated_at' => date('c'),
    ];
}

writeJSON('bills.json', $bills);

echo 'Billing Cycle Run: ' . count($liveProjects) . ' Bills Generated.';
