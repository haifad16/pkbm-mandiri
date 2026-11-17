<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Pengecekan Server PKBM Mandiri</h2>";
echo "PHP Version: " . phpversion() . "<br>";

$dirs = ['config', 'includes', 'modules', 'data', 'assets', 'uploads'];
echo "<h3>Status Folder:</h3><ul>";
foreach ($dirs as $d) {
    if (is_dir(__DIR__ . '/' . $d)) {
        echo "<li style='color:green;'>[OK] Folder $d ditemukan</li>";
    } else {
        echo "<li style='color:red;'>[TIDAK DITEMUKAN] Folder $d BELUM ada! Silakan unzip.</li>";
    }
}
echo "</ul>";

echo "<h3>Status Database:</h3>";
try {
    require_once __DIR__ . '/config/app.php';
    require_once __DIR__ . '/config/database.php';
    $pdo = getDB();
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $total = $stmt->fetchColumn();
    echo "<p style='color:green; font-weight:bold;'>[BERHASIL] Database terhubung! Terdata $total siswa.</p>";
} catch (Throwable $e) {
    echo "<p style='color:red;'>[ERROR] " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "<p><a href='index.php'>Buka Halaman Utama</a></p>";

