<?php
require_once dirname(__DIR__, 2) . '/config/app.php';

requireRole(['guru', 'admin']);

$examId = (int)($_GET['id'] ?? 0);
$pdo = getDB();

$stmtExam = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmtExam->execute([$examId]);
$exam = $stmtExam->fetch();

if (!$exam) {
    setFlash('danger', 'Ujian tidak ditemukan.');
    redirect('modules/guru/ujian_list.php');
}

// Handle Hapus Soal
if (isset($_GET['delete_soal'])) {
    $delQId = (int)$_GET['delete_soal'];
    $pdo->prepare("DELETE FROM exam_questions WHERE id = ? AND exam_id = ?")->execute([$delQId, $examId]);
    setFlash('success', 'Butir soal berhasil dihapus.');
    redirect("modules/guru/soal_manage.php?id={$examId}");
}

// Handle Tambah Soal Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis = sanitize($_POST['jenis'] ?? 'pg');
    $pertanyaan = trim($_POST['pertanyaan'] ?? '');
    $bobot = (float)($_POST['bobot'] ?? 10);
    $opsiA = trim($_POST['opsi_a'] ?? '');
    $opsiB = trim($_POST['opsi_b'] ?? '');
    $opsiC = trim($_POST['opsi_c'] ?? '');
    $opsiD = trim($_POST['opsi_d'] ?? '');
    $opsiE = trim($_POST['opsi_e'] ?? '');
    $kunciJawaban = sanitize($_POST['kunci_jawaban'] ?? '');

    if ($jenis === 'esai') {
        $kunciJawaban = trim($_POST['rubrik_esai'] ?? '');
    }

    if (empty($pertanyaan)) {
        setFlash('danger', 'Teks pertanyaan wajib diisi.');
    } else {
        // Hitung nomor urut berikutnya
        $stmtMax = $pdo->prepare("SELECT COALESCE(MAX(nomor_urut), 0) + 1 FROM exam_questions WHERE exam_id = ?");
        $stmtMax->execute([$examId]);
        $nomorUrut = (int)$stmtMax->fetchColumn();

        $stmtIns = $pdo->prepare("INSERT INTO exam_questions (
            exam_id, nomor_urut, jenis, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, kunci_jawaban, bobot
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtIns->execute([
            $examId, $nomorUrut, $jenis, $pertanyaan, $opsiA, $opsiB, $opsiC, $opsiD, $opsiE, $kunciJawaban, $bobot
        ]);

        setFlash('success', "Soal No. {$nomorUrut} berhasil ditambahkan ke bank soal ujian.");
        redirect("modules/guru/soal_manage.php?id={$examId}");
    }
}

// Ambil semua soal
$stmtQ = $pdo->prepare("SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY nomor_urut ASC");
$stmtQ->execute([$examId]);
$questions = $stmtQ->fetchAll();

$pageTitle = 'Kelola Butir Soal - ' . $exam['judul'];
require_once BASE_PATH . '/includes/admin_header.php';
?>

<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                    <?= htmlspecialchars($exam['mapel']) ?>
                </span>
                <span class="text-xs text-slate-500 font-semibold">• <?= htmlspecialchars($exam['paket']) ?></span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1"><?= htmlspecialchars($exam['judul']) ?></h1>
            <p class="text-xs text-slate-500">Kelola butir soal Pilihan Ganda (koreksi otomatis) dan Esai (koreksi mandiri)</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/modules/guru/ujian_list.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                Kembali ke Daftar Ujian
            </a>
            <a href="<?= BASE_URL ?>/modules/guru/rekap_nilai.php?exam_id=<?= $exam['id'] ?>" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                <i class="bi bi-printer"></i> Lihat Rekap Nilai
            </a>
        </div>
    </div>

    <!-- Form Input Soal Baru -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
            <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
                <i class="bi bi-plus-circle-fill text-blue-600"></i> Tambah Butir Soal Baru
            </h3>
            <span class="text-xs font-semibold text-slate-400">Total Soal Saat Ini: <?= count($questions) ?></span>
        </div>

        <form method="POST" action="" class="space-y-5">
            
            <!-- Jenis Soal -->
            <div class="flex items-center gap-6 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Pilih Tipe Soal:</span>
                <label class="flex items-center gap-2 cursor-pointer font-bold text-sm text-slate-800">
                    <input type="radio" name="jenis" value="pg" checked onchange="toggleFormSoal('pg')" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                    <span>Pilihan Ganda (PG - Deteksi Otomatis Nilai)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer font-bold text-sm text-slate-800">
                    <input type="radio" name="jenis" value="esai" onchange="toggleFormSoal('esai')" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                    <span>Esai / Uraian (Koreksi Mandiri Guru)</span>
                </label>
            </div>

            <!-- Teks Pertanyaan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Teks Pertanyaan / Soal <span class="text-rose-500">*</span>
                </label>
                <textarea name="pertanyaan" required rows="3" placeholder="Tuliskan butir soal pertanyaan disini..."
                    class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition leading-relaxed"></textarea>
            </div>

            <!-- SECTION PILIHAN GANDA -->
            <div id="section-pg" class="space-y-3.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Pilihan Jawaban (A, B, C, D, E) & Kunci Jawaban:
                </label>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 font-bold text-blue-700 flex items-center justify-center text-xs flex-shrink-0">A</span>
                        <input type="text" name="opsi_a" placeholder="Teks pilihan A" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 font-bold text-blue-700 flex items-center justify-center text-xs flex-shrink-0">B</span>
                        <input type="text" name="opsi_b" placeholder="Teks pilihan B" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 font-bold text-blue-700 flex items-center justify-center text-xs flex-shrink-0">C</span>
                        <input type="text" name="opsi_c" placeholder="Teks pilihan C" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 font-bold text-blue-700 flex items-center justify-center text-xs flex-shrink-0">D</span>
                        <input type="text" name="opsi_d" placeholder="Teks pilihan D" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white">
                    </div>
                    <div class="flex items-center gap-2 sm:col-span-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 font-bold text-blue-700 flex items-center justify-center text-xs flex-shrink-0">E</span>
                        <input type="text" name="opsi_e" placeholder="Teks pilihan E (opsional jika hanya 4 opsi)" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kunci Jawaban Benar <span class="text-rose-500">*</span>
                        </label>
                        <select name="kunci_jawaban" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-blue-800 focus:bg-white">
                            <option value="A">Pilihan A</option>
                            <option value="B">Pilihan B</option>
                            <option value="C">Pilihan C</option>
                            <option value="D">Pilihan D</option>
                            <option value="E">Pilihan E</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Bobot Poin Soal
                        </label>
                        <input type="number" name="bobot" value="10" min="1" max="100" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800">
                    </div>
                </div>
            </div>

            <!-- SECTION ESAI -->
            <div id="section-esai" class="hidden space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Rubrik / Panduan Kunci Jawaban Guru
                    </label>
                    <textarea name="rubrik_esai" rows="3" placeholder="Tuliskan poin-poin utama jawaban esai yang benar sebagai panduan saat mengoreksi jawaban siswa..."
                        class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white"></textarea>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="bi bi-save"></i> Simpan Butir Soal
                </button>
            </div>

        </form>
    </div>

    <!-- Daftar Butir Soal Terdaftar -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
        <h3 class="font-extrabold text-base text-slate-900 border-b border-slate-100 pb-3">
            Daftar Butir Soal Ujian (<?= count($questions) ?> Soal)
        </h3>

        <?php if (empty($questions)): ?>
            <div class="py-8 text-center text-slate-400 text-xs">
                Belum ada butir soal. Gunakan formulir di atas untuk menambahkan soal baru.
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($questions as $q): ?>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center">
                                    <?= $q['nomor_urut'] ?>
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $q['jenis'] === 'pg' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' ?>">
                                    <?= $q['jenis'] === 'pg' ? 'Pilihan Ganda' : 'Esai / Uraian' ?>
                                </span>
                                <span class="text-xs text-slate-400">Bobot: <?= $q['bobot'] ?> Poin</span>
                            </div>
                            <a href="?id=<?= $examId ?>&delete_soal=<?= $q['id'] ?>" onclick="return confirm('Hapus soal ini?')" class="text-rose-500 hover:text-rose-700 text-xs font-bold transition">
                                <i class="bi bi-trash mr-1"></i> Hapus
                            </a>
                        </div>

                        <div class="text-sm font-medium text-slate-900">
                            <?= nl2br(htmlspecialchars($q['pertanyaan'])) ?>
                        </div>

                        <?php if ($q['jenis'] === 'pg'): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-1">
                                <div class="<?= $q['kunci_jawaban'] === 'A' ? 'font-bold text-emerald-700' : 'text-slate-600' ?>">A. <?= htmlspecialchars($q['opsi_a']) ?></div>
                                <div class="<?= $q['kunci_jawaban'] === 'B' ? 'font-bold text-emerald-700' : 'text-slate-600' ?>">B. <?= htmlspecialchars($q['opsi_b']) ?></div>
                                <div class="<?= $q['kunci_jawaban'] === 'C' ? 'font-bold text-emerald-700' : 'text-slate-600' ?>">C. <?= htmlspecialchars($q['opsi_c']) ?></div>
                                <div class="<?= $q['kunci_jawaban'] === 'D' ? 'font-bold text-emerald-700' : 'text-slate-600' ?>">D. <?= htmlspecialchars($q['opsi_d']) ?></div>
                            </div>
                            <div class="text-xs font-bold text-emerald-700 mt-1">
                                <i class="bi bi-key-fill"></i> Kunci Jawaban: <?= $q['kunci_jawaban'] ?>
                            </div>
                        <?php else: ?>
                            <div class="text-xs text-slate-500 italic bg-white p-3 rounded-xl border border-slate-200">
                                <strong>Panduan Rubrik Guru:</strong> <?= htmlspecialchars($q['kunci_jawaban'] ?: 'Belum diisi') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
function toggleFormSoal(jenis) {
    const secPg = document.getElementById('section-pg');
    const secEsai = document.getElementById('section-esai');
    if (jenis === 'pg') {
        secPg.classList.remove('hidden');
        secEsai.classList.add('hidden');
    } else {
        secPg.classList.add('hidden');
        secEsai.classList.remove('hidden');
    }
}
</script>

<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

