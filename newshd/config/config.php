<?php
/**
 * NEWSHD News Portal - Application Configuration
 */

declare(strict_types=1);

define('APP_NAME', 'NEWSHD');
define('APP_URL', 'http://localhost/newshd');
define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_PATH', APP_ROOT . '/uploads');
define('UPLOAD_URL', APP_URL . '/uploads');

define('DB_HOST', 'localhost');
define('DB_NAME', 'news_portal');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('SESSION_LIFETIME', 7200);
define('REMEMBER_ME_DAYS', 30);
define('COMMENT_EDIT_WINDOW', 900);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);
define('ITEMS_PER_PAGE', 9);
define('ADMIN_ITEMS_PER_PAGE', 15);

define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

date_default_timezone_set('Asia/Kathmandu');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/functions.php';
require_once APP_ROOT . '/includes/csrf.php';
require_once APP_ROOT . '/includes/auth.php';
