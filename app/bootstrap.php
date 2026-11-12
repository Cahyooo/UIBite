<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/helpers.php';

$timezone = env('APP_TIMEZONE', 'Asia/Jakarta');
date_default_timezone_set((string) $timezone);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('UIBITESESSID');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

require_once BASE_PATH . '/app/db.php';
require_once BASE_PATH . '/app/pages.php';

if (isset($_SESSION['user']['id'])) {
    // Refresh the role/name from the database so disabled or changed accounts are not kept stale.
    try {
        $stmt = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([(int) $_SESSION['user']['id']]);
        $freshUser = $stmt->fetch();
        if ($freshUser) {
            $_SESSION['user'] = $freshUser;
        } else {
            unset($_SESSION['user']);
        }
    } catch (Throwable $exception) {
        // The request will show the normal database connection error at the routing layer.
    }
}
