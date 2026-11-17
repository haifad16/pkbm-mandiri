<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole(['guru', 'admin']);

$pageTitle = 'Dashboard Guru / Tutor';
$pdo = getDB();
$teacherId = $_SESSION['teacher_id'] ?? 1;

// Statistik
$totalUjian = (int)$pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn();
$totalMurid = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'aktif'")->fetchColumn();
$totalAttempts = (int)$pdo->query("SELECT COUNT(*) FROM exam_attempts WHERE status = 'selesai'")->fetchColumn();
$pendingEsai = (int)$pdo->query("SELECT COUNT(*) FROM exam_attempts WHERE status_koreksi = 'belum' AND status = 'selesai'")->fetchColumn();

// Ujian Terbaru
$stmtRecentExams = $pdo->query("SELECT e.*, 
    (SELECT COUNT(*) FROM exam_questions q WHERE q.exam_id = e.id) as total_soal,
    (SELECT COUNT(*) FROM exam_attempts a WHERE a.exam_id = e.id AND a.status = 'selesai') as total_peserta
    FROM exams e 
    ORDER BY e.created_at DESC LIMIT 5");
$recentExams = $stmtRecentExams->fetchAll();

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Welcome Header Card -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-indigo-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold uppercase tracking-wider text-blue-100">
                        <i class="bi bi-person-workspace mr-1"></i> Dewan Tutor PKBM Mandiri
                    </span>
                    <?php if ($pendingEsai > 0): ?>
                        <span class="px-3 py-1 bg-amber-500 rounded-full text-xs font-bold text-white animate-pulse">
                            <i class="bi bi-bell-fill"></i> <?= $pendingEsai ?> Esai Perlu Koreksi
                        </span>
                    <?php endif; ?>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Panel Tutor & Penilaian Ujian
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">
                    Kelola soal pilihan ganda & esai secara otomatis dan mandiri. Rekap nilai dan download laporan hasil belajar warga belajar PKBM Mandiri.
                </p>
            </div>

            <!-- Quick Action -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="<?= BASE_URL ?>/modules/guru/ujian_buat.php" class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm shadow-md transition flex items-center gap-2 transform active:scale-95">
                    <i class="bi bi-plus-circle-fill text-lg"></i>
                    <span>Buat Ujian Baru</span>
                </a>
                <a href="<?= BASE_URL ?>/modules/guru/koreksi_esai.php" class="px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-sm backdrop-blur-md transition flex items-center gap-2">
                    <i class="bi bi-check2-square text-lg"></i>
                    <span>Koreksi Esai (<?= $pendingEsai ?>)</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Ujian Aktif</div>
            <div class="text-2xl font-black text-slate-900 mt-1"><?= $totalUjian ?></div>
            <div class="text-[11px] text-blue-600 font-medium mt-1">
                <a href="<?= BASE_URL ?>/modules/guru/ujian_list.php" class="hover:underline">Kelola Ujian &rarr;</a>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Esai Belum Dikoreksi</div>
            <div class="text-2xl font-black <?= $pendingEsai > 0 ? 'text-amber-600' : 'text-emerald-600' ?> mt-1">
                <?= $pendingEsai ?>
            </div>
            <div class="text-[11px] text-slate-500 mt-1">
                <a href="<?= BASE_URL ?>/modules/guru/koreksi_esai.php" class="hover:underline text-amber-600 font-bold">Periksa Lembar Esai &rarr;</a>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Peserta Telah Ujian</div>
            <div class="text-2xl font-black text-emerald-600 mt-1"><?= $totalAttempts ?></div>
            <div class="text-[11px] text-slate-500 mt-1">Dari 556 Warga Belajar</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Rekapitulasi Nilai</div>
            <div class="text-2xl font-black text-blue-700 mt-1">Cetak & Excel</div>
            <div class="text-[11px] text-blue-600 font-medium mt-1">
                <a href="<?= BASE_URL ?>/modules/guru/rekap_nilai.php" class="hover:underline">Buka Rekap &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Recent Exams Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-base text-slate-900">Daftar Ujian Terbaru</h3>
                <p class="text-xs text-slate-500">Mata pelajaran dan asesmen yang dikelola oleh tutor</p>
            </div>
            <a href="<?= BASE_URL ?>/modules/guru/ujian_list.php" class="text-xs font-bold text-blue-600 hover:underline">
                Lihat Semua Ujian &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200 text-left">
                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4">Judul Ujian</th>
                        <th class="py-3 px-4">Paket / Jenjang</th>
                        <th class="py-3 px-4 text-center">Jumlah Soal</th>
                        <th class="py-3 px-4 text-center">Peserta Selesai</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($recentExams as $ex): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($ex['mapel']) ?></td>
                            <td class="py-3.5 px-4 font-medium text-slate-800"><?= htmlspecialchars($ex['judul']) ?></td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <?= htmlspecialchars($ex['paket']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-700"><?= $ex['total_soal'] ?> Soal</td>
                            <td class="py-3.5 px-4 text-center font-bold text-emerald-600"><?= $ex['total_peserta'] ?> Murid</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase <?= $ex['status'] === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= $ex['status'] ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="<?= BASE_URL ?>/modules/guru/soal_manage.php?id=<?= $ex['id'] ?>" class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-xs transition" title="Kelola Soal PG & Esai">
                                        <i class="bi bi-list-task"></i> Soal
                                    </a>
                                    <a href="<?= BASE_URL ?>/modules/guru/rekap_nilai.php?exam_id=<?= $ex['id'] ?>" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-xs transition" title="Rekap Nilai">
                                        <i class="bi bi-printer"></i> Nilai
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

