<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('siswa');

$pageTitle = 'Ujian Online (CBT)';
$studentId = $_SESSION['student_id'] ?? 0;
$pdo = getDB();

$stmtStudent = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmtStudent->execute([$studentId]);
$student = $stmtStudent->fetch();

if (!$student) {
    redirect('modules/siswa/index.php');
}

$paketSiswa = $student['paket'] ?? 'Paket B';

// Ambil semua ujian yang aktif untuk paket ini
$stmtExams = $pdo->prepare("SELECT e.*, 
    (SELECT COUNT(*) FROM exam_questions q WHERE q.exam_id = e.id) as total_soal,
    (SELECT COUNT(*) FROM exam_questions q WHERE q.exam_id = e.id AND q.jenis = 'pg') as total_pg,
    (SELECT COUNT(*) FROM exam_questions q WHERE q.exam_id = e.id AND q.jenis = 'esai') as total_esai,
    a.id as attempt_id, a.status as attempt_status, a.nilai_total, a.status_koreksi, a.waktu_selesai
    FROM exams e
    LEFT JOIN exam_attempts a ON a.exam_id = e.id AND a.student_id = ?
    WHERE e.status = 'aktif' AND (e.paket = ? OR e.paket = 'Semua')
    ORDER BY e.created_at DESC");
$stmtExams->execute([$studentId, $paketSiswa]);
$exams = $stmtExams->fetchAll();

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Header Banner -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 mb-2">
                <i class="bi bi-shield-check"></i> Sistem Ujian Kesetaraan Online (CBT)
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Daftar Ujian Tersedia</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Ujian kesetaraan untuk jenjang <strong><?= htmlspecialchars($paketSiswa) ?></strong>. Waktu pengerjaan fleksibel tanpa pembatasan kaku yang menyulitkan siswa.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-4 py-2 bg-slate-100 rounded-xl font-bold text-xs text-slate-700">
                <i class="bi bi-person-fill text-blue-600"></i> Rombel: <?= htmlspecialchars($student['rombel_saat_ini'] ?: $student['paket']) ?>
            </span>
        </div>
    </div>

    <!-- Exam Cards List -->
    <?php if (empty($exams)): ?>
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="bi bi-calendar-x"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800">Belum Ada Ujian Aktif</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                Saat ini belum ada jadwal ujian yang dipublikasikan untuk jenjang <?= htmlspecialchars($paketSiswa) ?>. Silakan cek kembali secara berkala atau hubungi tutor Anda.
            </p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($exams as $ex): ?>
                <?php
                $isCompleted = ($ex['attempt_status'] === 'selesai');
                $isInProgress = ($ex['attempt_status'] === 'sedang_mengerjakan');
                ?>
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition p-6 flex flex-col justify-between">
                    <div>
                        <!-- Top Meta -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800">
                                <?= htmlspecialchars($ex['mapel']) ?>
                            </span>
                            <span class="text-xs font-semibold text-slate-400">
                                <?= htmlspecialchars($ex['paket']) ?>
                            </span>
                        </div>

                        <!-- Judul Ujian -->
                        <h3 class="font-extrabold text-lg text-slate-900 leading-snug">
                            <?= htmlspecialchars($ex['judul']) ?>
                        </h3>
                        <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">
                            <?= htmlspecialchars($ex['deskripsi'] ?: 'Ujian kesetaraan mandiri berbasis komputer dan mobile.') ?>
                        </p>

                        <!-- Badges Rincian Soal -->
                        <div class="flex flex-wrap items-center gap-3 mt-4 pt-4 border-t border-slate-100 text-xs text-slate-600">
                            <span class="flex items-center gap-1 font-medium">
                                <i class="bi bi-question-circle-fill text-blue-500"></i>
                                <?= $ex['total_soal'] ?> Soal (<?= $ex['total_pg'] ?> PG, <?= $ex['total_esai'] ?> Esai)
                            </span>
                            <span class="flex items-center gap-1 font-medium">
                                <i class="bi bi-hourglass-split text-amber-500"></i>
                                <?= $ex['durasi_menit'] > 0 ? $ex['durasi_menit'] . ' Menit' : 'Waktu Fleksibel' ?>
                            </span>
                            <span class="flex items-center gap-1 font-medium text-emerald-600">
                                <i class="bi bi-shuffle"></i> Soal Acak
                            </span>
                        </div>
                    </div>

                    <!-- Action Button & Status -->
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <?php if ($isCompleted): ?>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nilai Anda:</span>
                                <span class="text-2xl font-black <?= $ex['nilai_total'] >= 75 ? 'text-emerald-600' : 'text-amber-600' ?>">
                                    <?= number_format($ex['nilai_total'], 0) ?>
                                </span>
                                <span class="text-[10px] text-slate-400 ml-1">/ 100</span>
                            </div>
                            <a href="<?= BASE_URL ?>/modules/siswa/nilai.php" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition flex items-center gap-1.5">
                                <i class="bi bi-eye-fill"></i> Lihat Detail Nilai
                            </a>
                        <?php elseif ($isInProgress): ?>
                            <div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    <i class="bi bi-clock-history"></i> Belum Diselesaikan
                                </span>
                            </div>
                            <a href="<?= BASE_URL ?>/modules/siswa/kerjakan.php?id=<?= $ex['id'] ?>" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md transition flex items-center gap-1.5">
                                <i class="bi bi-play-fill text-lg"></i> Lanjutkan Ujian
                            </a>
                        <?php else: ?>
                            <div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                    <i class="bi bi-circle"></i> Siap Dikerjakan
                                </span>
                            </div>
                            <a href="<?= BASE_URL ?>/modules/siswa/kerjakan.php?id=<?= $ex['id'] ?>" onclick="return confirm('Mulai mengerjakan ujian ini sekarang? Pastikan Anda fokus dan koneksi stabil.')" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-1.5">
                                <i class="bi bi-pencil-fill"></i> Mulai Ujian
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

