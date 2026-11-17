<?php
/**
 * Database Connection & Initialization
 * Mendukung SQLite (default, portable, tanpa setup server database)
 * dan MySQL / MariaDB (tinggal ubah DB_DRIVER ke 'mysql').
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Konfigurasi Database
define('DB_DRIVER', 'sqlite'); // 'sqlite' atau 'mysql'

// Jika menggunakan MySQL:
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'db_pkbm_mandiri');
define('DB_USER', 'root');
define('DB_PASS', '');

// File SQLite
define('SQLITE_FILE', BASE_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'pkbm.sqlite');

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                if (DB_DRIVER === 'mysql') {
                    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                    self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                } else {
                    // Default SQLite
                    $dbDir = dirname(SQLITE_FILE);
                    if (!is_dir($dbDir)) {
                        mkdir($dbDir, 0777, true);
                    }
                    $isNew = !file_exists(SQLITE_FILE) || filesize(SQLITE_FILE) === 0;
                    
                    $dsn = "sqlite:" . SQLITE_FILE;
                    self::$instance = new PDO($dsn, null, null, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                    
                    // Konfigurasi performa database aman untuk hosting
                    try {
                        self::$instance->exec("PRAGMA journal_mode = WAL;");
                    } catch (Throwable $t) {
                        try { self::$instance->exec("PRAGMA journal_mode = DELETE;"); } catch (Throwable $e) {}
                    }
                    try { self::$instance->exec("PRAGMA foreign_keys = ON;"); } catch (Throwable $e) {}

                    if ($isNew) {
                        self::migrateAndSeed(self::$instance);
                    }
                }
            } catch (PDOException $e) {
                // Jika MySQL gagal, coba fallback ke SQLite agar web tetap berjalan
                if (DB_DRIVER === 'mysql') {
                    try {
                        $dsn = "sqlite:" . SQLITE_FILE;
                        self::$instance = new PDO($dsn, null, null, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        ]);
                        self::$instance->exec("PRAGMA journal_mode = WAL;");
                        self::$instance->exec("PRAGMA foreign_keys = ON;");
                        if (!file_exists(SQLITE_FILE) || filesize(SQLITE_FILE) === 0) {
                            self::migrateAndSeed(self::$instance);
                        }
                        return self::$instance;
                    } catch (Exception $ex) {
                        die("Koneksi Database Gagal: " . $e->getMessage());
                    }
                }
                die("Koneksi Database Gagal: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    /**
     * Jalankan migrasi tabel dan seeding data awal
     */
    public static function migrateAndSeed(PDO $pdo): void {
        require_once BASE_PATH . '/data/migrate.php';
        runMigration($pdo);
    }
}

function getDB(): PDO {
    return Database::getConnection();
}

