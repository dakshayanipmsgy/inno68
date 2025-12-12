<?php

define('DATA_DIR', __DIR__ . '/data');

/**
 * Ensure the data directory exists and is writable.
 */
function ensureDataDirectory(): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0775, true);
    }

    if (!is_writable(DATA_DIR)) {
        die('Data directory is not writable. Please adjust permissions.');
    }
}

/**
 * Read JSON data from a file inside the data directory.
 */
function readJSON(string $filename): array
{
    ensureDataDirectory();

    $filepath = DATA_DIR . '/' . $filename;

    if (!file_exists($filepath)) {
        return [];
    }

    $contents = file_get_contents($filepath);
    if ($contents === false) {
        return [];
    }

    $data = json_decode($contents, true);

    return is_array($data) ? $data : [];
}

/**
 * Write an array as JSON to a file inside the data directory.
 */
function writeJSON(string $filename, array $data): bool
{
    ensureDataDirectory();

    $filepath = DATA_DIR . '/' . $filename;
    $json = json_encode($data, JSON_PRETTY_PRINT);

    if ($json === false) {
        return false;
    }

    return file_put_contents($filepath, $json, LOCK_EX) !== false;
}

/**
 * Append a notification for a user.
 */
function sendNotification(string $userId, string $message, string $type = 'info'): bool
{
    $notifications = readJSON('notifications.json');

    $notifications[] = [
        'id' => uniqid('note_', true),
        'user_id' => $userId,
        'message' => $message,
        'type' => $type,
        'is_read' => false,
        'created_at' => date('c'),
    ];

    return writeJSON('notifications.json', $notifications);
}

/**
 * Get unread notifications for a user.
 */
function getUnreadNotifications(string $userId): array
{
    $notifications = readJSON('notifications.json');

    $unread = array_values(array_filter($notifications, function ($notification) use ($userId) {
        return isset($notification['user_id'], $notification['is_read'])
            && (string)$notification['user_id'] === (string)$userId
            && $notification['is_read'] === false;
    }));

    usort($unread, function ($a, $b) {
        return strtotime($b['created_at'] ?? 'now') <=> strtotime($a['created_at'] ?? 'now');
    });

    return [
        'count' => count($unread),
        'notifications' => $unread,
    ];
}

/**
 * Read platform settings from settings.json.
 */
function readSettings(): array
{
    $settings = readJSON('settings.json');

    if (empty($settings)) {
        $settings = [
            'weather_factor' => 1.0,
        ];
        writeJSON('settings.json', $settings);
    }

    return $settings;
}

/**
 * Persist platform settings.
 */
function writeSettings(array $settings): bool
{
    return writeJSON('settings.json', $settings);
}

/**
 * Get the current weather factor used in simulations.
 */
function getWeatherFactor(): float
{
    $settings = readSettings();
    return (float)($settings['weather_factor'] ?? 1.0);
}

/**
 * Update the weather factor used in simulations.
 */
function setWeatherFactor(float $factor): bool
{
    $settings = readSettings();
    $settings['weather_factor'] = $factor;
    return writeSettings($settings);
}

/**
 * Run a billing cycle for all LIVE projects using the provided weather factor.
 */
function runBillingCycle(float $weatherFactor = 1.0): int
{
    $projects = readJSON('projects.json');
    $bills = readJSON('bills.json');

    $liveProjects = array_values(array_filter($projects, function ($project) {
        return isset($project['status']) && strcasecmp($project['status'], 'LIVE') === 0;
    }));

    $unitMultiplier = $weatherFactor >= 0.7 ? 120 : 60;
    $ratePerUnit = 8; // INR per unit

    foreach ($liveProjects as $project) {
        if (empty($project['id'])) {
            continue;
        }

        $capacityKw = (float)($project['capacity_kw'] ?? 0);
        $generationUnits = max($capacityKw * $unitMultiplier, 0);
        $billAmount = $generationUnits * $ratePerUnit;
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

    return count($liveProjects);
}

/**
 * Redirect to a URL and exit.
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
