<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('siswa');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('modules/siswa/ujian.php');
}

$examId = (int)($_POST['exam_id'] ?? 0);
$attemptId = (int)($_POST['attempt_id'] ?? 0);
$studentId = $_SESSION['student_id'] ?? 0;
$jawaban = $_POST['jawaban'] ?? [];

$pdo = getDB();

// Validasi ujian dan attempt
$stmtExam = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmtExam->execute([$examId]);
$exam = $stmtExam->fetch();

$stmtAttempt = $pdo->prepare("SELECT * FROM exam_attempts WHERE id = ? AND student_id = ?");
$stmtAttempt->execute([$attemptId, $studentId]);
$attempt = $stmtAttempt->fetch();

if (!$exam || !$attempt) {
    setFlash('danger', 'Data ujian atau pengerjaan tidak sah.');
    redirect('modules/siswa/ujian.php');
}

// Ambil semua soal
$stmtQ = $pdo->prepare("SELECT * FROM exam_questions WHERE exam_id = ?");
$stmtQ->execute([$examId]);
$questions = $stmtQ->fetchAll();

$totalBobotPg = 0;
$skorPgDidapat = 0;
$totalBobotEsai = 0;
$jumlahSoalEsai = 0;
$jumlahSoalPg = 0;
$benarPg = 0;
$salahPg = 0;

// Bersihkan jawaban lama jika ada
$pdo->prepare("DELETE FROM exam_answers WHERE attempt_id = ?")->execute([$attemptId]);

$insAns = $pdo->prepare("INSERT INTO exam_answers (attempt_id, question_id, jawaban_siswa, nilai_skor) VALUES (?, ?, ?, ?)");

foreach ($questions as $q) {
    $qid = $q['id'];
    $ans = trim($jawaban[$qid] ?? '');

    if ($q['jenis'] === 'pg') {
        $jumlahSoalPg++;
        $totalBobotPg += (float)$q['bobot'];

        $isCorrect = (strtoupper($ans) === strtoupper(trim($q['kunci_jawaban'])));
        $skor = $isCorrect ? (float)$q['bobot'] : 0;
        if ($isCorrect) {
            $benarPg++;
            $skorPgDidapat += $skor;
        } else {
            $salahPg++;
        }

        $insAns->execute([$attemptId, $qid, $ans, $skor]);
    } else {
        // Esai
        $jumlahSoalEsai++;
        $totalBobotEsai += (float)$q['bobot'];
        $insAns->execute([$attemptId, $qid, $ans, 0]); // Skor 0 sementara hingga dikoreksi guru
    }
}

// Hitung proporsi nilai PG (skala 0 - 100)
$nilaiPg = 0;
if ($totalBobotPg > 0) {
    $nilaiPg = ($skorPgDidapat / $totalBobotPg) * 100;
}

// Jika tidak ada esai, maka nilai total = nilai PG dan status koreksi sudah selesai
if ($jumlahSoalEsai === 0) {
    $nilaiTotal = $nilaiPg;
    $statusKoreksi = 'sudah';
} else {
    // Jika ada soal PG dan Esai, bobot standar: PG 60%, Esai 40% (atau sesuai bobot masing-masing)
    // Nilai PG dihitung proporsional
    $nilaiTotal = $nilaiPg * 0.6; // Nilai sementara sebelum esai dinilai
    $statusKoreksi = 'belum';
}

// Update status attempt
$stmtUpdate = $pdo->prepare("UPDATE exam_attempts SET 
    waktu_selesai = datetime('now'),
    status = 'selesai',
    nilai_pg = ?,
    nilai_total = ?,
    status_koreksi = ?
    WHERE id = ?");
$stmtUpdate->execute([round($nilaiPg, 1), round($nilaiTotal, 1), $statusKoreksi, $attemptId]);

$pageTitle = 'Ujian Selesai - ' . $exam['judul'];
require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden text-center p-8 sm:p-12">
        
        <!-- Celebration Badge -->
        <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-4xl mx-auto mb-6 shadow-inner animate-bounce">
            <i class="bi bi-check-lg"></i>
        </div>

        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            Ujian Berhasil Diselesaikan!
        </h1>
        <p class="text-slate-500 text-xs sm:text-sm mt-2 max-w-md mx-auto">
            Terima kasih telah mengerjakan <strong><?= htmlspecialchars($exam['judul']) ?></strong> dengan jujur dan tertib. Jawaban Anda telah tersimpan di sistem.
        </p>

        <!-- Result Box -->
        <div class="my-8 p-6 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200/60 pb-3">
                <span class="text-xs font-bold uppercase text-slate-400">Mata Pelajaran:</span>
                <span class="font-extrabold text-sm text-slate-900"><?= htmlspecialchars($exam['mapel']) ?> (<?= htmlspecialchars($exam['paket']) ?>)</span>
            </div>

            <!-- Statistik Pilihan Ganda -->
            <?php if ($jumlahSoalPg > 0): ?>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-700 block">Nilai Soal Pilihan Ganda:</span>
                        <span class="text-[11px] text-slate-400">Terdeteksi otomatis oleh sistem (Benar: <?= $benarPg ?>, Salah: <?= $salahPg ?>)</span>
                    </div>
                    <div class="text-xl font-black text-blue-700">
                        <?= number_format($nilaiPg, 1) ?> <span class="text-xs text-slate-400 font-normal">/ 100</span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Status Soal Esai -->
            <?php if ($jumlahSoalEsai > 0): ?>
                <div class="flex items-center justify-between border-t border-slate-200/60 pt-3">
                    <div>
                        <span class="text-xs font-bold text-slate-700 block">Soal Esai / Uraian (<?= $jumlahSoalEsai ?> Soal):</span>
                        <span class="text-[11px] text-amber-600 font-medium flex items-center gap-1 mt-0.5">
                            <i class="bi bi-hourglass-split"></i> Menunggu koreksi mandiri oleh Guru
                        </span>
                    </div>
                    <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-full uppercase">
                        Proses Koreksi
                    </span>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-between border-t border-slate-200/60 pt-3">
                    <div>
                        <span class="text-xs font-bold text-slate-700 block">Nilai Akhir Ujian:</span>
                        <span class="text-[11px] text-emerald-600 font-medium">100% Pilihan Ganda</span>
                    </div>
                    <div class="text-2xl font-black text-emerald-600">
                        <?= number_format($nilaiTotal, 0) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="<?= BASE_URL ?>/modules/siswa/nilai.php" class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center justify-center gap-2">
                <i class="bi bi-award-fill"></i> Lihat Rapor & Hasil Belajar
            </a>
            <a href="<?= BASE_URL ?>/modules/siswa/index.php" class="w-full sm:w-auto px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition">
                Kembali ke Dashboard Murid
            </a>
        </div>

    </div>
</div>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

