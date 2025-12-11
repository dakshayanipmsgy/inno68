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
 * Redirect to a URL and exit.
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
