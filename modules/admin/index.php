<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('admin');

$pageTitle = 'Dashboard Administrator PKBM';
$pdo = getDB();

// Statistik Data
$totalSiswa = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'aktif'")->fetchColumn();
$totalPaketA = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE paket = 'Paket A' AND status = 'aktif'")->fetchColumn();
$totalPaketB = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE paket = 'Paket B' AND status = 'aktif'")->fetchColumn();
$totalPaketC = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE paket = 'Paket C' AND status = 'aktif'")->fetchColumn();

$totalPendaftar = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
$pendingPendaftar = (int)$pdo->query("SELECT COUNT(*) FROM registrations WHERE status_verifikasi = 'menunggu'")->fetchColumn();

$totalGuru = (int)$pdo->query("SELECT COUNT(*) FROM teachers WHERE status_aktif = 1")->fetchColumn();
$totalUjian = (int)$pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn();

// Pendaftar Baru Terbaru
$recentRegistrations = $pdo->query("SELECT * FROM registrations ORDER BY created_at DESC LIMIT 5")->fetchAll();

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold uppercase tracking-wider text-blue-200">
                    <i class="bi bi-shield-check"></i> Administrator Pusat PKBM Mandiri
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight mt-2">
                    Sistem Informasi & Database Terpadu
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">
                    Pangkalan data 556 warga belajar, manajemen guru, asesmen ujian online CBT, penerimaan murid baru, dan CMS profil lembaga.
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="<?= BASE_URL ?>/modules/admin/pendaftar.php" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Verifikasi Pendaftar (<?= $pendingPendaftar ?>)</span>
                </a>
                <a href="<?= BASE_URL ?>/modules/admin/siswa.php" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                    <i class="bi bi-people-fill"></i>
                    <span>Data 556 Murid</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Murid Aktif</div>
                <div class="text-2xl font-black text-slate-900 leading-tight"><?= $totalSiswa ?></div>
                <div class="text-[10px] text-blue-600 font-semibold">Tercatat di Database</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="bi bi-person-workspace"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tutor & Guru</div>
                <div class="text-2xl font-black text-slate-900 leading-tight"><?= $totalGuru ?></div>
                <div class="text-[10px] text-emerald-600 font-semibold">Tenaga Pendidik</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pendaftar PPDB</div>
                <div class="text-2xl font-black text-slate-900 leading-tight"><?= $totalPendaftar ?></div>
                <div class="text-[10px] text-amber-600 font-bold"><?= $pendingPendaftar ?> Menunggu Verifikasi</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="bi bi-journal-check"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Instrumen Ujian</div>
                <div class="text-2xl font-black text-slate-900 leading-tight"><?= $totalUjian ?></div>
                <div class="text-[10px] text-purple-600 font-semibold">CBT Online Aktif</div>
            </div>
        </div>

    </div>

    <!-- Breakdown Rombel & Jenjang -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-6 rounded-3xl border border-amber-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900 uppercase">Setara SD</span>
                <h3 class="text-xl font-black text-amber-950 mt-1">Paket A</h3>
                <p class="text-xs text-amber-800 mt-0.5">Warga Belajar Usia Dini & Dewasa</p>
            </div>
            <div class="text-3xl font-black text-amber-900"><?= $totalPaketA ?> <span class="text-xs text-amber-700 font-normal">Siswa</span></div>
        </div>

        <div class="bg-gradient-to-br from-sky-50 to-blue-50 p-6 rounded-3xl border border-sky-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-200 text-sky-900 uppercase">Setara SMP</span>
                <h3 class="text-xl font-black text-sky-950 mt-1">Paket B</h3>
                <p class="text-xs text-sky-800 mt-0.5">Kelas 7, 8, dan 9</p>
            </div>
            <div class="text-3xl font-black text-sky-900"><?= $totalPaketB ?> <span class="text-xs text-sky-700 font-normal">Siswa</span></div>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 p-6 rounded-3xl border border-emerald-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200 text-emerald-900 uppercase">Setara SMA</span>
                <h3 class="text-xl font-black text-emerald-950 mt-1">Paket C</h3>
                <p class="text-xs text-emerald-800 mt-0.5">Kelas 10, 11, dan 12</p>
            </div>
            <div class="text-3xl font-black text-emerald-900"><?= $totalPaketC ?> <span class="text-xs text-emerald-700 font-normal">Siswa</span></div>
        </div>
    </div>

    <!-- Pendaftar Baru Masuk -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-base text-slate-900">Pendaftar PPDB Online Terbaru</h3>
                <p class="text-xs text-slate-500">Berkas masuk yang perlu diverifikasi dan dimasukkan ke database murid</p>
            </div>
            <a href="<?= BASE_URL ?>/modules/admin/pendaftar.php" class="text-xs font-bold text-blue-600 hover:underline">
                Kelola Semua Pendaftar &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200 text-left">
                        <th class="py-3 px-4">No. Pendaftaran</th>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">NIK</th>
                        <th class="py-3 px-4">Program Pilihan</th>
                        <th class="py-3 px-4">No. HP / WA</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($recentRegistrations)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada data pendaftar baru yang masuk.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentRegistrations as $reg): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-mono font-bold text-blue-700"><?= htmlspecialchars($reg['no_pendaftaran']) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($reg['nama_lengkap']) ?></td>
                                <td class="py-3 px-4 font-mono text-slate-600"><?= htmlspecialchars($reg['nik']) ?></td>
                                <td class="py-3 px-4 font-medium text-slate-800"><?= htmlspecialchars($reg['paket_pilihan']) ?></td>
                                <td class="py-3 px-4 font-semibold text-slate-700"><?= htmlspecialchars($reg['hp']) ?></td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase <?= $reg['status_verifikasi'] === 'diterima' ? 'bg-emerald-100 text-emerald-800' : ($reg['status_verifikasi'] === 'ditolak' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') ?>">
                                        <?= $reg['status_verifikasi'] ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="<?= BASE_URL ?>/modules/admin/pendaftar.php?detail=<?= $reg['id'] ?>" class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition inline-flex items-center gap-1 shadow-sm">
                                        <i class="bi bi-eye"></i> Periksa Berkas
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

