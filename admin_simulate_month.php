<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
require_once __DIR__ . '/functions.php';

if (empty($_SESSION['user_id'])) {
    redirect('login.php');
}

$weatherFactor = getWeatherFactor();
$generatedCount = runBillingCycle($weatherFactor);

echo 'Billing Cycle Run: ' . $generatedCount . ' Bills Generated with weather factor ' . $weatherFactor . '.';
