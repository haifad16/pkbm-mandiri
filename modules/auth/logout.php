<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect dengan flash message di session baru
session_start();
setFlash('info', 'Anda telah berhasil keluar dari sistem.');
redirect('modules/auth/login.php');

