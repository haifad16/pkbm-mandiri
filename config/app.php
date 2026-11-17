<?php
/**
 * Application Global Configuration
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Session Configuration
$sessionDir = BASE_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0777, true);
}
if (is_dir($sessionDir)) {
    @ini_set('session.save_path', $sessionDir);
    @session_save_path($sessionDir);
}

// Session Start jika bukan CLI dan session belum aktif
if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Deteksi otomatis base URL yang fleksibel
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));

if (($pos = strpos($scriptDir, '/modules')) !== false) {
    $subDir = substr($scriptDir, 0, $pos);
} else {
    $subDir = ($scriptDir === '/' || $scriptDir === '.') ? '' : $scriptDir;
}
$baseUrl = rtrim($protocol . $host . $subDir, '/');

if (empty($scriptName) || php_sapi_name() === 'cli') {
    $baseUrl = 'http://localhost/web_pkbm_mandiri';
}

define('BASE_URL', $baseUrl);

// Informasi Lembaga PKBM Mandiri
define('APP_NAME', 'PKBM Mandiri');
define('APP_FULL_NAME', 'Pusat Kegiatan Belajar Masyarakat (PKBM) Mandiri');
define('APP_NPSN', 'P2962977');
define('APP_SK_OPERASIONAL', '421.9/056/Disdik/2021');
define('APP_ADDRESS', 'Jl. A. Yani KM. 60 RT. 02 RW. 02, Desa Mataraman, Kec. Mataraman, Kab. Banjar, Kalimantan Selatan');
define('APP_PHONE', '0812-5597-4191');
define('APP_WHATSAPP', '6281255974191');
define('APP_EMAIL', 'pkbmmandiri.mataraman@gmail.com');
define('APP_YEAR', '2026/2027');

// Folder upload
define('UPLOAD_DIR', BASE_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('UPLOAD_BERKAS_DIR', UPLOAD_DIR . DIRECTORY_SEPARATOR . 'berkas');
define('UPLOAD_FOTO_DIR', UPLOAD_DIR . DIRECTORY_SEPARATOR . 'foto');

// Pastikan direktori uploads tersedia
foreach ([UPLOAD_DIR, UPLOAD_BERKAS_DIR, UPLOAD_FOTO_DIR] as $path) {
    if (!is_dir($path)) {
        @mkdir($path, 0777, true);
    }
}

// Muat database dan fungsi bantuan
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/functions.php';
