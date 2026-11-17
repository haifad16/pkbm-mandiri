<?php
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/config/app.php';
}

$role = $_SESSION['user_role'] ?? 'siswa';
$currentScript = $_SERVER['SCRIPT_NAME'] ?? '';

// Hitung pending pendaftar untuk badge
$pendingRegCount = 0;
if ($role === 'admin') {
    try {
        $pdo = getDB();
        $pendingRegCount = (int)$pdo->query("SELECT COUNT(*) FROM registrations WHERE status_verifikasi = 'menunggu'")->fetchColumn();
    } catch (Exception $e) {}
}

function isNavActive($pathPart, $currentScript) {
    return strpos($currentScript, $pathPart) !== false ? 'bg-blue-600 text-white shadow-sm font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium';
}
?>

<!-- Sidebar Backdrop (Mobile) -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/50 z-30 hidden lg:hidden"></div>

<!-- Sidebar Component -->
<aside id="admin-sidebar" class="fixed lg:static inset-y-0 left-0 z-30 w-64 bg-white border-r border-slate-200 flex flex-col flex-shrink-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out no-print">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center justify-between px-5 border-b border-slate-200">
        <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center text-white text-lg font-bold">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div>
                <div class="font-extrabold text-sm text-slate-900 leading-tight">PKBM MANDIRI</div>
                <div class="text-[10px] text-slate-500 font-semibold tracking-wider uppercase">Panel <?= ucfirst($role) ?></div>
            </div>
        </a>
        <button id="sidebar-close-btn" class="lg:hidden text-slate-400 hover:text-slate-600">
            <i class="bi bi-x-lg text-lg"></i>
        </button>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

        <?php if ($role === 'admin'): ?>
            <!-- ADMIN MENU -->
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Utama</div>
            <a href="<?= BASE_URL ?>/modules/admin/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('admin/index.php', $currentScript) ?>">
                <i class="bi bi-speedometer2 text-lg"></i>
                <span>Dashboard Admin</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/admin/pendaftar.php" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('admin/pendaftar.php', $currentScript) ?>">
                <div class="flex items-center gap-3">
                    <i class="bi bi-person-plus-fill text-lg"></i>
                    <span>Pendaftar Baru</span>
                </div>
                <?php if ($pendingRegCount > 0): ?>
                    <span class="px-2 py-0.5 text-xs font-bold bg-rose-500 text-white rounded-full"><?= $pendingRegCount ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/modules/admin/siswa.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('admin/siswa.php', $currentScript) || isNavActive('admin/siswa_edit.php', $currentScript) ?>">
                <i class="bi bi-people-fill text-lg"></i>
                <span>Database Murid (556)</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/admin/siswa_tahun_ajaran.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('admin/siswa_tahun_ajaran.php', $currentScript) ?>">
                <i class="bi bi-arrow-repeat text-lg"></i>
                <span>Tahun Ajaran / Rombel</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/admin/guru.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('admin/guru.php', $currentScript) ?>">
                <i class="bi bi-person-workspace text-lg"></i>
                <span>Data Guru & Tutor</span>
            </a>

            <div class="pt-4 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Akademik & Ujian</div>
            <a href="<?= BASE_URL ?>/modules/admin/ujian_rekap.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('admin/ujian_rekap.php', $currentScript) ?>">
                <i class="bi bi-journal-check text-lg"></i>
                <span>Rekap Nilai Semua Mapel</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/admin/bank_soal_arsip.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('admin/bank_soal_arsip.php', $currentScript) ?>">
                <i class="bi bi-archive-fill text-lg"></i>
                <span>Bank Soal & Arsip Guru</span>
            </a>

            <div class="pt-4 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pengaturan & Web CMS</div>
            <a href="<?= BASE_URL ?>/modules/admin/cms_profil.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('admin/cms_profil.php', $currentScript) ?>">
                <i class="bi bi-pencil-square text-lg"></i>
                <span>Profil & Visi Misi</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/admin/cms_galeri.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('admin/cms_galeri.php', $currentScript) ?>">
                <i class="bi bi-images text-lg"></i>
                <span>Kelola Galeri Foto</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/admin/pengaturan.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('admin/pengaturan.php', $currentScript) ?>">
                <i class="bi bi-gear-fill text-lg"></i>
                <span>Pengaturan & Drive</span>
            </a>

        <?php elseif ($role === 'guru'): ?>
            <!-- GURU MENU -->
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Portal Guru / Tutor</div>
            <a href="<?= BASE_URL ?>/modules/guru/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('guru/index.php', $currentScript) ?>">
                <i class="bi bi-speedometer2 text-lg"></i>
                <span>Dashboard Guru</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/guru/ujian_buat.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('guru/ujian_buat.php', $currentScript) ?>">
                <i class="bi bi-plus-circle-fill text-lg"></i>
                <span>Buat Ujian Baru</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/guru/ujian_list.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('guru/ujian_list.php', $currentScript) || isNavActive('guru/soal_manage.php', $currentScript) ?>">
                <i class="bi bi-journal-text text-lg"></i>
                <span>Daftar Ujian Saya</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/guru/koreksi_esai.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('guru/koreksi_esai.php', $currentScript) ?>">
                <i class="bi bi-check2-square text-lg"></i>
                <span>Koreksi Soal Esai</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/guru/rekap_nilai.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('guru/rekap_nilai.php', $currentScript) ?>">
                <i class="bi bi-printer-fill text-lg"></i>
                <span>Rekap Nilai & Cetak</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/guru/bank_soal.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('guru/bank_soal.php', $currentScript) ?>">
                <i class="bi bi-folder-fill text-lg"></i>
                <span>Bank Soal Guru</span>
            </a>

        <?php else: ?>
            <!-- SISWA MENU -->
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Portal Murid</div>
            <a href="<?= BASE_URL ?>/modules/siswa/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('siswa/index.php', $currentScript) ?>">
                <i class="bi bi-person-badge-fill text-lg"></i>
                <span>Biodata & Rombel</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/siswa/kartu.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('siswa/kartu.php', $currentScript) ?>">
                <i class="bi bi-card-heading text-lg"></i>
                <span>Kartu Pelajar Digital</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/siswa/ujian.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('siswa/ujian.php', $currentScript) || isNavActive('siswa/kerjakan.php', $currentScript) ?>">
                <i class="bi bi-pencil-fill text-lg"></i>
                <span>Ujian Online (CBT)</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/siswa/nilai.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition <?= isNavActive('siswa/nilai.php', $currentScript) ?>">
                <i class="bi bi-award-fill text-lg"></i>
                <span>Hasil & Nilai Ujian</span>
            </a>
        <?php endif; ?>

        <div class="pt-6 border-t border-slate-200 mt-6">
            <a href="<?= BASE_URL ?>/modules/auth/logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-rose-600 hover:bg-rose-50 font-medium transition">
                <i class="bi bi-box-arrow-right text-lg"></i>
                <span>Keluar / Logout</span>
            </a>
        </div>

    </div>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-slate-200 text-xs text-slate-400 text-center">
        PKBM Mandiri &copy; <?= date('Y') ?>
    </div>
</aside>

