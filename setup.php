#!/usr/bin/env php
<?php
/**
 * Railway DB Setup Script
 * Run once after deployment to create all tables.
 * Usage: php setup.php
 */

require_once __DIR__ . '/config/database.php';

echo "Connecting to database...\n";
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, '', (int)DB_PORT);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error . "\n");
}

echo "Connected! Creating database if not exists...\n";
$db->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$db->select_db(DB_NAME);

echo "Running schema...\n";
$sql = file_get_contents(__DIR__ . '/config/schema.sql');

// Remove the CREATE DATABASE and USE lines since Railway provides the DB
$sql = preg_replace('/CREATE DATABASE.*?;\s*/si', '', $sql);
$sql = preg_replace('/USE\s+\w+\s*;\s*/si', '', $sql);

// Split and run each statement
$statements = array_filter(array_map('trim', explode(';', $sql)));
$success = 0;
$errors  = 0;

foreach ($statements as $stmt) {
    if (empty($stmt)) continue;
    if ($db->query($stmt)) {
        $success++;
    } else {
        // Ignore "already exists" errors
        if (strpos($db->error, 'already exists') === false) {
            echo "  Warning: " . $db->error . "\n";
            $errors++;
        }
    }
}

echo "Done! $success statements executed, $errors errors.\n";
echo "Your Lost & Found system is ready!\n";
