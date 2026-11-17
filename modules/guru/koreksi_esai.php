<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole(['guru', 'admin']);

$pageTitle = 'Koreksi Mandiri Soal Esai';
$pdo = getDB();

$selectedExamId = (int)($_GET['exam_id'] ?? 0);
$selectedAttemptId = (int)($_GET['attempt_id'] ?? 0);

// Ambil daftar ujian yang memiliki soal esai
$stmtExams = $pdo->query("SELECT e.id, e.judul, e.mapel, e.paket,
    (SELECT COUNT(*) FROM exam_questions q WHERE q.exam_id = e.id AND q.jenis = 'esai') as total_esai
    FROM exams e 
    WHERE (SELECT COUNT(*) FROM exam_questions q WHERE q.exam_id = e.id AND q.jenis = 'esai') > 0
    ORDER BY e.created_at DESC");
$examsWithEssay = $stmtExams->fetchAll();

if ($selectedExamId === 0 && !empty($examsWithEssay)) {
    $selectedExamId = $examsWithEssay[0]['id'];
}

// Handle Form Simpan Nilai Koreksi Esai
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_koreksi'])) {
    $attemptId = (int)$_POST['attempt_id'];
    $skorEsaiInput = $_POST['skor_esai'] ?? [];
    $catatanGuru = sanitize($_POST['catatan_guru'] ?? '');

    $totalSkorEsai = 0;
    $upAns = $pdo->prepare("UPDATE exam_answers SET nilai_skor = ?, koreksi_catatan = ? WHERE attempt_id = ? AND question_id = ?");

    foreach ($skorEsaiInput as $qid => $skorVal) {
        $skor = (float)$skorVal;
        $totalSkorEsai += $skor;
        $upAns->execute([$skor, 'Telah dikoreksi guru', $attemptId, (int)$qid]);
    }

    // Ambil data attempt untuk menghitung ulang nilai total
    $stmtAtt = $pdo->prepare("SELECT a.*, e.id as ex_id FROM exam_attempts a JOIN exams e ON a.exam_id = e.id WHERE a.id = ?");
    $stmtAtt->execute([$attemptId]);
    $att = $stmtAtt->fetch();

    if ($att) {
        $nilaiPg = (float)$att['nilai_pg'];
        // Hitung nilai akhir total (Bobot PG 60% + Esai 40%, atau skala 100)
        // Maksimum nilai esai dinormalisasi
        $nilaiEsaiNorm = min(100, $totalSkorEsai * 2.5); // Normalisasi ke skala 100 jika bobot 40
        $nilaiAkhir = round(($nilaiPg * 0.6) + ($nilaiEsaiNorm * 0.4), 1);

        $pdo->prepare("UPDATE exam_attempts SET 
            nilai_esai = ?, 
            nilai_total = ?, 
            status_koreksi = 'sudah', 
            catatan_guru = ? 
            WHERE id = ?")
            ->execute([round($nilaiEsaiNorm, 1), $nilaiAkhir, $catatanGuru, $attemptId]);

        setFlash('success', "Nilai esai berhasil disimpan! Nilai akhir {$nilaiAkhir} otomatis tampil di portal murid.");
        redirect("modules/guru/koreksi_esai.php?exam_id={$selectedExamId}");
    }
}

// Ambil data peserta yang mengerjakan ujian terpilih
$attempts = [];
if ($selectedExamId > 0) {
    $stmtAttempts = $pdo->prepare("SELECT a.*, s.nama, s.nik, s.rombel_saat_ini, s.hp 
        FROM exam_attempts a 
        JOIN students s ON a.student_id = s.id 
        WHERE a.exam_id = ? AND a.status = 'selesai'
        ORDER BY a.status_koreksi ASC, a.created_at DESC");
    $stmtAttempts->execute([$selectedExamId]);
    $attempts = $stmtAttempts->fetchAll();
}

// Jika guru sedang membuka lembar jawaban satu murid tertentu
$activeStudentAttempt = null;
$essayQuestionsAndAnswers = [];
if ($selectedAttemptId > 0) {
    $stmtDetail = $pdo->prepare("SELECT a.*, s.nama, s.nik, s.rombel_saat_ini, e.judul, e.mapel 
        FROM exam_attempts a 
        JOIN students s ON a.student_id = s.id 
        JOIN exams e ON a.exam_id = e.id 
        WHERE a.id = ?");
    $stmtDetail->execute([$selectedAttemptId]);
    $activeStudentAttempt = $stmtDetail->fetch();

    if ($activeStudentAttempt) {
        $stmtQA = $pdo->prepare("SELECT q.id as qid, q.nomor_urut, q.pertanyaan, q.kunci_jawaban as rubrik, q.bobot, 
            ans.jawaban_siswa, ans.nilai_skor 
            FROM exam_questions q 
            LEFT JOIN exam_answers ans ON ans.question_id = q.id AND ans.attempt_id = ? 
            WHERE q.exam_id = ? AND q.jenis = 'esai' 
            ORDER BY q.nomor_urut ASC");
        $stmtQA->execute([$selectedAttemptId, $activeStudentAttempt['exam_id']]);
        $essayQuestionsAndAnswers = $stmtQA->fetchAll();
    }
}

require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Top Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Koreksi Mandiri Soal Esai</h1>
            <p class="text-xs text-slate-500">Periksa lembar jawaban esai warga belajar secara mandiri, berikan skor dan evaluasi</p>
        </div>

        <!-- Filter Pilih Ujian -->
        <form method="GET" action="" class="flex items-center gap-2">
            <select name="exam_id" onchange="this.form.submit()" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white transition">
                <?php foreach ($examsWithEssay as $ex): ?>
                    <option value="<?= $ex['id'] ?>" <?= $selectedExamId == $ex['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ex['mapel']) ?> - <?= htmlspecialchars($ex['judul']) ?> (<?= $ex['total_esai'] ?> Esai)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- JIKA GURU MEMBUKA LEMBAR KOREKSI SATU SISWA -->
    <?php if ($activeStudentAttempt): ?>
        <div class="bg-white rounded-3xl border-2 border-blue-500 shadow-lg p-6 sm:p-8 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase">
                        Form Pemeriksaan Esai Murid
                    </span>
                    <h2 class="text-xl font-black text-slate-900 mt-1 uppercase">
                        <?= htmlspecialchars($activeStudentAttempt['nama']) ?>
                    </h2>
                    <div class="text-xs text-slate-500 mt-0.5">
                        NIK: <?= htmlspecialchars($activeStudentAttempt['nik']) ?> • Rombel: <?= htmlspecialchars($activeStudentAttempt['rombel_saat_ini']) ?>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-400">Nilai PG Siswa:</span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 font-black text-sm rounded-xl">
                        <?= number_format($activeStudentAttempt['nilai_pg'], 1) ?>
                    </span>
                    <a href="<?= BASE_URL ?>/modules/guru/koreksi_esai.php?exam_id=<?= $selectedExamId ?>" class="ml-3 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Tutup
                    </a>
                </div>
            </div>

            <!-- Form Penilaian Esai -->
            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="attempt_id" value="<?= $activeStudentAttempt['id'] ?>">
                
                <?php foreach ($essayQuestionsAndAnswers as $idx => $qa): ?>
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-sm text-blue-700">Soal Esai No. <?= $qa['nomor_urut'] ?></span>
                            <span class="text-xs font-bold text-slate-500">Bobot Maksimal: <?= $qa['bobot'] ?> Poin</span>
                        </div>

                        <!-- Pertanyaan -->
                        <div class="text-sm font-semibold text-slate-900">
                            <?= nl2br(htmlspecialchars($qa['pertanyaan'])) ?>
                        </div>

                        <!-- Panduan Rubrik Jawaban Guru -->
                        <?php if (!empty($qa['rubrik'])): ?>
                            <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200/60 text-xs text-amber-900">
                                <strong>Panduan Rubrik Guru:</strong> <?= htmlspecialchars($qa['rubrik']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Lembar Jawaban Siswa -->
                        <div class="bg-white p-4 rounded-xl border border-slate-200 space-y-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Jawaban yang Ditulis Siswa:</span>
                            <div class="text-sm text-slate-800 leading-relaxed font-sans">
                                <?= !empty($qa['jawaban_siswa']) ? nl2br(htmlspecialchars($qa['jawaban_siswa'])) : '<span class="italic text-slate-400">Siswa tidak mengisi jawaban esai ini.</span>' ?>
                            </div>
                        </div>

                        <!-- Input Nilai Skor oleh Guru -->
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <label class="text-xs font-bold text-slate-700">Berikan Skor Poin (0 - <?= $qa['bobot'] ?>):</label>
                            <input type="number" step="0.5" min="0" max="<?= $qa['bobot'] ?>" name="skor_esai[<?= $qa['qid'] ?>]" value="<?= $qa['nilai_skor'] ?: $qa['bobot'] ?>" required
                                class="w-24 px-3 py-2 bg-white border-2 border-blue-400 rounded-xl text-sm font-black text-blue-900 text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Catatan / Evaluasi Guru -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Catatan / Komentar Evaluasi untuk Murid:
                    </label>
                    <textarea name="catatan_guru" rows="2" placeholder="Contoh: Jawaban esai nomor 1 sangat baik. Tingkatkan lagi pemahaman pada kalimat efektif."
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm"><?= htmlspecialchars($activeStudentAttempt['catatan_guru'] ?? '') ?></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="submit" name="simpan_koreksi" value="1" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="bi bi-check2-circle text-lg"></i>
                        <span>Simpan Hasil Koreksi & Publikasikan Nilai</span>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Tabel Daftar Peserta Ujian -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-base text-slate-900">
                Daftar Murid yang Mengerjakan (<?= count($attempts) ?> Peserta)
            </h3>
            <span class="text-xs text-slate-400">Klik "Periksa Jawaban" untuk mengoreksi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-900 text-white font-bold uppercase text-[11px] tracking-wider text-left">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Murid</th>
                        <th class="py-3 px-4">NIK</th>
                        <th class="py-3 px-4">Rombel</th>
                        <th class="py-3 px-4 text-center">Nilai PG</th>
                        <th class="py-3 px-4 text-center">Nilai Esai</th>
                        <th class="py-3 px-4 text-center">Nilai Akhir</th>
                        <th class="py-3 px-4 text-center">Status Koreksi</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($attempts)): ?>
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-400">
                                Belum ada siswa yang menyelesaikan ujian ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; ?>
                        <?php foreach ($attempts as $at): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3.5 px-4 text-center font-bold text-slate-500"><?= $no++ ?></td>
                                <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($at['nama']) ?></td>
                                <td class="py-3.5 px-4 font-mono text-slate-600"><?= htmlspecialchars($at['nik']) ?></td>
                                <td class="py-3.5 px-4 text-slate-700"><?= htmlspecialchars($at['rombel_saat_ini']) ?></td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800"><?= number_format($at['nilai_pg'], 1) ?></td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">
                                    <?= $at['status_koreksi'] === 'sudah' ? number_format($at['nilai_esai'], 1) : '<span class="text-amber-600 text-xs font-sans">Belum</span>' ?>
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-black text-base text-blue-900">
                                    <?= number_format($at['nilai_total'], 1) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase <?= $at['status_koreksi'] === 'sudah' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                        <?= $at['status_koreksi'] === 'sudah' ? 'Selesai' : 'Perlu Koreksi' ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="?exam_id=<?= $selectedExamId ?>&attempt_id=<?= $at['id'] ?>" class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition inline-flex items-center gap-1 shadow-sm">
                                        <i class="bi bi-pencil-square"></i> Periksa Jawaban
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

