<?php
/**
 * Global Helper Functions
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

/**
 * Ambil nilai pengaturan dari database
 */
function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        $res = ($val !== false) ? (string)$val : $default;
        $cache[$key] = $res;
        return $res;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Simpan atau update pengaturan
 */
function updateSetting(string $key, string $value): bool {
    try {
        $pdo = getDB();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) 
                VALUES (?, ?, datetime('now')) 
                ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, updated_at = datetime('now')");
        } else {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) 
                VALUES (?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        }
        return $stmt->execute([$key, $value]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Sanitasi string input
 */
function sanitize(?string $str): string {
    if ($str === null) return '';
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash message untuk notifikasi tampilan
 */
function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) @session_start();
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Ambil flash message
 */
function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) @session_start();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Redirect halaman
 */
function redirect(string $url): void {
    if (strpos($url, 'http') !== 0) {
        $url = BASE_URL . '/' . ltrim($url, '/');
    }
    header("Location: $url");
    exit();
}

/**
 * Cek apakah user sedang login
 */
function isLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) @session_start();
    return !empty($_SESSION['user_id']);
}

/**
 * Ambil data user yang sedang login
 */
function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return $_SESSION['user'] ?? null;
}

/**
 * Proteksi akses berdasarkan peran (Role)
 */
function requireRole($roles): void {
    if (!isLoggedIn()) {
        setFlash('warning', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        redirect('modules/auth/login.php');
    }
    $roles = is_array($roles) ? $roles : [$roles];
    $userRole = $_SESSION['user_role'] ?? '';
    if (!in_array($userRole, $roles)) {
        setFlash('danger', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        if ($userRole === 'siswa') {
            redirect('modules/siswa/index.php');
        } elseif ($userRole === 'guru') {
            redirect('modules/guru/index.php');
        } else {
            redirect('modules/admin/index.php');
        }
    }
}

/**
 * Format tanggal Indonesia: 17 November 2026
 */
function formatTanggalIndo(?string $dateStr, bool $includeTime = false): string {
    if (empty($dateStr) || $dateStr === '0000-00-00' || $dateStr === '0000-00-00 00:00:00') {
        return '-';
    }
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $time = strtotime($dateStr);
    if (!$time) return $dateStr;
    
    $d = date('j', $time);
    $m = $bulan[(int)date('n', $time)];
    $y = date('Y', $time);
    
    $result = "$d $m $y";
    if ($includeTime) {
        $result .= ' ' . date('H:i', $time) . ' WITA';
    }
    return $result;
}

/**
 * Hitung usia berdasarkan tanggal lahir
 */
function hitungUsia(?string $tglLahir): int {
    if (empty($tglLahir)) return 0;
    try {
        $dob = new DateTime($tglLahir);
        $today = new DateTime('today');
        return $dob->diff($today)->y;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Generate Nomor Pendaftaran Otomatis
 */
function generateNoPendaftaran(): string {
    $pdo = getDB();
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) FROM registrations WHERE strftime('%Y', created_at) = '$year'");
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
        $stmt = $pdo->query("SELECT COUNT(*) FROM registrations WHERE YEAR(created_at) = $year");
    }
    $count = (int)$stmt->fetchColumn() + 1;
    return 'REG-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

/**
 * Upload file berkas pendaftaran atau foto
 */
function uploadFile(array $file, string $targetFolder, array $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'], int $maxSizeMb = 10): array {
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Tidak ada file yang diunggah atau terjadi kesalahan upload.'];
    }

    if ($file['size'] > ($maxSizeMb * 1024 * 1024)) {
        return ['success' => false, 'message' => "Ukuran file terlalu besar. Maksimal {$maxSizeMb} MB."];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        return ['success' => false, 'message' => 'Format file tidak didukung. Hanya file ' . implode(', ', $allowedExtensions) . ' yang diperbolehkan.'];
    }

    if (!is_dir($targetFolder)) {
        @mkdir($targetFolder, 0777, true);
    }

    $newFileName = uniqid('dok_', true) . '.' . $ext;
    $destination = rtrim($targetFolder, '/\\') . DIRECTORY_SEPARATOR . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'filename' => $newFileName,
            'path' => $destination,
            'url' => BASE_URL . '/uploads/' . basename($targetFolder) . '/' . $newFileName
        ];
    }

    return ['success' => false, 'message' => 'Gagal memindahkan file ke folder tujuan di server.'];
}

/**
 * Format respon JSON
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

