<?php
define('APP_NAME', 'LabTech Office');
define('APP_SUBTITLE', 'Lost & Found System');
define('APP_VERSION', '1.1.0');

// Auto-detect BASE_URL — works on localhost, XAMPP, any host automatically
if (!defined('BASE_URL')) {
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script   = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $basePath = rtrim(dirname($script), '/\\');
    define('BASE_URL', $scheme . '://' . $host . $basePath);
}

define('UPLOAD_PATH', __DIR__ . '/../public/uploads/items/');
define('UPLOAD_URL',  BASE_URL . '/public/uploads/items/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg','jpeg','png','gif','webp']);

// Session
session_start();

// Autoload helpers
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';
