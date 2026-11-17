<?php
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/config/app.php';
}

if (!isLoggedIn()) {
    setFlash('warning', 'Sesi Anda telah berakhir. Silakan login kembali.');
    redirect('modules/auth/login.php');
}

$user = currentUser();
$userRole = $_SESSION['user_role'] ?? 'siswa';
$flash = getFlash();
$schoolName = getSetting('nama_sekolah', APP_NAME);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Dashboard <?= htmlspecialchars($schoolName) ?></title>

    <!-- PWA Meta -->
    <link rel="manifest" href="<?= BASE_URL ?>/assets/manifest.json">
    <meta name="theme-color" content="#1e40af">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-slate-100 text-slate-800 antialiased flex h-screen overflow-hidden">

    <!-- Sidebar Component Included Below -->
    <?php require_once BASE_PATH . '/includes/admin_sidebar.php'; ?>

    <!-- Main Content Area Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-6 z-20 flex-shrink-0 no-print">
            <div class="flex items-center gap-3">
                <button id="sidebar-toggle-btn" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <i class="bi bi-list text-2xl"></i>
                </button>
                <div class="font-bold text-lg text-slate-800 hidden sm:block">
                    <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?>
                </div>
            </div>

            <!-- User Menu & Shortcuts -->
            <div class="flex items-center gap-3 sm:gap-4">
                <a href="<?= BASE_URL ?>/index.php" target="_blank" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center gap-1.5 transition">
                    <i class="bi bi-box-arrow-up-right text-blue-600"></i>
                    <span class="hidden md:inline">Lihat Web Utama</span>
                </a>

                <div class="h-6 w-[1px] bg-slate-200"></div>

                <!-- User Profile Badge -->
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="hidden md:block text-left">
                        <div class="text-xs font-bold text-slate-900 leading-tight">
                            <?= htmlspecialchars($user['full_name'] ?? 'Pengguna') ?>
                        </div>
                        <div class="text-[10px] uppercase font-semibold text-blue-600 tracking-wider">
                            <?= strtoupper($userRole) ?>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/modules/auth/logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?')" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Logout">
                        <i class="bi bi-box-arrow-right text-lg"></i>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Scrollable Content Body -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            
            <!-- Flash Notification -->
            <?php if ($flash): ?>
            <div id="flash-alert" class="mb-6">
                <?php
                $alertBg = [
                    'success' => 'bg-emerald-50 border-emerald-300 text-emerald-800',
                    'danger' => 'bg-rose-50 border-rose-300 text-rose-800',
                    'warning' => 'bg-amber-50 border-amber-300 text-amber-800',
                    'info' => 'bg-blue-50 border-blue-300 text-blue-800',
                ][$flash['type']] ?? 'bg-blue-50 border-blue-300 text-blue-800';

                $icon = [
                    'success' => 'bi-check-circle-fill text-emerald-500',
                    'danger' => 'bi-exclamation-triangle-fill text-rose-500',
                    'warning' => 'bi-exclamation-circle-fill text-amber-500',
                    'info' => 'bi-info-circle-fill text-blue-500',
                ][$flash['type']] ?? 'bi-info-circle-fill text-blue-500';
                ?>
                <div class="flex items-center justify-between p-4 rounded-xl border <?= $alertBg ?> shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="bi <?= $icon ?> text-xl"></i>
                        <div class="text-sm font-medium"><?= htmlspecialchars($flash['message']) ?></div>
                    </div>
                    <button onclick="this.closest('#flash-alert').remove()" class="text-slate-400 hover:text-slate-600">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <?php endif; ?>

