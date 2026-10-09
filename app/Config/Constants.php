<?php

defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

defined('COMPOSER_PATH') || define('COMPOSER_PATH', (string) realpath(ROOTPATH . 'vendor/autoload.php'));

defined('EXIT_SUCCESS') || define('EXIT_SUCCESS', 0);
defined('EXIT_ERROR') || define('EXIT_ERROR', 1);
defined('EXIT_CONFIG') || define('EXIT_CONFIG', 3);
defined('EXIT_UNKNOWN_FILE') || define('EXIT_UNKNOWN_FILE', 4);
defined('EXIT_UNKNOWN_CLASS') || define('EXIT_UNKNOWN_CLASS', 5);
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6);
defined('EXIT_USER_INPUT') || define('EXIT_USER_INPUT', 7);
defined('EXIT_DATABASE') || define('EXIT_DATABASE', 8);
defined('EXIT__AUTO_MIN') || define('EXIT__AUTO_MIN', 9);
defined('EXIT__AUTO_MAX') || define('EXIT__AUTO_MAX', 125);

defined('APP_NAME') || define('APP_NAME', 'The Daily Chronicle');
defined('UPLOAD_PATH') || define('UPLOAD_PATH', ROOTPATH . 'uploads');

defined('REMEMBER_ME_DAYS') || define('REMEMBER_ME_DAYS', 30);
defined('LOGIN_MAX_ATTEMPTS') || define('LOGIN_MAX_ATTEMPTS', 5);
defined('LOGIN_LOCKOUT_MINUTES') || define('LOGIN_LOCKOUT_MINUTES', 15);
defined('ITEMS_PER_PAGE') || define('ITEMS_PER_PAGE', 9);
defined('ADMIN_ITEMS_PER_PAGE') || define('ADMIN_ITEMS_PER_PAGE', 15);

defined('MAX_UPLOAD_SIZE') || define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);
defined('ALLOWED_IMAGE_TYPES') || define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
