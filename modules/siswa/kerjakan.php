<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole('siswa');

$examId = (int)($_GET['id'] ?? 0);
$studentId = $_SESSION['student_id'] ?? 0;
$pdo = getDB();

// Validasi ujian
$stmtExam = $pdo->prepare("SELECT * FROM exams WHERE id = ? AND status = 'aktif'");
$stmtExam->execute([$examId]);
$exam = $stmtExam->fetch();

if (!$exam) {
    setFlash('danger', 'Ujian tidak ditemukan atau sudah tidak aktif.');
    redirect('modules/siswa/ujian.php');
}

// Cek apakah sudah pernah menyelesaikan ujian ini
$stmtAttempt = $pdo->prepare("SELECT * FROM exam_attempts WHERE exam_id = ? AND student_id = ?");
$stmtAttempt->execute([$examId, $studentId]);
$attempt = $stmtAttempt->fetch();

if ($attempt && $attempt['status'] === 'selesai') {
    setFlash('info', 'Anda sudah menyelesaikan ujian ini sebelumnya.');
    redirect('modules/siswa/nilai.php');
}

// Jika belum ada attempt, buat attempt baru (status: sedang_mengerjakan)
if (!$attempt) {
    $stmtNew = $pdo->prepare("INSERT INTO exam_attempts (exam_id, student_id, status) VALUES (?, ?, 'sedang_mengerjakan')");
    $stmtNew->execute([$examId, $studentId]);
    $attemptId = $pdo->lastInsertId();
} else {
    $attemptId = $attempt['id'];
}

// Ambil semua soal untuk ujian ini
$stmtQuestions = $pdo->prepare("SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY nomor_urut ASC");
$stmtQuestions->execute([$examId]);
$questions = $stmtQuestions->fetchAll();

if (empty($questions)) {
    setFlash('warning', 'Belum ada butir soal yang diinputkan untuk ujian ini.');
    redirect('modules/siswa/ujian.php');
}

// Acak urutan soal menggunakan seed attempt_id agar urutan acak tetap konsisten bagi murid ini
if ($exam['acak_soal']) {
    mt_srand($attemptId * 100 + $studentId);
    $order = range(0, count($questions) - 1);
    shuffle($order);
    $shuffledQuestions = [];
    foreach ($order as $i) {
        $shuffledQuestions[] = $questions[$i];
    }
    $questions = $shuffledQuestions;
}

// Ambil jawaban yang sudah pernah disimpan sebelumnya jika siswa me-refresh halaman
$stmtSavedAnswers = $pdo->prepare("SELECT question_id, jawaban_siswa FROM exam_answers WHERE attempt_id = ?");
$stmtSavedAnswers->execute([$attemptId]);
$savedAnswers = $stmtSavedAnswers->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Mengerjakan Ujian: ' . $exam['judul'];
$schoolName = getSetting('nama_sekolah', APP_NAME);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($exam['judul']) ?> - <?= htmlspecialchars($schoolName) ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-slate-100 text-slate-800 antialiased exam-secure-mode select-none">

    <!-- Top Sticky Exam Header -->
    <header class="bg-slate-900 text-white sticky top-0 z-40 shadow-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white text-lg font-bold">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-extrabold tracking-tight truncate max-w-xs sm:max-w-md">
                        <?= htmlspecialchars($exam['judul']) ?>
                    </h1>
                    <div class="text-[10px] text-slate-400">
                        <?= htmlspecialchars($exam['mapel']) ?> • <?= htmlspecialchars($exam['paket']) ?>
                    </div>
                </div>
            </div>

            <!-- Fleksibel Status & Submit Trigger -->
            <div class="flex items-center gap-3">
                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 bg-slate-800 rounded-full text-xs font-semibold text-emerald-400">
                    <i class="bi bi-shield-lock-fill"></i> Mode Aman Ujian
                </span>
                <button type="button" id="btn-finish-exam" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="bi bi-check2-circle text-base"></i> Selesai Ujian
                </button>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
            
            <!-- Question Box Area (Col 1, 2, 3) -->
            <div class="lg:col-span-3">
                <form id="exam-form" action="<?= BASE_URL ?>/modules/siswa/selesai.php" method="POST">
                    <input type="hidden" name="exam_id" value="<?= $examId ?>">
                    <input type="hidden" name="attempt_id" value="<?= $attemptId ?>">

                    <?php foreach ($questions as $index => $q): ?>
                        <?php 
                        $num = $index + 1; 
                        $qid = $q['id'];
                        $savedAns = $savedAnswers[$qid] ?? '';
                        ?>
                        <div id="q-box-<?= $num ?>" class="question-box bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6 hidden">
                            
                            <!-- Header Soal -->
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-3.5 py-1.5 bg-blue-600 text-white font-black text-sm rounded-xl">
                                        Soal No. <?= $num ?>
                                    </span>
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold uppercase">
                                        <?= $q['jenis'] === 'pg' ? 'Pilihan Ganda' : 'Esai / Uraian' ?>
                                    </span>
                                </div>
                                <div class="text-xs text-slate-400 font-semibold">
                                    Bobot: <?= $q['bobot'] ?> Poin
                                </div>
                            </div>

                            <!-- Pertanyaan Teks -->
                            <div class="text-base sm:text-lg text-slate-900 font-medium leading-relaxed">
                                <?= nl2br(htmlspecialchars($q['pertanyaan'])) ?>
                            </div>

                            <!-- Jika ada gambar soal -->
                            <?php if (!empty($q['gambar'])): ?>
                                <div class="my-4 max-w-md">
                                    <img src="<?= BASE_URL ?>/uploads/soal/<?= htmlspecialchars($q['gambar']) ?>" alt="Gambar Soal" class="rounded-2xl border border-slate-200">
                                </div>
                            <?php endif; ?>

                            <!-- Jawaban Options -->
                            <?php if ($q['jenis'] === 'pg'): ?>
                                <div class="space-y-3 pt-2">
                                    <?php 
                                    $options = [
                                        'A' => $q['opsi_a'],
                                        'B' => $q['opsi_b'],
                                        'C' => $q['opsi_c'],
                                        'D' => $q['opsi_d'],
                                        'E' => $q['opsi_e']
                                    ];
                                    ?>
                                    <?php foreach ($options as $optKey => $optVal): ?>
                                        <?php if (empty($optVal)) continue; ?>
                                        <label class="flex items-start gap-3.5 p-4 rounded-2xl border-2 border-slate-200 hover:border-blue-400 bg-slate-50 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/60">
                                            <input type="radio" name="jawaban[<?= $qid ?>]" value="<?= $optKey ?>" <?= ($savedAns === $optKey) ? 'checked' : '' ?> class="mt-1 w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                            <div class="flex items-start gap-2.5">
                                                <span class="w-6 h-6 rounded-lg bg-white border border-slate-300 flex items-center justify-center font-bold text-xs text-slate-700 flex-shrink-0">
                                                    <?= $optKey ?>
                                                </span>
                                                <span class="text-sm font-medium text-slate-800 leading-snug">
                                                    <?= htmlspecialchars($optVal) ?>
                                                </span>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <!-- Esai Textarea -->
                                <div class="pt-2">
                                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                                        Tuliskan Jawaban Esai Anda di Bawah Ini:
                                    </label>
                                    <textarea name="jawaban[<?= $qid ?>]" rows="6" placeholder="Ketik jawaban penjelasan Anda secara lengkap dan mandiri..."
                                        class="w-full p-4 bg-slate-50 border-2 border-slate-200 rounded-2xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition leading-relaxed"><?= htmlspecialchars($savedAns) ?></textarea>
                                </div>
                            <?php endif; ?>

                            <!-- Bottom Navigation for Questions -->
                            <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                                <button type="button" id="btn-prev-q" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs flex items-center gap-1.5 transition">
                                    <i class="bi bi-arrow-left"></i> Soal Sebelumnya
                                </button>
                                <button type="button" id="btn-next-q" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center gap-1.5 transition">
                                    Soal Berikutnya <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </form>
            </div>

            <!-- Sticky Right Sidebar: Question Navigation Grid (Col 4) -->
            <div class="lg:col-span-1 bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sticky top-24 space-y-5">
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900">Navigasi Soal</h3>
                    <div id="answered-progress-text" class="text-xs font-bold text-emerald-600 mt-0.5">
                        0 / <?= count($questions) ?> Soal Terjawab
                    </div>
                </div>

                <!-- Number Grid Buttons -->
                <div class="grid grid-cols-5 gap-2 max-h-72 overflow-y-auto pr-1">
                    <?php foreach ($questions as $index => $q): ?>
                        <?php $num = $index + 1; ?>
                        <button type="button" data-q="<?= $num ?>" onclick="jumpToQuestion(<?= $num ?>)"
                            class="exam-nav-num h-10 rounded-xl border border-slate-300 bg-white font-black text-xs text-slate-700 hover:border-blue-500 flex items-center justify-center transition">
                            <?= $num ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Legend -->
                <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-500 space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded bg-emerald-600"></span>
                        <span>Sudah Dijawab</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded bg-white border border-slate-300"></span>
                        <span>Belum Dijawab</span>
                    </div>
                </div>

                <!-- Submit Button in Sidebar -->
                <div class="pt-2">
                    <button type="button" onclick="document.getElementById('btn-finish-exam').click()" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5">
                        <i class="bi bi-send-check"></i> Selesaikan & Kirim Ujian
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Exam Engine JavaScript -->
    <script src="<?= BASE_URL ?>/assets/js/exam.js"></script>
</body>
</html>

