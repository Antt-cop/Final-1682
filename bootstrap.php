<?php
/**
 * bootstrap.php - Central path & config file
 * Include this at the top of any file that needs paths/db.
 */

// -- Root path (absolute, always correct regardless of caller depth) ------
define('ROOT_PATH', __DIR__);
define('APP_PATH',      ROOT_PATH . '/app');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('PUBLIC_PATH',   ROOT_PATH . '/public');
define('ADMIN_PATH',    ROOT_PATH . '/admin');
define('SELLER_PATH',   ROOT_PATH . '/seller');
define('ASSETS_URL',    'assets');   // relative URL for HTML src/href
define('UPLOADS_PATH',  ROOT_PATH . '/uploads');

// -- Autoload DB connection -----------------------------------------------
require_once APP_PATH . '/config/db_connect.php';

// URL Base path (dynamic calculation)
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
$appRoot = str_replace('\\', '/', __DIR__);
$baseUrl = str_replace($docRoot, '', $appRoot);
if (!defined('BASE_URL')) {
    define('BASE_URL', $baseUrl);
}

// ── Load .env variables (Robust parser) ──────────────────────────────────
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || strpos($line, ';') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!empty($name)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
}

// ── Currency helper ───────────────────────────────────────────────────────
if (!function_exists('format_vnd')) {
    function format_vnd($amount) {
        return number_format((int)$amount, 0, ',', '.') . ' ₫';
    }
}

// ── Image helper with automatic cache-busting ──────────────────────────────
if (!function_exists('asset_img')) {
    function asset_img($path, $fallback = 'images/img-pro-01.jpg') {
        $p = !empty($path) ? ltrim($path, '/') : $fallback;
        if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
            return $p;
        }
        $fullPath = ROOT_PATH . '/assets/' . $p;
        $v = file_exists($fullPath) ? filemtime($fullPath) : time();
        return BASE_URL . '/assets/' . $p . '?v=' . $v;
    }
}

// ── Packaging & Inventory Helper ───────────────────────────────────────────
require_once APP_PATH . '/helpers/packaging_helper.php';

