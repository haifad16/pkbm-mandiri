<?php
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/config/app.php';
}
$flash = getFlash();
$schoolName = getSetting('nama_sekolah', APP_NAME);
$schoolNpsn = getSetting('npsn', APP_NPSN);
$waNumber = getSetting('whatsapp', APP_WHATSAPP);
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?><?= htmlspecialchars($schoolName) ?> (Pendidikan Kesetaraan)</title>
    
    <!-- PWA Meta -->
    <link rel="manifest" href="<?= BASE_URL ?>/assets/manifest.json">
    <link rel="icon" type="image/jpeg" href="<?= BASE_URL ?>/assets/img/logo.jpg">
    <meta name="theme-color" content="#1e40af">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($schoolName) ?>">

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
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen antialiased">

    <!-- Top Announcement Bar -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-4">
                <span><i class="bi bi-patch-check-fill text-emerald-400 mr-1"></i> NPSN: <strong><?= htmlspecialchars($schoolNpsn) ?></strong></span>
                <span class="hidden sm:inline"><i class="bi bi-geo-alt-fill text-rose-400 mr-1"></i> Kec. Mataraman, Kab. Banjar</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $waNumber) ?>" target="_blank" class="hover:text-white flex items-center gap-1 transition">
                    <i class="bi bi-whatsapp text-emerald-400"></i> Konsultasi Pendaftaran
                </a>
                <span class="text-slate-600">|</span>
                <button id="pwa-install-btn" class="hidden text-amber-300 hover:text-white flex items-center gap-1 font-medium transition">
                    <i class="bi bi-phone"></i> Pasang Aplikasi (APK)
                </button>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="bg-white/95 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo & Brand -->
                <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-3 group">
                    <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="Logo PKBM Mandiri" class="w-12 h-12 sm:w-14 sm:h-14 object-contain rounded-2xl shadow-md group-hover:scale-105 transition-transform bg-white p-0.5 border border-slate-200">
                    <div>
                        <div class="font-extrabold text-xl text-slate-900 tracking-tight flex items-center gap-2">
                            <?= htmlspecialchars($schoolName) ?>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">Resmi</span>
                        </div>
                        <div class="text-xs text-slate-500 font-medium">Pendidikan Kesetaraan Paket A • B • C</div>
                    </div>
                </a>

                <!-- Desktop Menu -->
                <nav class="hidden md:flex items-center gap-6 font-medium text-sm text-slate-600">
                    <a href="<?= BASE_URL ?>/index.php#beranda" class="hover:text-blue-600 transition">Beranda</a>
                    <a href="<?= BASE_URL ?>/index.php#profil" class="hover:text-blue-600 transition">Profil & Visi Misi</a>
                    <a href="<?= BASE_URL ?>/index.php#program" class="hover:text-blue-600 transition">Program Belajar</a>
                    <a href="<?= BASE_URL ?>/index.php#guru" class="hover:text-blue-600 transition">Tutor / Guru</a>
                    <a href="<?= BASE_URL ?>/index.php#galeri" class="hover:text-blue-600 transition">Galeri</a>
                    
                    <div class="h-5 w-[1px] bg-slate-200"></div>

                    <!-- Tombol Pendaftaran PPDB -->
                    <a href="<?= BASE_URL ?>/modules/pendaftaran/index.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm hover:shadow transition transform active:scale-95">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Daftar Murid Baru</span>
                    </a>

                    <!-- Tombol Login / Portal -->
                    <?php if (isLoggedIn()): ?>
                        <?php 
                        $user = currentUser();
                        $roleUrl = BASE_URL . '/modules/' . $user['role'] . '/index.php';
                        ?>
                        <a href="<?= $roleUrl ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold transition">
                            <i class="bi bi-grid-fill"></i>
                            <span>Dashboard (<?= ucfirst($user['role']) ?>)</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/modules/auth/login.php" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm transition">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>Masuk / Login</span>
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Mobile Hamburger Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none" aria-label="Toggle Menu">
                    <i class="bi bi-list text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-100 bg-white px-4 pt-3 pb-6 space-y-3">
            <a href="<?= BASE_URL ?>/index.php#beranda" class="block py-2 text-slate-700 font-medium hover:text-blue-600">Beranda</a>
            <a href="<?= BASE_URL ?>/index.php#profil" class="block py-2 text-slate-700 font-medium hover:text-blue-600">Profil & Visi Misi</a>
            <a href="<?= BASE_URL ?>/index.php#program" class="block py-2 text-slate-700 font-medium hover:text-blue-600">Program Kesetaraan</a>
            <a href="<?= BASE_URL ?>/index.php#guru" class="block py-2 text-slate-700 font-medium hover:text-blue-600">Tutor / Guru</a>
            <a href="<?= BASE_URL ?>/index.php#galeri" class="block py-2 text-slate-700 font-medium hover:text-blue-600">Galeri Foto</a>
            <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
                <a href="<?= BASE_URL ?>/modules/pendaftaran/index.php" class="text-center py-2.5 bg-emerald-600 text-white font-semibold rounded-xl">
                    <i class="bi bi-person-plus-fill mr-1"></i> Formulir Pendaftaran Murid Baru
                </a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/modules/<?= currentUser()['role'] ?>/index.php" class="text-center py-2.5 bg-slate-900 text-white font-semibold rounded-xl">
                        <i class="bi bi-grid-fill mr-1"></i> Buka Dashboard (<?= ucfirst(currentUser()['role']) ?>)
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/modules/auth/login.php" class="text-center py-2.5 bg-blue-600 text-white font-semibold rounded-xl">
                        <i class="bi bi-box-arrow-in-right mr-1"></i> Masuk (Siswa / Guru / Admin)
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Global Flash Notification Alert -->
    <?php if ($flash): ?>
    <div id="flash-alert" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
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

    <main class="flex-grow">

