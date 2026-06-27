<?php
/**
 * bootstrap.php — Central path & config file
 * Include this at the top of any file that needs paths/db.
 */

// ── Root path (absolute, always correct regardless of caller depth) ──────
define('ROOT_PATH', __DIR__);
define('APP_PATH',      ROOT_PATH . '/app');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('PUBLIC_PATH',   ROOT_PATH . '/public');
define('ADMIN_PATH',    ROOT_PATH . '/admin');
define('SELLER_PATH',   ROOT_PATH . '/seller');
define('ASSETS_URL',    'assets');   // relative URL for HTML src/href
define('UPLOADS_PATH',  ROOT_PATH . '/uploads');

// ── Autoload DB connection ────────────────────────────────────────────────
require_once APP_PATH . '/config/db_connect.php';
