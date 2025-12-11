<?php
session_start();
require_once __DIR__ . '/functions.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';

if (!$userId || strcasecmp($role, 'Consumer') !== 0) {
    redirect('dashboard.php?error=' . urlencode('Unauthorized access. Consumer role required.'));
}

$billId = $_POST['bill_id'] ?? '';
if ($billId === '') {
    redirect('consumer_dashboard.php?error=' . urlencode('Bill not specified.'));
}

$bills = readJSON('bills.json');
$projects = readJSON('projects.json');
$transactions = readJSON('transactions.json');

$billIndex = null;
foreach ($bills as $index => $bill) {
    if (isset($bill['id']) && (string)$bill['id'] === (string)$billId) {
        $billIndex = $index;
        break;
    }
}

if ($billIndex === null) {
    redirect('consumer_dashboard.php?error=' . urlencode('Bill not found.'));
}

$bill = $bills[$billIndex];
if (strcasecmp($bill['status'] ?? '', 'UNPAID') !== 0) {
    redirect('consumer_dashboard.php?error=' . urlencode('Bill already processed.'));
}

if (!isset($bill['consumer_id']) || (string)$bill['consumer_id'] !== (string)$userId) {
    redirect('consumer_dashboard.php?error=' . urlencode('You can only pay your own bills.'));
}

$projectId = $bill['project_id'] ?? null;
$projectVendorId = null;
foreach ($projects as $project) {
    if (isset($project['id']) && (string)$project['id'] === (string)$projectId) {
        $projectVendorId = $project['vendor_id'] ?? null;
        break;
    }
}

$emiAmount = (float)($bill['emi_due'] ?? 0);
$billAmount = (float)($bill['bill_amount'] ?? 0);
$vendorShare = max($billAmount - $emiAmount, 0);

$bills[$billIndex]['status'] = 'PAID';
$bills[$billIndex]['paid_at'] = date('c');

$transactions[] = [
    'id' => uniqid('txn_'),
    'bill_id' => $bill['id'],
    'project_id' => $projectId,
    'to' => 'Bank',
    'amount' => $emiAmount,
    'consumer_id' => $userId,
    'vendor_id' => $projectVendorId,
    'recorded_at' => date('c'),
];

$transactions[] = [
    'id' => uniqid('txn_'),
    'bill_id' => $bill['id'],
    'project_id' => $projectId,
    'to' => 'Vendor',
    'amount' => $vendorShare,
    'consumer_id' => $userId,
    'vendor_id' => $projectVendorId,
    'recorded_at' => date('c'),
];

writeJSON('bills.json', $bills);
writeJSON('transactions.json', $transactions);

redirect('consumer_dashboard.php?success=' . urlencode('Payment Successful. EMI Auto-Deducted.'));
